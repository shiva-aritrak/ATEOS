<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>AnexSpot | Manage</title>
</head>

<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/hotspot.php' ?>

<?php


if(count($_POST) > 0) {
  $status = $_POST["status"];
  $gateway_intf = $_POST["gatewayinterface"];
  $net_type = exec("uci get network.".$gateway_intf.".type");
  if($net_type == "bridge") {
    $gatewayinterface = "br-".$_POST["gatewayinterface"];
  } else {
    $gatewayinterface = exec("uci get network.".$gateway_intf.".ifname");
  }
  $port_type = exec("uci get network.".$gateway_intf.".type");

  $instance_no = $_POST["instance_no"];
  $oper_mode = $_POST["oper_mode"];
  $gatewayport = $_POST["gatewayport"];
  $gatewayname = $_POST["gatewayname"];
  $redirect_url = $_POST["redirect_url"];
  $fasremoteip = $_POST["fasremoteip"];
  $fasremoteip6 = $_POST["fasremoteip6"];
  $walled_garden = array_filter($_POST["walled_garden"]);
  $walled_garden_ip = array_filter($_POST["walled_garden_ip"]);
  $trusted_macs = array_filter($_POST["trusted_macs"]);
  $maxclients = $_POST["maxclients"];
  $checkinterval = $_POST["checkinterval"]*60;
  $preauthidletimeout = $_POST["preauthidletimeout"];
  $authidletimeout = $_POST["authidletimeout"];
  $block_tethering = $_POST["block_tethering"];
  $preempt_auth = $_POST["preempt_auth"];
  $seamless_roaming = $_POST["seamless_roaming"];
  
  $redirect_url_parsed = parse_url($redirect_url);
  
  $fasremotefqdn = $redirect_url_parsed["host"];
  $faspath = $redirect_url_parsed["path"];

  set_anexspot_config($status, $oper_mode, $gatewayinterface, $gateway_intf, $gatewayport, $gatewayname, $redirect_url, $fasremoteip, $fasremoteip6, $fasremotefqdn, $faspath, $walled_garden, $maxclients, $trusted_macs, $checkinterval, $preauthidletimeout, $authidletimeout,  $instance_no, $walled_garden_ip, $block_tethering, $preempt_auth, $seamless_roaming);

}

$instance_no="99";

if(count($_GET) > 0) {
  $instance_no = $_GET["instance"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_anexspot_instance($instance_no);
    echo '<script>window.location.href = "/anexspot/manage.php";</script>';
    exit;
  }
}


?>

