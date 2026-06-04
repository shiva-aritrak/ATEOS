<?php

function create_section($section, $sub_section, $config)
{
  $out = "echo \"config ".$section." '".$sub_section."'\" >> /etc/config/".$config;
  exec($out);
  exec("uci commit ".$config);
}

function get_int_from_on_off($on_off_str) {
  if ($on_off_str == "on" ) {
    return "1";
  } else {
    return "0";
  }
}

function get_inverse_int_from_on_off($on_off_str) {
  if ($on_off_str == "on" ) {
    return "0";
  } else {
    return "1";
  }
}

function write_passphrase($filename, $vpn_passphrase)
{
  $handle = fopen($filename, 'w') or die('Cannot open file:  '.$filename);
  fwrite($handle, $vpn_passphrase);
  fclose($handle);
}

function set_openvpn_config($client_no)
{
  exec('uci commit openvpn');
  exec("sync");
}

function set_network_config($vpn_interface)
{
  exec("/sbin/ifdown ".$vpn_interface);
  exec('uci commit network');
  exec("sync");
  exec("/etc/init.d/openvpn restart");
  exec("/sbin/ifup ".$vpn_interface);
}

function configure_openvpn_vpn($client_no, $status, $proto, $concentrator_ip, $concentrator_port, $vpn_passphrase, $compression_mode, $pull_mode, $persist_key, $persist_tun, $tls_verify, $remote_lan_ip, $remote_lan_subnet, $metric, $cipher_mode, $auth_mode, $zip_file_type)
{
  $pre = "uci set openvpn.oclient".$client_no.".";
  exec("uci set openvpn.oclient".$client_no."=openvpn");
  exec($pre."dev=ovpn".$client_no);
  exec($pre."dev_type='tun'");
  exec($pre."verb='3'");
  exec($pre."client='1'");
  exec($pre."remote_cert_tls='server'");
  exec($pre."resolv_retry='infinite'");
  exec($pre."keepalive='10 120'");
  exec($pre."nobind='1'");

  exec($pre."enabled='".$status."'");
  exec($pre."proto=".strtolower($proto));

  if ($cipher_mode != "None") {
    exec($pre."cipher='".$cipher_mode."'");
    exec("uci add_list openvpn.oclient.data_cipher='".$cipher_mode."'");
  } else {
    exec("uci delete openvpn.oclient".$client_no.".cipher");
    exec("uci delete openvpn.oclient".$client_no.".data_cipher");
  }
  if ($auth_mode != "None") {
    exec($pre."auth='".$auth_mode."'");
  } else {
    exec("uci delete openvpn.oclient".$client_no.".auth");
  }

  exec("uci delete openvpn.oclient".$client_no.".remote");
  foreach (explode(",", $concentrator_ip) as $concentrator) {
    exec("uci add_list openvpn.oclient".$client_no.".remote='".$concentrator." ".$concentrator_port."'");
  }
  exec($pre."route_nopull=".get_inverse_int_from_on_off($pull_mode));
  exec($pre."pull=".get_int_from_on_off($pull_mode));

  exec("uci delete openvpn.oclient".$client_no.".route");
  exec("uci delete openvpn.oclient".$client_no.".route_metric");
  if ($pull_mode != "on") {
    if ($remote_lan_ip != "" && $remote_lan_subnet !="") {
      exec("uci add_list openvpn.oclient".$client_no.".route='".$remote_lan_ip." ".$remote_lan_subnet."'");
      exec($pre."route_metric='".$metric."'");
    }
  }

  if ($compression_mode != "None") {
    exec($pre."compress='".$compression_mode."'");
  } else {
    exec("uci delete openvpn.oclient".$client_no.".compress");
  }

  exec($pre."zip_file_type='".$zip_file_type."'");
  exec($pre."persist_key=".get_int_from_on_off($persist_key));
  exec($pre."persist_tun=".get_int_from_on_off($persist_tun));
  exec($pre."tls_client=".get_int_from_on_off($tls_verify));
  exec($pre."key_direction=".get_int_from_on_off($tls_verify));
  exec($pre."ca=/etc/openvpn/ca_client".$client_no.".crt");
  exec($pre."cert=/etc/openvpn/cert_client".$client_no.".crt");
  exec($pre."key=/etc/openvpn/key_client".$client_no.".key");
  if ($tls_verify == "on") {
    exec($pre."tls_auth='/etc/openvpn/tls_key_client".$client_no.".key ".get_int_from_on_off($tls_verify)."'");
  } else {
    exec("uci delete openvpn.oclient".$client_no.".tls_auth");
  }

  exec("uci delete network.ovpn".$client_no);
  exec("uci set network.ovpn".$client_no."=interface");
  exec("uci set network.ovpn".$client_no.".proto='none'");
  exec("uci set network.ovpn".$client_no.".ifname='ovpn".$client_no."'");

  if ($vpn_passphrase) {
    write_passphrase("/etc/config/openvpn".$client_no."_pass", $vpn_passphrase);
    exec($pre."askpass='/etc/config/openvpn".$client_no."_pass'");
  } else {
    unlink("/etc/config/openvpn".$client_no."_pass");
    exec("uci delete openvpn.oclient".$client_no.".askpass");
  }

  set_openvpn_config($client_no);
  set_network_config("ovpn".$client_no);
}

