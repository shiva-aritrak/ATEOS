<?php include '/www/lib/sessioncheck.php' ?>
<head>
	<title>Wireless | Wireless WAN - AP Client</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
include  '/www/lib/wirelesslib.php';
include  '/www/lib/common.php';
?>

<?php

if(count($_POST) > 0)
{
	$status = $_POST["status"];
	
	if($status == "1") {
		disable_client();
	} elseif ($status == "0") {
		$ssid = $_POST["ssid"];
		$encryption=$_POST["encryption"];
		$wireless_password = $_POST["wireless_password"];
		$metric = $_POST["metric"];
		
		enable_client($ssid, $encryption, $wireless_password, $metric);
	}
}
?>

<?php startblock('contentbar') ?>

<div class="main">
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">Wireless WAN - AP Client</h3>
							</div>
							<hr>
							<div class="panel-body">
								<form action="apclient.php" method="post">
									<table>
										<tr>
											<td>Status</td>
											<td>
											  <label class="fancy-radio">
											    <?php
											    $wireless_disabled = exec("uci get wireless.radio0.disabled");
											    if ($wireless_disabled == "0") {
											      echo '<input name="status" value="0" checked="checked" type="radio" required>';
											    } else {
											      echo '<input name="status" value="0" type="radio" required>';
											    }
											    ?>
											    <span><i></i>Enabled</span>
											  </label>
											  <label class="fancy-radio">
											    <?php
											    if ($wireless_disabled == "1") {
											      echo '<input name="status" value="1" checked="checked" type="radio" required>';
											    } else {
											      echo '<input name="status" value="1" type="radio" required>';
											    }
											    ?>
											    <span><i></i>Disabled</span>
											  </label>
											</td>
                    					</tr>

										<tr>
											<td>SSID</td>
											<td>
												<select required name="ssid" id="id_ssid" class="form-control input-sm">
													<?php
													$ssid = exec("uci get wireless.default_radio0.ssid");
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
                                            <select required class="form-control" name="encryption">
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
											<td>Password</td>
											<td><input type="password" name="wireless_password" id="id_wireless_password" class="form-control" value="<?php echo exec("uci get wireless.default_radio0.key"); ?>" required></td>
											<td><a href="#" id="id_apclient_hidepass" onclick="hidepass()"><i class="fa fa-eye-slash"></i></a></td>
										</tr>

										<tr>
											<td>Metric</td>
											<td><input type="text" pattern="^[0-9]*$" title="Metric should be a number" name="metric" id="metric_id" class="form-control" value="<?php echo exec("uci get network.wlan0.metric"); ?>" ></td>
										</tr>

									</table>
									<br/>
									
									<div class="row">
										<div class="col-md-3">
											<button type="submit" class="btn btn-primary" >Submit</button>
										</div>
										<div class="col-md-3">
											<button type="reset" class="btn btn-danger">Reset</button>
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

<?php endblock() ?>

<script type="text/javascript">
function hidepass()
{
	var x = document.getElementById('id_apclient_hidepass');
	if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
		x.innerHTML = '<i class="fa fa-eye"></i>';
		document.getElementById('id_wireless_password').type = "text";
	} else {
		x.innerHTML = '<i class="fa fa-eye-slash"></i>';
		document.getElementById('id_wireless_password').type = "password";
	}
}
</script>
