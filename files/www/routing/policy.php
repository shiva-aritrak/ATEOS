<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Routing | Policy Route</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php

$route_id = 0;
$out = "";
$ret = 99;
$html_out = "";
$filled_route_no = 99;


if(count($_POST) > 0) {
  $route_no = $_POST["route_no"];
  $interface = $_POST["interface"];
  $local_addresses = $_POST["local_addresses"];
  $local_ports = $_POST["local_ports"];
  $remote_addresses = $_POST["remote_addresses"];
  $remote_ports = $_POST["remote_ports"];
  $comment = $_POST["comment"];
  configure_policy_route($route_no, $interface, $local_addresses, $local_ports, $remote_addresses, $remote_ports, $comment);
}


exec("uci show vpn-policy-routing.@policy[".$route_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.($route_id + 1).'</td>';
  $html_out = $html_out.'  <td>'.strtoupper(exec("uci get vpn-policy-routing.@policy[".$route_id."].interface")).'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get vpn-policy-routing.@policy[".$route_id."].local_addresses").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get vpn-policy-routing.@policy[".$route_id."].local_ports").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get vpn-policy-routing.@policy[".$route_id."].remote_addresses").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get vpn-policy-routing.@policy[".$route_id."].remote_ports").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get vpn-policy-routing.@policy[".$route_id."].comment").'</td>';
  $html_out = $html_out.'  <td><a href="policy.php?route_no='.$route_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="policy.php?action=delete&route_no='.$route_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  ++$route_id;
  exec("uci show vpn-policy-routing.@policy[".$route_id."]" , $out, $ret);
}

if(count($_GET) > 0) {
  $action = $_GET["action"];
  $filled_route_no = $_GET["route_no"];

  if ( $action == "delete" ) {
    delete_policy_route($filled_route_no);
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
                <h3 class="panel-title">Policy Routes</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="policy_route_form" action="policy.php" method="post">
                  <input type="hidden" id="route_no_id" name="route_no" value="<?php if (count($_GET) > 0) { echo $filled_route_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Outgoing Interface</td>
                      <td>
                        <select required name="interface" class="form-control input-sm">
                          <?php
                          $selected_src_zone = exec("uci get vpn-policy-routing.@policy[".$filled_zone_no."].interface");
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
                      <td>Local IP</td>
                      <td><input type="text" id="local_addresses_id" name="local_addresses" class="form-control" value="<?php  echo exec("uci get vpn-policy-routing.@policy[".$filled_route_no."].local_addresses");?>"></td>
                    </tr>
                    <tr>
                      <td>Local Ports</td>
                      <td><input type="text" id="local_ports_id" name="local_ports" class="form-control" value="<?php  echo exec("uci get vpn-policy-routing.@policy[".$filled_route_no."].local_ports");?>"></td>
                    </tr>
                    <tr>
                      <td>Remote IP</td>
                      <td><input type="text" id="remote_addresses_id" name="remote_addresses" class="form-control" value="<?php  echo exec("uci get vpn-policy-routing.@policy[".$filled_route_no."].remote_addresses");?>"></td>
                    </tr>
                    <tr>
                      <td>Remote Ports</td>
                      <td><input type="text" id="remote_ports_id" name="remote_ports" class="form-control" value="<?php  echo exec("uci get vpn-policy-routing.@policy[".$filled_route_no."].remote_ports");?>"></td>
                    </tr>
                    <tr>
                      <td>Comment</td>
                      <td><input type="text" id="comment_id" name="comment" class="form-control" value="<?php  echo exec("uci get vpn-policy-routing.@policy[".$filled_route_no."].comment");?>"></td>
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
                <h3 class="panel-title">Current Routes</h3>
              </div>
              <div class="panel-body">
                <div id="static_route_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Outgoing<br>Interface</th>
                        <th>Local IP</th>
                        <th>Local Port</th>
                        <th>Remote IP</th>
                        <th>Remote Port</th>
                        <th>Comment</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $html_out;
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
