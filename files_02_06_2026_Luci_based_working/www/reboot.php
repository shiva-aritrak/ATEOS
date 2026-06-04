<?php include '/www/lib/sessioncheck.php' ?>

<?php

if (count($_GET) > 0) {
    if ($_GET["action"] == "reboot") {
        exec("reboot");
    }
}
?>

<!doctype html>
<html lang="en" class="fullscreen-bg">

<head>
  <title>Reboot</title>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
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
            </div>
            <div class="row">
              <div class="col-md-12">
                <!-- END LABELS -->
                <!-- PROGRESS BARS -->
                <div class="panel">
                  <div class="panel-heading">
                    <h3 class="panel-title"><center>Rebooting ACE</center></h3>
                  </div>
                  <div class="panel-body">
                    <div class="progress">
                      <div class="progress-bar" id="progress_bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        0%
                      </div>
                    </div>
                    <p id="message"></p>
                  </div>
                </div>
              </div>
              <!-- END ALERT MESSAGES -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>

<script src="/assets/vendor/jquery/jquery.min.js"></script>
<script src="/assets/vendor/bootstrap/js/bootstrap.min.js"></script>

<script>
var counter = 0;
var origin = window.location.origin;

$.ajax({
  url: "reboot.php",
  type: "get", //send it through get method
  data: {
    action: "reboot"
  },
  success: function(response) {
    var interval = setInterval(function() {
      counter++;
      $("#progress_bar").html(counter + "%");
      $('#progress_bar').attr('style','width: '+ counter +'%');
      if (counter == 100) {
        $("#message").html("Router reboot complete. Redirecting to login page in 5 seconds...");
        setTimeout(
          function()
          {
            window.location.href = origin;
          }, 7000);
          // Display a login box
          clearInterval(interval);
        }
      }, 1000);  },
      error: function(xhr) {
        //Do Something to handle error
      }
});
</script>
</html>
