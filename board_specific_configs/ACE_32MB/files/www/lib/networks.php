<?php

function create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name)
{
	exec("uci set network.vlan".$vlan_id."=device");
	exec("uci set network.vlan".$vlan_id.".type='8021q'");
	exec("uci set network.vlan".$vlan_id.".vid='".$vlan_id."'");
	exec("uci set network.vlan".$vlan_id.".ifname='".$wan_ifname."'");
	exec("uci commit network.vlan".$vlan_id);
}

function delete_vlan_device($vlan_id)
{
	exec("uci delete network.vlan".$vlan_id);
}

function check_and_change_dhcp_server_config($interface_name, $lan_ip)
{
	$status = exec("uci get dhcp.".$interface_name.".ignore");
	if ($status == "0") {
		$lan_ip_num = ip2long($lan_ip);

		$start = exec("uci get dhcp.".$interface_name.".start");
		$range = exec("uci get dhcp.".$interface_name.".limit");

		$startip_num = $lan_ip_num + $start - 1;
		$endip_num = $startip_num + $range;

		$startip = long2ip($startip_num);
		$endip = long2ip($endip_num);

		exec("uci set dhcp.".$interface_name.".start_ip='".$startip."'");
		exec("uci set dhcp.".$interface_name.".end_ip='".$endip."'");
		exec("uci commit dhcp");
	}
}

function set_bridge_config($port_names, $ip_addr, $netmask, $bridge_stp, $mtu) 
{
	clear_wan("lan_br0", $reload=false);
	clear_bridge_dev_device($port_names);
	create_bridge_dev_device($port_names);
	exec("uci set network.lan_br0=interface");
	exec("uci set network.lan_br0.type='bridge'");
	exec("uci set network.lan_br0.proto='static'");
	exec("uci set network.lan_br0.ipv6='0'");
	exec("uci set network.lan_br0.enabled='1'");
	exec("uci set network.lan_br0.ipaddr='".$ip_addr."'");
	exec("uci set network.lan_br0.netmask='".$netmask."'");
	
	if ($bridge_stp && $bridge_stp == "on") {
		exec("uci delete network.lan_br0.stp");
		exec("uci set network.lan_br0.stp='1'");
	} else {
		exec("uci set network.lan_br0.stp='0'");
	}

	$used_switch_ports = false;
	exec("uci set network.lan_br0.ports='".implode(" ", $port_names)."'");
	exec("uci delete network.lan_br0.ifname");
	foreach($port_names as $port_name) {
		$switch_port = exec("uci get network.".$port_name.".switch_port");
		if ($switch_port == "0") {
			$raw_if = exec("uci get network.".$port_name.".raw_if");
			exec("uci add_list network.lan_br0.ifname='".$raw_if."'");
		} else {
			$used_switch_ports = true;
		}
	}
	if($used_switch_ports) {
		exec("uci add_list network.lan_br0.ifname='eth0.8'");
	}

	check_and_change_dhcp_server_config("lan_br0", $ip_addr);

	set_config($reload=true);
}


function clear_dev_device($port_name)
{
	$dev_name = str_replace("port", "dev", $port_name );
	$svport_name = str_replace("port", "svport", $port_name );
	exec("uci delete network.".$svport_name);
	exec("uci delete network.".$dev_name);
	exec("uci commit network");
}

function create_dev_device($wan_ifname, $port_name)
{
	
	$switch_port = exec("uci get network.".$port_name.".switch_port");
	if ($switch_port == "1") {
		$sw_name = exec("uci get network.main.name");
		$port_vlan = exec("uci get network.".$port_name.".vlan");
		$port_no = exec("uci get network.".$port_name.".port_no");
		$raw_if = exec("uci get network.".$port_name.".raw_if");
		$tag_port = exec("uci get network.".$port_name.".tag_port");
		$mac_addr = exec("uci get network.".$port_name.".mac_addr");
		
		$dev_name = str_replace("port", "dev", $port_name );
		$svport_name = str_replace("port", "svport", $port_name );
		exec("uci set network.".$svport_name."=switch_vlan");
		exec("uci set network.".$svport_name.".device='".$sw_name."'");
		exec("uci set network.".$svport_name.".vlan='".$port_vlan."'");
		exec("uci set network.".$svport_name.".ports='".$port_no." ".$tag_port."t'");
		exec("uci commit network.".$svport_name);
	
		exec("uci set network.".$dev_name."=device");
		exec("uci set network.".$dev_name.".port_num='".$port_name."'");
		exec("uci set network.".$dev_name.".name='".$wan_ifname."'");
		exec("uci set network.".$dev_name.".macaddr='".$mac_addr."'");
		exec("uci commit network.".$dev_name);	
	} else if ($switch_port == "0") {
		$dev_name = str_replace("port", "dev", $port_name );

		exec("uci set network.".$dev_name."=device");
		exec("uci set network.".$dev_name.".port_num='".$port_name."'");
		exec("uci set network.".$dev_name.".name='".$wan_ifname."'");
		exec("uci set network.".$dev_name.".macaddr='".$mac_addr."'");
		exec("uci commit network.".$dev_name);	
	}

}


function clear_bridge_dev_device($port_names)
{
	exec("uci delete network.devbr");
	exec("uci delete network.svbridge");
	foreach ($port_names as $port_name) {
		exec("uci set network.".$port_name.".bridged='0'");
	}
}

function create_bridge_dev_device($port_names)
{
	$sw_name = exec("uci get network.main.name");
	$port_vlan = exec("uci get network.".$port_names[0].".vlan");
	$port_no = exec("uci get network.".$port_names[0].".port_no");
	$raw_if = exec("uci get network.".$port_names[0].".raw_if");
	$tag_port = exec("uci get network.".$port_names[0].".tag_port");
	$mac_addr = exec("uci get network.".$port_names[0].".mac_addr");
	
	$dev_name = "devbr";
	$svport_name = "svbridge";
	exec("uci set network.".$svport_name."=switch_vlan");
	exec("uci set network.".$svport_name.".device='".$sw_name."'");
	exec("uci set network.".$svport_name.".vlan='8'");

	$port_nos = array();
	foreach ($port_names as $port_name) {
		$switch_port = exec("uci get network.".$port_name.".switch_port");
		if ($switch_port == "1") {
			$port_no = exec("uci get network.".$port_name.".port_no");
			array_push($port_nos, $port_no);
			exec("uci set network.".$port_name.".bridged='1'");
			if ($mtu) {
				exec("uci set network.".$interface_name.".mtu='".$mtu."'");
			} else {
				exec("uci delete network.".$interface_name.".mtu");
			}	
		} else {
			exec("uci set network.".$port_name.".bridged='1'");
		}
	}
	exec("uci set network.".$svport_name.".ports='".implode(" ", $port_nos)." ".$tag_port."t'");
	exec("uci commit network.".$svport_name);

	exec("uci set network.".$dev_name."=device");
	exec("uci set network.".$dev_name.".macaddr='".$mac_addr."'");
	exec("uci set network.".$dev_name.".name='".$raw_if.".8'");
	exec("uci commit network.".$dev_name);
}


