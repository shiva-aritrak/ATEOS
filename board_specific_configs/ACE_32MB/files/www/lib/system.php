<?php

function get_dns_servers($anexdns_domain)
{
  try {
    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");
    $macaddr = exec("uci get anexgate.license.macaddr");

    if ($license_key == "No License Key") {
      return array(False, True);
    }

    exec("uci delete dhcp.@dnsmasq[-1].server");
    exec("uci delete dhcp.@dnsmasq[-1].addsubnet");
    exec("uci delete dhcp.@dnsmasq[-1].addmac");
    exec("uci delete dhcp.@dnsmasq[-1].addcpeid");
    exec("uci delete dhcp.@dnsmasq[-1].server");
    exec("uci delete anexgate.dns.success");
    exec("uci add_list dhcp.@dnsmasq[-1].server='8.8.8.8'");

    exec("uci commit dhcp");
    exec("uci commit anexgate");
    exec("sync");
    exec("/etc/init.d/dnsmasq restart");

    $ch = curl_init( "http://".$anexdns_domain."/api/ace/dns/init/" );

    $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr  ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "AnexDNS Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

    $result = curl_exec($ch);

    curl_close($ch);

    $json_response = json_decode($result, true);



    if ($json_response && $json_response["valid"] == "Yes") {
      exec("uci set anexgate.dns.success='1'");

      exec("uci delete dhcp.@dnsmasq[-1].server");
      foreach (array_filter($json_response["dns_servers"]) as $dns_server) {
        exec("uci add_list dhcp.@dnsmasq[-1].server='".$dns_server."'");
        exec("uci set anexgate.dns.server1='".$dns_server."'");
      }
      $serial = exec("uci get anexgate.license.serial");
      exec("uci set dhcp.@dnsmasq[-1].addsubnet='32'");
      exec("uci set dhcp.@dnsmasq[-1].addmac='1'");
      exec("uci set dhcp.@dnsmasq[-1].addcpeid='".$serial."'");
      exec("uci commit dhcp");
      exec("uci commit anexgate");
      exec("sync");
      exec("/etc/init.d/dnsmasq restart");
    }
  }
  catch(Exception $e) {

    exec("uci set anexgate.dns.success='0'");
    return False;
  }
}

function set_anexdns_config($status, $anexdns_domain)
{

  exec("uci set anexgate.dns.domain='".$anexdns_domain."'");
  exec("uci set anexgate.dns.status='".$status."'");

  if ($status == "1") {
    get_dns_servers($anexdns_domain);
  } else {
    exec("uci delete dhcp.@dnsmasq[-1].addsubnet");
    exec("uci delete dhcp.@dnsmasq[-1].addmac");
    exec("uci delete dhcp.@dnsmasq[-1].addcpeid");
    exec("uci delete dhcp.@dnsmasq[-1].server");
    exec("uci delete anexgate.dns.success");
    exec("uci add_list dhcp.@dnsmasq[-1].server='8.8.8.8'");
  }
  exec("uci commit dhcp");
  exec("uci commit anexgate");
  exec("sync");
  exec("/etc/init.d/dnsmasq restart");
}

function reinit_anexhub() {
  exec("ifdown hub0");
  exec("uci set anexhub.hub.registered='No'");
  exec("uci set anexhub.hub.cloud_config_id='false'");
  exec("uci set anexhub.hub.config_id='false'");
  exec("uci set anexhub.hub.override='No'");
  exec("uci set anexhub.hub.one_time_push='0'");
  exec("uci set anexgate.hub.one_time_push='0'");
  exec("uci delete anexgate.config.hub_domain");
  exec("uci delete anexgate.config.flow_id");
  exec("uci delete anexhub.hub.last_checked");
  exec("uci delete anexhub.hub.sync_status");
  exec("uci delete network.hub0");
  exec("uci delete network.wgserver");
  exec("uci commit anexgate");
  exec("uci commit anexhub");
  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
  sleep(5);
  exec("/etc/init.d/hub restart");
}

