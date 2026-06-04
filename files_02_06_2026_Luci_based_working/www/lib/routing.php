<?php

function get_int_from_on_off($on_off_str) {
  if ($on_off_str == "on" ) {
    return "1";
  }
  else {
    return "0";
  }
}

function get_routes()
{
  $out = "";
  exec("uci show network | grep -E '=route' | grep -v loopback | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function str_starts_with($haystack, $needle) {
  return $needle !== '' && strpos($haystack, $needle) === 0;
}

function set_static_route()
{
  exec('uci commit network');
  exec("sync");
  exec("/etc/init.d/network reload");
}

function set_policy_routes()
{
  exec('uci commit');
  exec("sync");
  exec("/etc/init.d/vpn-policy-routing restart");
}

function configure_static_route($route_no, $interface_name, $dest_ip, $netmask, $gateway_ip, $metric, $mtu, $force_route, $float_route, $routing_table)
{
  if ($route_no == "-1") {
    exec("uci add network route");
  }

  $pre = "uci set network.@route[".$route_no."].";

  if ($interface_name) {
    exec($pre."interface='".$interface_name."'");
  } else {
    exec("uci delete network.@route[".$route_no."].interface");
  }

  exec($pre."target=".$dest_ip);
  exec($pre."netmask=".$netmask);
  exec($pre."gateway=".$gateway_ip);

  if ($force_route == "on") {
    exec($pre."onlink='1'");
  } else {
    exec($pre."onlink='0'");
  }
  if ($float_route == "on") {
    exec($pre."float='1'");
  } else {
    exec("uci delete network.@route[".$route_no."].float");
  }

  if ($metric) {
    exec($pre."metric=".$metric);
  } else {
    exec("uci delete network.@route[".$route_no."].metric");
  }
  if ($mtu) {
    exec($pre."mtu=".$mtu);
  } else {
    exec("uci delete network.@route[".$route_no."].mtu");
  }

  if ($routing_table) {
    exec($pre."table=".$routing_table);
  } else {
    exec("uci delete network.route".$route_no.".table");
  }
  set_static_route();
}

function configure_static6_route($route_no, $interface_name, $dest_ip, $gateway_ip, $metric, $mtu, $force_route, $float_route, $routing_table)
{
  if ($route_no == "-1") {
    exec("uci add network route6");
  }

  $pre = "uci set network.@route6[".$route_no."].";

  if ($interface_name) {
    exec($pre."interface='".$interface_name."'");
  } else {
    exec("uci delete network.@route6[".$route_no."].interface");
  }

  exec($pre."target=".$dest_ip);
  exec($pre."gateway=".$gateway_ip);

  if ($force_route == "on") {
    exec($pre."onlink='1'");
  } else {
    exec($pre."onlink='0'");
  }
  if ($float_route == "on") {
    exec($pre."float='1'");
  } else {
    exec("uci delete network.@route6[".$route_no."].float");
  }

  if ($metric) {
    exec($pre."metric=".$metric);
  } else {
    exec("uci delete network.@route6[".$route_no."].metric");
  }
  if ($mtu) {
    exec($pre."mtu=".$mtu);
  } else {
    exec("uci delete network.@route6[".$route_no."].mtu");
  }

  if ($routing_table) {
    exec($pre."table=".$routing_table);
  } else {
    exec("uci delete network.@route6[".$route_no."].table");
  }
  set_static_route();
}

function delete_static_route($route_no) {
  exec("uci delete network.@route[".$route_no."]");
  set_static_route();
}

function delete_static6_route($route_no) {
  exec("uci delete network.@route6[".$route_no."]");
  set_static_route();
}

function configure_policy_route($route_no, $interface, $local_addresses, $local_ports, $remote_addresses, $remote_ports, $comment)
{
  if ($route_no == "-1") {
    exec("uci add vpn-policy-routing policy");
  }

  $pre = "uci set vpn-policy-routing.@policy[".$route_no."].";

  if ($interface) { exec($pre."interface=".$interface); }
  if ($local_addresses) { exec($pre."local_addresses=".$local_addresses); }
  if ($local_ports) { exec($pre."local_ports=".$local_ports); }
  if ($remote_addresses) { exec($pre."remote_addresses=".$remote_addresses); }
  if ($remote_ports) { exec($pre."remote_ports=".$remote_ports); }
  if ($comment) { exec($pre."comment=".$comment); }

  set_policy_routes();
}

function delete_policy_route($filled_route_no)
{
  exec("uci delete vpn-policy-routing.@policy[".$filled_route_no."]");
}

function set_bird_config($v46)
{
  exec("sync");
  if ($v46 == "0") {
    exec("/etc/init.d/bird4 restart");
  } else {
    exec("/etc/init.d/bird6 restart");
  }
}

function clear_bgp_route($bgp_route_no, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.bgp".$bgp_route_no."");
    exec("uci commit bird4");
  } else {
    exec("uci delete bird6.bgp".$bgp_route_no."");
    exec("uci commit bird6");
  }
}

