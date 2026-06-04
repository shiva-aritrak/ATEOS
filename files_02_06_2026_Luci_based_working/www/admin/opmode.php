<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Administration | Operation Mode</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
if (count($_POST) > 0 ) {
	$tcp_timeout_established = $_POST["tcp_timeout_established"];
	$udp_timeout = $_POST["udp_timeout"];
	$tcp_keepalive = $_POST["tcp_keepalive"];
	$conntrack_max = $_POST["conntrack_max"];

	$sysctl_file = fopen("/etc/sysctl.conf", "w") or die("Error");
	fwrite($sysctl_file, "# Custom\n\n" );
	fwrite($sysctl_file, "net.netfilter.nf_conntrack_tcp_timeout_established=".$tcp_timeout_established."\n" );
	fwrite($sysctl_file, "net.netfilter.nf_conntrack_udp_timeout=".$udp_timeout."\n" );
	fwrite($sysctl_file, "net.ipv4.tcp_keepalive_time=".$tcp_keepalive."\n" );
	fwrite($sysctl_file, "net.netfilter.nf_conntrack_max=".$conntrack_max."\n" );
	fflush($sysctl_file);
	fclose($sysctl_file);
	exec("/sbin/sysctl -p");
}

$default_values = array();
$handle = fopen("/etc/sysctl.conf", "r");
if ($handle) {
	while (($line = fgets($handle)) !== false) {
		if (trim($line) == "" || substr($line, 0, strlen('#')) === '#') {
			continue;
		} else {
			$res = explode("=",$line);
			$default_values[$res[0]] = trim($res[1]);
		}
	}
	fclose($handle);
}

if (array_key_exists("net.netfilter.nf_conntrack_max", $default_values) ) { $conntrack_max = $default_values["net.netfilter.nf_conntrack_max"]; } else { $conntrack_max = "16384"; }
if (array_key_exists("net.netfilter.nf_conntrack_tcp_timeout_established", $default_values) ) { $tcp_timeout_established = $default_values["net.netfilter.nf_conntrack_tcp_timeout_established"]; } else { $tcp_timeout_established = "7440"; }
if (array_key_exists("net.netfilter.nf_conntrack_udp_timeout", $default_values) ) { $udp_timeout = $default_values["net.netfilter.nf_conntrack_udp_timeout"]; } else { $udp_timeout = "60"; }
if (array_key_exists("net.ipv4.tcp_keepalive_time", $default_values) ) { $tcp_keepalive = $default_values["net.ipv4.tcp_keepalive_time"]; } else { $tcp_keepalive = "120"; }

?>


<?php startblock('contentbar') ?>
<div class="main">
	<!-- MAIN CONTENT -->
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-body">
								<div id="content1" class="content">
									<form id="form" action="opmode.php" method="post">
										<div>
											<table>
												<tr>
													<td>TCP Established Timeout (Sec)</td>
													<td>
														<input required type="number" min="5000" max="9660" name="tcp_timeout_established" value="<?php echo $tcp_timeout_established; ?>" class="form-control">
													</td>
												</tr>
												<tr>
													<td>UDP Timeout (Sec)</td>
													<td>
														<input required type="number" min="30" max="120" value="<?php echo $udp_timeout; ?>" name="udp_timeout" class="form-control" >
													</td>
												</tr>
												<tr>
													<td>TCP Keepalive Timeout (Sec)</td>
													<td>
														<input required type="number" min="60" max="7200" value="<?php echo $tcp_keepalive; ?>" name="tcp_keepalive" class="form-control" >
													</td>
												</tr>
												<tr>
													<td>NAT Table Connection Limit</td>
													<td>
														<input required type="number" min="10240" max="500000" value="<?php echo $conntrack_max; ?>" name="conntrack_max" class="form-control" >
													</td>
												</tr>
											</table>
											<br/>
											<div class="row">
												<div class="col-md-6">
													<button type="submit" class="btn btn-primary">Submit</button>
												</div>
												<div class="col-md-6">
													<button type="reset" class="btn btn-danger">Reset</button>
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
