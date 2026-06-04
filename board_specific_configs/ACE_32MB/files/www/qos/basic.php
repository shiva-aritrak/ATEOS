<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>QoS | Basic</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/qos.php' ?>

<?php

$zone_id = 0;
$out = "";
$ret = 99;
$qos_html_out = "";

$interfaces = get_configured_interfaces();
$counter = 1;

if(count($_POST) > 0) {
  $status = $_POST["status"];
  $wan_interface = $_POST["wan_interface"];
  $upload_limit = $_POST["upload_limit"];
  $download_limit = $_POST["download_limit"];
  $saturation = $_POST["saturation"];
  set_basic_qos($wan_interface, $upload_limit, $download_limit, $saturation, $status);
}

foreach ($interfaces as $interface) {
  exec("uci show qos.".$interface , $out, $ret);
  if ($ret == 0) {
    $qos_html_out = $qos_html_out.'<tr>';
    $qos_html_out = $qos_html_out.'  <td>'.$counter.'</td>';
    $qos_html_out = $qos_html_out.'  <td>'.show_enabled_disabled(exec("uci get qos.".$interface.".enabled")).'</td>';
    $qos_html_out = $qos_html_out.'  <td>'.strtoupper($interface).'</td>';
    $qos_html_out = $qos_html_out.'  <td>'.exec("uci get qos.".$interface.".upload").'</td>';
    $qos_html_out = $qos_html_out.'  <td>'.exec("uci get qos.".$interface.".download").'</td>';
    $qos_html_out = $qos_html_out.'  <td>'.show_yes_no(exec("uci get qos.".$interface.".overhead")).'</td>';
    $qos_html_out = $qos_html_out.'  <td><a href="basic.php?wan='.$interface.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="basic.php?action=delete&wan='.$interface.'"><i class="fa fa-times"></i></a></td>';
    $qos_html_out = $qos_html_out.'</tr>';
    $counter++;
  }
}

if(count($_GET) > 0) {
  $interface_name = $_GET["wan"];
  $action = $_GET["action"];
  if ($action == "delete") {
    delete_qos_basic($interface_name);
    echo '<script>window.location.href = "basic.php";</script>';
    exit;
  }
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
                <h3 class="panel-title">Basic QoS</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="basic_qos_form" action="basic.php" method="post">
                  <table>
                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $qos_status = exec("uci get qos.".$interface_name.".enabled");
                          if ($qos_status == "1") {
                            echo '<input name="status" value="enabled" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="enabled" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($qos_status != "1") {
                            echo '<input name="status" value="disabled" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="disabled" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Disabled</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>WAN Interface</td>
                      <td>
                        <select required name="wan_interface" class="form-control input-sm">
                          <?php
                          foreach ($interfaces as $intf) {
                            if ($intf == $interface_name) {
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
                      <td>Upload Bandwidth (Kbps)</td>
                      <td><input required type="text" id="upload_limit_id" name="upload_limit" class="form-control" value="<?php  echo exec("uci get qos.".$interface_name.".upload"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Download Bandwidth (Kbps)</td>
                      <td><input required type="text" id="download_limit_id" name="download_limit" class="form-control" value="<?php  echo exec("uci get qos.".$interface_name.".download"); ?>"></td>
                    </tr>
                    <tr>
                      <td>Saturation Prevention</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $saturation = exec("uci get qos.".$interface_name.".overhead");
                          if ($saturation == "1") {
                            echo '<input name="saturation" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="saturation" type="checkbox">';
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
                <h3 class="panel-title">Basic QoS rules</h3>
              </div>
              <div class="panel-body">
                <div id="basic_qos_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Status</th>
                        <th>WAN Interface</th>
                        <th>Upload Limit (Kbps)</th>
                        <th>Download Limit (Kbps)</th>
                        <th>Saturation Prevention</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $qos_html_out;
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
