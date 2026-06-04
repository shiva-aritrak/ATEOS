<?php

$login = exec('uci get anexgate.authentication.username');
$password = exec('uci get anexgate.authentication.password');

$error = false;
if (isset($_POST['username']) && isset($_POST['password'])) { //when form submitted
    if ($_POST['username'] === $login && $_POST['password'] === $password) {
        session_start();
        $_SESSION['username'] = $_POST['username']; //write login to server storage
    } else {
        echo '<script>window.location.href = "/login.php?error=1";</script>';
        exit;
    }
}

?>

<?php include '/www/lib/sessioncheck.php' ?>

<?php
include '/www/sidenav.php';
include '/www/lib/infostatics.php';
include '/www/lib/common.php';

$nofclients = no_of_clients();
$nofvpnclients = no_of_vpn_clients();
$total_bytes = total_transfer_counters();
$recvd = (int)$total_bytes[1];
$recvd = $recvd/(1024*1024);
$transmit = (int)$total_bytes[2];
$transmit = $transmit/(1024*1024);

?>

<?php startblock('contentbar') ?>

<div class="main">
	<div class="main-content">
		<div class="container-fluid">
			<div class="panel panel-headline">
				<div class="panel-heading">
					<h3 class="panel-title">Dashboard</h3>
					<p class="panel-subtitle">Snapshot of your network</p>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-3">
							<div class="metric">
								<span class="icon"><i class="fa fa-desktop"></i></span>
								<p>
									<span class="number"><?php echo $nofclients; ?></span>
									<span class="title">Hosts</span>
								</p>
							</div>
						</div>
						<div class="col-md-3">
							<div class="metric">
								<span class="icon"><i class="fa fa-random"></i></span>
								<p>
									<span class="number"><?php echo no_tcp_con() ?></span>
									<span class="title">TCP Connections</span>
								</p>
							</div>
						</div>
						<div class="col-md-3">
							<div class="metric">
								<span class="icon"><i class="fa fa-upload"></i></span>
								<p>
									<span class="number"><?php echo (int)$transmit; ?> MB</span>
									<span class="title">Upload</span>
								</p>
							</div>
						</div>
						<div class="col-md-3">
							<div class="metric">
								<span class="icon"><i class="fa fa-download"></i></span>
								<p>
									<span class="number"><?php echo (int)$recvd; ?> MB</span>
									<span class="title">Download</span>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">System Load</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body">
							<div id="system-load" class="easy-pie-chart" data-percent="<?php echo cpu_usage()?>">
								<span class="percent" ><?php echo cpu_usage()?></span>
							</div>
							<ul class="list-unstyled list-justify">
								<li>Current: <span id="high_load"><?php echo (cpu_info("1")*100)."%";?></span></li>
								<li>10 Min: <span id="avg_load"><?php echo (cpu_info("2")*100)."%";?></span></li>
								<li>15 Min: <span id="low_load"><?php echo (cpu_info("0")*100)."%";?></span></li>
								<li>Processes: <span id="processes"><?php echo no_of_ps()?> </span></li>
								<li>Uptime: <span id="uptime"><?php echo boot_uptime()?> </span></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">Resources</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body">
							<ul class="list-unstyled task-list">
								<li>
									<p>Free Memory <span class="label-percent" id="free_ram"><?php echo ramstatus("1")."%"; ?></span></p>
									<div class="progress progress-xs">
										<div class="progress-bar" role="progressbar" name="progressbar" aria-valuenow="<?php echo ramstatus("1")."%"; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo ramstatus("1")."%"; ?>">
											<span class="sr-only"><?php echo ramstatus("1")."%"; ?> Complete</span>
										</div>
									</div>
								</li>
								<li>
									<p>Available Memory <span class="label-percent" id="ava_ram"><?php echo ramstatus("2")."%"; ?></span></p>
									<div class="progress progress-xs">
										<div class="progress-bar" role="progressbar" name="progressbar" aria-valuenow="<?php echo ramstatus("2")."%"; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo ramstatus("2")."%"; ?>">
											<span class="sr-only"><?php echo ramstatus("2")."%"; ?> Complete</span>
										</div>
									</div>
								</li>
								<li>
									<p>VPN Clients Configured <span class="label-percent"><?php echo $nofvpnclients.'/'.$MAX_VPN_CLIENTS; ?></span></p>
									<div class="progress progress-xs">
										<div class="progress-bar" role="progressbar" name="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo (int)(100*$nofvpnclients/$MAX_VPN_CLIENTS); ?>%">
											<span class="sr-only"><?php echo (int)(100*$nofvpnclients/$MAX_VPN_CLIENTS); ?></span>
										</div>
									</div>
								</li>
								<li>
									<p>Hosts Connected <span class="label-percent"><?php echo $nofclients.'/'.$MAX_LAN_CLIENTS; ?></span></p>
									<div class="progress progress-xs">
										<div class="progress-bar" role="progressbar" name="progressbar"  aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo (int)(100*$nofclients/$MAX_LAN_CLIENTS); ?>%">
											<span class="sr-only"><?php echo (int)(100*$nofclients/$MAX_LAN_CLIENTS); ?></span>
										</div>
									</div>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">Product Information</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body no-padding">
							<table class="table">
								<tbody>
									<tr>
										<td>License Key</td>
										<td>
											<?php
											echo exec("uci get anexgate.license.key");
											?>
										</td>
									</tr>

									<tr>
										<td>License Valid</td>
										<td>
											<?php
											echo ucwords(exec("uci get anexgate.license.valid"));
											?>
										</td>
									</tr>
									<tr>
										<td>Product</td>
										<td>
											<?php
											echo exec("uci get anexgate.license.product")." ".exec("uci get anexgate.license.model");
											?>
										</td>
									</tr>
									<tr>
										<td>Software Version</td>
										<td>
											<?php
											echo exec("uci get anexgate.software.version");
											?>
										</td>
									</tr>
									<tr>
										<td>Build Version</td>
										<td> <?php echo exec("uci get anexgate.software.build"); ?></td>
									</tr>
									<tr>
										<td>Serial No.</td>
										<td>
											<?php
											echo exec("uci get anexgate.license.serial");
											?>
										</td>
									</tr>
									<tr>
										<td>Customer Name</td>
										<td>
											<?php
											echo exec("uci get anexgate.customer.name");
											?>
										</td>
									</tr>
									<tr>
										<td>Registered Date</td>
										<td>
											<?php
											echo exec("uci get anexgate.license.registered_date");
											?>
										</td>
									</tr>
									<tr>
										<td>Support Expiry Date</td>
										<td>
											<?php
											echo exec("uci get anexgate.license.support_expiry");
											?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="panel-footer">
							<div class="row">
								<div class="col-md-6 text-right"><a href="/admin/software/upgrade.php" class="btn btn-primary btn-sm">Software Update</a></div>
								<div class="col-md-6 text-right"><a href="/admin/license.php" class="btn btn-primary btn-sm">View License Details</a></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">WAN</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>Interface</th>
										<th>Port Status</th>
										<th>Link Status</th>
										<th>Protocol</th>
										<th>Uptime</th>
										<th>IP</th>
										<th>Route</th>
										<th>Next Hop</th>
										<th>DNS Servers</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$wan_interfaces = array('wan1', 'wan2');
									foreach ($wan_interfaces as $intf) {
										echo '<tr>';
										echo "<td>".strtoupper($intf)."</td>";

										$port_status = get_link_status($intf);

										$out = "";
										$ret = 1;
										exec("ifstatus ".$intf, $out, $ret);
										$json_out = implode("", $out);
										$json = json_decode($json_out, true);

										if ($port_status == "up") {
											echo '<td><span class="label label-success" id="'.$intf.'_status">Connected</span></td>';
										} else {
											echo '<td><span class="label label-danger" id="'.$intf.'_status">Not Connected</span></td>';
										}

										if ($port_status == "up") {
											echo "<td>".show_up_down($json['up'])."</td>";
											echo "<td>".strtoupper($json['proto'])."</td>";
											echo "<td>".get_time_from_seconds($json['uptime'])."</td>";
											if ($json['ipv4-address']) {
												echo "<td>".$json['ipv4-address'][0]['address']."/".$json['ipv4-address'][0]['mask']."</td>";
											} else {
												echo "<td></td>";
											}
											if ($json['route']) {
												echo "<td>".$json['route'][0]['target']."/".$json['route'][0]['mask']."</td>";
												echo "<td>".$json['route'][0]['nexthop']."</td>";
											} else {
												echo "<td colspan='2'></td>";
											}
											echo "<td>";
											if ($json['dns-server']) {
												foreach ($json['dns-server'] as $dns) {
													echo $dns."</br>";
												}
											}
											echo "</td>";
										} else {
											echo "<td colspan='7'></td>";
										}
										echo '</tr>';
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">LAN</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>Interface</th>
										<th>Port Status</th>
										<th>Link Status</th>
										<th>Protocol</th>
										<th>Uptime</th>
										<th>IP</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$lan_interfaces = array('lan1', 'lan2', 'lan3');
									foreach ($lan_interfaces as $intf) {
										echo '<tr>';
										echo "<td>".strtoupper($intf)."</td>";
										$port_status = get_link_status($intf);

										if ($port_status == "up") {
											echo '<td><span class="label label-success" id="'.$intf.'_status">Connected</span></td>';
										} else {
											echo '<td><span class="label label-danger" id="'.$intf.'_status">Not Connected</span></td>';
										}

										if ($intf == "lan1") {
											$out = "";
											$ret = 1;
											exec("ifstatus lan", $out, $ret);
											$json_out = implode("", $out);
											$json = json_decode($json_out, true);

											echo "<td rowspan='3'>".show_up_down($json['up'])."</td>";
											echo "<td rowspan='3'>".strtoupper($json['proto'])."</td>";
											echo "<td rowspan='3'>".get_time_from_seconds($json['uptime'])."</td>";
											if ($json['ipv4-address']) {
												echo "<td rowspan='3'>".$json['ipv4-address'][0]['address']."/".$json['ipv4-address'][0]['mask']."</td>";
											} else {
												echo "<td rowspan='3'></td>";
											}
										} else {
											echo "<td colspan='4'></td>";
										}
										echo '</tr>';
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">3G/4G</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>Interface</th>
										<th>Device</th>
										<th>Link Status</th>
										<th>Protocol</th>
										<th>Uptime</th>
										<th>IP</th>
										<th>Route</th>
										<th>Next Hop</th>
										<th>DNS Servers</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$wwan_interfaces = array('usb0', '3g');
									foreach ($wwan_interfaces as $intf) {
										echo '<tr>';
										echo "<td>".strtoupper($intf)."</td>";

										$interface_name = exec("uci get network.".$intf.".ifname");
										$out = "";
										$ret = 1;
										if ($interface_name == $intf) {
											exec("ifstatus ".$intf, $out, $ret);
										} else {
											exec("ifstatus ".$interface_name, $out, $ret);
										}
										$json_out = implode("", $out);
										$json = json_decode($json_out, true);

										$ifconfig_ret = 1;
										exec("ifconfig ".$intf, $out, $ifconfig_ret);
										if ($ifconfig_ret == 0) {
											echo '<td><span class="label label-success" id="'.$intf.'_status">Connected</span></td>';
										} else {
											echo '<td><span class="label label-danger" id="'.$intf.'_status">Disconnected</span></td>';
										}

										if ($json['up'] == "1") {
											echo "<td>".show_up_down($json['up'])."</td>";
											echo "<td>".strtoupper($json['proto'])."</td>";
											echo "<td>".get_time_from_seconds($json['uptime'])."</td>";
											if ($json['ipv4-address']) {
												echo "<td>".$json['ipv4-address'][0]['address']."/".$json['ipv4-address'][0]['mask']."</td>";
											} else {
												echo "<td></td>";
											}
											if ($json['route']) {
												echo "<td>".$json['route'][0]['target']."/".$json['route'][0]['mask']."</td>";
												echo "<td>".$json['route'][0]['nexthop']."</td>";
											} else {
												echo "<td colspan='2'></td>";
											}
											echo "<td>";
											if ($json['dns-server']) {
												foreach ($json['dns-server'] as $dns) {
													echo $dns."</br>";
												}
											}
										} else {
											echo "<td colspan='7'></td>";
										}
										echo "</td>";
										echo '</tr>';
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">VPN</h3>
							<div class="right">
								<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
								<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
							</div>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>Interface</th>
										<th>Status</th>
										<th>Link Status</th>
										<th>Protocol</th>
										<th>Uptime</th>
										<th>IP</th>
										<th>Route</th>
										<th>Next Hop</th>
									</tr>
								</thead>
								<tbody>
									<?php

									foreach (get_configured_interfaces() as $intf) {
										if (strpos($intf, "vpn") === 0) {
											echo '<tr>';
											echo "<td>".strtoupper($intf)."</td>";

											$out = "";
											$ret = 1;
											exec("ifstatus ".$intf, $out, $ret);
											$json_out = implode("", $out);
											$json = json_decode($json_out, true);

											if ($json['up'] == "1") {
												echo '<td><span class="label label-success" id="'.$intf.'_status">Connected</span></td>';
												echo "<td>".show_up_down($json['up'])."</td>";
												echo "<td>SSL</td>";
												echo "<td>".get_time_from_seconds($json['uptime'])."</td>";
												echo "<td>".get_tunnel_ip($intf)."</td>";

												$out = exec("uci get openvpn.client".substr($intf, -1).".route");
												if ($out) {
													echo "<td>".str_replace("'", "", str_replace(" ", "/", $out))."</td>";
												} else {
													echo "<td></td>";
												}
												echo "<td>".get_tunnel_gw_ip($intf)."</td>";
											} else {
												echo '<td><span class="label label-danger" id="'.$intf.'_status">Not Connected</span></td>';
												echo "<td colspan='5'></td>";
											}
											echo '</tr>';
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
<?php endblock() ?>

<script>
// real-time pie chart
var sysLoad = $('#system-load').easyPieChart({
	size: 130,
	barColor: function(percent) {
		return "#173448";
	},
	trackColor: '#99d3c0',
	scaleColor: "#173448",
	lineWidth: 10,
	lineCap: "square",
	animate: 800
});

var randomVal = "<?php echo cpu_usage()?>";

sysLoad.data('easyPieChart').update(randomVal);
sysLoad.find('.percent').text(randomVal);

</script>
