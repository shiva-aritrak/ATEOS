<?php include '/www/lib/sessioncheck.php' ?>
<!-- Set Title of Page -->
<head>
	<title>Network | SIM</title>
</head>

<!-- Load the side nav bar -->
<?php include '/www/sidenav.php' ?>

<!-- Load any Libraries if required/written -->
<?php include '/www/lib/networks.php' ?>
<?php include '/www/lib/common.php' ?>

<?php
$MODEM_MODE_CHOICES = array("0" => "Auto", "1" => "GSM/2G", "2" => "3G/WCDMA", "3" => "4G/LTE Only");

if(count($_GET) > 0) {
	$action = $_GET["action"];
	$wwan_interface = $_GET["wwan_num"];
	if ($action == "activate") {
		set_sim($wwan_interface);
	}
	// echo '<script>window.location.href = "/network/sim.php";</script>';
    exit;
}

if(count($_POST) > 0)
{
	$wwan_interface = $_POST["wwan_num"];
	$choice_1 = $_POST["choice1"];
	$choice_2 = $_POST["choice2"];

	if ($wwan_interface == "wwan0")
	{
		$old_lock_pin = exec("uci get simswitch.wwan0.lock_pin");
		$lock_pin = $_POST["lock_pin"];

		if($choice_1 == "disable")
		{
			if ($old_lock_pin) {
				if($lock_pin == "2048") {
					$lock_pin="";
					disable_switching_wwan($wwan_interface, $lock_pin);
					$error="PIN Removed and configuration set";
				}
				else if($old_lock_pin != $lock_pin) {
					$error = "PIN Incorrect, Cannot change SIM Configuration";
				} else {
					disable_switching_wwan($wwan_interface, $lock_pin);
				}
			} else {
				disable_switching_wwan($wwan_interface, $lock_pin);
			}
		}
		else
		{
			$wwan_device = $_POST["wwan_name"];
			$apn = $_POST["APN"];
			$pin = $_POST["PIN"];
			$nw_mode = $_POST["nw_mode"];
			$nw_type = $_POST["nw_type"];
			$username = $_POST["auth_username"];
			$password = $_POST["auth_password"];
			$metric = $_POST["metric"];
			$mtu = $_POST["mtu"];
			$peerdns = $_POST["sim1_peer_dns"];
			$wwan_desc = $_POST["wwan_desc1"];
			$fw_zone = $_POST["fw_zone1"];
			if ($old_lock_pin) {
				if($lock_pin == "2048") {
					$lock_pin="";
					set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $nw_type, $wwan_desc, $fw_zone, $mtu);
					$error="PIN Removed and configuration set";
				}
				else if($old_lock_pin != $lock_pin) {
					$error = "PIN Incorrect, Cannot change SIM Configuration";
				} else {
					set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $nw_type, $wwan_desc, $fw_zone, $mtu);
				}
			} else {
				set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $nw_type, $wwan_desc, $fw_zone, $mtu);
			}
		}
	}
	elseif($wwan_interface == "wwan1")
	{
		$old_lock_pin = exec("uci get simswitch.wwan1.lock_pin");
		$lock_pin = $_POST["lock_pin"];

		//wwan2
		if($choice_2 == "disable")
		{
			if ($old_lock_pin) {
				if($lock_pin == "2048") {
					$lock_pin="";
					disable_switching_wwan($wwan_interface, $lock_pin);
					$error="PIN Removed and configuration set";
				}
				else if($old_lock_pin != $lock_pin) {
					$error = "PIN Incorrect, Cannot change SIM Configuration";
				} else {
					disable_switching_wwan($wwan_interface, $lock_pin);
				}
			} else {
				disable_switching_wwan($wwan_interface, $lock_pin);
			}
		}
		else
		{
			$wwan_device = $_POST["wwan_name"];
			$apn = $_POST["APN"];
			$pin = $_POST["PIN"];
			$username = $_POST["auth_username"];
			$password = $_POST["auth_password"];
			$metric = $_POST["metric"];
			$mtu = $_POST["mtu"];
			$peerdns = $_POST["sim2_peer_dns"];
			$nw_mode = $_POST["nw_mode"];
            $nw_type = $_POST["nw_type"];
			$wwan_desc = $_POST["wwan_desc2"];
			$fw_zone = $_POST["fw_zone2"];
			if ($old_lock_pin) {
				if($lock_pin == "2048") {
					$lock_pin="";
					set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $nw_type, $wwan_desc, $fw_zone, $mtu);
					$error="PIN Removed and configuration set";
				}
				else if($old_lock_pin != $lock_pin) {
					$error = "PIN Incorrect, Cannot change SIM Configuration";
				} else {
					set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $nw_type, $wwan_desc, $fw_zone, $mtu);
				}
			} else {
				set_wwan_switching($wwan_interface, $wwan_device, $apn, $nw_mode, $pin, $username, $password, $metric, $lock_pin, $peerdns, $nw_type, $wwan_desc, $fw_zone, $mtu);
			}
		}
	}
}

