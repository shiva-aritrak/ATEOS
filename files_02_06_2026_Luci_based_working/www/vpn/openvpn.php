<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | OpenVPN</title>
</head>

<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php

$client_id = 1;
$out = "";
$ret = 99;
$html_out = "";

if(count($_POST) > 0) {
  $client_no = $_POST["client_no"];
  $status = $_POST["status"];
  $proto = $_POST["proto"];
  $concentrator_ip = $_POST["concentrator_ip"];
  $concentrator_port = $_POST["concentrator_port"];
  $vpn_passphrase = $_POST["vpn_passphrase"];
  $compression_mode = $_POST["compression_mode"];
  $pull_mode = $_POST["pull_mode"];
  $persist_key = $_POST["persist_key"];
  $persist_tun = $_POST["persist_tun"];
  $tls_verify = $_POST["tls_verify"];
  $ca_cert = $_POST["ca_cert"];
  $client_cert = $_POST["client_cert"];
  $client_key = $_POST["client_key"];
  $tls_key = $_POST["tls_key"];
  $remote_lan_ip = $_POST["remote_lan_ip"];
  $remote_lan_subnet = $_POST["remote_lan_subnet"];
  $metric = $_POST["metric"];
  $cipher_mode = $_POST["cipher_mode"];
  $auth_mode = $_POST["auth_mode"];
  $zip_file_type = $_POST["zip_file_type"];

  if ($zip_file_type == "1") {
    move_uploaded_file($_FILES['zip_file']['tmp_name'], "/tmp/ssl_vpn.zip");
    $zip = new ZipArchive;
    if ($zip->open("/tmp/ssl_vpn.zip") === TRUE) {
      $zip->extractTo('/tmp/ssl_vpn/');
      $zip->close();
    }
    foreach (glob("/tmp/ssl_vpn/*") as $file_path) {
      if( (substr( $file_path, 0, 16 ) === "/tmp/ssl_vpn/ca_") && (substr( $file_path, -4 ) === ".crt") ) {
        copy($file_path, "/etc/openvpn/ca_client".$client_no.".crt");
      }
      else if( (substr( $file_path, 0, 16 ) != "/tmp/ssl_vpn/ca_") && (substr( $file_path, -4 ) === ".crt") ) {
        copy($file_path, "/etc/openvpn/cert_client".$client_no.".crt");
      }
      else if( $file_path != "/tmp/ssl_vpn/ta.key" && (substr( $file_path, -4 ) === ".key") ) {
        copy($file_path, "/etc/openvpn/key_client".$client_no.".key");
      }
      else if( $file_path === "/tmp/ssl_vpn/ta.key" ) {
        $tls_key = file_get_contents("/tmp/ssl_vpn/ta.key");
        copy($file_path, "/etc/openvpn/tls_key_client".$client_no.".key");
      }
    }
  } else {
    if(!empty($_FILES['ca_cert'])) {
      move_uploaded_file($_FILES['ca_cert']['tmp_name'], "/etc/openvpn/ca_client".$client_no.".crt");
    }
    if(!empty($_FILES['client_cert']))
    {
      move_uploaded_file($_FILES['client_cert']['tmp_name'], "/etc/openvpn/cert_client".$client_no.".crt");
    }
    if(!empty($_FILES['client_key']))
    {
      move_uploaded_file($_FILES['client_key']['tmp_name'], "/etc/openvpn/key_client".$client_no.".key");
    }
    if(!empty($_FILES['tls_key']))
    {
      $tls_key = file_get_contents($_FILES['tls_key']['tmp_name']);
      if (strpos($tls_key, '762b2ce8de65bde9831a6cbd6e131c50') !== false) {
        move_uploaded_file($_FILES['tls_key']['tmp_name'], "/etc/openvpn/tls_key_client".$client_no.".key");
      } 
    }
  }

  configure_openvpn_vpn($client_no, $status, $proto, $concentrator_ip, $concentrator_port, $vpn_passphrase, $compression_mode, $pull_mode, $persist_key, $persist_tun, $tls_verify, $remote_lan_ip, $remote_lan_subnet, $metric, $cipher_mode, $auth_mode, $zip_file_type);
}

