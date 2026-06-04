<?php include 'rawbase.php' ?>

<?php startblock('navbar') ?>
<nav class="navbar navbar-default navbar-fixed-top">
	<div class="brand">
		<a href="/dashboard.php"><img src="/assets/img/logo-dark.png" alt="AnexGATE Logo" class="img-responsive logo"></a>
	</div>
	<div class="container-fluid">
		<div class="navbar-btn">
			<button type="button" class="btn-toggle-fullwidth"><i class="fa fa-arrow-circle-left"></i></button>
		</div>
		<div id="navbar-menu">
			<ul class="nav navbar-nav navbar-center">
				<li class="dropdown">
					<?php
					$failed_attempts = exec("uci get anexgate.license.failed_attempts");
					$valid = exec("uci get anexgate.license.valid");
					if ((int)$failed_attempts >= 10) {
						echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-warning"></i> <span><b>System License Checked is failing. Kind contact support for assistance</b></span></a>';
					} else if ($valid == "No") {
						echo '<a href="/admin/license.php"><i class="fa fa-warning"></i> <span><b>License Invalid click here to add license</b></span></a>';
					}
					?>
				</li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><span>Hello, <?php echo $_SESSION['username']; ?></span></a>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-life-ring" aria-hidden="true"></i> <span>Help &amp; Support</span> <i class="icon-submenu fa fa-chevron-down"></i></a>
					<ul class="dropdown-menu">
						<li><a href="/admin/troubleshoot.php">Troubleshooting</a></li>
						<li><a href="/status/support.php">Support</a></li>
						<li><a href="/status/support.php">Contact Us</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
<?php endblock() ?>
