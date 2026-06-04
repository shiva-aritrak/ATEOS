<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Routing | SD-WAN</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/balancing.php' ?>

<?php

$balanced_interfaces = get_balanced_interfaces();
$interface_status = get_balancing_status();

if (count($_GET) > 0) {
  exec("uci show network.route".$route_id, $out, $ret);
}

if (count($_POST) > 0) {
  $mode = $_POST["mode"];

  $percent = $_POST["percent"];
  $priority = $_POST["priority"];
  $ba_interfaces = $_POST["ba_interfaces"];
  $local_source_interface = $_POST["local_source_interface"];
  $rt_table_lookup = $_POST["rt_table_lookup"];
  $def_rule_https_sticky = $_POST["def_rule_https_sticky"];
  $def_all_rule_sticky = $_POST["def_all_rule_sticky"];
  $sticky_timeout = $_POST["sticky_timeout"];
  $last_resort = $_POST["last_resort"];

  $interfaces = $_POST["interfaces"];
  if ($mode == "llb") {
    $total_percent = 0;
    foreach ($interfaces as $interface) {
      $total_percent = $total_percent + (int)$percent[$interface];
    }
    if ($total_percent != 100) {
      $percent_incorrect = true;
    }
  } elseif ($mode == "flb") {
    if (count(array_unique($priority))<count($priority)) {
      $same_priority = true;
    }
  }
  if (!$percent_incorrect && !$same_priority) {
    set_lb_config($local_source_interface, $rt_table_lookup, $mode, $interfaces, $ba_interfaces, $percent, $priority, $def_rule_https_sticky, $def_all_rule_sticky, $sticky_timeout, $last_resort);
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
              <div class="panel-heading">
                <h3 class="panel-title">SD-WAN Global Configuration</h3>
              </div>
              <div class="panel-body">
                <?php
                if ($percent_incorrect) {
                  echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                  echo '  <i class="fa fa-warning"></i> Alert! Total Percentage Distribution is '.$total_percent.'% Should be 100%</p>';
                  echo '</div>';
                }
                if ($same_priority) {
                  echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                  echo '  <i class="fa fa-warning"></i> Alert! Cannot have equal priority in Failover Load Balance mode</p>';
                  echo '</div>';
                }
                ?>
                <form enctype="multipart/form-data" id="aggreagation_form" action="aggregation.php" method="post">
                  <?php
                  foreach ($balanced_interfaces as $bal_interface) {
                    $out = exec("uci get mwan3.".$bal_interface.".enabled");
                    if ($out == "1") {
                      echo "<input type='hidden' id='".$bal_interface."_percent_id' name='interfaces[]' class='form-control' value='".$bal_interface."'>";
                    }
                  }
                  ?>
                  <table>
                    <tr>
                      <td>Router Outgoing Interface</td>
                      <td>
                        <select required name="local_source_interface" class="form-control input-sm">
                          <?php
                          $local_source = exec("uci get mwan3.globals.local_source");
                          echo '<option value="none">Default Routing</option>';
                          foreach (get_configured_interfaces() as $intf) {
                            if (strtoupper($intf) == strtoupper($local_source)) {
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
                    <td>Import Routes from Table</td>
                      <td>
                        <select required name="rt_table_lookup" class="form-control input-sm">
                          <?php
                          $rt_table_lookup = exec("uci get mwan3.globals.rt_table_lookup");
                          echo '<option value="none" selected>None</option>';
                          foreach (get_all_routing_tables() as $intf) {
                            $table_no_name = preg_split('/\s+/', $intf);
                            if ($rt_table_lookup == $table_no_name[0]) {
                              echo '<option selected="selected" value="'.$table_no_name[0].'">'.ucwords($table_no_name[1]).'</option>';
                            } else {
                              echo '<option value="'.$table_no_name[0].'">'.ucwords($table_no_name[1]).'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Sticky for HTTPS</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $def_rule_https_sticky = exec("uci get mwan3.def_rule_https.sticky");
                          if ($def_rule_https_sticky == "1") {
                            echo '<input name="def_rule_https_sticky" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="def_rule_https_sticky" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>                    
                    <tr>
                      <td>Sticky on Source IP</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $def_all_rule_sticky = exec("uci get mwan3.def_all_rule.sticky");
                          if ($def_all_rule_sticky == "1") {
                            echo '<input name="def_all_rule_sticky" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="def_all_rule_sticky" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Sticky Timeout</td>
                      <td>
                        <input type="number" id="sticky_timeout_id" name="sticky_timeout" class="form-control" value="<?php  echo exec("uci get mwan3.def_all_rule.timeout"); ?>">  
                      </td>
                    </tr>
                    
                    <tr>
                      <td>Mode</td>
                      <td>
                        <label class="fancy-radio">
                          <input name="mode" value="disabled" id="mode_disabled" type="radio" required>
                          <span><i></i>Disabled</span>
                        </label>
                        <label class="fancy-radio">
                          <input name="mode" value="ba" type="radio" id="mode_ba" >
                          <span><i></i>Bandwith Aggregation</span>
                        </label>
                        <label class="fancy-radio">
                          <input name="mode" value="llb" type="radio" id="mode_llb" >
                          <span><i></i>Load Balance by Load Level</span>
                        </label>
                        <label class="fancy-radio">
                          <input name="mode" value="flb" type="radio" id="mode_flb" >
                          <span><i></i>Failover Load Balance by Priority</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Fallback Routing Option</td>
                      <td>
                        <label class="fancy-radio">
                          <?php 
                          $routing_last_resort = exec("uci get mwan3.def_all_rule.routing_last_resort");
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

                    <table id="ba_mode_form" class="content">
                      <tr>
                        <td>Aggregation Interfaces</td>
                        <td>
                          <select name="ba_interfaces[]" multiple class="form-control input-sm">
                            <?php
                            $enabled_interfaces = get_default_ba_interfaces();
                            foreach ($balanced_interfaces as $bal_interface) {
                              $out = exec("uci get mwan3.".$bal_interface.".enabled");
                              if ($out == "1") {
                                if (in_array($bal_interface, $enabled_interfaces)) {
                                  echo "<pre>".var_dump($enabled_interfaces)."</pre>";
                                  echo '<option selected="selected" value="'.$bal_interface.'">'.strtoupper($bal_interface).'</option>';
                                } else {
                                  echo '<option value="'.$bal_interface.'">'.strtoupper($bal_interface).'</option>';
                                }
                              }
                            }
                            ?>
                          </select>
                        </td>
                      </tr>
                    </table>
                    <table id="llb_mode_form" class="content">
                      <?php
                      foreach ($balanced_interfaces as $bal_interface) {
                        $out = exec("uci get mwan3.".$bal_interface.".enabled");
                        if ($out == "1") {
                          echo "<tr>";
                          echo "<td>".strtoupper($bal_interface)." Load Percentage</td>";
                          echo "<td><input type='number' id='".$bal_interface."_percent_id' name='percent[".$bal_interface."]' class='form-control' value=".get_percent_for_interface($bal_interface)."></td>";
                          echo "</tr>";
                        }
                      }
                      ?>
                    </table>
                    <table id="flb_mode_form" class="content">
                      <?php
                      foreach ($balanced_interfaces as $bal_interface) {
                        $out = exec("uci get mwan3.".$bal_interface.".enabled");
                        if ($out == "1") {
                          echo "<tr>";
                          echo "<td>".strtoupper($bal_interface)." Priority</td>";
                          echo "<td><input type='number' id='".$bal_interface."_priority_id' name='priority[".$bal_interface."]' class='form-control' value=".get_priority_for_interface($bal_interface)."></td>";
                          echo "</tr>";
                        }
                      }
                      ?>
                    </table>
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
        <div class="col-md-8">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">SD-WAN Interfaces <div class="pull-right"><a href="/routing/balancing/interface.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="rules_div_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Interface</th>
                      <th>Connectivity</br>Status</th>
                      <th>Interface</br>Status</th>
                      <th>Ping IPs</th>
                      <th>Min</br>Responses</th>
                      <th>Min Ping</br>Responses</th>
                      <th>Ping</br>Timeout</th>
                      <th>Ping</br>Interval</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($balanced_interfaces as $bal_interface) {
                      echo '<tr>';
                      echo '<td>'.strtoupper($bal_interface).'</td>';
                      if ($interface_status[$bal_interface] == "online") {
                        echo '<td><span class="label label-success" id="'.$intf.'_status">'.ucfirst($interface_status[$bal_interface]).'</span></td>';
  										} else {
  											echo '<td><span class="label label-danger" id="'.$intf.'_status">'.ucfirst($interface_status[$bal_interface]).'</span></td>';
  										}
                      echo '<td>'.show_enabled_disabled(exec("uci get mwan3.".$bal_interface.".enabled")).'</td>';
                      echo '<td>';
                      foreach (show_zone_networks(exec("uci get mwan3.".$bal_interface.".track_ip")) as $track_ip) {
                        echo ''.$track_ip.'</br>';
                      }
                      echo '</td>';
                      echo '<td>'.exec("uci get mwan3.".$bal_interface.".reliability").'</td>';
                      echo '<td>'.exec("uci get mwan3.".$bal_interface.".count").'</td>';
                      echo '<td>'.exec("uci get mwan3.".$bal_interface.".timeout").'</td>';
                      echo '<td>'.exec("uci get mwan3.".$bal_interface.".interval").'</td>';
                      echo '<td><a href="/routing/balancing/interface.php?interface='.$bal_interface.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="/routing/balancing/interface.php?action=delete&interface='.$bal_interface.'"><i class="fa fa-times"></i></a></td>';
                      echo '</tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">SD-WAN Failover Policy <div class="pull-right"><a href="/routing/balancing/routing.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="rules_div_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Protocol</th>
                      <th>Source IP</th>
                      <th>Source Port/Range</th>
                      <th>Destination IP</th>
                      <th>Destination Port/Range</th>
                      <th>Session Adherence</th>
                      <th>Priority</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $rule_id = 0;
                    $counter = 0;
                    exec("uci show mwan3.@rule[".$rule_id."]", $out, $ret);
                    while ($ret == 0) {
                      $rule_name = get_rule_name($rule_id);
                      if ($rule_name != "def_rule_https" && $rule_name != "def_all_rule") {
                        echo '<tr>';
                        echo '  <td>'.($counter + 1).'</td>';
                        echo '  <td>'.show_qos_proto(exec("uci get mwan3.@rule[".$rule_id."].proto")).'</td>';
                        echo '  <td>'.show_qos_hosts(exec("uci get mwan3.@rule[".$rule_id."].src_ip")).'</td>';
                        echo '  <td>'.show_qos_ports(exec("uci get mwan3.@rule[".$rule_id."].src_port")).'</td>';
                        echo '  <td>'.show_qos_hosts(exec("uci get mwan3.@rule[".$rule_id."].dest_ip")).'</td>';
                        echo '  <td>'.show_qos_ports(exec("uci get mwan3.@rule[".$rule_id."].dest_port")).'</td>';
                        echo '  <td>'.show_enabled_disabled(exec("uci get mwan3.@rule[".$rule_id."].sticky")).'</td>';
                        // echo '  <td>'.exec("uci get mwan3.@rule[".$rule_id."].use_policy").'</td>';
                        echo '  <td>'.get_html_for_policy(exec("uci get mwan3.@rule[".$rule_id."].use_policy")).'</td>';
                        echo '  <td><a href="/routing/balancing/routing.php?rule_no='.$rule_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="/routing/balancing/routing.php?action=delete&rule_no='.$rule_id.'"><i class="fa fa-times"></i></a></td>';
                        echo '</tr>';
                        ++$counter;
                      }
                      ++$rule_id;
                      exec("uci show mwan3.@rule[".$rule_id."]", $out, $ret);
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
<?php endblock() ?>

<script type="text/javascript">
$(document).ready(function() {
  <?php
  $mode = exec("uci get mwan3.def_all_rule.use_policy");
  echo 'var mode="'.$mode.'";';
  ?>
  $("#llb_mode_form").hide();
  $("#flb_mode_form").hide();
  $("#ba_mode_form").hide()

  if (mode == "def_llb_bal") {
    $("#llb_mode_form").show();
    $("#mode_llb").attr('checked', 'checked');
  } else if ( mode == "def_flb_bal") {
    $("#flb_mode_form").show();
    $("#mode_flb").attr('checked', 'checked');
  } else if ( mode == "def_ba_bal") {
    $("#ba_mode_form").show();
    $("#mode_ba").attr('checked', 'checked');
  } else {
    $("#mode_disabled").attr('checked', 'checked');
  }


  $("#mode_disabled").click(function() {
    $("#llb_mode_form").hide();
    $("#flb_mode_form").hide();
    $("#ba_mode_form").hide()
  });
  $("#mode_llb").click(function() {
    $("#flb_mode_form").hide();
    $("#ba_mode_form").hide()
    $("#llb_mode_form").show();
  });
  $("#mode_flb").click(function() {
    $("#llb_mode_form").hide();
    $("#ba_mode_form").hide()
    $("#flb_mode_form").show();
  });
  $("#mode_ba").click(function() {
    $("#llb_mode_form").hide();
    $("#flb_mode_form").hide();
    $("#ba_mode_form").show()
  });

});
</script>