function set_wan_static($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $ip_addr, $subnet, $gateway, $dns1, $dns2, $default_route, $metric, $mtu, $port_type, $wan_desc, $fw_zone)
{
	clear_wan($edit_interface, $reload=false);
	clear_dev_device($edit_port_num);
	create_dev_device($wan_ifname, $port_name);

	exec("uci set network.".$interface_name."=interface");
	exec("uci set network.".$interface_name.".port_num='".$port_name."'");
	exec("uci set network.".$interface_name.".proto='static'");
	exec("uci set network.".$interface_name.".enabled='1'");

	if ($wan_desc) {
		exec("uci set network.".$interface_name.".desc='".$wan_desc."'");
	} else {
		exec("uci delete network.".$interface_name.".desc='".$wan_desc."'");
	}

	if ($fw_zone) {
		exec("uci set network.".$interface_name.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$interface_name."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$interface_name."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$interface_name.".fw_zone");
	}

	if ($vlan_id) {
		create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci set network.".$interface_name.".vlan_id='".$vlan_id."'");
		exec("uci set network.".$interface_name.".macaddr='00:".implode(':', str_split(substr(md5(mt_rand()), 0, 10), 2))."'");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname.".".$vlan_id."'");
	} else {
		delete_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci delete network.".$interface_name.".vlan_id");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	}

	exec("uci set network.".$interface_name.".ipaddr=".$ip_addr);
	exec("uci set network.".$interface_name.".netmask=".$subnet);

	if ($mtu) {
		exec("uci set network.".$interface_name.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$interface_name.".mtu");
	}

	if ($interface_type == "wan") {
		if ($metric) {
			exec("uci set network.".$interface_name.".metric='".$metric."'");
		} else {
			exec("uci delete network.".$interface_name.".metric");
		}

		if ($default_route && $default_route == "on") {
			exec("uci delete network.".$interface_name.".defaultroute");
			exec("uci set network.".$interface_name.".defaultroute='1'");
		} else {
			exec("uci set network.".$interface_name.".defaultroute='0'");
		}
		
		exec("uci set network.".$interface_name.".gateway=".$gateway);
		if ($dns1) {
			exec("uci add_list network.".$interface_name.".dns='".$dns1."'");
		}
		if ($dns2) {
			exec("uci add_list network.".$interface_name.".dns='".$dns2."'");			
		}
	} else if ($interface_type == "lan") {
		if ($port_type && $port_type == "on") {
			exec("uci set network.".$interface_name.".type='bridge'");
		}
		exec("uci set network.".$interface_name.".defaultroute='0'");
		check_and_change_dhcp_server_config($interface_name, $ip_addr);
	}
	exec("uci set network.".$interface_name.".ipv6='0'");
	exec("uci set network.".$port_name.".ipv4_configured='1'");
	set_config($reload=true);
}

function set_wan_pppoe($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $service_name, $username, $password, $metric, $mtu, $peerdns, $wan_desc, $fw_zone)
{
	clear_wan($edit_interface, $reload=false);
	clear_dev_device($edit_port_num);
	create_dev_device($wan_ifname, $port_name);
	exec("uci set network.".$interface_name."=interface");

	if ($wan_desc) {
		exec("uci set network.".$interface_name.".desc='".$wan_desc."'");
	} else {
		exec("uci delete network.".$interface_name.".desc='".$wan_desc."'");
	}

	if ($metric) {
		exec("uci set network.".$interface_name.".metric='".$metric."'");
	} else {
		exec("uci delete network.".$interface_name.".metric");
	}
	if ($mtu) {
		exec("uci set network.".$interface_name.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$interface_name.".mtu");
	}

	if ($fw_zone) {
		exec("uci set network.".$interface_name.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$interface_name."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$interface_name."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$interface_name.".fw_zone");
	}

	if ($vlan_id) {
		create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci set network.".$interface_name.".vlan_id='".$vlan_id."'");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname.".".$vlan_id."'");
	} else {
		delete_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci delete network.".$interface_name.".vlan_id");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	}

	exec("uci set network.".$interface_name.".proto=pppoe");
	exec("uci set network.".$interface_name.".enabled='1'");
	exec("uci set network.".$interface_name.".port_num='".$port_name."'");
	exec("uci set network.".$interface_name.".service='".$service_name."'");
	exec("uci set network.".$interface_name.".username='".$username."'");
	exec("uci set network.".$interface_name.".password='".$password."'");
	exec("uci set network.".$interface_name.".ipv6='0'");
	exec("uci set network.".$port_name.".ipv4_configured='1'");

	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.".$interface_name.".peerdns");
		exec("uci set network.".$interface_name.".peerdns='1'");
	} else {
		exec("uci set network.".$interface_name.".peerdns='0'");
	}

	set_config($reload=true);
}

function set_modem_mode($wwan_interface, $mode) {
	$command = 'timeout 4 /bin/at-cmd '.$wwan_interface.' \'AT+QCFG="nwscanmode",'.$mode.'\'';
	exec($command);
}


function set_wan_dhcp($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $metric, $mtu, $peerdns, $wan_desc, $fw_zone)
{
	clear_wan($edit_interface, $reload=false);
	clear_dev_device($edit_port_num);
	create_dev_device($wan_ifname, $port_name);

	exec("uci set network.".$interface_name."=interface");
	exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	if ($wan_desc) {
		exec("uci set network.".$interface_name.".desc='".$wan_desc."'");
	} else {
		exec("uci delete network.".$interface_name.".desc='".$wan_desc."'");
	}
	if ($metric) {
		exec("uci set network.".$interface_name.".metric='".$metric."'");
	} else {
		exec("uci delete network.".$interface_name.".metric");
	}

	if ($mtu) {
		exec("uci set network.".$interface_name.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$interface_name.".mtu");
	}

	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.".$interface_name.".peerdns");
		exec("uci set network.".$interface_name.".peerdns='1'");
	} else {
		exec("uci set network.".$interface_name.".peerdns='0'");
	}

	if ($fw_zone) {
		exec("uci set network.".$interface_name.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$interface_name."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$interface_name."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$interface_name.".fw_zone");
	}

	if ($vlan_id) {
		create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci set network.".$interface_name.".vlan_id='".$vlan_id."'");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname.".".$vlan_id."'");
	} else {
		delete_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci delete network.".$interface_name.".vlan_id");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	}

	exec("uci set network.".$interface_name.".port_num='".$port_name."'");
	exec("uci set network.".$interface_name.".ipv6='0'");
	exec("uci set network.".$interface_name.".enabled='1'");
	exec("uci set network.".$interface_name.".proto=dhcp");
	exec("uci set network.".$port_name.".ipv4_configured='1'");

	set_config($reload=true);
}