function delete_openvpn_vpn($client_no) {
  unlink("/etc/openvpn/ca_client".$client_no.".crt");
  unlink("/etc/openvpn/cert_client".$client_no.".crt");
  unlink("/etc/openvpn/key_client".$client_no.".key");
  unlink("/etc/config/openvpn".$client_no."_pass");
  exec("uci delete openvpn.oclient".$client_no);
  exec("uci delete network.ovpn".$client_no);
  set_openvpn_config($client_no);
  exec("/sbin/ifdown ovpn".$client_no);
  exec("/etc/init.d/openvpn restart");
}

function set_gre($gre_no, $status, $local_ip_addr, $remote_ip_addr, $ttl, $static_ip_addr, $static_netmask, $tun_target, $tun_netmask, $tun_gateway)
{
  exec("uci set network.tungre".$gre_no."=interface");
  exec("uci set network.tungre".$gre_no.".enabled='".$status."'");
  exec("uci set network.tungre".$gre_no.".ipaddr='".$local_ip_addr."'");
  exec("uci set network.tungre".$gre_no.".peeraddr='".$remote_ip_addr."'");
  exec("uci set network.tungre".$gre_no.".ttl='".$ttl."'");
  exec("uci set network.tungre".$gre_no.".proto='gre'");
  exec("uci set network.tungre".$gre_no.".ifname='tungre".$gre_no."'");

  exec("uci set network.tungre".$gre_no."_static=interface");
  exec("uci set network.tungre".$gre_no."_static.enabled='".$status."'");
  exec("uci set network.tungre".$gre_no."_static.proto='static'");
  exec("uci set network.tungre".$gre_no."_static.ifname='@tungre".$gre_no."'");
  exec("uci set network.tungre".$gre_no."_static.ipaddr='".$static_ip_addr."'");
  exec("uci set network.tungre".$gre_no."_static.netmask='".$static_netmask."'");

  if ($status == "0") {
    exec("uci set network.gre".$gre_no."_tunnel=route");
  } else {
    exec("uci set network.gre".$gre_no."_tunnel=disabled_route");
  }
  exec("uci set network.gre".$gre_no."_tunnel.interface='tungre".$gre_no."_static'");
  exec("uci set network.gre".$gre_no."_tunnel.target='".$tun_target."'");
  exec("uci set network.gre".$gre_no."_tunnel.netmask='".$tun_netmask."'");
  exec("uci set network.gre".$gre_no."_tunnel.gateway='".$tun_gateway."'");

  exec("ifdown tungre".$gre_no);
  exec("uci commit network");
  exec("ifup tungre".$gre_no);
}

