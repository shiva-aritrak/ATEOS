<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | Configuration Management</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
$restore_complete = false;
$error = "";

if(count($_GET) > 0) {
  $action = $_GET["action"];

  if ($action == "download") {
    exec("sysupgrade -b /www/admin/config/backup.bin");
    header('Content-type: binary/octet-stream');
    echo '<script>window.location.href = "/admin/config/backup.bin";</script>';
    exit;
  } else if ($action == "download_template") {
      header('Content-type: application/json');
      echo '<script>window.location.href = "/admin/config/template.json";</script>';
      exit;
  } else if ($action == "factory") {
	  $SERIAL=exec("uci get anexgate.license.serial");
	  $MAC_ADDR=exec("uci get anexgate.license.macaddr");
	  $HOSTNAME=exec("uci get system.@system[0].hostname");
	  $BUILD=exec("uci get anexgate.software.build");
    $HUB_STATUS=exec("uci get anexhub.hub.status");
    if ($HUB_STATUS == "yes") {
        exec("uci set anexhub.hub.registered='no'");
        exec("uci commit anexhub");
        exec("uci export anexhub > /tmp/hub.config");
    }
    exec("uci export anexgate > /tmp/license.config");
    exec("uci import < /etc/factory.config");
    exec("uci set anexgate.license.serial='".$SERIAL."'");
    exec("uci set anexgate.license.macaddr='".$MAC_ADDR."'");
    exec("uci set anexgate.software.build='".$BUILD."'");
    exec("uci set system.@system[0].hostname='".$HOSTNAME."'");
    exec("uci import anexgate < /tmp/license.config");
    exec("uci import anexhub < /tmp/hub.config");
    exec("uci commit");
    exec("sync");
    exec("/etc/init.d/firewall enable");
    exec("/etc/init.d/mwan3 enable");
    exec("/etc/init.d/openvpn enable");
    exec("sync");
    exec("sleep 3");
    exec("/sbin/reboot");
    echo '<script>window.location.href = "/admin/config/manage.php";</script>';
    exit;
  }
}

if(!empty($_FILES['config_restore_file']))
{
  $preserve_cred = $_POST['preserve_cred'];
  $change_loop_ip = $_POST['change_loop_ip'];
  $change_ipsec = $_POST['change_ipsec'];
  $change_fw = $_POST['change_fw'];
  $loopback_ip = $_POST['loopback_ip'];

  $fpath = "/tmp/restore.tar.gz";
  if(move_uploaded_file($_FILES['config_restore_file']['tmp_name'], $fpath)) {
    exec("mkdir /tmp/restoreconf");
    exec("tar xzfv ".$fpath." -C /tmp/restoreconf");
    exec("mkdir /tmp/restoreconf");
    exec("tar xzfv ".$fpath." -C /tmp/restoreconf");
    
    $restore_model = exec("uci -c /tmp/restoreconf/etc/config get anexgate.license.model");
    $orig_model=exec("uci get anexgate.license.model");
    if ($restore_model != $orig_model) {
      $error = "Backup file is taken from a different model, Cannot restore";
    }

    if($preserve_cred) {
      $user_username = exec("uci -c /tmp/restoreconf/etc/config get anexgate.user.username");
      $user_password = exec("uci -c /tmp/restoreconf/etc/config get anexgate.user.password");
      $admin_username = exec("uci -c /tmp/restoreconf/etc/config get anexgate.authentication.username");
      $admin_password = exec("uci -c /tmp/restoreconf/etc/config get anexgate.authentication.password");
    }

    exec("cp /etc/config/anexgate /tmp/anexgate.original");
    exec("cp /etc/factory.config /tmp/factory.config.original");

    $PORT1_MAC=exec("uci get network.port1.macaddr");
    $PORT2_MAC=exec("uci get network.port2.macaddr");
    $PORT3_MAC=exec("uci get network.port3.macaddr");
    $PORT4_MAC=exec("uci get network.port4.macaddr");
    $PORT5_MAC=exec("uci get network.port5.macaddr");
    $HOSTNAME=exec("uci get system.@system[0].hostname");

    exec("sysupgrade -r /tmp/restore.tar.gz");

    exec("cp /tmp/anexgate.original /etc/config/anexgate");
    exec("cp /tmp/factory.config.original /etc/factory.config ");

    exec("uci set system.@system[0].hostname='".$HOSTNAME."'");
    if ($PORT1_MAC) {
      exec("uci set network.port1.macaddr='".$PORT1_MAC."'");
    }
    if ($PORT2_MAC) {
      exec("uci set network.port2.macaddr='".$PORT2_MAC."'");
    }
    if ($PORT3_MAC) {
      exec("uci set network.port3.macaddr='".$PORT3_MAC."'");
    }
    if ($PORT4_MAC) {
      exec("uci set network.port4.macaddr='".$PORT4_MAC."'");
    }
    if ($PORT5_MAC) {
      exec("uci set network.port5.macaddr='".$PORT5_MAC."'");
    }

    if($preserve_cred) {
      exec("uci set anexgate.user.username='".$user_username."'");
      exec("uci set anexgate.user.password='".$user_password."'");
      exec("uci set anexgate.authentication.username='".$admin_username."'");
      exec("uci set anexgate.authentication.password='".$admin_password."'");
    }
    $old_loopback = exec("uci get network.lo1.ipaddr");
    if($change_loop_ip && $loopback_ip) {
      exec("uci set network.lo1.ipaddr='".$loopback_ip."'");
      exec("uci commit network");
    }
    if($change_ipsec && $loopback_ip) {
      exec("logger -t test '/bin/sed -i 's/'$old_loopback'/'".$loopback_ip."'/g' /etc/config/ipsec'");
      exec("/bin/sed -i 's/'$old_loopback'/'".$loopback_ip."'/g' /etc/config/ipsec");
    }
    if($change_fw && $loopback_ip) {
      exec("logger -t test '/bin/sed -i 's/'$old_loopback'/'".$loopback_ip."'/g' /etc/config/firewall'");
      exec("/bin/sed -i 's/'$old_loopback'/'".$loopback_ip."'/g' /etc/config/firewall");
    }
    exec("uci commit anexgate");
    exec("uci commit");
    exec("sync");
    exec("sleep 3");
    $restore_complete = true;
    exec("rm -rf /tmp/restoreconf");
    exec("rm -rf /tmp/restore.tar.gz");
  }
}

