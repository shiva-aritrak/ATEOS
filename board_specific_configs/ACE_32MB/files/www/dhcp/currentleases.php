<?php include '/www/lib/sessioncheck.php' ?>
<!-- Set Title of Page -->
<head>
	<title>DHCP | Current Leases</title>
</head>

<!-- Load the side nav bar -->
<?php include '/www/sidenav.php' ?>

<?php startblock('contentbar') ?>

<!-- HTML code within the main section of the page -->

<div class="main">

	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">DHCP leases</h3>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>#</th>
										<th>Renewal Time</th>
										<th>Mac Address</th>
										<th>IP Address</th>
										<th>Machine Name</th>

									</tr>
								</thead>
								<tbody>
									<?php
									if ($file = fopen("/tmp/dhcp.leases", "r"))
									{
										$line_counter = 1;
										$line=array();
										while(!feof($file))
										{
											echo "<tr>";
											$line = (fgets($file));
											$elements = explode(" ", $line);
											if(sizeof($elements) > 3)
											{
												echo "<td>".$line_counter."</td>";
												for ($element_index = 0; $element_index <= 3; $element_index++)
												{
													if($element_index == 0)
													{
														$dt = new DateTime("@".$elements[$element_index] );
														echo "<td>".$dt->format('Y-m-d H:i:s')."</td>";
													}
													else
													{
														echo "<td>".$elements[$element_index]."</td>";
													}
												}
												$line_counter += 1;
											}
											echo "</tr>";
										}
										fclose($file);
										echo "</tbody>";

										echo "</table>";
									}
									?>

								</div>
							</div>
						</div>


						<br />
					</div>



				</div>
			</div>
		</div>

		<?php endblock() ?>
