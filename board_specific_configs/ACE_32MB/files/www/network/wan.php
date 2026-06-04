<?php include '/www/lib/sessioncheck.php' ?>

<head>
	<title>Network | WAN/LAN</title>
</head>

<?php
include '/www/sidenav.php';
include '/www/lib/networks.php';
include '/www/lib/common.php';
?>

<?php
$error_message = "";

if(count($_GET) > 0) {
	$selected_wan = $_GET["interface"];
	$port_num = exec("uci get network.".$selected_wan.".port_num");
	$action = $_GET["action"];
	
	if ( $action == "delete" ) {
		clear_wan($selected_wan, $reload=true);
		$is_ipv6_configured = exec("uci get network.".$port_num.".ipv6_configured");
		$is_ipv4_configured = exec("uci get network.".$port_num.".ipv4_configured");
		if ($is_ipv6_configured == "0" && $is_ipv4_configured == "0" ) {
			clear_dev_device($port_num);
		}

		echo '<script>window.location.href = "wan.php";</script>';
		exit;
	}
}

if(count($_POST) > 0) {
	$port_name = $_POST["port_name"];
	$wan_mode = $_POST["wan_mode"];
	$vlan_id = $_POST["vlan_id"];
	$edit_interface = $_POST["edit_interface"];
	$edit_port_num = $_POST["edit_port_num"];
	$interface_type = $_POST["interface_type"];
	$interface_name = $_POST["interface_name"];
	$wan_desc = $_POST["wan_desc"];
	$fw_zone = $_POST["fw_zone"];
	$bridged = exec("uci get network.".$port_name.".bridged");
	$ipv4_configured = exec("uci get network.".$port_name.".ipv4_configured");
	
	if ($ipv4_configured == "1") {
		$current_interfaces = array();
		exec("uci show network| grep ".$port_name." | grep port_num | grep -v dev | awk -F'.' '{print $2}'", $current_interfaces);
		$current_vlans = array();
		foreach($current_interfaces as $current_interface) {
			$current_vlan = exec("uci get network.".$current_interface.".vlan_id");
			if ($edit_interface != "" && intval($current_vlan) == intval($vlan_id) ) {
				; // noop
			} else {
				array_push($current_vlans , intval($current_vlan));
			}
		}
	}

	if ($wan_mode != "static" && $interface_type == "lan") {
		$error_message = "Mode ".strtoupper($wan_mode)." cannot be set for ".strtoupper($interface_type)." Interface";
	} else if ($edit_port_num != "" && $port_name != "" && $edit_port_num != $port_name) {
		$error_message = "<b>".strtoupper($edit_port_num)."</b> cannot be changed to <b>".strtoupper($port_name)."</b> using edit";
	} else if ($bridged == "1") {
		$error_message = "<b>".strtoupper($port_name)."</b> part of existing bridge, Cannot use as a seperated interface";
	} else if ($ipv4_configured == "1" && (in_array(intval($vlan_id) , $current_vlans)) ) {
		$error_message = "<b>".strtoupper($port_name)."</b> already configured with same VLAN ID";
	}
	else {
		$wan_ifname = exec("uci get network.".$port_name.".ifname");

		$interface_name = $interface_type . str_replace('port', '', $port_name);
		if ($vlan_id) {
			$interface_name = $interface_name."_".$vlan_id;
		}

		if($wan_mode == "dhcp") {
			$metric = $_POST["dhcp_wan_metric"];
			$mtu = $_POST["dhcp_wan_mtu"];
			$dhcp_wan_peer_dns = $_POST["dhcp_wan_peer_dns"];
			set_wan_dhcp($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $metric, $mtu, $dhcp_wan_peer_dns, $wan_desc, $fw_zone);
		} else if ($wan_mode == "static") {
			$ip_addr = $_POST["wan_ip_address"];
			$netmask = $_POST["wan_subnet_mask"];
			$gateway = $_POST["wan_gateway"];
			$dns1 = $_POST["wan_dns1"];
			$dns2 = $_POST["wan_dns2"];
			$metric = $_POST["wan_static_metric"];
			$mtu = $_POST["wan_static_mtu"];
			$port_type = $_POST["wan_static_port_type"];
			$default_route = $_POST["wan_static_default_route"];
			set_wan_static($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $ip_addr, $netmask, $gateway, $dns1, $dns2, $default_route, $metric, $mtu, $port_type, $wan_desc, $fw_zone);
		} else if ($wan_mode == "pppoe") {
			if (trim($_POST["pppoe_username"]) == "" || trim($_POST["pppoe_password"]) == "") {
				$error = "PPPoE username and password cannot be blank";
			} else {
				$metric = $_POST["pppoe_metric"];
				$mtu = $_POST["pppoe_wan_mtu"];
				$service_name = $_POST["pppoe_service_name"];
				$username = $_POST["pppoe_username"];
				$password = $_POST["pppoe_password"];
				$pppoe_peer_dns = $_POST["pppoe_peer_dns"];
				set_wan_pppoe($edit_interface, $edit_port_num, $interface_type, $wan_ifname, $port_name, $vlan_id, $interface_name, $service_name, $username, $password, $metric, $mtu, $pppoe_peer_dns, $wan_desc, $fw_zone);	
			}
		} else if($wan_mode == "disable"){
			$is_ipv6_configured = exec("uci get network.".$port_num.".ipv6_configured");
			$is_ipv4_configured = exec("uci get network.".$port_num.".ipv4_configured");
			if ($is_ipv6_configured == "0" && $is_ipv4_configured == "0" ) {
				clear_dev_device($port_num);
			}
			clear_wan($interface_name, $reload=true);
		}
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
									if($selected_wan) {
										echo "Edit Interface ".strtoupper($selected_wan);
									} else {
										echo "Interface Configuration";
									}
									?>
								</h3>
							</div>
							<div class="panel-body">
								<?php 
								if ($error_message) {
									echo '<div class="alert alert-danger alert-dismissible" role="alert">';
									echo '<i class="fa fa-warning"></i> Alert! '.$error_message;
									echo '</div>';
								}
								?>
								<form autocomplete="off" action="wan.php" method="post">
									<input type="hidden" id="edit_interface_id" name="edit_interface" value="<?php if (count($_GET) > 0) { echo $selected_wan; } else { echo ""; } ?>">
									<input type="hidden" id="edit_port_num_id" name="edit_port_num" value="<?php if (count($_GET) > 0) { echo $port_num; } else { echo ""; } ?>">

									<table id="main_wan">
                                        <tr>
											<td>Port</td>
											<td>
												<select required name="port_name" id="id_port_name" onchange="set_interface_name()" class="form-control input-sm">
													<?php
													foreach (get_network_devices() as $intf) {
														if(strtolower($port_num) == strtolower($intf)) {
															echo '<option onclick="set_interface_name()" selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
														} else {
															echo '<option onclick="set_interface_name()" value="'.$intf.'">'.strtoupper($intf).'</option>';
														}
													}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td>Description</td>
											<td><input type="text" class="form-control" name="wan_desc" id="wan_desc_id" value="<?php echo exec("uci get network.".$selected_wan.".desc");?>" ></td>
										</tr>
                                        <tr>
                                            <td>Interface Type</td>
											<td>
												<label class="fancy-radio">
													<input name="interface_type" value="wan" type="radio" <?php if (strpos($selected_wan, 'wan') === 0) { echo 'checked="checked"'; } ?> id="id_interface_type_wan" onclick="set_interface_name()">
													<span><i></i>WAN</span>
												</label>
												<label class="fancy-radio">
													<input name="interface_type" value="lan" type="radio" <?php if (strpos($selected_wan, 'lan') === 0) { echo 'checked="checked"'; } ?> id="id_interface_type_lan" onclick="set_interface_name()">
													<span><i></i>LAN</span>
												</label>
											</td>
										</tr>
                                        <tr>
											<td>VLAN ID</td>
											<td><input type="number" onkeydown="set_interface_name()" onkeyup="set_interface_name()" name="vlan_id" min="1" max="4094" id="id_vlan_id" class="form-control" value="<?php echo exec("uci get network.".$selected_wan.".vlan_id"); ; ?>" ></td>
										</tr>
                                        <tr>
											<td>Interface Name</td>
											<td><input type="text" class="form-control" id="id_interface_name" name="interface_name" value="<?php echo strtoupper($selected_wan); ?>" readonly></td>
										</tr>
										<tr>
											<td>Firewall Zone</td>
											<td>
												<select required name="fw_zone" class="form-control input-sm">
												<?php
													$fw_zones = exec("uci get network.".$selected_wan.".fw_zone");
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
                                        	<td>Interface Mode</td>
											<td>
												<label class="fancy-radio">
													<input name="wan_mode" value="static" type="radio" id="static" onclick="change_wan_mode(this.id)">
													<span><i></i>Static</span>
												</label>
												<label class="fancy-radio">
													<input name="wan_mode" value="dhcp" type="radio" id="dhcp" onclick="change_wan_mode(this.id)">
													<span><i></i>DHCP</span>
												</label>
												<label class="fancy-radio">
													<input name="wan_mode" value="pppoe" type="radio" id="pppoe" onclick="change_wan_mode(this.id)">
													<span><i></i>PPPoE</span>
												</label>
												<label class="fancy-radio">
													<input name="wan_mode" value="disable" type="radio" id="disabled" onclick="change_wan_mode(this.id)">
													<span><i></i>Disable</span>
												</label>
											</td>
										</tr>
                                       									
									</table>
									
									<table id="static_wan">
										<tr>
											<td>IP Address</td>
											<td><input type="text" pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-4])$" title="Enter a IP Address e.g. 192.168.1.5" class="form-control" id="wan_static_ip" name="wan_ip_address" value="<?php echo exec("uci get network.".$selected_wan.".ipaddr"); ?>" ></td>
										</tr>
										<tr>
											<td>Subnet Mask</td>
											<td><input type="text" pattern="^(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$" title="Enter a valid subnet mask 255.255.255.0" class="form-control" id="wan_static_netmask" name="wan_subnet_mask" value="<?php echo exec("uci get network.".$selected_wan.".netmask"); ?>"></td>
										</tr>
										<tr>
											<td>Gateway</td>
											<td><input type="text" pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-4])$" title="Enter a IP Address e.g. 192.168.1.5" class="form-control" id="wan_static_gateway" name="wan_gateway" value="<?php echo exec("uci get network.".$selected_wan.".gateway"); ?>" ></td>
										</tr>
										<tr>
											<td>DNS 1</td>
											<td><input type="text" pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-4])$" title="Enter a valid IPv4 Address e.g. 8.8.8.8" class="form-control" id="wan_static_dns1" name="wan_dns1" value="<?php $get=exec("uci get network.".$selected_wan.".dns"); echo explode(" ",$get)[0];?>" ></td>
										</tr>
										<tr>
											<td>DNS 2</td>
											<td><input type="text" pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-4])$" title="Enter a valid IPv4 Address e.g. 1.1.1.1" class="form-control" id="wan_static_dns2" name="wan_dns2" value="<?php $get=exec("uci get network.".$selected_wan.".dns"); echo explode(" ",$get)[1];?>" ></td>
										</tr>
										<tr>
											<td>Metric</td>
											<td><input type="text" pattern="^[0-9]*$" title="Metric should be a number" class="form-control" name="wan_static_metric" id="metric_id" value="<?php echo exec("uci get network.".$selected_wan.".metric"); ; ?>" ></td>
										</tr>
										<tr>
                                        	<td>Make Port as Bridge</td>
											<td>
												<label class="fancy-checkbox">
													<?php
													$port_type = exec("uci get network.".$selected_wan.".type");
													if ($port_type == "bridge") {
														echo '<input name="wan_static_port_type" id="id_wan_static_port_type" checked=checked type="checkbox">';
													} else {
														echo '<input name="wan_static_port_type" id="id_wan_static_port_type" type="checkbox">';
													}
													?>
													<span></span>
												</label>
											</td>
										</tr>
                                       			
										<tr>
											<td>MTU</td>
											<td><input type="number" min="1200" max="9000" title="MTU should be a number between 1200 and 9000, Default is 1500" class="form-control" name="wan_static_mtu" id="wan_static_mtu_id" value="<?php echo exec("uci get network.".$selected_wan.".mtu"); ; ?>" ></td>
										</tr>
										<tr>
											<td>Default Route</td>
											<td>
												<label class="fancy-checkbox">
													<?php
													$default_route = exec("uci get network.".$selected_wan.".defaultroute");
													if ($default_route == "0") {
														echo '<input name="wan_static_default_route" id="id_wan_static_default_route" type="checkbox">';
													} else {
														echo '<input name="wan_static_default_route" id="id_wan_static_default_route" checked=checked type="checkbox">';
													}
													?>
													<span></span>
												</label>
											</td>
										</tr>
									</table>
									
									<table id="dhcp_wan">
										<tr>
											<td>Use DNS</td>
											<td>
												<label class="fancy-checkbox">
													<?php
													$peerdns = exec("uci get network.".$selected_wan.".peerdns");
													if ($peerdns == "1") {
														echo '<input name="dhcp_wan_peer_dns" id="id_dhcp_wan_peer_dns" checked=checked type="checkbox">';
													} else {
														echo '<input name="dhcp_wan_peer_dns" id="id_dhcp_wan_peer_dns" type="checkbox">';
													}
													?>
													<span></span>
												</label>
											</td>
										</tr>
										<tr>
											<td>Metric</td>
											<td><input type="text" pattern="^[0-9]*$" title="Metric should be a number" name="dhcp_wan_metric" id="metric_id" class="form-control" value="<?php echo exec("uci get network.".$selected_wan.".metric"); ?>" ></td>
										</tr>
										<tr>
											<td>MTU</td>
											<td><input type="number" min="1200" max="9000" title="MTU should be a number between 1200 and 9000, Default is 1500" class="form-control" name="dhcp_wan_mtu" id="dhcp_wan_mtu_id" value="<?php echo exec("uci get network.".$selected_wan.".mtu"); ; ?>" ></td>
										</tr>
									</table>
									
									<table id="pppoe_wan">
										<tr>
											<td>Service Name</td>
											<td><input type="text" name="pppoe_service_name" id="pppoe_wan_service_name" class="form-control" value="<?php echo exec("uci get network.".$selected_wan.".service"); ?>"></td>
										</tr>
										<tr>
											<td>Username</td>
											<td><input type="text" name="pppoe_username" id="pppoe_wan_pppoe_username" class="form-control" value="<?php echo exec("uci get network.".$selected_wan.".username"); ?>"></td>
										</tr>
										<tr>
											<td>Password</td>
											<td><input type="password" name="pppoe_password" id="pppoe_wan_pppoe_password" class="form-control" value="<?php echo exec("uci get network.".$selected_wan.".password"); ?>"></td>
											<td><a href="#" id="id_network_hidepass" onclick="toggle_show_pass(this.id)"><i class="fa fa-eye-slash"></i></a></td>
										</tr>
										<tr>
											<td>Use DNS</td>
											<td>
												<label class="fancy-checkbox">
													<?php
													$peerdns = exec("uci get network.".$selected_wan.".peerdns");
													if ($peerdns == "1") {
														echo '<input name="pppoe_peer_dns" id="id_pppoe_peer_dns" checked=checked type="checkbox">';
													} else {
														echo '<input name="pppoe_peer_dns" id="id_pppoe_peer_dns" type="checkbox">';
													}
													?>
													<span></span>
												</label>
											</td>
										</tr>
										<tr>
											<td>Metric</td>
											<td><input type="text" pattern="^[0-9]*$" title="Metric should be a number" name="pppoe_metric" id="pppoe_wan_metric" class="form-control" value="<?php echo exec("uci get network.".$selected_wan.".metric"); ?>" ></td>
										</tr>
										<tr>
											<td>MTU</td>
											<td><input type="number" min="1200" max="9000" title="MTU should be a number between 1200 and 9000, Default is 1500" class="form-control" name="pppoe_wan_mtu" id="pppoe_wan_mtu_id" value="<?php echo exec("uci get network.".$selected_wan.".mtu"); ; ?>" ></td>
										</tr>
										
									</table>
									
									<table id="disabled_wan">
									</div>
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
			
			<div class="col-md-8">
				<div class="panel">
					<div class="panel-body">
						<div class="panel-heading">
							<h3 class="panel-title">Current Interface Configuration</h3>
						</div>
						<div class="panel-body">
							<table class="table">
								<thead>
									<tr>
										<th>Interface</th>
										<th>Port</th>
										<th>Status</th>
										<th>Protocol</th>
										<th>IP/Username</th>
										<th>Metric</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach (get_configured_interfaces() as $configured_interface) {
											if(!strpos($configured_interface, "br0")) {
												$ipv6_status = exec("uci get network.".$configured_interface.".ipv6");
												if ($ipv6_status == "0") {
													echo "<tr>";
													echo "<td><kbd>".strtoupper($configured_interface)."</kbd></td>";
													echo "<td>".strtoupper(exec("uci get network.".$configured_interface.".port_num"))."</td>";
													echo "<td>".show_enabled_disabled(exec("uci get network.".$configured_interface.".enabled"))."</td>";
													echo "<td>".strtoupper(exec("uci get network.".$configured_interface.".proto"))."</td>";
													echo "<td><samp>".exec("uci get network.".$configured_interface.".ipaddr").exec("uci get network.".$configured_interface.".username")."</samp></td>";
													echo "<td>".exec("uci get network.".$configured_interface.".metric")."</td>";
													echo '<td><a href="wan.php?interface='.$configured_interface.'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a href="wan.php?action=delete&interface='.$configured_interface.'"><i class="fa fa-times"></i></a></td>';
													echo "</tr>";
		
												}
											}
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
<?php endblock() ?>

<?php startblock('scriptblock') ?>
<script>
    function set_interface_name() {
        interface_name_input = document.getElementById('id_interface_name');
        interface_type_wan = document.getElementById('id_interface_type_wan');
        interface_type_lan = document.getElementById('id_interface_type_lan');
        if (interface_type_wan.checked) {
            interface_name_input.value = "WAN";
			static_mode_status = document.getElementById('static').checked;
			if (static_mode_status) {
				document.getElementById("wan_static_dns1").setAttribute('required','required');
				document.getElementById("wan_static_gateway").setAttribute('required','required');
			}
		}
        else if (interface_type_lan.checked) {
            interface_name_input.value = "LAN";
			document.getElementById("wan_static_dns1").removeAttribute('required');
			document.getElementById("wan_static_gateway").removeAttribute('required');
        } else {
            return;
        }
        interface_name_input.value += document.getElementById('id_port_name').value.replace('port', '');

        vlan_id = document.getElementById('id_vlan_id');
        if (vlan_id.value != "") {
            interface_name_input.value += "_" + vlan_id.value;
        }
    }

	function change_wan_mode(id) {
		document.getElementById('static_wan').style.display = "none";
		document.getElementById('pppoe_wan').style.display = "none";
		document.getElementById('dhcp_wan').style.display = "none";
		document.getElementById('disabled_wan').style.display = "none";
		
		document.getElementById(id).checked=true;
		document.getElementById(id + '_wan').style.display = "block";

		document.getElementById("wan_static_ip").required = false;
		document.getElementById("wan_static_netmask").required = false;
		document.getElementById("wan_static_gateway").required = false;
		document.getElementById("wan_static_dns1").required = false;
		document.getElementById("pppoe_wan_pppoe_username").required = false;
		document.getElementById("pppoe_wan_pppoe_password").required = false;

		if(id == "static") {
			interface_type_wan = document.getElementById('id_interface_type_wan');
			interface_type_lan = document.getElementById('id_interface_type_lan');
			if (interface_type_wan.checked) {
				document.getElementById("wan_static_gateway").setAttribute('required','required');
			}
			else if (interface_type_lan.checked) {
				document.getElementById("wan_static_gateway").removeAttribute('required');
			} else {
				return;
			}
			document.getElementById("wan_static_ip").required = true;
			document.getElementById("wan_static_netmask").required = true;
		} else if (id == "pppoe") {
			document.getElementById("pppoe_wan_pppoe_username").required = true;
			document.getElementById("pppoe_wan_pppoe_password").required = true;
		}
	}
	
	window.onload=preselected;
	
	function preselected() {
		<?php
		if($selected_wan) {
			echo "var selected_wan_mode='".exec("uci get network.".$selected_wan.".proto")."';";
		} else {
			echo "var selected_wan_mode='none';";
		}
		?>
		if(selected_wan_mode == "static") {
			change_wan_mode("static");
		} else if (selected_wan_mode == "dhcp") {
			change_wan_mode("dhcp");
		} else if (selected_wan_mode == "pppoe") {
			change_wan_mode("pppoe");
		} else {
			change_wan_mode("disabled");
		}

        
	}

	function toggle_show_pass(show_pass_clicked_id)
	{
		var x = document.getElementById(show_pass_clicked_id);
		if (x.innerHTML ===  '<i class="fa fa-eye-slash"></i>') {
			x.innerHTML = '<i class="fa fa-eye"></i>';
			document.getElementById('pppoe_wan_pppoe_password').type = "text";
		} else {
			x.innerHTML = '<i class="fa fa-eye-slash"></i>';
			document.getElementById('pppoe_wan_pppoe_password').type = "password";
		}
	}
</script>
<?php endblock() ?>