if(!empty($_FILES['config_json_loopback']))
{
  $change_ipsec = $_POST['change_ipsec'];
  $change_hostname = $_POST['change_hostname'];
  $fpath = "/tmp/loopback.json";
  if(move_uploaded_file($_FILES['config_json_loopback']['tmp_name'], $fpath)) {
    $json_loopback = file_get_contents($fpath);
    $loopback_data = json_decode($json_loopback, true);
    exec("uci set network.lo1.ipaddr='".$loopback_data['loopback_ip']."'");
    exec("uci commit network");
    if($change_ipsec) {
      exec("uci set ipsec.tun1_remote.my_identifier='".$loopback_data['loopback_ip']."'");
      exec("uci set ipsec.tun1_remote.local_identifier='".$loopback_data['loopback_ip']."'");
      exec("uci set ipsec.tun1.local_subnet='".$loopback_data['loopback_ip']."/32'");
      exec("uci commit ipsec");
    }
    if($change_hostname) {
      exec("uci set system.@system[0].hostname='".$loopback_data['hostname']."'");
      exec("uci commit system");
    }
    exec("sync");
  }
}

?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <?php 
        if ($restore_complete) {
          echo '<div class="row">';
          echo '<div class="col-md-6">';
          echo '<div class="panel">';
          echo '  <div class="panel-body">';
          echo '    <div class="alert alert-success alert-dismissible" role="alert">';
          echo '      <i class="fa fa-success"></i> Success! Configuration Restore Complete, Reboot Router for configuration to take effect';
          echo '    </div>';
          echo '  </div>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
        if ($error) {
          echo '<div class="row">';
          echo '<div class="col-md-6">';
          echo '<div class="panel">';
          echo '  <div class="panel-body">';
          echo '    <div class="alert alert-danger alert-dismissible" role="alert">';
          echo '      <i class="fa fa-danger"></i> Error! '.$error;
          echo '    </div>';
          echo '  </div>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
			?>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Download Current Configuration</h3>
              </div>
              <div class="panel-body">
                <a href="manage.php?action=download"><button type="submit" class="btn btn-primary" align="left">Download Configuration</button></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Restore Configuration</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" action="manage.php" method="post">
                  <input required name="config_restore_file" type="file" />
                  <p class="help-block">
                    <em>Choose Configuration File</em>
                  </p>
                  <label class="fancy-checkbox">
                    <input id="preserve_cred_id" name="preserve_cred" type="checkbox">
                    <span>Overwrite Credentials from Backup File?</span>
                  </label>
		  <br>
		  <tr>
                     <td>After Restore update below Parameters</td>
                     <td><input type="text" id="loopback_ip_id" name="loopback_ip" class="form-control" value="<?php  echo exec("uci get network.lo1.ipaddr");?>"></td>
                  </tr>
		    <label class="fancy-checkbox">
                      <input id="change_loop_id" name="change_loop_ip" type="checkbox">
                      <span>Change Loopback IP?</span>
                    </label>
		    <label class="fancy-checkbox">
                      <input id="change_ipsec_id" name="change_ipsec" type="checkbox">
                      <span>Change Loopback IP in IPSec Configurations?</span>
                    </label>
		    <label class="fancy-checkbox">
                      <input id="change_fw_id" name="change_fw" type="checkbox">
                      <span>Change Loopback IP in Firewall Port Forwarding/SNAT?</span>
                    </label>
                  <br>
                  <button type="submit" class="btn btn-primary" align="left">Restore</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Restore to Factory Defaults</h3>
              </div>
              <div class="panel-body">
                <button id="factoryReset" class="btn btn-primary" align="left">Factory Reset</button>
		            <div id="confirmUser" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6);">
  			          <div style="background:#fff; margin:10% auto; padding:20px; width:300px; text-align:center;">
    				        <p>Are you sure you want to Reset the configurations to Factory defaults?</p>
    				        <button id="confirmYes" class="btn btn-primary">Yes</button>
    				        <button id="confirmNo" class="btn">No</button>
  			          </div>
		            </div>
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
document.getElementById('factoryReset').onclick = function() {
    document.getElementById('confirmUser').style.display = 'block';
};

document.getElementById('confirmNo').onclick = function() {
    document.getElementById('confirmUser').style.display = 'none';
};

document.getElementById('confirmYes').onclick = function() {
    window.location.href = "manage.php?action=factory";
};
</script>

