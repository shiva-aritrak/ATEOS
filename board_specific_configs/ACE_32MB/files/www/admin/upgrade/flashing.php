<?php include '/www/lib/sessioncheck.php' ?>
<html>
<head>
	<title>Upgrade in progress</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<!-- VENDOR CSS -->
	<link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="/assets/vendor/linearicons/style.css">
	<link rel="stylesheet" href="/assets/vendor/chartist/css/chartist-custom.css">
	<link rel="stylesheet" href="/assets/vendor/toastr/toastr.min.css">
	<link rel="stylesheet" href="/assets/css/fonts.css">
</head>
<body>
	<center>
		<div style="height: auto; position:absolute; top:35%;  left:40%;" >
			<?php
			if (file_exists("/www/assets/img/flash.gif") ) {
				echo '<img src="/assets/img/flash.gif">';
			} else {
				echo '<i class="fa fa-5x fa-spinner fa-spin"></i>';
			}
			?>
			<b><p>Upgrading</p></b>
			<b><p>Please do not power off the device!</p></b>
		</div>
	</center>
</body>

<script src="/assets/vendor/jquery/jquery.min.js" type="text/javascript"></script>
<script>
var static_ip = window.location.host;
$.ajax(
	{
		url: 'flash.php',
		dataType: 'text',
		type: 'post',
		success: function()
		{
		}
	})

	setTimeout(function pingtest(){
		$.ajax({
			url: 'index.php',
			type: 'HEAD',
			success: function(result){
				window.location="http://"+static_ip;
			},
			error: setTimeout(function(result){
				window.location="http://192.168.100.1";
			},100)
		});
	},249999);
</script>

</html>