function delete_ssl_gre($gre_no)
{
  exec("ifdown tungre".$gre_no);
  exec("uci delete network.tungre".$gre_no);
  exec("uci delete network.tungre".$gre_no."_static");
  exec("uci delete network.gre".$gre_no."_tunnel");
  exec("uci commit network");
  exec("/etc/init.d/network reload");
}



function delete_ipsec_tunnel($tunnel_no)
{
  exec("ipsec down tun".$tunnel_no."_remote-tun".$tunnel_no);

  exec("uci delete ipsec.tun".$tunnel_no."_p1");
  exec("uci delete ipsec.tun".$tunnel_no."_p2");
  exec("uci delete ipsec.tun".$tunnel_no."_remote");
  exec("uci delete ipsec.tun".$tunnel_no);

  exec("uci commit ipsec");

  exec("/etc/init.d/ipsec reload");
  exec("/etc/init.d/ipsec restart");
}

function set_ipsec_config($tunnel_no, $exchange_mode, $enc_alg, $hash_alg, $auth_method, $dh_group, $p2_dh_group, $p2_enc_alg, $p2_hash_alg, $local_gateway, $remote_ip, $local_net, $remote_net, $pre_shared_key, $log_level, $local_id, $key_exchange_mode, $status, $remote_ident, $ike_lifetime, $lifetime, $keyingtries, $dpddelay, $dpdaction, $dpdtimeout, $allow_lan, $passthrough_lan, $passthrough_lan_subnet, $esp_ah_mode, $pingcount, $track_ips, $backup_tunnel)
{
  delete_ipsec_tunnel($tunnel_no);

  exec("uci set ipsec.@ipsec[".($tunnel_no-1)."]=ipsec");
  exec("uci set ipsec.@ipsec[".($tunnel_no-1)."].listen=''");
  exec("uci set ipsec.@ipsec[".($tunnel_no-1)."].debug='".$log_level."'");
  exec("uci set ipsec.@ipsec[".($tunnel_no-1)."].ike_mode='".$exchange_mode."'");

  create_section("ipsec", "remote", "tun".$tunnel_no."_remote");
  exec("uci set ipsec.tun".$tunnel_no."_remote='remote'");
  exec("uci set ipsec.tun".$tunnel_no."_remote.enabled='".$status."'");

  exec("uci set ipsec.tun".$tunnel_no."_remote.active='1'");
  exec("uci set ipsec.tun".$tunnel_no."_remote.gateway='".$remote_ip."'");
  exec("uci set ipsec.tun".$tunnel_no."_remote.pre_shared_key='".$pre_shared_key."'");
  exec("uci set ipsec.tun".$tunnel_no."_remote.authentication_method='".$auth_method."'");

  if ($local_id) {
    if (filter_var($local_id, FILTER_VALIDATE_IP)) {
      $local_id = $local_id;
    } else {
      $local_id = $local_id;
    }
    exec("uci set ipsec.tun".$tunnel_no."_remote.my_identifier='".$local_id."'");
    exec("uci set ipsec.tun".$tunnel_no."_remote.local_identifier='".$local_id."'");
  }
  if ($remote_ident) {
    if (filter_var($remote_ident, FILTER_VALIDATE_IP)) {
      $remote_ident = $remote_ident;
    } else {
      $remote_ident = $remote_ident;
    }
    exec("uci set ipsec.tun".$tunnel_no."_remote.remote_identifier='".$remote_ident."'");
  }
  if ($local_gateway) {
    exec("uci set ipsec.tun".$tunnel_no."_remote.local_gateway='".$local_gateway."'");
  }

  if($backup_tunnel) {
    exec("uci set ipsec.tun".$tunnel_no."_remote.backup_tunnel='".$backup_tunnel."'");
  }

  create_section("ipsec", "p1_proposal", "tun".$tunnel_no."_p1");
  exec("uci set ipsec.tun".$tunnel_no."_p1='p1_proposal'");
  exec("uci set ipsec.tun".$tunnel_no."_p1.encryption_algorithm='".strtolower($enc_alg)."'");
  exec("uci set ipsec.tun".$tunnel_no."_p1.auth_method='".$auth_method."'");
  if ($dh_group != "None") {
    exec("uci set ipsec.tun".$tunnel_no."_p1.dh_group='".$dh_group."'");
  }
  if ($hash_alg != "None") {
    exec("uci set ipsec.tun".$tunnel_no."_p1.hash_algorithm='".strtolower($hash_alg)."'");
  }

  create_section("ipsec", "p2_proposal", "tun".$tunnel_no."_p2");
  exec("uci set ipsec.tun".$tunnel_no."_p2='p2_proposal'");
  exec("uci set ipsec.tun".$tunnel_no."_p2.encryption_algorithm='".strtolower($p2_enc_alg)."'");
  if ($p2_dh_group != "None") {
    exec("uci set ipsec.tun".$tunnel_no."_p2.dh_group='".$p2_dh_group."'");
  }
  if ($p2_hash_alg != "None") {
    exec("uci set ipsec.tun".$tunnel_no."_p2.hash_algorithm='".strtolower($p2_hash_alg)."'");
  }

  create_section("ipsec", "tunnel", "tun".$tunnel_no);
  exec("uci set ipsec.tun".$tunnel_no."='tunnel'");
  foreach (explode(",", $local_net) as $local_subnet) {
    exec("uci add_list ipsec.tun".$tunnel_no.".local_subnet='".$local_subnet."'");
  }
  foreach (explode(",", $remote_net) as $remote_subnet) {
    exec("uci add_list ipsec.tun".$tunnel_no.".remote_subnet='".$remote_subnet."'");
  }

  exec("uci set ipsec.tun".$tunnel_no.".pingcount='".$pingcount."'");

  foreach ($track_ips as $track_ip) {
    if ($track_ip) {
      exec("uci add_list ipsec.tun".$tunnel_no.".track_ip='".$track_ip."'");
    }
  }

  // exec("uci set ipsec.tun".$tunnel_no.".remote_subnet='".$remote_net."'");
  exec("uci set ipsec.tun".$tunnel_no.".mode='start'");
  exec("uci set ipsec.tun".$tunnel_no.".exchange_mode='".$exchange_mode."'");
  exec("uci set ipsec.tun".$tunnel_no.".esp_ah_mode='".$esp_ah_mode."'");
  exec("uci set ipsec.tun".$tunnel_no.".keyexchange='".$key_exchange_mode."'");
  if ($allow_lan == "on") {
    exec("uci set ipsec.tun".$tunnel_no.".local_updown='/bin/ipsec_postrouting.sh'");
  }
  if ($passthrough_lan == "on") {
    exec("uci set ipsec.tun".$tunnel_no.".passthrough_lan='yes'");
    exec("uci set ipsec.tun".$tunnel_no.".passthrough_lan_subnet='".$passthrough_lan_subnet."'");
  }

  exec("uci set ipsec.tun".$tunnel_no.".allow_lan='".get_int_from_on_off($allow_lan)."'");
  exec("uci set ipsec.tun".$tunnel_no.".crypto_proposal='tun".$tunnel_no."_p2'");
  exec("uci set ipsec.tun".$tunnel_no.".ikelifetime='".$ike_lifetime."'");
  exec("uci set ipsec.tun".$tunnel_no.".lifetime='".$lifetime."'");
  exec("uci set ipsec.tun".$tunnel_no.".keyingtries='".$keyingtries."'");
  exec("uci set ipsec.tun".$tunnel_no.".dpddelay='".$dpddelay."'");
  exec("uci set ipsec.tun".$tunnel_no.".dpdaction='".strtolower($dpdaction)."'");
  exec("uci set ipsec.tun".$tunnel_no.".dpdtimeout='".strtolower($dpdtimeout)."'");

  exec("uci delete ipsec.tun".$tunnel_no."_remote.p1_proposal");
  exec("uci delete ipsec.tun".$tunnel_no."_remote.tunnel");
  exec("uci add_list ipsec.tun".$tunnel_no."_remote.crypto_proposal='tun".$tunnel_no."_p1'");
  exec("uci add_list ipsec.tun".$tunnel_no."_remote.tunnel='tun".$tunnel_no."'");

  exec("uci commit ipsec");
  exec("/etc/init.d/ipsec reload");
  exec("/etc/init.d/ipsec restart");
  exec("/etc/init.d/ipsecmon restart");
  exec("ipsec up tun".$tunnel_no."_remote-tun".$tunnel_no);
}

