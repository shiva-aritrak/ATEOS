<?php

include_once 'common.php';

function get_int_from_on_off($on_off_str) {
  if ($on_off_str == "on" ) {
    return "1";
  }
  else {
    return "0";
  }
}

function get_balancing_status()
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
  $out = "echo \"config ".$section." '".$sub_section."'\" >> /etc/config/".$config;
  exec($out);
  exec("uci commit ".$config);
}

function get_rule_name($rule_id)
{
  $out = exec("uci show mwan3.@rule[".$rule_id."].use_policy");
  return explode(".", $out)[1];
}

function get_html_for_policy($policy_name)
{
  $out = "";
  $members = exec("uci get mwan3.".$policy_name.".use_member");
  foreach (explode(" ", $members) as $member) {
    $mem_details = explode("_", $member);
    $out = $out."".str_replace("m","Priority ", $mem_details[3]).' = '.strtoupper($mem_details[0]).'</br>';
  }
  return $out;
}

function set_config()
{
  exec("uci commit mwan3");
  exec("/etc/init.d/mwan3 restart >/dev/null  &");
}

function get_members_for_interface($interface)
{
  exec("uci show mwan3 | grep -E \"=member\"  | grep ".$interface." | awk -F\"[.=]\" '{print $2}'", $out, $ret);
  return $out;
}

function get_percent_for_interface($interface)
{
  return exec("uci show mwan3 | grep -E \"mwan3.*_".$interface."_\" | grep weight | awk -F\"['=]\" '{print $3}'");
}

function get_default_ba_interfaces()
{
  exec("uci show mwan3 | grep def | grep -E \"=member\"  | awk -F'_' '{print $2}'", $out, $ret);
  return $out;
}

function get_priority_for_interface($interface)
{
  return exec("uci show mwan3 | grep -E \"mwan3.*_".$interface."_\" | grep metric | awk -F\"['=]\" '{print $3}'");
}

function get_priority_for_rule_interface($interface, $rule_id)
{
  $policy = exec("uci get mwan3.@rule[".$rule_id."].use_policy");
  $members = exec("uci get mwan3.".$policy.".use_member");
  foreach (explode(" ", $members) as $member) {
    if(strpos($member, $interface) !== false ) {
      $out = exec("uci get mwan3.".$member.".metric");
      return $out;
    }
  }
}

function delete_section($interface_name)
{
  exec("uci delete mwan3.".$interface_name);
  exec("uci commit mwan3");
}

function delete_balancing_interface($interface)
{
  $current_policy = exec("uci get def_rule.use_policy");
  foreach (get_members_for_interface($interface) as $member) {
    exec("uci delete mwan3.".$member);
    exec("uci del_list mwan3.".$current_policy.".use_member='".$member."'");
  }
  exec("uci delete mwan3.".$interface);
  // exec("uci delete network.".$interface.".metric");
  set_config();
}

function delete_balancing_rule($rule_no)
{
  $policy = exec("uci get mwan3.@rule[".$rule_no."].use_policy");
  $members = exec("uci get mwan3.".$policy.".use_member");
  foreach (explode(" ", $members) as $member) {
    exec("uci delete mwan3.".$member);
  }
  exec("uci delete mwan3.".$policy);
  exec("uci delete mwan3.@rule[".$rule_no."]");
  set_config();
}

