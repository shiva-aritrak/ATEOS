<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Firewall | Add/Edit Forwarding</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>

<?php

$filled_fw_no = "99";

if(count($_GET) > 0) {
  $filled_fw_no = $_GET["forward_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_firewall_forwardings($filled_fw_no);
    echo '<script>window.location.href = "/firewall/rules.php";</script>';
    exit;
  }
}

if(count($_POST) > 0) {
  $fw_no = $_POST["fw_no"];
  $src_zones = $_POST["src_zones"];
  $dest_zones = $_POST["dest_zones"];

  create_firewall_forwardings($fw_no, $src_zones, $dest_zones);
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
                <h3 class="panel-title">Add Forwarding</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="static_route_form" action="forwarding.php" method="post">
                  <input type="hidden" id="fw_no_id" name="fw_no" value="<?php if (count($_GET) > 0) { echo $filled_fw_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Source Zone</td>
                      <td>
                        <select required name="src_zones" class="form-control input-sm">
                          <?php
                          $selected_src_zone = exec("uci get firewall.@forwarding[".$filled_fw_no."].src");
                          foreach (get_configured_zones() as $intf) {
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
                      <td>Destination Zone</td>
                      <td>
                        <select required name="dest_zones" class="form-control input-sm">
                          <?php
                          $selected_src_zone = exec("uci get firewall.@forwarding[".$filled_fw_no."].dest");
                          foreach (get_configured_zones() as $intf) {
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
