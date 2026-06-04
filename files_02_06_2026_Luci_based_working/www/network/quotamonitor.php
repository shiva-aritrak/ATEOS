<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Network | Data Quota Monitor</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/networks.php' ?>

<?php

if(count($_GET) > 0) {
	$selected_interface = $_GET["interface"];
	$action = $_GET["action"];

  if ($action=="delete") {
    delete_data_monitoring_interface($selected_interface);
  }
}

if(count($_POST) > 0) {
  $status = $_POST["status"];
  $interface = $_POST["interface"];
  $gb_quota = $_POST["gb_quota"];
  $quota_start_date = $_POST["quota_start_date"];
  $quota_duration_days = $_POST["quota_duration_days"];
  $disable_interface = $_POST["disable_interface"];
  $sdwan_disable = $_POST["sdwan_disable"];

  set_data_monitoring($status, $interface, $gb_quota, $quota_start_date, $quota_duration_days, $disable_interface, $sdwan_disable);
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
              <div class="panel-heading">
                <h3 class="panel-title">Data Quota Monitor</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="quotamonitor_form" action="quotamonitor.php" autocomplete="off" method="post">
                  <table>
                    <tr>
                        <td>Status</td>
                        <td>
                            <label class="fancy-radio">
                                <?php
                                $fuse_status = exec("uci get datamon.".$selected_interface.".status");
                                if ($fuse_status == "1") {
                                    echo '<input name="status" value="1" checked="checked" type="radio" required>';
                                } else {
                                    echo '<input name="status" value="1" type="radio" required>';
                                }
                                ?>
                                <span><i></i>Enabled</span>
                            </label>
                            <label class="fancy-radio">
                                <?php
                                if ($fuse_status == "0") {
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
                        <td>Interfaces</td>
                        <td>
                            <select name="interface" class="form-control input-sm">
                                <?php
                                foreach (get_configured_internet_interfaces() as $intf) {
                                    if (strtolower($selected_interface) == strtolower($intf) ) {
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
                      <td>Total Quota (GB)</td>
                      <td><input type="number" step="0.1" min="0.1" max="10000" id="gb_quota_id" name="gb_quota" class="form-control" value="<?php echo exec("uci get datamon.".$selected_interface.".gb_quota"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Quota Start Date</td>
                      <td>
                        <input type="date" name="quota_start_date" value="<?php  echo exec("uci get datamon.".$selected_interface.".quota_start_date"); ?>" />
                      </td>
                    </tr>

                    <tr>
                      <td>Quota Duration (Days)</td>
                      <td><input type="number" step="1" min="1" max="366" id="quota_duration_days_id" name="quota_duration_days" class="form-control" value="<?php  echo exec("uci get datamon.".$selected_interface.".quota_duration_days"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Disable Interface on Expiry</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $disable_interface = exec("uci get datamon.".$selected_interface.".disable_interface");
                          if ($disable_interface == "1") {
                            echo '<input name="disable_interface" value="1" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="disable_interface" value="1" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Remove from SD-WAN Policy Only</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $sdwan_disable = exec("uci get datamon.".$selected_interface.".sdwan_disable");
                          if ($sdwan_disable == "1") {
                            echo '<input name="sdwan_disable" value="1" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="sdwan_disable" value="1" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
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
                <h3 class="panel-title">Currently Monitored Interfaces</h3>
              </div>
              <div class="panel-body">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Interface</th>
                      <th>Status</th>
                      <th>Active</th>
                      <th>Quota Start Date</th>
                      <th>Duration</th>
                      <th>Total Quota</th>
                      <th>Used Quota</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      foreach (get_configured_monitor_interfaces() as $configured_interface) {
                          echo "<tr>";
                          echo "<td><kbd>".strtoupper($configured_interface)."</kbd></td>";
                          echo "<td>".show_enabled_disabled(exec("uci get datamon.".$configured_interface.".status"))."</td>";
                          echo "<td>".show_force_route(exec("uci get datamon.".$configured_interface.".active"))."</td>";
                          echo "<td>".exec("uci get datamon.".$configured_interface.".quota_start_date")."</td>";
                          echo "<td>".exec("uci get datamon.".$configured_interface.".quota_duration_days")."</td>";
                          echo "<td>".exec("uci get datamon.".$configured_interface.".gb_quota")."</td>";
                          echo "<td>".exec("uci get datamon.".$configured_interface.".bytes_total")."</td>";
                          echo '<td><a href="quotamonitor.php?action=delete&interface='.$configured_interface.'"><i class="fa fa-times"></i></a></td>';
                          echo "</tr>";
                      }
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
