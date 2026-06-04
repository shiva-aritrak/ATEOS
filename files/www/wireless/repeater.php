<?php include '/www/lib/sessioncheck.php' ?>
<head>
	<title>Wireless | Repeater</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
include  '/www/lib/wirelesslib.php';
include  '/www/lib/common.php';
?>

<?php

if(count($_POST) > 0)
{
	$repeater_status = $_POST["repeater_status"];
	$ap_ssid = $_POST["ap_ssid"];
	$ap_encryption = $_POST["ap_encryption"];
	$ap_wireless_password = $_POST["ap_wireless_password"];
	$client_ssid = $_POST["client_ssid"];
	$client_encryption = $_POST["client_encryption"];
	$wireless_password = $_POST["wireless_password"];
	$part_of_network = $_POST["part_of_network"];
	$metric = $_POST["metric"];
	
	if($repeater_status == "1") {
		repeater_enable($repeater_status, $ap_ssid, $ap_encryption, $ap_wireless_password, $client_ssid, $client_encryption, $wireless_password, $part_of_network, $metric);
	} else {
		repeater_disable();
	}
}
?>

<?php startblock('contentbar') ?>

<div class="main">
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="panel">
					<div class="panel-body">
						<form  id="form" action="repeater.php" method="post" >
							<div class="panel-heading">
								<h3 class="panel-title">Repeater Mode</h3>
							</div>
							<div class="panel-body">
								<table>
									<tr>
										<td></td>
										<td>
											<label class="radio-inline">
												<?php $repeater_status=exec("uci get wireless.radio0.disabled");
												if($repeater_status=="0"){
													echo'<input name="repeater_status" value="1" type="radio" checked="checked">';
												}
												else{
													echo'<input name="repeater_status" value="1" type="radio">';
												}
												?>
												<span><i></i>Enable</span>
											</label>
											<label class="radio-inline">
												<?php if($repeater_status=="1"){
													echo'<input name="repeater_status" value="0" type="radio" checked="checked">';
												}
												else{
													echo'<input name="repeater_status" value="0" type="radio">';
												}
												?>
												<span><i></i>Disable</span>
											</label>
										</td>
									</tr>
								</table>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="panel">
										<div class="panel-body">
											<div class="panel-heading">
												<h3 class="panel-title">Access Point Settings</h3>
											</div>
											<div class="panel-body">
												<table>
													<tr>
														<td>SSID</td>
														<td><input type="text" class="form-control" name="ap_ssid" value="<?php echo exec("uci get wireless.default_radio0.ssid"); ?>" ></td>
													</tr>
													<tr>
														<td>Security</td>
														<td>
															<select required class="form-control" name="ap_encryption">
																<?php
																$encryption = exec("uci get wireless.default_radio0.encryption");
																foreach ($WIRELESS_ENCRYPTION_CHOICES as $key => $value) {
																	if ($key == $encryption) {
																		echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
																	}
																	else {
																		echo '<option value="'.$key.'">'.$value.'</option>';
																	}
																}
																?>
															</select>
														</td>
													</tr>
													<tr>
														<td>Part of LAN Network</td>
														<td>
															<select required name="part_of_network" id="id_part_of_network" class="form-control input-sm">
															<?php
																$interface = exec("uci get wireless.default_radio1.part_of_network");
																foreach (get_lan_interfaces() as $intf) {
																if(strtolower($interface) == strtolower($intf)) {
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
														<td>Passphrase</td>
														<td><input type="password" name="ap_wireless_password" id="id_ap_wireless_password" class="form-control" value="<?php echo exec("uci get wireless.default_radio0.key"); ?>" required></td>
														<td><a href="#" id="id_ap_wireless_password_hidepass" onclick="ap_hidepass()"><i class="fa fa-eye-slash"></i></a></td>
													</tr>

												</table>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="panel">
										<div class="panel-body">
											<div class="panel-heading">
												<h3 class="panel-title">Client Settings</h3>
											</div>
											<div class="panel-body">
												<table>
													<tr>
														<td>SSID</td>
														<td>
															<select required name="client_ssid" id="id_client_ssid" class="form-control input-sm">
																<?php
																$ssid = exec("uci get wireless.default_radio1.ssid");
																foreach (get_wireless_ssids() as $scanned_ssid) {
																	if(strtolower($ssid) == strtolower(trim($scanned_ssid))) {
																		echo '<option selected="selected" value="'.trim($scanned_ssid).'">'.trim($scanned_ssid).'</option>';
																	} else {
																		echo '<option value="'.trim($scanned_ssid).'">'.trim($scanned_ssid).'</option>';
																	}
																}
																?>
															</select>
														</td>
													</tr>
													
													<tr>
														<td>Security</td>
														<td>
															<select required class="form-control" name="client_encryption">
																<?php
																$encryption = exec("uci get wireless.default_radio1.encryption");
																foreach ($WIRELESS_ENCRYPTION_CHOICES as $key => $value) {
																	if ($key == $encryption) {
																		echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
																	}
																	else {
																		echo '<option value="'.$key.'">'.$value.'</option>';
																	}
																}
																?>
															</select>
														</td>
													</tr>
													<tr>
														<td>Passphrase</td>
														<td><input type="password" name="wireless_password" id="id_wireless_password" class="form-control" value="<?php echo exec("uci get wireless.default_radio1.key"); ?>" required></td>
														<td><a href="#" id="id_wireless_password_hidepass" onclick="hidepass()"><i class="fa fa-eye-slash"></i></a></td>
													</tr>
													
													<tr>
														<td>Metric</td>
														<td><input type="text" pattern="^[0-9]*$" title="Metric should be a number" name="metric" id="metric_id" class="form-control" value="<?php echo exec("uci get network.wlan1.metric"); ?>" ></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="col-md-6">
										<button type="submit"  class="btn btn-primary">Save</button>
									</div>
									<div class="col-md-6">
										<button type="button" class="btn btn-danger">Clear</button>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endblock() ?>

<script type="text/javascript">
	function hidepass()
	{
		var x = document.getElementById('id_wireless_password_hidepass');
		if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
			x.innerHTML = '<i class="fa fa-eye"></i>';
			document.getElementById('id_wireless_password').type = "text";
		} else {
			x.innerHTML = '<i class="fa fa-eye-slash"></i>';
			document.getElementById('id_wireless_password').type = "password";
		}
	}
	function ap_hidepass()
	{
		var x = document.getElementById('id_ap_wireless_password_hidepass');
		if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
			x.innerHTML = '<i class="fa fa-eye"></i>';
			document.getElementById('id_ap_wireless_password').type = "text";
		} else {
			x.innerHTML = '<i class="fa fa-eye-slash"></i>';
			document.getElementById('id_ap_wireless_password').type = "password";
		}
	}
</script>
