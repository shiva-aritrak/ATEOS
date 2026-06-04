<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | L2TP</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php
if(count($_POST) > 0) {
  $l2tp_no = $_POST["l2tp_no"];
  $status = $_POST["status"];
  $l2tp_server = $_POST["l2tp_server"];
  $username = $_POST["username"];
  $password = $_POST["password"];
  $lcp_echo_interval = $_POST["lcp_echo_interval"];
  $lcp_echo_timeout = $_POST["lcp_echo_timeout"];
  $defaultroute = $_POST["defaultroute"];
  $metric = $_POST["metric"];
  $mtu = $_POST["mtu"];

  set_l2tp_vpn($l2tp_no, $status, $l2tp_server, $username, $password, $lcp_echo_interval, $lcp_echo_timeout, $defaultroute, $metric, $mtu);
}
?>

<?php

$l2tp_id = 1;
$out = "";
$ret = 99;
$html_out = "";


exec("uci show network.l2tp".$l2tp_id , $out, $ret);
while ( $l2tp_id <= $MAX_GRE_CLIENTS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$l2tp_id.'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.l2tp".$l2tp_id.".username").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.l2tp".$l2tp_id.".password").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.l2tp".$l2tp_id.".server").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.l2tp".$l2tp_id.".metric").'</td>';
  $html_out = $html_out.'  <td><a href="l2tp.php?l2tp_no='.$l2tp_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="l2tp.php?action=delete&l2tp_no='.$l2tp_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $l2tp_id += 1;
  exec("uci show network.l2tp".$l2tp_id , $out, $ret);
}

if(count($_GET) > 0) {
  $filled_l2tp_no = $_GET["l2tp_no"];
  $action = $_GET["action"];

  if ( $action == "delete" ) {
    delete_l2tp_vpn($filled_l2tp_no);
    echo '<script>window.location.href = "l2tp.php";</script>';
    exit;
  }
}
?>
<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">L2TP</h3>
              </div>
              <div class="panel-body">
                <div style="display:none;" id="alertwindow"class="alert alert-warning alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"></span></button>
                  <i class="fa fa-warning"></i> Alert!<p id="verrors">  </p>
                </div>

                <form enctype="multipart/form-data" id="l2tp_vpn_form" action="l2tp.php" method="post">
                  <table>
                    <tr>
                      <td>Tunnel</td>
                      <td>
                        <select required id="l2tp_no_id" class="form-control" name="l2tp_no">
                          <?php
                          for ($x = 1; $x <= $MAX_GRE_CLIENTS; $x++) {
                            if ($x == $filled_l2tp_no) {
                              echo '<option selected=selected value="'.$x.'">'.$x.'</option>';
                            } else {
                              echo '<option value="'.$x.'">'.$x.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $client_status = exec("uci get network.l2tp".$filled_l2tp_no.".enabled");
                          if ($client_status == "1") {
                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($client_status == "0") {
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
                      <td>L2TP Server</td>
                      <td><input required type="text" id="id_l2tp_server" name="l2tp_server" class="form-control" value="<?php  echo exec("uci get network.l2tp".$filled_l2tp_no.".server"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>L2TP Username</td>
                      <td><input required type="text" id="id_username" name="username" class="form-control" value="<?php  echo exec("uci get network.l2tp".$filled_l2tp_no.".username"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>L2TP Password</td>
                      <td><input required type="text" id="id_password" name="password" class="form-control" value="<?php  echo exec("uci get network.l2tp".$filled_l2tp_no.".password"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>Metric</td>
                      <td><input type="text" id="id_metric" name="metric" class="form-control" value="<?php  echo exec("uci get network.l2tp".$filled_l2tp_no.".metric"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>MTU</td>
                      <td><input type="text" id="id_mtu" name="mtu" class="form-control" value="<?php  echo exec("uci get network.l2tp".$filled_l2tp_no.".mtu"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>LCP Echo Interval</td>
                      <td><input required type="text" id="id_lcp_echo_interval" name="lcp_echo_interval" class="form-control" value="<?php  echo explode(" ", exec("uci get network.l2tp".$filled_l2tp_no.".keepalive"))[0]; ?>" ></td>
                    </tr>

                    <tr>
                      <td>LCP Echo Timeout</td>
                      <td><input required type="text" id="id_lcp_echo_timeout" name="lcp_echo_timeout" class="form-control" value="<?php  echo explode(" ", exec("uci get network.l2tp".$filled_l2tp_no.".keepalive"))[1]; ?>" ></td>
                    </tr>

                    <tr>
                      <td>Default Route</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $default_route = exec("uci get network.l2tp".$filled_l2tp_no.".defaultroute");
                          if ($default_route == "0") {
                            echo '<input name="defaultroute" id="id_defaultroute" type="checkbox">';
                          } else {
                            echo '<input name="defaultroute" id="id_defaultroute" checked=checked type="checkbox">';
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
                      <button type="reset" class="btn btn-danger">Clear</button>
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
                <h3 class="panel-title">Current Clients (Max: <?php echo $MAX_GRE_CLIENTS; ?>)</h3>
              </div>
              <div class="panel-body">
                <div id="gre_vpn_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Server</th>
                        <th>Metric</th>
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