function configure_bgp_route($bgp_route_no, $v46, $status, $local_id, $local_as, $remote_id, $remote_as)
{
  if ($v46 == "0") {
      exec("uci delete bird4.bgp".$bgp_route_no."");
      exec("uci set bird4.bgp".$bgp_route_no."=bgp");
      exec("uci set bird4.bgp".$bgp_route_no.".disabled='".$status."'");
      exec("uci set bird4.bgp".$bgp_route_no.".import='all'");
      exec("uci set bird4.bgp".$bgp_route_no.".export='all'");
      exec("uci set bird4.bgp".$bgp_route_no.".source_address='".$local_id."'");
      exec("uci set bird4.bgp".$bgp_route_no.".local_as='".$local_as."'");
      exec("uci set bird4.bgp".$bgp_route_no.".neighbor_address='".$remote_id."'");
      exec("uci set bird4.bgp".$bgp_route_no.".neighbor_as='".$remote_as."'");
      exec("uci commit bird4");
  } else {
      exec("uci delete bird6.bgp".$bgp_route_no."");
      exec("uci set bird6.bgp".$bgp_route_no."=bgp");
      exec("uci set bird6.bgp".$bgp_route_no.".disabled='".$status."'");
      exec("uci set bird6.bgp".$bgp_route_no.".import='all'");
      exec("uci set bird6.bgp".$bgp_route_no.".export='all'");
      exec("uci set bird6.bgp".$bgp_route_no.".source_address='".$local_id."'");
      exec("uci set bird6.bgp".$bgp_route_no.".local_as='".$local_as."'");
      exec("uci set bird6.bgp".$bgp_route_no.".neighbor_address='".$remote_id."'");
      exec("uci set bird6.bgp".$bgp_route_no.".neighbor_as='".$remote_as."'");
      exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function delete_bgp($filled_configured_bgp, $configured_bgp, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.".$filled_configured_bgp."");
    exec("uci commit bird4");
  } else {
    exec("uci delete bird6.".$filled_configured_bgp."");
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function configure_global_params($router_id, $log, $debug, $interfaces, $v46)
{
    if ($v46 == "0") {
      exec("uci set bird4.global.router_id='".$router_id."'");
      if ($log == "off") {
	      exec("uci set bird4.global.log='off'");
      } else {
	      exec("uci set bird4.global.log='all'");
      }
      if ($debug == "off") {
	      exec("uci set bird4.global.debug='off'");
      } else {
	      exec("uci set bird4.global.debug='all'");
      }
      exec("uci delete bird4.direct1");
      exec("uci set bird4.direct1=direct");
      exec("uci set bird4.direct1.disabled='0'");
      foreach (array_filter($interfaces) as $interface) {
        $intf_type = exec("uci -q get network.".$interface.".type");
        if($intf_type == "bridge") {
          exec("uci add_list bird4.direct1.interface='br-".$interface."'");
        } else {
          $raw_intf = exec("uci -q get network.".$interface.".ifname");
          exec("uci add_list bird4.direct1.interface='".$raw_intf."'");
        }
      }
    exec("uci commit bird4");
    } else {
      exec("uci set bird6.global.router_id='".$router_id."'");
      if ($log == "off") {
        exec("uci set bird6.global.log='off'");
      } else {
        exec("uci set bird6.global.log='all'");
      }
      if ($debug == "off") {
        exec("uci set bird6.global.debug='off'");
      } else {
        exec("uci set bird6.global.debug='all'");
      }
      exec("uci delete bird6.direct1");
      exec("uci set bird6.direct1=direct");
      exec("uci set bird6.direct1.disabled='0'");
      foreach (array_filter($interfaces) as $interface) {
        $intf_type = exec("uci -q get network.".$interface.".type");
        if($intf_type == "bridge") {
          exec("uci add_list bird6.direct1.interface='br-".$interface."'");
        } else {
          $raw_intf = exec("uci -q get network.".$interface.".ifname");
          exec("uci add_list bird6.direct1.interface='".$raw_intf."'");
        }
      }
      exec("uci commit bird6");
    }
    set_bird_config($v46);
}

function add_ospf_networks($ospf_net_no, $name, $network_subnet, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.net".$ospf_net_no."");
    exec("uci commit bird4");
    exec("uci set bird4.net".$ospf_net_no."=ospf_networks");
    exec("uci set bird4.net".$ospf_net_no.".name='".$name."'");
    exec("uci add_list bird4.net".$ospf_net_no.".range='".$network_subnet."'");
    exec("uci commit bird4");
    set_bird_config($v46);
  } else {
    exec("uci delete bird6.net".$ospf_net_no."");
    exec("uci commit bird6");
    exec("uci set bird6.net".$ospf_net_no."=ospf_networks");
    exec("uci set bird6.net".$ospf_net_no.".name='".$name."'");
    exec("uci add_list bird6.net".$ospf_net_no.".range='".$network_subnet."'");
    exec("uci commit bird6");
    set_bird_config($v46);
  }
}

function delete_ospf_networks($filled_ospf_network, $ospf_network, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.".$filled_ospf_network."");
    exec("uci commit bird4");
  } else {
    exec("uci delete bird6.".$filled_ospf_network."");
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function delete_ospf_interface($interface, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.".$interface."");
    exec("uci commit bird4");
  } else {
    exec("uci delete bird6.".$interface."");
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function add_ospf_interface($interface_name, $cost, $hello, $priority, $wait, $dead, $retransmit, $auth, $passphrase, $v46)
{
  if ($v46 == "0") {
    if ($interface_name == "lan_br0") {
      $raw_interface = "lan_br0";
    } else {
      $raw_interface = exec("uci -q get network.".$interface_name.".ifname");
    }
    exec("uci delete bird4.".$interface_name."");
    exec("uci commit bird4");
    exec("uci set bird4.".$interface_name."=ospf_interface");
    exec("uci set bird4.".$interface_name.".interface='".$raw_interface."'");
    exec("uci set bird4.".$interface_name.".cost='".$cost."'");
    exec("uci set bird4.".$interface_name.".type='broadcast'");
    exec("uci set bird4.".$interface_name.".hello='".$hello."'");
    exec("uci set bird4.".$interface_name.".priority='".$priority."'");
    exec("uci set bird4.".$interface_name.".wait='".$wait."'");
    exec("uci set bird4.".$interface_name.".dead='".$dead."'");
    exec("uci set bird4.".$interface_name.".retransmit='".$retransmit."'");
    if ($auth == "MD5") {
        exec("uci set bird4.".$interface_name.".authentication='MD5'");
    } else if ($auth == "Simple") {
        exec("uci set bird4.".$interface_name.".authentication='simple'");
    } else {
      exec("uci delete bird4.".$interface_name.".authentication");
    }
    exec("uci commit bird4");
  } else {
    if ($interface_name == "lan_br0") {
      $raw_interface = "lan_br0";
    } else {
      $raw_interface = exec("uci -q get network.".$interface_name.".ifname");
    }
    exec("uci delete bird6.".$interface_name."");
    exec("uci commit bird6");
    exec("uci set bird6.".$interface_name."=ospf_interface");
    exec("uci set bird6.".$interface_name.".interface='".$raw_interface."'");
    exec("uci set bird6.".$interface_name.".cost='".$cost."'");
    exec("uci set bird6.".$interface_name.".type='broadcast'");
    exec("uci set bird6.".$interface_name.".hello='".$hello."'");
    exec("uci set bird6.".$interface_name.".priority='".$priority."'");
    exec("uci set bird6.".$interface_name.".wait='".$wait."'");
    exec("uci set bird6.".$interface_name.".dead='".$dead."'");
    exec("uci set bird6.".$interface_name.".retransmit='".$retransmit."'");
    if ($auth == "MD5") {
        exec("uci set bird6.".$interface_name.".authentication='MD5'");
    } else if ($auth == "Simple") {
        exec("uci set bird6.".$interface_name.".authentication='simple'");
    } else {
      exec("uci delete bird6.".$interface_name.".authentication");
    }
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function add_ospf_area($area, $stub, $def_cost, $networks, $interfaces, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.".$area."");
    exec("uci commit bird4");
    exec("uci set bird4.".$area."=ospf_area");
    exec("uci set bird4.".$area.".instance='".$area."'");
    exec("uci set bird4.".$area.".stub='".$stub."'");
    exec("uci set bird4.".$area.".name='".$area."'");
    exec("uci set bird4.".$area.".default_cost='".$def_cost."'");
    foreach ($networks as $network) {
      exec("uci add_list bird4.".$area.".ospf_networks='".$network."'");
      exec("uci set bird4.".$network.".area='".$area."'");
    }
    foreach($interfaces as $interface) {
      exec("uci add_list bird4.".$area.".ospf_interface='".$interface."'");
      exec("uci set bird4.".$interface.".area='".$area."'");
    }
    exec("uci commit bird4");
  } else {
    exec("uci delete bird6.".$area."");
    exec("uci commit bird6");
    exec("uci set bird6.".$area."=ospf_area");
    exec("uci set bird6.".$area.".instance='".$area."'");
    exec("uci set bird6.".$area.".stub='".$stub."'");
    exec("uci set bird6.".$area.".name='".$area."'");
    exec("uci set bird6.".$area.".default_cost='".$def_cost."'");
    foreach ($networks as $network) {
      exec("uci add_list bird6.".$area.".ospf_networks='".$network."'");
      exec("uci set bird6.".$network.".area='".$area."'");
    }
    foreach($interfaces as $interface) {
      exec("uci add_list bird6.".$area.".ospf_interface='".$interface."'");
      exec("uci set bird6.".$interface.".area='".$area."'");
    }
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function delete_ospf_area($filled_area, $ospf_area, $v46)
{
  if ($v46 == "0") {
    exec("uci delete bird4.".$filled_area."");
    exec("uci commit bird4");
  } else {
    exec("uci delete bird6.".$filled_area."");
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

function set_ospf_instance($status, $tick, $areas, $v46)
{

  if ($v46 == "0") {
    if ($status == "0") {
      exec("uci delete bird4.ospf1");
      exec("uci commit bird4");
      exec("uci set bird4.ospf1=ospf");
      exec("uci set bird4.ospf1.disabled='".$status."'");
      exec("uci set bird4.ospf1.tick='".$tick."'");
      exec("uci set bird4.ospf1.import='all'");
      exec("uci set bird4.ospf1.export='all'");
      foreach($areas as $area) {
        exec("uci add_list bird4.ospf1.ospf_area='".$area."'");
      }
    } else {
      exec("uci delete bird4.ospf1");
    }
    exec("uci commit bird4");
  } else {
    if ($status == "0") {
      exec("uci delete bird6.ospf1");
      exec("uci commit bird6");
      exec("uci set bird6.ospf1=ospf");
      exec("uci set bird6.ospf1.disabled='".$status."'");
      exec("uci set bird6.ospf1.tick='".$tick."'");
      exec("uci set bird6.ospf1.import='all'");
      exec("uci set bird6.ospf1.export='all'");
      foreach($areas as $area) {
        exec("uci add_list bird6.ospf1.ospf_area='".$area."'");
      }
    } else {
      exec("uci delete bird6.ospf1");
    }
    exec("uci commit bird6");
  }
  set_bird_config($v46);
}

?>
