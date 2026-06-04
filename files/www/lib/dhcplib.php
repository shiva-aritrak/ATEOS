<?php

function delete_dhcp_server($interface)
{
	exec("uci delete dhcp.".$interface);
	exec("uci del_list dhcp.@dnsmasq[0].interface='".$interface."'");
	set_config();
}

function get_dhcp_start_limit($interface, $start_ip, $end_ip)
{
	$lan_ip = exec("uci get network.".$interface.".ipaddr");
	$parts = explode('.', $lan_ip);
	$parts[3] = 1;
	$new_ip = implode('.', $parts);
        $lan_ip_num = ip2long($new_ip);
        $startip_num = ip2long($start_ip);
        $endip_num = ip2long($end_ip);

        $start = $startip_num - $lan_ip_num + 1;
        $range = $endip_num - $startip_num;
	return [$start, $range];
}
 
function set_dhcp_server($interface, $status, $start_ip, $end_ip, $start, $range, $leasedur, $dns_servers, $force, $strict_dhcp, $dhcpv6, $ra, $ra_management, $ndp, $relay_master, $dhcpv4_mode, $ra_default, $adv_hoplimit, $ra_route_pref, $adv_mtu, $adv_lifetime, $ra_min_interval, $ra_max_interval, $adv_pref_lifetime, $ra_dns, $router, $add_pool1, $add_pool2, $add_pool3)
{
	delete_dhcp_server($interface);
	exec("uci set dhcp.".$interface."=dhcp");
	exec("uci set dhcp.".$interface.".interface='".$interface."'");
	exec("uci set dhcp.".$interface.".ignore='".$status."'");
	exec("uci set dhcp.".$interface.".start_ip=".$start_ip);
	exec("uci set dhcp.".$interface.".end_ip=".$end_ip);
	$start_limit = get_dhcp_start_limit($interface, $start_ip, $end_ip);
    exec("uci set dhcp.".$interface.".start='".$start_limit[0]."'");
    exec("uci set dhcp.".$interface.".limit='".$start_limit[1]."'");
	exec("uci set dhcp.".$interface.".leasetime='".$leasedur."h'");
	exec("uci set dhcp.".$interface.".dhcpv6='".$dhcpv6."'");
	exec("uci set dhcp.".$interface.".ra='".$ra."'");
	exec("uci set dhcp.".$interface.".ndp='".$ndp."'");
	exec("uci set dhcp.".$interface.".master='".$relay_master."'");
	exec("uci set dhcp.".$interface.".ra_default='".$ra_default."'");
	exec("uci set dhcp.".$interface.".interface='".$interface."'");
	if($ra_dns == "on") {
		exec("uci set dhcp.".$interface.".ra_dns='1'");
	} else {
		exec("uci set dhcp.".$interface.".ra_dns='0'");
	}
	if ($ra_default == "1") {
		exec("uci set dhcp.".$interface.".ra_useleasetime='1'");
	}
	if($adv_hoplimit != "") {
		exec("uci set dhcp.".$interface.".ra_hoplimit='".$adv_hoplimit."'");
	} else {
		exec("uci delete dhcp.".$interface.".ra_hoplimit");
	}
	if($ra_route_pref != "") {
		if($ra_route_pref == "disabled") {
			exec("uci delete dhcp.".$interface.".ra_preference");
		} else {
			exec("uci set dhcp.".$interface.".ra_preference='".$ra_route_pref."'");
		}
	}
	if($adv_mtu != "") {
		exec("uci set dhcp.".$interface.".ra_mtu='".$adv_mtu."'");
	} else {
		exec("uci delete dhcp.".$interface.".ra_mtu");
	}
	if($adv_lifetime != "") {
		exec("uci set dhcp.".$interface.".ra_lifetime='".$adv_lifetime."'");
	} else {
		exec("uci delete dhcp.".$interface.".ra_lifetime");
	}
	if($ra_min_interval != "") {
		exec("uci set dhcp.".$interface.".ra_mininterval='".$ra_min_interval."'");
	} else {
		exec("uci delete dhcp.".$interface.".ra_mininterval");
	}
	if($ra_max_interval != "") {
		exec("uci set dhcp.".$interface.".ra_maxinterval='".$ra_max_interval."'");
	} else {
		exec("uci delete dhcp.".$interface.".ra_maxinterval");
	}
	if($adv_pref_lifetime != "") {
		exec("uci set dhcp.".$interface.".preferred_lifetime='".$adv_pref_lifetime."h'");
	} else {
		exec("uci delete dhcp.".$interface.".preferred_lifetime");
	}
	if($ra_management == "0") {
		exec("uci set dhcp.".$interface.".ra_management='".$ra_management."'");
		exec("uci set dhcp.".$interface.".ra_slaac='1'");
		exec("uci set dhcp.".$interface.".ra_flags='none'");
	}
	if($ra_management == "1") {
		exec("uci set dhcp.".$interface.".ra_management='".$ra_management."'");
		exec("uci set dhcp.".$interface.".ra_slaac='1'");
		exec("uci set dhcp.".$interface.".ra_flags='managed-config other-config'");
	}
	if($ra_management == "2") {
		exec("uci set dhcp.".$interface.".ra_management='".$ra_management."'");
		exec("uci set dhcp.".$interface.".ra_slaac='0'");
		exec("uci set dhcp.".$interface.".ra_flags='managed-config other-config'");
	}
	
	if($dhcpv4_mode == "on") {
		exec("uci set dhcp.".$interface.".dhcpv4='server'");
	} else {
		exec("uci set dhcp.".$interface.".dhcpv4='disabled'");
	}

	if($force == "on") {
		exec("uci set dhcp.".$interface.".force='1'");
	} else {
		exec("uci set dhcp.".$interface.".force='0'");
	}

	if($strict_dhcp == "on") {
		exec("uci set dhcp.".$interface.".dynamicdhcp='0'");
	} else {
		exec("uci set dhcp.".$interface.".dynamicdhcp='1'");
	}
	exec("uci del_list dhcp.".$interface.".dhcp_option");
	if($router != "") {
        	exec("uci add_list dhcp.".$interface.".dhcp_option='3,".$router."'");
	}
	if (array_filter($dns_servers)) {
		$servers = implode(",", array_filter($dns_servers));
		exec("uci add_list dhcp.".$interface.".dhcp_option='6,".$servers."'");
	}
	exec("uci set dhcp.@dnsmasq[-1].cachelocal='0'");
	exec("uci add_list dhcp.@dnsmasq[-1].interface='".$interface."'");
	exec("uci -q delete dhcp.".$interface."_p1");
	if($add_pool1) {
		exec("uci set dhcp.".$interface."_p1=dhcp");
		exec("uci set dhcp.".$interface."_p1.interface='".$interface."'");
		exec("uci set dhcp.".$interface."_p1.pool='".$add_pool1."'");
		$start_ip = exec("echo $add_pool1 | cut -d '-' -f 1");
		$end_ip = exec("echo $add_pool1 | cut -d '-' -f 2");
		$start_limit = get_dhcp_start_limit($interface, $start_ip, $end_ip);
		exec("uci set dhcp.".$interface."_p1.start='".$start_limit[0]."'");
		exec("uci set dhcp.".$interface."_p1.limit='".$start_limit[1]."'");	
	}
	exec("uci -q delete dhcp.".$interface."_p2");
        if($add_pool2) {
                exec("uci set dhcp.".$interface."_p2=dhcp");
                exec("uci set dhcp.".$interface."_p2.interface='".$interface."'");
                exec("uci set dhcp.".$interface."_p2.pool='".$add_pool2."'");
                $start_ip = exec("echo $add_pool2 | cut -d '-' -f 1");
                $end_ip = exec("echo $add_pool2 | cut -d '-' -f 2");
                $start_limit = get_dhcp_start_limit($interface, $start_ip, $end_ip);
                exec("uci set dhcp.".$interface."_p2.start='".$start_limit[0]."'");
                exec("uci set dhcp.".$interface."_p2.limit='".$start_limit[1]."'");
        }
	exec("uci -q delete dhcp.".$interface."_p3");
        if($add_pool3) {
                exec("uci set dhcp.".$interface."_p3=dhcp");
                exec("uci set dhcp.".$interface."_p3.interface='".$interface."'");
                exec("uci set dhcp.".$interface."_p3.pool='".$add_pool3."'");
                $start_ip = exec("echo $add_pool3 | cut -d '-' -f 1");
                $end_ip = exec("echo $add_pool3 | cut -d '-' -f 2");
                $start_limit = get_dhcp_start_limit($interface, $start_ip, $end_ip);
                exec("uci set dhcp.".$interface."_p3.start='".$start_limit[0]."'");
                exec("uci set dhcp.".$interface."_p3.limit='".$start_limit[1]."'");
        }
	set_config();
}


