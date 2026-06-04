<?php include '/www/lib/sessioncheck.php' ?>
<head>
    <title>SSL VPN | Configuration</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/vpn.php' ?>

<?php

$client_id = 1;
$out = "";
$ret = 99;
$html_out = "";

$response = true;

if(count($_POST) > 0) {
    $status = $_POST["status"];
    $vpn_id = $_POST["vpn_id"];
    $client_no = $_POST["client_no"];
    $persist_key = $_POST["persist_key"];
    $persist_tun = $_POST["persist_tun"];
    $metric = $_POST["metric"];
    $concentrator_ips = $_POST["concentrator_ip"];
    
    $vpn_count_status = false;
    $message = "";
    
    $ssl_vpn_max = exec("uci get anexgate.license.ssl_vpn_max");
    $ssl_vpn_count = exec("grep -o -i sclient /etc/config/openvpn | wc -l");
    
    if ($ssl_vpn_count >= $ssl_vpn_max) {
        $vpn_count_status = true;
        $message = "You have reached the VPN client limit";
    }
    else {
        $response = set_ssl_vpn_config($client_no, $status, $vpn_id, $concentrator_ips, $persist_key, $persist_tun, $metric);
    }
}

if(count($_GET) > 0) {
  $filled_client_no = $_GET["client_no"];
  $action = $_GET["action"]; 
  
  if ( $action == "delete" ) {
    delete_ssl_vpn_from_id($filled_client_no);
    echo '<script>window.location.href = "/vpn/ssl.php";</script>';
    exit;
  }
}

exec("uci show openvpn.sclient".$client_id , $out);
while ( $client_id <= $MAX_VPN_CLIENTS ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.$client_id.'</td>';
  $html_out = $html_out.'  <td>'.show_enabled_disabled(exec("uci get openvpn.sclient".$client_id.".enabled")).'</td>';
  $sync_status = exec("uci get openvpn.sclient".$client_id.".sync_status");
  if ($sync_status == 'success') {
    $html_out = $html_out.'  <td>Success</td>';
  } else if ($sync_status == 'failed') {
    $html_out = $html_out.'  <td>Error</td>';
  } else {
    $html_out = $html_out.'  <td>Unknown</td>';
  }
  $html_out = $html_out.'  <td><samp>'.exec("uci get openvpn.sclient".$client_id.".vpn_id").'</samp></td>';
  $html_out = $html_out.'  <td><samp>'.exec("uci get openvpn.sclient".$client_id.".api_ip").'</samp></td>';
  $html_out = $html_out.'  <td><samp>'.str_replace("'", "", exec("uci get openvpn.sclient".$client_id.".remote")).'</samp></td>';
  $html_out = $html_out.'  <td><a href="/vpn/ssl.php?action=edit&client_no='.$client_id.'"><i class="fa fa-edit"></i></a><a href="/vpn/ssl.php?action=delete&client_no='.$client_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  $client_id += 1;
  exec("uci show openvpn.sclient".$client_id , $out);
}

?>

