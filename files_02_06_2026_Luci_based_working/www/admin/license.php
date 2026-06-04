<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | License</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/license.php' ?>

<?php
$error = '';

if(count($_POST) > 0) {
  $license_key = $_POST["license_key"];
  $license_mode = $_POST["license_mode"];

  if ($license_mode == "offline") {
    if (empty($_FILES['license_key_file'])) {
      $error = 'License Key File is required for Offline Mode. Login to your account on cloud.anexgate.com to reterive your key';
    } else {
      $fpath = "/tmp/licensekey.acekey";
      move_uploaded_file($_FILES['license_key_file']['tmp_name'], $fpath);
    }
  }

  set_license_key($license_key, $license_mode);
  if ($license_mode == "online") {
    register_license();
  } else if ($license_mode == "offline") {
    register_license_offline();
  }
  exec("uci set anexgate.license.last_checked=\"$(date)\"");
}

if(count($_GET) > 0) {
  $action = $_GET["action"];
  if($action == "check") {
    check_license();
    exec("uci set anexgate.license.last_checked=\"$(date)\"");
  } else if ($action == "register") {
    register_license();
    exec("uci set anexgate.license.last_checked=\"$(date)\"");
  }
  echo '<script>window.location.href = "license.php";</script>';
  exit;
}
?>

