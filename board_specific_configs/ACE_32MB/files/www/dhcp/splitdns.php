<?php include '/www/lib/sessioncheck.php' ?>
<!-- Set Title of Page -->
<head>
	<title>DHCP | Split DNS</title>
</head>

<!-- Load the side nav bar -->
<?php include '/www/sidenav.php' ?>

<!-- Load any Libraries if required/written -->
<?php
include '/www/lib/dhcplib.php';
?>

<?php
if(count($_POST) > 0) {
	// WE GOT SOME DATA IN A POST REQUEST
	$host_no = $_POST["host_no"];
	$ip_addr = $_POST["IP_Address"];
	$dom_name = $_POST["Domain_name"];
	set_splitdns($host_no,$ip_addr,$dom_name);
}
?>

<?php
$host_id = 0;
$out = "";
$ret = 99;
$html_out = "";
$filled_host_no = 99;

exec("uci show dhcp.@domain[".$host_id."]", $out, $ret);
while ( $ret == 0 ) {
	$html_out = $html_out.'<tr>';
	$html_out = $html_out.'  <td>'.$host_id.'</td>';
	$html_out = $html_out.'  <td>'.exec("uci get dhcp.@domain[".$host_id."].name").'</td>';
	$html_out = $html_out.'  <td>'.exec("uci get dhcp.@domain[".$host_id."].ip").'</td>';
	$html_out = $html_out.'  <td><a href="splitdns.php?host_no='.$host_id.'"><i class="fa fa-edit"></i></a></td>';
	$html_out = $html_out.'  <td><a href="splitdns.php?action=delete&host_no='.$host_id.'"><i class="fa fa-times"></i></a></td>';
	$html_out = $html_out.'</tr>';

	++$host_id;
	exec("uci show dhcp.@domain[".$host_id."]", $out, $ret);
}

if(count($_GET) > 0) {
	$action = $_GET["action"];
	$filled_host_no = $_GET["host_no"];

	if ( $action == "delete" ) {
		delete_dom($filled_host_no, $host_id);
		echo '<script>window.location.href = "splitdns.php";</script>';
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
								<h3 class="panel-title">DNS SPLIT HORIZON</h3>
							</div>
							<div class="panel-body">
								<div style="display:none;" id="alertwindow"class="alert alert-warning alert-dismissible" role="alert">
									<i class="fa fa-warning"></i> Alert!<p id="verrors">  </p>
								</div>
								<hr />

								<div id="content1" class="content">
									<form autocomplete="off" id="myForm" action="splitdns.php" method="post">
										<input type="hidden" id="host_no_id" name="host_no" value="<?php if (count($_GET) > 0) { echo $filled_host_no; } else { echo "-1"; } ?>">

										<div>
											<table>
												<tr>
													<td>Domain Name</td>
													<td><input required type="text" id="dom_name" name="Domain_name" class="form-control" value="<?php  echo exec("uci get dhcp.@domain[".$filled_host_no."].name");?>" ></td>
												</tr>
												<tr>
													<td>IP Address</td>
													<td><input required type="text" id="ip_addr" name="IP_Address" class="form-control" value="<?php  echo exec("uci get dhcp.@domain[".$filled_host_no."].ip");?>" ></td>
												</tr>
											</table>
											<br />
											<div class="row">
												<div class="col-md-6">
													<button type="button" id="ID" class="btn btn-primary" onclick="check(this)" >Save</button>
												</div>
												<div class="col-md-6">
													<button type="button" class="btn btn-danger" onclick="myFunction()">Reset</button>
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
							<h3 class="panel-title">Current DNS Split Horizon</h3>
						</div>
						<div class="panel-body">
							<table class="table table-condensed">
								<thead>
									<tr>
										<th>#</th>
										<th>Domain</th>
										<th>IP Address</th>
									</tr>
								</thead>
								<tbody>
									<?php
									echo $html_out;
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
function myFunction() {
	document.getElementById("myForm").reset();
	document.getElementById("verrors").innerHTML="";
	document.getElementById("alertwindow").style.display="none";
}
</script>
<script src="validatesplit.js"></script>
