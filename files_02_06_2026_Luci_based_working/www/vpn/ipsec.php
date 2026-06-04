<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | IPSec</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php

$error = "";

if(count($_GET) > 0) {
  $filled_tun_no = $_GET["tunnel_no"];
  $action = $_GET["action"];

  if ( $action == "delete" ) {
    delete_ipsec_tunnel($filled_tun_no);
    echo '<script>window.location.href = "/vpn/ipsec.php";</script>';
    exit;
  }
}

if(count($_POST) > 0) {
  $tunnel_no = $_POST["tunnel_no"];
  $enc_alg = $_POST["enc_alg"];
  $hash_alg = $_POST["hash_alg"];
  $auth_method = $_POST["auth_method"];
  $dh_group = $_POST["dh_group"];
  $p2_dh_group = $_POST["p2_dh_group"];
  $p2_enc_alg = $_POST["p2_enc_alg"];
  $p2_hash_alg = $_POST["p2_hash_alg"];
  $remote_ip = $_POST["remote_ip"];
  $exchange_mode = $_POST["exchange_mode"];
  $local_gateway = $_POST["local_gateway"];
  $local_net = $_POST["local_net"];
  $remote_net = $_POST["remote_net"];
  $pre_shared_key = $_POST["pre_shared_key"];
  $local_id = $_POST["local_id"];
  $status = $_POST["status"];
  $key_exchange_mode = $_POST["key_exchange_mode"];
  $remote_ident = $_POST["remote_ident"];
  $ike_lifetime = $_POST["ike_lifetime"];
  $lifetime = $_POST["lifetime"];
  $keyingtries = $_POST["keyingtries"];
  $dpddelay = $_POST["dpddelay"];
  $dpdaction = $_POST["dpdaction"];
  $dpdtimeout = $_POST["dpdtimeout"];
  $allow_lan = $_POST["allow_lan"];
  $log_level = $_POST["log_level"];
  $passthrough_lan = $_POST["passthrough_lan"];
  $passthrough_lan_subnet = $_POST["passthrough_lan_subnet"];
  $esp_ah_mode = $_POST["esp_ah_mode"];
  $pingcount = $_POST["pingcount"];
  $track_ips = $_POST["track_ips"];
  $backup_tunnel = $_POST["backup_tunnel"];

  if (count(array_filter($track_ips)) < $pingcount) {
    $error = "No of Ping Responses cannot be more than Tracking IPs";
  }
  else {
    set_ipsec_config($tunnel_no, $exchange_mode, $enc_alg, $hash_alg, $auth_method, $dh_group, $p2_dh_group, $p2_enc_alg, $p2_hash_alg, $local_gateway, $remote_ip, $local_net, $remote_net, $pre_shared_key, $log_level, $local_id, $key_exchange_mode, $status, $remote_ident, $ike_lifetime, $lifetime, $keyingtries, $dpddelay, $dpdaction, $dpdtimeout, $allow_lan, $passthrough_lan, $passthrough_lan_subnet, $esp_ah_mode, $pingcount, $track_ips, $backup_tunnel);
  }
}
?>

<?php

$ipsec_id = 1;
$out = "";
$ret = 99;
$html_out = "";


exec("uci show ipsec.tun".$ipsec_id."_remote", $out, $ret);
while ( $ipsec_id <= $MAX_IPSEC_TUNNELS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$ipsec_id.'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get ipsec.tun".$ipsec_id."_remote.gateway").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get ipsec.tun".$ipsec_id.".remote_subnet").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get ipsec.tun".$ipsec_id.".local_subnet").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get ipsec.tun".$ipsec_id."_p1.dh_group").'</td>';
  $html_out = $html_out.'  <td>'.strtoupper(exec("uci get ipsec.tun".$ipsec_id."_p1.encryption_algorithm"))." ".strtoupper(exec("uci get ipsec.tun".$ipsec_id."_p1.hash_algorithm")).'</td>';
  $html_out = $html_out.'  <td>'.strtoupper(exec("uci get ipsec.tun".$ipsec_id."_p2.encryption_algorithm"))." ".strtoupper(exec("uci get ipsec.tun".$ipsec_id."_p2.authentication_algorithm")).'</td>';
  $html_out = $html_out.'  <td><a href="ipsec.php?tunnel_no='.$ipsec_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="ipsec.php?action=delete&tunnel_no='.$ipsec_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $ipsec_id += 1;
  exec("uci show ipsec.tun".$ipsec_id."_remote", $out, $ret);
}


