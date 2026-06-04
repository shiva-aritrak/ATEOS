<?php

include '/www/lib/system.php';

function do_license_register()
{
  try {
    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");
    $macaddr = exec("uci get anexgate.license.macaddr");
    $cloud_domain = exec("uci get anexgate.config.cloud_domain");

    $ch = curl_init( "http://".$cloud_domain."/api/ace/license/register/" );

    $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr  ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "ACE License Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

    $result = curl_exec($ch);
    curl_close($ch);

    $json_response = json_decode($result, true);

    if ($json_response && $json_response["response"] != 0 ) {
      exec("uci set anexgate.license.valid='No'");
      exec("uci set anexgate.license.product='ACE'");
      exec("uci delete anexgate.license.model");
      exec("uci delete anexgate.license.ssl_vpn_max");
      exec("uci delete anexgate.license.ipsec_vpn_max");
      exec("uci delete anexgate.license.gre_vpn_max");
      exec("uci delete anexgate.license.registered_date");
      exec("uci delete anexgate.license.license_type");
      exec("uci delete anexgate.license.license_expiry");
      exec("uci delete anexgate.license.support_expiry");
      exec("uci delete anexgate.license.daily_checkin");

      exec("uci set anexgate.customer.name='Unregistered'");
      exec("uci set anexgate.customer.address='Unregistered'");
      exec("uci set anexgate.customer.city='Unregistered'");
      exec("uci set anexgate.customer.state='Unregistered'");
      exec("uci set anexgate.customer.country='Unregistered'");
    } else if ($json_response) {
      exec("uci set anexgate.license.product='".$json_response["product"]."'");
      exec("uci set anexgate.license.valid='".$json_response["valid"]."'");
      exec("uci set anexgate.license.model='".$json_response["model"]."'");
      exec("uci set anexgate.license.ssl_vpn_max='".$json_response["ssl_vpn_max"]."'");
      exec("uci set anexgate.license.ipsec_vpn_max='".$json_response["ipsec_vpn_max"]."'");
      exec("uci set anexgate.license.gre_vpn_max='".$json_response["gre_vpn_max"]."'");
      exec("uci set anexgate.license.user_limit='".$json_response["user_limit"]."'");
      exec("uci set anexgate.license.registered_date='".$json_response["registered_date"]."'");
      exec("uci set anexgate.license.license_type='".$json_response["license_type"]."'");
      exec("uci set anexgate.license.license_expiry='".$json_response["license_expiry"]."'");
      exec("uci set anexgate.license.support_expiry='".$json_response["support_expiry"]."'");
      exec("uci set anexgate.license.daily_checkin='".$json_response["daily_checkin"]."'");

      exec("uci set anexgate.customer.name='".$json_response["customer_name"]."'");
      exec("uci set anexgate.customer.address='".$json_response["customer_address"]."'");
      exec("uci set anexgate.customer.city='".$json_response["customer_city"]."'");
      exec("uci set anexgate.customer.state='".$json_response["customer_state"]."'");
      exec("uci set anexgate.customer.country='".$json_response["customer_country"]."'");
      exec("uci set anexgate.customer.location='".$json_response["location"]."'");
      exec("uci set anexgate.license.failed_attempts='0'");
    } else {
      return array(False, False);
    }

    exec("uci commit anexgate");
    exec("sync");

    if ($json_response["valid"] == "Yes" && !$json_response["disable"]) {
      if ($json_response["anexconnect"]) {
        enable_anexconnect($json_response['anexconnect_domain']);
      } else {
        disable_anexconnect();
      }

      if ($json_response["anexhub"]) {
        enable_anexhub();
      } else {
        disable_anexhub();
      }

      if ($json_response["anexfuse"]) {
        enable_anexfuse();
      } else {
        disable_anexfuse();
      }

      if ($json_response["hotspot"]) {
        enable_hotspot();
      } else {
        disable_hotspot();
      }

      exec("uci commit anexgate");
      exec("uci commit anexhub");
      exec("sync");

      return array(True, True);
    }
    return array(False, True);
  }
  catch(Exception $e) {
    exec("logger -t license Error During License Registration ".$e->getMessage());
    return array(False, False);
  }
}

