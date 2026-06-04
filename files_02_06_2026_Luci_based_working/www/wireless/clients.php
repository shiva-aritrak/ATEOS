<?php include '/www/lib/sessioncheck.php' ?>

<head>
	<title>Wireless | Clients</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/wirelesslib.php' ?>

<?php startblock('contentbar') ?>
<div class="main">
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">Wireless Connected Clients</h3>
							</div>
							<div class="panel-body">
								<table class="table">
									<thead>
										<tr>
											<th>#</th>
											<th>Connected Time</th>
											<th>MAC Address</th>
											<th>IP Address</th>
											<th>Device Name</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$counter = 1;
										foreach(get_connected_clients_wifi() as $client) {
											echo "<tr>";
											echo "<td>".($counter++)."</td>";
											echo "<td>".$client[0]."</td>";
											echo "<td>".$client[1]."</td>";
											echo "<td>".$client[2]."</td>";
											echo "<td>".$client[3]."</td>";
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
<?php endblock() ?>
