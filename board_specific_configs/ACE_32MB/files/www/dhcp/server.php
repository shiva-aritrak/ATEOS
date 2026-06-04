<?php include '/www/lib/sessioncheck.php' ?>

<head>
	<title>DHCP | Server</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
include '/www/lib/common.php';
include '/www/lib/dhcplib.php';

if(count($_GET) > 0) {
	$selected_interface = $_GET["interface"];
	$action = $_GET["action"];
	$servers = get_lan_dhcp_servers($selected_interface);
	$routers = get_lan_dhcp_router_ip($selected_interface);
	if ($action == "delete") {
		delete_dhcp_server($selected_interface);
		echo '<script>window.location.href = "server.php";</script>';
		exit;
	}
}

if(count($_POST) > 0) {
	$dhcp_interface = $_POST["dhcp_interface"];

	
	$startip = $_POST["start_ip"];
	$endip = $_POST["end_ip"];

	if(filter_var($startip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($endip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$lan_ip = exec("uci get network.".$dhcp_interface.".ipaddr");
		$lan_ip_num = ip2long($lan_ip);
		$startip_num = ip2long($startip);
		$endip_num = ip2long($endip);

		$start = $startip_num - $lan_ip_num + 1;
		$range = $endip_num - $startip_num + 1;
	} else if(filter_var($startip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($endip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {   
		$startip_num = ipv6_2long($startip);
		$endip_num = ipv6_2long($endip);

		$start = $startip_num - $lan_ip_num + 1;
		$range = $endip_num - $startip_num;
	}

	$status = $_POST["status"];
	$dhcpv4_mode = $_POST["dhcpv4_mode"];
	$leasedur = $_POST["lease_duration"];
	$dns_servers = $_POST["dns_servers"];
	$force = $_POST["force"];
	$strict_dhcp = $_POST["strict_dhcp"];
	$dhcpv6 = $_POST["dhcpv6"];
	$ra = $_POST["ra"];
	$ra_management = $_POST["ra_management"];
	$ndp = $_POST["ndp"];
	$relay_master = $_POST["relay_master"];
	$ra_default = $_POST["ra_default"];
	$adv_hoplimit = $_POST["adv_hoplimit"];
        $ra_route_pref = $_POST["ra_route_pref"];
        $adv_mtu = $_POST["adv_mtu"];
        $adv_lifetime = $_POST["adv_lifetime"];
        $ra_min_interval = $_POST["ra_min_interval"];
        $ra_max_interval = $_POST["ra_max_interval"];
        $adv_pref_lifetime = $_POST["adv_pref_lifetime"];
        $ra_dns = $_POST["ra_dns"];
	$router = $_POST["router_ip"];
	$add_pool1 = $_POST["add_ip_pool1"];
	$add_pool2 = $_POST["add_ip_pool2"];
	$add_pool3 = $_POST["add_ip_pool3"];
	
	set_dhcp_server($dhcp_interface, $status, $startip, $endip, $start, $range, $leasedur, $dns_servers, $force, $strict_dhcp, $dhcpv6, $ra, $ra_management, $ndp, $relay_master, $dhcpv4_mode, $ra_default, $adv_hoplimit, $ra_route_pref, $adv_mtu, $adv_lifetime, $ra_min_interval, $ra_max_interval, $adv_pref_lifetime, $ra_dns, $router, $add_pool1, $add_pool2, $add_pool3);
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
								<h3 class="panel-title">DHCP Server</h3>
							</div>
							<div class="panel-body">
								<div id="content1" class="content">
									<form autocomplete="off" id="dhcp_server_form" action="server.php" method="post">
										<div>
											<table>
												<tr>
													<td>Interface</td>
													<td>
														<select required name="dhcp_interface" id="id_dhcp_interface" class="form-control input-sm">
															<?php
															foreach (get_configured_link_interfaces() as $intf) {
																if(strtolower($selected_interface) == strtolower($intf)) {
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
													<td>Status</td>
													<td>
														<label class="fancy-radio">
															<?php
															$dhcp_ignore = exec("uci get dhcp.".$selected_interface.".ignore");
															if ($dhcp_ignore != "1") {
																echo '<input name="status" value="0" checked="checked" type="radio" required>';
															} else {
																echo '<input name="status" value="0" type="radio" required>';
															}
															?>
															<span><i></i>Enabled</span>
														</label>
														<label class="fancy-radio">
															<?php
															if ($dhcp_ignore == "1") {
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
													<td>DHCPv4 Server</td>
													<td>
														<label class="fancy-checkbox">
															<?php
															$dhcpv4 = exec("uci get dhcp.".$selected_interface.".dhcpv4");
															if ($dhcpv4 == "server")
															{
																echo '<input name="dhcpv4_mode" checked=checked type="checkbox">';
															}
															else
															{
																echo '<input name="dhcpv4_mode" type="checkbox">';
															}
															?><span></span>

														</label>

													</td>
												<tr>
													<td>Force DHCP Server</td>
													<td>
														<label class="fancy-checkbox">
															<?php
															$force = exec("uci get dhcp.".$selected_interface.".force");
															if ($force == "1")
															{
																echo '<input name="force" checked=checked type="checkbox">';
															}
															else
															{
																echo '<input name="force" type="checkbox">';
															}
															?><span></span>

														</label>
													</td>
												</tr>
												
												<tr>
													<td>Strict DHCP</td>
													<td>
														<label class="fancy-checkbox">
															<?php
															$strict_dhcp = exec("uci get dhcp.".$selected_interface.".dynamicdhcp");
															if ($strict_dhcp == "0")
															{
																echo '<input name="strict_dhcp" checked=checked type="checkbox">';
															}
															else
															{
																echo '<input name="strict_dhcp" type="checkbox">';
															}
															?><span></span>

														</label>
													</td>
												</tr>
												<tr>
                                                                                                        <td>Router IP</td>
													<?php
														if ($routers) {
                                                                                                                	echo "<td><input type='text' id='rip' name='router_ip' class='form-control' value='".$routers."'></td>";
                                                                                                        	} else {
                                                                                                                	echo "<td><input type='text' id='rip' name='router_ip' class='form-control' value=''></td>";
                                                                                                        	}
                                                                                                        ?>
                                                                                                </tr>
												<tr>
													<td>Start IP</td>
													<td><input type="text" id="sip" name="start_ip" class="form-control" value="<?php echo exec("uci get dhcp.".$selected_interface.".start_ip");?>"></td>
												</tr>
												<tr>
													<td>End IP</td>
													<td><input type="text" id="eip" name="end_ip" class="form-control" value="<?php echo exec("uci get dhcp.".$selected_interface.".end_ip");?>"></td>
												</tr>
												<tr>
                                                                                                        <td>Additional IP POOL-1</td>
                                                                                                        <td><input type="text" id="aipp1" name="add_ip_pool1" class="form-control" placeholder= "172.16.1.20-172.16.1.25" value="<?php echo exec("uci get dhcp.".$selected_interface."_p1.pool");?>"></td>
                                                                                                </tr>
												<tr>
                                                                                                        <td>Additional IP POOL-2</td>
                                                                                                        <td><input type="text" id="aipp2" name="add_ip_pool2" class="form-control" placeholder= "172.16.1.50-172.16.1.55" value="<?php echo exec("uci get dhcp.".$selected_interface."_p2.pool");?>"></td>
                                                                                                </tr>
												<tr>
                                                                                                        <td>Additional IP POOL-3</td>
                                                                                                        <td><input type="text" id="aipp3" name="add_ip_pool3" class="form-control" placeholder= "172.16.1.60-172.16.1.65" value="<?php echo exec("uci get dhcp.".$selected_interface."_p3.pool");?>"></td>
                                                                                                </tr>
												<tr>
													<td>Lease Duration (Hours)</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".leasetime");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="text" id="ldur" name="lease_duration" class="form-control" placeholder= "(in hours)" value = "<?php echo($arr[0]); ?>"></td>
												</tr>

												<tr>
													<td>DHCPv6 Mode</td>
													<td>
														<select name="dhcpv6" class="form-control input-sm">
														<?php
														$dhcpv6_mode = exec("uci get dhcp.".$selected_interface.".dhcpv6");
														foreach ($DHCPV6_MODES as $key => $value) {
															if($key == $dhcpv6_mode) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>														

												<tr>
													<td>DHCPv6 Server Mode</td>
													<td>
														<select name="ra_management" class="form-control input-sm">
														<?php
														$ra_management = exec("uci get dhcp.".$selected_interface.".ra_management");
														foreach ($DHCPV6_SERVER_MODES as $key => $value) {
															if($key == $ra_management) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>

												<tr>
													<td>DHCPv6 Router Advertisement Mode</td>
													<td>
														<select name="ra" class="form-control input-sm">
														<?php
														$dhcpv6_ra_mode = exec("uci get dhcp.".$selected_interface.".ra");
														foreach ($DHCPV6_RA_MODES as $key => $value) {
															if($key == $dhcpv6_ra_mode) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>														

												<tr>
													<td>DHCPv6 NDP</td>
													<td>
														<select name="ndp" class="form-control input-sm">
														<?php
														$ndp_mode = exec("uci get dhcp.".$selected_interface.".ndp");
														foreach ($DHCPV6_NDP_MODES as $key => $value) {
															if($key == $ndp_mode) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>														

												<tr>
													<td>Relay Mode</td>
													<td>
														<label class="fancy-radio">
															<?php
															$relay_master = exec("uci get dhcp.".$selected_interface.".master");
															if ($relay_master == "1") {
																echo '<input name="relay_master" value="1" checked="checked" type="radio">';
															} else {
																echo '<input name="relay_master" value="1" type="radio">';
															}
															?>
															<span><i></i>Master</span>
														</label>
														<label class="fancy-radio">
															<?php
															if ($relay_master == "0") {
																echo '<input name="relay_master" value="0" checked="checked" type="radio">';
															} else {
																echo '<input name="relay_master" value="0" type="radio">';
															}
															?>
															<span><i></i>Non-Master Relay</span>
														</label>
													</td>
												</tr>
												<tr>
													<td>Announce IPv6 Default Route</td>
													<td>
														<label class="fancy-checkbox">
															<?php
															$ra_default = exec("uci get dhcp.".$selected_interface.".ra_default");
															if ($ra_default == "0") {
																echo '<input value="0" name="ra_default" id="id_ra_default" type="checkbox">';
															} else {
																echo '<input value="1" name="ra_default" id="id_ra_default" checked=checked type="checkbox">';
															}
															?>
															<span></span>
														</label>
													</td>
												</tr>
												<tr>
													<td>Advertise Hop Limit</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".ra_hoplimit");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="number" min="0" max="255" id="adv_hoplimit_id" name="adv_hoplimit" class="form-control" value = "<?php echo($arr[0]); ?>"></td>
												</tr>
												<tr>
													<td>Advertise Route Preference</td>
													<td>
														<select name="ra_route_pref" class="form-control input-sm">
														<?php
														$adv_route_pref = exec("uci get dhcp.".$selected_interface.".ra_preference");
														foreach ($DHCPV6_RA_ROUTE_PREFERENCE as $key => $value) {
															if($key == $adv_route_pref) {
															echo '<option selected="selected" value="'.$key.'">'.$value.'</option>';
															} else {
															echo '<option value="'.$key.'">'.$value.'</option>';
															}
														}
														?>
														</select>
													</td>
												</tr>
												<tr>
													<td>Advertise MTU</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".ra_mtu");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="number" min="0" max="9000" id="adv_mtu_id" name="adv_mtu" class="form-control" value = "<?php echo($arr[0]); ?>"></td>
												</tr>
												<tr>
													<td>Advertise Lifetime (In Secs)</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".ra_lifetime");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="text" id="adv_lifetime_id" name="adv_lifetime" placeholder= "(In Secs)" class="form-control" value = "<?php echo($arr[0]); ?>"></td>
												</tr>
												<tr>
													<td>RA Min Interval (In Secs)</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".ra_mininterval");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="number" min="200" id="ra_min_interval_id" name="ra_min_interval" placeholder= "(In Secs)" class="form-control" value = "<?php echo($arr[0]); ?>"></td>
												</tr>
												<tr>
													<td>RA Max Interval (In Secs)</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".ra_maxinterval");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="number" id="ra_max_interval_id" name="ra_max_interval" placeholder= "(< RA Lifetime)" class="form-control" value = "<?php echo($arr[0]); ?>"></td>
												</tr>
												<tr>
													<td>Advertise Preferred Lifetime (In Hours)</td>
													<?php $num = exec("uci get dhcp.".$selected_interface.".preferred_lifetime");
													$arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$num);?>
													<td><input type="text" id="adv_pref_lifetime_id" name="adv_pref_lifetime" placeholder= "(In Hours)" class="form-control" value = "<?php echo($arr[0]); ?>"></td>
												</tr>
												<tr>
													<td>Advertise DNS</td>
													<td>
														<label class="fancy-checkbox">
															<?php
															$ra_dns = exec("uci get dhcp.".$selected_interface.".ra_dns");
															if ($ra_dns == "1") {
																echo '<input name="ra_dns" id="ra_dns_id" checked=checked type="checkbox">';
															} else {
																echo '<input name="ra_dns" id="ra_dns_id" type="checkbox">';
															}
															?>
															<span></span>
														</label>
													</td>
												</tr>
												<tr>
													<td>Primary DNS</td>
													<?php
													if ($servers[0]) {
														echo "<td><input type='text' name='dns_servers[]' class='form-control' value='".$servers[0]."'></td>";
													} else {
														echo "<td><input type='text' name='dns_servers[]' class='form-control'></td>";
													}
													?>
												</tr>
												<tr>
													<td>Secondary DNS</td>
													<?php
													if ($servers[1]) {
														echo "<td><input type='text' name='dns_servers[]' class='form-control' value='".$servers[1]."'></td>";
													} else {
														echo "<td><input type='text' name='dns_servers[]' class='form-control'></td>";
													}
													?>
												</tr>

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
								<h3 class="panel-title">Current Servers</h3>
							</div>
							<div class="panel-body">
								<table class="table">
									<thead>
										<tr>
											<th>Interface</th>
											<th>Status</th>
											<th>Start IP</th>
											<th>End IP</th>
											<th>Protocol</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach (get_dhcp_server_interfaces() as $configured_interface) {
											echo "<tr>";
												echo "<td><kbd>".strtoupper($configured_interface)."</kbd></td>";
												echo "<td>".show_disabled_enabled(exec("uci get dhcp.".$configured_interface.".ignore"))."</td>";
												echo "<td>".strtoupper(exec("uci get dhcp.".$configured_interface.".start_ip"))."</td>";
												echo "<td>".strtoupper(exec("uci get dhcp.".$configured_interface.".end_ip"))."</td>";
												echo '<td><a href="server.php?action=edit&interface='.$configured_interface.'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a href="server.php?action=delete&interface='.$configured_interface.'"><i class="fa fa-times"></i></a></td>';
												echo "</tr>";
											}
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

</html>
