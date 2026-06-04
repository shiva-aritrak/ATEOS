<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Firewall | Add/Edit Port Forward</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>

<?php

$filled_port_fw_no = "99";
$port_fw_id = 0;
$out = "";
$ret = 99;
$port_fw_html = "";

if(count($_GET) > 0) {
  $filled_port_fw_no = $_GET["port_fw_no"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_port_forwardings($filled_port_fw_no);
  }
}

if(count($_POST) > 0) {
  $port_fw_no = $_POST["port_fw_no"];
  $src_zone = $_POST["src_zone"];
  $int_zone = $_POST["int_zone"];
  $src = $_POST["src"];
  $src_ip = $_POST["src_ip"];
  $src_port =  str_replace(",", " ", str_replace("-", ":", $_POST["src_port"]));
  $dest = $_POST["dest"];
  $dest_port =  str_replace(",", " ", str_replace("-", ":", $_POST["dest_port"]));
  $protocol = str_replace("/", "", $_POST["protocol"]);

  set_port_forwarding($port_fw_no, $src_zone, $src_ip, $src, $src_port, $dest, $dest_port, $protocol, $int_zone);
}

exec("uci show firewall.@redirect[".$port_fw_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $target = "";
  $target = exec("uci get firewall.@redirect[".$port_fw_id."].target");
  if ($target == "DNAT") {
    $port_fw_html = $port_fw_html.'<tr>';
    $port_fw_html = $port_fw_html.'  <td>'.($port_fw_id + 1).'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.strtoupper(exec("uci get firewall.@redirect[".$port_fw_id."].proto")).'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].src").'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].src_ip").'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].src_dip").'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].src_dport").'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].dest").'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].dest_ip").'</td>';
    $port_fw_html = $port_fw_html.'  <td>'.exec("uci get firewall.@redirect[".$port_fw_id."].dest_port").'</td>';
    $port_fw_html = $port_fw_html.'  <td><a href="port_forwarding.php?port_fw_no='.$port_fw_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="port_forwarding.php?action=delete&port_fw_no='.$port_fw_id.'"><i class="fa fa-times"></i></a></td>';
    $port_fw_html = $port_fw_html.'</tr>';
  }

  ++$port_fw_id;
  exec("uci show firewall.@redirect[".$port_fw_id."]" , $out, $ret);
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
                <h3 class="panel-title">Add Port Forward</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" id="port_forwarding_form" action="port_forwarding.php" method="post">
                  <input type="hidden" id="port_fw_no_id" name="port_fw_no" value="<?php if (count($_GET) > 0) { echo $filled_port_fw_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Source Zone</td>
                      <td>
                        <select required name="src_zone" class="form-control input-sm">
                          <?php
                          $port_fw_src_zones = exec("uci get firewall.@redirect[".$filled_port_fw_no."].src");
                          foreach (get_configured_zones() as $intf) {
                            if (in_array($intf, show_zone_networks($port_fw_src_zones))) {
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
                      <td><input type="text" id="src_ip_id" name="src_ip" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_port_fw_no."].src_ip");?>"></td>
                    </tr>

                    <tr>
                      <td>Public IP</td>
                      <td><input type="text" id="src_id" name="src" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_port_fw_no."].src_dip");?>"></td>
                    </tr>
                    <tr>
                      <td>Public Port</td>
                      <td><input type="text" id="src_port_id" name="src_port" class="form-control" value="<?php echo str_replace(" ", ",", str_replace(":", "-", exec("uci get firewall.@redirect[".$filled_port_fw_no."].src_dport")));?>"></td>
                    </tr>
		    <tr>
                      <td>Internal Zone</td>
                      <td>
                        <select required name="int_zone" class="form-control input-sm">
                          <?php
                          $port_fw_int_zones = exec("uci get firewall.@redirect[".$filled_port_fw_no."].dest");
                          foreach (get_configured_zones() as $intf) {
                            if (in_array($intf, show_zone_networks($port_fw_int_zones))) {
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
                      <td>Internal IP</td>
                      <td><input type="text" id="dst_id" name="dest" class="form-control" value="<?php  echo exec("uci get firewall.@redirect[".$filled_port_fw_no."].dest_ip");?>"></td>
                    </tr>
                    <tr>
                      <td>Internal Port</td>

                      <td><input type="text" id="dst_port_id" name="dest_port" class="form-control" value="<?php echo str_replace(" ", ",", str_replace(":", "-", exec("uci get firewall.@redirect[".$filled_port_fw_no."].dest_port")));?>"></td>
                    </tr>
                    <tr>
                      <td>Protocol</td>
                      <td>
                        <select required name="protocol" class="form-control input-sm">
                          <?php
                          $selected_dst_zone = exec("uci get firewall.@redirect[".$filled_port_fw_no."].proto");
                          foreach ($PORT_FORWARD_SUPPORTED_PROTOCOLS as $intf) {
                            if (str_replace("/", "", $intf) == $selected_dst_zone) {
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
                <h3 class="panel-title">Current Ports Forwarded</h3>
              </div>
              <div class="panel-body">
                <div id="port_forwarding_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Protocol</th>
                        <th>Source Zone</th>
                        <th>Source IP</th>
                        <th>Public IP</th>
                        <th>Public Port</th>
			<th>Internal Zone</th>
                        <th>Internal IP</th>
                        <th>Internal Port</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $port_fw_html;
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
