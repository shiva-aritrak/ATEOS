<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Firewall | Add/Edit Zone</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>

<?php

$filled_zone_no = "99";

if(count($_GET) > 0) {
  $filled_zone_no = $_GET["zone_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_firewall_zone($filled_zone_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }

  if ($action == "moveUp") {
    move_zone_up($filled_zone_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }
  
  if ($action == "moveDown") {
    move_zone_down($filled_zone_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }

}

if(count($_POST) > 0) {
  $zone_no = $_POST["zone_no"];
  $zone_name = $_POST["zone_name"];
  $src_zones = $_POST["src_zones"];
  $subnet = $_POST["subnet"];
  $incoming_packets = $_POST["incoming_packets"];
  $forwarding_packets = $_POST["forwarding_packets"];
  $outgoing_packets = $_POST["outgoing_packets"];
  $masquerade = $_POST["masquerade"];
  $v6nat = $_POST["v6nat"];
  $mtu_fix = $_POST["mtu_fix"];

  create_firewall_zone($zone_no, $zone_name, $src_zones, $subnet, $incoming_packets, $forwarding_packets, $outgoing_packets, $masquerade, $v6nat, $mtu_fix);
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
                <h3 class="panel-title">Add Zone</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="firewall_zone_add_form" action="zones.php" method="post">
                  <input type="hidden" id="zone_no_id" name="zone_no" value="<?php if (count($_GET) > 0) { echo $filled_zone_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Zone Name</td>
                      <td><input required type="text" id="zone_name_id" name="zone_name"  pattern="^[A-Za-z0-9-]{2,26}$" class="form-control" value="<?php  echo exec("uci get firewall.@zone[".$filled_zone_no."].name");?>"></td>
                    </tr>
                    <tr>
                      <td>Interfaces</td>
                      <td>
                        <select name="src_zones[]" multiple class="form-control input-sm">
                          <?php
                          $selected_src_zone = exec("uci get firewall.@zone[".$filled_zone_no."].network");
                          foreach (get_configured_interfaces() as $intf) {
                            if (in_array($intf, show_zone_networks($selected_src_zone))) {
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
                      <td>Subnets</td>
                      <td><input pattern="^((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/([0-9]|[1-2][0-9]|3[0-2])[,]?)){1,}$" type="text" id="subnet_id" name="subnet" class="form-control" value="<?php  $get = exec("uci get firewall.@zone[".$filled_zone_no."].subnet");
                      $out=array();
                      foreach (explode(" ", $get) as $remote_subnet) {
                        array_push($out, explode(' ', trim($remote_subnet, "'"))[0]);
                      }  echo implode(",",$out); ?>"></td>
                    </tr>
                    <tr>
                      <td>Incoming Action</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $zone_input_action = exec("uci get firewall.@zone[".$filled_zone_no."].input");
                          if ($zone_input_action == "ACCEPT") {
                            echo '<input name="incoming_packets" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="incoming_packets" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($zone_input_action == "DROP") {
                            echo '<input name="incoming_packets" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="incoming_packets" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Forwarding Action</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $zone_input_action = exec("uci get firewall.@zone[".$filled_zone_no."].forward");
                          if ($zone_input_action == "ACCEPT") {
                            echo '<input name="forwarding_packets" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="forwarding_packets" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($zone_input_action == "DROP") {
                            echo '<input name="forwarding_packets" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="forwarding_packets" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Outgoing Action</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $zone_input_action = exec("uci get firewall.@zone[".$filled_zone_no."].output");
                          if ($zone_input_action == "ACCEPT") {
                            echo '<input name="outgoing_packets" value="ACCEPT" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="outgoing_packets" value="ACCEPT" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Accept</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($zone_input_action == "DROP") {
                            echo '<input name="outgoing_packets" value="DROP" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="outgoing_packets" value="DROP" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Drop</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Masquerade (IPv4 NAT)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $drop_invalid = exec("uci get firewall.@zone[".$filled_zone_no."].masq");
                          if ($drop_invalid == "1") {
                            echo '<input name="masquerade" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="masquerade" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>NAT66</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $v6nat = exec("uci get firewall.@zone[".$filled_zone_no."].v6nat");
                          if ($v6nat == "1") {
                            echo '<input name="v6nat" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="v6nat" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>MSS Clamping</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $drop_invalid = exec("uci get firewall.@zone[".$filled_zone_no."].mtu_fix");
                          if ($drop_invalid == "1") {
                            echo '<input name="mtu_fix" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="mtu_fix" type="checkbox">';
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
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
