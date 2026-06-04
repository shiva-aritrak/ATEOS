<?php include '/www/lib/sessioncheck.php' ?>
<head>
    <title>Wireless | Settings</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/wirelesslib.php' ?>

<?php
$WIFI_2G = lookup_wifi_devices("radio0");
$WIFI_5G = lookup_wifi_devices("radio1");
if(count($_POST) > 0) {
    $wifi_mode = $_POST["wifi_mode"];
    $wifi_status = $_POST["wifi_status"];
    $ssid = $_POST["ssid"];
    $txpower = $_POST["txpower"];
    $channel = $_POST["channel"];
    $hwmode = $_POST["hwmode"];
    $isolate = $_POST["isolate"];
    $ssid_hidden = $_POST["ssid_hidden"];
    $chanbw = $_POST["chanbw"];
    $beacon_int = $_POST["beacon_int"];
    $rts = $_POST["rts"];
    $frag = $_POST["frag"];
    $encryption = $_POST["encryption"];
    $passkey = $_POST["passkey"];
    $lan_interface = $_POST["lan_interface"];
    $mac_filter_mode = $_POST["mac_filter_mode"];
    $mac_addresses = $_POST["mac_addresses"];
    $auth_server = $_POST["auth_server"];
    $auth_port = $_POST["auth_port"];
    $auth_secret = $_POST["auth_secret"];
    set_wireless_config($wifi_mode, $wifi_status, $ssid, $txpower, $channel, $hwmode, $isolate, $ssid_hidden, $chanbw, $beacon_int, $rts, $frag, $encryption, $passkey, $lan_interface, $mac_filter_mode, $mac_addresses, $auth_server, $auth_port, $auth_secret);
}

?>

