<?php

function logout_hotspot_user($ip_address)
{
  exec("/usr/sbin/chilli_query logout ip ".$ip_address);
}

function set_log_export_config($enabled, $log_server_ip, $log_server_port, $nat_log_protocol)
{
  exec("uci set hotspot.globals.enabled='".$enabled."'");
  exec("uci set hotspot.globals.nat_log_server='".$log_server_ip."'");
  exec("uci set hotspot.globals.nat_log_port='".$log_server_port."'");
  exec("uci set hotspot.globals.nat_log_protocol='".$nat_log_protocol."'");

  exec("uci commit hotspot");
  exec("/bin/sync");
  exec("/etc/init.d/natexport restart");
}


function set_hotspot_config($instance_no, $interface, $disabled, $radius_ip_primary, $radius_ip_secondary, $radius_auth_port, $radius_acct_port, $radius_secret, $mac_address, $hotspot_network, $redirect_url, $whitelisted_domains, $walled_garden, $external_dns, $dns1, $dns2, $dhcp_lease, $macallowed, $maxclients, $hotspot_gateway_ip, $hotspot_gateway_netmask, $start_ip, $ip_range, $domain, $uamuissl, $uamuiport, $defsessiontimeout, $defidletimeout, $mtu, $all_files)
{
  if ($instance_no == "-1") {
    exec("uci add chilli chilli");
    $instance_no = intval(exec("uci show chilli | grep '=chilli' | wc -l")) - 1;
  }

  move_uploaded_file($all_files['https_key']['tmp_name'], "/etc/chilli/hspt".$instance_no."_https_cert.key");
  move_uploaded_file($all_files['https_cert']['tmp_name'], "/etc/chilli/hspt".$instance_no."_https_cert.crt.pem");
  move_uploaded_file($all_files['https_ca_cert']['tmp_name'], "/etc/chilli/hspt".$instance_no."_https_ca_cert.crt.pem");

  exec("uci set chilli.@chilli[".$instance_no."].disabled='".$disabled."'");
  exec("uci set chilli.@chilli[".$instance_no."].radiusserver1='".$radius_ip_primary."'");

  if ($radius_ip_secondary) {
    exec("uci set chilli.@chilli[".$instance_no."].radiusserver2='".$radius_ip_secondary."'");
  } else {
    exec("uci delete chilli.@chilli[".$instance_no."].radiusserver2");
  }

  if ($mtu) {
    exec("uci set chilli.@chilli[".$instance_no."].mtu='".$mtu."'");
  } else {
    exec("uci delete chilli.@chilli[".$instance_no."].mtu");
  }

  exec("uci delete network.tun".$instance_no);
  exec("uci set network.tun".$instance_no."=interface");
  exec("uci set network.tun".$instance_no.".proto='none'");
  exec("uci set network.tun".$instance_no.".ifname='tun".$instance_no."'");
  exec("uci set network.tun".$instance_no.".ipaddr='".$hotspot_gateway_ip."'");
  exec("uci set network.tun".$instance_no.".netmask='".$hotspot_gateway_netmask."'");
  exec("uci commit network");

  exec("uci set chilli.@chilli[".$instance_no."].uamlisten='".$hotspot_gateway_ip."'");

  exec("uci set chilli.@chilli[".$instance_no."].dhcpstart='".$start_ip."'");
  exec("uci set chilli.@chilli[".$instance_no."].dhcpend='".$ip_range."'");
  exec("uci set chilli.@chilli[".$instance_no."].tundev='tun".$instance_no."'");
  
  exec("uci set chilli.@chilli[".$instance_no."].radiusauthport='".$radius_auth_port."'");
  exec("uci set chilli.@chilli[".$instance_no."].radiusacctport='".$radius_acct_port."'");
  exec("uci set chilli.@chilli[".$instance_no."].radiussecret='".$radius_secret."'");
  exec("uci set chilli.@chilli[".$instance_no."].radiusnasid='".$mac_address."'");
  exec("uci set chilli.@chilli[".$instance_no."].net='".$hotspot_network."'");
  exec("uci set chilli.@chilli[".$instance_no."].network='".$interface."'");
  exec("uci set chilli.@chilli[".$instance_no."].uamserver='".$redirect_url."'");
  exec("uci set chilli.@chilli[".$instance_no."].uamdomain='".$whitelisted_domains."'");
  exec("uci set chilli.@chilli[".$instance_no."].uamallowed='".$walled_garden."'");
  exec("uci set chilli.@chilli[".$instance_no."].lease='".$dhcp_lease."'");

  exec("uci set chilli.@chilli[".$instance_no."].conup='/bin/chilli-conup.sh'");
  exec("uci set chilli.@chilli[".$instance_no."].condown='/bin/chilli-condown.sh'");
  exec("uci set chilli.@chilli[".$instance_no."].ipup='/bin/chilli-up.sh'");
  exec("uci set chilli.@chilli[".$instance_no."].ipdown='/bin/chilli-down.sh'");
  exec("uci set chilli.@chilli[".$instance_no."].debug='0'");
  exec("uci set chilli.@chilli[".$instance_no."].loglevel='7'");
  exec("uci set chilli.@chilli[".$instance_no."].scalewin='1'");
  exec("uci set chilli.@chilli[".$instance_no."].swapoctets='1'");
  exec("uci set chilli.@chilli[".$instance_no."].txqlen='2500'");
  exec("uci set chilli.@chilli[".$instance_no."].kname='hspt".$instance_no."'");

  if($uamuissl == "on") {
    exec("uci set chilli.@chilli[".$instance_no."].uamuissl='1'");
    exec("uci set chilli.@chilli[".$instance_no."].uamuiport='".$uamuiport."'");

    exec("uci set chilli.@chilli[".$instance_no."].sslkeyfile='/etc/chilli/hspt".$instance_no."_https_cert.key'");
    exec("uci set chilli.@chilli[".$instance_no."].sslcertfile='/etc/chilli/hspt".$instance_no."_https_cert.crt.pem'");
    exec("uci set chilli.@chilli[".$instance_no."].sslcafile='/etc/chilli/hspt".$instance_no."_https_ca_cert.crt.pem'");
    exec("uci set chilli.@chilli[".$instance_no."].wwwdir='/www/embed'");
    exec("uci set chilli.@chilli[".$instance_no."].domaindnslocal='1'");
  }

  if ($external_dns == "on") {
    exec("uci set chilli.@chilli[".$instance_no."].uamanydns='1'");
  } else {
    exec("uci set chilli.@chilli[".$instance_no."].uamanydns='0'");
  }

  if ($domain) {
    exec("uci set dhcp.hotspot".$instance_no."=domain");
    exec("uci set dhcp.hotspot".$instance_no.".name='".$domain."'");
    exec("uci set dhcp.hotspot".$instance_no.".ip='".$hotspot_gateway_ip."'");
    exec("uci set chilli.@chilli[".$instance_no."].domain='".$domain."'");
  } else {
    exec("uci set chilli.@chilli[".$instance_no."].domain='local'");
  }

  if($defsessiontimeout) {
    exec("uci set chilli.@chilli[".$instance_no."].defsessiontimeout='".$defsessiontimeout."'");
  } else {
    exec("uci set chilli.@chilli[".$instance_no."].defsessiontimeout='0'");
  }
  if($defidletimeout) {
    exec("uci set chilli.@chilli[".$instance_no."].defidletimeout='".$defidletimeout."'");
  } else {
    exec("uci set chilli.@chilli[".$instance_no."].defidletimeout='0'");
  }

  if ($maxclients) {
    exec("uci set chilli.@chilli[".$instance_no."].maxclients='".$maxclients."'");
  } else {
    exec("uci set chilli.@chilli[".$instance_no."].maxclients='10'");
  }

  if ($dns1) {
    exec("uci set chilli.@chilli[".$instance_no."].dns1='".$dns1."'");
  } else {
    exec("uci delete chilli.@chilli[".$instance_no."].dns1");
  }

  if ($dns2) {
    exec("uci set chilli.@chilli[".$instance_no."].dns2='".$dns2."'");
  } else {
    exec("uci delete chilli.@chilli[".$instance_no."].dns2");
  }

  if ($macallowed) {
    exec("uci set chilli.@chilli[".$instance_no."].macallowlocal='1'");
    exec("uci set chilli.@chilli[".$instance_no."].macallowed='".$macallowed."'");
  } else {
    exec("uci delete chilli.@chilli[".$instance_no."].macallowlocal");
    exec("uci delete chilli.@chilli[".$instance_no."].macallowed");
  }

  if (file_exists('/etc/chilli/static-leases')) {
    exec("uci set chilli.@chilli[".$instance_no."].ethers='/etc/chilli/static-leases'");
  }
  
  exec("uci commit chilli");
  exec("uci commit dhcp");
  exec("/bin/sync");

  exec("/etc/init.d/chilli restart");

  exec("/etc/init.d/network reload");
  exec("/etc/init.d/dnsmasq reload");
}


