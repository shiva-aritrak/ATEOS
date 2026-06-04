<?php include '/www/lib/sessioncheck.php' ?>

<head>
<title>Administration | SNMP</title>
</head>

<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/system.php' ?>

<?php
$incorrect = false;
$message = "";

if(count($_POST) > 0) {
  $status = $_POST["status"];
  $sysname = $_POST["sysname"];
  $location = $_POST["location"];
  $contact = $_POST["contact"];
  $port = $_POST["port"];
  $snmpv1_only = $_POST["snmpv1_only"];
  $community_get = $_POST["pubcommunity"];
  $set_enable = $_POST["set_enable"];
  $community_set = $_POST["pricommunity"];
  $rohost = $_POST["rohost"];
  $trap_enable = $_POST["trap_enable"];
  $trap_host_ip = $_POST["traphostip"];
  $trap_host_port = $_POST["traphostport"];
  $v12 = $_POST["v12"];

  $rouser = $_POST["rouser"];
  $rouser_enc = $_POST["rouser_enc"];
  $rouser_auth = $_POST["rouser_auth"];
  $rouser_auth_pwd = $_POST["rouser_auth_pwd"];
  $rouser_priv = $_POST["rouser_priv"];
  $rouser_priv_pwd = $_POST["rouser_priv_pwd"];
  $user_rw = $_POST["user_rw"];
  $snmpv3_trap = $_POST["snmpv3_trap"];
  $trapsessip = $_POST["trapsessip"];
  $trapsessport = $_POST["trapsessport"];

  if($v12 == "0") {
    set_snmp_global($status, $sysname, $location, $contact, $port);
  }
  if($v12 == "1") {
    set_snmp_config($snmpv1_only, $community_get, $set_enable, $community_set, $rohost, $trap_enable, $trap_host_ip, $trap_host_port);
  }
  if($v12 == "2") {
    set_snmpv3_config($rouser, $rouser_enc, $rouser_auth, $rouser_auth_pwd, $rouser_priv, $rouser_priv_pwd, $user_rw, $snmpv3_trap, $trapsessip, $trapsessport);
  }

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
                <h3 class="panel-title">SNMP Global</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="snmp_form" action="snmp.php" method="post">
                <input type="hidden" id="v12_id" name="v12" value="0">
                  <table>

                    <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $snmp_status = exec("uci get snmpd.general.enabled");
                            if ($snmp_status == "1") {
                              echo '<input name="status" value="1" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($snmp_status == "0") {
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
                      <td>System Description</td>
                      <td><input type="text" required id="sysname_id" name="sysname" class="form-control" value="<?php  echo exec("uci -q get snmpd.@system[0].sysDescr"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Location</td>
                      <td><input type="text" required id="location_id" name="location" class="form-control" value="<?php  echo exec("uci -q get snmpd.@system[0].sysLocation"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Contact</td>
                      <td><input type="text" required id="contact_id" name="contact" class="form-control" value="<?php  echo exec("uci get snmpd.@system[0].sysContact"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Port</td>
                      <td><input type="number" required id="port_id" name="port" class="form-control" value="<?php  echo exec("uci get snmpd.@agent[0].agentaddress | cut -d ',' -f 2 | cut -d ':' -f 2"); ?>"></td>
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
                <h3 class="panel-title">SNMP v1/v2c</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="snmp_form" action="snmp.php" method="post">
                <input type="hidden" id="v12_id" name="v12" value="1">
                  <table>

		                <tr>
                      <td>SNMP v1 Only</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $snmpv1_only = exec("uci -q get snmpd.snmp_ro.version");
                          if ($snmpv1_only == "v1") {
                            echo '<input name="snmpv1_only" id="snmpv1_only_id" checked="checked" type="checkbox">';
                          } else {
                            echo '<input name="snmpv1_only" id="snmpv1_only_id" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Community Get</td>
                      <td><input type="text" required id="pubcommunity_id" name="pubcommunity" title="Read Only" class="form-control" value="<?php  echo exec("uci -q get snmpd.public.community"); ?>"></td>
                    </tr>

                    <tr>
                      <td>SET Enable</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $set_enable = exec("uci -q get snmpd.private.secname");
                          if ($set_enable == "rw") {
                            echo '<input name="set_enable" id="set_enable_id" onclick="change_set_enable()" checked="checked" type="checkbox">';
                          } else {
                            echo '<input name="set_enable" id="set_enable_id" onclick="change_set_enable()" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr id='toggle_comm_set'>
                      <td>Community Set</td>
                      <td><input type="text" id="pricommunity_id" name="pricommunity" title="Read Write" class="form-control" value="<?php  echo exec("uci get snmpd.private.community"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Host/Network Allowed</td>
                      <td><input type="text" required id="rohost_id" name="rohost" title="Ex: 192.168.100.10 or 192.168.100.0/24 or 'default' for Any Source" class="form-control" value="<?php  echo exec("uci -q get snmpd.public.source"); ?>"></td>
                    </tr>

                    <tr>
                      <td>SNMP TRAP</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $trap_enable = exec("uci -q get snmpd.authtrapenable.enable");
                          if ($trap_enable == "1") {
                            echo '<input name="trap_enable" id="trap_enable_id" onclick="change_trap()" checked="checked" type="checkbox">';
                          } else {
                            echo '<input name="trap_enable" id="trap_enable_id" onclick="change_trap()" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr id='toggle_trap'>
                      <td>TRAP Host IP</td>
                      <td><input type="text" id="traphostip_id" name="traphostip" class="form-control" value="<?php  echo exec("uci -q get snmpd.@trap2sink[0].host"); ?>"></td>
                    </tr>

                    <tr id='toggle_trap1'>
                      <td>TRAP HOST Port</td>
                      <td><input type="text" id="traphostport_id" name="traphostport" class="form-control" value="<?php  echo exec("uci -q get snmpd.@trap2sink[0].port"); ?>"></td>
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
                <h3 class="panel-title">SNMP v3</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="snmp_form" action="snmp.php" method="post">
                <input type="hidden" id="v12_id" name="v12" value="2">
                  <table>

                    <tr>
                      <td>Username</td>
                      <td><input type="text" required id="rouser_id" name="rouser" title="Read Only User" class="form-control" value="<?php  echo exec("uci -q get snmpd.ro_user.name"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Encryption Type</td>
                      <td>
                        <select required id="rouser_enc_id" name="rouser_enc" class="form-control input-sm">
                          <?php
                          foreach ($SNMPENCRYPT_TYPES as $key => $enc_type) {
                            $enc_ro_type = exec("uci -q get snmpd.ro_user.level");
                            if ($key == $enc_ro_type) {
                              echo '<option selected="selected" value="'.$key.'">'.$enc_type.'</option>';
                            } else {
                              echo '<option value="'.$key.'">'.$enc_type.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr id="auth_protocol">
                      <td>Authentication Protocol</td>
                      <td>
                        <select id="rouser_auth_id" name="rouser_auth" class="form-control input-sm">
                          <?php
                          foreach ($SNMPAUTHPROTOCOLS as $key => $auth_type) {
                            $auth_ro_type = exec("uci -q get snmpd.ro_user.auth_proto");
                            if ($key == $auth_ro_type) {
                              echo '<option selected="selected" value="'.$key.'">'.$auth_type.'</option>';
                            } else {
                              echo '<option value="'.$key.'">'.$auth_type.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr id="auth_pass">
                      <td>Authentication Password</td>
                      <td><input type="text" id="rouser_auth_pwd_id" name="rouser_auth_pwd" class="form-control" value="<?php  echo exec("uci -q get snmpd.ro_user.auth_passphrase"); ?>"></td>
                    </tr>

                    <tr id="priv_protocol">
                      <td>Privacy Protocol</td>
                      <td>
                        <select id="rouser_priv_id" name="rouser_priv" class="form-control input-sm">
                          <?php
                          foreach ($SNMPPRIVPROTOCOLS as $key => $priv_type) {
                            $priv_ro_type = exec("uci -q get snmpd.ro_user.priv_proto");
                            if ($key == $priv_ro_type) {
                              echo '<option selected="selected" value="'.$key.'">'.$priv_type.'</option>';
                            } else {
                              echo '<option value="'.$key.'">'.$priv_type.'</option>';
                            }
                          }
                          ?>
                        </select>
                      </td>
                    </tr>

                    <tr id="priv_pass">
                      <td>Privacy Password</td>
                      <td><input type="text" id="rouser_priv_pwd_id" name="rouser_priv_pwd" class="form-control" value="<?php  echo exec("uci -q get snmpd.ro_user.priv_passphrase"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Enable Read-Write</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $user_rw = exec("uci -q get snmpd.usm_pub_access.write");
                          if ($user_rw == "all") {
                            echo '<input name="user_rw" id="user_rw_id" checked="checked" type="checkbox">';
                          } else {
                            echo '<input name="user_rw" id="user_rw_id" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>SNMPv3 TRAP</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $snmpv3_trap = exec("uci -q get snmpd.authtrapenable.enable");
                          if ($snmpv3_trap == "1") {
                            echo '<input name="snmpv3_trap" id="snmpv3_trap_id" onclick="change_trap()" checked="checked" type="checkbox">';
                          } else {
                            echo '<input name="snmpv3_trap" id="snmpv3_trap_id" onclick="change_trap()" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr id='toggle_trapsess'>
                      <td>TRAPSESS IP</td>
                      <?php
                        $trapsess_ip = exec("uci -q get snmpd.trapsess.trapsess | cut -d ':' -f 2");
                        echo '<td><input type="text" id="trapsess_id" name="trapsessip" class="form-control" value="'.$trapsess_ip.'"></td>';
                      ?>
                    </tr>

                    <tr id='toggle_trapsess1'>
                      <td>TRAPSESS Port</td>
                      <?php
                        $trapsess_port = exec("uci -q get snmpd.trapsess.trapsess | cut -d ':' -f 3 | cut -d \"'\" -f 1");
                        echo '<td><input type="text" id="trapsessport_id" name="trapsessport" class="form-control" value="'.$trapsess_port.'"></td>';
                      ?>
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
<?php endblock() ?>
<script type="text/javascript">
<?php echo 'var set_enable="'.exec("uci -q get snmpd.private.secname").'";'; ?>
if (set_enable == "rw") {
    $('#toggle_comm_set').show();
    document.getElementById('pricommunity_id').setAttribute('required', 'required')
} else {
    $('#toggle_comm_set').hide();
    document.getElementById('pricommunity_id').removeAttribute('required', 'required')
}

function change_set_enable() {                                                                                                                                                                                                               
    var is_checked = document.getElementById('set_enable_id').checked;                
                                                                     
    if (is_checked) {                                                                 
        $('#toggle_comm_set').show();
        $('#pricommunity_id').attr('required', true);                                            
    } else {                                                                          
        $('#toggle_comm_set').hide();
        $('#pricommunity_id').removeAttr('required'); 
    }
}
</script>
<script type="text/javascript">
<?php echo 'var trap_enable="'.exec("uci -q get snmpd.authtrapenable.enable").'";'; ?>
if (trap_enable == "1") {
    $('#toggle_trap, #toggle_trap1, #toggle_trapsess, #toggle_trapsess1').show();
    document.getElementById('traphostip_id').setAttribute('required', 'required')
    document.getElementById('traphostport_id').setAttribute('required', 'required')
} else {
    $('#toggle_trap, #toggle_trap1, #toggle_trapsess, #toggle_trapsess1').hide();
    document.getElementById('traphostip_id').removeAttribute('required', 'required')
    document.getElementById('traphostport_id').removeAttribute('required', 'required')
}


function change_trap() {
    var is_checked = document.getElementById('trap_enable_id').checked;
    var is_checked_v3 = document.getElementById('snmpv3_trap_id').checked;
    if (is_checked) {
      $('#toggle_trap, #toggle_trap1').show();
      $('#traphostip_id, #traphostport_id').attr('required', true);
    } else if (!is_checked) {
      $('#toggle_trap, #toggle_trap1').hide();
      $('#traphostip_id, #traphostport_id').removeAttr('required');
      //$('#traphostip_id, #traphostport_id').attr('required', true);
    }
    if (is_checked_v3) {
      $('#toggle_trapsess, #toggle_trapsess1').show();
    } else if (!is_checked_v3) {
      $('#toggle_trapsess, #toggle_trapsess1').hide();
    } 
}
</script>

<script>

function toggleDetailsBox() {
  if ($('#user_rw_id').is(':checked')) {
    $('#rw_user, #rw_encr_type').show();
  } else {
    $('#rw_user, #rw_encr_type, #rw_auth_protocol, #rw_auth_pass, #rw_priv_protocol, #rw_priv_pass').hide();
    $('#rwuser_enc_id').val('noAuthNoPriv');
  }
}


function toggleSNMPFields() {
  var rouser_enc = $('#rouser_enc_id').val();
  if (rouser_enc === 'noAuthNoPriv') {
    $('#auth_protocol, #auth_pass, #priv_protocol, #priv_pass').hide();
    $('#auth_protocol, #auth_pass, #priv_protocol, #priv_pass').removeAttr('required');
  } else if (rouser_enc === 'authNoPriv') {
    $('#auth_protocol, #auth_pass').show();
    $('#priv_protocol, #priv_pass').hide();
    $('#auth_protocol, #auth_pass').attr('required', true);
    $('#priv_protocol, #priv_pass').removeAttr('required');
  } else {
    $('#auth_protocol, #auth_pass, #priv_protocol, #priv_pass').show();
    $('#auth_protocol, #auth_pass, #priv_protocol, #priv_pass').attr('required', true);
  }
}

function toggleRWSNMPFields() {
  var rouser_enc = $('#rwuser_enc_id').val();
  if (rouser_enc === 'noAuthNoPriv') {
    $('#rw_auth_protocol, #rw_auth_pass, #rw_priv_protocol, #rw_priv_pass').hide();
    $('#rw_auth_protocol, #rw_auth_pass, #rw_priv_protocol, #rw_priv_pass').removeAttr('required');
  } else if (rouser_enc === 'authNoPriv') {
    $('#rw_auth_protocol, #rw_auth_pass').show();
    $('#rw_priv_protocol, #rw_priv_pass').hide();
    $('#rw_auth_protocol, #rw_auth_pass').attr('required', true);
    $('#rw_priv_protocol, #rw_priv_pass').removeAttr('required');
  } else {
    $('#rw_auth_protocol, #rw_auth_pass, #rw_priv_protocol, #rw_priv_pass').show();
    $('#rw_auth_protocol, #rw_auth_pass, #rw_priv_protocol, #rw_priv_pass').attr('required', true);
  }
}

$(document).ready(function () {
  toggleDetailsBox();
  toggleSNMPFields();
  toggleRWSNMPFields();
  $('#rouser_enc_id').change(toggleSNMPFields);
  $('#rwuser_enc_id').change(toggleRWSNMPFields);
  $('#user_rw_id').change(toggleDetailsBox);
});

</script>