function set_pptp_vpn($pptp_no, $status, $pptp_server, $username, $password, $lcp_echo_interval, $lcp_echo_timeout, $defaultroute, $metric, $mtu)
{
  exec("uci delete network.pptp".$pptp_no);
  exec("uci commit network");

  exec("uci set network.pptp".$pptp_no."=interface");
  exec("uci set network.pptp".$pptp_no.".proto='pptp'");
  exec("uci set network.pptp".$pptp_no.".iface='pptp".$pptp_no."'");
  exec("uci set network.pptp".$pptp_no.".enabled='".$status."'");
  exec("uci set network.pptp".$pptp_no.".username='".$username."'");
  exec("uci set network.pptp".$pptp_no.".password='".$password."'");
  exec("uci set network.pptp".$pptp_no.".server='".$pptp_server."'");
  exec("uci set network.pptp".$pptp_no.".keepalive='".$lcp_echo_interval." ".$lcp_echo_timeout."'");
  exec("uci set network.pptp".$pptp_no.".defaultroute='".$defaultroute."'");

  if ($defaultroute && $defaultroute == "on") {
    exec("uci set network.pptp".$pptp_no.".defaultroute='1'");
  } else {
    exec("uci set network.pptp".$pptp_no.".defaultroute='0'");
  }

  if ($metric) {
    exec("uci set network.pptp".$pptp_no.".metric='".$metric."'");
  }

  if ($mtu) {
    exec("uci set network.pptp".$pptp_no.".mtu='".$mtu."'");
  }

  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
}

