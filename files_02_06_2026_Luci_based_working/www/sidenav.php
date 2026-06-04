<?php include 'navbar.php' ?>

<?php startblock('sidebar') ?>


<style>

.slimScrollBar{

	overflow-y: visible !important;
	opacity: 1 !important;
	display : block !important;
	width: 10px !important;
	background: rgb(68,175,149) !important;
}
.sidebar .nav>li>a {
    padding: 10px 15px;
}
</style>

<div id="sidebar-nav" class="sidebar">
	<div class="sidebar-scroll">
		<nav>
			<ul class="nav">
				<li><a href="/dashboard.php" class="active"><i class="fa fa-home"></i> <span>Dashboard</span></a></li>
				<li><a href="/admin/connect.php"><i class="fa fa-external-link"></i> <span>AnexConnect</span></a></li>
				<li>
					<a href="#networkMenu" data-toggle="collapse" class="collapsed"><i class="fa fa-sitemap"></i> <span>Network</span> <i class="icon-submenu fa fa-chevron-down"></i></a>
					<div id="networkMenu" class="collapse">
						<ul class="nav">
							<li><a href="/network/wan.php" class="">WAN/LAN</a></li>
							<li><a href="/network/wan6.php" class="">IPv6 WAN/LAN</a></li>
							<li><a href="/network/3g.php" class="">3G/4G</a></li>
							<li><a href="/network/sim.php" class="">SIM</a></li>
							<li><a href="/network/xlat.php" class="">XLAT (CLAT)</a></li>
							<li><a href="/network/bridge.php" class="">LAN Bridge</a></li>
							<li><a href="/network/loopback.php" class="">Loopback</a></li>
							<li><a href="/network/simswitch.php" class="">SIM Switch</a></li>
							<li><a href="/network/monitor.php" class="">Monitor</a></li>
							<li><a href="/network/quotamonitor.php" class="">Quota</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#wirelessMenu" data-toggle="collapse" class="collapsed"><i class="fa fa-wifi"></i> <span>Wireless</span> <i class="icon-submenu fa fa-chevron-down"></i></a>
					<div id="wirelessMenu" class="collapse">
						<ul class="nav">
							<li><a href="/wireless/wlan.php" class="">Settings</a></li>
							<li><a href="/wireless/clients.php" class="">Connected Clients</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#dhcpMenu" data-toggle="collapse" class="collapsed"><i class="fa fa-envelope"></i> <span>DHCP & DNS</span> <i class="icon-submenu fa fa-chevron-down"></i></a>
					<div id="dhcpMenu" class="collapse">
						<ul class="nav">
							<li><a href="/dhcp/server.php" class="">Server</a></li>
							<li><a href="/dhcp/staticlease.php" class="">Static Leases</a></li>
							<li><a href="/dhcp/currentleases.php" class="">Current Leases</a></li>
							<li><a href="/dhcp/dns.php" class="">DNS</a></li>
							<li><a href="/dhcp/splitdns.php" class="">DNS Split Horizon</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#routingMenu" data-toggle="collapse" class="collapsed">
						<i class="fa fa-arrows-alt"></i> <span>Routing</span>
						<i class="icon-submenu fa fa-chevron-down"></i>
					</a>
					<div id="routingMenu" class="collapse">
						<ul class="nav">
							<li><a href="/routing/static.php" class="">Static</a></li>
							<li><a href="/routing/static6.php" class="">Static6</a></li>
							<li><a href="/routing/aggregation.php" class="">SD-WAN</a></li>
							<li><a href="/routing/global_routing.php" class="">Global Route Params</a></li>
							<li><a href="/routing/bgp4.php" class="">BGP IPv4</a></li>
							<li><a href="/routing/bgp6.php" class="">BGP IPv6</a></li>
							<li><a href="/routing/ospfv2.php" class="">OSPF IPv4</a></li>
							<li><a href="/routing/ospfv3.php" class="">OSPF IPv6</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#firewallMenu" data-toggle="collapse" class="collapsed">
						<i class="fa fa-fire"></i> <span>Firewall</span>
						<i class="icon-submenu fa fa-chevron-down"></i>
					</a>
					<div id="firewallMenu" class="collapse">
						<ul class="nav">
							<li><a href="/firewall/rules.php" class="">Rules</a></li>
							<li><a href="/firewall/port_forwarding.php" class="">Port Forwarding</a></li>
							<li><a href="/firewall/snat.php" class="">Source NAT (SNAT)</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#vpnMenu" data-toggle="collapse" class="collapsed">
						<i class="fa fa-connectdevelop"></i> <span>VPN</span>
						<i class="icon-submenu fa fa-chevron-down"></i>
					</a>
					<div id="vpnMenu" class="collapse">
						<ul class="nav">
							<li><a href="/vpn/ssl.php" class="">SSL VPN Client</a></li>
							<li><a href="/vpn/ipsec.php" class="">IPSec</a></li>
							<li><a href="/vpn/gre.php" class="">GRE</a></li>
							<li><a href="/vpn/eogre.php" class="">EoGRE</a></li>
							<li><a href="/vpn/openvpn.php" class="">OpenVPN</a></li>
							<li><a href="/vpn/pptp.php" class="">PPTP</a></li>
							<li><a href="/vpn/l2tp.php" class="">L2TP</a></li>
							<li><a href="/vpn/sstp.php" class="">SSTP</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#adminMenu" data-toggle="collapse" class="collapsed"><i class="fa fa-user-circle"></i> <span>Administration</span> <i class="icon-submenu fa fa-chevron-down"></i></a>
					<div id="adminMenu" class="collapse">
						<ul class="nav">
							<li><a href="/admin/system.php" class="">System Management</a></li>
							<li><a href="/admin/services.php" class="">Services</a></li>
							<li><a href="/admin/lldp.php" class="">LLDP</a></li>
							<li><a href="/admin/config/manage.php" class="">Configuration Management</a></li>
							<li><a href="/admin/software/upgrade.php" class="">Software Upgrade</a></li>
							<li><a href="/admin/upgrade/upgrade.php" class="">Upgrade Firmware</a></li>
							<li><a href="/admin/license.php" class="">Warranty and License</a></li>
							<li><a href="/admin/opmode.php" class="">Operation Mode</a></li>
						</ul>
					</div>
				</li>
				<li>
					<a href="#statusMenu" data-toggle="collapse" class="collapsed">
						<i class="fa fa-info-circle"></i> <span>Status</span>
						<i class="icon-submenu fa fa-chevron-down"></i>
					</a>
					<div id="statusMenu" class="collapse">
						<ul class="nav">
							<li><a href="/status/bandwidth.php" class="">Bandwidth Graph</a></li>
							<li><a href="/status/command.php" class="">System Command</a></li>
							<li><a href="/status/logs.php" class="">Logs</a></li>
							<li><a href="/status/support.php" class="">Support</a></li>
						</ul>
					</div>
				</li>
				<li><a href="/logout.php"><i class="fa fa-sign-out"></i> <span>Logout</span></a></li>
				<li><a id="reboot_button" href="#"><i class="fa fa-power-off"></i> <span>Reboot</span></a></li>
			</ul>
		</nav>
	</div>
</div>




<?php endblock() ?>



<?php startblock('scriptblock') ?>
<script>
$('#reboot_button').on('click', function(e) {
	$('#reboot_confirm').modal({
		backdrop: 'static',
		keyboard: false
	})
	.one('click', '#delete', function(e) {
		window.location = '/reboot.php';
	});
});
</script>
<?php endblock() ?>