?>
<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">IPSec</h3>
              </div>
              <div class="panel-body">
                <?php 
                if($error) {
                  echo '<div id="alertwindow" class="alert alert-warning alert-dismissible" role="alert">';
                  echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"></span></button>';
                  echo '  <i class="fa fa-warning"></i> Alert! <p id="verrors">'.$error.'</p>';
                  echo '</div>';
                }
                ?>
                <form enctype="multipart/form-data" id="ipsec_vpn_form" action="ipsec.php" method="post">
                  <table>
                    <tr>
                      <td>Tunnel</td>
                      <td>
                        <select required id="tunnel_no_id" class="form-control" name="tunnel_no">
                          <?php
                          for ($x = 1; $x <= $MAX_IPSEC_TUNNELS; $x++) {
                            if ($x == $filled_tun_no) {
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
                          $tunnel_status = exec("uci get ipsec.tun".$filled_tun_no."_remote.enabled");
                          if ($tunnel_status == "1") {
                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($tunnel_status == "0") {
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
                      <td>Encapsulation Mode</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $esp_ah_mode = exec("uci get ipsec.tun".$filled_tun_no.".esp_ah_mode");
                          if ($esp_ah_mode == "esp") {
                            echo '<input name="esp_ah_mode" value="esp" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="esp_ah_mode" value="esp" type="radio" required>';
                          }
                          ?>
                          <span><i></i>ESP</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($esp_ah_mode == "ah") {
                            echo '<input name="esp_ah_mode" value="ah" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="esp_ah_mode" value="ah" type="radio" required>';
                          }
                          ?>
                          <span><i></i>AH</span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Local Identifier</td>
                      <td><input required type="textfield" class="form-control"  id="local_id_id" name="local_id" value="<?php  echo str_replace('', '', exec("uci get ipsec.tun".$filled_tun_no."_remote.local_identifier"));?>"></td>
                    </tr>

                    <tr>
                      <td colspan="2"><b>Phase 1</b></td>
                      <td></td>
                    </tr>

                    <tr>
                      <td>Encryption Algorithm</td>
                      <td>
                        <select required name="enc_alg" class="form-control input-sm">
                          <?php
                          $p1_enc_alg = exec("uci get ipsec.tun".$filled_tun_no."_p1.encryption_algorithm");
                          foreach ($IPSEC_ENC_ALG_OPTIONS as $IPSEC_ENC_ALG) {
                            if ($IPSEC_ENC_ALG == strtoupper($p1_enc_alg)) {
                              echo '<option selected="selected" value="'.$IPSEC_ENC_ALG.'">'.$IPSEC_ENC_ALG.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_ENC_ALG.'">'.$IPSEC_ENC_ALG.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Hash Algorithm</td>
                      <td>
                        <select required name="hash_alg" class="form-control input-sm">
                          <?php
                          $p1_hash_alg = exec("uci get ipsec.tun".$filled_tun_no."_p1.hash_algorithm");
                          foreach ($IPSEC_P1_HASH_ALG_OPTIONS as $IPSEC_P1_HASH_ALG) {
                            if ($IPSEC_P1_HASH_ALG == strtoupper($p1_hash_alg)) {
                              echo '<option selected="selected" value="'.$IPSEC_P1_HASH_ALG.'">'.$IPSEC_P1_HASH_ALG.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_P1_HASH_ALG.'">'.$IPSEC_P1_HASH_ALG.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Auth Method</td>
                      <td>
                        <select required name="auth_method" class="form-control input-sm">
                          <?php
                          $p1_auth_method = exec("uci get ipsec.tun".$filled_tun_no."_p1.auth_method");
                          foreach ($P1_AUTH_METHODS as $key => $value) {
                            if($key == $p1_auth_method) {
                              echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
                            } else {
                              echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>DH Group</td>
                      <td>
                        <select required name="dh_group" class="form-control input-sm">
                          <?php
                          $dh_group = exec("uci get ipsec.tun".$filled_tun_no."_p1.dh_group");
                          foreach ($IPSEC_DH_GROUPS as $IPSEC_DH_GROUP) {
                            if ($IPSEC_DH_GROUP == $dh_group) {
                              echo '<option selected="selected" value="'.$IPSEC_DH_GROUP.'">'.$IPSEC_DH_GROUP.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_DH_GROUP.'">'.$IPSEC_DH_GROUP.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>


                    <tr>
                      <td colspan="2"><b>Phase 2</b></td>
                      <td></td>
                    </tr>

                    <tr>
                      <td>DH Group</td>
                      <td>
                        <select required name="p2_dh_group" class="form-control input-sm">
                          <?php
                          $p2_dh_group = exec("uci get ipsec.tun".$filled_tun_no."_p2.dh_group");
                          foreach ($IPSEC_DH_GROUPS as $IPSEC_DH_GROUP) {
                            if ($IPSEC_DH_GROUP == $p2_dh_group) {
                              echo '<option selected="selected" value="'.$IPSEC_DH_GROUP.'">'.$IPSEC_DH_GROUP.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_DH_GROUP.'">'.$IPSEC_DH_GROUP.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Encryption Algorithm</td>
                      <td>
                        <select required name="p2_enc_alg" class="form-control input-sm">
                          <?php
                          $p2_enc_alg = exec("uci get ipsec.tun".$filled_tun_no."_p2.encryption_algorithm");
                          foreach ($IPSEC_ENC_ALG_OPTIONS as $IPSEC_ENC_ALG) {
                            if ($IPSEC_ENC_ALG == strtoupper($p2_enc_alg)) {
                              echo '<option selected="selected" value="'.$IPSEC_ENC_ALG.'">'.$IPSEC_ENC_ALG.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_ENC_ALG.'">'.$IPSEC_ENC_ALG.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Hash Algorithm</td>
                      <td>
                        <select required name="p2_hash_alg" class="form-control input-sm">
                          <?php
                          $p2_auth_alg = exec("uci get ipsec.tun".$filled_tun_no."_p2.hash_algorithm");
                          foreach ($IPSEC_P2_HASH_ALG_OPTIONS as $IPSEC_P2_HASH_ALG) {
                            if ($IPSEC_P2_HASH_ALG == strtoupper($p2_auth_alg)) {
                              echo '<option selected="selected" value="'.$IPSEC_P2_HASH_ALG.'">'.$IPSEC_P2_HASH_ALG.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_P2_HASH_ALG.'">'.$IPSEC_P2_HASH_ALG.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="2"><b>Tunnel</b></td>
                    </tr>

                    <tr>
                      <td>Local Gateway IP</td>
                      <td><input pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" type="text" id="local_gateway_id" name="local_gateway" class="form-control" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no."_remote.local_gateway");?>"></td>
                    </tr>

                    <tr>
                      <td>Remote IP</td>
                      <td><input required pattern="^(((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))|([0-9a-z\.\-]{1,}(([a-z]{3})\.|([a-z]{2})(\.[a-z]{2})?))$" title="Must contain a valid IP Address or Domain Name" type="text" id="remote_ip_id" name="remote_ip" class="form-control" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no."_remote.gateway");?>"></td>
                    </tr>

                    <tr>
                      <td>Remote Identifier</td>
                      <td><input title="Must contain a valid IP Address" type="text" id="remote_ident_id" name="remote_ident" class="form-control" value="<?php  echo str_replace('', '', exec("uci get ipsec.tun".$filled_tun_no."_remote.remote_identifier"));?>"></td>
                    </tr>

                    <tr>
                      <td>Mode</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $exchange_mode = exec("uci get ipsec.tun".$filled_tun_no.".exchange_mode");
                          if ($exchange_mode == "main") {
                            echo '<input name="exchange_mode" value="main" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="exchange_mode" value="main" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Main</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($exchange_mode == "aggressive") {
                            echo '<input name="exchange_mode" value="aggressive" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="exchange_mode" value="aggressive" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Aggressive</span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Key Exchange Mode</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $key_exchange_mode = exec("uci get ipsec.tun".$filled_tun_no.".keyexchange");
                          if ($key_exchange_mode == "ikev1") {
                            echo '<input name="key_exchange_mode" value="ikev1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="key_exchange_mode" value="ikev1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>IKEv1</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($key_exchange_mode == "ikev2") {
                            echo '<input name="key_exchange_mode" value="ikev2" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="key_exchange_mode" value="ikev2" type="radio" required>';
                          }
                          ?>
                          <span><i></i>IKEv2</span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Local Network</td>
                      <td><input required pattern="^((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/([0-9]|[1-2][0-9]|3[0-2])[,]?)){1,}$" title="Must contain a valid Subnet Eg. 10.20.30.0/24" type="text" id="local_net_id" name="local_net" class="form-control" value="<?php  $get=exec("uci get ipsec.tun".$filled_tun_no.".local_subnet");
                      $out=array();
                      foreach (explode(" ", $get) as $local_subnet) {
                        array_push($out, explode(' ', trim($local_subnet, "'"))[0]);
                      } echo implode(",",$out); ?>"></td>
                    </tr>

                    <tr>
                      <td>Remote Networks</td>
                      <td><input required pattern="^((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/([0-9]|[1-2][0-9]|3[0-2])[,]?)){1,}$" title="Must contain a valid Subnet Eg. 10.20.30.0/24" type="text" id="remote_net_id" name="remote_net" class="form-control" value="<?php  $get=exec("uci get ipsec.tun".$filled_tun_no.".remote_subnet");
                      $out=array();
                      foreach (explode(" ", $get) as $remote_subnet) {
                        array_push($out, explode(' ', trim($remote_subnet, "'"))[0]);
                      } echo implode(",",$out); ?>"></td>
                    </tr>

                    <tr>
                      <td>Passthrough LAN</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $passthrough_lan = exec("uci get ipsec.tun".$filled_tun_no.".passthrough_lan");
                          if ($passthrough_lan == "yes") {
                            echo '<input id="id_passthrough_lan" name="passthrough_lan" checked=checked type="checkbox" onclick="javascript:toggle_passthrough_lan_subnet();">';
                          } else {
                            echo '<input id="id_passthrough_lan" name="passthrough_lan" type="checkbox" onclick="javascript:toggle_passthrough_lan_subnet();">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr id="id_passthrough_lan_subnet_row">
                      <td>Passthrough Subnet</td>
                      <td>
                      <?php
                          $passthrough_lan_subnet = exec("uci get ipsec.tun".$filled_tun_no.".passthrough_lan_subnet");
                          echo '<input id="id_passthrough_lan_subnet" class="form-control" name="passthrough_lan_subnet" type="text" value="'.$passthrough_lan_subnet.'">';
                      ?>
                      </td>
                    </tr>

                    <tr>
                      <td>Allow LAN</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $allow_lan = exec("uci get ipsec.tun".$filled_tun_no.".allow_lan");
                          if ($allow_lan == "1") {
                            echo '<input name="allow_lan" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="allow_lan" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>IKE Lifetime (h/m/s)</td>
                      <td><input required type="textfield" class="form-control"  id="ike_lifetime_id" name="ike_lifetime" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no.".ikelifetime");?>"></td>
                    </tr>

                    <tr>
                      <td>Tunnel Lifetime (h/m/s)</td>
                      <td><input required type="textfield" class="form-control"  id="lifetime_id" name="lifetime" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no.".lifetime");?>"></td>
                    </tr>

                    <tr>
                      <td>Keying Tries</td>
                      <td><input required type="textfield" class="form-control"  id="keyingtries_id" name="keyingtries" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no.".keyingtries");?>"></td>
                    </tr>


                    <tr>
                      <td colspan="2"><b>Dead Peer Detection</b></td>
                    </tr>

                    <tr>
                      <td>DPD Delay</td>
                      <td><input required type="textfield" class="form-control"  id="dpddelay_id" name="dpddelay" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no.".dpddelay");?>"></td>
                    </tr>

                    <tr>
                      <td>DPD Timeout</td>
                      <td><input required type="textfield" class="form-control"  id="dpdtimeout_id" name="dpdtimeout" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no.".dpdtimeout");?>"></td>
                    </tr>

                    <tr>
                      <td>DPD Action</td>
                      <td>
                        <select required name="dpdaction" class="form-control input-sm">
                          <?php
                          $dpd_action = exec("uci get ipsec.tun".$filled_tun_no.".dpdaction");
                          foreach ($IPSEC_DPD_ACTIONS as $IPSEC_DPD_ACTION) {
                            if ($IPSEC_DPD_ACTION == ucwords($dpd_action)) {
                              echo '<option selected="selected" value="'.$IPSEC_DPD_ACTION.'">'.$IPSEC_DPD_ACTION.'</option>';
                            } else {
                              echo '<option value="'.$IPSEC_DPD_ACTION.'">'.$IPSEC_DPD_ACTION.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Passphrase</td>
                      <td><input required type="password" class="form-control"  id="pre_shared_key_id" name="pre_shared_key" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no."_remote.pre_shared_key");?>"></td>
                      <td><a href="#" id="pre_shared_key_hidepass" onclick="hidepass(this.id)"><i class="fa fa-eye-slash"></i></a></td>
                    </tr>

                    <tr>
											<td>Log Level</td>
											<td>
												<select required name="log_level" class="form-control input-sm">
												<?php
												$log_level = exec("uci get ipsec.ipsec[".($filled_tun_no-1)."].debug");
												foreach ($IPSEC_DEBUG_LEVELS as $key => $value) {
													if($key == $log_level) {
													echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
													} else {
													echo '<option value="'.$key.'">'.$value.'</option>';
													}
												}
												?>
												</select>
											</td>
                    </tr>
                      
                    <tr>
                      <td colspan="2"><b>Tunnel Failover and Tracking</b></td>
                    </tr>

                    <tr>
                      <td>Backup Tunnel</td>
                      <td>
                        <select id="backup_tunnel_id" class="form-control" name="backup_tunnel">
                          <option value="0">None</option>
                          <?php
                          for ($x = 1; $x <= $MAX_IPSEC_TUNNELS; $x++) {
                            if ($x != $filled_tun_no) {
                              if($x == exec("uci get ipsec.tun".$filled_tun_no."_remote.backup_tunnel")) {
                                echo '<option selected=selected value="'.$x.'">tun'.$x.'</option>';
                              } else {
                                echo '<option value="'.$x.'">tun'.$x.'</option>';
                              }
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Tunnel Track IPs</td>
                      <td id="id_track_ips">
                      <?php
                      $out = exec("uci get ipsec.tun".$filled_tun_no.".track_ip");
                      $track_ips = show_zone_networks($out);
                        foreach ($track_ips as $track_ip) {
                          echo '<input name="track_ips[]" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" type="text" class="form-control" value="'.$track_ip.'">';
                        }
                      ?>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><button id="add_tracking_ip_button" type="button"><i class="fa fa-plus"></i> Add IP</button></td>
                    </tr>

                    <tr>
                      <td>No of Responses</td>
                      <td><input type="textfield" class="form-control"  id="pingcount_id" name="pingcount" value="<?php  echo exec("uci get ipsec.tun".$filled_tun_no.".pingcount");?>"></td>
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
                <h3 class="panel-title">Current Clients (Max: <?php echo $MAX_IPSEC_CLIENTS; ?>)</h3>
              </div>
              <div class="panel-body">
                <div id="ipsec_vpn_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Remote</th>
                        <th>Remote LAN</th>
                        <th>Local LAN</th>
                        <th>DH Group</th>
                        <th>Phase 1</th>
                        <th>Phase 2</th>
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
    if (document.getElementById('id_passthrough_lan').checked) {
      document.getElementById('id_passthrough_lan_subnet_row').style.display = '';
    } else {
      document.getElementById('id_passthrough_lan_subnet_row').style.display = 'none';
    }
  }

  function toggle_passthrough_lan_subnet()
  {
    if (document.getElementById('id_passthrough_lan').checked) {
      document.getElementById('id_passthrough_lan_subnet_row').style.display = '';
    } else {
      document.getElementById('id_passthrough_lan_subnet_row').style.display = 'none';
    }
  }

  function hidepass(show_pass_clicked_id)
  {
    var x = document.getElementById(show_pass_clicked_id);

    if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
      x.innerHTML = '<i class="fa fa-eye"></i>';
      document.getElementById('pre_shared_key_id').type = "text";
    } else {
      x.innerHTML = '<i class="fa fa-eye-slash"></i>';
      document.getElementById('pre_shared_key_id').type = "password";
    }
  }
</script>
<script>
  $(document).ready(function() {

    $('#add_tracking_ip_button').click(function(e) {
      e.preventDefault();
      console.log("clicked add_tracking_ip_button");
      $('#id_track_ips').append('<br><input name="track_ips[]" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" type="text" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
    });
    
    $('#id_track_ips').on("click", ".delete", function(e) {
      console.log("clicked ip_list_wrapper delete");
      e.preventDefault();
      $(this).parent('tr').remove();
    })

  });
</script>
