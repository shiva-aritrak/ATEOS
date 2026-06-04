<?php

function set_anexgate_config()
{
  exec("uci commit anexgate");
}

function do_backup_to_cloud($domain)
{
  try {
    $license_key = exec("uci get anexgate.license.key");
    $serial = exec("uci get anexgate.license.serial");
    $macaddr = exec("uci get anexgate.license.macaddr");
    $cloud_domain = exec("uci get anexgate.config.cloud_domain");

    $ch = curl_init( "http://".$domain."/api/ace/backup/" );

    $payload = json_encode( array( "license_key"=> $license_key, "serial"=> $serial, "device_mac"=> $macaddr, 'backup_data'=> base64_encode(file_get_contents("/tmp/backup.bin")) ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "ACE Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

    $result = curl_exec($ch);
    curl_close($ch);

    $json_response = json_decode($result, true);
  }
  catch(Exception $e) {
    exec("logger ".$e->getMessage());
  }
}

function do_check_for_software_upgrade()
{
  try {
    $license_key = exec("uci get anexgate.license.key");
    $current_version = exec("uci get anexgate.software.version");
    $macaddr = exec("uci get anexgate.license.macaddr");
    $cloud_domain = exec("uci get anexgate.config.cloud_domain");
    $ch = curl_init( $cloud_domain."/api/ace/software/check/" );

    $payload = json_encode( array( "license_key"=> $license_key, "version"=> $current_version, "device_mac"=> $macaddr  ) );

    curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt( $ch, CURLOPT_USERAGENT, "ACE Agent");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_POSTREDIR, 7);

    $result = curl_exec($ch);

    curl_close($ch);

    $json_response = json_decode($result, true);

    if ($json_response["update_available"] == "Yes") {
      exec("uci set anexgate.software.update_available='Yes'");
      exec("uci set anexgate.software.update_version='".$json_response["new_version"]."'");
      exec("uci set anexgate.software.download_link='".$json_response["download_link"]."'");
      set_anexgate_config();
      return True;
    } else {
      exec("uci set anexgate.software.update_available='No'");
      set_anexgate_config();
      return False;
    }
  }
  catch(Exception $e) {
    return False;
  }
}

function download_software_upgrade()
{
  backup_to_cloud();
  exec("rm -rf /tmp/pupgrade.bin");
  $link_fetch = exec("uci get anexgate.software.download_link");
  $out = "";
  $ret = 0;
  exec("uclient-fetch -O /tmp/pupgrade.bin ".$link_fetch, $out, $ret);
  return $ret;
}

function check_for_software_upgrade()
{
  $status = do_check_for_software_upgrade();
}

function backup_to_cloud($domain)
{
  exec("sysupgrade -b /tmp/backup.bin");
  $status = do_backup_to_cloud($domain);
}

?>
