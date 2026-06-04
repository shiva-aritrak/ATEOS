<?php include '/www/lib/sessioncheck.php' ?>

<head>
	<title>Network | Bridge</title>
</head>

<?php
include '/www/sidenav.php';
include '/www/lib/networks.php';
include '/www/lib/common.php';
?>

<?php
$error_message = "";
$found_error = false;

if(count($_GET) > 0) {
	$selected_bridge = $_GET["interface"];
	$port_num = exec("uci get network.".$selected_bridge.".port_num");
	$action = $_GET["action"];
}

if(count($_POST) > 0) {
	foreach (get_network_devices() as $intf) {
		exec("uci set network.".$intf.".bridged='0'");
	}

	$status = $_POST["status"];
	$port_names = $_POST["port_names"];
	$ip_addr = $_POST["bridge_ip_address"];
	$netmask = $_POST["bridge_subnet_mask"];
	$bridge_stp = $_POST["bridge_stp"];
	$mtu = $_POST["mtu"];

	foreach ($port_names as $port_name) {
		$port_configured = exec("uci get network.".$port_name.".ipv4_configured");
		if ($port_configured == "1") {
			$found_error = true;
			$error_message = "<b>".strtoupper($port_name)."</b> is already configured as interface";
		}
	}

	if ($status == "1" && !$found_error) {
		set_bridge_config($port_names, $ip_addr, $netmask, $bridge_stp, $mtu);
	} else if ($status == "0") {
		clear_bridge_dev_device($port_names);
		clear_wan("lan_br0", $reload=true);
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
								<h3 class="panel-title">
									<?php
									if($selected_bridge) {
										echo "Edit Bridge ".strtoupper($selected_bridge);
									} else {
										echo "Bridge Configuration";
									}
									?>
								</h3>
							</div>
							<div class="panel-body">
								<?php 
								if ($error_message) {
									echo '<div class="alert alert-warning alert-dismissible" role="alert">';
									echo '<i class="fa fa-warning"></i> Alert! '.$error_message;
									echo '</div>';
								}
								?>
								<form autocomplete="off" action="bridge.php" method="post">
									<table>
										<tr>
											<td>Bridge Status</td>
											<td>
												<label class="fancy-radio">
												<?php
												$status = exec("uci get network.lan_br0.enabled");
												if ($status == "1") {
													echo '<input id="id_bridge_status_enabled" onclick="enable_bridge_checkbox_check()" name="status" value="1" checked="checked" type="radio" required>';
												} else {
													echo '<input id="id_bridge_status_enabled" onclick="enable_bridge_checkbox_check()" name="status" value="1" type="radio" required>';
												}
												?>
												<span><i></i>Enabled</span>
												</label>
												<label class="fancy-radio">
												<?php
												if ($status == "0") {
													echo '<input id="id_bridge_status_disabled" onclick="enable_bridge_checkbox_check()" name="status" value="0" checked="checked" type="radio" required>';
												} else {
													echo '<input id="id_bridge_status_disabled" onclick="enable_bridge_checkbox_check()" name="status" value="0" type="radio" required>';
												}
												?>
												<span><i></i>Disabled</span>
												</label>
											</td>
										</tr>
                                        <tr id="id_row_ports_to_bridge">
											<td>Ports to Bridge</td>
											<td>
												<select multiple required name="port_names[]" id="id_port_names" class="form-control input-sm">
													<?php
													$ports_in_bridge = explode(" ", exec("uci get network.lan_br0.ports") );
													foreach (get_network_devices() as $intf) {
														// $ifname = exec("uci get network.lan_br0.ports");
														if (in_array($intf, $ports_in_bridge)) {
															echo '<option selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
														} else {
															echo '<option value="'.$intf.'">'.strtoupper($intf).'</option>';
														}
													}
													?>
												</select>
											</td>
										</tr>
                                        <tr id="id_row_ip_address">
											<td>IP Address</td>
											<td><input type="text" pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-4])$" title="Enter a IP Address e.g. 192.168.1.5" class="form-control" id="bridge_static_ip" name="bridge_ip_address" value="<?php echo exec("uci get network.lan_br0.ipaddr"); ?>" ></td>
										</tr>
                                        <tr id="id_row_subnet">
											<td>Subnet Mask</td>
											<td><input type="text" pattern="^(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$" title="Enter a valid subnet mask 255.255.255.0" class="form-control" id="bridge_static_netmask" name="bridge_subnet_mask" value="<?php echo exec("uci get network.lan_br0.netmask"); ?>"></td>
										</tr>
										<tr id="id_row_mtu">
											<td>MTU</td>
											<td><input type="number" min="1200" max="9000" title="MTU should be a number between 1200 and 9000, Default is 1500" class="form-control" name="mtu" id="mtu_id" value="<?php echo exec("uci get network.lan_br0.mtu"); ?>" ></td>
										</tr>
										<tr>
											<td>Enable Spanning Tree Protocol</td>
											<td>
												<label class="fancy-checkbox">
													<?php
													$stp = exec("uci get network.lan_br0.stp");
													if ($stp == "1") {
														echo '<input name="bridge_stp" id="id_bridge_stp" checked=checked type="checkbox">';
													} else {
														echo '<input name="bridge_stp" id="id_bridge_stp" type="checkbox">';
													}
													?>
													<span></span>
												</label>
											</td>
										</tr>
									</table>
								</table>
								<br/>
								<div class="row">
									<div class="col-md-6">
										<button type="submit" class="btn btn-primary">Save</button>
									</div>
									<div class="col-md-6">
										<button type="reset" class="btn btn-danger">Clear</button>
									</div>
								</div>
							</form>
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
window.onload=enable_bridge_checkbox_check;

function enable_bridge_checkbox_check() {
  var bridge_status_enabled = document.getElementById("id_bridge_status_enabled");
  var bridge_status_disabled = document.getElementById("id_bridge_status_disabled");
  if (bridge_status_enabled.checked) {
	document.getElementById("id_port_names").required = true;
	document.getElementById("bridge_static_ip").required = true;
	document.getElementById("bridge_static_netmask").required = true;
	document.getElementById("id_row_ports_to_bridge").hidden = false;
	document.getElementById("id_row_ip_address").hidden = false;
	document.getElementById("id_row_subnet").hidden = false;
	document.getElementById("id_row_mtu").hidden = false;
	document.getElementById("id_bridge_stp").hidden = false;
  } else if (bridge_status_disabled.checked) {
	document.getElementById("id_port_names").required = false;
	document.getElementById("bridge_static_ip").required = false;
	document.getElementById("bridge_static_netmask").required = false;
	document.getElementById("id_row_ports_to_bridge").hidden = true;
	document.getElementById("id_row_ip_address").hidden = true;
	document.getElementById("id_row_subnet").hidden = true;
	document.getElementById("id_row_mtu").hidden = true;
	document.getElementById("id_bridge_stp").hidden = true;
  }
}
</script>
<?php endblock() ?>