function delete_hotspot_instance($instance_no)
{
  exec("uci delete chilli.@chilli[".$instance_no."]");
  exec("uci delete network.tun".$instance_no);
  exec("uci delete dhcp.hotspot".$instance_no);

  unlink("/etc/chilli/hspt".$instance_no."_https_cert.key'");
  unlink("/etc/chilli/hspt".$instance_no."_https_cert.crt.pem'");
  unlink("/etc/chilli/hspt".$instance_no."_https_ca_cert.crt.pem'");

  exec("uci commit chilli");
  exec("uci commit dhcp");
  exec("/bin/sync");
  exec("/etc/init.d/network reload");
  exec("/etc/init.d/chilli restart");
  exec("/etc/init.d/dnsmasq reload");
}


function delete_anexspot_instance($instance_no)
{
  exec("uci delete opennds.@opennds[".$instance_no."]");
  exec("uci delete dhcp.@dnsmasq[0].ipset");
  exec("uci delete dhcp.@dnsmasq[0].address");
  exec("uci commit dhcp");
  exec("uci commit opennds");
  
  exec("/bin/sync");
  exec("/etc/init.d/opennds stop");
  exec('sleep 5');
  exec("/etc/init.d/opennds start");
  exec("sleep 5");
  exec("uci commit dhcp");
  exec("/etc/init.d/dnsmasq restart");
}