function set_connect_web_server_port() {
  try{
      $nms_intf = exec("uci -q get network.nms0");
      if ($nms_intf == "interface") {
        $license_key = exec("uci get anexgate.license.key");
        $serial = exec("uci get anexgate.license.serial");
        $macaddr = exec("uci get anexgate.license.macaddr");
        $anex_connect_domain = exec("uci get anexgate.connect.connect_domain");
        $listen_http = exec("uci get uhttpd.main.listen_http | cut -d ' ' -f 1");
        $listen_port = explode(":", $listen_http)[1];
        
        $ch = curl_init( "https://".$anex_connect_domain."/api/connect/set/web/port/" );
        $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr,"web_server_port" => $listen_port  ) );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_USERAGENT, "Anexhub Agent");
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

        $result = curl_exec($ch);

        curl_close($ch);

        $json_response = json_decode($result, true);
        if($json_response && $json_response["status"] == 1){
            return true;
        }else{
            return false;
        }
      }
  }
  catch(Exception $e) {
  exec("logger -t NMS Web port not synced to the NMS server.");
  return false;
  }
}

function set_hub_device_web_server_port(){
  try{
      $license_key = exec("uci get anexgate.license.key");
      $serial = exec("uci get anexgate.license.serial");
      $macaddr = exec("uci get anexgate.license.macaddr");
      $anexhub_domain = exec("uci get anexgate.config.hub_domain");
      $listen_http = exec("uci get uhttpd.main.listen_http | cut -d ' ' -f 1");
      $listen_port = explode(":", $listen_http)[1];
      $is_hub_registered = exec("uci get anexhub.hub.status");
      if ($is_hub_registered == "yes"){
          $ch = curl_init( "http://".$anexhub_domain."/device/api/set/web/port/" );
          $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr,"web_server_port" => $listen_port  ) );
          curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
          curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
          curl_setopt( $ch, CURLOPT_USERAGENT, "Anexhub Agent");
          curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
          curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
          curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

          $result = curl_exec($ch);

          curl_close($ch);

          $json_response = json_decode($result, true);

          if($json_response && $json_response["status"] == 1){
              return true;
          }else{
              return false;
          }

      }else{
          return false;
      }
   }
catch(Exception $e) {
  exec("logger -t failed to set web server port, Internal error.");
  return False;
  }
}

function set_hub_config($status, $hub_domain, $override, $sync_interval)
{
  exec("uci set anexhub.hub.status='".$status."'");
  exec("uci set anexhub.hub.override='".$override."'");
  exec("uci set anexhub.hub.sync_interval='".$sync_interval."'");
  
  exec("uci set anexgate.config.hub_domain='".$hub_domain."'");
  exec("uci commit anexgate");
  exec("sync");
  if($status == "no") {
    exec("uci set anexhub.hub.registered='no'");
    exec("uci delete network.hub0");
    exec("uci delete network.wgserver");
    exec("uci commit network");
    exec("uci commit anexhub");
    exec("/etc/init.d/network reload");
  } else {
    set_hub_device_web_server_port();
    exec("/etc/init.d/hub restart");
  }
}

function push_local_config()
{
  exec("uci set anexhub.hub.one_time_push='1'");
  exec("uci commit anexgate");
  exec("sync");
  exec("/etc/init.d/hub restart");
}


function set_ddns_client($ddns_provider, $src_interface, $username, $password, $ddns_name, $update_url)
{
  exec("uci delete ddns.ace");

  if($ddns_provider == "custom"){
    exec("uci set ddns.ace.update_url='".$update_url."'");
  }
  exec("uci set ddns.ace.service_name='".$ddns_provider."'");
  exec("uci set ddns.ace.domain='".$ddns_name."'");
  exec("uci set ddns.ace.username='".$username."'");
  exec("uci set ddns.ace.password='".$password."'");
  exec("uci set ddns.ace.interface='".$src_interface."'");
  exec("uci set ddns.ace.enabled='1'");
  exec("uci commit ddns");
  exec("/etc/init.d/ddns restart");
}

function set_admin_auth($new_pass)
{
  exec("uci set anexgate.authentication.password='".$new_pass."'");
  exec("uci commit anexgate");
}

function set_oper_admin_auth($new_pass)
{
  exec("uci set anexgate.user.password='".$new_pass."'");
  exec("uci commit anexgate");
}