function delete_pptp_vpn($filled_pptp_no)
{
  exec("uci delete network.pptp".$filled_pptp_no);
  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
}


function set_l2tp_vpn($l2tp_no, $status, $l2tp_server, $username, $password, $lcp_echo_interval, $lcp_echo_timeout, $defaultroute, $metric, $mtu)
{
  exec("uci delete network.l2tp".$l2tp_no);
  exec("uci commit network");

  exec("uci set network.l2tp".$l2tp_no."=interface");
  exec("uci set network.l2tp".$l2tp_no.".proto='l2tp'");
  exec("uci set network.l2tp".$l2tp_no.".enabled='".$status."'");
  exec("uci set network.l2tp".$l2tp_no.".iface='l2tp".$l2tp_no."'");
  exec("uci set network.l2tp".$l2tp_no.".username='".$username."'");
  exec("uci set network.l2tp".$l2tp_no.".password='".$password."'");
  exec("uci set network.l2tp".$l2tp_no.".server='".$l2tp_server."'");
  exec("uci set network.l2tp".$l2tp_no.".keepalive='".$lcp_echo_interval." ".$lcp_echo_timeout."'");
  exec("uci set network.l2tp".$l2tp_no.".defaultroute='".$defaultroute."'");

  if ($metric) {
    exec("uci set network.l2tp".$l2tp_no.".metric='".$metric."'");
  }

  if ($mtu) {
    exec("uci set network.l2tp".$l2tp_no.".mtu='".$mtu."'");
  }

  if ($defaultroute && $defaultroute == "on") {
    exec("uci set network.l2tp".$l2tp_no.".defaultroute='1'");
  } else {
    exec("uci set network.l2tp".$l2tp_no.".defaultroute='0'");
  }
  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
}

