<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Network | SIM Monitor</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/networks.php' ?>

<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $ping_ips = $_POST["ping_ips"];
  $pingv6_ips = $_POST["pingv6_ips"];
  $min_resp = $_POST["min_resp"];
  $min_ping_resp = $_POST["min_ping_resp"];
  $resp_timeout = $_POST["resp_timeout"];
  $check_interval = $_POST["check_interval"];
  $failure_latency = $_POST["failure_latency"];
  $check_quality = $_POST["check_quality"];
  $check_ping = $_POST["check_ping"];
  $check_signal = $_POST["check_signal"];
  $switch_above_signal = $_POST["switch_above_signal"];
  $switch_prevent_interval = $_POST["switch_prevent_interval"];
  $reboot_on_down = $_POST["reboot_on_down"];
  $reboot_on_down_duration = $_POST["reboot_on_down_duration"];
  $accept_latency = $_POST["accept_latency"];
  $check_quota = $_POST["check_quota"];
  $sim1_quota = $_POST["sim1_quota"];
  $sim2_quota = $_POST["sim2_quota"];
  if (sizeof(array_filter($ping_ips)) < $min_resp ) {
    $incorrect = true;
    $message = "Alert! Minimum responses cannot be greater than Ping IPs";
  } else if (sizeof(array_filter($pingv6_ips)) < $min_resp ) {
    $incorrect = true;
    $message = "Alert! Minimum responses cannot be greater than Ping IPs";
  } else {
    set_simswitch_globals($ping_ips, $pingv6_ips, $min_resp, $min_ping_resp, $resp_timeout, $check_interval, $failure_latency, $check_quality, $check_ping, $check_signal, $switch_above_signal, $accept_latency, $switch_prevent_interval, $check_quota, $sim1_quota, $sim2_quota, $reboot_on_down, $reboot_on_down_duration);
    echo '<script>window.location.href = "/network/simswitch.php";</script>';
    exit;
  }
}

?>

<?php startblock('contentbar') ?>

<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <h3 class="page-title"></h3>
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <?php
                if ($incorrect) {
                  echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                  echo '  <i class="fa fa-warning"></i> '.$message.'</p>';
                  echo '</div>';
                }
              ?>
              <div class="panel-heading">
                <h3 class="panel-title">SIM Failover Monitoring</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="simswitch_form" action="simswitch.php" method="post">
                  <table>
                    <tr>
                      <td>Check Signal Strength</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $check_signal = exec("uci get simswitch.globals.check_signal");
                          if ($check_signal == "1") {
                            echo '<input name="check_signal" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="check_signal" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>Switch Above Signal (dBm)</td>
                      <td><input type="number" id="switch_above_signal_id" name="switch_above_signal" class="form-control" value="<?php  echo exec("uci get simswitch.globals.switch_above_signal"); ?>"></td>
                    </tr>
                    
                    <tr>
                      <td>SIM Flapping Protection Wait (Seconds) <br><i>Default 300s</i></td>
                      <td><input type="number" id="switch_prevent_interval_id" name="switch_prevent_interval" class="form-control" value="<?php  echo exec("uci get simswitch.globals.switch_prevent_interval"); ?>"></td>
                    </tr>


                    <tr>
                      <td>Enable Network Reboot</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $reboot_on_down = exec("uci get simswitch.globals.reboot_on_down");
                          if ($reboot_on_down == "1") {
                            echo '<input name="reboot_on_down" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="reboot_on_down" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Reboot on Network Loss (Seconds) <br><i>Default 1800s</i></td>
                      <td><input type="number" id="reboot_on_down_duration_id" name="reboot_on_down_duration" class="form-control" value="<?php  echo exec("uci get simswitch.globals.reboot_on_down_duration"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Check Ping Status</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $check_ping = exec("uci get simswitch.globals.check_ping");
                          if ($check_ping == "1") {
                            echo '<input name="check_ping" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="check_ping" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <?php
                      $out = exec("uci get simswitch.globals.track_ip");
                      $track_ips = show_zone_networks($out);
                      ?>
                      <td>Ping IPs</td>
                      <td>
                        <input name="ping_ips[]" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[0]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td><i>IPv4 IPs are pinged only when SIM is used in IPv4 mode</i></td>
                      <td>
                        <input name="ping_ips[]" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[1]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <input name="ping_ips[]" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[2]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <?php
                      $out = exec("uci get simswitch.globals.trackv6_ip");
                      $track_ips = show_zone_networks($out);
                      ?>
                      <td>Ping IPv6 IPs</td>
                      <td>
                        <input name="pingv6_ips[]" pattern="^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$"  title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[0]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td><i>IPv6 IPs are pinged only when SIM is used in IPv6 mode</i></td>
                      <td>
                        <input name="pingv6_ips[]" pattern="^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$"  title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[1]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <input name="pingv6_ips[]" pattern="^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$"  title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[2]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td>Minimum Responses</td>
                      <td><input type="number" id="min_resp_id" name="min_resp" class="form-control" value="<?php  echo exec("uci get simswitch.globals.reliability"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Minimum Ping Responses</td>
                      <td><input type="number" id="min_ping_resp_id" name="min_ping_resp" class="form-control" value="<?php  echo exec("uci get simswitch.globals.count"); ?>"></td>
                    </tr>
                    
                    <tr>
                      <td>Response Timeout</td>
                      <td><input type="number" id="resp_timeout_id" name="resp_timeout" class="form-control" value="<?php  echo exec("uci get simswitch.globals.timeout"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Check Link Performance</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $check_quality = exec("uci get simswitch.globals.check_quality");
                          if ($check_quality == "1") {
                            echo '<input name="check_quality" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="check_quality" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Failure Latency (ms)</td>
                      <td><input type="number" id="failure_latency_id" name="failure_latency" class="form-control" value="<?php  echo exec("uci get simswitch.globals.failure_latency"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Acceptable Latency (ms)</td>
                      <td><input type="number" id="accept_latency_id" name="accept_latency" class="form-control" value="<?php  echo exec("uci get simswitch.globals.recovery_latency"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Check Data Usage</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $check_quota = exec("uci get simswitch.globals.check_quota");
                          if ($check_quota == "1") {
                            echo '<input name="check_quota" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="check_quota" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>SIM1 Quota Per Day (GB)</td>
                      <td><input type="number" step="0.01" min="0" max="25" id="id_quota_sim1" name="sim1_quota" class="form-control" value="<?php  echo exec("uci get simswitch.globals.sim1_quota"); ?>"></td>
                    </tr>

                    <tr>
                      <td>SIM2 Quota Per Day (GB)</td>
                      <td><input type="number" step="0.01" min="0" max="25" id="id_quota_sim2" name="sim2_quota" class="form-control" value="<?php  echo exec("uci get simswitch.globals.sim2_quota"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Link Check Interval (Seconds)</td>
                      <td><input required type="number" id="check_interval_id" name="check_interval" class="form-control" value="<?php  echo exec("uci get simswitch.globals.interval"); ?>"></td>
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
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
