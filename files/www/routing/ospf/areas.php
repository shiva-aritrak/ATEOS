<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>OSPFv2 | Area</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php
$filled_area = "99";
if(count($_GET) > 0) {
  $filled_area = $_GET["ospf_area"];
  $v46 = $_GET["v46"];
  $action = $_GET["action"];
  if ($action == "delete") {
    delete_ospf_area($filled_area, $ospf_area, 0);
    echo '<script>window.location.href = "/routing/ospfv2.php";</script>';
    exit;
  }
}

if(count($_POST) > 0) {
  $area = $_POST["ospf_area"];
  $stub = $_POST["stub"];
  $def_cost = $_POST["def_cost"];
  $networks = $_POST["networks"];
  $interfaces = $_POST["interfaces"];
  $v46 = $_POST["v46"];
  add_ospf_area($area, $stub, $def_cost, $networks, $interfaces, $v46);
  echo '<script>window.location.href = "/routing/ospfv2.php";</script>';
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
                <h3 class="panel-title">Add/Edit OSPFv2 Area</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="ospf_area_form" action="areas.php" method="post">
                <input type="hidden" id="v46_id" name="v46" value="0">
                  <table>
                    <tr>
                      <td>Area</td>
                      <td><input type="text" id="area_id" name="ospf_area" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_area.".name");?>"></td>
                    </tr>
                    <tr>
                      <td>Stub</td>
                      <td><input type="text" id="stub_id" name="stub" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_area.".stub");?>"></td>
                    </tr>
                    <tr>
                      <td>Default Cost</td>
                      <td><input type="text" id="def_cost_id" name="def_cost" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_area.".default_cost");?>"></td>
                    </tr>
                    <tr id="id_networks">
                      <td>Networks</td>
                      <td>
                        <select multiple required name="networks[]" id="id_networks" class="form-control input-sm">
                            <?php
                            $network = explode(" ", exec("uci get bird4.".$filled_area.".ospf_networks"));
                            foreach (get_ospf_networks(0) as $ospf_network) {
                                if (in_array($ospf_network, $network)) {
                                    echo '<option selected="selected" value="'.$ospf_network.'">'.strtoupper($ospf_network).'</option>';
                                } else {
                                    echo '<option value="'.$ospf_network.'">'.strtoupper($ospf_network).'</option>';
                                }
                            }
                            ?>
                        </select>
                      </td>
                    </tr>
                    <tr id="id_interfaces">
                      <td>Interfaces</td>
                      <td>
                        <select multiple required name="interfaces[]" id="id_interfaces" class="form-control input-sm">
                            <?php
                            $interfaces = explode(" ", exec("uci get bird4.".$filled_area.".ospf_interface"));
                            foreach (get_ospf_interfaces(0) as $ospf_interface) {
                                if (in_array($ospf_interface, $interfaces)) {
                                    echo '<option selected="selected" value="'.$ospf_interface.'">'.strtoupper($ospf_interface).'</option>';
                                } else {
                                    echo '<option value="'.$ospf_interface.'">'.strtoupper($ospf_interface).'</option>';
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