function delete_l2tp_vpn($filled_l2tp_no)
{
  exec("uci delete network.l2tp".$filled_l2tp_no);
  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
}

function set_sstp_vpn($sstp_no, $status, $sstp_server, $username, $password, $lcp_echo_interval, $lcp_echo_timeout, $defaultroute, $metric, $mtu)
{
  exec("uci delete network.sstp".$sstp_no);
  exec("uci commit network");

  exec("uci set network.sstp".$sstp_no."=interface");
  exec("uci set network.sstp".$sstp_no.".proto='sstp'");
  exec("uci set network.sstp".$sstp_no.".iface='sstp".$sstp_no."'");
  exec("uci set network.sstp".$sstp_no.".enabled='".$status."'");
  exec("uci set network.sstp".$sstp_no.".username='".$username."'");
  exec("uci set network.sstp".$sstp_no.".password='".$password."'");
  exec("uci set network.sstp".$sstp_no.".server='".$sstp_server."'");
  exec("uci set network.sstp".$sstp_no.".keepalive='".$lcp_echo_interval." ".$lcp_echo_timeout."'");
  exec("uci set network.sstp".$sstp_no.".defaultroute='".$defaultroute."'");

  if ($metric) {
    exec("uci set network.sstp".$sstp_no.".metric='".$metric."'");
  }

  if ($mtu) {
    exec("uci set network.sstp".$sstp_no.".mtu='".$mtu."'");
  }

  if ($defaultroute && $defaultroute == "on") {
    exec("uci set network.sstp".$sstp_no.".defaultroute='1'");
  } else {
    exec("uci set network.sstp".$sstp_no.".defaultroute='0'");
  }
  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
}

function delete_sstp_vpn($filled_sstp_no)
{
  exec("uci delete network.sstp".$filled_sstp_no);
  exec("uci commit network");
  exec("sync");
  exec("/etc/init.d/network reload");
}


function cidr2NetmaskAddr($cidr) {
  $ta = substr ($cidr, strpos ($cidr, '/') + 1) * 1;
  $netmask = str_split (str_pad (str_pad ('', $ta, '1'), 32, '0'), 8);
  foreach ($netmask as &$element)
    $element = bindec ($element);
  return join ('.', $netmask);
}

