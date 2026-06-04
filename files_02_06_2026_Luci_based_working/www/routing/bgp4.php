<?php include '/www/lib/sessioncheck.php' ?>
<!-- Set Title of Page -->
<head>
	<title>BGP IPv4</title>
</head>

<!-- Load the side nav bar -->
<?php include '/www/sidenav.php' ?>

<!-- Load any Libraries if required/written -->
<?php
include '/www/lib/routing.php';
include '/www/lib/common.php';
?>

<?php

$bgp_route_no = 0;                                                                                                                                                                                                                            
$out = "";                                                                                                                                                                                                                                    
$ret = 99;                                                                                                                                                                                                                                    
$html_out = "";                                                                                                                                                                                                                               
$filled_bgp_route_no = 99;  

$bgp4_config = get_configured_bgp4();

if (!empty($bgp4_config)) {
    $counter = max(array_map(function($v) { return (int)preg_replace('/\D/', '', $v); }, $bgp4_config));
} else {
    $counter = 0;
}

if(count($_POST) > 0) {
	$bgp_route_no = $_POST["bgp_route_no"];
	$status = $_POST["status"];
	$local_id = $_POST["local_id"];
	$local_as = $_POST["local_as"];
	$remote_id = $_POST["remote_id"];
	$remote_as = $_POST["remote_as"];
	$v46 = $_POST["v46"];
	if (str_starts_with($bgp_route_no, "bgp")) {
		$bgp_route_no = str_replace("bgp", "", $bgp_route_no);
	} else {
		$bgp_route_no = $counter + 1;
	}
	configure_bgp_route($bgp_route_no, $v46, $status, $local_id, $local_as, $remote_id, $remote_as);
}

if(count($_GET) > 0) {
	$action = $_GET["action"];
	$filled_configured_bgp = $_GET["configured_bgp"];
	$bgp_route_no = str_replace("bgp", "", $filled_configured_bgp);
	$filled_bgp_route_no = $_GET["bgp_route_no"];
	if ( $action == "delete" ) {
		delete_bgp($filled_configured_bgp, $configured_bgp, 0);
		echo '<script>window.location.href = "bgp4.php";</script>';
		exit;
	}
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
								<h3 class="panel-title">BGP IPv4</h3>
							</div>
							<div class="panel-body">
								<div style="display:none;" id="alertwindow"class="alert alert-warning alert-dismissible" role="alert">
									<i class="fa fa-warning"></i> Alert!<p id="verrors">  </p>
								</div>
								<hr />

								<div id="content1" class="content">
									<form autocomplete="off" id="myForm" action="bgp4.php" method="post">
										<input type="hidden" id="bgp_route_no_id" name="bgp_route_no" value="<?php if (count($_GET) > 0) { echo $filled_configured_bgp; } else { echo "-1"; } ?>">
										<input type="hidden" id="v46_id" name="v46" value="0">
										<div>
											<table>
												<tr>
												    <td>Status</td>
														<td>
															<label class="fancy-radio">
																	<?php
																	$status = exec("uci get bird4.".$filled_configured_bgp.".disabled");
																	if ($status != "1") {
																			echo '<input name="status" value="0" checked="checked" type="radio" required>';
																	} else {
																			echo '<input name="status" value="0" type="radio" required>';
																	}
																	?>
																	<span><i></i>Enabled</span>
															</label>
															<label class="fancy-radio">
																	<?php
																	if ($status == "1") {
																			echo '<input name="status" value="1" checked="checked" type="radio" required>';
																	} else {
																			echo '<input name="status" value="1" type="radio" required>';
																	}
																	?>
																	<span><i></i>Disabled</span>
															</label>
														</td>
													</td>
												</tr>
												<tr>
													<td>Source Address</td>
													<td><input required type="text" id="local_router_id" name="local_id" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_configured_bgp.".source_address");?>" ></td>
												</tr>
												<tr>
                                                    <td>Local AS Number</td>
													<td><input required type="text" id="local_as_id" name="local_as" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_configured_bgp.".local_as");?>" ></td>
                                                </tr>
												<tr>
                                                    <td>Neighbor ID</td>
													<td><input required type="text" id="remote_id" name="remote_id" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_configured_bgp.".neighbor_address");?>" ></td>
                                                </tr>
												<tr>
                                                    <td>Neighbor AS Number</td>
													<td><input required type="text" id="remote_as_id" name="remote_as" class="form-control" value="<?php  echo exec("uci get bird4.".$filled_configured_bgp.".neighbor_as");?>" ></td>
                                                </tr>
											</table>
											<br/>
											<div class="row">
												<div class="col-md-6">
													<button type="submit" id="ID" class="btn btn-primary">Save</button>
												</div>
												<div class="col-md-6">
													<button type="button" class="btn btn-danger">Reset</button>
												</div>
											</div>
										</div>
									<p id="verrors"></p>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">Current BGP IPv4 Configuration</h3>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>#</th>
										<th>Status</th>
										<th>Source Address</th>
										<th>Local AS</th>
										<th>Neighbor ID</th>
										<th>Neighbor AS</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach (get_configured_bgp4() as $configured_bgp) {
										
										echo "<tr>";
										echo "<td><kbd>".strtoupper($configured_bgp)."</kbd></td>";
										$status = exec("uci get bird4.".$configured_bgp.".disabled");
										if ($status == "0") {
											echo "<td><span class='label label-success'>Enabled</span></td>";	
										} else {
											echo "<td><span class='label label-danger'>Disabled</span></td>";
										}
										echo "<td>".exec("uci get bird4.".$configured_bgp.".source_address")."</td>";
										echo "<td>".exec("uci get bird4.".$configured_bgp.".local_as")."</td>";
										echo "<td><samp>".exec("uci get bird4.".$configured_bgp.".neighbor_address")."</samp></td>";
										echo "<td>".exec("uci get bird4.".$configured_bgp.".neighbor_as")."</td>";
										echo '<td><a href="bgp4.php?configured_bgp='.$configured_bgp.'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;<a href="bgp4.php?action=delete&configured_bgp='.$configured_bgp.'"><i class="fa fa-times"></i></a></td>';
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

<?php endblock() ?>