if(count($_GET) > 0) {
  $filled_client_no = $_GET["client_no"];
  $action = $_GET["action"];

  if ( $action == "delete" ) {
    delete_openvpn_vpn($filled_client_no);
    echo '<script>window.location.href = "openvpn.php";</script>';
    exit;
  }
}

exec("uci show openvpn.oclient".$client_id , $out, $ret);
while ( $client_id <= $MAX_VPN_CLIENTS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$client_id.'</td>';
  if ($ret != 0) {
    $html_out = $html_out.'  <td>Not Configured</td><td></td><td></td><td></td><td></td><td></td><td></td>';
  } else {
    $html_out = $html_out.'  <td>'.show_enabled_disabled(exec("uci get openvpn.oclient".$client_id.".enabled")).'</td>';
    $html_out = $html_out.'  <td>'.strtoupper(exec("uci get openvpn.oclient".$client_id.".proto")).'</td>';
    $html_out = $html_out.'  <td>'.str_replace("'", "", exec("uci get openvpn.oclient".$client_id.".remote")).'</td>';
    $html_out = $html_out.'  <td>'.show_no_yes(exec("uci get openvpn.oclient".$client_id.".route_nopull")).'</td>';
    $html_out = $html_out.'  <td>'.show_yes_no(exec("uci get openvpn.oclient".$client_id.".persist_key")).'</td>';
    $html_out = $html_out.'  <td>'.show_yes_no(exec("uci get openvpn.oclient".$client_id.".persist_tun")).'</td>';
    $html_out = $html_out.'  <td>'.strtoupper(exec("uci get openvpn.oclient".$client_id.".compress")).'</td>';
  }
  $html_out = $html_out.'  <td><a href="openvpn.php?client_no='.$client_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="openvpn.php?action=delete&client_no='.$client_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $client_id += 1;
  exec("uci show openvpn.oclient".$client_id , $out, $ret);
}

?>