function set_dhcplease($host_no,$ip_addr,$mac_addr)
{
	if ($host_no == "-1") {
		exec("uci add dhcp host");
	}
	exec("uci set dhcp.@host[".$host_no."].mac=".$mac_addr);
	exec("uci set dhcp.@host[".$host_no."].ip=".$ip_addr);
	set_config();
}

function set_splitdns($host_no,$ip_addr,$dom_name)
{
	if ($host_no == "-1") {
                exec("uci add dhcp domain");
        }
        exec("uci set dhcp.@domain[".$host_no."].name=".$dom_name);
        exec("uci set dhcp.@domain[".$host_no."].ip=".$ip_addr);
        set_config();
}

function delete_dom($host_no, $last_host_no) {
        if ($host_no == $last_host_no) {
                exec("uci delete dhcp.@domain[".$host_no."]");
        } else {
                exec("uci delete dhcp.@domain[".$host_no."]");
                exec("uci set dhcp.@domain[".$host_no."] = uci get dhcp.@domain[".$last_host_no."]");
                exec("uci set dhcp.@domain[".$host_no."].mac=uci get dhcp.@domain[".$last_host_no."].name");
                exec("uci set dhcp.@domain[".$host_no."].ip=uci get dhcp.@domain[".$last_host_no."].ip");
                exec("uci delete dhcp.@domain[".$last_host_no."]");
        }
        set_config();
}