function add_mac_ip_bind($mac_address, $ip_address)
{
  $fp = fopen('/etc/chilli/static-leases', 'a');
  fwrite($fp, $mac_address." ".$ip_address."\n");  
  fclose($fp);  
}

function set_anexspot_config($status, $oper_mode, $gatewayinterface, $gateway_intf, $gatewayport, $gatewayname, $redirect_url, $fasremoteip, $fasremoteip6, $fasremotefqdn, $faspath, $walled_garden, $maxclients, $trusted_macs, $checkinterval, $preauthidletimeout, $authidletimeout, $instance_no, $walled_garden_ips, $block_tethering, $preempt_auth, $seamless_roaming) {

  if ($instance_no == "-1") {
    exec("uci add opennds opennds");
    $instance_no = intval(exec("uci show opennds | grep '=opennds' | wc -l")) - 1;
  }

  exec("uci set opennds.@opennds[".$instance_no."].oper_mode='".$oper_mode."'");
  exec("uci delete opennds.@opennds[".$instance_no."].fasremoteip6");
  exec("uci delete opennds.@opennds[".$instance_no."].fasremoteip");
  if($oper_mode == "ipv4") {
    exec("uci set opennds.@opennds[".$instance_no."].ip6='0'");
    exec("uci set opennds.@opennds[".$instance_no."].dualstack='0'");
    exec("uci set opennds.@opennds[".$instance_no."].fasremoteip='".$fasremoteip."'");
  } else if ($oper_mode == "ipv6") {
    exec("uci set opennds.@opennds[".$instance_no."].ip6='1'");
    exec("uci set opennds.@opennds[".$instance_no."].dualstack='0'");
    exec("uci set opennds.@opennds[".$instance_no."].fasremoteip='".$fasremoteip6."'");
    exec("uci set opennds.@opennds[".$instance_no."].fasremoteip6='".$fasremoteip6."'");
  } else if ($oper_mode == "ipv46") {
    exec("uci set opennds.@opennds[".$instance_no."].ip6='0'");
    exec("uci set opennds.@opennds[".$instance_no."].dualstack='1'");
    exec("uci set opennds.@opennds[".$instance_no."].fasremoteip='".$fasremoteip."'");
    exec("uci set opennds.@opennds[".$instance_no."].fasremoteip6='".$fasremoteip6."'");
  }

  exec("uci set opennds.@opennds[".$instance_no."].enabled='".$status."'");
  exec("uci set opennds.@opennds[".$instance_no."].gateway_intf='".$gateway_intf."'");
  exec("uci set opennds.@opennds[".$instance_no."].gatewayinterface='".$gatewayinterface."'");
  exec("uci set opennds.@opennds[".$instance_no."].gatewayport='".$gatewayport."'");
  exec("uci set opennds.@opennds[".$instance_no."].gatewayname='".$gatewayname."'");
  exec("uci set opennds.@opennds[".$instance_no."].fasremotefqdn='".$fasremotefqdn."'");
  exec("uci set opennds.@opennds[".$instance_no."].faspath='".$faspath."'");
  exec("uci set opennds.@opennds[".$instance_no."].maxclients='".$maxclients."'");
  exec("uci set opennds.@opennds[".$instance_no."].trustedmac='".$trustedmac."'");
  exec("uci set opennds.@opennds[".$instance_no."].checkinterval='".$checkinterval."'");
  exec("uci set opennds.@opennds[".$instance_no."].preauthidletimeout='".$preauthidletimeout."'");
  exec("uci set opennds.@opennds[".$instance_no."].authidletimeout='".$authidletimeout."'");
  exec("uci set opennds.@opennds[".$instance_no."].gatewayfqdn='gateway".$instance_no.".anexspot.com'");
  exec("uci set opennds.@opennds[".$instance_no."].gatewayport='206".$instance_no."'");
  exec("uci set opennds.@opennds[".$instance_no."].ndsctlsocket='/tmp/sock".$instance_no.".sock'");
  exec("uci set opennds.@opennds[".$instance_no."].debuglevel='0'");
  exec("uci set opennds.@opennds[".$instance_no."].fwhook_enabled='1'");
  exec("uci set opennds.@opennds[".$instance_no."].login_option_enabled='0'");
  exec("uci set opennds.@opennds[".$instance_no."].enable_serial_number_suffix='0'");
  exec("uci set opennds.@opennds[".$instance_no."].upload_bucket_ratio='0'");
  exec("uci set opennds.@opennds[".$instance_no."].download_bucket_ratio='0'");
  exec("uci set opennds.@opennds[".$instance_no."].download_unrestricted_bursting='1'");
  exec("uci set opennds.@opennds[".$instance_no."].upload_unrestricted_bursting='1'");
  exec("uci set opennds.@opennds[".$instance_no."].binauth='/bin/binauth.sh'");
  exec("uci set opennds.@opennds[".$instance_no."].fasport='443'");
  exec("uci set opennds.@opennds[".$instance_no."].faskey='6c09e8a3c768f77e01c9e1362802f55e'");
  exec("uci set opennds.@opennds[".$instance_no."].fas_secure_enabled='3'");
  exec("uci set opennds.@opennds[".$instance_no."].sessiontimeout='0'");

  exec("uci delete opennds.@opennds[".$instance_no."].users_to_router");

  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow tcp port 53'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow udp port 53'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow udp port 67'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow tcp port 2298'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow tcp port 80'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow udp port 547'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow udp port 546'");
  exec("uci add_list opennds.@opennds[".$instance_no."].users_to_router='allow tcp port 5353'");

  exec("uci delete opennds.@opennds[".$instance_no."].walledgarden_fqdn_list");

  if ($block_tethering == "on") {
    exec("uci set opennds.@opennds[".$instance_no."].block_tethering='1'");
  } else {
    exec("uci set opennds.@opennds[".$instance_no."].block_tethering='0'");
  }

  if ($preempt_auth == "on") {
    exec("uci set opennds.@opennds[".$instance_no."].allow_preemptive_authentication='1'");
  } else {
    exec("uci set opennds.@opennds[".$instance_no."].allow_preemptive_authentication='0'");
  }

  if ($seamless_roaming == "on") {
    exec("uci set opennds.@opennds[".$instance_no."].seamless_roaming='1'");
  } else {
    exec("uci set opennds.@opennds[".$instance_no."].seamless_roaming='0'");
  }

  if($walled_garden) {
    foreach ($walled_garden as $walled_garden_domain) {
      if (trim($walled_garden_domain)) {
        exec("uci add_list opennds.@opennds[".$instance_no."].walledgarden_fqdn_list='".$walled_garden_domain."'");
      }
    }
  }

  exec("uci delete opennds.@opennds[".$instance_no."].preauthenticated_users");

  if($walled_garden_ips) {
    foreach ($walled_garden_ips as $walled_garden_ip) {
      if (trim($walled_garden_ip)) {
        exec("uci add_list opennds.@opennds[".$instance_no."].preauthenticated_users='allow to ".$walled_garden_ip."'");
      }
    }
  }

  exec("uci delete opennds.@opennds[".$instance_no."].trustedmac");

  exec("uci delete dhcp.@dnsmasq[0].ipset");
  exec("uci delete dhcp.@dnsmasq[0].address");
  exec("uci commit dhcp");

  if($trusted_macs) {
    foreach ($trusted_macs as $trusted_mac) {
      exec("uci add_list opennds.@opennds[".$instance_no."].trustedmac='".$trusted_mac."'");
    }
  }
  exec("uci commit opennds");
  exec("/bin/sync");

  exec("/etc/init.d/opennds stop");
  exec("sleep 5");
  exec("/etc/init.d/opennds start");
  exec("sleep 5");
  exec("uci commit dhcp");
  exec("/etc/init.d/dnsmasq restart");
}


function logout_anexspot_user($instance_no, $ip_address)
{
  exec("/usr/bin/ndsctl -s /tmp/sock".$instance_no.".sock deauth ".$ip_address);
}

?>

