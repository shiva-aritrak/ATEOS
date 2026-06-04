<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>QoS | Advanced</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/qos.php' ?>

<?php

$filled_classify = "99";

$classify_id = 0;
$out = "";
$ret = 99;
$qos_html_out = "";

$interfaces = get_configured_interfaces();

if(count($_POST) > 0) {
  $new_classify_id = $_POST["classify"];
  $wan_interface = $_POST["wan_interface"];
  $protocol = $_POST["protocol"];
  $source_ip_subnet = $_POST["source_ip_subnet"];
  $source_port = $_POST["source_port"];
  $dest_ip_subnet = $_POST["dest_ip_subnet"];
  $dest_port = $_POST["dest_port"];
  $comment = $_POST["comment"];
  $percent_limit = $_POST["percent_limit"];

  set_advanced_qos($new_classify_id, $wan_interface, $protocol, $source_ip_subnet, $source_port, $dest_ip_subnet, $dest_port, $percent_limit, $comment);
}


exec("uci show qos.@classify[".$classify_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $target = "";
  $interface = get_interface_from_classification_id($classify_id);
  $target = exec("uci get qos.@classify[".$classify_id."].target");
  $qos_html_out = $qos_html_out.'<tr>';
  $qos_html_out = $qos_html_out.'  <td>'.($classify_id + 1).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.strtoupper($interface).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.show_qos_proto(exec("uci get qos.@classify[".$classify_id."].proto")).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.show_qos_hosts(exec("uci get qos.@classify[".$classify_id."].srchost")).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.show_qos_ports(exec("uci get qos.@classify[".$classify_id."].srcports")).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.show_qos_hosts(exec("uci get qos.@classify[".$classify_id."].dsthost")).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.show_qos_ports(exec("uci get qos.@classify[".$classify_id."].dstports")).'</td>';
  $qos_html_out = $qos_html_out.'  <td>'.exec("uci get qos.".get_target_from_classification_id($classify_id).".limitrate").'% </td>';
  $qos_html_out = $qos_html_out.'  <td>'.exec("uci get qos.@classify[".$classify_id."].comment").'</td>';
  $qos_html_out = $qos_html_out.'  <td><a href="advanced.php?rule='.$classify_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="advanced.php?action=delete&rule='.$classify_id.'"><i class="fa fa-times"></i></a></td>';
  $qos_html_out = $qos_html_out.'</tr>';

  ++$classify_id;
  exec("uci show qos.@classify[".$classify_id."]" , $out, $ret);
}

if(count($_GET) > 0) {
  $filled_classify = $_GET["rule"];
  $action = $_GET["action"];
  if ($action == "delete") {
    delete_advanced_qos($filled_classify);
    echo '<script>window.location.href = "advanced.php";</script>';
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
              <div class="panel-heading">
                <h3 class="panel-title">Advanced QoS</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="advanced_qos_form" action="advanced.php" method="post">
                  <input type="hidden" id="classify_id" name="classify" value="<?php if (count($_GET) > 0) { echo $filled_classify; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>WAN Interface</td>
                      <td>
                        <select required name="wan_interface" class="form-control input-sm">
                          <?php
                          foreach ($interfaces as $intf) {
                            if ($intf == get_interface_from_classification_id($filled_classify)) {
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
                      <td>Protocol</td>
                      <td>
                        <select required name="protocol" class="form-control input-sm">
                          <?php
                          $selected_proto = exec("uci get qos.@classify[".$filled_classify."].proto");
                          foreach ($PORT_FORWARD_SUPPORTED_PROTOCOLS as $intf) {
                            if (!$selected_proto && $intf == "tcp/udp") {
                              echo '<option selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
                            }
                            else if ($intf == $selected_proto) {
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
                      <td>Source IP/Subnet</td>
                      <td><input type="text" name="source_ip_subnet" class="form-control" value="<?php  echo exec("uci get qos.@classify[".$filled_classify."].srchost"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Source Port</td>
                      <td><input type="text" name="source_port" class="form-control" value="<?php  echo exec("uci get qos.@classify[".$filled_classify."].srcports"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Destination IP/Subnet</td>
                      <td><input type="text" name="dest_ip_subnet" class="form-control" value="<?php  echo exec("uci get qos.@classify[".$filled_classify."].dsthost"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Destination Port</td>
                      <td><input type="text" name="dest_port" class="form-control" value="<?php  echo exec("uci get qos.@classify[".$filled_classify."].dstports"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Percent Bandwith Allocation</td>
                      <td><input required  type="number" min="1" max="100" name="percent_limit" class="form-control" value="<?php  echo exec("uci get qos.".get_target_from_classification_id($filled_classify).".limitrate"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Comment</td>
                      <td><input required type="text" name="comment" class="form-control" value="<?php  echo exec("uci get qos.@classify[".$filled_classify."].comment"); ?>"></td>
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
                <h3 class="panel-title">Advanced QoS rules</h3>
              </div>
              <div class="panel-body">
                <div id="advanced_qos_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>WAN Interface</th>
                        <th>Protocol</th>
                        <th>Source IP/Subnet</th>
                        <th>Source Port</th>
                        <th>Destination IP/Subnet</th>
                        <th>Destination Port</th>
                        <th>Percent Limit</th>
                        <th>Comment</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $qos_html_out;
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