<?php startblock('contentbar') ?>
<div class="main">
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="col-md-12">
                        <div class="panel">
                            <div class="panel-body">
                                <?php
                                    if ($vpn_count_status) {
                                    echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                                    echo '  <i class="fa fa-warning"></i> '.$message.'</p>';
                                    echo '</div>';
                                    }
                                    if (!$response) {
                                        echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                                        echo '  <i class="fa fa-warning"></i>Error in Fetching VPN Configuration</p>';
                                        echo '</div>';
                                    }
                                ?>
                                <div class="panel-heading">
                                    <h3 class="panel-title">SSL VPN Auto Configure</h3>

                                </div>
                                <div class="panel-body">
                                    <form enctype="multipart/form-data" id="vpn_form" autocomplete="off" action="ssl.php" method="post">
                                        <table class="vpn_table">
                                            <tr>
                                                <td>Client</td>
                                                <td>
                                                <select required id="client_no_id" class="form-control" name="client_no">
                                                    <?php
                                                    for ($x = 1; $x <= $MAX_VPN_CLIENTS; $x++) {
                                                    if ($x == $filled_client_no) {
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
                                                        $vpn_status = exec("uci get openvpn.sclient".$filled_client_no.".enabled");
                                                        if ($vpn_status == "1") {
                                                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                                                        } else {
                                                            echo '<input name="status" value="1" type="radio" required>';
                                                        }
                                                        ?>
                                                        <span><i></i>Enabled</span>
                                                    </label>
                                                    <label class="fancy-radio">
                                                        <?php
                                                        if ($vpn_status == "0") {
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
                                                <td>VPN ID</td>
                                                <td><input required type="text" id="vpn_id_id" name="vpn_id"  pattern="^[A-Za-z0-9-]{2,26}$" class="form-control" value="<?php $get = exec("uci get openvpn.sclient".$filled_client_no.".vpn_id"); echo $get;?>" ></td>
                                            </tr>
                                            <tr>
                                                <td>Concentrator Address</td>
                                                <td><input required type="text"  name="concentrator_ip[]" value="<?php $get = exec("uci get openvpn.sclient".$filled_client_no.".api_ip"); echo $get; ?>"  class="form-control" ></td>
                                            </tr>
                                            <tr>
                                              <td>Metric</td>
                                              <td><input type="text" id="metric_id" name="metric" class="form-control" value="<?php  $get=exec("uci get openvpn.sclient".$filled_client_no.".route_metric"); echo $get; ?>"></td>
                                            </tr>
                                            <tr>
                                              <td>Persist Key</td>
                                              <td>
                                                <label class="fancy-checkbox">
                                                  <?php
                                                  $client_persist_key = exec("uci get openvpn.sclient".$filled_client_no.".persist_key");
                                                  if ($client_persist_key == "1") {
                                                    echo '<input name="persist_key" checked=checked type="checkbox">';
                                                  } else {
                                                    echo '<input name="persist_key" type="checkbox">';
                                                  }
                                                  ?>
                                                  <span></span>
                                                </label>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td>Persist Tunnel</td>
                                              <td>
                                                <label class="fancy-checkbox">
                                                  <?php
                                                  $client_persist_tun = exec("uci get openvpn.sclient".$filled_client_no.".persist_tun");
                                                  if ($client_persist_tun == "1") {
                                                    echo '<input name="persist_tun" checked=checked type="checkbox">';
                                                  } else {
                                                    echo '<input name="persist_tun" type="checkbox">';
                                                  }
                                                  ?>
                                                  <span></span>
                                                </label>
                                              </td>
                                            </tr>
                                        </table>
                                        <br>
                                        <button class="add_concentrator_ip" type="button"><i class="fa fa-plus"></i> Add Concentrator IP</button>
                                        <br>
                                        <br/>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-primary">Save</button>
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
                </div>
                <div class="col-md-8">
                <div class="panel">
                    <div class="panel-body">
                    <div class="panel-heading">
                        <h3 class="panel-title">Current Configs</h3>
                    </div>
                    <div class="panel-body">
                        <div id="static_route_form_div_id" class="content">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Status</th>
                                <th>Sync Status</th>
                                <th>VPN ID</th>
                                <th>Concentrator Address</th>
                                <th>VPN IP/Port(s)</th>
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
<?php startblock('scriptblock') ?>

<script>
$(document).ready(function() {
  var wrapper = $(".vpn_table");
  var add_button = $(".add_concentrator_ip");

  var x = 1;
  $(add_button).click(function(e) {
    e.preventDefault();
    $(wrapper).append('<tr><td></td><td><input  type="text" name="concentrator_ip[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a></td></tr>');
  });

  $(wrapper).on("click", ".delete", function(e) {
    e.preventDefault();
    $(this).parent('tr').remove();
  })
});
</script>
<?php endblock() ?>