function set_wan6_static($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $ip_addr, $ip6ifaceid, $ip6ifaceid_custom, $gateway, $dns1, $dns2, $default_route, $metric, $wan_static_prefix_length, $wan_static_ip6prefix, $ip6_prefix_hint, $mtu, $port_type, $wan_desc, $fw_zone)
{
	clear_wan6($edit_interface, $reload=false);
	clear_dev_device($edit_port_num);
	create_dev_device($wan_ifname, $port_name);

	exec("uci set network.".$interface_name."=interface");
	exec("uci set network.".$interface_name.".port_num='".$port_name."'");
	exec("uci set network.".$interface_name.".proto='static'");
	exec("uci set network.".$interface_name.".enabled='1'");
	exec("uci set network.".$interface_name.".ipv6='1'");

	if ($wan_desc) {
		exec("uci set network.".$interface_name.".desc='".$wan_desc."'");
	} else {
		exec("uci delete network.".$interface_name.".desc='".$wan_desc."'");
	}

	if ($fw_zone) {
		exec("uci set network.".$interface_name.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$interface_name."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$interface_name."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$interface_name.".fw_zone");
	}

	if ($vlan_id) {
		create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci set network.".$interface_name.".vlan_id='".$vlan_id."'");
		exec("uci set network.".$interface_name.".macaddr='00:".implode(':', str_split(substr(md5(mt_rand()), 0, 10), 2))."'");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname.".".$vlan_id."'");
	} else {
		delete_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci delete network.".$interface_name.".vlan_id");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	}

	if ($ip_addr) {
		exec("uci set network.".$interface_name.".ip6addr=".$ip_addr);
	}
	
	if ($ip6ifaceid == "custom") {
		exec("uci set network.".$interface_name.".ip6ifaceid='".$ip6ifaceid_custom."'");
	} else if ($ip6ifaceid) {
		exec("uci set network.".$interface_name.".ip6ifaceid='".$ip6ifaceid."'");
	}

	exec("uci set network.".$interface_name.".ip6prefix=".$wan_static_ip6prefix);
	exec("uci set network.".$interface_name.".ip6assign=".$wan_static_prefix_length);
	
	if ($mtu) {
		exec("uci set network.".$interface_name.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$interface_name.".mtu");
	}

	if ($interface_type == "wan") {
		if ($metric) {
			exec("uci set network.".$interface_name.".metric='".$metric."'");
		} else {
			exec("uci delete network.".$interface_name.".metric");
		}

		if ($default_route && $default_route == "on") {
			exec("uci delete network.".$interface_name.".defaultroute");
			exec("uci set network.".$interface_name.".defaultroute='1'");
		} else {
			exec("uci set network.".$interface_name.".defaultroute='0'");
		}
		exec("uci set network.".$interface_name.".ip6gw=".$gateway);
		if ($dns1) {
			exec("uci add_list network.".$interface_name.".dns='".$dns1."'");
		}
		if ($dns2) {
			exec("uci add_list network.".$interface_name.".dns='".$dns2."'");			
		}
	} else if ($interface_type == "lan") {
		if ($port_type && $port_type == "on") {
			exec("uci set network.".$interface_name.".type='bridge'");
		}
		exec("uci set network.".$interface_name.".defaultroute='0'");
		exec("uci set network.".$interface_name.".ip6hint='".$ip6_prefix_hint."'");
		check_and_change_dhcp_server_config($interface_name, $ip_addr);
	}
	exec("uci set network.".$interface_name.".ipv6='1'");
	exec("uci set network.".$port_name.".ipv6_configured='1'");
	set_config($reload=true);
}

function set_wan6_pppoe($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $service_name, $username, $password, $metric, $mtu, $peerdns, $wan_desc, $fw_zone)
{
	clear_wan6($edit_interface, $reload=false);
	clear_dev_device($edit_port_num);
	create_dev_device($wan_ifname, $port_name);
	exec("uci set network.".$interface_name."=interface");

	if ($wan_desc) {
		exec("uci set network.".$interface_name.".desc='".$wan_desc."'");
	} else {
		exec("uci delete network.".$interface_name.".desc='".$wan_desc."'");
	}

	if ($fw_zone) {
		exec("uci set network.".$interface_name.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$interface_name."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$interface_name."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$interface_name.".fw_zone");
	}

	if ($metric) {
		exec("uci set network.".$interface_name.".metric='".$metric."'");
	} else {
		exec("uci delete network.".$interface_name.".metric");
	}

	if ($vlan_id) {
		create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci set network.".$interface_name.".vlan_id='".$vlan_id."'");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname.".".$vlan_id."'");
	} else {
		delete_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci delete network.".$interface_name.".vlan_id");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	}

	if ($mtu) {
		exec("uci set network.".$interface_name.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$interface_name.".mtu");
	}

	exec("uci set network.".$interface_name.".proto=pppoe");
	exec("uci set network.".$interface_name.".enabled='1'");
	exec("uci set network.".$interface_name.".port_num='".$port_name."'");
	exec("uci set network.".$interface_name.".service='".$service_name."'");
	exec("uci set network.".$interface_name.".username='".$username."'");
	exec("uci set network.".$interface_name.".password='".$password."'");
	exec("uci set network.".$interface_name.".ipv6='1'");
	exec("uci set network.".$port_name.".ipv6_configured='1'");

	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.".$interface_name.".peerdns");
		exec("uci set network.".$interface_name.".peerdns='1'");
	} else {
		exec("uci set network.".$interface_name.".peerdns='0'");
	}

	set_config($reload=true);
}

