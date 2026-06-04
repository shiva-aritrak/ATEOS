<?php

function cidr2NetmaskAddr($cidr) {
  $ta = substr ($cidr, strpos ($cidr, '/') + 1) * 1;
  $netmask = str_split (str_pad (str_pad ('', $ta, '1'), 32, '0'), 8);
  foreach ($netmask as &$element)
    $element = bindec ($element);
  return join ('.', $netmask);
}

function fetch_fuse_config($fuse_id, $fuse_server_address)
{
  try {
    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");

    if ($license_key == "No License Key") {
      return array(False, True);
    }

    $ch = curl_init( "https://".$fuse_server_address."/api/ace/config/fetch" );
    $payload = json_encode( array( "fuse_id"=> $fuse_id, "license_key"=> $license_key  ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "AnexFuse Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);

    curl_close($ch);

    $json_response = json_decode($result, true);


    if ($json_response["valid"] == "Yes") {
      exec("uci set fuse.globals.success='1'");
      exec("uci set fuse.globals.message='".$json_response["message"]."'");

      exec("uci delete fuse.globals.vpn_port");
      foreach($json_response["vpn_port"] as $vpn_port) {
        exec("uci add_list fuse.globals.vpn_port='".$vpn_port."'");
      }
      exec("uci set fuse.globals.wan_count='".$json_response["wan_connections"]."'");
      exec("uci set fuse.globals.vpn_key='".$json_response["ta_key"]."'");
      exec("uci set fuse.globals.fuse_mode='".$json_response["bond_mode"]."'");
      exec("uci set fuse.globals.fuse_xmit_policy='".$json_response["hash_policy"]."'");
      exec("uci set fuse.globals.mtu_size='".$json_response["mtu_size"]."'");
      exec("uci set fuse.globals.bond_client_network='".$json_response["bond_subnet"]."'");
      exec("uci set fuse.globals.bond_client_ip='".$json_response["client_ip"]."'");
      exec("uci set fuse.globals.bond_client_nm='".cidr2NetmaskAddr($json_response["bond_subnet"])."'");
      exec("uci set fuse.globals.bond_client_gw='".$json_response["bond_ip"]."'");
      exec("uci set fuse.globals.split_tunnel='".$json_response["split_tunnel"]."'");
     
      return True;
    } else {
      exec("uci set fuse.globals.success='0'");
      exec("uci set fuse.globals.message='".$json_response["message"]."'");
      exec("uci delete fuse.globals.wan_count");
      exec("uci delete fuse.globals.vpn_key");
      exec("uci delete fuse.globals.fuse_mode");
      exec("uci delete fuse.globals.fuse_xmit_policy");
      exec("uci delete fuse.globals.mtu_size");
      exec("uci delete fuse.globals.bond_client_network");
      exec("uci delete fuse.globals.split_tunnel");
      exec("uci delete fuse.globals.vpn_port");

      exec("uci delete fuse.globals.transport_layer");
      exec("uci delete fuse.globals.bridge_port");
      
      return False;
    }
  }
  catch(Exception $e) {
    exec("uci set fuse.globals.success='0'");
    exec("uci set fuse.globals.message='".$json_response["message"]."'");
    exec("uci delete fuse.globals.vpn_key");
    exec("uci delete fuse.globals.fuse_mode");
    exec("uci delete fuse.globals.fuse_xmit_policy");
    exec("uci delete fuse.globals.mtu_size");
    exec("uci delete fuse.globals.bond_client_network");
    exec("uci delete fuse.globals.split_tunnel");
	
    exec("uci delete fuse.globals.transport_layer");                                                                                                                                                    
    exec("uci delete fuse.globals.bridge_port");
    return False;
  }
  exec("uci commit fuse");
  exec("sync");
}

function set_fuse_config($status, $fuse_id, $fuse_server_address, $fuse_interfaces, $local_source_interface, $fu_def_internet, $priority, $transport_layer, $port_name, $vlan_id)
{
  exec("uci set fuse.globals.status='".$status."'");
  exec("ip route del default dev lo");
  exec("uci delete fuse.globals.fu_failover_int");
  exec("uci delete fuse.globals.fu_def_internet");

  if($status == "0") {
    exec("uci set fuse.globals.success='0'");
    exec("cp /rom/etc/config/mwan3 /etc/config/mwan3");

    exec("uci delete fuse.globals.wan_count");
    exec("uci delete fuse.globals.wan_interface");
    exec("uci delete fuse.globals.vpn_key");
    exec("uci delete fuse.globals.fuse_mode");
    exec("uci delete fuse.globals.fuse_xmit_policy");
    exec("uci delete fuse.globals.mtu_size");
    exec("uci delete fuse.globals.bond_client_network");
    exec("uci delete fuse.globals.bond_client_ip");
    exec("uci delete fuse.globals.bond_client_nm");
    exec("uci delete fuse.globals.bond_client_gw");
    exec("uci delete fuse.globals.split_tunnel");
    exec("uci delete fuse.globals.vpn_port");
    exec("uci delete network.bond0");

    exec("uci delete fuse.globals.transport_layer");                                                                                                                                                    
    exec("uci delete fuse.globals.bridge_port");

    exec("uci commit fuse");
    exec("uci commit network");
    exec("/bin/sync");
    exec("/etc/init.d/network reload");
    exec("/etc/init.d/mwan3 reload");
    exec("/etc/init.d/fuse stop");
  } else {
    exec("uci set fuse.globals.fuse_id='".$fuse_id."'");
    exec("uci set fuse.globals.fuse_server_address='".$fuse_server_address."'");
    exec("uci set fuse.globals.local_source='".$local_source_interface."'");

    exec("uci delete fuse.globals.wan_interface");
    foreach ($fuse_interfaces as $fuse_interface) {
      exec("uci add_list fuse.globals.wan_interface='".$fuse_interface."'");
    }
    exec("uci commit fuse");
    exec("sync");
  
    $fuse_fetch_status = fetch_fuse_config($fuse_id, $fuse_server_address);

    exec("uci set fuse.globals.transport_layer='".$transport_layer."'");                                                                                                        
    exec("uci delete fuse.globals.port_name");
    exec("uci set fuse.globals.port_name='".$port_name."'");
    $network_port_name = exec("uci get network'.$port_name.'name");                                                                                                                   
    
    if ($vlan_id){
	$prev_vlan_id = exec("uci get fuse.globals.vlan_id");
	$vlan_del = 'vlan' . $prev_vlan_id;
	exec("uci delete network.$vlan_del");
	
	exec("uci delete fuse.globals.vlan_id");
        $network_port_name = $network_port_name.'.'.$vlan_id;
        exec("uci set fuse.globals.vlan_id='".$vlan_id."'");
    }
    else
	{
	$prev_vlan_id = exec("uci get fuse.globals.vlan_id");
	$vlan_del = 'vlan' . $prev_vlan_id;                                                                                                                                      
        exec("uci delete network.$vlan_del");                                                                                                                          
	
	exec("uci delete fuse.globals.vlan_id");
}
    
    exec("uci delete fuse.globals.bridge_port");                                                                                                             
    exec("uci add_list fuse.globals.bridge_port='".$network_port_name."'");                                                                                                             
    exec("uci add_list fuse.globals.bridge_port='bond0'");   
                                                                                                                                                                                                                                                     

    if ($fu_def_internet == "0") {
      exec("uci set fuse.globals.fu_def_internet='".$fu_def_internet."'");
      foreach (array_filter($priority) as $interface => $priority_val) {
        exec("uci add_list fuse.globals.fu_failover_int='".$interface."'");
      }
    } else {
      exec("uci set fuse.globals.fu_def_internet='".$fu_def_internet."'");
    }
    
    exec("uci commit fuse");
    exec("uci network commit");
    exec("sync");
  
    if ($fuse_fetch_status) {
      exec("/bin/sync");
      exec("/etc/init.d/fuse restart");
    }  
  }
}

function get_fuse_interface_status()
{
  $ret_out = array();
  exec("mwan3 interfaces", $out, $ret);
  foreach ($out as $line) {
    $items = explode(" ", trim($line));
    $ret_out[$items[1]] = $items[3];
  }
  return $ret_out;
}

function create_section($section, $sub_section, $config)
{
  $out = "echo \"\n\nconfig ".$section." '".$sub_section."'\" >> /etc/config/".$config;
  exec($out);
  exec("uci commit ".$config);
}

function delete_fuse_interface($interface)
{
  exec("uci delete fuse.".$interface);
  set_config();
}

function delete_section($interface_name)
{
  exec("uci delete fuse.".$interface_name);
  exec("uci commit fuse");
}

function set_config()
{
  exec("uci commit fuse");
  exec("sync");
}

function add_fuse_interface($interface_name, $status, $ping_ips, $min_resp, $min_ping_resp, $resp_timeout, $check_interval, $failure_latency, $accept_latency, $check_perf, $failure_loss, $recovery_loss)
{
  delete_section($interface_name);
  create_section("interface", $interface_name, "fuse");
  $pre = "uci set fuse.".$interface_name;

  exec($pre.".enabled='".$status."'");
  foreach ($ping_ips as $ping_ip) {
    if ($ping_ip) {
      exec("uci add_list fuse.".$interface_name.".track_ip='".$ping_ip."'");
    }
  }

  exec($pre.".family='ipv4'");
  exec($pre.".reliability='".$min_resp."'");
  exec($pre.".count='".$min_ping_resp."'");
  exec($pre.".timeout='".$resp_timeout."'");
  exec($pre.".interval='".$check_interval."'");
  exec($pre.".failure_latency='".$failure_latency."'");
  exec($pre.".recovery_latency='".$accept_latency."'");
  if ($check_perf == "on") {
    exec($pre.".check_quality='1'");
  }
  exec($pre.".check_quality='".$check_perf."'");
  exec($pre.".failure_loss='".$failure_loss."'");
  exec($pre.".recovery_loss='".$recovery_loss."'");
  exec($pre.".down='3'");
  exec($pre.".up='8'");
  exec($pre.".initial_state='online'");
  exec($pre.".track_method='ping'");
  exec($pre.".size='56'");
  exec($pre.".failure_interval='5'");
  exec($pre.".recovery_interval='5'");
  exec($pre.".flush_conntrack='always'");
  set_config();
}


?>