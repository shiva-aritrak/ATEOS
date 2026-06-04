<?php include '/www/lib/sessioncheck.php' ?>
<!-- Set Title of Page -->
<head>
	<title>Global Parameters</title>
</head>

<!-- Load the side nav bar -->
<?php include '/www/sidenav.php' ?>

<!-- Load any Libraries if required/written -->
<?php
include '/www/lib/routing.php';
include '/www/lib/common.php';
?>

<?php
if(count($_POST) > 0) {
	$router_id = $_POST["router_id_name"];
	$log = $_POST["log_id_name"];
	$debug = $_POST["debug_name"];
	$interfaces = $_POST["interfaces"];
	$v46 = $_POST["v46"];
	configure_global_params($router_id, $log, $debug, $interfaces, $v46);
}
?>

<?php startblock('contentbar') ?>
<div class="main">
	<!-- MAIN CONTENT -->
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">Global IPv4 Parameters</h3>
							</div>
							<div class="panel-body">
                                <div id="content1" class="content">
                                    <form id="global_form" action="global_routing.php" method="post">
									  <input type="hidden" id="v46_id" name="v46" value="0">
										<div>
											<table>
												<tr>
												     <td>Router ID</td>
												     <td><input required type="text" id="router_id" name="router_id_name" class="form-control" value="<?php  echo exec("uci get bird4.global.router_id");?>" ></td>
												     	
												</tr>
												<tr>
                                                	<td>Log</td>
                                                	<td>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                $log_enabled = exec("uci get bird4.global.log");
                                                                if ($log_enabled == "all") {
                                                                        echo '<input name="log_id_name" value="all" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="log_id_name" value="all" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Enabled</span>
                                                        </label>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                if ($log_enabled == "off") {
                                                                        echo '<input name="log_id_name" value="off" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="log_id_name" value="off" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Disabled</span>
                                                        </label>
                                                	</td>
												</tr>
												<tr>
                                                	<td>Debug</td>
                                                	<td>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                $debug_enabled = exec("uci get bird4.global.debug");
                                                                if ($debug_enabled == "all") {
                                                                        echo '<input name="debug_name" value="all" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="debug_name" value="all" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Enabled</span>
                                                        </label>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                if ($debug_enabled == "off") {
                                                                        echo '<input name="debug_name" value="off" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="debug_name" value="off" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Disabled</span>
                                                        </label>
                                                	</td>
												</tr>
												
												<tr id="id_interfaces">
													<td>Interfaces</td>
													<td>
														<select multiple required name="interfaces[]" id="id_interfaces" class="form-control input-sm">
																<?php
																$selected_interfaces = explode(" ", exec("uci get bird4.direct1.interface") );
																$selected_ifnames = [];
																foreach ($selected_interfaces as $iface) {
																	foreach (get_configured_link_interfaces() as $intf) {
																		$intf_type = exec("uci get network.".$intf.".type");
																		if ($intf_type == "bridge") {
																			$ifnm = "br-".$intf."";
																		} else {
																			$ifnm = exec("uci get network.".$intf.".ifname");
																		}
																		if ($ifnm == $iface) {
																			$selected_ifnames[] = $intf;
																		}
																	}
																}
																$interfaces_list = get_configured_link_interfaces();
																$v4_list = [];
																foreach($interfaces_list as $intrf) {
																	$is_v6 = exec("uci -q get network.".$intrf.".ipv6");
																	if ($is_v6 == "0") {
																		$v4_list[] = $intrf;
																	}
																	if ($intrf == "lo1") {
																		$v4_list[] = $intrf;
																	}
																}
																foreach ($v4_list as $intf) {
																	if (in_array($intf, $selected_ifnames)) {
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
													<button type="submit" id="ID" class="btn btn-primary">Save</button>
												</div>
												<div class="col-md-6">
													<button type="submit" class="btn btn-danger">Reset</button>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">Global IPv6 Parameters</h3>
							</div>
							<div class="panel-body">
                                <div id="content2" class="content">
                                    <form id="global_form2" action="global_routing.php" method="post">
									<input type="hidden" id="v46_id" name="v46" value="1">
										<div>
											<table>
												<tr>
												     <td>Router ID</td>
												     <td><input required type="text" id="router_id" name="router_id_name" class="form-control" value="<?php  echo exec("uci get bird6.global.router_id");?>" ></td>
												     	
												</tr>
												<tr>
                                                	<td>Log</td>
                                                	<td>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                $log_enabled = exec("uci get bird6.global.log");
                                                                if ($log_enabled == "all") {
                                                                        echo '<input name="log_id_name" value="all" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="log_id_name" value="all" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Enabled</span>
                                                        </label>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                if ($log_enabled == "off") {
                                                                        echo '<input name="log_id_name" value="off" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="log_id_name" value="off" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Disabled</span>
                                                        </label>
                                                	</td>
												</tr>
												<tr>
                                                	<td>Debug</td>
                                                	<td>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                $debug_enabled = exec("uci get bird6.global.debug");
                                                                if ($debug_enabled == "all") {
                                                                        echo '<input name="debug_name" value="all" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="debug_name" value="all" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Enabled</span>
                                                        </label>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                if ($debug_enabled == "off") {
                                                                        echo '<input name="debug_name" value="off" checked="checked" type="radio" required>';
                                                                } else {
                                                                        echo '<input name="debug_name" value="off" type="radio" required>';
                                                                }
                                                                ?>
                                                                <span><i></i>Disabled</span>
                                                        </label>
                                                	</td>
													<tr id="id_interfaces">
													<td>Interfaces</td>
													<td>
														<select multiple required name="interfaces[]" id="id_interfaces" class="form-control input-sm">
																<?php
																$selected_interfaces = explode(" ", exec("uci get bird6.direct1.interface") );
																$selected_ifnames = [];
																foreach ($selected_interfaces as $iface) {
																	foreach (get_configured_link_interfaces() as $intf) {
																		$intf_type = exec("uci get network.".$intf.".type");
																		if ($intf_type == "bridge") {
																			$ifnm = preg_replace('/^br-/', '', $iface);
																		} else {
																			$ifnm = exec("uci get network.".$intf.".ifname");
																		}
																		if ($ifnm == $iface) {
																			$selected_ifnames[] = $intf;
																		}
																	}
																}
																$interfaces_list = get_configured_link_interfaces();
																$v6_list = [];
																foreach($interfaces_list as $intrf) {
																	$is_v6 = exec("uci -q get network.".$intrf.".ipv6");
																	if ($is_v6 == "1") {
																		$v6_list[] = $intrf;
																	}
																}
																foreach ($v6_list as $intf) {
																	if (in_array($intf, $selected_ifnames)) {
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
													<button type="submit" id="ID1" class="btn btn-primary">Save</button>
												</div>
												<div class="col-md-6">
													<button type="submit" class="btn btn-danger">Reset</button>
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