function set_wan6_dhcp($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $metric, $ip6prefix, $sourcefilter, $mtu, $peerdns, $reqaddress, $reqprefix, $reqprefix_custom, $wan_desc, $fw_zone)
{
	clear_wan6($edit_interface, $reload=false);
	clear_dev_device($edit_port_num);
	create_dev_device($wan_ifname, $port_name);

	exec("uci set network.".$interface_name."=interface");
	exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");

	if ($wan_desc) {
		exec("uci set network.".$interface_name.".desc='".$wan_desc."'");
	} else {
		exec("uci delete network.".$interface_name.".desc='".$wan_desc."'");
	}

	if ($fw_zone) {
		exec("uci set network.".$interface_name.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$interface_name."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$interface_name."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$interface_name.".fw_zone");
	}

	if ($metric) {
		exec("uci set network.".$interface_name.".metric='".$metric."'");
	} else {
		exec("uci delete network.".$interface_name.".metric");
	}

	if ($ip6prefix) {
		exec("uci add_list network.".$interface_name.".ip6prefix='".$ip6prefix."'");
	} else {
		exec("uci delete network.".$interface_name.".ip6prefix");
	}

	if ($sourcefilter && $sourcefilter == "on") {
		exec("uci set network.".$interface_name.".sourcefilter='1'");
	} else {
		exec("uci set network.".$interface_name.".sourcefilter='0'");
	}

	if ($vlan_id) {
		create_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci set network.".$interface_name.".vlan_id='".$vlan_id."'");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname.".".$vlan_id."'");
	} else {
		delete_vlan_device($vlan_id, $port_name, $wan_ifname, $interface_name);
		exec("uci delete network.".$interface_name.".vlan_id");
		exec("uci set network.".$interface_name.".ifname='".$wan_ifname."'");
	}

	if ($mtu) {
		exec("uci set network.".$interface_name.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$interface_name.".mtu");
	}

	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.".$interface_name.".peerdns");
		exec("uci set network.".$interface_name.".peerdns='1'");
	} else {
		exec("uci set network.".$interface_name.".peerdns='0'");
	}
	
	if ($reqprefix == "custom") {
		exec("uci set network.".$interface_name.".reqprefix='".$reqprefix_custom."'");
	} else {
		exec("uci set network.".$interface_name.".reqprefix='".$reqprefix."'");
	}
	
	exec("uci set network.".$interface_name.".reqaddress='".$reqaddress."'");
	exec("uci set network.".$interface_name.".port_num='".$port_name."'");
	exec("uci set network.".$interface_name.".enabled='1'");
	exec("uci set network.".$interface_name.".ipv6='1'");
	exec("uci set network.".$interface_name.".proto=dhcpv6");
	exec("uci set network.".$port_name.".ipv6_configured='1'");

	set_config($reload=true);
}

function set_xlat_config($clatd_enable, $interface, $ip6prefix, $metric, $interface_ip, $fw_zone, $mtu)
{
	if($clatd_enable == "on") {
		exec("uci delete network.clatd");
		exec("uci set network.clatd=interface");

		exec("uci set network.clatd.enabled='1'");
		exec("uci set network.clatd.proto='464xlat'");
		exec("uci set network.clatd.tunintf='".$interface."'");
		if($interface) {
			exec("uci set network.clatd.interface='".$interface."'");
			exec("uci set network.clatd.tunlink='".$interface."_6'");
		}
		if ($ip6prefix) {
			exec("uci set network.clatd.ip6prefix='".$ip6prefix."'");
		}
		if ($mtu) {
			exec("uci set network.clatd.mtu='".$mtu."'");
		}
		if ($metric) {
			exec("uci set network.clatd.metric='".$metric."'");
		}
		if ($interface_ip) {
			exec("uci set network.clatd.interface_ip='".$interface_ip."'");
		}
		if ($fw_zone) {
			exec("uci set network.clatd.fw_zone='".$fw_zone."'");
			$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
			for ($zone = 0; $zone < $zone_num; $zone++){
				exec("uci del_list firewall.@zone[".$zone."].network='clatd'");
				exec("uci commit firewall");
			}
			for ($zone = 0; $zone < $zone_num; $zone++){
				$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
				if ($firewall_zone == $fw_zone) {
					exec("uci add_list firewall.@zone[".$zone."].network='clatd'");
					exec("uci commit firewall");
				}
			}
		} else {
			exec("uci delete network.clatd.fw_zone");
		}
	} else {
		exec("uci delete network.clatd");
	}
	set_config($reload=true);
}


function set_ula_prefix($ula_prefix)
{
	exec("uci set network.globals.ula_prefix='".$ula_prefix."'");
	set_config($reload=true);
}

function set_config_nwmon()
{
	exec('uci commit nwmon');
	exec("sync");
	exec("/etc/init.d/nwmon restart");
}

function set_config_sim_switching()
{
	exec('uci commit simswitch');
	exec("sync");
	exec("/etc/init.d/simswitcher restart");
}

function set_config($reload=false)
{
	exec('uci commit');
	exec("sync");
	if ($reload) {
		exec("/etc/init.d/network reload");
		exec("/etc/init.d/firewall reload");
	}
}

function clear_wan($iface, $reload=false)
{
	$ifname=exec('uci get network.'.$iface.'.ifname');

	$port_name = exec("uci get network.".$iface.".port_num");
	if ($port_name) {
		$interfaces_on_port = array();
		$counter = 0;
		exec("uci show network | grep ".$port_name." | grep port_num | grep -v dev  | grep -v '\.".$iface."\.' | awk -F'.' '{print $2}'", $interfaces_on_port );
		foreach($interfaces_on_port as $interface_on_port) {
			$is_ipv6 = exec("uci get network.".$interface_on_port.".ipv6");
			if (is_ipv6 == "0") {
				++$counter;
			}
		}
		if($counter == 0) {
			exec("uci set network.".$port_name.".ipv4_configured='0'");
		}
	}

	$vlan_id=exec('uci get network.'.$iface.'.vlan_id');
	if ($vlan_id) {
		exec('uci delete network.vlan'.$vlan_id);
	}
	exec('uci delete network.'.$iface);
	$ifname=exec("uci set network.".$iface.".ifname='".$ifname."'");
	exec("uci commit network");
	exec("sync");
	set_config($reload);
}


