<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>OSPFv2 | Interface</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php

//$selected_intf = "99";
if(count($_GET) > 0) {
  $selected_intf = $_GET["interface"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_ospf_interface($selected_intf, 0);
    echo '<script>window.location.href = "/routing/ospfv2.php";</script>';
    exit;
  }
}

if(count($_POST) > 0) {
  $interface_name = $_POST["interface"];
  $cost = $_POST["cost"];
  $hello = $_POST["hello"];
  $priority = $_POST['priority'];
  $wait = $_POST["wait"];
  $dead = $_POST["dead"];
  $retransmit = $_POST["retransmit"];
  $auth = $_POST["auth"];
  $passphrase = $_POST["passphrase"];
  $v46 = $_POST["v46"];
  add_ospf_interface($interface_name, $cost, $hello, $priority, $wait, $dead, $retransmit, $auth, $passphrase, $v46);
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
                <h3 class="panel-title">Add/Edit OSPFv2 Interface</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="ospf_interface_form" action="interfaces.php" method="post">
                <input type="hidden" id="v46_id" name="v46" value="0">
                  <table>
                    <tr>
                      <td>Interface</td>
                      <td>
                        <select required name="interface" class="form-control input-sm">
                          <?php
                          $interfaces_list = get_configured_interfaces();
                          $v4_list = [];
                          foreach($interfaces_list as $intrf) {
                            $is_v6 = exec("uci -q get network.".$intrf.".ipv6");
                            if ($is_v6 == "0") {
                              $v4_list[] = $intrf;
                            }
                          }
                          foreach ($v4_list as $intf) {
                            $raw_interface = exec("uci -q get network.".$intf.".ifname");
                            if ($raw_interface == $selected_intf) {
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
                      <td>Cost</td>
                      <td>
                        <input name="cost" class="form-control" value="<?php echo exec("uci -q get bird4.".$selected_intf.".cost"); ?>">
                      </td>
                    </tr>
                    <tr>
                      <td>Hello</td>
                      <td>
                        <input name="hello" class="form-control" value="<?php echo exec("uci -q get bird4.".$selected_intf.".hello"); ?>">
                      </td>
                    </tr>
                    <tr>
                      <td>Priority</td>
                      <td><input type="number" min=1 id="priority_id" name="priority" class="form-control" value="<?php  echo exec("uci get bird4.".$selected_intf.".priority"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Wait</td>
                      <td><input type="number" min=1 id="wait_id" name="wait" class="form-control" value="<?php  echo exec("uci -q get bird4.".$selected_intf.".wait"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Dead</td>
                      <td><input type="number" min=1 id="dead_id" name="dead" class="form-control" value="<?php  echo exec("uci -q get bird4.".$selected_intf.".dead"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Retransmit</td>
                      <td><input type="number" min=1 id="retransmit_id" name="retransmit" class="form-control" value="<?php  echo exec("uci -q get bird4.".$selected_intf.".retransmit"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Auth</td>
                      <td>
                        <select required name="authentication" class="form-control input-sm">
														<?php
														$auth_type = exec("uci -q get bird4.".$selected_intf.".authentication");
														foreach ($OSPF_AUTH_TYPES as $key => $value) {
															if($key == $auth_type) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
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