function get_connect_params($connect_domain)
{
  try {
    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");
    $macaddr = exec("uci get anexgate.license.macaddr");

    if ($license_key == "No License Key") {
      return array(False, True);
    }

    $ch = curl_init( "https://".$connect_domain."/api/ace/connect/init/" );

    $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr  ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "AnexConnect Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

    $result = curl_exec($ch);

    curl_close($ch);

    $json_response = json_decode($result, true);

    if ($json_response && $json_response["valid"] == "Yes") {
      exec("uci set anexgate.connect.success='1'");
      $is_tunnel = $json_response["is_tunnel"];
      if ($is_tunnel == "1") {
        exec("uci delete autossh.@autossh[0].ssh");
        exec("uci set autossh.@autossh[0].enabled='0'");
        exec("uci delete autossh.@autossh[0].poll='120'");
        exec("uci commit autossh");
        exec("/etc/init.d/autossh stop");
        exec("/etc/init.d/autossh disable");
        $device_ip_addr = $json_response["wireguard_ipv4"];
        $device_ip_addr_6 = $json_response["wireguard_ipv6"];
        $host_ip = $json_response["wireguard_server_ipv4"];
        $host_ip6 = $json_response["wireguard_server_ipv6"];
        $device_wireguard_private_key = $json_response["wireguard_privatekey"];
        $server_public_key = $json_response["wireguard_server_publickey"];
        $wireguard_port = $json_response["wireguard_server_port"];
        $connect_domain_name = $json_response["connect_domain"];
        exec("uci set anexgate.connect.success='1'");
        exec("uci commit anexgate");                                   
        exec("sync"); 

        exec("uci add network.nms0");
        exec("uci add network.wgserver0");
        
        exec("uci set network.nms0='interface'");
        exec("uci set network.nms0.proto='wireguard'");
        exec("uci set network.nms0.nohostroute='1'");
        exec("uci set network.nms0.private_key='".$device_wireguard_private_key."'");
        exec("uci delete network.nms0.addresses");
        exec("uci add_list network.nms0.addresses='".$device_ip_addr."'");
        exec("uci add_list network.nms0.addresses='".$device_ip_addr_6."'");

        exec("uci set network.wgserver0='wireguard_nms0'");
        exec("uci set network.wgserver0.public_key='".$server_public_key."'");
        exec("uci set network.wgserver0.endpoint_host='".$connect_domain_name."'");
        exec("uci set network.wgserver0.endpoint_port='51820'");
        exec("uci set network.wgserver0.route_allowed_ips='1'");
        exec("uci set network.wgserver0.persistent_keepalive='25'");
        exec("uci delete network.wgserver0.allowed_ips");
        exec("uci add_list network.wgserver0.allowed_ips='".$host_ip."'");
        exec("uci add_list network.wgserver0.allowed_ips='".$host_ip6."'");
        exec("uci commit network");
        exec("ifup nms0");
        set_connect_web_server_port();
        return True;
      } else {
        exec("ifdown nms0");
        exec("uci delete network.nms0");
        exec("uci delete network.wgserver0");
        exec("uci commit network");
        $pkey = base64_decode($json_response["pkey"]);
        file_put_contents("/etc/dropbear/connect.pkey", $pkey);
        exec("uci set anexgate.connect.pkey='/etc/dropbear/connect.pkey'");
        $listen_http = exec("uci get uhttpd.main.listen_http | cut -d ' ' -f 1");
        $listen_port = explode(":", $listen_http)[1];
        exec("uci set autossh.@autossh[0].ssh='-i /etc/dropbear/connect.pkey -f -y -K 30 -N -T -R ".$json_response["connect_port"].":127.0.0.1:".$listen_port." ace_connect@".$json_response["connect_domain"]."'");
        exec("uci set autossh.@autossh[0].enabled='1'");
        exec("uci set autossh.@autossh[0].poll='120'");
        exec("uci set anexgate.config.connect_port='".$json_response["connect_port"]."'");
        exec("uci set anexgate.config.connect_domain='".$json_response["connect_domain"]."'");
        exec("uci commit autossh");
        exec("uci commit anexgate");
        exec("sync");
        exec("/etc/init.d/autossh enable");
        exec("/etc/init.d/autossh start");
        return True;
      }
    } else if ($json_response && $json_response["valid"] == "No") {
      exec("uci set anexgate.connect.success='0'");
      return False;
    } else {
      return False;
    }
  }
  catch(Exception $e) {
    exec("uci set anexgate.connect.success='0'");
    return False;
  }
}

