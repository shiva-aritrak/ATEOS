<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | PPTP</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php
if(count($_POST) > 0) {
  $pptp_no = $_POST["pptp_no"];
  $status = $_POST["status"];
  $pptp_server = $_POST["pptp_server"];
  $username = $_POST["username"];
  $password = $_POST["password"];
  $lcp_echo_interval = $_POST["lcp_echo_interval"];
  $lcp_echo_timeout = $_POST["lcp_echo_timeout"];
  $defaultroute = $_POST["defaultroute"];
  $metric = $_POST["metric"];
  $mtu = $_POST["mtu"];

  set_pptp_vpn($pptp_no, $status, $pptp_server, $username, $password, $lcp_echo_interval, $lcp_echo_timeout, $defaultroute, $metric, $mtu);
}
?>

<?php

$pptp_id = 1;
$out = "";
$ret = 99;
$html_out = "";


exec("uci show network.pptp".$pptp_id , $out, $ret);
while ( $pptp_id <= $MAX_GRE_CLIENTS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$pptp_id.'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.pptp".$pptp_id.".username").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.pptp".$pptp_id.".password").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.pptp".$pptp_id.".server").'</td>';
  $html_out = $html_out.'  <td><a href="pptp.php?pptp_no='.$pptp_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="pptp.php?action=delete&pptp_no='.$pptp_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $pptp_id += 1;
  exec("uci show network.pptp".$pptp_id , $out, $ret);
}

if(count($_GET) > 0) {
  $filled_pptp_no = $_GET["pptp_no"];
  $action = $_GET["action"];

  if ( $action == "delete" ) {
    delete_pptp_vpn($filled_pptp_no);
    echo '<script>window.location.href = "pptp.php";</script>';
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
                <h3 class="panel-title">PPTP</h3>
              </div>
              <div class="panel-body">
                <div style="display:none;" id="alertwindow"class="alert alert-warning alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"></span></button>
                  <i class="fa fa-warning"></i> Alert!<p id="verrors">  </p>
                </div>

                <form enctype="multipart/form-data" id="pptp_vpn_form" action="pptp.php" method="post">
                  <table>
                    <tr>
                      <td>Tunnel</td>
                      <td>
                        <select required id="pptp_no_id" class="form-control" name="pptp_no">
                          <?php
                          for ($x = 1; $x <= $MAX_GRE_CLIENTS; $x++) {
                            if ($x == $filled_pptp_no) {
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
                          $client_status = exec("uci get network.pptp".$filled_pptp_no.".enabled");
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
                      <td>PPTP Server</td>
                      <td><input required type="text" id="id_pptp_server" name="pptp_server" class="form-control" value="<?php  echo exec("uci get network.pptp".$filled_pptp_no.".server"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>PPTP Username</td>
                      <td><input required type="text" id="id_username" name="username" class="form-control" value="<?php  echo exec("uci get network.pptp".$filled_pptp_no.".username"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>PPTP Password</td>
                      <td><input required type="text" id="id_password" name="password" class="form-control" value="<?php  echo exec("uci get network.pptp".$filled_pptp_no.".password"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>Metric</td>
                      <td><input type="text" id="id_metric" name="metric" class="form-control" value="<?php  echo exec("uci get network.pptp".$filled_pptp_no.".metric"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>MTU</td>
                      <td><input type="text" id="id_mtu" name="mtu" class="form-control" value="<?php  echo exec("uci get network.pptp".$filled_pptp_no.".mtu"); ?>" ></td>
                    </tr>

                    <tr>
                      <td>LCP Echo Interval</td>
                      <td><input required type="text" id="id_lcp_echo_interval" name="lcp_echo_interval" class="form-control" value="<?php  echo explode(" ", exec("uci get network.pptp".$filled_pptp_no.".keepalive"))[0]; ?>" ></td>
                    </tr>

                    <tr>
                      <td>LCP Echo Timeout</td>
                      <td><input required type="text" id="id_lcp_echo_timeout" name="lcp_echo_timeout" class="form-control" value="<?php  echo explode(" ", exec("uci get network.pptp".$filled_pptp_no.".keepalive"))[1]; ?>" ></td>
                    </tr>

                    <tr>
						<td>Default Route</td>
						<td>
							<label class="fancy-checkbox">
								<?php
								$default_route = exec("uci get network.pptp".$filled_pptp_no.".defaultroute");
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
