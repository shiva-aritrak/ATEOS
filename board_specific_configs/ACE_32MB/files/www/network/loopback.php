<?php include '/www/lib/sessioncheck.php' ?>
<head><title>Network | Loopback Interface</title></head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/networks.php' ?>

<?php
if(count($_POST) > 0) {
	$ip_addr = $_POST["IP_Address"];
	$netmask = $_POST["Subnet_Mask"];
	set_loopback($ip_addr,$netmask);
}
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
							<div class="panel-heading">
								<h3 class="panel-title">Loopback Interface</h3>
							</div>
							<div class="panel-body">
								<div id="content1" class="content">
									<form id="loopback_form" action="loopback.php" method="post">
										<div>
											<table>
												<tr>
													<td>IP Address</td>
													<td><input type="text" pattern="^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-4])$" id="l_ip" name="IP_Address" class="form-control" value="<?php  $get=exec("uci get network.lo1.ipaddr"); echo $get?>"></td>
												</tr>
												<tr>
													<td>Subnet Mask</td>
													<td><input type="text" pattern="^(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$" id="l_subnet" name="Subnet_Mask" class="form-control" value="<?php  $get=exec("uci get network.lo1.netmask"); echo $get?>"></td>
												</tr>
											</table>
											<br/>

											<div class="row">
												<div class="col-md-6">
													<button type="submit" id="loopback" class="btn btn-primary">Save</button>
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
			</div>
		</div>
	</div>
</div>
<?php endblock() ?>
