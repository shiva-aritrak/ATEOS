<?php include '/www/lib/sessioncheck.php' ?>

<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/system.php' ?>

<?php

$filled_fw_no = "99";

$repeat_no_match = 0;
$current_no_match = 0;

$oper_repeat_no_match = 0;
$oper_current_no_match = 0;

if(count($_POST) > 0) {
  $action = $_POST["action"];
  if ($action == "ddns") {
    $ddns_provider = $_POST["ddns_provider"];
    $src_interface = $_POST["src_interface"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $ddns_name = $_POST["ddns_name"];
    $update_url = $_POST["update_url"];
    set_ddns_client($ddns_provider, $src_interface, $username, $password, $ddns_name, $update_url);
  }
  else if ($action == "syslog") {
    $log_remote = $_POST["log_remote"];
    $log_size = $_POST["log_size"];
    $log_ip = $_POST["log_ip"];
    $log_port = $_POST["log_port"];
    $log_proto = $_POST["log_proto"];
    set_syslog_config($log_remote, $log_size, $log_ip, $log_port, $log_proto);
  }
  else if ($action == "auth") {
    $curr_password = $_POST["curr_password"];
    $new_pass = $_POST["new_pass"];
    $new_pass_rep = $_POST["new_pass_rep"];
    $curr_password_uci = exec("uci get anexgate.authentication.password");
    if ($curr_password == $curr_password_uci) {
      if($new_pass == $new_pass_rep){
        set_admin_auth($new_pass);
      } else {
        $repeat_no_match = 1;
      }
    } else {
      $current_no_match = 1;
    }
  }
  else if ($action == "oper_auth") {
    $oper_curr_password = $_POST["oper_curr_password"];
    $oper_new_pass = $_POST["oper_new_pass"];
    $oper_new_pass_rep = $_POST["oper_new_pass_rep"];
    $oper_curr_password_uci = exec("uci get anexgate.user.password");
    if (($oper_curr_password == $oper_curr_password_uci) || ($_SESSION['is_admin'] == "yes")) {
      if($oper_new_pass == $oper_new_pass_rep) {
        set_oper_admin_auth($oper_new_pass);
      } else {
        $oper_repeat_no_match = 1;
      }
    } else {
      $oper_current_no_match = 1;
    }
  }
  else if ($action == "system") {
    $hostname = $_POST["hostname"];
    $new_listen_port = $_POST["listen_port"];
    $auto_backup = $_POST["auto_backup"];
    $cloud_domain = $_POST["cloud_domain"];
    $nms_domain = $_POST["nms_domain"];
    $sms_management = $_POST["sms_management"];
    $whitelist_numbers = $_POST["whitelist_numbers"];
    set_system_settings($hostname, $new_listen_port, $auto_backup, $cloud_domain, $nms_domain, $sms_management, $whitelist_numbers);
    set_hub_device_web_server_port();
    set_connect_web_server_port();
    exec("/etc/init.d/smsman restart &");
    if ($listen_port != $new_listen_port) {
      exit;
    }
    exec("/etc/init.d/uhttpd reload &");
    exec("/etc/init.d/system restart &");
    exit;
  } else if ($action == "ntp") {
    $timezone = $_POST["timezone"];
    $ntp_server = $_POST["ntp_server"];
    set_ntp_server($timezone, $ntp_server);
  } else if ($action == "reboot") {
      $time = $_POST["time"];
      $enabled = $_POST["enabled"];
      $sun = $_POST["sun"];
      $mon = $_POST["mon"];
      $tue = $_POST["tue"];
      $wed = $_POST["wed"];
      $thu = $_POST["thu"];
      $fri = $_POST["fri"];
      $sat = $_POST["sat"];
      set_reboot_task($enabled, $time, $sun, $mon, $tue, $wed, $thu, $fri, $sat);
  }
}

?>

<?php include '/www/sidenav.php' ?>

<head>
  <title>Administration | System Management</title>
</head>
<?php startblock('contentbar') ?>

<div class="main">

  <div class="main-content">
    <div class="container-fluid">

      <h3 class="page-title"></h3>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Admin Authorization Settings</h3>
              </div>
              <div class="panel-body">
                <?php if ($repeat_no_match) echo '<p>Error! Password and Repeated Password do not match</p>'; ?>
                <?php if ($current_no_match) echo '<p>Error! Current Username or Password Incorrect</p>'; ?>
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="action_id" name="action" value="auth">
                  <table>
                    <tr>
                      <td>Admin Login Username</td>
                      <td><input disabled type="text" id="username_id" name="username" class="form-control" value="<?php echo exec("uci get anexgate.authentication.username"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Current Password</td>
                      <td><input required type="password" id="curr_password_id" name="curr_password" class="form-control"></td>
                    </tr>
                    <tr>
                      <td>New Password</td>
                      <td><input required type="password" id="new_pass_id" name="new_pass" class="form-control"></td>
                    </tr>
                    <tr>
                      <td>New Password (Repeat)</td>
                      <td><input required type="password" id="new_pass_rep_id" name="new_pass_rep" class="form-control"></td>
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
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Operator Authorization Settings</h3>
              </div>
              <div class="panel-body">
                <?php if ($oper_repeat_no_match) echo '<p>Error! Password and Repeated Password do not match</p>'; ?>
                <?php if ($oper_current_no_match) echo '<p>Error! Current Username or Password Incorrect</p>'; ?>
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="oper_action_id" name="action" value="oper_auth">
                  <table>
                    <tr>
                      <td>Operator Login Username</td>
                      <td><input disabled type="text" id="oper_username_id" name="oper_username" class="form-control" value="<?php echo exec("uci get anexgate.user.username"); ?>"></td>
                    </tr>
                    <?php 
                      if ($_SESSION['is_admin'] == 'no') {
                        echo '<tr>';
                        echo '  <td>Current Password</td>';
                        echo '  <td><input required type="password" id="oper_curr_password_id" name="oper_curr_password" class="form-control"></td>';
                        echo '</tr>';
                      } else {
                        echo '<tr>';
                        echo '  <td><input type="hidden" id="oper_curr_password_id" name="oper_curr_password" class="form-control"></td>';
                        echo '</tr>';
                      }
                    ?>
                    <tr>
                      <td>New Password</td>
                      <td><input required type="password" id="oper_new_pass_id" name="oper_new_pass" class="form-control"></td>
                    </tr>
                    <tr>
                      <td>New Password (Repeat)</td>
                      <td><input required type="password" id="oper_new_pass_rep_id" name="oper_new_pass_rep" class="form-control"></td>
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
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">System Settings</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="action_id" name="action" value="system">
                  <table>
                    <tr>
                      <td>Hostname</td>
                      <td><input required type="text" id="hostname_id" name="hostname" class="form-control" value="<?php echo exec("uci get system.@system[0].hostname"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Web Server Port</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $listen_http = exec("uci get uhttpd.main.listen_http | cut -d ' ' -f 1");
                          $listen_ip = explode(":", $listen_http)[0];
                          $listen_port = explode(":", $listen_http)[1];
                          echo '<input required type="text" id="listen_port_id" name="listen_port" class="form-control" value="'.$listen_port.'">';
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Cloud Address</td>
                      <td><input required type="text" id="cloud_domain_id" name="cloud_domain" class="form-control" value="<?php echo exec("uci get anexgate.config.cloud_domain"); ?>"></td>
                    </tr>
                    <tr>
                      <td>NMS Address</td>
                      <td><input required type="text" id="nms_domain_id" name="nms_domain" class="form-control" value="<?php echo exec("uci get anexgate.config.nms_domain"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Auto Backup to Cloud</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $auto_backup = exec("uci get anexgate.config.auto_backup");
                          if ($auto_backup == "1") {
                            echo '<input id="auto_backup_id" name="auto_backup" checked=checked type="checkbox">';
                          } else {
                            echo '<input id="auto_backup_id" name="auto_backup" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>SMS Management</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $sms_management = exec("uci get anexgate.config.sms_management");
                          if ($sms_management == "1") {
                            echo '<input id="sms_management_id" name="sms_management" checked type="checkbox">';
                          } else {
                            echo '<input id="sms_management_id" name="sms_management" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Whitelist Numbers</td>
                      <td class="filter_table">
                        <?php
                          foreach (show_zone_networks(exec("uci get anexgate.config.whitelist_numbers")) as $whitelist_number) {
                            echo '<input type="text" name="whitelist_numbers[]" class="form-control" value="'.$whitelist_number.'"><br>';
                          }
                          ?>
                      </td>
                    </tr>
                    <tr>
                          <td></td>
                          <td><button class="add_domain_button" type="button"><i class="fa fa-plus"></i> Add Number</button></td>
                    </tr>

                  </table>
                  <br/>
                  <div class="row">
                    <div class="col-md-6">
                      <p class="demo-button">
                        <button type="button" id="system_settings_button" class="btn btn-primary">Save</button>
                      </p>
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
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">DDNS Settings</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="action_id" name="action" value="ddns">
                  <table>
                    <tr>
                      <td>DDNS Provider</td>
                      <td>
                        <select required name="ddns_provider" class="form-control input-sm">
                          <option value="Custom">Custom</option>
                          <?php
                          $selected_ddns_service = exec("uci get ddns.ace.service_name");
                          foreach (get_ddns_options() as $ddns_services) {
                            if ($ddns_services == $selected_ddns_service) {
                              echo '<option selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
                            } else {
                              echo '<option value="'.$intf.'">'.$intf.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Interfaces</td>
                      <td>
                        <select required name="src_interface" class="form-control input-sm">
                          <?php
                          $ddns_network = exec("uci get ddns.ace.interface");
                          foreach (get_configured_interfaces() as $intf) {
                            if ($intf == $ddns_network) {
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
                      <td>Account Username</td>
                      <td><input required type="text" id="username_id" name="username" class="form-control"></td>
                    </tr>
                    <tr>
                      <td>Account Password</td>
                      <td><input type="password" id="password_id" name="password" class="form-control"></td>
                    </tr>
                    <tr>
                      <td>DDNS Name</td>
                      <td><input required type="text" id="ddns_name_id" name="ddns_name" class="form-control"></td>
                    </tr>
                    <tr>
                      <td>Update URL (Custom)</td>
                      <td><input type="text" id="update_url_id" name="update_url" class="form-control"></td>
                    </tr>
                  </table>
                  <p><samp>[USERNAME]</samp>, <samp>[PASSWORD]</samp>, <samp>[DOMAIN]</samp> and <samp>[IP]</samp> are placeholders</p></br>
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
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">NTP Settings</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="action_id" name="action" value="ntp">
                  <table>
                    <tr>
                      <td>Current Time</td>
                      <td><input type="text" size="25" id="username_id" name="username" class="form-control" value="<?php echo exec("date"); ?>" readonly></td>
                    </tr>
                    <tr>
                      <td>Timezone</td>
                      <td>
                        <select required name="timezone" class="form-control input-sm">
                          <?php
                          $timezone = exec("uci get system.@system[0].timezone");
                          foreach ($SUPPORTED_TIMEZONES as $SUPPORTED_TIMEZONE) {
                            if ($SUPPORTED_TIMEZONE == $timezone) {
                              echo '<option selected="selected" value="'.$SUPPORTED_TIMEZONE.'">'.strtoupper($SUPPORTED_TIMEZONE).'</option>';
                            } else {
                              echo '<option value="'.$SUPPORTED_TIMEZONE.'">'.strtoupper($SUPPORTED_TIMEZONE).'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>NTP Server</td>
                      <td><input required type="text" id="ntp_server_id" name="ntp_server" class="form-control" value="<?php echo exec("uci get system.ntp.server"); ?>"></td>
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
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Scheduled Reboot</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="action_id" name="action" value="reboot">
                  <table>
		    <tr>
                      <td>Enabled</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $enable = exec("uci get tasks.reboot.enabled");
                          if ($enable == "1") {
                            echo '<input name="enabled" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="enabled" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Current Time</td>
                      <td><input type="text" size="25" id="username_id" name="username" class="form-control" value="<?php echo exec("date"); ?>" readonly></td>
                    </tr>
		    <tr>
                      <td>Sunday</td>
		      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $sun = exec("uci get tasks.reboot.sunday");
                          if ($sun == "1") {
                            echo '<input name="sun" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="sun" type="checkbox">';
                          }
                          ?>
                          <span></span>
		        </label>
		      </td>
		    </tr>
		    <tr>
		    <td>Monday</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $mon = exec("uci get tasks.reboot.monday");
                          if ($mon == "1") {
                            echo '<input name="mon" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="mon" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
		    </tr>
		    <tr>
		    <td>Tuesday</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $tue = exec("uci get tasks.reboot.tuesday");
                          if ($tue == "1") {
                            echo '<input name="tue" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="tue" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
		   </tr>
		   <tr>
		    <td>Wednesday</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $wed = exec("uci get tasks.reboot.wednesday");
                          if ($wed == "1") {
                            echo '<input name="wed" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="wed" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
		   </tr>
		   <tr>
		    <td>Thursday</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $thu = exec("uci get tasks.reboot.thursday");
                          if ($thu == "1") {
                            echo '<input name="thu" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="thu" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
		   </tr>
		   <tr>
		    <td>Friday</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $fri = exec("uci get tasks.reboot.friday");
                          if ($fri == "1") {
                            echo '<input name="fri" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="fri" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
		   </tr>
		   <tr>
		    <td>Saturday</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $sat = exec("uci get tasks.reboot.saturday");
                          if ($sat == "1") {
                            echo '<input name="sat" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="sat" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
		      </td>
		     </tr>
                      </td>
                      <td>Time</td>
                      <td><input required type="text" pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$" placeholder="HH:MM" id="time_id" name="time" class="form-control" value="<?php echo exec("uci get tasks.reboot.time"); ?>"></td>
                    </tr>
		    <tr>
                      <td>Last Rebooted</td>
                      <td><input type="text" size="25" id="last_reboot_id" name="last_reboot" class="form-control" value="<?php echo exec("uci get tasks.reboot.last_reboot"); ?>" readonly></td>
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
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Syslog Settings</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" action="system.php" method="post">
                  <input type="hidden" id="action_id" name="action" value="syslog">

                  <table>
                    <tr>
                      <td>Log Buffer Size (KiB)</td>
                      <td><input required type="number" id="log_size_id" name="log_size" class="form-control" value="<?php echo exec("uci get system.@system[0].log_size"); ?>"></td>
                    </tr>
                    
                    <tr>
                      <td>Export Syslog</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $log_remote = exec("uci get system.@system[0].log_remote");
                          if ($log_remote == "1") {
                            echo '<input name="log_remote" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="log_remote" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    
                    <tr>
                      <td>Log Server IP</td>
                      <td><input required type="text" id="log_ip_id" name="log_ip" class="form-control" value="<?php echo exec("uci get system.@system[0].log_ip"); ?>"></td>
                    </tr>
                    
                    
                    <tr>
                      <td>Log Server Port</td>
                      <td><input required type="text" id="log_port_id" name="log_port" class="form-control" value="<?php echo exec("uci get system.@system[0].log_port"); ?>"></td>
                    </tr>
                    
                    <tr>
                      <td>Protocol</td>
                      <td>
                        <select required name="log_proto" class="form-control input-sm">
                          <?php
                          $log_proto = exec("uci get system.@system[0].log_proto");
                          foreach ($LOG_EXPORT_PROTOCOLS as $LOG_EXPORT_PROTOCOL) {
                            if (strtoupper($LOG_EXPORT_PROTOCOL) == strtoupper($log_proto)) {
                              echo '<option selected="selected" value="'.$LOG_EXPORT_PROTOCOL.'">'.$LOG_EXPORT_PROTOCOL.'</option>';
                            } else {
                              echo '<option value="'.$LOG_EXPORT_PROTOCOL.'">'.$LOG_EXPORT_PROTOCOL.'</option>';
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

<?php startblock('scriptblock') ?>
<script>
$(document).ready(function() {
  toastr.options = {
    positionClass: "toast-top-full-width"
  };

  $("#system_settings_button").click(function() {
    
    var whitelist_numbers = [];            
    $('input[name^=whitelist_numbers]').each(function(){
      whitelist_numbers.push($(this).val());
    });

    $.post("system.php",
    {
      action : "system",
      hostname : $("#hostname_id").val() ,
      listen_port : $("#listen_port_id").val() ,
      auto_backup : $("#auto_backup_id").prop("checked") ? "on" : "off", 
      cloud_domain : $("#cloud_domain_id").val() ,
      nms_domain : $("#nms_domain_id").val() ,
      sms_management : $("#sms_management_id").prop("checked") ? "on" : "off",
      whitelist_numbers : whitelist_numbers ,
      remote_dashboard : $("#system_remote_dash").prop('checked') ? "on" : "off",
    })
    .done( function(msg) {
      toastr.success('System Settings Saved</br>Restart device incase web server port changed', "Success")
    })
    .fail( function(xhr, textStatus, errorThrown) {
      toastr.warning('Failed to save settings', "Error")
    });
  });

  var wrapper = $(".filter_table");
  var add_button = $(".add_domain_button");

  $(add_button).click(function(e) {
    e.preventDefault();
    $(wrapper).append('<input type="text" name="whitelist_numbers[]" class="form-control"><br>');
  });


});
</script>
<?php endblock() ?>
