<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Firewall | Rules</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>

<?php

$zone_id = 0;
$out = "";
$ret = 99;
$zones_html_out = "";

exec("uci show firewall.@zone[".$zone_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $zones_html_out = $zones_html_out.'<tr>';
  $zones_html_out = $zones_html_out.'  <td><code>'.exec("uci get firewall.@zone[".$zone_id."].name").'</code></td>';
  $zones_html_out = $zones_html_out.'  <td>';
  foreach (show_zone_networks(exec("uci get firewall.@zone[".$zone_id."].network")) as $wan) {
    $zones_html_out = $zones_html_out.'<kbd>'.$wan.'</kbd> ';
  }
  $zones_html_out = $zones_html_out.'  </td>';
  $subnet = exec("uci get firewall.@zone[".$zone_id."].subnet");
  if ($subnet) {
    $zones_html_out = $zones_html_out.'  <td>'.$subnet.'</td>';
  } else {
    $zones_html_out = $zones_html_out.'  <td>0.0.0.0/0</td>';
  }
  $zones_html_out = $zones_html_out.'  <td>'.show_action(exec("uci get firewall.@zone[".$zone_id."].input")).'</td>';
  $zones_html_out = $zones_html_out.'  <td>'.show_action(exec("uci get firewall.@zone[".$zone_id."].forward")).'</td>';
  $zones_html_out = $zones_html_out.'  <td>'.show_action(exec("uci get firewall.@zone[".$zone_id."].output")).'</td>';
  $zones_html_out = $zones_html_out.'  <td>'.show_yes_no(exec("uci get firewall.@zone[".$zone_id."].masq")).'</td>';
  $zones_html_out = $zones_html_out.'  <td>'.show_yes_no(exec("uci get firewall.@zone[".$zone_id."].mtu_fix")).'</td>';
  $zones_html_out = $zones_html_out.'  <td><a href="/firewall/add/zones.php?action=moveUp&zone_no='.$zone_id.'"><i class="fa fa-arrow-up"></i></a> &nbsp;&nbsp; <a href="/firewall/add/rule.php?action=moveDown&zone_no='.$zone_id.'"><i class="fa fa-arrow-down"></i></a></td>';
  $zones_html_out = $zones_html_out.'  <td><a href="/firewall/add/zones.php?zone_no='.$zone_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="/firewall/add/zones.php?action=delete&zone_no='.$zone_id.'"><i class="fa fa-times"></i></a></td>';
  $zones_html_out = $zones_html_out.'</tr>';

  ++$zone_id;
  exec("uci show firewall.@zone[".$zone_id."]" , $out, $ret);
}

$rules_html_out = "";
$rule_id = 0;
$ret = 99;

exec("uci show firewall.@rule[".$rule_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $rules_html_out = $rules_html_out.'<tr>';
  $rules_html_out = $rules_html_out.'  <td>'.show_enabled_disabled(exec("uci get firewall.@rule[".$rule_id."].enabled")).'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].name").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].src").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].src_ip").''.exec("uci get firewall.@rule[".$rule_id."].src_mac").':'.exec("uci get firewall.@rule[".$rule_id."].src_port").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].dest").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].dest_ip").':'.exec("uci get firewall.@rule[".$rule_id."].dest_port").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.strtoupper(exec("uci get firewall.@rule[".$rule_id."].proto")).'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].start_date").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].start_time").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].stop_date	").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.exec("uci get firewall.@rule[".$rule_id."].stop_time").'</td>';
  $rules_html_out = $rules_html_out.'  <td>'.show_action(exec("uci get firewall.@rule[".$rule_id."].target")).'</td>';
  $rules_html_out = $rules_html_out.'  <td><a href="/firewall/add/rule.php?action=moveUp&rule_no='.$rule_id.'"><i class="fa fa-arrow-up"></i></a> &nbsp;&nbsp; <a href="/firewall/add/rule.php?action=moveDown&rule_no='.$rule_id.'"><i class="fa fa-arrow-down"></i></a></td>';
  $rules_html_out = $rules_html_out.'  <td><a href="/firewall/add/rule.php?rule_no='.$rule_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="/firewall/add/rule.php?action=delete&rule_no='.$rule_id.'"><i class="fa fa-times"></i></a></td>';
  $rules_html_out = $rules_html_out.'</tr>';

  ++$rule_id;
  exec("uci show firewall.@rule[".$rule_id."]" , $out, $ret);
}