function clear_wan6($iface, $reload=false)
{
	$ifname=exec('uci get network.'.$iface.'.ifname');

	$port_name = exec("uci get network.".$iface.".port_num");

	if ($port_name) {
		$interfaces_on_port = array();
		$counter = 0;
		exec("uci show network | grep ".$port_name." | grep port_num | grep -v dev  | grep -v '\.".$iface."\.' | awk -F'.' '{print $2}'", $interfaces_on_port );
		foreach($interfaces_on_port as $interface_on_port) {
			$is_ipv6 = exec("uci get network.".$interface_on_port.".ipv6");
			if (is_ipv6 == "1") {
				++$counter;
			}
		}
		if($counter == 0) {
			exec("uci set network.".$port_name.".ipv6_configured='0'");
		}
	}

	$vlan_id=exec('uci get network.'.$iface.'.vlan_id');
	if ($vlan_id) {
		exec('uci delete network.vlan'.$vlan_id);
	}
	exec('uci delete network.'.$iface);
	$ifname=exec("uci set network.".$iface.".ifname='".$ifname."'");
	exec("uci commit network");
	exec("sync");
	set_config($reload);
}

function clear_sim_switch_module($iface)
{
	exec('uci delete simswitch.'.$iface);
}

function set_loopback($ip_addr, $netmask)
{
	exec("uci delete network.lo1");
	exec("uci set network.lo1=interface");
	exec("uci set network.lo1.ifname='lo'");
	exec("uci set network.lo1.proto='static'");
	exec("uci set network.lo1.ipaddr='".$ip_addr."'");
	exec("uci set network.lo1.netmask='".$netmask."'");
	set_config($reload=true);
}

function set_lan($ip_addr, $netmask)
{
	exec("uci set network.lan.ipaddr=".$ip_addr);
	exec("uci set network.lan.netmask=".$netmask);
	exec("uci commit network");

	$start = exec("uci get dhcp.lan.start");
	$limit = exec("uci get dhcp.lan.limit");

	$start_ip = ip2long($ip_addr) + $start;

	exec("uci set dhcp.lan.start_ip='".long2ip($start_ip)."'");
	exec("uci set dhcp.lan.end_ip='".long2ip($start_ip+$limit)."'");
	exec("uci commit dhcp");

	$connect_status = exec("uci get anexgate.connect.status");
	if ($connect_status == "1") {
		$connect_port = exec("uci get anexgate.config.connect_port");
		$connect_domain = exec("uci get anexgate.config.connect_domain");
		exec("uci set autossh.@autossh[0].ssh='-i /etc/dropbear/connect.pkey -f -y -K 30 -N -T -R ".$connect_port.":".$ip_addr.":".$listen_items[1]." ace_connect@".$connect_domain."'");
		exec("uci commit autossh");
	}

	if ($connect_status == "1") {
		exec("/etc/init.d/autossh restart");
	}
	exec("/etc/init.d/uhttpd reload");

	set_config($reload=true);
}

function set_sim($wwan_interface) {
	$CURRENT_EPOCH = exec("/bin/date +%s");
	if($wwan_interface == "wwan0") {
		exec("echo 0 > /sys/class/gpio/sim-switch/value");

		exec("echo 0 > /sys/class/gpio/pcie-power-toggle/value");
		exec("sleep 1");
		exec("echo 1 > /sys/class/gpio/pcie-power-toggle/value");

		exec('uci set network.wwan0.apn="$(uci get simswitch.wwan0.apn)"');
		exec('uci set network.wwan0.pin="$(uci get simswitch.wwan0.pin)"');
		exec('uci set network.wwan0.username="$(uci get simswitch.wwan0.username)"');
		exec('uci set network.wwan0.pdptype="$(uci get simswitch.wwan0.pdptype)"');
		exec('uci set network.wwan0.password="$(uci get simswitch.wwan0.password)"');
		exec('uci set network.wwan0.metric="$(uci get simswitch.wwan0.metric)"');
		exec('uci set network.wwan0.mtu="$(uci get simswitch.wwan0.mtu)"');
		exec('uci set network.wwan0.desc="$(uci get simswitch.wwan0.desc)"');
		exec('uci set simswitch.wwan0.active="1"');
		exec('uci set simswitch.wwan1.active="0"');
		exec("uci set simswitch.globals.last_switch_ts=".$CURRENT_EPOCH);
		exec('uci commit');
		exec('sync');
		exec('echo 1 > /sys/class/leds/sim1/brightness');
		exec('echo 0 > /sys/class/leds/sim2/brightness');
	} else if ($wwan_interface == "wwan1") {
		exec("echo 1 > /sys/class/gpio/sim-switch/value");

		exec("echo 0 > /sys/class/gpio/pcie-power-toggle/value");
		exec("sleep 1");
		exec("echo 1 > /sys/class/gpio/pcie-power-toggle/value");

		exec('uci set network.wwan0.apn="$(uci get simswitch.wwan1.apn)"');
		exec('uci set network.wwan0.pin="$(uci get simswitch.wwan1.pin)"');
		exec('uci set network.wwan0.username="$(uci get simswitch.wwan1.username)"');
		exec('uci set network.wwan0.pdptype="$(uci get simswitch.wwan1.pdptype)"');
		exec('uci set network.wwan0.password="$(uci get simswitch.wwan1.password)"');
		exec('uci set network.wwan0.metric="$(uci get simswitch.wwan1.metric)"');
		exec('uci set network.wwan0.mtu="$(uci get simswitch.wwan1.mtu)"');
		exec('uci set network.wwan0.desc="$(uci get simswitch.wwan1.desc)"');
		exec('uci set simswitch.wwan1.active="1"');
		exec('uci set simswitch.wwan0.active="0"');
		exec("uci set simswitch.globals.last_switch_ts=".$CURRENT_EPOCH);
		exec('uci commit');
		exec('sync');
		exec('echo 0 > /sys/class/leds/sim1/brightness');
		exec('echo 1 > /sys/class/leds/sim2/brightness');
	} 
	exec('sleep 20');
        exec('/etc/init.d/network restart');
        exec('sleep 120');
        exec('/etc/init.d/firewall restart');
        exec('sleep 5');
        exec('/etc/init.d/mwan3 restart');
        exec('sleep 10');
        exec('/etc/init.d/openvpn restart');
        exec('ipsec restart');
}

