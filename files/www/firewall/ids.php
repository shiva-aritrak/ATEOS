<?php include '/www/lib/sessioncheck.php' ?>

<head>
<title>Advanced | IDS/IPS</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/firewall.php' ?>


<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $enabled = $_POST["enabled"];
  $interface = $_POST["interface"];
  $local_nets = array_filter(array_unique($_POST["local_nets"]));
  
  set_ids_config($enabled, $interface, $local_nets);
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
                <h3 class="panel-title">IDS/IPS</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="ids_form" action="ids.php" method="post">
                  <table>

                    <tr>
                      <td colspan="2"><b>Global IDS/IPS Settings</b></td>
                      <td></td>
                    </tr>

                    <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $enabled = exec("uci get snort.snort.enabled");
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
                      <td>Interface</td>
                      <td>
                      	<select required name="interface" id="id_interface" class="form-control input-sm">
                      		<?php
                      		foreach (get_configured_link_interfaces() as $intf) {
                            		$selected_interfaces = exec("uci get snort.snort.listen_interface");
                            		if (in_array($intf, show_zone_networks($selected_interfaces))) {
                      				echo '<option selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
                      			} else {
                      				echo '<option value="'.$intf.'">'.strtoupper($intf).'</option>';
                      			}
                      		}
                      		?>
                      	</select>
                      </td>
                    </tr>

                    <tr>
                      <td>Internal Network Subnets</td>
                      <td id="id_local_nets">
                      <?php
                        $local_nets = explode(" ", exec("uci get snort.snort.local_nets"));
                        if ($local_nets) {
                          foreach ($local_nets as $destination) {
                              if(trim($destination)) {
                                  echo '  <input type="text" name="local_nets[]" class="form-control" value="'.$destination.'"><a href="#" class="delete"><i class="fa fa-cross"></i></a>';
                              }
                          }
                        }
                      ?>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><button id="add_ip_nets" type="button"><i class="fa fa-plus"></i> Add Network Subnet</button></td>
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
  $('#add_ip_nets').click(function(e) {
        e.preventDefault();
        $('#id_local_nets').append('<br><input type="text" name="local_nets[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
    });
    
    $('#id_local_nets').on("click", ".delete", function(e) {
        e.preventDefault();
        $(this).parent('tr').remove();
    })
});    
</script>