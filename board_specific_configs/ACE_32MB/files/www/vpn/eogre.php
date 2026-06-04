<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | EoGRE/L2 GRE</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php
if(count($_POST) > 0) {
  $gre_no = $_POST["gre_no"];
  $instance_no = $_POST["instance_no"];
  $status = $_POST["status"];
  $name = $_POST["name"];
  $localip = $_POST["localip"];
  $peerip = $_POST["peerip"];
  $ttl = $_POST["ttl"];
  $network = $_POST["network"];
  exec("logger -t webgui gre_no: ".$gre_no." status: ".$status." name: ".$name." localip: ".$localip." peerip: ".$peerip." ttl: ".$ttl." network: ".$network);
  set_tapgre($gre_no, $instance_no, $status, $name, $localip, $peerip, $ttl, $network);
}

$tun_no = 9999;

if(count($_GET) > 0) {
  $tun_no = $_GET["tun_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_tapgre_tunnel($tun_no);
    echo '<script>window.location.href = "/vpn/eogre.php";</script>';
    exit;
  }
}

$gre_id = 0;
$out = "";
$ret = 0;
$html_out = "";

exec("uci show network.tapgre".$gre_id , $out, $ret);
while ( $ret == 0 ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$gre_id.'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tapgre".$gre_id.".name").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tapgre".$gre_id.".ipaddr").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tapgre".$gre_id.".peeraddr").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tapgre".$gre_id.".ttl").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tapgre".$gre_id.".network").'</td>';

  $html_out = $html_out.'  <td><a href="eogre.php?tun_no='.$gre_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="eogre.php?action=delete&tun_no='.$gre_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $gre_id += 1;
  exec("uci show network.tungre".$gre_id , $out, $ret);
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
                <h3 class="panel-title">GRE</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" action="eogre.php" method="post">
                <input type="hidden" id="instance_id" name="instance_no" value="<?php if (count($_GET) > 0) { echo $tun_no; } else { echo $gre_id; } ?>">

                  <table>
                    <tr>
                      <td>Tunnel No.</td>
                      <td><input name="gre_no" type="text" readonly class="form-control" value="<?php if (count($_GET) > 0) { echo $tun_no; } else { echo "-1"; } ?>"></td>
                    </tr>

                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $client_status = exec("uci get network.tapgre".$tun_no.".enabled");
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
                      <td>Tunnel Name</td>
                      <td><input required type="text" id="id_name" name="name" class="form-control" value="<?php  $get=exec("uci get network.tapgre".$tun_no.".name"); echo $get?>" ></td>
                    </tr>

                    <tr>
                      <td>Local IP Address</td>
                      <td><input required type="text" id="id_local_ip_addr" name="localip" class="form-control" value="<?php  $get=exec("uci get network.tapgre".$tun_no.".ipaddr"); echo $get?>" ></td>
                    </tr>

                    <tr>
                      <td>Remote IP Address</td>
                      <td><input required type="text" id="id_remote_ip_addr" name="peerip" class="form-control" value="<?php  $get=exec("uci get network.tapgre".$tun_no.".peeraddr"); echo $get?>" >
                      </td>
                    </tr>

                    <tr>
                      <td>TTL</td>
                      <td><input type="number" id="id_ttl" min="2" max="255" name="ttl" class="form-control" value="<?php  $get=exec("uci get network.tapgre".$tun_no.".ttl"); echo $get?>" required>
                      </td>
                    </tr>

                    <tr>
													<td>L3 Network</td>
													<td>
														<select required name="network" id="id_network" class="form-control input-sm">
															<?php
															foreach (get_configured_link_interfaces() as $intf) {
                                $selected_network = exec("uci get network.tapgre".$tun_no.".network");
																if(strtolower($selected_network) == strtolower($intf)) {
																	echo '<option selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
																} else {
																	echo '<option value="'.$intf.'">'.strtoupper($intf).'</option>';
																}
															}
															?>
														</select>
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
                    </div>
                </form>
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
                        <th>Name</th>
                        <th>Local IP Address</th>
                        <th>Remote IP Address</th>
                        <th>TTL</th>
                        <th>Network</th>
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