function set_connect_config($status, $connect_domain)
{
  exec("uci set anexgate.connect.connect_domain='".$connect_domain."'");
  exec("uci set anexgate.config.connect_domain='".$connect_domain."'");
  exec("uci set anexgate.connect.status='".$status."'");
  exec("uci commit anexgate");
  exec("sync");
  if ($status == "1") {
    get_connect_params($connect_domain);
  } else {
    exec("ifdown nms0");
    exec("uci set anexgate.connect.status='0'");
    exec("uci set anexgate.connect.success='0'");
    exec("uci commit anexgate");
    exec("uci delete network.nms0");
    exec("uci delete network.wgserver0");
    exec("uci commit network");
    exec("uci delete anexgate.connect.success");
    exec("uci delete autossh.@autossh[0].ssh");
    exec("uci set autossh.@autossh[0].enabled='0'");
    exec("uci delete autossh.@autossh[0].poll='120'");
    exec("uci commit autossh");
    exec("uci commit anexgate");
    exec("/etc/init.d/autossh stop");
    exec("/etc/init.d/autossh disable");
  }
}

function set_system_settings($hostname, $listen_port, $auto_backup, $cloud_domain, $nms_domain, $sms_management, $whitelist_numbers)
{

  exec("uci set anexgate.config.cloud_domain='".$cloud_domain."'");
  exec("uci set anexgate.config.nms_domain='".$nms_domain."'");
  exec("uci set system.@system[0].hostname='".$hostname."'");

  exec("uci delete uhttpd.main.listen_http");
  exec("uci add_list uhttpd.main.listen_http='0.0.0.0:".$listen_port."'");
  exec("uci add_list uhttpd.main.listen_http='[::]:".$listen_port."'");

  if ($auto_backup == "on") {
    exec("uci set anexgate.config.auto_backup='1'");
  } else {
    exec("uci set anexgate.config.auto_backup='0'");
  }

  if ($sms_management == "on") {
    exec("uci set anexgate.config.sms_management='1'");
  } else {
    exec("uci set anexgate.config.sms_management='0'");
  }

  exec("uci delete anexgate.config.whitelist_numbers");
  foreach ($whitelist_numbers as $whitelist_number) {
    if ($whitelist_number) {
      exec("uci add_list anexgate.config.whitelist_numbers='".$whitelist_number."'");
    }
  }

  exec("uci commit");
}

function set_ntp_server($timezone, $ntp_server)
{
  exec("uci set system.@system[0].timezone='".$timezone."'");
  exec("uci set system.ntp.server='".$ntp_server."'");
  exec("uci commit system");
  exec("/etc/init.d/system restart");
  exec("/etc/init.d/sysntpd restart");
}

function set_syslog_config($log_remote, $log_size, $log_ip, $log_port, $log_proto)
{
  exec("uci set system.@system[0].log_size='".$log_size."'");
  if ($log_remote == "on") {
    exec("uci set system.@system[0].log_remote='1'");
    exec("uci set system.@system[0].log_ip='".$log_ip."'");
    exec("uci set system.@system[0].log_port='".$log_port."'");
    exec("uci set system.@system[0].log_proto='".$log_proto."'");
  } else {
    exec("uci delete system.@system[0].log_remote");
    exec("uci delete system.@system[0].log_ip");
    exec("uci delete system.@system[0].log_port");
    exec("uci delete system.@system[0].log_proto");
  }

  exec("uci commit system");
  exec("/bin/sync");

  exec("/etc/init.d/system restart");
  exec("/etc/init.d/log restart");

}

function set_snmp_global($status, $sysname, $location, $contact, $port)
{
  exec("uci delete snmpd.general");
  exec("uci set snmpd.general=snmpd");
  exec("uci set snmpd.general.enabled='".$status."'");

  exec("uci delete snmpd.@agent[0]");
  exec("uci set snmpd.agent=agent");
  exec("uci set snmpd.@agent[0].agentaddress='UDP:".$port.",UDP6:".$port."'");

  exec("uci delete snmpd.@agentx[0]");
  exec("uci set snmpd.agentx=agentx");
  exec("uci set snmpd.agentx.agentxsocket='/var/run/agentx.sock'");

  exec("uci delete snmpd.@system[0]");
  exec("uci set snmpd.system=system");
  exec("uci set snmpd.@system[0].sysDescr='".$sysname."'");
  exec("uci set snmpd.@system[0].sysLocation='".$location."'");
  exec("uci set snmpd.@system[0].sysContact='".$contact."'");

  exec("uci set snmpd.@exec[0].name='filedescriptors'");
  exec("uci set snmpd.@exec[0].prog='/bin/cat'");
  exec("uci set snmpd.@exec[0].args='/proc/sys/fs/file-nr'");

  exec("uci commit snmpd");
  exec("/etc/init.d/snmpd restart");
}