$nat_forwarding_html_out = "";
$nat_fw_id = 0;
$ret = 99;

exec("uci show firewall.@forwarding[".$nat_fw_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $nat_forwarding_html_out = $nat_forwarding_html_out.'<tr>';

  $nat_forwarding_html_out = $nat_forwarding_html_out.'  <td>';
  foreach (show_zone_networks(exec("uci get firewall.@forwarding[".$nat_fw_id."].src")) as $wan) {
    $nat_forwarding_html_out = $nat_forwarding_html_out.'<code>'.$wan.'</code> ';
  }
  $nat_forwarding_html_out = $nat_forwarding_html_out.'  </td>';

  $nat_forwarding_html_out = $nat_forwarding_html_out.'  <td>';
  foreach (show_zone_networks(exec("uci get firewall.@forwarding[".$nat_fw_id."].dest")) as $wan) {
    $nat_forwarding_html_out = $nat_forwarding_html_out.'<code>'.$wan.'</code> ';
  }
  $nat_forwarding_html_out = $nat_forwarding_html_out.'  </td>';

  $nat_forwarding_html_out = $nat_forwarding_html_out.'  <td><a href="/firewall/add/forwarding.php?forward_no='.$nat_fw_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="/firewall/add/forwarding.php?action=delete&forward_no='.$nat_fw_id.'"><i class="fa fa-times"></i></a></td>';

  $nat_forwarding_html_out = $nat_forwarding_html_out.'</tr>';

  ++$nat_fw_id;
  exec("uci show firewall.@forwarding[".$nat_fw_id."]" , $out, $ret);
}

