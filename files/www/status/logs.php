<?php include '/www/lib/sessioncheck.php' ?>
<!-- Set Title of Page -->
<head>
	<title>Logs</title>
</head>

<!-- Load the side nav bar -->
<?php include '/www/sidenav.php' ?>
<?php startblock('contentbar') ?>
<div class="main">
	<div class="main-content">
		<div class="container-fluid">
			<h3 class="page-title">Logs</h3>
			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title"></h3>
							</div>
							<div class="panel-body">
								<?php
								$output = "";
								$to_run_cmd = "/sbin/logread -l 200 | grep -v uci | grep -v uhttpd | grep -v cron";
								exec($to_run_cmd, $output);
								echo '<pre>';
								foreach ($output as $value) {
									$filtered_value = str_replace("mwan3","bandaggr", $value);
									$filtered_value = str_replace("openvpn","sslvpn", $filtered_value);
									echo $filtered_value."\n";
								}
								echo '</pre>';
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endblock() ?>
