<?php include '/www/lib/sessioncheck.php' ?>

<head>
<title>Administration | Log Export</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/system.php' ?>

<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $enabled = $_POST["enabled"];
  $hook_v4 = $_POST["hook_v4"];
  $hook_v6 = $_POST["hook_v6"];
  $natevents = $_POST["natevents"];
  $appevents = $_POST["appevents"];
  $source_id = $_POST["source_id"];
  $destinations = array_filter(array_unique($_POST["destinations"]));
  $app_destinations = array_filter(array_unique($_POST["app_destinations"]));
  
  set_netflow_config($enabled, $hook_v4, $hook_v6, $natevents, $appevents, $source_id, $destinations, $app_destinations);
}

?>

<?php startblock('contentbar') ?>

<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <h3 class="page-title"></h3>
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <?php
                if ($incorrect) {
                  echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                  echo '  <i class="fa fa-warning"></i> '.$message.'</p>';
                  echo '</div>';
                }
              ?>
              <div class="panel-heading">
                <h3 class="panel-title">Log Export</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="logexport_form" action="logexport.php" method="post">
                  <table>

                    <tr>
                      <td colspan="2"><b>Global Log Export Settings</b></td>
                      <td></td>
                    </tr>

                    <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $enabled = exec("uci get netflow.globals.enabled");
                            if ($enabled == "1") {
                              echo '<input name="enabled" value="1" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="enabled" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($enabled == "0") {
                              echo '<input name="enabled" value="0" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="enabled" value="0" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Disabled</span>
                          </label>
                        </td>
                      </tr>
                    <tr>
                      <td>Enable NAT Logs</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $natevents = exec("uci get netflow.globals.natevents");
                          if ($natevents == "1") {
                            echo '<input name="natevents" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="natevents" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Export IPv4 Flows</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $hook_v4 = exec("uci get netflow.globals.hook_v4");
                          if ($hook_v4 == "1") {
                            echo '<input name="hook_v4" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="hook_v4" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Export IPv6 Flows</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $hook_v6 = exec("uci get netflow.globals.hook_v6");
                          if ($hook_v6 == "1") {
                            echo '<input name="hook_v6" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="hook_v6" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Source Identifier</td>
                      <td><input type="text" id="source_id_id" name="source_id" class="form-control" value="<?php  echo exec("uci get netflow.globals.source_id"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Flow/NAT Log Destinations</td>
                      <td id="id_flow_export_destinations">
                      <?php
                        $destinations = explode(" ", exec("uci get netflow.globals.destinations"));
                        if ($destinations) {
                          foreach ($destinations as $destination) {
                              if(trim($destination)) {
                                  echo '  <input type="text" name="destinations[]" class="form-control" value="'.$destination.'"><a href="#" class="delete"><i class="fa fa-cross"></i></a>';
                              }
                          }
                        }
                      ?>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><button id="add_ip_button" type="button"><i class="fa fa-plus"></i> Add IP</button></td>
                    </tr>

                    <tr>
                      <td>Monitor Applications</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $appevents = exec("uci get netflow.globals.appevents");
                          if ($appevents == "1") {
                            echo '<input name="appevents" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="appevents" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Export Application Flows</td>
                      <td id="id_app_export_destinations">
                      <?php
                        $app_destinations = explode(" ", exec("uci get dpiagent.globals.destinations"));
                        if ($app_destinations) {
                          foreach ($app_destinations as $app_destination) {
                              if(trim($app_destination)) {
                                  echo '  <input type="text" name="app_destinations[]" class="form-control" value="'.$app_destination.'"><a href="#" class="delete"><i class="fa fa-cross"></i></a>';
                              }
                          }
                        }
                      ?>
                      </td>
                    </tr>

                    <tr>
                      <td></td>
                      <td><button id="add_app_ip_button" type="button"><i class="fa fa-plus"></i> Add IP</button></td>
                    </tr>


                </table>
                  <br/>
                  <div class="row">
                    <div class="col-md-6">
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                    <div class="col-md-6">
                      <button type="button" class="btn btn-danger">Reset</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>

<script>
$(document).ready(function() {
  $('#add_ip_button').click(function(e) {
        e.preventDefault();
        $('#id_flow_export_destinations').append('<br><input type="text" name="destinations[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
    });
    
    $('#id_flow_export_destinations').on("click", ".delete", function(e) {
        e.preventDefault();
        $(this).parent('tr').remove();
    })

    $('#add_app_ip_button').click(function(e) {
        e.preventDefault();
        $('#id_app_export_destinations').append('<br><input type="text" name="app_destinations[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
    });
    
    $('#id_app_export_destinations').on("click", ".delete", function(e) {
        e.preventDefault();
        $(this).parent('tr').remove();
    })

});    
</script>