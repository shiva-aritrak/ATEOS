<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | SSTP</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php
if(count($_POST) > 0) {
  $sstp_no = $_POST["sstp_no"];
  $status = $_POST["status"];
  $sstp_server = $_POST["sstp_server"];
  $username = $_POST["username"];
  $password = $_POST["password"];
  $lcp_echo_interval = $_POST["lcp_echo_interval"];
  $lcp_echo_timeout = $_POST["lcp_echo_timeout"];
  $defaultroute = $_POST["defaultroute"];
  $metric = $_POST["metric"];
  $mtu = $_POST["mtu"];

  set_sstp_vpn($sstp_no, $status, $sstp_server, $username, $password, $lcp_echo_interval, $lcp_echo_timeout, $defaultroute, $metric, $mtu);
}
?>

<?php

$sstp_id = 1;
$out = "";
$ret = 99;
$html_out = "";


exec("uci show network.sstp".$sstp_id , $out, $ret);
while ( $sstp_id <= $MAX_GRE_CLIENTS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$sstp_id.'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.sstp".$sstp_id.".username").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.sstp".$sstp_id.".password").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.sstp".$sstp_id.".server").'</td>';
  $html_out = $html_out.'  <td><a href="sstp.php?sstp_no='.$sstp_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="sstp.php?action=delete&sstp_no='.$sstp_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $sstp_id += 1;
  exec("uci show network.sstp".$sstp_id , $out, $ret);
}

if(count($_GET) > 0) {
  $filled_sstp_no = $_GET["sstp_no"];
  $action = $_GET["action"];

  if ( $action == "delete" ) {
    delete_sstp_vpn($filled_sstp_no);
    echo '<script>window.location.href = "sstp.php";</script>';
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
                <h3 class="panel-title">SSTP</h3>
              </div>
              <div class="panel-body">
                <div style="display:none;" id="alertwindow"class="alert alert-warning alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"></span></button>
                  <i class="fa fa-warning"></i> Alert!<p id="verrors">  </p>
                </div>

                <form enctype="multipart/form-data" id="sstp_vpn_form" action="sstp.php" method="post">
                  <table>
                    <tr>
                      <td>Tunnel</td>
                      <td>
                        <select required id="sstp_no_id" class="form-control" name="sstp_no">
                          <?php
                          for ($x = 1; $x <= $MAX_GRE_CLIENTS; $x++) {
                            if ($x == $filled_sstp_no) {
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
                          $client_status = exec("uci get network.sstp".$filled_sstp_no.".enabled");
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
                      <td>SSTP Server</td>
                      <td><input required type="text" id="id_sstp_server" name="sstp_server" class="form-control" value="<?php  echo exec("uci get network.sstp".$filled_sstp_no.".server"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>SSTP Username</td>
                      <td><input required type="text" id="id_username" name="username" class="form-control" value="<?php  echo exec("uci get network.sstp".$filled_sstp_no.".username"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>SSTP Password</td>
                      <td><input required type="text" id="id_password" name="password" class="form-control" value="<?php  echo exec("uci get network.sstp".$filled_sstp_no.".password"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>Metric</td>
                      <td><input type="text" id="id_metric" name="metric" class="form-control" value="<?php  echo exec("uci get network.sstp".$filled_sstp_no.".metric"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>MTU</td>
                      <td><input type="text" id="id_mtu" name="mtu" class="form-control" value="<?php  echo exec("uci get network.sstp".$filled_sstp_no.".mtu"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>LCP Echo Interval</td>
                      <td><input required type="text" id="id_lcp_echo_interval" name="lcp_echo_interval" class="form-control" value="<?php  echo explode(" ", exec("uci get network.sstp".$filled_sstp_no.".keepalive"))[0]; ?>" ></td>
                    </tr>

                    <tr>
                      <td>LCP Echo Timeout</td>
                      <td><input required type="text" id="id_lcp_echo_timeout" name="lcp_echo_timeout" class="form-control" value="<?php  echo explode(" ", exec("uci get network.sstp".$filled_sstp_no.".keepalive"))[1]; ?>" ></td>
                    </tr>

                    <tr>
						<td>Default Route</td>
						<td>
							<label class="fancy-checkbox">
								<?php
								$default_route = exec("uci get network.sstp".$filled_sstp_no.".defaultroute");
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