if(count($_POST) > 0) {

  $incoming_traffic = $_POST["incoming_traffic"];
  $forwarding_traffic = $_POST["forwarding_traffic"];
  $outgoing_traffic = $_POST["outgoing_traffic"];
  $drop_invalid = $_POST["drop_invalid"];
  $tcp_window_scaling = $_POST["tcp_window_scaling"];
  $synflood_protect = $_POST["synflood_protect"];
  $tcp_syncookies = $_POST["tcp_syncookies"];
  $tcp_ecn = $_POST["tcp_ecn"];
  $tcp_westwood = $_POST["tcp_westwood"];
  $synflood_burst = $_POST["synflood_burst"];
  $flow_offloading = $_POST["flow_offloading"];
  $flow_offloading_hw = $_POST["flow_offloading_hw"];

  set_firewall_defaults($incoming_traffic, $forwarding_traffic, $outgoing_traffic, $drop_invalid, $tcp_window_scaling, $synflood_protect, $tcp_syncookies, $tcp_ecn, $tcp_westwood, $synflood_burst, $flow_offloading, $flow_offloading_hw);
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
                <h3 class="panel-title">Global Defaults</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="firewall_main_form" action="rules.php" method="post">
                  <table>
                    <tr>
                      <td>Incoming Traffic</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $fw_defaults_input = exec("uci get firewall.@defaults[0].input");
                          if ($fw_defaults_input == "ACCEPT") {
                            echo '<input name="incoming_traffic" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="incoming_traffic" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($fw_defaults_input == "DROP") {
                            echo '<input name="incoming_traffic" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="incoming_traffic" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Forwarding Traffic</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $fw_defaults_forward = exec("uci get firewall.@defaults[0].forward");
                          if ($fw_defaults_forward == "ACCEPT") {
                            echo '<input name="forwarding_traffic" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="forwarding_traffic" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($fw_defaults_forward == "DROP") {
                            echo '<input name="forwarding_traffic" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="forwarding_traffic" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Outgoing Traffic</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $fw_defaults_output = exec("uci get firewall.@defaults[0].output");
                          if ($fw_defaults_output == "ACCEPT") {
                            echo '<input name="outgoing_traffic" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="outgoing_traffic" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($fw_defaults_output == "DROP") {
                            echo '<input name="outgoing_traffic" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="outgoing_traffic" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Drop Invalid Packets</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $drop_invalid = exec("uci get firewall.@defaults[0].drop_invalid");
                          if ($drop_invalid == "1") {
                            echo '<input name="drop_invalid" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="drop_invalid" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>TCP Window Scaling</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $drop_invalid = exec("uci get firewall.@defaults[0].tcp_window_scaling");
                          if ($drop_invalid == "1") {
                            echo '<input name="tcp_window_scaling" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="tcp_window_scaling" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>TCP SYN Flood Protection</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $tcp_dyn_flood_protect = exec("uci get firewall.@defaults[0].synflood_protect");
                          if ($tcp_dyn_flood_protect == "1") {
                            echo '<input name="synflood_protect" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="synflood_protect" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Max SYN Flood Burst</td>
                      <td><input type="text" id="synflood_burst_id" name="synflood_burst" class="form-control" value="<?php  echo exec("uci get firewall.@defaults[0].synflood_burst");?>"></td>
                    </tr>
                    <tr>
                      <td>TCP SYN Cookies</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $tcp_syn_cookies = exec("uci get firewall.@defaults[0].tcp_syncookies");
                          if ($tcp_syn_cookies == "1") {
                            echo '<input name="tcp_syncookies" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="tcp_syncookies" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>TCP Explicit Congestion Notification</br>(ECN)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $tcp_ecn = exec("uci get firewall.@defaults[0].tcp_ecn");
                          if ($tcp_ecn == "1") {
                            echo '<input name="tcp_ecn" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="tcp_ecn" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>TCP Westwood</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $tcp_westwood = exec("uci get firewall.@defaults[0].tcp_westwood");
                          if ($tcp_westwood == "1") {
                            echo '<input name="tcp_westwood" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="tcp_westwood" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Software Flow Offloading</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $flow_offloading = exec("uci get firewall.@defaults[0].flow_offloading");
                          if ($flow_offloading == "1") {
                            echo '<input name="flow_offloading" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="flow_offloading" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Hardware NAT</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $flow_offloading_hw = exec("uci get firewall.@defaults[0].flow_offloading_hw");
                          if ($flow_offloading_hw == "1") {
                            echo '<input name="flow_offloading_hw" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="flow_offloading_hw" type="checkbox">';
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
                <h3 class="panel-title">Zones <div class="pull-right"><a href="/firewall/add/zones.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="zones_div_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Interfaces</th>
                      <th>Subnet</th>
                      <th>Incoming<br>Action</th>
                      <th>Forwarding<br>Action</th>
                      <th>Output<br>Action</th>
                      <th>Masquerading</th>
                      <th>MSS</th>
                      <th></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    echo $zones_html_out;
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">NAT Forwarding <div class="pull-right"><a href="/firewall/add/forwarding.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="forwarding_div_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Source Zone</th>
                      <th>Destination Zone</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    echo $nat_forwarding_html_out;
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
                <h3 class="panel-title">Rules <div class="pull-right"><a href="/firewall/add/rule.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="rules_div_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Status</th>
                      <th>Name</th>
                      <th>Source Zone</th>
                      <th>Source</th>
                      <th>Destination Zone</th>
                      <th>Destination</th>
                      <th>Protocol</th>
                      <th>Start Date</th>
                      <th>Start Time</th>
                      <th>End Date</th>
                      <th>End Time</th>
                      <th>Action</th>
                      <th>Order</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    echo $rules_html_out;
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