function add_balancing_rule($new, $rule_no, $src, $src_port, $dest, $dest_port, $protocol, $applications, $session_adhere, $priority, $interfaces, $last_resort)
{
  $post_order_rule_no = $rule_no;

  if ($new == "1") {
    $post_order_rule_no = $rule_no - 2;
    exec("uci add mwan3 rule");
  } else {
    $policy = exec("uci get mwan3.@rule[".$rule_no."].use_policy");
    $members = exec("uci get mwan3.".$policy.".use_member");
    foreach (explode(" ", $members) as $member) {
      exec("uci delete mwan3.".$member);
    }
    exec("uci delete mwan3.".$policy);
    exec("ipset flush balapprule".$post_order_rule_no."");
    exec("uci commit mwan3");
  }

  create_section("policy", "r".$post_order_rule_no."_flb_bal", "mwan3");
  exec("uci delete mwan3.r".$post_order_rule_no."_flb_bal.use_member");
  exec("uci set mwan3.r".$post_order_rule_no."_flb_bal.last_resort='".$last_resort."'");
  exec("uci commit mwan3");

  foreach (array_filter($interfaces) as $interface) {
    if ($priority[$interface] != "") {
      create_section("member", $interface."_r".$post_order_rule_no."_w1_m".$priority[$interface], "mwan3");
      exec("uci set mwan3.".$interface."_r".$post_order_rule_no."_w1_m".$priority[$interface].".interface='".$interface."'");
      exec("uci set mwan3.".$interface."_r".$post_order_rule_no."_w1_m".$priority[$interface].".metric='".$priority[$interface]."'");
      exec("uci set mwan3.".$interface."_r".$post_order_rule_no."_w1_m".$priority[$interface].".weight='1'");
      exec("uci add_list mwan3.r".$post_order_rule_no."_flb_bal.use_member='".$interface."_r".$post_order_rule_no."_w1_m".$priority[$interface]."'");
      }
  }
  exec("uci commit mwan3");

  if(array_filter($applications)) {
    exec("ipset destroy balapprule".$post_order_rule_no."");
    exec("ipset create balapprule".$post_order_rule_no." hash:net");
    exec("uci set mwan3.@rule[".$rule_no."].ipset='balapprule".$post_order_rule_no."'");
    exec("uci set mwan3.balapprule".$post_order_rule_no."=apprule");
    exec("uci set mwan3.balapprule".$post_order_rule_no.".name='balapprule".$post_order_rule_no."'");
    exec("uci delete mwan3.balapprule".$post_order_rule_no.".apps");
    $domains_json = file_get_contents('/usr/lib/appsdb.json');
    $domains_db = json_decode($domains_json, true);

    foreach(array_filter($applications) as $application) {
      $app_name = str_replace(array(".", "-"), "_", $application);
      if ($application == "netify.tor") {
        $not_tor = false;
        exec("uci add_list mwan3.balapprule".$post_order_rule_no.".apps='".$application."'");
        exec("uci set mwan3.balapprule".$post_order_rule_no.".loadfile='/usr/lib/torlist.txt'");
      } else if ($application) {
        exec("uci add_list mwan3.balapprule".$post_order_rule_no.".apps='".$application."'");
        foreach($domains_db[$application] as $domain) {
          $records = dns_get_record($domain, DNS_A);
          foreach($records as $record) {
            if($record["type"] == "A") {
              exec("ipset add balapprule".$post_order_rule_no." ".$record["ip"]);
            }
          }
        }
      } else {
        exec("uci delete mwan3.balapprule".$post_order_rule_no);
      }
      exec("/etc/init.d/dpiagent restart");
      sleep(2);
      exec("/etc/init.d/netifyd restart");
    }
    exec("uci commit mwan3");
  }

  exec("uci set mwan3.@rule[".$rule_no."].family='any'");
  exec("uci set mwan3.@rule[".$rule_no."].src_ip='".$src."'");
  exec("uci set mwan3.@rule[".$rule_no."].src_port='".$src_port."'");
  if ($protocol != "all") {
    exec("uci set mwan3.@rule[".$rule_no."].proto='".$protocol."'");
  }
  exec("uci set mwan3.@rule[".$rule_no."].dest_ip='".$dest."'");
  exec("uci set mwan3.@rule[".$rule_no."].dest_port='".$dest_port."'");
  exec("uci set mwan3.@rule[".$rule_no."].sticky='".get_int_from_on_off($session_adhere)."'");
  exec("uci set mwan3.@rule[".$rule_no."].use_policy='r".$post_order_rule_no."_flb_bal'");

  if ($new == "1") {
    exec("uci reorder mwan3.@rule[".$rule_no."]=".$post_order_rule_no);
  }
  set_config();
}

function add_balancing_interface($interface_name, $status, $ping_ips, $proto_family, $min_resp, $min_ping_resp, $resp_timeout, $check_interval, $failure_latency, $accept_latency, $check_perf, $failure_loss, $recovery_loss, $no_of_failed_tests, $no_of_success_tests, $terminate_sessions)
{
  // exec("uci set network.".$interface_name.".metric='".$INTERFACE_METRICS[$interface_name]."'");

  delete_section($interface_name);
  create_section("interface", $interface_name, "mwan3");
  $pre = "uci set mwan3.".$interface_name;

  exec($pre.".enabled='".$status."'");
  foreach ($ping_ips as $ping_ip) {
    if ($ping_ip) {
      exec("uci add_list mwan3.".$interface_name.".track_ip='".$ping_ip."'");
    }
  }

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
  exec($pre.".down='".$no_of_failed_tests."'");
  exec($pre.".up='".$no_of_success_tests."'");
  exec($pre.".family='".$proto_family."'");
  exec($pre.".initial_state='online'");
  exec($pre.".track_method='ping'");
  exec($pre.".size='56'");
  exec($pre.".failure_interval='2'");
  exec($pre.".recovery_interval='2'");
  if ($terminate_sessions == "on") {
    exec($pre.".flush_conntrack='always'");
  }
  set_config();
}

function clear_defaults()
{
  $out = "";
  exec("uci show mwan3 | grep 'def_' | awk -F'.' '{print $2}' | grep -v rule | grep -v '=' | sort -u", $out);
  foreach ($out as $member) {
    exec("uci delete mwan3.".$member);
  }
  exec("uci commit");
}

function delete_default_policies()
{
  $out = "";
  exec("uci show mwan3 | grep -E \"=policy\" | awk -F'[\.=]'  '{print $2}'", $out);
  foreach ($out as $policy) {
    exec("uci delete mwan3.".$policy);
  }
}

