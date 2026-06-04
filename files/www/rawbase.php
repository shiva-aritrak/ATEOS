<?php require_once 'ti.php' ?>
<!doctype html>
<html lang="en">

<head>
	<title>Dashboard</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="/assets/vendor/linearicons/style.css">
	<link rel="stylesheet" href="/assets/vendor/chartist/css/chartist-custom.css">
	<link rel="stylesheet" href="/assets/vendor/toastr/toastr.min.css">
	<link rel="stylesheet" href="/assets/css/main.css">
	<link rel="stylesheet" href="/assets/css/fonts.css">
	<link rel="stylesheet" href="/assets/css/custom.css">
	<link rel="apple-touch-icon" sizes="76x76" href="/assets/img/apple-icon.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/assets/img/favicon.png">
</head>
<body>
	<div id="wrapper">
		<?php emptyblock('navbar') ?>
		<?php emptyblock('sidebar') ?>
		<?php emptyblock('contentbar') ?>

		<div id="reboot_confirm" class="modal fade"  role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Reboot</h4>
					</div>
					<div class="modal-body">
						Are you sure?
					</div>
					<div class="modal-footer">
						<button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Reboot</button>
						<button type="button" data-dismiss="modal" class="btn">Cancel</button>
					</div>
				</div>
			</div>
		</div>
		<footer>
			<div class="container-fluid">
				<p class="copyright">&copy; <?php echo date("Y"); ?> <a href="https://www.anexgate.com" target="_blank">AnexGATE</a>. All Rights Reserved.</p>
			</div>
		</footer>
	</div>

	<script src="/assets/vendor/jquery/jquery.min.js"></script>
	<script src="/assets/vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="/assets/vendor/jquery-slimscroll/jquery.slimscroll.min.js"></script>
	<script src="/assets/vendor/toastr/toastr.min.js"></script>
	<script src="/assets/vendor/jquery.easy-pie-chart/jquery.easypiechart.min.js"></script>
	<script src="/assets/vendor/chartist/js/chartist.min.js"></script>
	<script src="/assets/scripts/klorofil-common.js"></script>

	<?php emptyblock('scriptblock') ?>
	<?php emptyblock('scriptblock_sidenav') ?>
</body>
</html>
