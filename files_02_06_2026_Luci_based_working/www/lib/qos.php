<?php

function set_qos_config()
{
  exec("uci commit qos");
  exec("/etc/init.d/qos reload");
}

function get_int_from_on_off($on_off_str) {
  if ($on_off_str == "on" ) {
    return "1";
  } else {
    return "0";
  }
}

function get_added_qos_id()
{
  $ret = exec("uci show qos | grep -E '=classify' | awk -F '[' '{print $2}' | awk -F ']' '{print $1}'");
  return $ret;
}

function get_interface_from_classification_id($classify_id)
{
  $target = exec("uci get qos.@classify[".$classify_id."].target");
  $interface = array_shift(explode('_', $target));
  return $interface;
}


function get_target_from_classification_id($classify_id)
{
  $target = exec("uci get qos.@classify[".$classify_id."].target");
  return $target;
}

function delete_qos_basic($interface)
{
  exec("uci delete qos.".$interface."_Default");
  exec("uci delete qos.".$interface."_Default_Class");
  exec("uci delete qos.".$interface);
  set_qos_config();
}

function create_section($section, $sub_section, $config)
{
  $out = "echo \"config ".$section." '".$sub_section."'\" >> /etc/config/".$config;
  exec($out);
}

function set_basic_qos($interface, $upload_limit, $download_limit, $saturation, $status)
{
  delete_qos_basic($interface);
  create_section("classgroup", $interface."_Default", "qos");
  exec("uci set qos.".$interface."_Default.classes='".$interface."_Default_Class'");
  exec("uci set qos.".$interface."_Default.default='".$interface."_Default_Class'");
  create_section("class", $interface."_Default_Class", "qos");
  exec("uci set qos.".$interface."_Default_Class.packetsize='1500'");
  exec("uci set qos.".$interface."_Default_Class.packetdelay='100'");
  exec("uci set qos.".$interface."_Default_Class.avgrate='10'");
  exec("uci set qos.".$interface."_Default_Class.priority='5'");

  create_section("interface", $interface, "qos");
  exec("uci set qos.".$interface.".classgroup='".$interface."_Default'");
  if ($status == "enabled") {
    exec("uci set qos.".$interface.".enabled='1'");
  } else {
    exec("uci set qos.".$interface.".enabled='0'");
  }

  exec("uci set qos.".$interface.".upload='".$upload_limit."'");
  exec("uci set qos.".$interface.".download='".$download_limit."'");
  exec("uci set qos.".$interface.".overhead='".get_int_from_on_off($saturation)."'");
  set_qos_config();
}

function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function delete_advanced_qos($classify_id)
{
  $wan_interface = get_interface_from_classification_id($classify_id);
  $class_name = get_target_from_classification_id($classify_id);

  exec("uci delete qos.@classify[".$classify_id."]");
  exec("uci delete qos.".$class_name);

  $current_classes = explode(" ", exec("uci get qos.".$wan_interface."_Default.classes"));

  if (in_array( $class_name, $current_classes )) {
    array_splice($current_classes, array_search($class_name, $current_classes), array_search($class_name, $current_classes));
    $new_classes = implode(" ", $current_classes);
    exec("uci set qos.".$wan_interface."_Default.classes='".$new_classes."'");
  }
  set_qos_config();
}


function set_advanced_qos($classify_id, $wan_interface, $proto, $source_ip_subnet, $source_port, $dest_ip_subnet, $dest_port, $percent_limit, $comment)
{

  if($classify_id != "-1") {
    delete_advanced_qos($classify_id);
  }

  $random_str = generateRandomString();
  exec("uci add qos classify");
  $added_id = get_added_qos_id();

  if ($added_id == "0") {
    exec("/etc/init.d/qos enable");
  }

  $class_name = $wan_interface."_".$random_str;

  exec("uci set qos.@classify[-1].target='".$class_name."'");
  if ($proto != "tcp/udp") {
    exec("uci set qos.@classify[-1].proto='".$proto."'");
  }

  if ($source_ip_subnet) { exec("uci set qos.@classify[-1].srchost='".$source_ip_subnet."'"); }
  if ($source_port) { exec("uci set qos.@classify[-1].srcports='".$source_port."'"); }
  if ($dest_ip_subnet) { exec("uci set qos.@classify[-1].dsthost='".$dest_ip_subnet."'"); }
  if ($dest_port) { exec("uci set qos.@classify[-1].dstports='".$dest_port."'"); }
  if ($comment) { exec("uci set qos.@classify[-1].comment='".$comment."'"); }

  exec("uci set qos.".$class_name."=class");
  exec("uci set qos.".$class_name.".packetsize='1500'");
  exec("uci set qos.".$class_name.".packetdelay='10'");
  exec("uci set qos.".$class_name.".limitrate='".$percent_limit."'");

  $current_classes = explode(" ", exec("uci get qos.".$wan_interface."_Default.classes"));
  if (!in_array( $class_name, $current_classes )) {
    array_push( $current_classes ,$class_name );
    $new_classes = implode(" ", $current_classes);
    exec("uci set qos.".$wan_interface."_Default.classes='".$new_classes."'");
  }
  set_qos_config();
}


?>
