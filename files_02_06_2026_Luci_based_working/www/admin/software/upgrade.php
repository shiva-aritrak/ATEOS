<?php include '/www/lib/sessioncheck.php' ?>
<?php include '/www/lib/update.php' ?>
<?php
if(count($_GET) > 0) {
  $action = $_GET["action"];
  if($action == "check") {
    check_for_software_upgrade();
    exec("uci set anexgate.software.last_checked=\"$(date)\"");
  } else if ($action == "backup") {
    $hub_domain = "";
    $anexconnect_domain = "";

    $anexconnect_registered = exec("uci get anexgate.connect.status");
    $hub_registered = exec("uci get anexhub.hub.registered");

    if($hub_registered == "Yes") {
      $hub_domain = exec("uci get anexgate.config.hub_domain");
    }

    if ($anexconnect_registered == "1") {
      $anexconnect_domain = exec("uci get anexgate.connect.connect_domain");
    }

    if ($hub_domain) {
        backup_to_cloud($hub_domain);
    } else if ($anexconnect_domain) {
        backup_to_cloud($anexconnect_domain);
    }

    exec("uci set anexgate.software.backup_ts=\"$(date)\"");
  } else if ($action == "upgrade") {
    $status = download_software_upgrade();
    if ($status != 0) {
      http_response_code(404);
      die();
    }
  } else {
    http_response_code(404);
    die();
  }
  echo '<script>window.location.href = "/admin/software/upgrade.php";</script>';
  exit;
}
?>

<?php include '/www/sidenav.php' ?>

<head>
  <title>Administration | Software Update</title>
</head>



<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">Software Information</h3>
              <div class="right">
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body no-padding">
              <table class="table">
                <tbody>
                  <tr>
                    <td>License Valid</td>
                    <td><?php echo ucwords(exec("uci get anexgate.license.valid")); ?></td>
                  </tr>
                  <tr>
                    <td>Product</td>
                    <td><?php echo exec("uci get anexgate.license.product")." ".exec("uci get anexgate.license.model"); ?></td>
                  </tr>
                  <tr>
                    <td>Installed Version</td>
                    <td><?php echo exec("uci get anexgate.software.version"); ?></td>
                  </tr>
                  <tr>
                    <td>Build Version</td>
                    <td><?php echo exec("uci get anexgate.software.build"); ?></td>
                  </tr>
                  <tr>
                    <td>Update Last Checked</td>
                    <td><?php echo exec("uci get anexgate.software.last_checked"); ?></td>
                  </tr>
                  <tr>
                    <td>Update Available</td>
                    <td><?php echo exec("uci get anexgate.software.update_available"); ?></td>
                  </tr>
                  <tr>
                    <td>Update Version</td>
                    <td><?php echo exec("uci get anexgate.software.update_version"); ?></td>
                  </tr>
                  <tr>
                    <td>Last Backup Timestamp</td>
                    <td><?php echo exec("uci get anexgate.software.backup_ts"); ?></td>
                  </tr>
                  <?php
                  $update_available = exec("uci get anexgate.software.update_available");
                  if ($update_available == "Yes") {
                    echo '<tr>';
                    echo '  <td></td>';
                    echo '  <td id="download_button_id"><button id="download_update_button_id" type="button" class="btn btn-primary">Download &amp; Update Software</button></td>';
                    echo '</tr>';
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div class="panel-footer">
              <div class="row">
                <div class="col-md-3"><a href="/admin/software/upgrade.php?action=check" class="btn btn-primary btn-sm">Check for Upgrade</a></div>
                <div class="col-md-3"><a href="/admin/software/upgrade.php?action=backup" class="btn btn-primary btn-sm">Backup to Cloud</a></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php endblock() ?>

<?php
if(!empty($_FILES['uploaded_file']))
{
  $filename ="pupgrade.bin";
  $fpath = "/tmp/";

  if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $fpath . $filename)) {
    echo("<script>window.location = '/admin/upgrade/flashing.php';</script>");
  } else {
    echo "Error during upgrade";
  }
}
?>


<script>
$(document).ready(function() {
  $("#download_update_button_id").click(function(e) {
    $( "#download_update_button_id" ).replaceWith( '<button id="download_update_button_id" type="button" class="btn btn-danger" disabled="disabled"><i class="fa fa-spinner fa-spin"></i> Download in Progress...</button>' );

    e.preventDefault();
    $.ajax({
      type: "GET",
      url: "/admin/software/upgrade.php",
      data: {
        action: "upgrade",
      },
      success: function(result) {
        alert("Call Complete");
        $("#download_update_button_id" ).replaceWith( '<button id="download_update_button_id" type="button" class="btn btn-success"><i class="fa fa-check-circle"></i> Success. Upgrade will commence in 5 seconds...</button>' );
        setTimeout(
          function()
          {
            window.location.href = "/admin/upgrade/flashing.php";
          }, 5000);
        },
        error: function(result) {
          alert("Call Failed");
          $("#download_update_button_id" ).replaceWith( '<button id="download_update_button_id" type="button" class="btn btn-warning"><i class="fa fa-warning"></i> Error during Download</button>' );
        }
      });
    });
  });
</script>