<?php startblock('contentbar') ?>
<div class="main">
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container-fluid">
            <h3 class="page-title">Wireless LAN</h3>
            <div class="row">
		<?php if ($WIFI_2G): ?>
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="panel-heading">
                                <h3 class="panel-title">2.4GHz</h3>
                            </div>
                            <div class="panel-body">
                                <form enctype="multipart/form-data" action="wlan.php" method="post">
                                    <input type="hidden" name="wifi_mode" value="2.4">
                                    <table class="band2_wireless_table">
                                        <tr>
                                            <td>Status</td>
                                            <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $disabled = exec("uci get wireless.radio0.disabled");
                                                    if ($disabled == "0") {
                                                        echo '<input name="wifi_status" value="0" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="wifi_status" value="0" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Enabled</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($disabled == "1") {
                                                        echo '<input name="wifi_status" value="1" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="wifi_status" value="1" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Disabled</span>
                                                </label>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>SSID</td>
                                            <?php $ssid=exec("uci get wireless.default_radio0.ssid");
                                            if ($ssid)
                                            {
                                                echo "<td><input required class='form-control' type='text' name='ssid' value='".$ssid."'></td>";
                                            }
                                            else
                                            {
                                                echo "<td><input required class='form-control' type='text' name='ssid' value='AnexGate ACE'></td>";
                                            }
                                            ?>
                                        </tr>
                                        
                                        <tr>
											<td>Interface</td>
											<td>
												<select required name="lan_interface" id="id_lan_interface" class="form-control input-sm">
													<?php
                                                    $network = exec("uci get wireless.default_radio0.network");
													foreach (get_lan_interfaces() as $intf) {
														if(strtolower($network) == strtolower($intf)) {
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
                                            <td>Transmission Power (dBm)</td>
                                            <?php $txpower=exec("uci get wireless.default_radio0.txpower");
                                            if ($txpower)
                                            {
                                                echo'<td><input  class="form-control" type="number" name="txpower" min="0" max="140" value='.$txpower.'></td>';
                                            }
                                            else
                                            {
                                                echo'<td><input  class="form-control" type="number" name="txpower" min="0" max="140" value="20"></td>';
                                            }
                                            ?>
                                        </tr>
                                        
                                        
                                        <tr>
                                            <td>Channel</td>
                                            <td>
                                                <select required class="form-control" id="selectchannel" name="channel">
                                                    <?php
                                                    $channel = exec("uci get wireless.radio0.channel");
                                                    foreach ($WIRELESS_24_CHANNELS as $WIRELESS_24_CHANNEL) {
                                                        if ($WIRELESS_24_CHANNEL == $channel) {
                                                            echo '<option value="'.$WIRELESS_24_CHANNEL.'" selected="selected">'.ucfirst($WIRELESS_24_CHANNEL).'</option>';
                                                        } else {
                                                            echo '<option value="'.$WIRELESS_24_CHANNEL.'">'.ucfirst($WIRELESS_24_CHANNEL).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Hardware Mode</td>
                                            <td>
                                                <select required class="form-control" id="hwmode" name="hwmode">
                                                    <?php
                                                    $hwmode = exec("uci get wireless.radio0.hwmode");
                                                    foreach ($WIRELESS_24_HW_MODES as $WIRELESS_24_HW_MODE) {
                                                        if ($WIRELESS_24_HW_MODE == $hwmode) {
                                                            echo '<option value="'.$WIRELESS_24_HW_MODE.'" selected="selected">'.$WIRELESS_24_HW_MODE.'</option>';
                                                        } else {
                                                            echo '<option value="'.$WIRELESS_24_HW_MODE.'">'.$WIRELESS_24_HW_MODE.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>AP Isolation <br/></td>
                                            <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $isolate = exec("uci get wireless.default_radio0.isolate");
                                                    if ($isolate == "1") {
                                                        echo '<input name="isolate" value="1" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="isolate" value="1" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Enabled</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($isolate != "1") {
                                                        echo '<input name="isolate" value="0" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="isolate" value="0" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Disabled</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>SSID Hidden</td>
                                            <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $disabled = exec("uci get wireless.default_radio0.hidden");
                                                    if ($disabled == "1") {
                                                        echo '<input name="ssid_hidden" value="1" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="ssid_hidden" value="1" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Enabled</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($disabled != "1") {
                                                        echo '<input name="ssid_hidden" value="0" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="ssid_hidden" value="0" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Disabled</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Channel Width (MHz)</td>
                                            <td>
                                                <select required id="chw" class="form-control" name="chanbw">
                                                    <?php
                                                    $chanbw = exec("uci get wireless.default_radio0.chanbw");
                                                    foreach ($WIRELESS_CHANNEL_WIDTH as $CHANNEL_WIDTH) {
                                                        if ($CHANNEL_WIDTH == $chanbw) {
                                                            echo '<option value="'.$CHANNEL_WIDTH.'" selected="selected">'.$CHANNEL_WIDTH.'</option>';
                                                        }
                                                        else {
                                                            if ($chanbw == '' && $CHANNEL_WIDTH == "20") {
                                                                echo '<option value="'.$CHANNEL_WIDTH.'" selected="selected">'.$CHANNEL_WIDTH.'</option>';
                                                            } else {
                                                                echo '<option value="'.$CHANNEL_WIDTH.'">'.$CHANNEL_WIDTH.'</option>';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                       
                                        <tr>
                                            <td>Beacon Interval (ms)</td>
                                            <?php $beacon_int=exec("uci get wireless.default_radio0.beacon_int");
                                            if($beacon_int) {
                                                echo'<td><input required name="beacon_int" class="form-control" type="number" id="bid" min="15" max="65535"  value='.$beacon_int.'></td>';
                                            } else {
                                                echo'<td><input required name="beacon_int" class="form-control" type="number" id="bid" min="15" max="65535"  value="100"></td>';
                                            }
                                            ?>
                                        </tr>
                                        <tr>
                                            <td>CTS/RTS Threshold (Bytes)</td>
                                            <?php $rts=exec("uci get wireless.default_radio0.rts");
                                            if($rts) {
                                                echo'<td><input required name="rts" class="form-control" type="number" id="rts" min="0" max="2347"  value='.$rts.'></td>';
                                            } else {
                                                echo'<td><input required name="rts" class="form-control" type="number" id="rts" min="0" max="2347"  value="2347"></td>';
                                            }
                                            ?>
                                        </tr>
                                        
                                        <tr>
                                            <td>Fragmentation Threshold (Bytes)</td>
                                            <?php $frag=exec("uci get wireless.default_radio0.frag");
                                            if($frag) {
                                                echo'<td><input name="frag" class="form-control" type="number" id="frag" min="256" max="2346"  value='.$frag.'></td>';
                                            } else {
                                                echo'<td><input name="frag" class="form-control" type="number" id="frag" min="256" max="2346"  value="2346"></td>';
                                            }
                                            ?>
                                        </tr>
                                        <tr>
                                            <td>Security</td>
                                            <td>
                                            <select required class="form-control" name="encryption">
                                                    <?php
                                                    $encryption = exec("uci get wireless.default_radio0.encryption");
                                                    foreach ($WIRELESS_ENCRYPTION_CHOICES as $key => $value) {
                                                        if ($key == $encryption) {
                                                            echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
                                                        }
                                                        else {
                                                            echo '<option value="'.$key.'">'.$value.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Auth Server IP</td>
                                            <td>
                                                <?php $auth_server_ip=exec("uci get wireless.default_radio0.auth_server");
                                                echo '<input type="textfield" class="form-control"  id="auth_server_id" name="auth_server" value="'.$auth_server_ip.'" ></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Auth Server Port</td>
                                            <td>
                                                <?php $auth_server_port=exec("uci get wireless.default_radio0.auth_port");
                                                echo '<input type="textfield" class="form-control"  id="auth_port_id" name="auth_port" value="'.$auth_server_port.'" ></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Auth Server Secret</td>
                                            <td>
                                                <?php $auth_server_secret=exec("uci get wireless.default_radio0.auth_secret");
                                                echo '<input type="textfield" class="form-control"  id="auth_secret_id" name="auth_secret" value="'.$auth_server_secret.'" ></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Passphrase</td>
                                            <td>
                                                <?php $key=exec("uci get wireless.default_radio0.key");
                                                echo '<input type="textfield" class="form-control"  id="user_pass" name="passkey" value="'.$key.'" pattern="^[A-Za-z\d$@$!%*?&]{10,26}$" title="Must contain minimum 10 or maximum 26 characters" required></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                    
                                            <td>MAC Filtering Mode</td>
                                            <td>
                                            <select class="form-control" name="mac_filter_mode">
                                                    <?php
                                                    $macfilter = exec("uci get wireless.default_radio0.macfilter");
                                                    foreach ($WLAN_MAC_FILTERING_MODES as $key => $value) {
                                                        if ($key == $macfilter) {
                                                            echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
                                                        }
                                                        else {
                                                            echo '<option value="'.$key.'">'.$value.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
					<tr>
					    <tr></tr>
					    <td>MAC Addresses</td>
                                            <?php
                                            $mac_addresses = exec("uci get wireless.default_radio0.maclist");
                                            foreach (show_zone_networks($mac_addresses) as $mac_address) {
						echo '<tr></tr>';
                                                echo '<td></td><td><input type="text" name="mac_addresses[]" class="form-control" value="'.$mac_address.'"></td>';
                                            }
                                            ?>
                                        </tr>
                                    </table>
				    <td></td><td><br><br><button class="add_mac_band2" type="button"><i class="fa fa-plus"></i> Add MAC Address</button></td></br></br>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="reset" class="btn btn-danger">Reset</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
		<?php endif; ?>
                <?php if ($WIFI_5G): ?>
                <div class="col-md-6">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="panel-heading">
                                <h3 class="panel-title">5GHz</h3>
                            </div>
                            <div class="panel-body">
                                <form enctype="multipart/form-data" action="wlan.php" method="post">
                                    <input type="hidden" name="wifi_mode" value="5">
                                    <table class="band5_wireless_table">
                                        <tr>
                                            <td>Status</td>
                                            <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $disabled = exec("uci get wireless.radio1.disabled");
                                                    if ($disabled == "0") {
                                                        echo '<input name="wifi_status" value="0" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="wifi_status" value="0" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Enabled</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($disabled == "1") {
                                                        echo '<input name="wifi_status" value="1" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="wifi_status" value="1" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Disabled</span>
                                                </label>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>SSID</td>
                                            <?php $ssid=exec("uci get wireless.default_radio1.ssid");
                                            if ($ssid)
                                            {
                                                echo "<td><input required class='form-control' type='text' name='ssid' value='".$ssid."'></td>";
                                            }
                                            else
                                            {
                                                echo "<td><input required class='form-control' type='text' name='ssid' value='AnexGate ACE'></td>";
                                            }
                                            ?>
                                        </tr>

                                        <tr>
											<td>Interface</td>
											<td>
												<select required name="lan_interface" id="id_lan_interface" class="form-control input-sm">
													<?php
                                                    $network = exec("uci get wireless.default_radio1.network");
													foreach (get_lan_interfaces() as $intf) {
														if(strtolower($network) == strtolower($intf)) {
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
                                            <td>Transmission Power (dBm)</td>
                                            <?php $txpower=exec("uci get wireless.default_radio1.txpower");
                                            if ($txpower)
                                            {
                                                echo'<td><input  class="form-control" type="number" name="txpower" min="0" max="140" value='.$txpower.'></td>';
                                            }
                                            else
                                            {
                                                echo'<td><input  class="form-control" type="number" name="txpower" min="0" max="140" value="20"></td>';
                                            }
                                            ?>
                                        </tr>
                                        
                                        <tr>
                                            <td>Channel</td>
                                            <td>
                                                <select required class="form-control" id="selectchannel" name="channel">
                                                    <?php
                                                    $channel = exec("uci get wireless.radio1.channel");
                                                    foreach ($WIRELESS_50_CHANNELS as $WIRELESS_50_CHANNEL) {
                                                        if ($WIRELESS_50_CHANNEL == $channel) {
                                                            echo '<option value="'.$WIRELESS_50_CHANNEL.'" selected="selected">'.ucfirst($WIRELESS_50_CHANNEL).'</option>';
                                                        } else {
                                                            echo '<option value="'.$WIRELESS_50_CHANNEL.'">'.ucfirst($WIRELESS_50_CHANNEL).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Hardware Mode</td>
                                            <td>
                                                <select required class="form-control" id="hwmode" name="hwmode">
                                                    <?php
                                                    $hwmode = exec("uci get wireless.radio1.hwmode");
                                                    foreach ($WIRELESS_50_HW_MODES as $WIRELESS_50_HW_MODE) {
                                                        if ($WIRELESS_50_HW_MODE == $hwmode) {
                                                            echo '<option value="'.$WIRELESS_50_HW_MODE.'" selected="selected">'.$WIRELESS_50_HW_MODE.'</option>';
                                                        } else {
                                                            echo '<option value="'.$WIRELESS_50_HW_MODE.'">'.$WIRELESS_50_HW_MODE.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>AP Isolation <br/></td>
                                            <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $isolate = exec("uci get wireless.default_radio1.isolate");
                                                    if ($isolate == "1") {
                                                        echo '<input name="isolate" value="1" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="isolate" value="1" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Enabled</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($isolate != "1") {
                                                        echo '<input name="isolate" value="0" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="isolate" value="0" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Disabled</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>SSID Hidden</td>
                                            <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $hidden = exec("uci get wireless.default_radio1.hidden");
                                                    if ($hidden == "1") {
                                                        echo '<input name="ssid_hidden" value="1" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="ssid_hidden" value="1" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Enabled</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($hidden != "1") {
                                                        echo '<input name="ssid_hidden" value="0" checked="checked" type="radio">';
                                                    } else {
                                                        echo '<input name="ssid_hidden" value="0" type="radio">';
                                                    }
                                                    ?>
                                                    <span><i></i>Disabled</span>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Channel Width (MHz)</td>
                                            <td>
                                                <select required id="chw" class="form-control" name="chanbw">
                                                    <?php
                                                    $chanbw = exec("uci get wireless.default_radio1.chanbw");
                                                    foreach ($WIRELESS_50_CHANNEL_WIDTH as $CHANNEL_WIDTH) {
                                                        if ($CHANNEL_WIDTH == $chanbw) {
                                                            echo '<option value="'.$CHANNEL_WIDTH.'" selected="selected">'.$CHANNEL_WIDTH.'</option>';
                                                        }
                                                        else {
                                                            if ($chanbw == '' && $CHANNEL_WIDTH == "20") {
                                                                echo '<option value="'.$CHANNEL_WIDTH.'" selected="selected">'.$CHANNEL_WIDTH.'</option>';
                                                            } else {
                                                                echo '<option value="'.$CHANNEL_WIDTH.'">'.$CHANNEL_WIDTH.'</option>';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>Beacon Interval (ms)</td>
                                            <?php $beacon_int=exec("uci get wireless.default_radio1.beacon_int");
                                            if($beacon_int) {
                                                echo'<td><input required name="beacon_int" class="form-control" type="number" id="bid" min="15" max="65535"  value='.$beacon_int.'></td>';
                                            } else {
                                                echo'<td><input required name="beacon_int" class="form-control" type="number" id="bid" min="15" max="65535"  value="100"></td>';
                                            }
                                            ?>
                                            
                                        </tr>
                                        <tr>
                                            <td>CTS/RTS Threshold (Bytes)</td>
                                            <?php $rts=exec("uci get wireless.default_radio1.rts");
                                            if($rts) {
                                                echo'<td><input required name="rts" class="form-control" type="number" id="rts" min="0" max="2347"  value='.$rts.'></td>';
                                            } else {
                                                echo'<td><input required name="rts" class="form-control" type="number" id="rts" min="0" max="2347"  value="2347"></td>';
                                            }
                                            ?>
                                        </tr>
                                        
                                        <tr>
                                            <td>Fragmentation Threshold (Bytes)</td>
                                            <?php $frag=exec("uci get wireless.default_radio1.frag");
                                            if($frag) {
                                                echo'<td><input name="frag" class="form-control" type="number" id="frag" min="256" max="2346"  value='.$frag.'></td>';
                                            } else {
                                                echo'<td><input name="frag" class="form-control" type="number" id="frag" min="256" max="2346"  value="2346"></td>';
                                            }
                                            ?>
                                        </tr>

                                        <tr>
                                            <td>Security</td>
                                            <td>
                                            <select required class="form-control" name="encryption">
                                                    <?php
                                                    $encryption = exec("uci get wireless.default_radio0.encryption");
                                                    foreach ($WIRELESS_ENCRYPTION_CHOICES as $key => $value) {
                                                        if ($key == $encryption) {
                                                            echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
                                                        }
                                                        else {
                                                            echo '<option value="'.$key.'">'.$value.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Auth Server IP</td>
                                            <td>
                                                <?php $auth_server_ip=exec("uci get wireless.default_radio1.auth_server");
                                                echo '<input type="textfield" class="form-control"  id="auth_server_id" name="auth_server" value="'.$auth_server_ip.'" ></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Auth Server Port</td>
                                            <td>
                                                <?php $auth_server_port=exec("uci get wireless.default_radio1.auth_port");
                                                echo '<input type="textfield" class="form-control"  id="auth_port_id" name="auth_port" value="'.$auth_server_port.'" ></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Auth Server Secret</td>
                                            <td>
                                                <?php $auth_server_secret=exec("uci get wireless.default_radio1.auth_secret");
                                                echo '<input type="textfield" class="form-control"  id="auth_secret_id" name="auth_secret" value="'.$auth_server_secret.'" ></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Passphrase</td>
                                            <td>
                                                <?php $key=exec("uci get wireless.default_radio1.key");
                                                echo '<input type="textfield" class="form-control"  id="user_pass" name="passkey" value="'.$key.'" pattern="^[A-Za-z\d$@$!%*?&]{10,26}$" title="Must contain minimum 10 or maximum 26 characters" required></td>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>MAC Filtering Mode</td>
                                            <td>
                                            <select class="form-control" name="mac_filter_mode">
                                                    <?php
                                                    $macfilter = exec("uci get wireless.default_radio1.macfilter");
                                                    foreach ($WLAN_MAC_FILTERING_MODES as $key => $value) {
                                                        if ($key == $macfilter) {
                                                            echo '<option value="'.$key.'" selected="selected">'.$value.'</option>';
                                                        }
                                                        else {
                                                            echo '<option value="'.$key.'">'.$value.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
					    <td>MAC Addresses</td>
                                            <?php
                                            $mac_addresses = exec("uci get wireless.default_radio1.maclist");
                                            foreach (show_zone_networks($mac_addresses) as $mac_address) {
						echo '<tr></tr>';
                                                echo '<td></td><td><input type="text" name="mac_addresses[]" class="form-control" value="'.$mac_address.'"></td>';
                                            }
                                            ?>
                                        </tr>
                                    </table>
				    <td></td><td><br><br><button class="add_mac_band5" type="button"><i class="fa fa-plus"></i> Add MAC Address</button></td></br></br>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="reset" class="btn btn-danger">Reset</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
		<?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endblock() ?>


<?php startblock('scriptblock') ?>

<script>
$(document).ready(function() {
  var band2_wireless_table_wrapper = $(".band2_wireless_table");
  var add_mac_band2_button = $(".add_mac_band2");

  $(add_mac_band2_button).click(function(e) {
    e.preventDefault();
    $(band2_wireless_table_wrapper).append('<tr><td></td><td><input type="text" name="mac_addresses[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a></td></tr>');
  });

  $(band2_wireless_table_wrapper).on("click", ".delete", function(e) {
    e.preventDefault();
    $(this).parent('tr').remove();
  });

  var band5_wireless_table_wrapper = $(".band5_wireless_table");
  var add_mac_band5_button = $(".add_mac_band5");

  $(add_mac_band5_button).click(function(e) {
    e.preventDefault();
    $(band5_wireless_table_wrapper).append('<tr><td></td><td><input type="text" name="mac_addresses[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a></td></tr>');
  });

  $(band5_wireless_table_wrapper).on("click", ".delete", function(e) {
    e.preventDefault();
    $(this).parent('tr').remove();
  });

});
</script>
<?php endblock() ?>