function set_snmp_config($snmpv1_only, $community_get, $set_enable, $community_set, $rohost, $trap_enable, $trap_host_ip, $trap_host_port)
{
  exec("uci delete snmpd.public");
  exec("uci set snmpd.public=com2sec");
  exec("uci set snmpd.public.secname='ro'");
  exec("uci set snmpd.public.community='".$community_get."'");
  exec("uci set snmpd.public.source='".$rohost."'");
  exec("uci delete snmpd.public6");
  exec("uci set snmpd.public6=com2sec6");
  exec("uci set snmpd.public6.secname='ro'");
  exec("uci set snmpd.public6.community='".$community_get."'");
  exec("uci set snmpd.public6.source='".$rohost."'");

  exec("uci delete snmpd.private");
  exec("uci delete snmpd.private6");
  if ($set_enable == "on") {
    exec("uci set snmpd.private=com2sec");
    exec("uci set snmpd.private.secname='rw'");
    exec("uci set snmpd.private.community='".$community_set."'");
    exec("uci set snmpd.private.source='".$rohost."'");

    exec("uci set snmpd.private6=com2sec6");
    exec("uci set snmpd.private6.secname='rw'");
    exec("uci set snmpd.private6.community='".$community_set."'");
    exec("uci set snmpd.private6.source='".$rohost."'");
  }

  exec("uci delete snmpd.snmp_ro");
  exec("uci set snmpd.snmp_ro=group");
  exec("uci set snmpd.snmp_ro.secname='ro'");
  exec("uci set snmpd.snmp_ro.group='".$community_get."'");
  if ($snmpv1_only == "on") {
    exec("uci set snmpd.snmp_ro.version='v1'");
  } else {
    exec("uci set snmpd.snmp_ro.version='v2c'");
  }
  exec("uci delete snmpd.snmp_rw");
  if ($set_enable == "on") {
    exec("uci set snmpd.snmp_rw=group");
    exec("uci set snmpd.snmp_rw.secname='rw'");
    exec("uci set snmpd.snmp_rw.group='".$community_set."'");
    if ($snmpv1_only == "on") {
      exec("uci set snmpd.snmp_rw.version='v1'");
    } else {
      exec("uci set snmpd.snmp_rw.version='v2c'");
    }
  }

  exec("uci delete snmpd.all");
  exec("uci set snmpd.all=view");
  exec("uci set snmpd.all.viewname='all'");
  exec("uci set snmpd.all.type='included'");
  exec("uci set snmpd.all.oid='.1'");

  exec("uci delete snmpd.public_access");
  exec("uci set snmpd.public_access=access");
  exec("uci set snmpd.public_access.context='none'");
  exec("uci set snmpd.public_access.version='any'");
  exec("uci set snmpd.public_access.level='noauth'");
  exec("uci set snmpd.public_access.prefix='exact'");
  exec("uci set snmpd.public_access.read='all'");
  exec("uci set snmpd.public_access.write='none'");
  exec("uci set snmpd.public_access.notify='none'");
  exec("uci set snmpd.public_access.group='".$community_get."'");

  exec("uci delete snmpd.private_access");

  if ($set_enable == "on") {
    exec("uci set snmpd.private_access=access");
    exec("uci set snmpd.private_access.context='none'");
    exec("uci set snmpd.private_access.version='any'");
    exec("uci set snmpd.private_access.level='noauth'");
    exec("uci set snmpd.private_access.prefix='exact'");
    exec("uci set snmpd.private_access.read='all'");
    exec("uci set snmpd.private_access.write='all'");
    exec("uci set snmpd.private_access.notify='none'");
    exec("uci set snmpd.private_access.group='".$community_set."'");
  }

  exec("uci delete snmpd.@trap2sink[0]");
  exec("uci delete snmpd.authtrapenable");
  exec("uci set snmpd.authtrapenable=authtrapenable");
  if ($trap_enable == "on") {
    exec("uci set snmpd.authtrapenable.enable='1'");
    exec("uci set snmpd.trap2sink=trap2sink");
    exec("uci set snmpd.@trap2sink[0].host='".$trap_host_ip."'");
    exec("uci set snmpd.@trap2sink[0].port='".$trap_host_port."'");
  } else {
    exec("uci set snmpd.authtrapenable.enable='0'");
  }
  exec("uci commit snmpd");
  exec("/etc/init.d/snmpd restart");
}

