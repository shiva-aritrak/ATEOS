<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Firewall | Add/Edit Rule</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>

<?php

$filled_rule_no = "99";
if(count($_GET) > 0) {
  $filled_rule_no = $_GET["rule_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_firewall_rule($filled_rule_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }

  if ($action == "moveUp") {
    move_rule_up($filled_rule_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }
  
  if ($action == "moveDown") {
    move_rule_down($filled_rule_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }
}

if(count($_POST) > 0) {
  $rule_no = $_POST["rule_no"];
  $status = $_POST["status"];
  $rule_name = $_POST["rule_name"];
  $src_zone = $_POST["src_zone"];
  $src_type = $_POST["src_type"];
  $src =  str_replace(",", " ", str_replace("-", ":", $_POST["src"]));
  $src_port =  str_replace(",", " ", str_replace("-", ":", $_POST["src_port"]));
  $dst_zone = $_POST["dst_zone"];
  $dest =  str_replace(",", " ", str_replace("-", ":", $_POST["dest"]));
  $dest_port =  str_replace(",", " ", str_replace("-", ":", $_POST["dest_port"]));
  $protocol = $_POST["protocol"];
  $application = $_POST["application"];
  $daysofweek = $_POST["daysofweek"];
  $rule_start_date = $_POST["rule_start_date"];
  $rule_start_time = $_POST["rule_start_time"];
  $rule_end_date = $_POST["rule_end_date"];
  $rule_end_time = $_POST["rule_end_time"];
  $target_action = $_POST["target_action"];
  $src_ccs = $_POST["src_ccs"];
  $dst_ccs = $_POST["dst_ccs"];

  create_firewall_rule($rule_no, $status, $rule_name, $src_zone, $src_type, $src, $src_port, $dst_zone, $dest, $dest_port, $protocol, $application, $daysofweek, $rule_start_date, $rule_start_time, $rule_end_date, $rule_end_time, $target_action, $src_ccs, $dst_ccs);
  echo '<script>window.location.href = "/firewall/rules.php";</script>';
  exit;
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
                <h3 class="panel-title">Add Rule</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" id="firewall_rule_add_form" action="rule.php" method="post">
                  <input type="hidden" id="rule_no_id" name="rule_no" value="<?php if (count($_GET) > 0) { echo $filled_rule_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Rule Name</td>
                      <td><input required type="text" id="rule_name_id" pattern="^[A-Za-z0-9\-\ ]{2,}$" title="Rule name can have alphabets, numbers, dash and space only" name="rule_name" class="form-control" value="<?php  echo exec("uci get firewall.@rule[".$filled_rule_no."].name");?>"></td>
                    </tr>

                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $rule_status = exec("uci get firewall.@rule[".$filled_rule_no."].enabled");
                          if ($rule_status == "1") {
                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($rule_status == "0") {
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
                      <td>Source Zone</td>
                      <td>
                        <select required name="src_zone" class="form-control input-sm">
                          <?php
                          $rule_src_zones = exec("uci get firewall.@rule[".$filled_rule_no."].src");
                          foreach (get_configured_zones() as $intf) {
                            if (in_array($intf, show_zone_networks($rule_src_zones))) {
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
                      <td>Source Type</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $is_src_mac = 0;
                          $ret = 99;
                          exec("uci get firewall.@rule[".$filled_rule_no."].src_mac", $is_src_mac, $ret);
                          if ($ret != 0) {
                            echo '<input name="src_type" value="ip" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="src_type" value="ip" type="radio" required>';
                          }
                          ?>
                          <span><i></i>IP</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($ret == 0) {
                            echo '<input name="src_type" value="mac" checked="checked" type="radio" required>';
                            $is_src_mac = 1;
                          } else {
                            echo '<input name="src_type" value="mac" type="radio" required>';
                          }
                          ?>
                          <span><i></i>MAC Address</span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Source MAC/IP</td>
                      <td><input type="text" id="src_id" name="src" class="form-control" value="<?php if(!$is_src_mac)  { echo exec("uci get firewall.@rule[".$filled_rule_no."].src_ip"); } else { echo exec("uci get firewall.@rule[".$filled_rule_no."].src_mac"); } ?>"></td>
                    </tr>

                    <tr>
                      <td>Source Port</td>
                      <td><input type="text" id="src_port_id" name="src_port" class="form-control" value="<?php echo str_replace(" ", ",", str_replace(":", "-", exec("uci get firewall.@rule[".$filled_rule_no."].src_port")));?>"></td>
                    </tr>
                    <tr>
                      <td>Source Country</td>
                      <td>
                        <select name="src_ccs[]" multiple class="form-control input-sm">
                            <?php
                            $src_ccs = exec("uci get firewall.@rule[".$filled_rule_no."].src_ccs");
                            foreach ($GEOIP_COUNTRIES as $key => $value) {
                              if (in_array($key, explode(" ", $src_ccs) )) {
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
                      <td>Destination Zone</td>
                      <td>
                        <select required name="dst_zone" class="form-control input-sm">
                          <?php
                          $rule_dest_zones = exec("uci get firewall.@rule[".$filled_rule_no."].dest");
                          foreach (get_configured_zones() as $dest_zone) {
                            if (in_array($dest_zone, show_zone_networks($rule_dest_zones))) {
                              echo '<option selected="selected" value="'.$dest_zone.'">'.strtoupper($dest_zone).'</option>';
                            } else {
                              echo '<option value="'.$dest_zone.'">'.strtoupper($dest_zone).'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Destination IP</td>
                      <td><input type="text" id="dst_id" name="dest" class="form-control" value="<?php  echo exec("uci get firewall.@rule[".$filled_rule_no."].dest_ip");?>"></td>
                    </tr>
                    <tr>
                      <td>Destination Port</td>
                      <td><input type="text" id="dst_port_id" name="dest_port" class="form-control" value="<?php echo str_replace(" ", ",", str_replace(":", "-", exec("uci get firewall.@rule[".$filled_rule_no."].dest_port")));?>"></td>
                    </tr>
                    <tr>
                      <td>Destination Country</td>
                      <td>
                        <select name="dst_ccs[]" multiple class="form-control input-sm">
                            <?php
                            $dst_ccs = exec("uci get firewall.@rule[".$filled_rule_no."].dst_ccs");
                            foreach ($GEOIP_COUNTRIES as $key => $value) {
                              if (in_array($key, explode(" ", $dst_ccs) )) {
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
                      <td>Protocol</td>
                      <td>
                        <select required name="protocol" class="form-control input-sm">
                          <?php
                          $selected_dst_zone = exec("uci get firewall.@rule[".$filled_rule_no."].proto");
                          foreach ($SUPPORTED_PROTOCOLS as $intf) {
                            if ($intf == $selected_dst_zone) {
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
                      <td>Application</td>
                      <td>
                        <select name="application[]" multiple class="form-control input-sm">
                            <?php
                            $application = exec("uci get firewall.@rule[".$filled_rule_no."].ipset");
                            foreach ($RULES_APP_LIST as $key => $value) {
                              if (in_array($key, explode(" ", $application) )) {
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
                      <td>Days of Week</td>
                      <td>
                        <select name="daysofweek[]" multiple class="form-control input-sm">
                          <?php
                          $weekdays = exec("uci get firewall.@rule[".$filled_rule_no."].weekdays");
                          foreach ($DAYS_OF_WEEK as $dayofweek) {
                            if (in_array($dayofweek, explode(" ", $weekdays) )) {
                              echo '<option selected="selected" value="'.$dayofweek.'">'.ucfirst($dayofweek).'</option>';
                            } else {
                              echo '<option value="'.$dayofweek.'">'.ucfirst($dayofweek).'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Start Date</td>
                      <td>
                        <input type="date" name="rule_start_date" value="<?php  echo exec("uci get firewall.@rule[".$filled_rule_no."].start_date");?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>Start Time</td>
                      <td>
                        <input type="time" name="rule_start_time"value="<?php  echo exec("uci get firewall.@rule[".$filled_rule_no."].start_time");?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>End Date</td>
                      <td>
                        <input type="date" name="rule_end_date"value="<?php  echo exec("uci get firewall.@rule[".$filled_rule_no."].stop_date");?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>End Time</td>
                      <td>
                        <input type="time" name="rule_end_time" value="<?php  echo exec("uci get firewall.@rule[".$filled_rule_no."].stop_time");?>" />
                      </td>
                    </tr>
                    <tr>
                      <td>Action</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $fw_defaults_output = exec("uci get firewall.@rule[".$filled_rule_no."].target");
                          if ($fw_defaults_output == "ACCEPT") {
                            echo '<input name="target_action" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="target_action" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($fw_defaults_output == "DROP") {
                            echo '<input name="target_action" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="target_action" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
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
