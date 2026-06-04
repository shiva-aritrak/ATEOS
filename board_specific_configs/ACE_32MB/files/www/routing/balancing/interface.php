<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Aggregation | Add/Edit SD-WAN Interface</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/balancing.php' ?>

<?php

$interface = "99";
if(count($_GET) > 0) {
  $interface = $_GET["interface"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_balancing_interface($interface);
    echo '<script>window.location.href = "/routing/aggregation.php";</script>';
    exit;
  }
}

$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $interface_name = $_POST["interface_name"];
  $status = $_POST["status"];
  $ping_ips = $_POST["ping_ips"];
  $proto_family = $_POST['proto_family'];
  $min_resp = $_POST["min_resp"];
  $min_ping_resp = $_POST["min_ping_resp"];
  $resp_timeout = $_POST["resp_timeout"];
  $check_interval = $_POST["check_interval"];
  $failure_latency = $_POST["failure_latency"];
  $check_perf = $_POST["check_perf"];
  $accept_latency = $_POST["accept_latency"];
  $failure_loss = $_POST["failure_loss"];
  $recovery_loss = $_POST["recovery_loss"];
  $no_of_failed_tests = $_POST["no_of_failed_tests"];
  $no_of_success_tests = $_POST["no_of_success_tests"];
  $terminate_sessions = $_POST["terminate_sessions"];
  if (sizeof(array_filter($ping_ips)) < $min_resp ) {
    $incorrect = true;
    $message = "Alert! Minimum responses cannot be greater than Ping IPs";
  } else {
    add_balancing_interface($interface_name, $status, $ping_ips, $proto_family, $min_resp, $min_ping_resp, $resp_timeout, $check_interval, $failure_latency, $accept_latency, $check_perf, $failure_loss, $recovery_loss, $no_of_failed_tests, $no_of_success_tests, $terminate_sessions);
    echo '<script>window.location.href = "/routing/aggregation.php";</script>';
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
                <h3 class="panel-title">Add SD-WAN Interface</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="static_route_form" action="interface.php" method="post">
                  <table>
                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $intf_status = exec("uci get mwan3.".$interface.".enabled");
                          if ($intf_status == "1") {
                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($intf_status == "0") {
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
                      <td>Interface</td>
                      <td>
                        <select required name="interface_name" class="form-control input-sm">
                          <?php
                          foreach (get_configured_interfaces() as $intf) {
                            if ($intf == $interface) {
                              echo '<option selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
                            } else {
                              echo '<option value="'.$intf.'">'.strtoupper($intf).'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>IP Family</td>
                      <td>
                        <select required name="proto_family" class="form-control input-sm">
														<?php
														$family = exec("uci get mwan3.".$interface.".family");
														foreach ($IP_PROTO_CHOICES as $key => $value) {
															if($key == $family) {
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
                      <?php
                      $out = exec("uci get mwan3.".$interface.".track_ip");
                      $track_ips = show_zone_networks($out);
                      ?>
                      <td>Ping IPs</td>
                      <td>
                        <input name="ping_ips[]" pattern="((^\h*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\h*$)|(^\h*((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\h*$))" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[0]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <input name="ping_ips[]" pattern="((^\h*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\h*$)|(^\h*((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\h*$))" title="Must contain a valid IPv4/v6 Address" type="text" class="form-control" value="<?php echo $track_ips[1]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <input name="ping_ips[]" pattern="((^\h*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\h*$)|(^\h*((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\h*$))" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[2]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td>Minimum Responses</td>
                      <td><input type="number" id="min_resp_id" name="min_resp" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".reliability"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Number of Ping Requests</td>
                      <td><input type="number" id="min_ping_resp_id" name="min_ping_resp" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".count"); ?>"></td>
                    </tr>
                    <tr>
                      <td>No of Rounds for Success</td>
                      <td><input type="number" min=1 id="no_of_success_tests_id" name="no_of_success_tests" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".up"); ?>"></td>
                    </tr>
                    <tr>
                      <td>No of Rounds for Failed</td>
                      <td><input type="number" min=1 id="no_of_failed_tests_id" name="no_of_failed_tests" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".down"); ?>"></td>
                    </tr>
                    <tr>
                    <td>Terminate Existing Sessions</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $flush_conntrack = exec("uci get mwan3.".$interface.".flush_conntrack");
                          if ($flush_conntrack) {
                            echo '<input name="terminate_sessions" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="terminate_sessions" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Monitor Link Performance</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $check_perf = exec("uci get mwan3.".$interface.".check_quality");
                          if ($check_perf == "on") {
                            echo '<input name="check_perf" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="check_perf" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Failure Packet Loss (%)</td>
                      <td><input type="number" id="failure_latency_id" name="failure_loss" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".failure_loss"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Acceptable Packet Loss (%)</td>
                      <td><input type="number" id="recovery_loss_id" name="recovery_loss" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".recovery_loss"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Failure Latency (ms)</td>
                      <td><input type="number" id="failure_latency_id" name="failure_latency" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".failure_latency"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Acceptable Latency (ms)</td>
                      <td><input type="number" id="accept_latency_id" name="accept_latency" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".recovery_latency"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Ping Response Timeout</td>
                      <td><input type="number" id="resp_timeout_id" name="resp_timeout" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".timeout"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Link Monitoring Interval (Seconds)</td>
                      <td><input required type="number" id="check_interval_id" name="check_interval" class="form-control" value="<?php  echo exec("uci get mwan3.".$interface.".interval"); ?>"></td>
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
                  </form>
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