?>

<?php startblock('contentbar') ?>
<div class="main">
	<!-- MAIN CONTENT -->
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<?php
					if ($error) {
						echo '<h5 class="danger">'.$error.'</h2>';
					}
				?>
				<div class="col-md-6">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">SIM 1
								<?php
								$enabled = exec("uci get simswitch.wwan0.active");
								if ($enabled == "1") {
									echo "(Active)</h3>";
								} else {
									echo '</h3><h5><a id="id_set_active_sim1" href="#">Set Active</a></h5>';
								}
								?>
							</div>
							<div class="panel-body">
								<div id="content1" class="content">
									<form  onload="preselected()" id="wwan0_form" action="sim.php" method="post" >

										<div><hr>
											<table cellspacing="5">
												<tr>
													<td></td>
													<td>
														<label class="radio-inline">
															<input name="choice1" value="enable" id="en1" type="radio" onclick="javascript:wwan0_status_check();" checked>
															<span><i></i>Enable</span>
														</label>
														<label class="radio-inline">
															<input name="choice1" value="disable" id="dis1" type="radio" onclick="javascript:wwan0_status_check();">
															<span><i></i>Disable</span>
														</label>
													</td>
												</tr>

												<tr id="wwan_desc1">
													<td>Description</td>
													<td><input type="text" class="form-control" id="wwan_desc1_id" name="wwan_desc1" value="<?php  $get=exec("uci get simswitch.wwan0.desc"); echo $get?>"></td>
												</tr>

												<tr id="apn1">
													<td>APN</td>
													<td><input type="text" class="form-control" id="w_netmask" name="APN" value="<?php  $get=exec("uci get simswitch.wwan0.apn"); echo $get?>"></td>
												</tr>

												<tr id="nwmode1">
													<td>Network Mode</td>
													<td>
														<select required name="nw_mode" class="form-control input-sm">
														<?php
														$nw_mode = exec("uci get simswitch.wwan0.nw_mode");
														foreach ($MODEM_MODE_CHOICES as $key => $value) {
															if($key == $nw_mode) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>

												<tr id="nwtype1">
													<td>Network Type</td>
													<td>
														<select required name="nw_type" class="form-control input-sm">
														<?php
														$pdptype = exec("uci get simswitch.wwan0.pdptype");
														foreach ($PDPTYPE_CHOICES as $key => $value) {
															if($key == $pdptype) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>
					
												<tr id="pin1">
													<td>PIN</td>
													<td><input type="text" class="form-control" id="w_gateway" name="PIN" value="<?php  $get=exec("uci get simswitch.wwan0.pin"); echo $get?>" ></td>
												</tr>

												<tr id="user1">
													<td>APN Username</td>
													<td><input type="text" name="auth_username" id="user_id" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan0.username"); echo $get?>" ></td>
												</tr>
												<tr id="pass1">
													<td>APN Password</td>
													<td><input type="password" name="auth_password" id="wwan0_user_pass" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan0.password"); echo $get?>"></td>
													<td><a href="#" id="wwan0_hidepass" onclick="hidepass(this.id)"><i class="fa fa-eye-slash"></i></a></td>
												</tr>
												<tr id="metric1">
													<td>Metric</td>
													<td><input type="text" name="metric" id="metric_id" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan0.metric"); echo $get?>" ></td>
												</tr>
												<tr id="mtu1">
													<td>MTU</td>
													<td><input type="number" name="mtu" id="mtu_id" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan0.mtu"); echo $get?>" ></td>
												</tr>
												<tr id="lock_pin1">
													<td>Lock PIN</td>
													<td><input type="password" name="lock_pin" id="lock_pin_id" class="form-control"></td>
												</tr>
												<tr id="fw_zone1">
													<td>Firewall Zone</td>
													<td>
														<select required name="fw_zone1" class="form-control input-sm">
														<?php
															$fw_zones = exec("uci get simswitch.wwan0.fw_zone");
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
												<tr id="use_dns1">
													<td>Use DNS</td>
													<td>
														<label class="fancy-checkbox">
															<?php
																$peerdns = exec("uci get network.wwan0.peerdns");
																if ($peerdns == "1") {
																		echo '<input name="sim1_peer_dns" id="id_sim1_peer_dns" checked=checked type="checkbox">';
																} else {
																		echo '<input name="sim1_peer_dns" id="id_sim1_peer_dns" type="checkbox">';
																}
															?>
															<span></span>
														</label>
													</td>
                                                 </tr>

													<td><input type="hidden" class="form-control" value="wwan0" name="wwan_num"></td>
													<td><input type="hidden" class="form-control" value="/dev/ttyUSB2" name="wwan_name"></td>
												</tr>
											</table>
											<br />
											<div class="row">
												<div class="col-md-6">
													<button type="submit"  class="btn btn-primary">Save</button>
												</div>
												<div class="col-md-6">
													<button type="button" class="btn btn-danger" onclick="reset_wwan0_form_func()">Clear</button>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-6">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">SIM 2
								<?php
								$enabled = exec("uci get simswitch.wwan1.active");
								if ($enabled == "1") {
									echo "(Active)</h3>";
								} else {
									echo '</h3><h5><a id="id_set_active_sim2" href="#">Set Active</a></h5>';
								}
								?>
							</div>
							<div class="panel-body">

								<form onload="preselected()" action="sim.php" method="post" id="wwan1_form" >
									<div id="cont1" class="cont">

										<div><hr>
											<table cellspacing="5">
												<tr>
													<td></td>
													<td>
														<label class="radio-inline">
															<input name="choice2" value="enable" id="en2" type="radio" onclick="javascript:wwan1_status_check();">
															<span><i></i>Enable</span>
														</label>
														<label class="radio-inline">
															<input name="choice2" value="disable" id="dis2" type="radio" onclick="javascript:wwan1_status_check();" checked>
															<span><i></i>Disable</span>
														</label>
													</td>
												</tr>

												<tr id="wwan_desc2">
													<td>Description</td>
													<td><input type="text" class="form-control" id="wwan_desc2_id" name="wwan_desc2" value="<?php  $get=exec("uci get simswitch.wwan1.desc"); echo $get?>"></td>
												</tr>

												<tr id="apn2">
													<td>APN</td>
													<td><input type="text" class="form-control" id="w1_netmask" name="APN" value="<?php  $get=exec("uci get simswitch.wwan1.apn"); echo $get?>" ></td>
												</tr>

												<tr id="nwmode2">
													<td>Network Mode</td>
													<td>
														<select required name="nw_mode" class="form-control input-sm">
														<?php
														$nw_mode = exec("uci get simswitch.wwan0.nw_mode");
														foreach ($MODEM_MODE_CHOICES as $key => $value) {
															if($key == $nw_mode) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>

												<tr id="nwtype2">
													<td>Network Type</td>
													<td>
														<select required name="nw_type" class="form-control input-sm">
														<?php
														$pdptype = exec("uci get simswitch.wwan1.pdptype");
														foreach ($PDPTYPE_CHOICES as $key => $value) {
															if($key == $pdptype) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>


												<tr id="pin2">
													<td>Pin</td>
													<td><input type="text" class="form-control" id="w1_gateway" name="PIN" value="<?php  $get=exec("uci get simswitch.wwan1.pin"); echo $get?>"></td>
												</tr>

												<tr id="user2">
													<td>APN Username</td>
													<td><input type="text" name="auth_username" id="user_id" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan1.username"); echo $get?>"></td>
												</tr>

												<tr id="pass2">
													<td>APN Password</td>
													<td><input type="password" name="auth_password" id="wwan1_user_pass" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan1.password"); echo $get?>"></td>
													<td><a href="#"  id="wwan1_hidepass" onclick="hidepass(this.id)"><i class="fa fa-eye-slash"></i></a></td>
												</tr>

												<tr id="metric2">
													<td>Metric</td>
													<td><input type="text" name="metric" id="metric_id" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan1.metric"); echo $get?>" ></td>
												</tr>

												<tr id="mtu2">
													<td>MTU</td>
													<td><input type="number" name="mtu" id="mtu_id" class="form-control" value="<?php  $get=exec("uci get simswitch.wwan1.mtu"); echo $get?>" ></td>
												</tr>

												<tr id="lock_pin2">
													<td>Lock PIN</td>
													<td><input type="password" name="lock_pin" id="lock_pin_id" class="form-control"></td>
												</tr>
												<tr id="fw_zone2">
													<td>Firewall Zone</td>
													<td>
														<select required name="fw_zone2" class="form-control input-sm">
														<?php
															$fw_zones = exec("uci get simswitch.wwan1.fw_zone");
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
												<tr id="use_dns2">
													<td>Use DNS</td>
													<td>
														<label class="fancy-checkbox">
															<?php
																$peerdns = exec("uci get network.wwan0.peerdns");
																if ($peerdns == "1") {
																		echo '<input name="sim2_peer_dns" id="id_sim2_peer_dns" checked=checked type="checkbox">';
																} else {
																		echo '<input name="sim2_peer_dns" id="id_sim2_peer_dns" type="checkbox">';
																}
															?>
															<span></span>
														</label>
													</td>
                                                </tr>

												<tr>
													<td><input type="hidden" class="form-control" value="wwan1" name="wwan_num"></td>
													<td><input type="hidden" class="form-control" value="/dev/ttyUSB2" name="wwan_name"></td>
												</tr>
											</table>
											<br />
											<div class="row">
												<div class="col-md-6">
													<button type="submit"  class="btn btn-primary">Save</button>
												</div>
												<div class="col-md-6">
													<button type="button" class="btn btn-danger" onclick="reset_wwan1_form_func()">Clear</button>
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
		</div>
	</div>
</div>

<?php endblock() ?>
<?php startblock('scriptblock') ?>

<script>
window.onload=preselected;
function preselected()
{
	<?php
	$ret_code = 0;
	$out= "";
	$out = exec("uci get simswitch.wwan0.enabled");
	if ($out == "1") { echo 'var select = 0;'; } else { echo 'var select = 1;'; }
	$out = exec("uci get simswitch.wwan1.enabled");
	if ($out == "1") { echo 'var select1 = 0;'; } else { echo 'var select1 = 1;'; }
	?>

	if(select == 1)
	{
		document.getElementById('dis1').checked=true;
		document.getElementById('apn1').style.display = 'none';
		document.getElementById('nwmode1').style.display = 'none';
		document.getElementById('nwtype1').style.display = 'none';
		document.getElementById('pin1').style.display = 'none';
		document.getElementById('user1').style.display = 'none';
		document.getElementById('pass1').style.display = 'none';
		document.getElementById('mtu1').style.display = 'none';
		document.getElementById('metric1').style.display = 'none';
		document.getElementById('wwan_desc1').style.display = 'none';
		document.getElementById('lock_pin1').style.display = 'none';
		document.getElementById('use_dns1').style.display = 'none';
		document.getElementById('fw_zone1').style.display = 'none';
	}
	else
	{
		document.getElementById('en1').checked=true;
	}

	if(select1 == 1)
	{
		document.getElementById('dis2').checked=true;
		document.getElementById('apn2').style.display = 'none';
		document.getElementById('nwmode2').style.display = 'none';
		document.getElementById('nwtype2').style.display = 'none';
		document.getElementById('pin2').style.display = 'none';
		document.getElementById('user2').style.display = 'none';
		document.getElementById('pass2').style.display = 'none';
		document.getElementById('metric2').style.display = 'none';
		document.getElementById('mtu2').style.display = 'none';
		document.getElementById('wwan_desc2').style.display = 'none';
		document.getElementById('lock_pin2').style.display = 'none';
		document.getElementById('use_dns2').style.display = 'none';
		document.getElementById('fw_zone2').style.display = 'none';
	}
	else
	{
		document.getElementById('en2').checked=true;
	}


}
function reset_wwan0_form_func()
{
	document.getElementById("wwan0_form").reset();
	document.getElementById("verrors").innerHTML="";
	document.getElementById("alertwindow").style.display="none";
}

function reset_wwan1_form_func()
{
	document.getElementById("wwan1_form").reset();
	document.getElementById("verrors").innerHTML="";
	document.getElementById("alertwindow").style.display="none";
}

function hidepass(show_pass_clicked_id)
{
	var x = document.getElementById(show_pass_clicked_id);
	var wwan_name = show_pass_clicked_id.split("_",1);

	if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
		x.innerHTML = '<i class="fa fa-eye"></i>';

		document.getElementById(wwan_name[0].concat("_user_pass")).type = "text";
	} else {
		x.innerHTML = '<i class="fa fa-eye-slash"></i>';
		document.getElementById(wwan_name[0].concat("_user_pass")).type = "password";
	}
}
</script>

<script type="text/javascript">

function wwan0_status_check()
{
	if (document.getElementById('en1').checked==true)
	{
		document.getElementById('apn1').style.display = '';
		document.getElementById('nwmode1').style.display = '';
		document.getElementById('nwtype1').style.display = '';
		document.getElementById('pin1').style.display = '';
		document.getElementById('user1').style.display = '';
		document.getElementById('pass1').style.display = '';
		document.getElementById('metric1').style.display = '';
		document.getElementById('mtu1').style.display = '';
		document.getElementById('wwan_desc1').style.display = '';
		document.getElementById('lock_pin1').style.display = '';
		document.getElementById('use_dns1').style.display = '';
		document.getElementById('fw_zone1').style.display = '';
	}
	else if (document.getElementById('dis1').checked==true)
	{
		document.getElementById('apn1').style.display = 'none';
		document.getElementById('nwmode1').style.display = 'none';
		document.getElementById('nwtype1').style.display = 'none';
		document.getElementById('pin1').style.display = 'none';
		document.getElementById('user1').style.display = 'none';
		document.getElementById('pass1').style.display = 'none';
		document.getElementById('metric1').style.display = 'none';
		document.getElementById('mtu1').style.display = 'none';
		document.getElementById('wwan_desc1').style.display = 'none';
		document.getElementById('lock_pin1').style.display = 'none';
		document.getElementById('use_dns1').style.display = 'none';
		document.getElementById('fw_zone1').style.display = 'none';
	}
}

function wwan1_status_check()
{
	if (document.getElementById('en2').checked==true)
	{
		document.getElementById('apn2').style.display = '';
		document.getElementById('nwmode2').style.display = '';
		document.getElementById('nwtype2').style.display = '';
		document.getElementById('pin2').style.display = '';
		document.getElementById('user2').style.display = '';
		document.getElementById('pass2').style.display = '';
		document.getElementById('metric2').style.display = '';
		document.getElementById('mtu2').style.display = '';
		document.getElementById('wwan_desc2').style.display = '';
		document.getElementById('lock_pin2').style.display = '';
		document.getElementById('use_dns2').style.display = '';
		document.getElementById('fw_zone2').style.display = '';
	}
	else if (document.getElementById('dis2').checked==true)
	{
		document.getElementById('apn2').style.display = 'none';
		document.getElementById('nwmode2').style.display = 'none';
		document.getElementById('nwtype2').style.display = 'none';
		document.getElementById('pin2').style.display = 'none';
		document.getElementById('user2').style.display = 'none';
		document.getElementById('pass2').style.display = 'none';
		document.getElementById('metric2').style.display = 'none';
		document.getElementById('mtu2').style.display = 'none';
		document.getElementById('wwan_desc2').style.display = 'none';
		document.getElementById('lock_pin2').style.display = 'none';
		document.getElementById('use_dns2').style.display = 'none';
		document.getElementById('fw_zone2').style.display = 'none';
	}
}
</script>

<script>
	function sleep(milliseconds) {
  const date = Date.now();
  let currentDate = null;
  do {
    currentDate = Date.now();
  } while (currentDate - date < milliseconds);
}


$(document).ready(function() {
  toastr.options = {
    positionClass: "toast-top-full-width",
	fadeOut: 40000
  };

  $("#id_set_active_sim1").click(function() {
	toastr.success('Setting SIM1 as active SIM, Please wait...', "Success");
    $.get("/network/sim.php",
    {
      action : "activate",
      wwan_num : "wwan0"
    })
    .done( function(msg) {
		location.reload();
    })
    .fail( function(xhr, textStatus, errorThrown) {
		sleep(5000);
		location.reload();
    });
  });

  $("#id_set_active_sim2").click(function() {
	toastr.success('Setting SIM2 as active SIM, Please wait...', "Success");
    $.get("/network/sim.php",
    {
      action : "activate",
      wwan_num : "wwan1"
    })
    .done( function(msg) {
		location.reload();
    })
    .fail( function(xhr, textStatus, errorThrown) {
		sleep(5000);
		location.reload();
    });
  });

});
</script>
<?php endblock() ?>