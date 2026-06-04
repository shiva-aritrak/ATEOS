<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Routing | OSPFv3</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php
$net_counter = 0;
$intf_counter = 0;
$area_counter = 0;

$ospf_network = get_ospf_networks(1);
if (!empty($ospf_network)) {
  $net_counter = max(array_map(function($v) { return (int)preg_replace('/\D/', '', $v); }, $ospf_network));
} else {
  $net_counter = 0;
}

$ospf_interfaces = get_ospf_interfaces(1);
if (!empty($ospf_interface)) {
  $intf_counter = max(array_map(function($v) { return (int)preg_replace('/\D/', '', $v); }, $ospf_interface));
} else {
  $intf_counter = 0;
}

$ospf_areas = get_ospf_areas(1);
if (!empty($ospf_interface)) {
  $area_counter = max(array_map(function($v) { return (int)preg_replace('/\D/', '', $v); }, $ospf_areas));
} else {
  $area_counter = 0;
}

if (count($_POST) > 0) {
  $status = $_POST["status"];
  $tick = $_POST["tick"];
  $areas = $_POST["areas"];
  $v46 = $_POST["v46"];
  set_ospf_instance($status, $tick, $areas, $v46);
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
                <h3 class="panel-title">OSPFv3 Global Configuration</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="ospfv3_form" action="ospfv3.php" method="post">
                <input type="hidden" id="v46_id" name="v46" value="1">
                  <table>
                    <tr>
                    <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                            <?php
                            $status = exec("uci get bird6.ospf1.disabled");
                            if ($status != "1") {
                                echo '<input name="status" value="0" checked="checked" type="radio" required>';
                            } else {
                                echo '<input name="status" value="0" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                            <?php
                            if ($status == "1") {
                                echo '<input name="status" value="1" checked="checked" type="radio" required>';
                            } else {
                                echo '<input name="status" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Disabled</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Interface Scan Interval</td>
                      <td><input required type="text" id="tick_id" name="tick" class="form-control" placeholder= "(In Secs)" value = "<?php  echo exec("uci -q get bird6.ospf1.tick"); ?>"></td>
                    </tr>
                    <tr id="id_areas">
                      <td>Area</td>
                      <td>
                        <select multiple name="areas[]" id="id_areas" class="form-control input-sm">
                            <?php
                            $selected_area = explode(" ", exec("uci get bird6.ospf1.ospf_area"));
                            foreach ($ospf_areas as $ospf_area) {
                                if (in_array($ospf_area, $selected_area)) {
                                    echo '<option selected="selected" value="'.$ospf_area.'">'.strtoupper($ospf_area).'</option>';
                                } else {
                                    echo '<option value="'.$ospf_area.'">'.strtoupper($ospf_area).'</option>';
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
        <div class="col-md-8">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">OSPFv3 Interfaces<div class="pull-right"><a href="/routing/ospf/interfacesv3.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="interfaces_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Interface</th>
                      <th>Cost</th>
                      <th>Hello</th>
                      <th>Priority</th>
                      <th>Wait</th>
                      <th>Dead</th>
                      <th>Retransmit</th>
                      <th>Auth</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($ospf_interfaces as $ospf_interface) {
                      echo '<tr>';
                      echo '<td>'.strtoupper($ospf_interface).'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".cost").'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".hello").'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".priority").'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".wait").'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".dead").'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".retransmit").'</td>';
                      echo '<td>'.exec("uci get bird6.".$ospf_interface.".authentication").'</td>';
                      echo '<td>';
                      echo '<td><a href="/routing/ospf/interfacesv3.php?interface='.$ospf_interface.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="/routing/ospf/interfacesv3.php?action=delete&interface='.$ospf_interface.'"><i class="fa fa-times"></i></a></td>';
                      echo '</tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">OSPFv3 Networks<div class="pull-right"><a href="/routing/ospf/networksv3.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="networks_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Networks</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $ospf_networks = get_ospf_networks(1);
                    foreach ($ospf_networks as $ospf_network) {
                      echo '<tr>';
                      echo '  <td>'.exec("uci -q get bird6.$ospf_network.name").'</td>';
                      echo '  <td>'.exec("uci -q get bird6.$ospf_network.range").'</td>';
                      echo '  <td><a href="/routing/ospf/networksv3.php?ospf_network='.$ospf_network.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="/routing/ospf/networksv3.php?action=delete&ospf_network='.$ospf_network.'"><i class="fa fa-times"></i></a></td>';
                      echo '</tr>';
                      ++$net_counter;
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">OSPFv3 Areas<div class="pull-right"><a href="/routing/ospf/areasv3.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
              </div>
              <div id="areas_id" class="content">
                <table class="table">
                  <thead>
                    <tr>
                      <th>Area</th>
                      <th>Stub</th>
                      <th>Default Cost</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($ospf_areas as $ospf_area) {
                      echo '<tr>';
                      echo '  <td>'.exec("uci -q get bird6.$ospf_area.name").'</td>';
                      echo '  <td>'.exec("uci -q get bird6.$ospf_area.stub").'</td>';
                      echo '  <td>'.exec("uci -q get bird6.$ospf_area.default_cost").'</td>';
                      echo '  <td><a href="/routing/ospf/areasv3.php?ospf_area='.$ospf_area.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="/routing/ospf/areasv3.php?action=delete&ospf_area='.$ospf_area.'"><i class="fa fa-times"></i></a></td>';
                      echo '</tr>';
                      ++$area_counter;
                    }
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
<?php endblock() ?>
