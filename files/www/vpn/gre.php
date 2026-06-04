<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>VPN | GRE</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php
if(count($_POST) > 0) {
  $gre_no = $_POST["gre_no"];
  $status = $_POST["status"];
  $local_ip_addr = $_POST["Local_IP_Address"];
  $remote_ip_addr = $_POST["Remote_IP_Address"];
  $ttl = $_POST["TTL"];
  $static_ip_addr = $_POST["Static_IP_Address"];
  $static_netmask = $_POST["Static_Subnet_Mask"];
  $tun_target= $_POST["Tunnel_Target"];
  $tun_netmask= $_POST["Tunnel_Subnet_Mask"];
  $tun_gateway= $_POST["Tunnel_Gateway"];

  set_gre($gre_no, $status, $local_ip_addr, $remote_ip_addr, $ttl, $static_ip_addr, $static_netmask, $tun_target, $tun_netmask, $tun_gateway);
}
?>

<?php

$gre_id = 1;
$out = "";
$ret = 99;
$html_out = "";


exec("uci show network.tungre".$gre_id , $out, $ret);
while ( $gre_id <= $MAX_GRE_CLIENTS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$gre_id.'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tungre".$gre_id.".ipaddr").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tungre".$gre_id.".peeraddr").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tungre".$gre_id.".ttl").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tungre".$gre_id."_static.ipaddr").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.tungre".$gre_id."_static.netmask").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.gre".$gre_id."_tunnel.target").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.gre".$gre_id."_tunnel.netmask").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get network.gre".$gre_id."_tunnel.gateway").'</td>';

  $html_out = $html_out.'  <td><a href="gre.php?gre_no='.$gre_id.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="gre.php?action=delete&gre_no='.$gre_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $gre_id += 1;
  exec("uci show network.tungre".$gre_id , $out, $ret);
}

if(count($_GET) > 0) {
  $filled_gre_no = $_GET["gre_no"];
  $action = $_GET["action"];

  if ( $action == "delete" ) {
    delete_ssl_gre($filled_gre_no);
    echo '<script>window.location.href = "gre.php";</script>';
    exit;
  }
}
?>
<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">GRE</h3>
              </div>
              <div class="panel-body">
                <div style="display:none;" id="alertwindow"class="alert alert-warning alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"></span></button>
                  <i class="fa fa-warning"></i> Alert!<p id="verrors">  </p>
                </div>

                <form enctype="multipart/form-data" id="gre_vpn_form" action="gre.php" method="post">
                  <table>
                    <tr>
                      <td>Tunnel</td>
                      <td>
                        <select required id="gre_no_id" class="form-control" name="gre_no">
                          <?php
                          for ($x = 1; $x <= $MAX_GRE_CLIENTS; $x++) {
                            if ($x == $filled_gre_no) {
                              echo '<option selected=selected value="'.$x.'">'.$x.'</option>';
                            } else {
                              echo '<option value="'.$x.'">'.$x.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $client_status = exec("uci get network.tungre".$filled_gre_no.".enabled");
                          if ($client_status == "1") {
                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($client_status == "0") {
                            echo '<input name="status" value="0" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="0" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Disabled</span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Local IP Address</td>
                      <td><input required type="text" id="local_ip_addr" name="Local_IP_Address" class="form-control" value="<?php  $get=exec("uci get network.tungre".$filled_gre_no.".ipaddr"); echo $get?>" ></td>
                    </tr>

                    <tr>
                      <td>Remote IP Address</td>
                      <td><input required type="text" id="remote_ip_addr" name="Remote_IP_Address" class="form-control" value="<?php  $get=exec("uci get network.tungre".$filled_gre_no.".peeraddr"); echo $get?>" >
                      </td>
                    </tr>

                    <tr>
                      <td>TTL</td>
                      <td><input type="number" id="ttl" min="1" max="255" name="TTL" class="form-control" value="<?php  $get=exec("uci get network.tungre".$filled_gre_no.".ttl"); echo $get?>" required>
                      </td>
                    </tr>

                    <tr>
                      <td>Static IP Address</td>
                      <td><input required type="text" id="static_ip_addr" name="Static_IP_Address" class="form-control" value="<?php  $get=exec("uci get network.tungre".$filled_gre_no."_static.ipaddr"); echo $get?>">
                      </td>
                    </tr>

                    <tr>
                      <td>Static Subnet Mask</td>
                      <td><input required type="text" id="static_subnet_mask" name="Static_Subnet_Mask" class="form-control" value="<?php  $get=exec("uci get network.tungre".$filled_gre_no."_static.netmask"); echo $get?>">
                      </td>
                    </tr>

                    <tr>
                      <td>Tunnel Target</td>
                      <td><input required type="text" id="tunnel_target" name="Tunnel_Target" class="form-control" value="<?php  $get=exec("uci get network.gre".$filled_gre_no."_tunnel.target"); echo $get?>">
                      </td>
                    </tr>

                    <tr>
                      <td>Tunnel Subnet Mask</td>
                      <td><input required type="text" id="tunnel_subnet_mask" name="Tunnel_Subnet_Mask" class="form-control" value="<?php  $get=exec("uci get network.gre".$filled_gre_no."_tunnel.netmask"); echo $get?>">
                      </td>
                    </tr>

                    <tr>
                      <td>Tunnel Gateway</td>
                      <td><input required type="text" id="tunnel_gateway" name="Tunnel_Gateway" class="form-control" value="<?php  $get=exec("uci get network.gre".$filled_gre_no."_tunnel.gateway"); echo $get?>">
                      </td>
                    </tr>

                  </table>
                  <br/>
                  <div class="row">
                    <div class="col-md-6">
                      <button type="button" class="btn btn-primary" id="gre" onclick="validate()">Save</button>
                    </div>
                    <div class="col-md-6">
                      <button type="button" class="btn btn-danger">Clear</button>
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
                <h3 class="panel-title">Current Clients (Max: <?php echo $MAX_GRE_CLIENTS; ?>)</h3>
              </div>
              <div class="panel-body">
                <div id="gre_vpn_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Local IP Address</th>
                        <th>Remote IP Address</th>
                        <th>TTL</th>
                        <th>Static IP Address</th>
                        <th>Static Subnet Mask</th>
                        <th>Tunnel Target</th>
                        <th>Tunnel Subnet Mask</th>
                        <th>Tunnel Gateway</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      echo $html_out;
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
<script src="validate.js"></script>