function delete_host($host_no, $last_host_no) {
	if ($host_no == $last_host_no) {
		exec("uci delete dhcp.@host[".$host_no."]");
	} else {
		exec("uci delete dhcp.@host[".$host_no."]");
		exec("uci set dhcp.@host[".$host_no."] = uci get dhcp.@host[".$last_host_no."]");
		exec("uci set dhcp.@host[".$host_no."].mac=uci get dhcp.@host[".$last_host_no."].mac");
		exec("uci set dhcp.@host[".$host_no."].ip=uci get dhcp.@host[".$last_host_no."].ip");
		exec("uci delete dhcp.@host[".$last_host_no."]");
	}
	set_config();

}

function set_config()
{
	exec('uci commit dhcp');
	exec("sync");
	exec("/etc/init.d/odhcpd restart");
	exec("/etc/init.d/dnsmasq restart");
}


function set_dns_servers($dns_servers)
{
	exec("uci delete dhcp.@dnsmasq[-1].server");
	foreach (array_filter($dns_servers) as $dns_server) {
		exec("uci add_list dhcp.@dnsmasq[-1].server='".$dns_server."'");
	}
	set_config();
}

function set_ad_forward_domains($ad_domain_names, $ad_domain_ips)
{
        $all_servers = explode(" ", exec("uci get dhcp.@dnsmasq[0].server"));
        $ad_servers = array();
        foreach($all_servers as $server) {
                if (strpos($server,"/") === 0) {
                        if (strpos($server, "_msdcs") !== false) {
                                array_push($ad_servers, $server);
                        }
                }
        }
        foreach($ad_servers as $ad_server) {
                exec("uci del_list dhcp.@dnsmasq[0].server='".$ad_server."'");
                $ad_domain = str_replace("_msdcs.", "", $ad_server);
                exec("uci del_list dhcp.@dnsmasq[0].server='".$ad_domain."'");
        }
        exec("uci commit dhcp");
        exec("sync");
        exec("uci set dhcp.@dnsmasq[0].rebind_protection='0'");
        exec("uci set dhcp.@dnsmasq[0].rebind_localhost='0'");
        $length = min(count($ad_domain_names), count($ad_domain_ips));
        if($length == 0) {
                exec("uci set dhcp.@dnsmasq[0].rebind_protection='1'");
                exec("uci set dhcp.@dnsmasq[0].rebind_localhost='1'");
                exec("uci commit dhcp");
        } else {
                exec("uci set dhcp.@dnsmasq[0].rebind_protection='0'");
                exec("uci set dhcp.@dnsmasq[0].rebind_localhost='0'");
        }
        for ($i = 0; $i < $length; $i++) {
                if($ad_domain_names[$i]) {
                        exec("uci add_list dhcp.@dnsmasq[0].server='/".$ad_domain_names[$i]."/".$ad_domain_ips[$i]."'");
                        exec("uci add_list dhcp.@dnsmasq[0].server='/_msdcs.".$ad_domain_names[$i]."/".$ad_domain_ips[$i]."'");
                }
        }
        set_config();
}

