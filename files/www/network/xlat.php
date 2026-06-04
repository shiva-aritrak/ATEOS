<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Network | XLAT (CLAT)</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/networks.php' ?>

<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $clatd_enable = $_POST["clatd_enable"];
  $interface = $_POST["interface"];
  $ip6prefix = $_POST["ip6prefix"];
  $metric = $_POST["metric"];
  $mtu = $_POST["mtu"];
  $fw_zone = $_POST["fw_zone"];
  $interface_ip = $_POST["interface_ip"];
  
  set_xlat_config($clatd_enable, $interface, $ip6prefix, $metric, $interface_ip, $fw_zone, $mtu);
  echo '<script>window.location.href = "/network/xlat.php";</script>';
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
              <?php
                if ($incorrect) {
                  echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                  echo '  <i class="fa fa-warning"></i> '.$message.'</p>';
                  echo '</div>';
                }
              ?>
              <div class="panel-heading">
                <h3 class="panel-title">IPv6 XLAT (CLAT)</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="xlat_form" action="xlat.php" method="post">
                  <table>
                    <tr>
                      <td>Enable 464XLAT (CLAT)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $clatd_enable = exec("uci get network.clatd.enabled");
                          if ($clatd_enable == "1") {
                            echo '<input name="clatd_enable" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="clatd_enable" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Tunnel Interface</td>
                      <td>
                        <select name="interface" class="form-control input-sm">
                          <?php
                          $interface = exec("uci get network.clatd.tunintf");
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
												<td>IPv6 Prefix</td>
												<td><input type="text" pattern="^(((([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))(\/(?:\d|[1-9]\d|1[0-1]\d|12[0-8])){0,1})|([Aa][Uu][Tt][Oo]))$" title="Enter a valid IPv6 IP/Prefix or Auto" class="form-control" id="id_global_ip6prefix" name="ip6prefix" value="<?php echo exec("uci get network.clatd.ip6prefix"); ?>"></td>
										</tr>
                    <tr>
											<td>Interface IP/Subnet</td>
											<td><input type="text" class="form-control" name="interface_ip" id="id_interface_ip" value="<?php echo exec("uci get network.clatd.interface_ip"); ; ?>" ></td>
                    </tr>
                    <tr>
											<td>Firewall Zone</td>
											<td>
												<select required name="fw_zone" class="form-control input-sm">
												<?php
													$fw_zones = exec("uci get network.clatd.fw_zone");
													foreach (get_configured_zones() as $intf) {
                            if (in_array($intf, show_zone_networks($fw_zones))) {
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
                      <td>MTU</td>
                      <td><input type="number" name="mtu" id="mtu_id" class="form-control" value="<?php  $get=exec("uci get network.clatd.mtu"); echo $get?>" ></td>
                    </tr>
                    <tr>
											<td>Metric</td>
											<td><input type="text" pattern="^[0-9]*$" title="Metric should be a number" class="form-control" name="metric" id="metric_id" value="<?php echo exec("uci get network.clatd.metric"); ; ?>" ></td>
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
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
