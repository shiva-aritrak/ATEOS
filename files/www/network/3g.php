<?php include '/www/lib/sessioncheck.php' ?>

<?php
include '/www/lib/networks.php';
include '/www/lib/common.php';
?>

<?php
if(count($_POST) > 0)
{
	$mode = $_POST["mode"];
	if($mode == "3gmodem") {
		// WE GOT SOME DATA IN A POST REQUEST
		$user = $_POST["Username"];
		$password = $_POST["Password"];
		$dial_no = $_POST["dialno"];
		$apn = $_POST["APN"];
		$pin = $_POST["PIN"];
		$mtu = $_POST["MTU"];
		$device = $_POST["device"];
		$metric = $_POST["metric"];
		$ggg_desc = $_POST["ggg_desc"];
		$fw_zone = $_POST["fw_zone"];
		$ggg_peer_dns = $_POST["ggg_peer_dns"];
		set_3g($user, $password, $device, $dial_no, $apn, $pin, $mtu, $metric, $ggg_peer_dns, $ggg_desc, $fw_zone);
	}
	elseif($mode == "tethermode") {
		$tether_interface = $_POST["tether_interface"];
		$metric = $_POST["metric"];
		$tether_desc = $_POST["tether_desc"];
		$mtu = $_POST["MTU"];
		$fw_zone = $_POST["fw_zone"];
		$usb_peer_dns = $_POST["usb_peer_dns"];
		set_tethermode($tether_interface, $metric, $usb_peer_dns, $tether_desc, $mtu, $fw_zone);
	}
	elseif($mode == "disable") {
		disable_3g4g();
	}
}

if(count($_GET) > 0) {
	$action = $_GET["action"];
	if ($action == "modem_detect") {
		$start = 0;
		$modem_count = exec("ls /dev/cdc-wdm* | wc -l");
		if ($modem_count == "1") {
			$start = 0;
		} else if ($modem_count == "2") {
			$start = 4;
		} else if ($modem_count == "3") {
			$start = 8;
		} else {
			echo "Modem not found. Connect modem first";
			exit;
		}
		$end = $start + 3;
		foreach (range($start, $end) as $number) {
			$device = "/dev/ttyUSB".$number;
			$ret = 1;
			$out = "0";
			exec("timeout -t 10 comgt -d ".$device, $out, $ret);
			if ($ret == 0) {
				$payload = json_encode( array( "device"=> $device ) );
				echo $payload;
				exit;
			}
		}
	}
}

?>

<head>
	<title>Network | 3G/4G</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php startblock('contentbar') ?>