function set_simswitch_globals($ping_ips, $pingv6_ips, $min_resp, $min_ping_resp, $resp_timeout, $check_interval, $failure_latency, $check_quality, $check_ping, $check_signal, $switch_above_signal, $accept_latency, $switch_prevent_interval, $check_quota, $sim1_quota, $sim2_quota, $reboot_on_down, $reboot_on_down_duration) {
	exec('uci delete simswitch.globals');
	exec("uci set simswitch.globals=globals");
	exec("uci set simswitch.globals.failure_loss='20'");
	exec("uci set simswitch.globals.recovery_loss='5'");
	exec("uci set simswitch.globals.down='2'");
	exec("uci set simswitch.globals.up='8'");
	exec("uci set simswitch.globals.size='56'");
	exec("uci set simswitch.globals.max_ttl='60'");
	exec("uci set simswitch.globals.keep_failure_interval='0'");
	exec("uci set simswitch.globals.failure_interval='2'");
	exec("uci set simswitch.globals.recovery_interval='2'");
	
	exec("uci set simswitch.globals.switch_prevent_interval='".$switch_prevent_interval."'");
	exec("uci set simswitch.globals.interval='".$check_interval."'");
	exec("uci set simswitch.globals.reliability='".$min_resp."'");
	exec("uci set simswitch.globals.count='".$min_ping_resp."'");
	exec("uci set simswitch.globals.timeout='".$resp_timeout."'");
	if ($check_quality == "on") {
		exec("uci set simswitch.globals.check_quality='1'");
	}
	
	exec("uci set simswitch.globals.failure_latency='".$failure_latency."'");
	exec("uci set simswitch.globals.recovery_latency='".$accept_latency."'");
	exec("uci set simswitch.globals.timeout='".$resp_timeout."'");

	if ($reboot_on_down == "on") {
		exec("uci set simswitch.globals.reboot_on_down='1'");
		exec("uci set simswitch.globals.reboot_on_down_duration='".$reboot_on_down_duration."'");
	}

	if ($check_ping == "on") {
		exec("uci set simswitch.globals.check_ping='1'");
	}
	foreach ($ping_ips as $ping_ip) {
		if ($ping_ip) {
		  exec("uci add_list simswitch.globals.track_ip='".$ping_ip."'");
		}
	}
	foreach ($pingv6_ips as $pingv6_ip) {
		if ($pingv6_ip) {
		  exec("uci add_list simswitch.globals.trackv6_ip='".$pingv6_ip."'");
		}
	}

	if ($check_signal == "on") {
		exec("uci set simswitch.globals.check_signal='1'");
	}

	if ($check_quota == "on") {
		exec("uci set simswitch.globals.check_quota='1'");
	}
	exec("uci set simswitch.globals.sim1_quota='".$sim1_quota."'");
	exec("uci set simswitch.globals.sim2_quota='".$sim2_quota."'");

	exec("uci set simswitch.globals.switch_above_signal='".$switch_above_signal."'");

	set_config_sim_switching();
}


function disable_switching_wwan($wwan_interface, $lock_pin)
{
	clear_sim_switch_module($wwan_interface);
	exec("uci set simswitch.".$wwan_interface."=module");
	exec("uci set simswitch.".$wwan_interface.".enabled='0'");
	exec("uci set simswitch.".$wwan_interface.".lock_pin='".$lock_pin."'");
	exec("uci set simswitch.".$wwan_interface.".active='0'");
	
	if ($wwan_interface == "wwan0") {
		$other_sim_status = exec("uci get simswitch.wwan1.enabled");
		if ($other_sim_status == "1") {
			exec("uci set network.wwan0.device=$(uci get simswitch.wwan1.device)");
			exec("uci set network.wwan0.apn=$(uci get simswitch.wwan1.apn)");
			exec("uci set network.wwan0.pin=$(uci get simswitch.wwan1.pin)");
			exec("uci set network.wwan0.username=$(uci get simswitch.wwan1.username)");
			exec("uci set network.wwan0.password=$(uci get simswitch.wwan1.password)");
			exec("uci set network.wwan0.metric=$(uci get simswitch.wwan1.metric)");
			exec("uci set network.wwan0.desc=$(uci get simswitch.wwan1.desc)");
			exec("uci set network.wwan0.device='/dev/ttyUSB2'");
			exec("uci set simswitch.wwan1.active='1'");
		} else {
			disable_wwan("wwan0", $lock_pin);
		}
	} else if ($wwan_interface == "wwan1") {
		$other_sim_status = exec("uci get simswitch.wwan0.enabled");
		if ($other_sim_status == "1") {
			exec("uci set network.wwan0.device=$(uci get simswitch.wwan0.device)");
			exec("uci set network.wwan0.apn=$(uci get simswitch.wwan0.apn)");
			exec("uci set network.wwan0.pin=$(uci get simswitch.wwan0.pin)");
			exec("uci set network.wwan0.username=$(uci get simswitch.wwan0.username)");
			exec("uci set network.wwan0.password=$(uci get simswitch.wwan0.password)");
			exec("uci set network.wwan0.metric=$(uci get simswitch.wwan0.metric)");
			exec("uci set network.wwan0.desc=$(uci get simswitch.wwan0.desc)");
			exec("uci set network.wwan0.device='/dev/ttyUSB2'");
			exec("uci set simswitch.wwan0.active='1'");
		} else {
			disable_wwan("wwan0", $lock_pin);
		}
	}
	set_config_sim_switching();
}

