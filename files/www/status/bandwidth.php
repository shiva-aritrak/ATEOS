<?php include '/www/lib/sessioncheck.php' ?>
<?php include '/www/lib/infostatics.php' ?>

<?php
if (count($_GET) > 0) {
	$action = $_GET["action"];
	$interface_type = $_GET["interface_type"];

	if ($action == "check_usage" && $interface_type == "inet") {
		$payload = json_encode( array( "timestamp"=> time(), "usage"=> iface_network_usage($_GET["interface"]) ) );
		echo $payload;
		exit;
	} else if ($action == "check_usage" && $interface_type=="ipsec") {
		$payload = json_encode( array( "timestamp"=> time(), "usage"=> ipsec_stats($_GET["interface"])) );
		echo $payload;
		exit;
	}
}
?>

<head>
	<title>Status | Bandwidth</title>
</head>

<?php
include '/www/sidenav.php';
include '/www/lib/common.php';
?>


<?php startblock('contentbar') ?>
<div class="main">
	<!-- MAIN CONTENT -->
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<?php

				foreach (get_configured_interfaces() as $intf) {
				echo '<div class="col-md-4">';
				echo '	<div class="panel">';
				echo '		<div class="panel-heading">';
				echo '			<h3 class="panel-title">'.strtoupper($intf).' <small id="'.$intf.'_current"></small></h3>';
				echo '			<div class="right">';
				echo '				<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>';
				echo '				<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>';
				echo '			</div>';
				echo '		</div>';
				echo '		<div class="panel-body">';
				echo '			<div id="'.$intf.'-bw-chart" class="ct-chart">';
				echo '			</div>';
				echo '		</div>';
				echo '	</div>';
				echo '</div>';
				}

				foreach (get_configured_ipsec_sas() as $tunnel_name) {
					echo '<div class="col-md-4">';
					echo '	<div class="panel">';
					echo '		<div class="panel-heading">';
					echo '			<h3 class="panel-title">'.strtoupper(str_replace("-", "_", str_replace("-", "_", $tunnel_name))).' <small id="'.str_replace("-", "_", $tunnel_name).'_current"></small></h3>';
					echo '			<div class="right">';
					echo '				<button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>';
					echo '				<button type="button" class="btn-remove"><i class="fa fa-times"></i></button>';
					echo '			</div>';
					echo '		</div>';
					echo '		<div class="panel-body">';
					echo '			<div id="'.str_replace("-", "_", $tunnel_name).'-bw-chart" class="ct-chart">';
					echo '			</div>';
					echo '		</div>';
					echo '	</div>';
					echo '</div>';
				}

				?>
			</div>
		</div>
	</div>
	<!-- END MAIN CONTENT -->
</div>

<?php endblock() ?>

<?php 

startblock('scriptblock');

echo '<script>';
echo 'var chart_options;';
echo '';
echo 'chart_options = {';
echo '	height: 300,';
echo '	showArea: true,';
echo '	showLine: false,';
echo '	showPoint: false,';
echo '	fullWidth: true,';
echo '	axisX: {';
echo '		showGrid: false';
echo '	},';
echo '	lineSmooth: false,';
echo '};';

foreach (get_configured_interfaces() as $intf) {
echo $intf.'_dwn_usage = [0, 0, 0, 0, 0];';
echo $intf.'_up_usage = [0, 0, 0, 0, 0];';
echo $intf.'_timeint = [0, 0, 0, 0, 0];';
echo $intf.'_chart_data = {';
echo '	labels: '.$intf.'_timeint,';
echo '	series: ['.$intf.'_dwn_usage, '.$intf.'_up_usage]';
echo '};';
echo "var ".$intf."_chart_data;";
echo "var ".$intf."_chart = new Chartist.Line('#".$intf."-bw-chart', ".$intf."_chart_data, chart_options);";
}