<?php include '/www/sidenav.php' ?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">HotSpot Configuration</h3>
              </div>
              <div class="panel-body">
                <form autocomplete="off" enctype="multipart/form-data" id="hotspot_form" action="manage.php" method="post">
                  <input type="hidden" id="instance_id" name="instance_no" value="<?php if (count($_GET) > 0) { echo $instance_no; } else { echo "-1"; } ?>">
                  <table>

                    <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $hotspot_status = exec("uci get opennds.@opennds[".$instance_no."].enabled");
                            if ($hotspot_status == "1") {
                              echo '<input name="status" value="1" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($hotspot_status == "0") {
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
                        <td colspan="2"><b>AnexSpot Client</b></td>
                    </tr>

                    <tr>
                      <td>Bind to Interface</td>
                      <td>
                        <select required name="gatewayinterface" id="id_gatewayinterface" class="form-control input-sm">
                          <?php
                            $interface = exec("uci get opennds.@opennds[".$instance_no."].gateway_intf");
                            foreach (get_lan_interfaces() as $intf) {
                              if(strtolower($interface) == strtolower($intf)) {
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
                      <td>Operation Mode</td>
											<td>
												<label class="fancy-radio">
													<input name="oper_mode" value="ipv4" type="radio" id="oper_ipv4" onclick="change_operation_mode(this.id)">
													<span><i></i>IPv4 Only</span>
												</label>
												<label class="fancy-radio">
													<input name="oper_mode" value="ipv6" type="radio" id="oper_ipv6" onclick="change_operation_mode(this.id)">
													<span><i></i>IPv6 Only</span>
												</label>
												<label class="fancy-radio">
													<input name="oper_mode" value="ipv46" type="radio" id="oper_ipv46" onclick="change_operation_mode(this.id)">
													<span><i></i>IPv4 + IPv6</span>
												</label>
											</td>
										</tr>

                    <tr>
                        <td>Local Server Port</td>
                        <td><input type="text" disabled="disabled" id="gatewayport_id" name="gatewayport" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].gatewayport"); ?>"></td>
                    </tr>

                    <tr>
                        <td>Local Gateway Name</td>
                        <td><input required type="text" id="gatewayname_id" name="gatewayname" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].gatewayname"); ?>"></td>
                    </tr>

                    <tr>
                        <td colspan="2"><b>Controller Settings</b></td>
                    </tr>

                    <tr>
                        <td>Cloud Redirect URL</td>
                        <td><input required type="text" id="redirect_url_id" name="redirect_url" class="form-control" value="<?php echo "https://".exec("uci get opennds.@opennds[".$instance_no."].fasremotefqdn").exec("uci get opennds.@opennds[".$instance_no."].faspath"); ?>"></td>
                    </tr>

                    <tr>
                        <td>Cloud Server IP</td>
                        <td><input required type="text" id="fasremoteip_id" name="fasremoteip" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].fasremoteip"); ?>"></td>
                    </tr>

                    <tr>
                        <td>Cloud Server IP v6</td>
                        <td><input required type="text" id="fasremoteip6_id" name="fasremoteip6" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].fasremoteip6"); ?>"></td>
                    </tr>

                    <tr>
                        <td>Maximum Clients</td>
                        <td><input required type="text" id="maxclients_id" name="maxclients" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].maxclients"); ?>"></td>
                    </tr>

                    <tr>
                        <td>Status Poll Interval (Mins)</td>
                        <td><input type="number" min="1" step="1" id="checkinterval_id" name="checkinterval" class="form-control" value="<?php echo intval(intval(exec("uci get opennds.@opennds[".$instance_no."].checkinterval"))/60); ?>"></td>
                    </tr>

                    <tr>
                        <td>No Auth Idle Timeout (Mins)</td>
                        <td><input type="number" min="11" step="1" id="preauthidletimeout_id" name="preauthidletimeout" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].preauthidletimeout"); ?>"></td>
                    </tr>
                    
                    <tr>
                        <td>Idle Timeout (Mins)</td>
                        <td><input type="text" id="authidletimeout_id" name="authidletimeout" class="form-control" value="<?php echo exec("uci get opennds.@opennds[".$instance_no."].authidletimeout"); ?>"></td>
                    </tr>

                    <tr>
                      <td>Block Connection Tethering</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $block_tethering = exec("uci get opennds.@opennds[".$instance_no."].block_tethering");
                          if ($block_tethering == "1") {
                            echo '<input name="block_tethering" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="block_tethering" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>
		
		    <tr>
                      <td>Allow Preemptive Auth</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $preempt_auth = exec("uci get opennds.@opennds[".$instance_no."].allow_preemptive_authentication");
                          if ($preempt_auth == "1") {
                            echo '<input name="preempt_auth" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="preempt_auth" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

		    <tr>
                      <td>Seamless Roaming Clients</td>
                      <td>
                        <label class="fancy-checkbox">
                          <?php
                          $seamless_roaming = exec("uci get opennds.@opennds[".$instance_no."].seamless_roaming");
                          if ($seamless_roaming == "1") {
                            echo '<input name="seamless_roaming" checked=checked type="checkbox">';
                          } else {
                            echo '<input name="seamless_roaming" type="checkbox">';
                          }
                          ?>
                          <span></span>
                        </label>
                      </td>
                    </tr>

                    <tr>
                      <td>Walled Garden Domains</td>
                      <td id="id_walled_garden_domains">
                      <?php
                      $domain_names = explode(" ", exec("uci get opennds.@opennds[".$instance_no."].walledgarden_fqdn_list"));
                      if ($domain_names) {
                        foreach ($domain_names as $domain_name) {
                          echo '  <input type="text" name="walled_garden[]" class="form-control" value="'.$domain_name.'"><a href="#" class="delete"><i class="fa fa-cross"></i></a>';
                        }
                      }
                      ?>
                      </td>
                    </tr>

                    <tr>
                      <td></td>
                      <td><button id="add_domain_button" type="button"><i class="fa fa-plus"></i> Add Domain</button></td>
                    </tr>

		                <tr>
                      <td>Walled Garden IPs</td>
                      <td id="id_walled_garden_ips">
                      <?php
                      $ip_names = explode("'", exec("uci get opennds.@opennds[".$instance_no."].preauthenticated_users"));
                      if ($ip_names) {
                        foreach ($ip_names as $ip_name) {
				                  if(trim($ip_name)) {
                            echo '  <input type="text" name="walled_garden_ip[]" class="form-control" value="'.trim(str_replace('allow to', '', $ip_name)).'"><a href="#" class="delete"><i class="fa fa-cross"></i></a>';
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
                      <td>Whitelisted MAC Addresses</td>
                      <td id="id_trusted_macs">
                      <?php
                      $trusted_macs = explode(" ", exec("uci get opennds.@opennds[".$instance_no."].trustedmac"));
                      if ($trusted_macs) {
                        foreach ($trusted_macs as $trusted_mac) {
                          echo '  <input pattern="^([a-fA-F0-9]{2}:){5}[a-fA-F0-9]{2}$" type="text" name="trusted_macs[]" class="form-control" value="'.$trusted_mac.'"><a href="#" class="delete"><i class="fa fa-cross"></i></a>';
                        }
                      }
                      ?>
                    </tr>

                    <tr>
                      <td></td>
                      <td><button id="add_mac_button" type="button"><i class="fa fa-plus"></i> Add MAC Address</button></td>
                    </tr>
                  </table>

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
        <div class="col-md-8">
          <div class="panel">
              <div class="panel-body">
                <div class="panel-heading">
                  <h3 class="panel-title">Current AnexSpot Instances</h3>
                </div>
                <div class="panel-body">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Interface</th>
                        <th>Gateway MAC</th>
                        <th>Gateway Name</th>
                        <th>Remote Server</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php

                        $instance_id = 0;
                        $out = "";
                        $ret = 99;

                        exec("uci show opennds.@opennds[".$instance_id."]" , $out, $ret);
                        while ( $ret == 0 ) {
                          echo '<tr>';
                          echo '  <td><kbd>'.exec("uci get  opennds.@opennds[".$instance_id."].gateway_intf").'</kbd></td>';
                          echo '  <td><samp>';
                          readfile("/sys/class/net/".exec("uci get  opennds.@opennds[".$instance_id."].gatewayinterface")."/address");
                          echo '</samp></td>';
                          echo '  <td>'.exec("uci get  opennds.@opennds[".$instance_id."].gatewayname").'</td>';
                          echo '  <td><samp>'.exec("uci get  opennds.@opennds[".$instance_id."].fasremotefqdn").'</samp></td>';
                          echo '  <td><a href="manage.php?instance='.$instance_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="manage.php?action=delete&instance='.$instance_id.'"><i class="fa fa-times"></i></a></td>';
                          echo '  <td><a href="users.php?instance='.$instance_id.'"><i class="fa fa-user"></i></a> </td>';
                          echo '</tr>';

                          ++$instance_id;
                          exec("uci show opennds.@opennds[".$instance_id."]" , $out, $ret);
                        }

                        ?>
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
  </div>
</div>
<?php endblock() ?>

<?php startblock('scriptblock') ?>
<script>
  	function change_operation_mode(id) {
		document.getElementById(id).checked=true;

		if(id == "oper_ipv4") {
      document.getElementById("fasremoteip_id").required = true;
      document.getElementById("fasremoteip6_id").required = false;
    } else if (id == "oper_ipv6") {
      document.getElementById("fasremoteip_id").required = false;
      document.getElementById("fasremoteip6_id").required = true;
		} else if (id == "oper_ipv6") {
      document.getElementById("fasremoteip_id").required = true;
      document.getElementById("fasremoteip6_id").required = true;
		}
	}
	
	window.onload=preselected;
	
	function preselected() {
		<?php
			echo "var oper_mode='".exec("uci get  opennds.@opennds[".$instance_no."].oper_mode")."';";
		?>
		if(oper_mode == "ipv4") {
			change_operation_mode("oper_ipv4");
		} else if (oper_mode == "ipv6") {
			change_operation_mode("oper_ipv6");
		} else if (oper_mode == "ipv46") {
			change_operation_mode("oper_ipv46");
		}
	}
</script>
<script>
$(document).ready(function() {
  $('#add_domain_button').click(function(e) {
    e.preventDefault();
    console.log("clicked add_domain_button");
    $('#id_walled_garden_domains').append('<br><input type="text" name="walled_garden[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
  });

  $('#id_walled_garden_domains').on("click", ".delete", function(e) {
    console.log("clicked domain_list_wrapper delete");
    e.preventDefault();
    $(this).parent('tr').remove();
  })

$('#add_ip_button').click(function(e) {
    e.preventDefault();
    console.log("clicked add_ip_button");
    $('#id_walled_garden_ips').append('<br><input type="text" name="walled_garden_ip[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
  });

  $('#id_walled_garden_ips').on("click", ".delete", function(e) {
    console.log("clicked ip_list_wrapper delete");
    e.preventDefault();
    $(this).parent('tr').remove();
  })

  $('#add_mac_button').click(function(e) {
    e.preventDefault();
    console.log("clicked add_mac_button");
    $('#id_trusted_macs').append('<br><input pattern="^([a-fA-F0-9]{2}:){5}[a-fA-F0-9]{2}$" type="text" name="trusted_macs[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a>');
  });

  $('#id_trusted_macs').on("click", ".delete", function(e) {
    e.preventDefault();
    $(this).parent('tr').remove();
  })

});
</script>
<?php endblock() ?>