function set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $pdp_type, $wwan_desc, $fw_zone, $mtu) {
	clear_sim_switch_module($wwan_interface);
	exec("uci set simswitch.".$wwan_interface."=module");

	set_modem_mode("/dev/ttyUSB1", $nw_mode);

	if ($wwan_interface == "wwan0") {
		clear_wan($wwan_interface);
		exec("uci set network.wwan0=interface");
		exec("uci set network.wwan0.enabled='1'");
		exec("uci set network.wwan0.proto='3g'");
		exec("uci set network.wwan0.mode='LTE'");
		exec("uci set network.wwan0.service='LTE'");
		exec("uci set network.wwan0.pdptype='".$pdp_type."'");
		exec("uci set network.wwan0.keepalive='10'");
		exec("uci set network.wwan0.device=".$wwan_device);
		exec("uci set network.wwan0.apn=".$apn);
		exec("uci set network.wwan0.pin=".$pin);
		exec("uci set network.wwan0.username=".$username);
		exec("uci set network.wwan0.password=".$password);
		if ($wwan_desc) {
			exec("uci set network.wwan0.desc='".$wwan_desc."'");
		} else {
			exec("uci delete network.wwan0.desc");
		}
		if ($fw_zone) {
			exec("uci set network.wwan0.fw_zone='".$fw_zone."'");
			$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
			for ($zone = 0; $zone < $zone_num; $zone++){
				exec("uci del_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
				exec("uci commit firewall");
			}
			for ($zone = 0; $zone < $zone_num; $zone++){
				$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
				if ($firewall_zone == $fw_zone) {
					exec("uci add_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
					exec("uci commit firewall");
				}
			}
		} else {
			exec("uci delete network.".$wwan_interface.".fw_zone");
		}

	} else if ($wwan_interface == "wwan1") {
		$wwan0_status = exec("uci get simswitch.wwan0.enabled");
		if ($wwan0_status != "1") {
			clear_wan($wwan_interface);
			exec("uci set network.wwan0=interface");
			exec("uci set network.wwan0.enabled='1'");
			exec("uci set network.wwan0.proto='3g'");
			exec("uci set network.wwan0.mode='LTE'");
			exec("uci set network.wwan0.service='LTE'");
			exec("uci set network.wwan0.pdptype='".$pdp_type."'");
			exec("uci set network.wwan0.keepalive='10'");
			exec("uci set network.wwan0.device=".$wwan_device);
			exec("uci set network.wwan0.apn=".$apn);
			exec("uci set network.wwan0.pin=".$pin);
			exec("uci set network.wwan0.username=".$username);
			exec("uci set network.wwan0.password=".$password);
			if ($wwan_desc) {
				exec("uci set network.wwan0.desc='".$wwan_desc."'");
			} else {
				exec("uci delete network.wwan0.desc");
			}
			if ($fw_zone) {
				exec("uci set network.wwan0.fw_zone='".$fw_zone."'");
				$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
				for ($zone = 0; $zone < $zone_num; $zone++){
					exec("uci del_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
					exec("uci commit firewall");
				}
				for ($zone = 0; $zone < $zone_num; $zone++){
					$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
					if ($firewall_zone == $fw_zone) {
						exec("uci add_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
						exec("uci commit firewall");
					}
				}
			} else {
				exec("uci delete network.".$wwan_interface.".fw_zone");
			}
		}
	 	else {
			exec("uci set simswitch.".$wwan_interface.".active='0'");
		}
	}
	exec("uci set simswitch.".$wwan_interface.".active='1'");

	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.".$wwan_interface.".peerdns");
		exec("uci set network.".$wwan_interface.".peerdns='1'");
	} else {
		exec("uci set network.".$wwan_interface.".peerdns='0'");
	}
	
	if ($mtu) {
		exec("uci set network.".$wwan_interface.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$wwan_interface.".mtu");
	}

	if ($metric) {
		exec("uci set network.".$wwan_interface.".metric='".$metric."'");
	} else {
		exec("uci delete network.".$wwan_interface.".metric");
	}

	exec("uci set simswitch.".$wwan_interface.".enabled='1'");
	exec("uci set simswitch.".$wwan_interface.".nw_mode=".$nw_mode);
	exec("uci set simswitch.".$wwan_interface.".pdptype=".$pdp_type);
	exec("uci set simswitch.".$wwan_interface.".apn=".$apn);
	exec("uci set simswitch.".$wwan_interface.".pin=".$pin);
	exec("uci set simswitch.".$wwan_interface.".lock_pin='".$lock_pin."'");
	exec("uci set simswitch.".$wwan_interface.".username=".$username);
	exec("uci set simswitch.".$wwan_interface.".password=".$password);
	if ($wwan_desc) {
		exec("uci set simswitch.".$wwan_interface.".desc='".$wwan_desc."'");
	} else {
		exec("uci delete simswitch.".$wwan_interface.".desc");
	}
	if ($fw_zone) {
		exec("uci set simswitch.".$wwan_interface.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete simswitch.".$wwan_interface.".fw_zone");
	}

	if ($mtu) {
		exec("uci set simswitch.".$wwan_interface.".mtu=".$mtu);
	} else {
		exec("uci delete simswitch.".$wwan_interface.".mtu");
	}

	if ($metric) {
		exec("uci set simswitch.".$wwan_interface.".metric=".$metric);
	} else {
		exec("uci delete simswitch.".$wwan_interface.".metric");
	}
	exec("uci commit");
	exec("sync");
	set_config_sim_switching();

	set_config($reload=true);
}

//wwan configuration
function set_wwan($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $pdp_type, $track, $wwan_desc, $fw_zone, $mtu)
{
	clear_wan($wwan_interface);

	set_modem_mode($wwan_device, $nw_mode);

	exec("uci set network.".$wwan_interface."=interface");
	exec("uci set network.".$wwan_interface.".enabled='1'");
	exec("uci set network.".$wwan_interface.".proto='3g'");
	exec("uci set network.".$wwan_interface.".mode='LTE'");
	exec("uci set network.".$wwan_interface.".service='LTE'");
	exec("uci set network.".$wwan_interface.".keepalive='10'");
	exec("uci set network.".$wwan_interface.".pdptype='".$pdp_type."'");
	exec("uci set network.".$wwan_interface.".nw_mode='".$nw_mode."'");
	exec("uci set network.".$wwan_interface.".lock_pin='".$lock_pin."'");
	exec("uci set network.".$wwan_interface.".device='".$wwan_device."'");
	exec("uci set network.".$wwan_interface.".apn='".$apn."'");
	exec("uci set network.".$wwan_interface.".pin='".$pin."'");
	exec("uci set network.".$wwan_interface.".username='".$username."'");
	exec("uci set network.".$wwan_interface.".password='".$password."'");
	exec("uci set network.".$wwan_interface.".track='".$track."'");
	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.".$wwan_interface.".peerdns");
		exec("uci set network.".$wwan_interface.".peerdns='1'");
	} else {
			exec("uci set network.".$wwan_interface.".peerdns='0'");
	}
	if ($wwan_desc) {
		exec("uci set network.".$wwan_interface.".desc='".$wwan_desc."'");
	} else {
		exec("uci delete network.".$wwan_interface.".desc");
	}
	if ($fw_zone) {
		exec("uci set network.".$wwan_interface.".fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='".$wwan_interface."'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.".$wwan_interface.".fw_zone");
	}
	if ($mtu) {
		exec("uci set network.".$wwan_interface.".mtu='".$mtu."'");
	} else {
		exec("uci delete network.".$wwan_interface.".mtu");
	}
	if ($metric) {
		exec("uci set network.".$wwan_interface.".metric='".$metric."'");
	} else {
		exec("uci delete network.".$wwan_interface.".metric");
	}
	exec("uci commit network");

	set_config($reload=true);
}

function disable_wan($wan_interface)
{
	clear_wan($wan_interface);
	exec("uci set network.".$wan_interface."=interface");
	exec("uci set network.".$wan_interface.".enabled='0'");
	exec("uci set network.".$wan_interface.".disabled='1'");
	exec('uci set network.'.$wan_interface.'.ifname="$(uci get network.'.$wan_interface.'_dev.name)"');
	set_config($reload=true);
}

function disable_wwan($wwan_interface, $lock_pin)
{
	clear_wan($wwan_interface);
	exec("uci set network.".$wwan_interface."=interface");
	exec("uci set network.".$wwan_interface.".enabled='0'");
	exec("uci set network.".$wwan_interface.".disabled='1'");
	exec("uci set network.".$wwan_interface.".lock_pin='".$lock_pin."'");
	set_config($reload=true);
}

function set_3g($user, $password, $device, $dial_no, $apn, $pin, $mtu, $metric, $peerdns, $ggg_desc, $fw_zone)
{
	exec("uci delete network.3g");
	exec("uci delete network.usb0");

	exec("uci set network.3g=interface");
	exec("uci set network.3g.proto='3g'");
	exec("uci set network.3g.enabled='1'");
	exec("uci set network.3g.device='".$device."'");
	exec("uci set network.3g.username='".$user."'");
	exec("uci set network.3g.password='".$password."'");
	exec("uci set network.3g.dialnumber='".$dial_no."'");
	exec("uci set network.3g.apn='".$apn."'");
	exec("uci set network.3g.pincode='".$pin."'");
	exec("uci set network.3g.mtu='".$mtu."'");
	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.3g.peerdns");
		exec("uci set network.3g.peerdns='1'");
    } else {
        exec("uci set network.3g.peerdns='0'");
    }
	if ($fw_zone) {
		exec("uci set network.3g.fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='3g'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='3g'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.3g.fw_zone");
	}
	if ($ggg_desc) {
		exec("uci set network.3g.desc='".$ggg_desc."'");
	} else {
		exec("uci delete network.3g.desc");
	}

	if ($metric) {
		exec("uci set network.3g.metric='".$metric."'");
	} else {
		exec("uci delete network.3g.metric");
	}
	set_config($reload=true);
}

function disable_3g4g()
{
	exec("uci delete network.3g");
	exec("uci delete network.usb0");
	set_config($reload=true);
}

function set_tethermode($tether_interface, $metric, $peerdns, $tether_desc, $mtu, $fw_zone)
{
	exec("uci delete network.3g");
	exec("uci delete network.usb0");

	exec("uci set network.usb0=interface");
	exec("uci set network.usb0.proto='dhcp'");
	exec("uci set network.usb0.enabled='1'");
	exec("uci set network.usb0.ifname='".$tether_interface."'");
	if ($peerdns && $peerdns == "on") {
		exec("uci delete network.usb0.peerdns");
		exec("uci set network.usb0.peerdns='1'");
	} else {
		exec("uci set network.usb0.peerdns='0'");
	}

	if ($fw_zone) {
		exec("uci set network.usb0.fw_zone='".$fw_zone."'");
		$zone_num = exec("uci show firewall | grep '=zone' | wc -l");
		for ($zone = 0; $zone < $zone_num; $zone++){
			exec("uci del_list firewall.@zone[".$zone."].network='usb0'");
			exec("uci commit firewall");
		}
		for ($zone = 0; $zone < $zone_num; $zone++){
			$firewall_zone = exec("uci get firewall.@zone[".$zone."].name");
			if ($firewall_zone == $fw_zone) {
				exec("uci add_list firewall.@zone[".$zone."].network='usb0'");
				exec("uci commit firewall");
			}
		}
	} else {
		exec("uci delete network.usb0.fw_zone");
	}

	if ($tether_desc) {
		exec("uci set network.usb0.desc='".$tether_desc."'");
	} else {
		exec("uci delete network.usb0.desc");
	}

	if ($mtu) {
		exec("uci set network.usb0.mtu='".$mtu."'");
	} else {
		exec("uci delete network.usb0.mtu");
	}

	if ($metric) {
		exec("uci set network.usb0.metric='".$metric."'");
	} else {
		exec("uci delete network.usb0.metric");
	}
	set_config($reload=true);
}

function set_nwmon_globals($ping_ips, $interface, $proto_family, $count, $min_resp, $resp_timeout, $check_interval, $reboot_on_down, $reboot_on_down_duration, $action)
{
	exec('uci delete nwmon.globals');
	exec("uci set nwmon.globals=globals");
	exec("uci set nwmon.globals.size='56'");
	exec("uci set nwmon.globals.max_ttl='60'");

	exec("uci set nwmon.globals.interface='".$interface."'");
	exec("uci set nwmon.globals.interval='".$check_interval."'");
	exec("uci set nwmon.globals.family='".$proto_family."'");
	exec("uci set nwmon.globals.reliability='".$min_resp."'");
	exec("uci set nwmon.globals.count='".$count."'");
	exec("uci set nwmon.globals.timeout='".$resp_timeout."'");
	exec("uci set nwmon.globals.action='".$action."'");

	foreach ($ping_ips as $ping_ip) {
		if ($ping_ip) {
		  exec("uci add_list nwmon.globals.track_ip='".$ping_ip."'");
		}
	}

	if ($reboot_on_down == "on") {
		exec("uci set nwmon.globals.reboot_on_down='1'");
		exec("uci set nwmon.globals.reboot_on_down_duration='".$reboot_on_down_duration."'");
	}

	set_config_nwmon();
}

function set_data_monitoring($status, $interface, $gb_quota, $quota_start_date, $quota_duration_days, $disable_interface, $sdwan_disable)
{
	exec("uci delete datamon.".$interface);
	exec("uci commit datamon");

	exec("uci set datamon.".$interface."=interface");
	exec("uci set datamon.".$interface.".status='".$status."'");
	exec("uci set datamon.".$interface.".active='1'");
	exec("uci set datamon.".$interface.".uptime='0'");
	exec("uci set datamon.".$interface.".last_known_bytes_session='0'");
	exec("uci set datamon.".$interface.".gb_quota='".$gb_quota."'");
	exec("uci set datamon.".$interface.".quota_start_date='".$quota_start_date."'");
	exec("uci set datamon.".$interface.".quota_duration_days='".$quota_duration_days."'");
	exec("uci set datamon.".$interface.".disable_interface='".$disable_interface."'");
	exec("uci set datamon.".$interface.".sdwan_disable='".$sdwan_disable."'");
	exec("uci commit datamon");
	exec("sync");
	exec("/etc/init.d/datamon restart");
}

function delete_data_monitoring_interface($interface)
{
	exec("uci delete datamon.".$interface);
	exec("uci commit datamon");
	exec("sync");
	exec("/etc/init.d/datamon restart");
}

?>