function fetch_ssl_vpn_config($client_no, $vpn_id, $concentrator_ips, $persist_key, $persist_tun, $metric) {
  try {
    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");

    if ($license_key == "No License Key") {
      return array(False, True);
    }

    $remote_ips = array();
    $success=false;
    foreach ($concentrator_ips as $value) {
      if(!$success) {
        $remote_address = explode(":", $value);

        array_push($remote_ips, $remote_address[0]);
        $ch = curl_init( "https://".$remote_address[0].":".$remote_address[1]."/api/v1/firewall/vpn_client/" );
        $payload = json_encode( array( "vpn_uid" => $vpn_id ) );

        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_USERAGENT, "ACE Agent");
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_POSTREDIR, 7);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 202){
          $success=true;
          $api_ip = $value;
        }
        curl_close($ch);

        $json_response = json_decode($result, true);
      }
    }

    if ($json_response["valid"] == "Yes") {
        $proto = $json_response["protocol"];
        $concentrator_ip = implode(",", $remote_ips);
        $concentrator_port = $json_response["vpn_port"];
        $vpn_passphrase = $json_response["passphrase"];
        $compression_mode = $json_response["use_compression"];
        $pull_mode = 'on';
        $tls_verify = 'on';
        $ca_cert = $json_response["ca_crt"];
        $client_cert = $json_response["client_crt"];
        $client_key = $json_response["client_key"];
        $tls_key = $json_response["ta_vpn_key"];
        $remote_lan_ip = '';
        $remote_lan_subnet = '';
        $metric = '';
        $cipher_mode = $json_response['cipher'];
        $auth_mode = $json_response['auth'];
        $zip_file_type = "0";

        file_put_contents("/etc/openvpn/ssl_ca_client".$client_no.".crt", $ca_cert);
        file_put_contents("/etc/openvpn/ssl_cert_client".$client_no.".crt", $client_cert);
        file_put_contents("/etc/openvpn/ssl_key_client".$client_no.".key", $client_key);
        file_put_contents("/etc/openvpn/ssl_tls_key_client".$client_no.".key", $tls_key);

        $pre = "uci set openvpn.sclient".$client_no.".";
        exec("uci set openvpn.sclient".$client_no."=openvpn");
        exec($pre."dev=stun".$client_no);
        exec($pre."dev_type='tun'");
        exec($pre."verb='3'");
        exec($pre."client='1'");
        exec($pre."remote_cert_tls='server'");
        exec($pre."resolv_retry='infinite'");
        exec($pre."keepalive='10 120'");
        exec($pre."nobind='1'");
        exec($pre."enabled='1'");

        exec($pre."proto=".strtolower($proto));

        if ($cipher_mode != "None") {
          exec($pre."cipher='".$cipher_mode."'");
        } else {
          exec("uci delete openvpn.sclient".$client_no.".cipher");
        }
        if ($auth_mode != "None") {
          exec($pre."auth='".$auth_mode."'");
        } else {
          exec("uci delete openvpn.sclient".$client_no.".auth");
        }

        exec("uci delete openvpn.sclient".$client_no.".remote");
        foreach (explode(",", $concentrator_ip) as $concentrator) {
          exec("uci add_list openvpn.sclient".$client_no.".remote='".$concentrator." ".$concentrator_port."'");
        }
        exec($pre."route_nopull=".get_inverse_int_from_on_off($pull_mode));
        exec($pre."pull=".get_int_from_on_off($pull_mode));

        exec("uci delete openvpn.sclient".$client_no.".route");
        exec("uci delete openvpn.sclient".$client_no.".route_metric");
        if ($pull_mode != "on") {
          if ($remote_lan_ip != "" && $remote_lan_subnet !="") {
            exec("uci add_list openvpn.sclient".$client_no.".route='".$remote_lan_ip." ".$remote_lan_subnet."'");
          }
        }
        exec($pre."route_metric='".$metric."'");

        if ($compression_mode != "None") {
          exec($pre."compress='".$compression_mode."'");
        } else {
          exec("uci delete openvpn.sclient".$client_no.".compress");
        }

        exec($pre."zip_file_type='".$zip_file_type."'");
        exec($pre."persist_key=".get_int_from_on_off($persist_key));
        exec($pre."persist_tun=".get_int_from_on_off($persist_tun));
        exec($pre."tls_client=".get_int_from_on_off($tls_verify));
        exec($pre."key_direction=".get_int_from_on_off($tls_verify));

        exec($pre."ca=/etc/openvpn/ssl_ca_client".$client_no.".crt");
        exec($pre."cert=/etc/openvpn/ssl_cert_client".$client_no.".crt");
        exec($pre."key=/etc/openvpn/ssl_key_client".$client_no.".key");
        if ($tls_verify == "on") {
          exec($pre."tls_auth='/etc/openvpn/ssl_tls_key_client".$client_no.".key ".get_int_from_on_off($tls_verify)."'");
        } else {
          exec("uci delete openvpn.sclient".$client_no.".tls_auth");
        }

        exec("uci delete network.stun".$client_no);
        exec("uci set network.stun".$client_no."=interface");
        exec("uci set network.stun".$client_no.".proto='none'");
        exec("uci set network.stun".$client_no.".ifname='stun".$client_no."'");

        if ($vpn_passphrase) {
          write_passphrase("/etc/config/sslvpn".$client_no."_pass", $vpn_passphrase);
          exec($pre."askpass='/etc/config/sslvpn".$client_no."_pass'");
        } else {
          unlink("/etc/config/sslvpn".$client_no."_pass");
          exec("uci delete openvpn.sclient".$client_no.".askpass");
        }

        set_openvpn_config($client_no);
        set_network_config("stun".$client_no);

        exec("uci set openvpn.sclient".$client_no.".vpn_id=".$vpn_id);
        exec("uci set openvpn.sclient".$client_no.".api_ip=".$api_ip);
        exec("uci set openvpn.sclient".$client_no.".sync_status='success'");
        exec('uci commit openvpn');
        exec("sync");
        return True;
    } else {
      exec("uci set openvpn.sclient".$client_no.".sync_status='failed'");
      exec('uci commit openvpn');
      exec("sync");
    return False;
    }
  }
  catch(Exception $e) {
    return False;
  }
}

