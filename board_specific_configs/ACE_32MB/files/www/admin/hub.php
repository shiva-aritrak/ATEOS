<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | AnexHub</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/system.php' ?>

<?php
if(count($_POST) > 0) {
  $mode = $_POST["mode"];
  if($mode == "hub_config") {
    $status = $_POST["status"];
    $hub_domain = $_POST["hub_domain"];
    $override = $_POST["override"];
    $sync_interval = $_POST["sync_interval"];
    set_hub_config($status, $hub_domain, $override, $sync_interval);
  } else if ($mode == "local_override") {
    push_local_config();
  } else if ($mode == "reinit") {
    reinit_anexhub();
  }
}
?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="col-md-12">
            <div class="panel">
              <div class="panel-body">
                <div class="panel-heading">
                  <h3 class="panel-title">AnexHub</h3>
                </div>
                <div class="panel-body">
                  <form autocomplete="off" enctype="multipart/form-data" id="hub_form" action="hub.php" method="post">
                    <input type="hidden" name="mode" value="hub_config">
                    <table>
                      <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $hub_status = exec("uci get anexhub.hub.status");
                            if ($hub_status == "yes") {
                              echo '<input name="status" value="yes" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="yes" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($hub_status == "no") {
                              echo '<input name="status" value="no" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="no" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Disabled</span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>AnexHub Domain</td>
                        <td><input required type="text" id="hub_domain_id" name="hub_domain" class="form-control" value="<?php  echo exec("uci get anexgate.config.hub_domain");?>"></td>
                      </tr>
                      <tr>
                        <td>Local Override</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $hub_override = exec("uci get anexhub.hub.override");
                            if ($hub_override == "yes") {
                              echo '<input name="override" value="yes" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="override" value="yes" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Yes</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($hub_override == "no") {
                              echo '<input name="override" value="no" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="override" value="no" type="radio" required>';
                            }
                            ?>
                            <span><i></i>No</span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Sync Interval</td>
                        <td><input required type="number" min="90" max="1800" id="sync_interval_id" name="sync_interval" class="form-control" value="<?php  echo exec("uci get anexhub.hub.sync_interval");?>"></td>
                      </tr>
                    </table>
                    <br/>
                    <div class="row">
                      <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Save</button>
                      </div>
                      <div class="col-md-6">
                        <button type="button" class="btn btn-danger">Clear</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                  <div class="panel-heading">
                    <h3 class="panel-title">Push Local Configuration</h3>
                  </div>
                  <div class="panel-body">
                    <form autocomplete="off" enctype="multipart/form-data" id="hub_form" action="hub.php" method="post">
                      <input type="hidden" name="mode" value="local_override">
                      <button type="submit" class="btn btn-primary">Push Local Configuration to AnexHub</button>
                    </form>
                  </div>
                </div>
            </div>
          </div>
          <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                  <div class="panel-heading">
                    <h3 class="panel-title">Reinitialize AnexHub</h3>
                  </div>
                  <div class="panel-body">
                    <form autocomplete="off" enctype="multipart/form-data" id="hub_form" action="hub.php" method="post">
                      <input type="hidden" name="mode" value="reinit">
                      <button type="submit" class="btn btn-danger">Reinitialize AnexHub Sync</button>
                      <br><br><i>Selecting this option will forgo all existing configuration in AnexHub and setup the device as new, all previous history will be lost</i>
                    </form>
                  </div>
                </div>
            </div>
          </div>          
          <div class="col-md-12">
            <div class="panel">
              <div class="panel-heading">
                <h3 class="panel-title">AnexHub Information</h3>
                <div class="right">
                  <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                  <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
                </div>
              </div>
              <div class="panel-body no-padding">
                <table class="table">
                  <tbody>
                    <tr>
                      <td>Cloud Configuration ID</td>
                      <td> <?php echo exec("uci get anexhub.hub.cloud_config_id"); ?></td>
                    </tr>
                    <tr>
                      <td>Local Configuration ID</td>
                      <td> <?php echo exec("uci get anexhub.hub.config_id"); ?></td>
                    </tr>
                    <tr>
                      <td>Sync Status</td>
                      <td> <?php echo exec("uci get anexhub.hub.sync_status"); ?></td>
                    </tr>
                    <tr>
                      <td>Last Checked</td>
                      <td> <?php echo exec("uci get anexhub.hub.last_checked"); ?></td>
                    </tr>
                    <tr>
                      <td>Last Applied</td>
                      <td> <?php echo exec("uci get anexhub.hub.last_applied"); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
