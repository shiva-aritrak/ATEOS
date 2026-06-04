<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Network | Monitor</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/networks.php' ?>

<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $ping_ips = $_POST["ping_ips"];
  $interface = $_POST["interface"];
  $proto_family = $_POST["proto_family"];
  $min_resp = $_POST["min_resp"];
  $count = $_POST["count"];
  $resp_timeout = $_POST["resp_timeout"];
  $check_interval = $_POST["check_interval"];
  $reboot_on_down = $_POST["reboot_on_down"];
  $reboot_on_down_duration = $_POST["reboot_on_down_duration"];
  $action = $_POST["action"];
  
  if (sizeof(array_filter($ping_ips)) < $min_resp ) {
    $incorrect = true;
    $message = "Alert! Minimum responses cannot be greater than Ping IPs";
  } else {
    set_nwmon_globals($ping_ips, $interface, $proto_family, $count, $min_resp, $resp_timeout, $check_interval, $reboot_on_down, $reboot_on_down_duration, $action);
    echo '<script>window.location.href = "/network/monitor.php";</script>';
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
                <h3 class="panel-title">Network Connectivity Monitor</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="nwmon_form" action="monitor.php" method="post">
                  <table>
                    <tr>
                      <td>Enable Network Monitoring</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $reboot_on_down = exec("uci get nwmon.globals.reboot_on_down");
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
                      <td>Down Duration (Seconds) <br><i>Default 1800s</i></td>
                      <td><input type="number"  min="300" id="reboot_on_down_duration_id" name="reboot_on_down_duration" class="form-control" value="<?php  echo exec("uci get nwmon.globals.reboot_on_down_duration"); ?>"></td>
                    </tr>
                    <tr>
                      <td>IP Family  <br><i>Default IPv4</i></td>
                      <td>
                        <select required name="proto_family" class="form-control input-sm">
														<?php
														$family = exec("uci get nwmon.globals.family");
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
                      $out = exec("uci get nwmon.globals.track_ip");
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
                        <input name="ping_ips[]" pattern="((^\h*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\h*$)|(^\h*((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\h*$))" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[1]; ?>">
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td>
                        <input name="ping_ips[]" pattern="((^\h*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\h*$)|(^\h*((([0-9a-f]{1,4}:){7}([0-9a-f]{1,4}|:))|(([0-9a-f]{1,4}:){6}(:[0-9a-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){5}(((:[0-9a-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9a-f]{1,4}:){4}(((:[0-9a-f]{1,4}){1,3})|((:[0-9a-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){3}(((:[0-9a-f]{1,4}){1,4})|((:[0-9a-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){2}(((:[0-9a-f]{1,4}){1,5})|((:[0-9a-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9a-f]{1,4}:){1}(((:[0-9a-f]{1,4}){1,6})|((:[0-9a-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9a-f]{1,4}){1,7})|((:[0-9a-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\h*$))" title="Must contain a valid IP Address" type="text" class="form-control" value="<?php echo $track_ips[2]; ?>">
                      </td>
                    </tr>

                    <tr>
                      <td>Via Interface</td>
                      <td>
                        <select required name="interface" class="form-control input-sm">
                          <?php
                          $interface = exec("uci get nwmon.globals.interface");
                          echo '<option value="default">Any</option>';
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
                      <td>Number of Pings</td>
                      <td><input type="number" min="5" id="count_id" name="count" class="form-control" value="<?php  echo exec("uci get nwmon.globals.count"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Minimum Responses</td>
                      <td><input type="number" id="min_resp_id" name="min_resp" class="form-control" value="<?php  echo exec("uci get nwmon.globals.reliability"); ?>"></td>
                    </tr>
                   
                    <tr>
                      <td>Response Timeout</td>
                      <td><input type="number" id="resp_timeout_id" name="resp_timeout" class="form-control" value="<?php  echo exec("uci get nwmon.globals.timeout"); ?>"></td>
                    </tr>
                   
                    <tr>
                      <td>Link Check Interval (Seconds)</td>
                      <td><input required min="20" type="number" id="check_interval_id" name="check_interval" class="form-control" value="<?php  echo exec("uci get nwmon.globals.interval"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Action</td>
                      <td>
                        <select required name="action" class="form-control input-sm">
                          <?php
                          $action = exec("uci get nwmon.globals.action");
                          foreach ($NWMON_ACTIONS as $key => $value) {
                            if($key == $action) {
                              echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
                            } else {
                              echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                          }
                          ?>
                        </select>
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
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