<div class="main">
	<!-- MAIN CONTENT -->
	<div class="main-content">
		<div class="container-fluid">


			<div class="row">
				<div class="col-md-6">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">3G/4G</h3>
							</div>
							<div class="panel-body">
								<form onload="preselected()" id="myForm" name="modempage" action="3g.php" method="post">
									<label class="fancy-radio">
										<input name="mode" value="3gmodem" type="radio" id="3gmode" onclick="show(1)" checked>
										<span><i></i>3G Modem</span>
									</label>
									<label class="fancy-radio">
										<input name="mode" value="tethermode" type="radio" id="tmode" onclick="show(2)">
										<span><i></i>Tethering Mode</span>
									</label>
									<label class="fancy-radio">
										<input name="mode" value="disable" type="radio" id="disabled" onclick="show(3)">
										<span><i></i>Disable</span>
									</label>
									<div id="content1" class="content">
										<div><hr>
											<table>
											<tr>
													<td>Description</td>
													<td><input type="text" name="ggg_desc" id="ggg_desc_id" class="form-control" value="<?php  $get=exec("uci get network.3g.desc"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Username</td>
													<td><input type="text" name="Username" id="user_id" class="form-control" value="<?php  $get=exec("uci get network.3g.username"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Password</td>
													<td><input type="password" name="Password" id="user_pass" class="form-control" value="<?php  $get=exec("uci get network.3g.password"); echo $get?>"> </td>
													<td><a href="#" onclick="hidepass()" id="hidepass" ><i class="fa fa-eye-slash"></i></a></td>
												</tr>
												<tr>
													<td>Dial Number</td>
													<td><input type="text" class="form-control" id="dno" name="dialno" value="<?php  $get=exec("uci get network.3g.dialnumber"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>3G Device</td>
													<td><input type="text" class="form-control" id="device" name="device" value="<?php  $get=exec("uci get network.3g.device"); echo $get?>"></td>
													<td><a href="#" onclick="modem_detect()" id="hidepass" ><span class="label label-danger">Detect Modem</span></a></td>
												</tr>
												<tr>
													<td>APN</td>
													<td><input type="text" class="form-control" id="apn" name="APN" value="<?php  $get=exec("uci get network.3g.apn"); echo $get?>" required></td>
												</tr>
												<tr>
													<td>PIN</td>
													<td><input type="text" class="form-control" id="pin" name="PIN" value="<?php  $get=exec("uci get network.3g.pincode"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>MTU</td>
													<td><input type="number" class="form-control" id="mtu" name="MTU" value="<?php  $get=exec("uci get network.3g.mtu"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Metric</td>
													<td><input type="number" class="form-control" id="metric_id" name="metric" value="<?php  $get=exec("uci get network.3g.metric"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Firewall Zone</td>
													<td>
														<select required name="fw_zone" class="form-control input-sm">
														<?php
															$fw_zones = exec("uci get network.3g.fw_zone");
															foreach (get_configured_zones() as $intf) {
																if (in_array($intf, show_zone_networks($fw_zones))) {
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
													<td>Use DNS</td>
													<td>
														<label class="fancy-checkbox">
															<?php
															$peerdns = exec("uci get network.3g.peerdns");
															if ($peerdns == "1") {
																	echo '<input name="ggg_peer_dns" id="id_ggg_peer_dns" checked=checked type="checkbox">';
															} else {
																	echo '<input name="ggg_peer_dns" id="id_ggg_peer_dns" type="checkbox">';
															}
															?>
															<span></span>
														</label>
													</td>
												</tr>
												<tr><td><input type="hidden" class="form-control" value="3gmodem" name="mode"></td></tr>

											</table>
											<br />
											<div class="row">
												<div class="col-md-6">
													<button type="submit" class="btn btn-primary" >Save</button>
												</div>
												<div class="col-md-6">
													<button type="button" class="btn btn-danger" onclick="myFunction()">Clear</button>
												</div>
											</div>
										</div>
									</div>
								</form>

								<div id="content2" class="content hidden"><hr>
									<div>
										<form action="3g.php" method="post">
											<table>
												<tr><td></td><td><input type="hidden" class="form-control" value="tethermode" name="mode"></td></tr>
												<tr>
													<td>Description</td>
													<td><input type="text" class="form-control" id="tether_desc_id" name="tether_desc" value="<?php  $get=exec("uci get network.usb0.desc"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Tethering Interface</td>
													<td><input type="text" class="form-control" id="tether_interface_id" name="tether_interface" value="<?php  $get=exec("uci get network.usb0.ifname"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Metric</td>
													<td><input type="number" class="form-control" id="metric_id" name="metric" value="<?php  $get=exec("uci get network.usb0.metric"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>MTU</td>
													<td><input type="number" class="form-control" id="mtu" name="MTU" value="<?php  $get=exec("uci get network.usb0.mtu"); echo $get?>" ></td>
												</tr>
												<tr>
													<td>Firewall Zone</td>
													<td>
														<select required name="fw_zone" class="form-control input-sm">
														<?php
															$fw_zones = exec("uci get network.usb0.fw_zone");
															foreach (get_configured_zones() as $intf) {
																if (in_array($intf, show_zone_networks($fw_zones))) {
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
													<td>Use DNS</td>
													<td>
															<label class="fancy-checkbox">
																	<?php
																	$peerdns = exec("uci get network.usb0.peerdns");
																	if ($peerdns == "1") {
																			echo '<input name="usb_peer_dns" id="id_usb_peer_dns" checked=checked type="checkbox">';
																	} else {
																			echo '<input name="usb_peer_dns" id="id_usb_peer_dns" type="checkbox">';
																	}
																	?>
																	<span></span>
															</label>
													</td>
											</tr>
											</table>
											<br />
											<div class="row">
												<div class="col-md-6">
													<button type="submit" class="btn btn-primary" >Save</button>
												</div>
												<div class="col-md-6">
													<button type="button" class="btn btn-danger" onclick="myFunction()">Clear</button>
												</div>
											</div>
										</form>
									</div>
								</div>

								<div id="content3" class="content hidden"><hr>
									<form action="3g.php" method="post">
										<p class="demo-button">
											<td><input type="hidden" class="form-control" value="disable" name="mode"></td>
											<button type="submit" class="btn btn-primary">Save</button>
										</p>
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

<div id="modemDetectModal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Modem Device Detection</h4>
			</div>
			<div class="modal-body">
				<p id="modem_detect_text">.</p>
			</div>
		</div>
	</div>
</div>

<?php endblock() ?>

<script>
window.onload=preselected;

var wan = "<?php $ret = 1; exec("uci show network.3g", $out, $ret) ; if(!$ret) { echo "1"; } ?>";
if (wan != "1") {
	wan = "<?php $ret = 1; exec("uci show network.usb0", $out, $ret) ; if(!$ret) { echo "1"; } else { echo "none"; } ?>";
}

<?php
exec("uci show network.usb0", $out, $ret);

if ($ret == 0) {
	echo 'wan_type = "tether";';
}

exec("uci show network.3g", $out, $ret);
if ($ret == 0) {
	echo 'wan_type = "3g";';
}
?>

function preselected()
{
	if(wan == "1"){
		if (wan_type == "3g") {
			document.getElementById("3gmode").checked=true;
			show(1);
		} else if (wan_type == "tether") {
			document.getElementById("tmode").checked=true;
			show(2);
		}
	}	else {
		document.getElementById("disabled").checked=true;
		show(3);
	}
}

function myFunction() {
	document.getElementById("myForm").reset();
}


function show(id) {
	var allDivs = document.getElementsByClassName('content');

	for (var i = 0; i < allDivs.length; i++) {
		allDivs[i].classList.add('hidden');
	}
	document.getElementById('content' + id).classList.remove('hidden');

}

function hidepass() {
	var x = document.getElementById("hidepass");
	if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
		x.innerHTML = '<i class="fa fa-eye"></i>';
		document.getElementById("user_pass").type = "text";
	} else {
		x.innerHTML = '<i class="fa fa-eye-slash"></i>';
		document.getElementById("user_pass").type = "password";
	}
}

function modem_detect() {
	$("#modemDetectModal").modal('toggle');
	$("#modem_detect_text").html("Finding device for 3G modem");
	$.ajax({
		url: "3g.php",
		type: "get",
		data: {
			action: "modem_detect"
		},
		dataType: 'json',
		success: function(response) {
			$("#modem_detect_text").append("</br></br>Device " + response.device + " found for 3G Dongle</br></br>Setting as 3G Device");
			$("#device").val(response.device);
			$("#modem_detect_text").append("</br></br>This window will close in 5 seconds");
			setTimeout(
				function()
				{
					$("#modemDetectModal").modal('toggle');
					$("#modem_detect_text").html("");
				}, 5000);
			},
			error: function(xhr) {
			}
		});
	}

</script>
