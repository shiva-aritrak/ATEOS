<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | AnexConnect</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/system.php' ?>

<?php
if(count($_POST) > 0) {
  $status = $_POST["status"];
  $connect_domain = $_POST["connect_domain"];
  $con_type = $_POST["con_type"];
  set_connect_config($status, $connect_domain);
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
                  <h3 class="panel-title">AnexConnect</h3>
                </div>
                <div class="panel-body">
                  <form autocomplete="off" enctype="multipart/form-data" id="connect_form" action="connect.php" method="post">
                    <table>
                      <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $connect_status = exec("uci get anexgate.connect.status");
                            if ($connect_status == "1") {
                              echo '<input name="status" value="1" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($connect_status == "0") {
                              echo '<input name="status" value="0" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="0" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Disabled</span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td>Connect Server Address</td>
                        <td><input required type="text" id="connect_domain_id" name="connect_domain" class="form-control" value="<?php  echo exec("uci get anexgate.connect.connect_domain");?>"></td>
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
        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">AnexConnect Configuration</h3>
            </div>
            <div class="panel-body no-padding">
              <table class="table">
                <tbody>
                  <tr>
                    <td>Configuration Sync</td>
                    <td>
                      <?php
                      $connect_status = exec("uci get anexgate.connect.success");
                      if ($connect_status == "1") {
                        echo '<span class="label label-success" id="connect_status">SUCCESS</span>';
                      } else {
                        echo '<span class="label label-danger" id="connect_status">NO SYNC</span>';
                      }
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Connect Domain</td>
                    <td>
                      <samp>
                        <?php
                        echo exec("uci get anexgate.connect.connect_domain");
                        ?>
                      </samp>
                    </td>
                  </tr>
		<?php
                $connect_domain = exec("uci get anexgate.connect.connect_domain");

                if ($connect_domain == "prod1.anexconnect.com") {
                    // Get the port value from UCI
                    $connect_port = exec("uci get anexgate.config.connect_port");

                    echo "<tr>
                        <td>Port</td>
                        <td>
                            <samp>$connect_port</samp>
                        </td>
                    </tr>";
                }
                ?>
		            <tr>
                  <td>Status</td>
                  <td>
                  <samp>
                    <?php
                      $connect_status = exec("uci get anexgate.connect.status"); 
                      if ($connect_status == 1){
                      echo "UP";
                      }else{
                      echo "DOWN";
                      }
                      ?>
                    </td> 
                  </samp>	
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
<?php endblock() ?>
 
