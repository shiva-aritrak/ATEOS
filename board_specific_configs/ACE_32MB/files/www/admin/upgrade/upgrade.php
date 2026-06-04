<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | Upgrade Firmware</title>
</head>
<?php include '/www/sidenav.php' ?>




<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Update Firmware</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" action="upgrade.php" method="post">
                  <input required name="uploaded_file" type="file" />
                  <p class="help-block">
                    <em>Firmware Image file ending in .bin extension</em>
                  </p>
                </br>
                <label class="fancy-checkbox">
                  <input type="checkbox" name="pcheck" />
                  <span>Keep previous configuration</span>
                </label>
                <label class="fancy-checkbox">
                  <input name="check_sha256" type="checkbox">
                  <span>Verify SHA-256</span>
                </label>
              </br>
              <input class="form-control input-sm" placeholder="SHA-1" type="text" name="sha256sum">
            </br>
            <button type="submit" class="btn btn-primary" align="left">Upgrade</button>
          </div>
        </form>
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
  $checkd = $_POST['pcheck'];
  if($checkd == 'on'){
    $filename ="pupgrade.bin";
  } else {
    $filename = "upgrade.bin";
  }

  $check_sha256 = $_POST["check_sha256"];
  $sha256sum = $_POST["sha256sum"];

  $fpath = "/tmp/";
  if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $fpath . $filename)) {
    if ( $check_sha256 == "on" ) {
      if ( get_shasum($fpath . $filename) == $sha256sum ) {
        echo("<script>window.location = 'flashing.php';</script>");
      } else {
        echo "SHA-256 does not match. Aborting Upgrade";
      }
    } else {
      echo("<script>window.location = 'flashing.php';</script>");
    }
  } else {
    echo "Error in file upload";
  }
}
?>