function set_snmpv3_config($rouser, $rouser_enc, $rouser_auth, $rouser_auth_pwd, $rouser_priv, $rouser_priv_pwd, $user_rw, $snmpv3_trap, $trapsessip, $trapsessport)
{
    exec("uci delete snmpd.ro_user");
    exec("uci delete snmpd.snmp_usm_ro");
    exec("uci delete snmpd.usm_pub_access");
    exec("uci delete snmpd.rw_user");
	exec("uci delete snmpd.snmp_usm_rw");
	exec("uci delete snmpd.usm_pub_priv");
    exec("uci delete snmpd.trapsess");
	exec("uci delete snmpd.authtrapenable");

    exec("uci set snmpd.ro_user=user");
	exec("uci set snmpd.ro_user.name='".$rouser."'");
    if ($rouser_enc == "authPriv") {
        exec("uci set snmpd.ro_user.level='".$rouser_enc."'");
        exec("uci set snmpd.ro_user.auth_proto='".$rouser_auth."'");
        exec("uci set snmpd.ro_user.auth_passphrase='".$rouser_auth_pwd."'");
        exec("uci set snmpd.ro_user.priv_proto='".$rouser_priv."'");
        exec("uci set snmpd.ro_user.priv_passphrase='".$rouser_priv_pwd."'");
    } else if ($rouser_enc == "authNoPriv") {
        exec("uci set snmpd.ro_user.level='".$rouser_enc."'");
        exec("uci set snmpd.ro_user.auth_proto='".$rouser_auth."'");
        exec("uci set snmpd.ro_user.auth_passphrase='".$rouser_auth_pwd."'");
    } else {
        exec("uci set snmpd.ro_user.level='".$rouser_enc."'");
    }
    exec("uci set snmpd.snmp_usm_ro=group");
    exec("uci set snmpd.snmp_usm_ro.group='ro_user'");
    exec("uci set snmpd.snmp_usm_ro.context='none'");
    exec("uci set snmpd.snmp_usm_ro.seclevel='".$rouser_enc."'");
    exec("uci set snmpd.usm_pub_access=access");
    exec("uci set snmpd.usm_pub_access.context='none'");
    exec("uci set snmpd.usm_pub_access.version='v3'");
    exec("uci set snmpd.usm_pub_access.level='noauth'");
    exec("uci set snmpd.usm_pub_access.prefix='exact'");
    exec("uci set snmpd.usm_pub_access.read='all'");
    exec("uci set snmpd.usm_pub_access.notify='none'");
	exec("uci set snmpd.usm_pub_access.group='ro_user'");

    if ($user_rw == "on") {
		exec("uci set snmpd.ro_user.secname='rwuser'");
		exec("uci set snmpd.snmp_usm_ro.secname='rwuser'");
		exec("uci set snmpd.usm_pub_access.write='all'");
	} else {
		exec("uci set snmpd.ro_user.secname='rouser'");
		exec("uci set snmpd.snmp_usm_ro.secname='rouser'");
		exec("uci set snmpd.usm_pub_access.write='none'");
	}
	exec("uci set snmpd.authtrapenable=authtrapenable");
	if ($snmpv3_trap == "on") {
		exec("uci set snmpd.authtrapenable.enable='1'");
		exec("uci commit snmpd");
		if ($trapsessip) {
			exec("uci set snmpd.trapsess=trapsess");
			if ($rouser_enc == "authNoPriv") {
				exec("uci set snmpd.trapsess.trapsess='-v 3 -u ".$rouser." -l ".$rouser_enc." -a ".$rouser_auth." -A ".$rouser_auth_pwd." udp:".$trapsessip.":".$trapsessport."'");
			}
			if ($rouser_enc == "authPriv") {
				exec("uci set snmpd.trapsess.trapsess='-v 3 -u ".$rouser." -l ".$rouser_enc." -a ".$rouser_auth." -A ".$rouser_auth_pwd." -x ".$rouser_priv." -X ".$rouser_priv_pwd." udp:".$trapsessip.":".$trapsessport."'");
			}
			if ($rouser_enc == "noAuthNoPriv") {
				exec("uci set snmpd.trapsess.trapsess='-v 3 -u ".$rouser." -l ".$rouser_enc." udp:".$trapsessip.":".$trapsessport."'");
			}
		}
	}
	exec("uci commit snmpd");
	exec("/etc/init.d/snmpd restart");
}