function get_lan_dhcp_servers($interface)
{
	$out = exec("uci get dhcp.".$interface.".dhcp_option | cut -d ' ' -f 2");
	$parts = explode(',', str_replace(' ', ',', $out));
	if($parts[0] == "6") {
		if($parts[2] == "") {
			$dns_ips = array_slice($parts, -1);
		} else {
			$dns_ips = array_slice($parts, -2);
		}
		return $dns_ips;
	}
	return;
}

function get_lan_dhcp_router_ip($interface)
{
	$out = exec("uci get dhcp.".$interface.".dhcp_option | cut -d ' ' -f 1");
        $parts = explode(',', str_replace(' ', ',', $out));
        if($parts[0] == "3") {
                return $parts[1];
        }
        return;
}

function set_dns_filter_ipset($mode, $domain_names)
{
	exec("uci delete dhcp.filter");
	exec("uci commit dhcp");
	exec("uci delete firewall.filter");
	$rule_no = exec("uci show firewall | grep '=rule' | wc -l");
	for ( $no = 0; $no < $rule_no; $no++) {
    	        $prev_rule = exec("uci get firewall.@rule[".$no."].ipset");
         	if ( $prev_rule == "filter") {
                 	exec("uci delete firewall.@rule[".$no."]");
                	break;
        	}
        }
	exec("uci commit firewall");
	if(is_array($domain_names) && count($domain_names) > 0) {
		exec("uci set dhcp.filter=ipset");
		exec("uci add_list dhcp.filter.name='filter'");
		exec("uci add_list dhcp.filter.name='filter6'");
		foreach (array_filter($domain_names) as $domain_name) {
			exec("uci add_list dhcp.filter.domain='".$domain_name."'");
		}
		exec("uci set firewall.filter=ipset");
		exec("uci set firewall.filter.name='filter'");
		exec("uci set firewall.filter.enabled='1'");
		exec("uci set firewall.filter.storage='hash'");
		exec("uci set firewall.filter.match='dest_net'");

		$rule_nw = exec("uci show firewall | grep '=rule' | wc -l");
		exec("uci add firewall rule");
		exec("uci set firewall.@rule[".$rule_nw."].ipset='filter'");
		exec("uci set firewall.@rule[".$rule_nw."].enabled='1'");
		exec("uci set firewall.@rule[".$rule_nw."].name='DNSFilterRule'");
		exec("uci set firewall.@rule[".$rule_nw."].src='lan'");
		exec("uci set firewall.@rule[".$rule_nw."].proto='all'");
		exec("uci set firewall.@rule[".$rule_nw."].dest='wan'");
		if ($mode == "whitelist") {
			exec("uci set firewall.@rule[".$rule_nw."].target='ACCEPT'");
			exec("uci set dhcp.filter.mode='whitelist'");
		} else {
			exec("uci set firewall.@rule[".$rule_nw."].target='DROP'");
			exec("uci set dhcp.filter.mode='blacklist'");
		}
	}
	exec("uci commit firewall");
	set_config();
	exec("/etc/init.d/firewall restart");
}

function set_dns_filter($mode, $domain_names)
{
	delete_dns_filter();
	if ($mode == "whitelist") {
		if (!in_array("anexgate.com", $domain_names) && !in_array("anexprivate.sspl.securens.in", $domain_names)) {
			exec("uci add_list dhcp.@dnsmasq[0].server='/anexgate.com/8.8.8.8'");
			exec("uci add_list dhcp.@dnsmasq[0].server='/anexprivate.sspl.securens.in/8.8.8.8'");
		}
		exec("uci add_list dhcp.@dnsmasq[0].address='/#/127.0.0.1'");
		foreach (array_filter($domain_names) as $domain_name) {
	    exec("uci add_list dhcp.@dnsmasq[0].server='/".$domain_name."/8.8.8.8'");
	  }
	} else if ($mode == "blacklist") {
		foreach (array_filter($domain_names) as $domain_name) {
	    exec("uci add_list dhcp.@dnsmasq[0].address='/".$domain_name."/127.0.0.1'");
	  }
	}
  set_config();
}


function delete_dns_filter()
{
  exec("uci delete dhcp.@dnsmasq[0].address");
  exec("uci delete dhcp.@dnsmasq[0].server");
  set_config();
}

?>