<?php include '/www/sidenav.php' ?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">SSL VPN Client Configuration</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="ssl_vpn_form" action="openvpn.php" method="post">
                  <table>
                      <tr>
                        <td>Client</td>
                        <td>
                          <select required id="client_no_id" class="form-control" name="client_no">
                            <?php
                            for ($x = 1; $x <= $MAX_VPN_CLIENTS; $x++) {
                              if ($x == $filled_client_no) {
                                echo '<option selected=selected value="'.$x.'">'.$x.'</option>';
                              } else {
                                echo '<option value="'.$x.'">'.$x.'</option>';
                              }
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $client_status = exec("uci get openvpn.oclient".$filled_client_no.".enabled");
                            if ($client_status == "1") {
                              echo '<input name="status" value="1" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($client_status == "0") {
                              echo '<input name="status" value="0" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="0" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Disabled</span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Protocol</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $client_proto = exec("uci get openvpn.oclient".$filled_client_no.".proto");
                            if ($client_proto == "tcp") {
                              echo '<input name="proto" value="TCP" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="proto" value="TCP" type="radio" required>';
                            }
                            ?>
                            <span><i></i>TCP</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($client_proto == "udp") {
                              echo '<input name="proto" value="udp" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="proto" value="udp" type="radio" required>';
                            }
                            ?>
                            <span><i></i>UDP</span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Concentrator IP</td>
                        <td><input required type="text" id="concentrator_ip_id" name="concentrator_ip" class="form-control" value="<?php  $get=exec("uci get openvpn.oclient".$filled_client_no.".remote");
                        $out=array();
                        foreach (explode("' '", $get) as $concentrator) {
                          array_push($out, explode(' ', trim($concentrator, "'"))[0]);
                        }  echo implode(",",$out); ?>"></td>
                      </tr>
                      <tr>
                        <td>Port</td>
                        <td><input required pattern="^(102[4-9]|10[3-9]\d|1[1-9]\d{2}|[2-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$" title="Must contain a valid port number" type="text" id="concentrator_port_id" name="concentrator_port" class="form-control" value="<?php  $get=exec("uci get openvpn.oclient".$filled_client_no.".remote"); echo trim(array_pop(explode(' ', $get)), "'")?>"></td>
                      </tr>

                      <tr>
                        <td>Cipher</td>
                        <td>
                          <select required id="cipher" class="form-control" name="cipher_mode">
                            <?php
                            $client_cipher = exec("uci get openvpn.oclient".$filled_client_no.".cipher");
                            foreach ($OPENVPN_CIPHERS as $CIPHER) {
                              if ( strtoupper($client_cipher) == $CIPHER) {
                                echo '<option selected=selected value="'.$CIPHER.'">'.$CIPHER.'</option>';
                              } else {
                                echo '<option value="'.$CIPHER.'">'.$CIPHER.'</option>';
                              }
                            }
                            ?>
                          </select>
                        </td>
                      </tr>

                      <tr>
                        <td>Authentication</td>
                        <td>
                          <select required id="auth" class="form-control" name="auth_mode">
                            <?php
                            $client_alg = exec("uci get openvpn.oclient".$filled_client_no.".auth");
                            foreach ($OPENVPN_AUTH_ALG as $AUTH_ALG) {
                              if ( strtoupper($client_alg) == $AUTH_ALG) {
                                echo '<option selected=selected value="'.$AUTH_ALG.'">'.$AUTH_ALG.'</option>';
                              } else {
                                echo '<option value="'.$AUTH_ALG.'">'.$AUTH_ALG.'</option>';
                              }
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td>Passphrase</td>
                        <td><input type="text" id="vpn_passphrase_id" name="vpn_passphrase" class="form-control" value="<?php if (file_exists('/etc/config/sslvpn'.$filled_client_no.'_pass')) { echo file_get_contents('/etc/config/sslvpn'.$filled_client_no.'_pass'); } ?>"></td>
                      </tr>
                      <tr>
                        <td>Compression</td>
                        <td>
                          <select required id="compression" class="form-control" name="compression_mode">
                            <?php
                            $client_compression = exec("uci get openvpn.oclient".$filled_client_no.".compress");
                            if ( $client_compression == "lzo" ) {
                              echo '<option value="None">None</option>';
                              echo '<option selected=selected value="lzo">LZO</option>';
                              echo '<option value="lz4">LZ4</option>';
                            } else if ($client_compression == "lz4") {
                              echo '<option value="None">None</option>';
                              echo '<option value="lzo">LZO</option>';
                              echo '<option selected=selected value="lz4">LZ4</option>';
                            } else {
                              echo '<option selected=selected value="None">None</option>';
                              echo '<option value="lzo">LZO</option>';
                              echo '<option value="lz4">LZ4</option>';
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                      <tr>
                        <td>Route Pull</td>
                        <td>
                          <label class="fancy-checkbox">
                            <?php
                            $client_pull_mode = exec("uci get openvpn.oclient".$filled_client_no.".route_nopull");
                            if ($client_pull_mode == "1") {
                              echo '<input name="pull_mode" type="checkbox">';
                            } else {
                              echo '<input name="pull_mode" checked=checked type="checkbox">';
                            }
                            ?>
                            <span></span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Remote LAN</td>
                        <td><input type="text" id="remote_lan_ip_id" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" name="remote_lan_ip" class="form-control" value="<?php  $get=exec("uci get openvpn.oclient".$filled_client_no.".route"); echo str_replace("'", "", array_shift(explode(' ', $get)))?>"></td>
                      </tr>
                      <tr>
                        <td>Remote LAN Subnet</td>
                        <td><input pattern="^(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$" title="Must contain a valid Subnet Mask"  type="text" id="remote_lan_subnet_id" name="remote_lan_subnet" class="form-control" value="<?php  $get=exec("uci get openvpn.oclient".$filled_client_no.".route"); echo str_replace("'", "", array_pop(explode(' ', $get))) ?>"></td>
                      </tr>
                      <tr>
                        <td>Metric</td>
                        <td><input type="text" id="metric_id" name="metric" class="form-control" value="<?php  $get=exec("uci get openvpn.oclient".$filled_client_no.".route_metric"); echo $get; ?>"></td>
                      </tr>
                      <tr>
                        <td>Persist Key</td>
                        <td>
                          <label class="fancy-checkbox">
                            <?php
                            $client_persist_key = exec("uci get openvpn.oclient".$filled_client_no.".persist_key");
                            if ($client_persist_key == "1") {
                              echo '<input name="persist_key" checked=checked type="checkbox">';
                            } else {
                              echo '<input name="persist_key" type="checkbox">';
                            }
                            ?>
                            <span></span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Persist Tunnel</td>
                        <td>
                          <label class="fancy-checkbox">
                            <?php
                            $client_persist_tun = exec("uci get openvpn.oclient".$filled_client_no.".persist_tun");
                            if ($client_persist_tun == "1") {
                              echo '<input name="persist_tun" checked=checked type="checkbox">';
                            } else {
                              echo '<input name="persist_tun" type="checkbox">';
                            }
                            ?>
                            <span></span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>TLS Verify Client</td>
                        <td>
                          <label class="fancy-checkbox">
                            <?php
                            $client_tls_verify = exec("uci get openvpn.oclient".$filled_client_no.".tls_client");
                            if ($client_tls_verify == "1") {
                              echo '<input name="tls_verify" checked=checked type="checkbox">';
                            } else {
                              echo '<input name="tls_verify" type="checkbox">';
                            }
                            ?>
                            <span></span>
                          </label>
                        </td>
                      </tr>
                      
                      <tr>
                        <td>File Type</td>
                        <td>
                          <label class="radio-inline">
                            <input name="zip_file_type" value="1" id="zip_file_type_enabled_id" type="radio" onclick="javascript:file_type_choice();">
                            <span><i></i>ZIP File</span>
                          </label>
                          <label class="radio-inline">
                            <input name="zip_file_type" value="0" id="zip_file_type_disabled_id" type="radio" onclick="javascript:file_type_choice();" checked>
                            <span><i></i>Individual Certificates</span>
                          </label>
                        </td>
                      </tr>

                      <tr id="row_zip_file_id">
                        <td>Certificate ZIP Files</td>
                        <td>
                          <input name="zip_file" type="file" />
                          <p class="help-block">
                            <?php
                            $zip_file_type = exec("uci get openvpn.oclient".$filled_client_no.".zip_file_type");
                            if ($zip_file_type == "1") {
                              echo '<em>Existing certificates</em>';
                            } else {
                              echo '<em>Zip File for Certificates</em>';
                            }
                            ?>
                          </p>
                        </td>
                      </tr>

                      <tr id="row_ca_cert_id">
                        <td>CA Certificate</td>
                        <td>
                          <input name="ca_cert" type="file" />
                          <p class="help-block">
                            <?php
                            $ca_cert = exec("uci get openvpn.oclient".$filled_client_no.".ca");
                            if ($ca_cert) {
                              $ca_cert_name = array_pop(explode('/', $ca_cert));
                              echo '<em>Existing: '.$ca_cert_name.'</em>';
                            } else {
                              echo '<em>Certificate of Certificate Authority</em>';
                            }
                            ?>
                          </p>
                        </td>
                      </tr>
                      <tr id="row_client_cert_id">
                        <td>Client Certificate</td>
                        <td>
                          <input name="client_cert" type="file" />
                          <p class="help-block">
                            <?php
                            $ca_cert = exec("uci get openvpn.oclient".$filled_client_no.".cert");
                            if ($ca_cert) {
                              $ca_cert_name = array_pop(explode('/', $ca_cert));
                              echo '<em>Existing: '.$ca_cert_name.'</em>';
                            } else {
                              echo '<em>Certificate of Client</em>';
                            }
                            ?>
                          </p>
                        </td>
                      </tr>
                      <tr id="row_client_key_id">
                        <td>Client Key</td>
                        <td>
                          <input type="file" id="client_key" name="client_key" />
                          <p class="help-block">
                            <?php
                            $ca_cert = exec("uci get openvpn.oclient".$filled_client_no.".key");
                            if ($ca_cert) {
                              $ca_cert_name = array_pop(explode('/', $ca_cert));
                              echo '<em>Existing: '.$ca_cert_name.'</em>';
                            } else {
                              echo '<em>Client Key</em>';
                            }
                            ?>
                          </p>
                        </td>
                      </tr>
                      <tr id="row_tls_key_id">
                        <td>TLS Key</td>
                        <td>
                          <input name="tls_key" type="file" />
                          <p class="help-block">
                            <?php
                            $ca_cert = exec("uci get openvpn.oclient".$filled_client_no.".tls_auth");
                            if ($ca_cert) {
                              $ca_cert_name = array_pop(explode('/', $ca_cert));
                              echo '<em>Existing: '.$ca_cert_name.'</em>';
                            } else {
                              echo '<em>No TLS Authentication Key</em>';
                            }
                            ?>
                            <em></em>
                          </p>
                        </td>
                      </tr>
                    </table>
                    <br/>
                    <div class="row">
                      <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Save</button>
                      </div>
                      <div class="col-md-6">
                        <button type="button" class="btn btn-danger">Reset</button>
                      </div>
                    </div>
                  </form>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Current Clients (Max: <?php echo $MAX_VPN_CLIENTS; ?>)</h3>
              </div>
              <div class="panel-body">
                <div id="ssl_vpn_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>Protocol</th>
                        <th>Server Port</th>
                        <th>Route Pull</th>
                        <th>Persist Key</th>
                        <th>Persist Tunnel</th>
                        <th>Compress</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $html_out;
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
  
  <script type="text/javascript">
  window.onload=preselected;
  function preselected()
  {
    <?php
    $zip_file_type = exec("uci -q get openvpn.oclient".$filled_client_no.".zip_file_type");
    if ($zip_file_type == "1") { echo 'var zip_file_type = 1;'; } else { echo 'var zip_file_type = 0;'; }
    ?>

    if(zip_file_type == 1)
    {
      document.getElementById('row_zip_file_id').style.display = '';
      document.getElementById('row_ca_cert_id').style.display = 'none';
      document.getElementById('row_client_cert_id').style.display = 'none';
      document.getElementById('row_client_key_id').style.display = 'none';
      document.getElementById('row_tls_key_id').style.display = 'none';
      document.getElementById('zip_file_type_enabled_id').checked = true;
      document.getElementById('zip_file_type_disabled_id').checked = false;
    }
    else
    {
      document.getElementById('row_zip_file_id').style.display = 'none';
      document.getElementById('row_ca_cert_id').style.display = '';
      document.getElementById('row_client_cert_id').style.display = '';
      document.getElementById('row_client_key_id').style.display = '';
      document.getElementById('row_tls_key_id').style.display = '';
      document.getElementById('zip_file_type_enabled_id').checked = false;
      document.getElementById('zip_file_type_enabled_id').checked = true;
    }
  }

  function file_type_choice()
  {
    if (document.getElementById('zip_file_type_enabled_id').checked==true)
    {
      document.getElementById('row_zip_file_id').style.display = '';
      document.getElementById('row_ca_cert_id').style.display = 'none';
      document.getElementById('row_client_cert_id').style.display = 'none';
      document.getElementById('row_client_key_id').style.display = 'none';
      document.getElementById('row_tls_key_id').style.display = 'none';
    }
    else if (document.getElementById('zip_file_type_disabled_id').checked==true)
    {
      document.getElementById('row_zip_file_id').style.display = 'none';
      document.getElementById('row_ca_cert_id').style.display = '';
      document.getElementById('row_client_cert_id').style.display = '';
      document.getElementById('row_client_key_id').style.display = '';
      document.getElementById('row_tls_key_id').style.display = '';
    }
  }
  </script>