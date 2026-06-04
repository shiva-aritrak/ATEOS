<?php include '/www/lib/sessioncheck.php' ?>

<head>
<title>Administration | LLDP</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/system.php' ?>

<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $enable_cdp = $_POST["enable_cdp"];
  $enable_fdp = $_POST["enable_fdp"];
  $enable_sonmp = $_POST["enable_sonmp"];
  $enable_edp = $_POST["enable_edp"];
  $lldp_class = $_POST["lldp_class"];
  $lldp_description = $_POST["lldp_description"];
  $lldp_hostname = $_POST["lldp_hostname"];
  $interfaces = $_POST["interfaces"];

  set_lldp_config($enable_cdp, $enable_fdp, $enable_sonmp, $enable_edp, $lldp_class, $lldp_description, $lldp_hostname, $interfaces);
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
                <h3 class="panel-title">LLDP</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="lldp_form" action="lldp.php" method="post">
                  <table>

                    <tr>
                      <td colspan="2"><b>Global LLDP Settings</b></td>
                      <td></td>
                    </tr>

                    <tr>
                      <td>Enable CDP (Cisco Discovery Protocol)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $enable_cdp = exec("uci get lldpd.config.enable_cdp");
                          if ($enable_cdp == "1") {
                            echo '<input name="enable_cdp" value="1" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="enable_cdp" value="1" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Enable FDP (Foundry Discovery Protocol)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $enable_fdp = exec("uci get lldpd.config.enable_fdp");
                          if ($enable_fdp == "1") {
                            echo '<input name="enable_fdp" value="1" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="enable_fdp" value="1" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Enable SONMP (Nortel)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $enable_sonmp = exec("uci get lldpd.config.enable_sonmp");
                          if ($enable_sonmp == "1") {
                            echo '<input name="enable_sonmp" value="1" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="enable_sonmp" value="1" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Enable EDP (Extreme Discovery Protocol)</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $enable_edp = exec("uci get lldpd.config.enable_edp");
                          if ($enable_edp == "1") {
                            echo '<input name="enable_edp" value="1" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="enable_edp" value="1" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>LLDP Class</td>
                      <td>
                        <select required name="lldp_class" class="form-control input-sm">
                          <?php
                          $lldp_class = exec("uci get lldpd.config.lldp_class");
                          foreach ($LLDP_CLASSES as $key => $value) {
                            if($key == $lldp_class) {
                              echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
                            } else {
                              echo '<option value="'.$key.'">'.$value.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Description</td>
                      <td><input type="text" id="lldp_description_id" name="lldp_description" class="form-control" value="<?php  echo exec("uci get lldpd.config.lldp_description"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Hostname</td>
                      <td><input type="text" id="lldp_hostname_id" name="lldp_hostname" class="form-control" value="<?php  echo exec("uci get lldpd.config.lldp_hostname"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Interfaces</td>
                      <td>
                      	<select required multiple name="interfaces[]" id="id_interfaces" class="form-control input-sm">
                      		<?php
                      		foreach (get_configured_link_interfaces() as $intf) {
                            		$selected_interfaces = exec("uci get lldpd.config.interface");
                            		if (in_array($intf, show_zone_networks($selected_interfaces))) {
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
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
