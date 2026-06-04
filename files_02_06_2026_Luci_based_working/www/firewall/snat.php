<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Firewall | Add/Edit Source NAT</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>

<?php

if(count($_POST) > 0) {
  $snat_no = $_POST["snat_no"];
  $src_zone = $_POST["src_zone"];
  $src_ip = $_POST["src_ip"];
  $src_port = $_POST["src_port"];
  $dest_zone = $_POST["dest_zone"];
  $dest_ip = $_POST["dest_ip"];
  $dest_port = $_POST["dest_port"];
  $snat_ip = $_POST["snat_ip"];
  $protocol = str_replace("/", "", $_POST["protocol"]);

  set_snat_rule($snat_no, $src_zone, $src_ip, $src_port, $dest_zone, $dest_ip, $dest_port, $snat_ip, $protocol);
}



$filled_snat_no = "99";
$snat_id = 0;
$out = "";
$ret = 99;
$snat_html = "";

exec("uci show firewall.@redirect[".$snat_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $target = "";
  $target = exec("uci get firewall.@redirect[".$snat_id."].target");
  if ($target == "SNAT") {
    $snat_html = $snat_html.'<tr>';
    $snat_html = $snat_html.'  <td>'.($snat_id + 1).'</td>';
    $snat_html = $snat_html.'  <td>'.strtoupper(exec("uci get firewall.@redirect[".$snat_id."].proto")).'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].src").'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].src_ip").'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].src_port").'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].dest").'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].dest_ip").'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].dest_port").'</td>';
    $snat_html = $snat_html.'  <td>'.exec("uci get firewall.@redirect[".$snat_id."].src_dip").'</td>';
    $snat_html = $snat_html.'  <td><a href="snat.php?snat_no='.$snat_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="snat.php?action=delete&snat_no='.$snat_id.'"><i class="fa fa-times"></i></a></td>';
    $snat_html = $snat_html.'</tr>';
  }
  ++$snat_id;
  exec("uci show firewall.@redirect[".$snat_id."]" , $out, $ret);
}


if(count($_GET) > 0) {
  $filled_snat_no = $_GET["snat_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_snat_rule($filled_snat_no);
    echo '<script>window.location.href = "snat.php";</script>';
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
                <h3 class="panel-title">Add Source NAT</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" id="snat_form" action="snat.php" method="post">
                  <input type="hidden" id="snat_no_id" name="snat_no" value="<?php if (count($_GET) > 0) { echo $filled_snat_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Source Zone</td>
                      <td>
                        <select required name="src_zone" class="form-control input-sm">
                          <?php
                          $snat_src_zones = exec("uci get firewall.@redirect[".$filled_snat_no."].src");
                          foreach (get_configured_zones() as $intf) {
                            if (in_array($intf, show_zone_networks($snat_src_zones))) {
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
                      <td>Source IP</td>
                      <td><input type="text" id="src_ip_id" name="src_ip" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_snat_no."].src_ip");?>"></td>
                    </tr>
                    <tr>
                      <td>Source Port</td>
                      <td><input type="text" id="src_port_id" name="src_port" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_snat_no."].src_port");?>"></td>
                    </tr>
                    <tr>
                      <td>Destination Zone</td>
                      <td>
                        <select required name="dest_zone" class="form-control input-sm">
                          <?php
                          $snat_dest_zones = exec("uci get firewall.@redirect[".$filled_snat_no."].dest");
                          foreach (get_configured_zones() as $intf) {
                            if (in_array($intf, show_zone_networks($snat_dest_zones))) {
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
                      <td>Destination IP</td>
                      <td><input required type="text" id="dest_ip_id" name="dest_ip" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_snat_no."].dest_ip");?>"></td>
                    </tr>
                    <tr>
                      <td>Destination Port</td>
                      <td><input type="text" id="dest_port_id" name="dest_port" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_snat_no."].dest_port");?>"></td>
                    </tr>
                    <tr>
                      <td>SNAT IP</td>
                      <td><input required type="text" id="snat_ip_id" name="snat_ip" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_snat_no."].src_dip");?>"></td>
                    </tr>
                    <tr>
                      <td>Protocol</td>
                      <td>
                        <select required name="protocol" class="form-control input-sm">
                          <?php
                          $selected_dst_zone = exec("uci get firewall.@redirect[".$filled_snat_no."].proto");
                          foreach ($PORT_FORWARD_SUPPORTED_PROTOCOLS as $intf) {
                            if ($intf == $selected_dst_zone) {
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
        <div class="col-md-8">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Current SNAT Rules</h3>
              </div>
              <div class="panel-body">
                <div id="snat_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Protocol</th>
                        <th>Source Zone</th>
                        <th>Source IP</th>
                        <th>Source Port</th>
                        <th>Destination Zone</th>
                        <th>Destination IP</th>
                        <th>Destination Port</th>
                        <th>SNAT IP</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $snat_html;
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