function set_lldp_config($enable_cdp, $enable_fdp, $enable_sonmp, $enable_edp, $lldp_class, $lldp_description, $lldp_hostname, $interfaces)
{
  exec("uci set lldpd.config.enable_cdp='".$enable_cdp."'");
  exec("uci set lldpd.config.enable_fdp='".$enable_fdp."'");
  exec("uci set lldpd.config.enable_sonmp='".$enable_sonmp."'");
  exec("uci set lldpd.config.enable_edp='".$enable_edp."'");
  exec("uci set lldpd.config.lldp_class='".$lldp_class."'");
  exec("uci set lldpd.config.lldp_description='".$lldp_description."'");
  exec("uci set lldpd.config.lldp_hostname='".$lldp_hostname."'");
  exec("uci delete lldpd.config.interface");

  foreach (array_filter($interfaces) as $interface) {
		exec("uci add_list lldpd.config.interface='".$interface."'");
	}
  exec("uci commit lldpd");
  exec("/etc/init.d/lldpd restart");
}

function set_reboot_task($enabled, $time, $sunday, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday)
{
  if($enabled == "on") {
    exec("uci set tasks.reboot.enabled='1'");
  } else {
    exec("uci set tasks.reboot.enabled='0'");
  }
  exec("uci set tasks.reboot.time='".$time."'");
  if($sunday == "on") {
    exec("uci set tasks.reboot.sunday='1'");
  } else {
    exec("uci set tasks.reboot.sunday='0'");
  }
  if($monday == "on") {
    exec("uci set tasks.reboot.monday='1'");
  } else {
    exec("uci set tasks.reboot.monday='0'");
  }
  if($tuesday == "on") {
    exec("uci set tasks.reboot.tuesday='1'");
  } else {
    exec("uci set tasks.reboot.tuesday='0'");
  }
  if($wednesday == "on") {
    exec("uci set tasks.reboot.wednesday='1'");
  } else {
    exec("uci set tasks.reboot.wednesday='0'");
  }
  if($thursday == "on") {
    exec("uci set tasks.reboot.thursday='1'");
  } else {
    exec("uci set tasks.reboot.thursday='0'");
  }
  if($friday == "on") {
    exec("uci set tasks.reboot.friday='1'");
  } else {
    exec("uci set tasks.reboot.friday='0'");
  }
  if($saturday == "on") {
    exec("uci set tasks.reboot.saturday='1'");
  } else {
    exec("uci set tasks.reboot.saturday='0'");
  }
  exec("uci commit tasks");
  exec("/bin/sed -i '/tasks/d' /etc/crontabs/root");
  if($enabled) {
    $hrs=explode(':', $time)[0];
    $min=explode(':', $time)[1];
    exec("/bin/echo \"$min $hrs * * * /bin/sh /bin/tasks\" >> /etc/crontabs/root");
  }
}

function set_netflow_config($enabled, $hook_v4, $hook_v6, $natevents, $appevents, $source_id, $destinations, $app_destinations)
{
  exec("uci set netflow.globals.enabled='".$enabled."'");
  exec("uci set netflow.globals.source_id='".$source_id."'");
  if ($hook_v4 == "on") {
    exec("uci set netflow.globals.hook_v4='1'");
  } else {
    exec("uci set netflow.globals.hook_v4='0'");
  }

  if ($hook_v6 == "on") {
    exec("uci set netflow.globals.hook_v6='1'");
  } else {
    exec("uci set netflow.globals.hook_v6='0'");
  }

  if ($natevents == "on") {
    exec("uci set netflow.globals.natevents='1'");
  } else {
    exec("uci set netflow.globals.natevents='0'");
  }

  if ($appevents == "on") {
    exec("uci delete dpiagent.globals.destinations");
    exec("uci set dpiagent.globals.source_id='".$source_id."'");
    exec("uci set netflow.globals.appevents='1'");
    exec("uci set dpiagent.globals.enabled='1'");
    foreach($app_destinations as $app_destination) {
      exec("uci add_list dpiagent.globals.destinations='".$app_destination."'");
    }
  } else {
    exec("uci set dpiagent.globals.enabled='0'");
    exec("uci set netflow.globals.appevents='0'");
    exec("uci delete dpiagent.globals.destinations");
  }

  exec("uci delete netflow.globals.destinations");

  foreach($destinations as $destination) {
    exec("uci add_list netflow.globals.destinations='".$destination."'");
  }

  exec("uci commit netflow");
  exec("uci commit dpiagent");
  exec("/etc/init.d/netflow restart");
  exec("/etc/init.d/dpiagent restart");
  sleep(2);
  exec("/etc/init.d/netifyd restart");
}