foreach (get_configured_ipsec_sas() as $tunnel_name) {
	echo str_replace("-", "_", $tunnel_name).'_dwn_usage = [0, 0, 0, 0, 0];';
	echo str_replace("-", "_", $tunnel_name).'_up_usage = [0, 0, 0, 0, 0];';
	echo str_replace("-", "_", $tunnel_name).'_timeint = [0, 0, 0, 0, 0];';
	echo str_replace("-", "_", $tunnel_name).'_chart_data = {';
	echo '	labels: '.str_replace("-", "_", $tunnel_name).'_timeint,';
	echo '	series: ['.str_replace("-", "_", $tunnel_name).'_dwn_usage, '.str_replace("-", "_", $tunnel_name).'_up_usage]';
	echo '};';
	echo "var ".str_replace("-", "_", $tunnel_name)."_chart_data;";
	echo "var ".str_replace("-", "_", $tunnel_name)."_chart = new Chartist.Line('#".str_replace("-", "_", $tunnel_name)."-bw-chart', ".str_replace("-", "_", $tunnel_name)."_chart_data, chart_options);";
}

echo 'setInterval(function(){';
echo '	$(function() {';

foreach (get_configured_interfaces() as $intf) {
	echo '$.ajax({';
	echo '	url: "bandwidth.php",';
	echo '	type: "get",';
	echo '	data: {';
	echo '		action: "check_usage",';
	echo '		interface_type: "inet",';
	echo '		interface: "'.$intf.'"';
	echo '	},';
	echo '	dataType: "json",';
	echo '	success: function(response) {';
	echo '		'.$intf.'_dwn_usage.splice(0,1);';
	echo '		'.$intf.'_up_usage.splice(0,1);';
	echo '		'.$intf.'_timeint.splice(0,1);';
	echo '		'.$intf.'_dwn_usage.push(response.usage[1]);';
	echo '		'.$intf.'_up_usage.push(response.usage[0]);';
	echo '		var date = new Date();';
	echo '		'.$intf.'_timeint.push( date.toLocaleTimeString());';
	echo '		$("#'.$intf.'_current").html("Download: " + response.usage[1] + " Mbps Upload: " + response.usage[0] + " Mbps");';
	echo '		'.$intf.'_chart.update();';
	echo '	},';
	echo '	error: function(xhr) {';
	echo '	}';
	echo '});';
}
echo '});';
echo '},2000);';


echo 'setInterval(function(){';
echo '	$(function() {';

foreach (get_configured_ipsec_sas() as $tunnel_name) {
	echo '$.ajax({';
	echo '	url: "bandwidth.php",';
	echo '	type: "get",';
	echo '	data: {';
	echo '		action: "check_usage",';
	echo '		interface_type: "ipsec",';
	echo '		interface: "'.$tunnel_name.'"';
	echo '	},';
	echo '	dataType: "json",';
	echo '	success: function(response) {';
	echo '		'.str_replace("-", "_", $tunnel_name).'_dwn_usage.splice(0,1);';
	echo '		'.str_replace("-", "_", $tunnel_name).'_up_usage.splice(0,1);';
	echo '		'.str_replace("-", "_", $tunnel_name).'_timeint.splice(0,1);';
	echo '		'.str_replace("-", "_", $tunnel_name).'_dwn_usage.push(response.usage[1]);';
	echo '		'.str_replace("-", "_", $tunnel_name).'_up_usage.push(response.usage[0]);';
	echo '		var date = new Date();';
	echo '		'.str_replace("-", "_", $tunnel_name).'_timeint.push( date.toLocaleTimeString());';
	echo '		$("#'.str_replace("-", "_", $tunnel_name).'_current").html("Download: " + response.usage[1] + " Mbps Upload: " + response.usage[0] + " Mbps");';
	echo '		'.str_replace("-", "_", $tunnel_name).'_chart.update();';
	echo '	},';
	echo '	error: function(xhr) {';
	echo '		console.log("Error"); ';
	echo '		console.log(xhr); ';
	echo '	}';
	echo '});';
}
echo '});';
echo '},6000);';

echo '</script>';
endblock('scriptblock');

?>