function do_license_check()
{
  try {

    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");
    $macaddr = exec("uci get anexgate.license.macaddr");
    $cloud_domain = exec("uci get anexgate.config.cloud_domain");

    if ($license_key == "No License Key") {
      return array(False, True);
    }

    $ch = curl_init( "http://".$cloud_domain."/api/ace/license/check/" );

    $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr  ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "ACE License Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

    $result = curl_exec($ch);

    curl_close($ch);

    $json_response = json_decode($result, true);

    if ($json_response["valid"] == "Yes") {
      exec("uci set anexgate.license.license_expiry='".$json_response["license_expiry"]."'");
      exec("uci set anexgate.license.support_expiry='".$json_response["support_expiry"]."'");
      exec("uci commit anexgate");
      exec("sync");
      return array(True, False);
    } else if ($json_response["valid"] == "No" && $json_response["disable"]) {
      return array(False, True);
    } else {
      return array(False, False);
    }
  }
  catch(Exception $e) {
    return array(False, False);
  }
}

function enable_anexconnect($anexconnect_domain)
{
  exec("/etc/init.d/autossh enable");
  exec("uci set anexgate.connect.feature_enabled='Yes'");
  set_connect_config("1", $anexconnect_domain);
}

function enable_anexhub()
{
  exec("uci set anexhub.hub.feature_enabled='Yes'");
  exec("/etc/init.d/hub enable");
  exec("/etc/init.d/hub start");
}

function enable_anexfuse()
{
  exec("uci set anexgate.anexfuse.feature_enabled='Yes'");
  exec("/etc/init.d/fuse enable");
  exec("/etc/init.d/fuse start");
}

function enable_hotspot()
{
  exec("uci set anexgate.anexspot.feature_enabled='Yes'");
  exec("/etc/init.d/opennds enable");
  exec("/etc/init.d/opennds start");
}

function disable_anexconnect()
{
  exec("uci set anexgate.connect.feature_enabled='No'");
  set_connect_config("0", "anexprivate.sspl.securens.in");
  exec("/etc/init.d/autossh stop");
  exec("/etc/init.d/autossh disable");
}

function disable_anexhub()
{
  exec("uci set anexhub.hub.feature_enabled='No'");
  exec("/etc/init.d/hub stop");
  exec("/etc/init.d/hub disable");
}

function disable_anexfuse()
{
  exec("uci set anexgate.anexfuse.feature_enabled='No'");
  exec("/etc/init.d/fuse stop");
  exec("/etc/init.d/fuse disable");
}

function disable_hotspot()
{
  exec("uci set anexgate.anexspot.feature_enabled='No'");
  exec("/etc/init.d/hub stop");
  exec("/etc/init.d/hub disable");
}

function disable_main_functionality()
{
  exec("/etc/init.d/firewall stop");
  exec("/etc/init.d/openvpn stop");
  exec("/etc/init.d/mwan3 stop");

  exec("/etc/init.d/firewall disable");
  exec("/etc/init.d/openvpn disable");
  exec("/etc/init.d/mwan3 disable");
}

function enable_main_functionality()
{
  exec("/etc/init.d/firewall enable");
  exec("/etc/init.d/mwan3 enable");
  exec("/etc/init.d/openvpn enable");

  exec("/etc/init.d/firewall start");
  exec("/etc/init.d/mwan3 start");
  exec("/etc/init.d/openvpn start");
}

function register_license()
{
  $status = do_license_register();
  if($status[0] && $status[1]) {
    exec("logger -t license License server reached and license validated");
    enable_main_functionality();
  } else if (!$status[0] && $status[1]) {
    exec("logger -t license License server reached and license has expired");
    $license_type = exec("uci get anexgate.license.license_type");
    if ($license_type != "Perpetual") {
      disable_main_functionality();
    }
  }
  else {
    exec("logger -t license License server not reachable");
  }
}

function check_license()
{
  $license_expiry = exec("uci -q get anexgate.license.license_expiry");
  $present_date = exec("date \"+%Y-%m-%d\"");
  $license_type = exec("uci -q get anexgate.license.license_type");
  $failed_attempts = exec("uci get anexgate.license.failed_attempts");
  $failed_attempts = (int)$failed_attempts;

  $status = do_license_check();
  if($status[0]) {
    $failed_attempts = 0;
  } else {
    if ($license_type == "Demo" && strtotime($license_expiry) <= strtotime($present_date)) {
        $failed_attempts++;
      } else {
        $failed_attempts = 0;
      } 
  }
  exec("uci set anexgate.license.failed_attempts='".$failed_attempts."'");
  exec("uci commit anexgate");

  if ($failed_attempts >= 20 || $status[1]) {
    $license_type = exec("uci get anexgate.license.license_type");
    if ($license_type != "Perpetual") {
      disable_main_functionality();
    }
  }
}