function set_ssl_vpn_config($client_no, $status, $vpn_id, $concentrator_ips, $persist_key, $persist_tun, $metric)
{
  $vpn_fetch_status=False;
  if($status == "0") {
    exec("uci set openvpn.sclient".$client_no.".enabled='0'");
    exec("uci set openvpn.sclient".$client_no.".disabled='1'");
    exec("/etc/init.d/openvpn restart");
    return true;
  } else {
    exec("uci set openvpn.sclient".$client_no.".disabled='0'");
    exec("uci set openvpn.sclient".$client_no.".enabled='1'");
    $vpn_fetch_status = fetch_ssl_vpn_config($client_no, $vpn_id, $concentrator_ips, $persist_key, $persist_tun, $metric);
    exec("uci commit openvpn");
    exec("sync");
    if ($vpn_fetch_status) {
      exec("/etc/init.d/openvpn restart");
    }
  }
  return $vpn_fetch_status;
}

function delete_ssl_vpn_from_id($client_no) {
  unlink("/etc/openvpn/ssl_ca_client".$client_no.".crt");
  unlink("/etc/openvpn/ssl_cert_client".$client_no.".crt");
  unlink("/etc/openvpn/ssl_key_client".$client_no.".key");
  unlink("/etc/config/sslvpn".$client_no."_pass");
  exec("uci delete openvpn.sclient".$client_no);
  exec("uci delete network.stun".$client_no);
  set_ssl_vpn_config_from_id($client_no);
  exec("/sbin/ifdown stun".$client_no);
  exec("/etc/init.d/openvpn restart");
}

function set_ssl_vpn_config_from_id($client_no)
{
exec('uci commit openvpn');
exec("sync");
}

function set_tapgre($gre_no, $instance_no, $status, $name, $ipaddr, $peer_ip_addr, $ttl, $network)
{
  exec("logger -t gre_no: ".$gre_no." status: ".$status." name: ".$name." ipaddr: ".$ipaddr." peer_ip_addr: ".$peer_ip_addr." ttl: ".$ttl." network: ".$network);

  if ($gre_no == "-1") {
    exec("uci set network.tapgre".$instance_no."=interface");
  }

  exec("uci set network.tapgre".$instance_no.".enabled='".$status."'");
  exec("uci set network.tapgre".$instance_no.".ipaddr='".$ipaddr."'");
  exec("uci set network.tapgre".$instance_no.".peeraddr='".$peer_ip_addr."'");
  exec("uci set network.tapgre".$instance_no.".name='".$name."'");
  exec("uci set network.tapgre".$instance_no.".ttl='".$ttl."'");
  exec("uci set network.tapgre".$instance_no.".proto='gretap'");
  exec("uci set network.tapgre".$instance_no.".ifname='tapgre".$instance_no."'");
  exec("uci set network.tapgre".$instance_no.".network='".$network."'");

  exec("ifdown tapgre".$gre_no);
  exec("uci commit network");
  exec("ifup tapgre".$gre_no);
}

function delete_tapgre_tunnel($tun_no)
{
  exec("ifdown tapgre".$tun_no);
  exec("uci delete network.tapgre".$tun_no);
  exec("uci commit network");
  exec("/etc/init.d/network reload");
}


?>