function set_lb_config($local_source_interface, $rt_table_lookup, $mode, $interfaces, $ba_interfaces, $percent, $priority, $def_rule_https_sticky, $def_all_rule_sticky, $sticky_timeout, $last_resort)
{
  if ($local_source_interface == "none") {
    exec("uci delete mwan3.globals.local_source");
  } else {
    exec("uci set mwan3.globals.local_source='".$local_source_interface."'");
  }

  if ($rt_table_lookup == "none") {
    exec("uci delete mwan3.globals.rt_table_lookup");
  } else {
    exec("uci set mwan3.globals.rt_table_lookup='".$rt_table_lookup."'");
  }
  exec("uci set mwan3.def_all_rule.routing_last_resort='".$last_resort."'");

  if ($mode == "disabled") {
    foreach ($interfaces as $interface) {
      exec("uci set mwan3.".$interface.".enabled='0'");
    }
    exec("uci set mwan3.def_all_rule.use_policy='disabled'");
    exec("uci set mwan3.def_rule_https.use_policy='disabled'");
    exec("uci commit mwan3");
    exec("/etc/init.d/mwan3 stop");
    exec("/etc/init.d/mwan3 disabled");
    return;
  } else if ($mode == "ba") {
    clear_defaults();
    create_section("policy", "def_ba_bal", "mwan3");
    foreach ($ba_interfaces as $ba_interface) {
      create_section("member", "def_".$ba_interface."_m1_w1", "mwan3");
      exec("uci set mwan3.def_".$ba_interface."_m1_w1.interface='".$ba_interface."'");
      exec("uci set mwan3.def_".$ba_interface."_m1_w1.weight='1'");
      exec("uci set mwan3.def_".$ba_interface."_m1_w1.metric='1'");
      exec("uci add_list mwan3.def_ba_bal.use_member='def_".$ba_interface."_m1_w1'");
    }
    exec("uci set mwan3.def_ba_bal.last_resort='".$last_resort."'");
    exec("uci commit mwan3");

    exec("uci set mwan3.def_rule_https.use_policy='def_ba_bal'");
    exec("uci set mwan3.def_all_rule.use_policy='def_ba_bal'");

  } else if ($mode == "llb") {
    clear_defaults();
    create_section("policy", "def_llb_bal", "mwan3");
    foreach ($interfaces as $interface) {
      create_section("member", "def_".$interface."_m1_w".$percent[$interface]."", "mwan3");
      exec("uci set mwan3.def_".$interface."_m1_w".$percent[$interface].".interface='".$interface."'");
      exec("uci set mwan3.def_".$interface."_m1_w".$percent[$interface].".weight='".$percent[$interface]."'");
      exec("uci set mwan3.def_".$interface."_m1_w".$percent[$interface].".metric='1'");
      exec("uci add_list mwan3.def_llb_bal.use_member='def_".$interface."_m1_w".$percent[$interface]."'");
    }
    exec("uci set mwan3.def_llb_bal.last_resort='".$last_resort."'");

    exec("uci commit mwan3");

    exec("uci set mwan3.def_rule_https.use_policy='def_llb_bal'");
    exec("uci set mwan3.def_all_rule.use_policy='def_llb_bal'");

  } else if ($mode == "flb") {
    clear_defaults();
    create_section("policy", "def_flb_bal", "mwan3");
    foreach ($interfaces as $interface) {
      create_section("member", "def_".$interface."_w1_m".$priority[$interface]."", "mwan3");
      exec("uci set mwan3.def_".$interface."_w1_m".$priority[$interface].".interface='".$interface."'");
      exec("uci set mwan3.def_".$interface."_w1_m".$priority[$interface].".metric='".$priority[$interface]."'");
      exec("uci set mwan3.def_".$interface."_w1_m".$priority[$interface].".weight='1'");
      exec("uci add_list mwan3.def_flb_bal.use_member='def_".$interface."_w1_m".$priority[$interface]."'");
    }
    exec("uci set mwan3.def_flb_bal.last_resort='".$last_resort."'");
    exec("uci commit mwan3");

    exec("uci set mwan3.def_rule_https.use_policy='def_flb_bal'");
    exec("uci set mwan3.def_all_rule.use_policy='def_flb_bal'");
  }

  if ($def_rule_https_sticky && $def_rule_https_sticky == "on") {
    exec("uci set mwan3.def_rule_https.sticky='1'");
    exec("uci set mwan3.def_rule_https.timeout='".$sticky_timeout."'");
  } else {
    exec("uci set mwan3.def_rule_https.sticky='0'");
    exec("uci delete mwan3.def_rule_https.timeout");
  }

  if ($def_all_rule_sticky && $def_all_rule_sticky == "on") {
    exec("uci set mwan3.def_all_rule.sticky='1'");
    exec("uci set mwan3.def_all_rule.timeout='".$sticky_timeout."'");
  } else {
    exec("uci set mwan3.def_all_rule.sticky='0'");
    exec("uci delete mwan3.def_all_rule.timeout");
  }
  exec("uci commit mwan3");
  exec("sync");

  set_config();
}

?>