function register_license_offline()
{
  $status = do_register_license_offline();
  if($status[0] && $status[1]) {
    exec("logger -t license License key file valid and license validated");
    enable_main_functionality();
  } else if (!$status[0] && $status[1]) {
    exec("logger -t license License key file valid but license has expired");
    $license_type = exec("uci get anexgate.license.license_type");
    if ($license_type != "Perpetual") {
      disable_main_functionality();
    }
  }
  else {
    exec("logger -t license License key file is invalid");
  }
}

function do_register_license_offline()
{
  try {

    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");
    $macaddr = exec("uci get anexgate.license.macaddr");

    $passphrase = md5($license_key);

    $filename = "/tmp/licensekey.acekey";

    if (!file_exists($filename)) {
      return array(False, False);
    }

    $fp = fopen($filename, "r");
    $data = fread($fp, filesize($filename));
    fclose($fp);

    $secret_key = hex2bin($passphrase);
    $json = json_decode(base64_decode($data));
    $iv = base64_decode($json->{'iv'});
    $encrypted_64 = $json->{'data'};
    $data_encrypted = base64_decode($encrypted_64);
    $decrypted = openssl_decrypt($data_encrypted, 'aes-128-cbc', $secret_key, OPENSSL_RAW_DATA, $iv);

    if (!$decrypted) {
      return array(False, False);
    }

    $license_data = json_decode($decrypted);

    if ($macaddr != $license_data->{"device_mac"} && $serial != $license_data->{'serial'}) {
      return array(False, False);
    }

    exec("uci set anexgate.license.product='".$license_data->{"product"}."'");
    exec("uci set anexgate.license.valid='".$license_data->{"valid"}."'");
    exec("uci set anexgate.license.model='".$license_data->{"model"}."'");
    exec("uci set anexgate.license.ssl_vpn_max='".$license_data->{"ssl_vpn_max"}."'");
    exec("uci set anexgate.license.ipsec_vpn_max='".$license_data->{"ipsec_vpn_max"}."'");
    exec("uci set anexgate.license.gre_vpn_max='".$license_data->{"gre_vpn_max"}."'");
    exec("uci set anexgate.license.user_limit='".$license_data->{"user_limit"}."'");
    exec("uci set anexgate.license.registered_date='".$license_data->{"registered_date"}."'");
    exec("uci set anexgate.license.license_type='".$license_data->{"license_type"}."'");
    exec("uci set anexgate.license.license_expiry='".$license_data->{"license_expiry"}."'");
    exec("uci set anexgate.license.support_expiry='".$license_data->{"support_expiry"}."'");
    exec("uci set anexgate.license.daily_checkin='".$license_data->{"daily_checkin"}."'");

    exec("uci set anexgate.customer.name='".$license_data->{"customer_name"}."'");
    exec("uci set anexgate.customer.address='".$license_data->{"customer_address"}."'");
    exec("uci set anexgate.customer.city='".$license_data->{"customer_city"}."'");
    exec("uci set anexgate.customer.state='".$license_data->{"customer_state"}."'");
    exec("uci set anexgate.customer.country='".$license_data->{"customer_country"}."'");
    exec("uci set anexgate.customer.location='".$license_data->{"location"}."'");
    exec("uci commit anexgate");
    exec("sync");

    if ($license_data->{"valid"} == "Yes" && !$license_data->{"disable"}) {
      if ($license_data->{"anexconnect"}) {
        enable_anexconnect($license_data->{'anexconnect_domain'});
      } else {
        disable_anexconnect();
      }

      if ($license_data->{"anexhub"}) {
        enable_anexhub();
      } else {
        disable_anexhub();
      }

      if ($license_data->{"anexfuse"}) {
        enable_anexfuse();
      } else {
        disable_anexfuse();
      }

      if ($license_data->{"hotspot"}) {
        enable_hotspot();
      } else {
        disable_hotspot();
      }

      exec("uci commit anexgate");
      exec("uci commit anexhub");
      exec("sync");

      return array(True, True);
    }
  }
  catch(Exception $e) {
    exec("logger -t license Error During License Registration ".$e->getMessage());
    return array(False, False);
  }
  // return $decrypted;
}

function set_license_key($license_key, $license_mode)
{
  exec("uci set anexgate.license.key='".$license_key."'");
  exec("uci set anexgate.license.license_mode='".$license_mode."'");
  exec("uci commit anexgate");
}
?>
