<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Aggregation | Add/Edit SD-WAN Rule</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/balancing.php' ?>

<?php

$balanced_interfaces = get_balanced_interfaces();

$filled_rule_no = "99";
if(count($_GET) > 0) {
  $filled_rule_no = $_GET["rule_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_balancing_rule($filled_rule_no);
    echo '<script>window.location.href = "/routing/aggregation.php";</script>';
    exit;
  }
}

$rule_id = 0;
exec("uci show mwan3.@rule[".$rule_id."]" , $out, $ret);
while ( $ret == 0 ) {
  ++$rule_id;
  exec("uci show mwan3.@rule[".$rule_id."]" , $out, $ret);
}

if(count($_POST) > 0) {
  $new = $_POST["new"];
  $rule_no = $_POST["rule_no"];
  $src = $_POST["src"];
  $src_port = $_POST["src_port"];
  $dest = $_POST["dest"];
  $dest_port = $_POST["dest_port"];
  $application = $_POST["application"];
  $protocol = $_POST["protocol"];
  $session_adhere = $_POST["session_adhere"];
  $priority = $_POST["priority"];
  $interfaces = $_POST["interfaces"];
  $last_resort = $_POST["last_resort"];

  if ( count(array_unique(array_filter($priority))) < count(array_filter($priority)) ) {
    $same_priority = true;
  } else {
    add_balancing_rule($new, $rule_no, $src, $src_port, $dest, $dest_port, $protocol, $application, $session_adhere, $priority, $interfaces, $last_resort);
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
              if ($same_priority) {
                echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                echo '  <i class="fa fa-warning"></i> Alert! Cannot have equal priority in Failover Policy, Leave blank to exclude from policy</p>';
                echo '</div>';
              }
              ?>
              <div class="panel-heading">
                <h3 class="panel-title">Add SD-WAN Rule</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="routing_balancing_form" action="routing.php" method="post">
                  <?php
                  foreach ($balanced_interfaces as $bal_interface) {
                    $out = exec("uci get mwan3.".$bal_interface.".enabled");
                    if ($out == "1") {
                      echo "<input type='hidden' id='".$bal_interface."_percent_id' name='interfaces[]' class='form-control' value='".$bal_interface."'>";
                    }
                  }
                  ?>
                  <input type="hidden" id="new_id" name="new" value="<?php if (count($_GET) > 0) { echo "0"; } else { echo "1"; } ?>">
                  <input type="hidden" id="rule_no_id" name="rule_no" value="<?php if (count($_GET) > 0) { echo $filled_rule_no; } else { echo $rule_id; } ?>">
                  <table>
                    <tr>
                      <td>Source IP/Subnet</td>
                      <td><input type="text" id="src_id" name="src" class="form-control" value="<?php  echo exec("uci get mwan3.@rule[".$filled_rule_no."].src_ip");?>"></td>
                    </tr>
                    <tr>
                      <td>Source Port/Range</td>
                      <td><input type="text" id="src_port_id" name="src_port" class="form-control" value="<?php  echo exec("uci get mwan3.@rule[".$filled_rule_no."].src_port");?>"></td>
                    </tr>
                    <tr>
                      <td>Destination IP/Subnet</td>
                      <td><input type="text" id="dst_id" name="dest" class="form-control" value="<?php  echo exec("uci get mwan3.@rule[".$filled_rule_no."].dest_ip");?>"></td>
                    </tr>
                    <tr>
                      <td>Destination Port/Range</td>
                      <td><input type="text" id="dst_port_id" name="dest_port" class="form-control" value="<?php  echo exec("uci get mwan3.@rule[".$filled_rule_no."].dest_port");?>"></td>
                    </tr>
                    <tr>
                      <td>Protocol</td>
                      <td>
                        <select required name="protocol" class="form-control input-sm">
                          <?php
                          $selected_dst_zone = exec("uci get mwan3.@rule[".$filled_rule_no."].proto");
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
                            $application = exec("uci get mwan3.@rule[".$filled_rule_no."].ipset");
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
                      <td>Session Adherence</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $session_adhere = exec("uci get mwan3.@rule[".$filled_rule_no."].sticky");
                          if ($session_adhere == "1") {
                            echo '<input name="session_adhere" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="session_adhere" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <?php
                    foreach ($balanced_interfaces as $bal_interface) {
                      $out = exec("uci get mwan3.".$bal_interface.".enabled");
                      if ($out == "1") {
                        echo "<tr>";
                        echo "<td>".strtoupper($bal_interface)." Priority</td>";
                        echo "<td><input type='number' min='1' max='10' id='".$bal_interface."_priority_id' name='priority[".$bal_interface."]' class='form-control' value=".get_priority_for_rule_interface($bal_interface, $filled_rule_no)."></td>";
                        echo "</tr>";
                      }
                    }
                    ?>
                    <tr>
                      <td>Fallback Routing Option</td>
                      <td>
                        <label class="fancy-radio">
                          <?php 
                          $routing_last_resort = exec("uci get mwan3.r".$filled_rule_no."_flb_bal.last_resort");
                          if ($routing_last_resort == "unreachable") {
                            echo '<input name="last_resort" value="unreachable" id="mode_sinkhole" type="radio" checked="checked" required>';
                          } else {
                            echo '<input name="last_resort" value="unreachable" id="mode_sinkhole" type="radio" required>';
                          }
                          echo '<span><i></i>Sinkhole</span>';
                          echo '</label>';
                          echo '<label class="fancy-radio">';
                          if ($routing_last_resort == "default") {
                            echo '<input name="last_resort" value="default" type="radio" id="mode_default" checked="checked">';
                          } else {
                            echo '<input name="last_resort" value="default" type="radio" id="mode_default">';
                          }
                          echo '<span><i></i>Use Default Routing</span>';
                          ?>
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
