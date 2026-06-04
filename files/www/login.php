<!doctype html>
<html lang="en" class="fullscreen-bg">

<head>
	<title>Login</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="cache-control" content="max-age=0" />

	<link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/vendor/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="/assets/vendor/linearicons/style.css">
	<link rel="stylesheet" href="/assets/css/main.css">
	<link rel="stylesheet" href="/assets/css/fonts.css">

	<link rel="apple-touch-icon" sizes="76x76" href="/assets/img/apple-icon.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/assets/img/favicon.png">
</head>

<body>
	<div id="wrapper">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle">
				<div class="auth-box lockscreen clearfix">
					<div class="content">
						<div class="logo text-center">
							<img src="/assets/img/login-bg.png" alt="AnexGATE Logo">
							<?php
							if ($_GET['error'] == "1") {
								echo '<h5 class="name">Username or Password Incorrect</h5>';
							} else if ($_GET['error'] == "3") {
								echo '<h5 class="name">User not authorized to make changes, Login with admin credentials</h5>';
							}
							?>
						</div>
						<form class="form-auth-small" action="dashboard.php" method="post" autocomplete="off">
							<div class="form-group">
								<label for="signin-email" class="control-label sr-only">Username</label>
								<input name="username" class="form-control" placeholder="Username">
							</div>
							<div class="form-group">
								<label for="signin-password" class="control-label sr-only">Password</label>
								<input name="password" type="password" class="form-control" placeholder="Password">
							</div>
							<button type="submit" class="btn btn-primary btn-lg btn-block">LOGIN</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

</html>