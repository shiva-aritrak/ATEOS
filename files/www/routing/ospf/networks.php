<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>OSPFv2 | Networks</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php
$ospf_net_no = 0;
$counter = 0;

$ospf_net = get_ospf_networks(0);
if (!empty($ospf_net)) {
  $counter = max(array_map(function($v) { return (int)preg_replace('/\D/', '', $v); }, $ospf_net));
} else {
  $counter = 0;
}

if(count($_GET) > 0) {
  $action = $_GET["action"];
  $filled_ospf_network = $_GET["ospf_network"];
  $ospf_net_no = str_replace("net", "", $filled_ospf_network);
	$filled_ospf_network = $_GET["ospf_network"];
  if ($action == "delete") {
    delete_ospf_networks($filled_ospf_network, $ospf_network, 0);
    echo '<script>window.location.href = "/routing/ospfv2.php";</script>';
    exit;
  }
}

if(count($_POST) > 0) {
  $ospf_network = $_POST["ospf_network"];
  $name = $_POST["network_name"];
  $network_subnet = $_POST["network_subnet"];
  $v46 = $_POST["v46"];
  if (str_starts_with($ospf_network, "net")) {
		$ospf_net_no = str_replace("net", "", $ospf_network);
	} else {
		$ospf_net_no = $counter + 1;
	}
  add_ospf_networks($ospf_net_no, $name, $network_subnet, $v46);
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
                <h3 class="panel-title">Add/Edit OSPFv2 Network</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="ospf_networks_form" action="networks.php" method="post">
                  <input type="hidden" id="ospf_net_no_id" name="ospf_network" value="<?php if (count($_GET) > 0) { echo $filled_ospf_network; } else { echo "-1"; } ?>">
									<input type="hidden" id="v46_id" name="v46" value="0">
                  <table>
                    <tr>
                      <td>Name</td>
                      <td><input type="text" id="network_name_id" name="network_name" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_ospf_network.".name");?>"></td>
                    </tr>
                    <tr>
                      <td>Network Subnet</td>
                      <td><input type="text" id="net_sub_id" name="network_subnet" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_ospf_network.".range");?>"></td>
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