function anexconnect_init($status, $connect_domain)
{

  try {
      
      exec("uci set anexgate.connect.connect_domain='".$connect_domain."'");
      exec("uci set anexgate.config.connect_domain='".$connect_domain."'");
      exec("uci set anexgate.connect.status='".$status."'");
      exec("uci commit anexgate");                                   
      exec("sync"); 
      $license_key = exec("uci get anexgate.license.key");                                                                                                                                                           
      $serial = exec("uci get anexgate.license.serial");                                                                                                                                                             
      $macaddr = exec("uci get anexgate.license.macaddr");                                                                                                                                                           
      $connect_status = exec("uci get anexgate.connect.status");                                         
      if ($connect_status == "0" ){
          exec("ifdown nms0");
          exec("uci set anexgate.connect.status='0'");
          exec("uci set anexgate.connect.success='0'");
          exec("uci commit anexgate");
          exec("uci delete network.nms0");
          exec("uci delete network.wgserver0");
	        exec("uci commit network");
          return True;
      }
      if ($license_key == "No License Key") {                                                                                                                                                                        
        return array(False, True);                                                                                                                                                                                   
      }                                                                                                                                                                                  
      $ch = curl_init( "https://".$connect_domain."/api/ace/connect/init/" );                                                                                                                                        
                                                                                                                                                                                                                      
      $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr  ) );                                                                                                 
                                                                                                                                                                                                                      
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );                                                                                                                                                              
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));                                                                                                                                 
      curl_setopt( $ch, CURLOPT_USERAGENT, "AnexConnect Agent");                                                                                                                                                     
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );                                                                                                                                                              
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );                                                                                                                                                              
      curl_setopt( $ch, CURLOPT_POSTREDIR, 7);                                                                                                                                                                       
                                                                                                                                                                                                                      
      $result = curl_exec($ch);                                                                                                                                                                                      
                                                                                                                                                                                                                      
      curl_close($ch);                                                                                                                                                                                               
                                                                                                                                                                                                          
      $json_response = json_decode($result, true);  
      if ($json_response && $json_response["valid"] == "Yes")
      {
        
        $device_ip_addr = $json_response["wireguard_ipv4"];
        $device_ip_addr_6 = $json_response["wireguard_ipv6"];
        $host_ip = $json_response["wireguard_server_ipv4"];
        $host_ip6 = $json_response["wireguard_server_ipv6"];
        $device_wireguard_private_key = $json_response["wireguard_privatekey"];
        $server_public_key = $json_response["wireguard_server_publickey"];
        $wireguard_port = $json_response["wireguard_server_port"];
        $connect_domain_name = $json_response["connect_domain"];
        exec("uci set anexgate.connect.success='1'");
        exec("uci commit anexgate");                                   
        exec("sync"); 

        exec("uci add network.nms0");
        exec("uci add network.wgserver0");
        
        exec("uci set network.nms0='interface'");
        exec("uci set network.nms0.proto='wireguard'");
        exec("uci set network.nms0.nohostroute='1'");
        exec("uci set network.nms0.private_key='".$device_wireguard_private_key."'");
        exec("uci add_list network.nms0.addresses='".$device_ip_addr."'");
        exec("uci add_list network.nms0.addresses='".$device_ip_addr_6."'");


        exec("uci set network.wgserver0='wireguard_nms0'");
        exec("uci set network.wgserver0.public_key='".$server_public_key."'");
        exec("uci set network.wgserver0.endpoint_host='".$connect_domain_name."'");
        exec("uci set network.wgserver0.endpoint_port='51820'");
        exec("uci set network.wgserver0.route_allowed_ips='1'");
        exec("uci set network.wgserver0.persistent_keepalive='25'");
        exec("uci add_list network.wgserver0.allowed_ips='".$host_ip."'");
        exec("uci add_list network.wgserver0.allowed_ips='".$host_ip6."'");
        exec("uci commit network");
        exec("ifup nms0");

        return True;
      }else if ($json_response && $json_response["valid"] == "No") {
        exec("uci set anexgate.connect.success='0'");
        return False;
      } 
    }catch(Exception $e) {
    exec("uci set anexgate.connect.success='0'");
    return False;
    }
}   

function remove_config_con($status){
  exec("uci set anexgate.connect.status='".$status."'");
  exec("uci commit anexgate");                                   
  exec("sync");
  if ($status == 0){
  exec("uci delete network.nms0");
  exec("uci delete network.wgserver0");
  exec("uci commit network");
  exec("/etc/init.d/network restart");
  }
}


?> 