<?php startblock('contentbar') ?>
<div class="main">
  <!-- MAIN CONTENT -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="col-md-12">
            <div class="panel">
              <div class="panel-body">
                <?php
                  if ($error) {
                    echo '    <div class="alert alert-danger alert-dismissible" role="alert">';
                    echo '      <i class="fa fa-danger"></i> Error! '.$error;
                    echo '    </div>';
                  }
                ?>
                <div class="panel-heading">
                  <h3 class="panel-title">License</h3>
                </div>
                <div class="panel-body">
                  <form autocomplete="off" enctype="multipart/form-data" id="license_form" action="license.php" method="post">
                    <table>
                      <tr>
                        <td>License Key</td>
                        <td><input required type="text" id="license_key_id" name="license_key" class="form-control" value="<?php  echo exec("uci get anexgate.license.key");?>"></td>
                      </tr>
                      <tr>
                        <td>Serial No.</td>
                        <td><input required type="text" disabled="disabled" id="serial_no_id" name="serial_no" class="form-control" value="<?php  echo exec("uci get anexgate.license.serial");?>"></td>
                      </tr>
                      <tr>
                        <td>Registered MAC</td>
                        <td><input type="text" disabled="disabled" class="form-control" value="<?php echo exec("uci get anexgate.license.macaddr"); ?>"></td>
                      </tr>
                      <tr>
                        <td>Mode</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $license_mode = exec("uci get anexgate.license.license_mode");
                            if ($license_mode == "online") {
                              echo '<input name="license_mode" value="online" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="license_mode" value="online" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Online via AnexGate Cloud</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($license_mode == "offline") {
                              echo '<input name="license_mode" value="offline" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="license_mode" value="offline" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Offline using License Key File</span>
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                        <input name="license_key_file" type="file" />
                        <p class="help-block">
                            <em>Choose License Key File</em>
                        </p>
                        </td>
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
              <div class="panel-heading">
                <h3 class="panel-title">License Information</h3>
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
                      <td> <?php echo ucwords(exec("uci get anexgate.license.valid")); ?></td>
                    </tr>
                    <tr>
                      <td>Product</td>
                      <td> <?php echo exec("uci get anexgate.license.product")." ".exec("uci get anexgate.license.model"); ?></td>
                    </tr>
                    <tr>
                      <td>Software Version</td>
                      <td> <?php echo exec("uci get anexgate.software.version"); ?></td>
                    </tr>
                    <tr>
                      <td>Build Version</td>
                      <td> <?php echo exec("uci get anexgate.software.build"); ?></td>
                    </tr>
                    <tr>
                      <td>Last Checked</td>
                      <td> <?php echo exec("uci get anexgate.license.last_checked"); ?></td>
                    </tr>
                    <tr>
                      <td>License Type</td>
                      <td> <?php echo exec("uci get anexgate.license.license_type"); ?></td>
                    </tr>
                    <tr>
                      <td>License Verification Mode</td>
                      <td> <?php echo ucwords(exec("uci get anexgate.license.license_mode")); ?></td>
                    </tr>
                    <tr>
                      <td>License Registered Date</td>
                      <td> <?php echo exec("uci get anexgate.license.registered_date"); ?></td>
                    </tr>
                    <?php
                    $license_type = exec("uci get anexgate.license.license_type");
                    if ($license_type != "Perpetual") {
                      echo '<tr>';
                      echo '  <td>License Expiry Date</td>';
                      echo '  <td>';
                      echo exec("uci get anexgate.license.license_expiry");
                      echo '</td>';
                      echo '</tr>';
                    }
                    ?>
                    <tr>
                      <td>Support Expiry Date</td>
                      <td> <?php echo exec("uci get anexgate.license.support_expiry"); ?></td>
                    </tr>
                    <tr>
                      <td>Max SSL VPN Clients</td>
                      <td> <?php echo exec("uci get anexgate.license.ssl_vpn_max"); ?></td>
                    </tr>
                    <tr>
                      <td>Max IPSec Tunnels</td>
                      <td> <?php echo exec("uci get anexgate.license.ipsec_vpn_max"); ?></td>
                    </tr>
                    <tr>
                      <td>Max GRE Tunnels</td>
                      <td> <?php echo exec("uci get anexgate.license.gre_vpn_max"); ?></td>
                    </tr>
                    <tr>
                      <td>AnexConnect</td>
                      <td> <?php echo exec("uci get anexgate.connect.feature_enabled"); ?></td>
                    </tr>
                    <tr>
                      <td>AnexHub</td>
                      <td> <?php echo exec("uci get anexhub.hub.feature_enabled"); ?></td>
                    </tr>
                    <tr>
                      <td>AnexSpot</td>
                      <td> <?php echo exec("uci get anexgate.anexspot.feature_enabled"); ?></td>
                    </tr>
                    <tr>
                      <td>AnexFuse</td>
                      <td> <?php echo exec("uci get anexgate.anexfuse.feature_enabled"); ?></td>
                    </tr>
                    <tr>
                      <td>Failed Check Attempts</td>
                      <td> <?php echo exec("uci get anexgate.license.failed_attempts"); ?></td>
                    </tr>
                    <tr>
                      <td>Customer Name</td>
                      <td> <?php echo exec("uci get anexgate.customer.name"); ?></td>
                    </tr>
                    <tr>
                      <td>Customer Address</td>
                      <td> <?php echo exec("uci get anexgate.customer.address").", ".exec("uci get anexgate.customer.city").", ".exec("uci get anexgate.customer.state").", ".exec("uci get anexgate.customer.country"); ?></td>
                    </tr>
                    <tr>
                      <td>Device Location</td>
                      <td> <?php echo exec("uci get anexgate.customer.location"); ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="panel-footer">
                <div class="row">
                  <div class="col-md-3"><a href="license.php?action=check" class="btn btn-primary btn-sm">Re-Check License</a></div>
                  <div class="col-md-3"><a href="license.php?action=register" class="btn btn-primary btn-sm">Re-Register License</a></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">License</h3>
              </div>
              <div class="panel-body">
                <h4>AnexGATE ACE</h4>
                <p>What the warranty covers:</p>

                <p>We warrant its products to be free from defects in material and
                  workmanship during the warranty period. If a product proves to be
                  defective in material or workmanship during the warranty period, we will
                  at its sole option repair or replace the product with a like product
                  with a like product. Replacement product or parts may include
                  remanufactured or refurbished parts or components.</p>

                  <p>How long the warranty is effective:</p>

                  <p>The AnexGATE-ACE is warranted for one year for all parts and one year for all labor from the date of the first consumer purchase.</p>

                  <p>Whom the warranty protects:</p>

                  <p>This warranty is valid only for the first consumer purchaser.</p>

                  <p>
                    What the warranty does not cover:
                    <ol>
                      <li>Any product, on which the serial number has been defaced, modified or
                        removed.</li>

                        <li>Damage, deterioration or malfunction resulting from:
                          <ul>
                            <li>Accident, misuse, neglect, fire, water, lightning, or other acts of nature, unauthorized product modification, or failure to follow instructions supplied with the product.</li>
                            <li>Repair or attempted repair by anyone not authorized by us.</li>
                            <li>Any damage of the product due to shipment.</li>
                            <li>Removal or installation of the product.</li>
                            <li>Causes external to the product, such as electric power fluctuations or failure.</li>
                            <li>Use of supplies or parts not meeting our specifications.</li>
                            <li>Normal wears and tears.</li>
                            <li>Any other cause that does not relate to a product defect.</li>
                          </ul>
                        </li>
                        <li>Removal, installation, and set-up service charges.</li>
                      </ol>
                    </p>

                    <p>How to get service:
                      <ol>
                        <li>For information about receiving service under warranty, contact our Customer Support.</li>
                        <li>To obtain warranted service, you will be required to provide (a) the original dated sales slip, (b) your name, (c) your address (d) a description of the problem and (e) the serial number of the product.</li>
                        <li>Take or ship the product prepaid in the original container to your dealer, and our service center.</li>
                        <li>For additional information, contact your dealer or our Customer Service Center.</li>
                      </ol>
                    </p>

                    <p>
                      Limitation of implied warranties:

                      THERE ARE NO WARRANTIES, EXPRESSED OR IMPLIED, WHICH EXTEND BEYOND THE
                      DESCRIPTION CONTAINED HEREIN INCLUDING THE IMPLIED WARRANTY OF
                      MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.
                    </p>
                    <p>Exclusion of damages:</br>

                      Our LIABILITY IS LIMITED TO THE COST OF REPAIR OR REPLACEMENT OF THE
                      PRODUCT. We SHALL NOT BE LIABLE FOR:
                      <ol>


                        <li>DAMAGE TO OTHER PROPERTY CAUSED BY ANY DEFECTS IN THE PRODUCT, DAMAGES BASED UPON INCONVENCE, LOSS OF USE OF THE PRODUCT, LOSS OF TIME, LOSS OF PROFITS, LOSS OF BUSINESS OPPORTUNITY, LOSS OF GOODWILL, INTERFERENCE WITH BUSINESS RELATIONSHIPS, OR OTHER COMMERCIAL LOSS, EVEN IF ADVISED OF THE POSSIBLITY OF SUCH DAMAGES.</li>

                        <li>ANY OTHER DAMAGES, WHETHER INCIDENTAL, CONSEQUENTIAL OR OTHERWISE.</li>

                        <li>ANY CLAIM AGAINST THE CUSTOMER BY ANY OTHER PARTY.</li>
                      </ol>
                    </p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <?php endblock() ?>
