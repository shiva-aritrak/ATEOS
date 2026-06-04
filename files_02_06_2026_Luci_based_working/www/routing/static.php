<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Routing | Static</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php

$filled_route_no = 99;
$route_id = 0;
$out = "";
$ret = 99;
$html_out = "";

if(count($_POST) > 0) {
  $route_no = $_POST["route_no"];
  $interface_name = $_POST["interface_name"];
  $dest_ip = $_POST["dest_ip"];
  $netmask = $_POST["netmask"];
  $gateway_ip = $_POST["gateway_ip"];
  $metric = $_POST["metric"];
  $mtu = $_POST["mtu"];
  $force_route = $_POST["force_route"];
  $float_route = $_POST["float_route"];
  $routing_table = $_POST["routing_table"];
  configure_static_route($route_no, $interface_name, $dest_ip, $netmask, $gateway_ip, $metric, $mtu, $force_route, $float_route, $routing_table);
}

if(count($_GET) > 0) {
  $action = $_GET["action"];
  $filled_route_no = $_GET["route_no"];

  if ( $action == "delete" ) {
    delete_static_route($filled_route_no);
    echo '<script>window.location.href = "static.php";</script>';
    exit;
  }
}

exec("uci show network.@route[".$route_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.($route_id+1).'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.@route[".$route_id."].interface").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.@route[".$route_id."].target").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.@route[".$route_id."].netmask").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.@route[".$route_id."].gateway").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.@route[".$route_id."].metric").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.@route[".$route_id."].mtu").'</td>';
  $html_out = $html_out.'  <td>'.show_force_route(exec("uci get network.@route[".$route_id."].onlink")).'</td>';
  $html_out = $html_out.'  <td>'.show_force_route(exec("uci get network.@route[".$route_id."].float")).'</td>';
  $html_out = $html_out.'  <td><a href="static.php?route_no='.$route_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="static.php?action=delete&route_no='.$route_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  ++$route_id;
  exec("uci show network.@route[".$route_id."]" , $out, $ret);
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
                <h3 class="panel-title">Static Routes</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" id="static_route_form" action="static.php" method="post">
                  <input type="hidden" id="route_no_id" name="route_no" value="<?php if (count($_GET) > 0) { echo $filled_route_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Interface</td>
                      <td>
                        <select required name="interface_name" class="form-control input-sm">
                          <?php
                          $interface = exec("uci get network.@route[".$filled_route_no."].interface");
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
                      <td>Destination IP</td>
                      <td><input required type="text" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" id="dest_ip_id" name="dest_ip" class="form-control" value="<?php  echo exec("uci get network.@route[".$filled_route_no."].target");?>"></td>
                    </tr>
                    <tr>
                      <td>Netmask</td>
                      <td><input required pattern="^(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$" title="Must contain a valid Subnet Mask" type="text" id="netmask_id" name="netmask" class="form-control" value="<?php  echo exec("uci get network.@route[".$filled_route_no."].netmask");?>"></td>
                    </tr>
                    <tr>
                      <td>Gateway IP</td>
                      <td><input required type="text" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" id="gateway_ip_id" name="gateway_ip" class="form-control" value="<?php  echo exec("uci get network.@route[".$filled_route_no."].gateway");?>"></td>
                    </tr>
                    <tr>
                      <td>Metric</td>
                      <td><input required type="number" id="metric_id" name="metric" class="form-control" value="<?php  echo exec("uci get network.@route[".$filled_route_no."].metric");?>"></td>
                    </tr>
                    <tr>
                      <td>MTU</td>
                      <td><input type="number" id="mtu_id" max="1500" name="mtu" class="form-control" value="<?php  echo exec("uci get network.@route[".$filled_route_no."].mtu");?>"></td>
                    </tr>
                    <tr>
                      <td>Routing Table</td>
                      <td>
                        <select name="routing_table" class="form-control input-sm">
                          <?php
                          $table = exec("uci get network.@route[".$filled_route_no."].table");
                          foreach (get_all_routing_tables() as $routing_table) {
                            $table_no_name = preg_split('/\s+/', $routing_table);
                            if ($table == trim($table_no_name[1]) || ( ($table == '') && (trim($table_no_name[1]) == 'main' ) ) ) {
                              echo '<option selected="selected" value="'.trim($table_no_name[1]).'">'.strtoupper(trim($table_no_name[1])).'</option>';
                            } else {
                              echo '<option value="'.trim($table_no_name[1]).'">'.strtoupper(trim($table_no_name[1])).'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Force Route</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $route_force = exec("uci get network.@route[".$filled_route_no."].onlink");
                          if ($route_force == "1") {
                            echo '<input name="force_route" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="force_route" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Float Route</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $float_route = exec("uci get network.@route[".$filled_route_no."].float");
                          if ($float_route == "1") {
                            echo '<input name="float_route" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="float_route" type="checkbox">';
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
                      <button type="reset" class="btn btn-danger">Reset</button>
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
                        <th>Interface</th>
                        <th>Destination IP</th>
                        <th>Netmask</th>
                        <th>Gateway</th>
                        <th>Metric</th>
                        <th>MTU</th>
                        <th>Force</th>
                        <th>Float</th>
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
