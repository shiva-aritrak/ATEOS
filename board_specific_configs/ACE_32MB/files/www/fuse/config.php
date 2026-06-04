<?php include '/www/lib/sessioncheck.php' ?>
<head>
<title>AnexFuse | Configuration</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/fuse.php' ?>

<?php
if(count($_POST) > 0) {
    $status = $_POST["status"];
    $fuse_id = $_POST["fuse_id"];
    $fuse_server_address = $_POST["fuse_server_address"];
    $fuse_interfaces = $_POST["fuse_interfaces"];
    $local_source_interface = $_POST["local_source_interface"];
    $fu_def_internet = $_POST["fu_def_internet"];
    $priority = $_POST["priority"];
    $transport_layer = $_POST["transport_layer"];
    $port_name = $_POST["port_name"];
    $vlan_id = $_POST["vlan_id"];    
    	
       
 
    asort($priority);
    $same_priority = false;
    if ($fu_def_internet == "0") {
        if ( count(array_unique(array_filter($priority))) < count(array_filter($priority)) ) {
            $same_priority = true;
        }
    }

    if (!$same_priority) {
        set_fuse_config($status, $fuse_id, $fuse_server_address, $fuse_interfaces, $local_source_interface, $fu_def_internet, $priority, $transport_layer, $port_name, $vlan_id);
    }
}

$configured_fuse_interfaces = get_fuse_interfaces();
$fuse_interface_status = get_fuse_interface_status();

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
                                    <h3 class="panel-title">AnexFuse</h3>
                                </div>
                                <div class="panel-body">
                                    <?php
				    			    
                                    if ($same_priority) {
                                    echo '<div class="alert alert-warning alert-dismissible" role="alert">';
                                    echo '  <i class="fa fa-warning"></i> Alert! Cannot have equal priority in Failover order</p>';
                                    echo '</div>';
                                    }
                                    ?>
                                    <form autocomplete="off" enctype="multipart/form-data" id="fuse_form" action="config.php" method="post">
                                        <table>
                                            <tr>
                                                <td>Status</td>
                                                <td>
                                                    <label class="fancy-radio">
                                                        <?php
                                                        $fuse_status = exec("uci get fuse.globals.status");
                                                        if ($fuse_status == "1") {
                                                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                                                        } else {
                                                            echo '<input name="status" value="1" type="radio" required>';
                                                        }
                                                        ?>
                                                        <span><i></i>Enabled</span>
                                                    </label>
                                                    <label class="fancy-radio">
                                                        <?php
                                                        if ($fuse_status == "0") {
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
                                                <td>FUSE ID</td>
                                                <td><input required type="text" id="fuse_id_id" name="fuse_id"  pattern="^[A-Za-z0-9-]{2,26}$" class="form-control" value="<?php  echo exec("uci get fuse.globals.fuse_id");?>"></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>FUSE Server Address</td>
                                                <td><input required type="text" id="fuse_server_address_id" name="fuse_server_address"  class="form-control" value="<?php  echo exec("uci get fuse.globals.fuse_server_address");?>"></td>
                                            </tr>
                                            
                                            <tr>
                                                <td>FUSE Interfaces</td>
                                                <td>
                                                    <select required name="fuse_interfaces[]" multiple class="form-control input-sm">
                                                        <?php
                                                        $wan_interface = exec("uci get fuse.globals.wan_interface");
                                                        foreach ($configured_fuse_interfaces as $intf) {
                                                            if (in_array($intf, show_zone_networks($wan_interface))) {
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
                                                <td>Router Outgoing Interface</td>
                                                <td>
                                                    <select required name="local_source_interface" class="form-control input-sm">
                                                    <?php
                                                    $local_source = exec("uci get fuse.globals.local_source");
                                                    echo '<option value="none" selected>Default Routing</option>';
                                                    foreach (get_configured_interfaces() as $intf) {
                                                        if ($intf == $local_source) {
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
                                                <Td>Route all Outgoing Traffic</td>
                                                <td>
                                                <label class="fancy-radio">
                                                    <?php
                                                    $fu_def_internet = exec("uci get fuse.globals.fu_def_internet");
                                                    if ($fu_def_internet == "1") {
                                                    echo '<input id="id_fu_def_int_fuse" name="fu_def_internet" onclick="change_internet_via()" value="1" checked="checked" type="radio" required>';
                                                    } else {
                                                    echo '<input id="id_fu_def_int_fuse" name="fu_def_internet" onclick="change_internet_via()" value="1" type="radio" required>';
                                                    }
                                                    ?>
                                                    <span><i></i>via FUSE</span>
                                                </label>
                                                <label class="fancy-radio">
                                                    <?php
                                                    if ($fu_def_internet == "0") {
                                                    echo '<input id="id_fu_def_int_fail" name="fu_def_internet" onclick="change_internet_via()" value="0" checked="checked" type="radio" required>';
                                                    } else {
                                                    echo '<input id="id_fu_def_int_fail" name="fu_def_internet" onclick="change_internet_via()" value="0" type="radio" required>';
                                                    }
                                                    ?>
                                                    <span><i></i>via WAN and Failover to FUSE</span>
                                                </label>
                                                </td>
                                            </tr>
					    <tr>
                                                <td>Fuse Transport Layer</td>
                                                <td>
                                                    <label class="fancy-radio">
                                                        <?php
                                                        $fuse_transport_layer = exec("uci get fuse.globals.transport_layer");
                                                        if ($fuse_transport_layer == "1") {
                                                            echo '<input name="transport_layer" onclick="change_transport_layer()" value="1" checked="checked" type="radio" required>';
                                                        } else {
                                                            echo '<input name="transport_layer" onclick="change_transport_layer()" value="1" type="radio" required>';
                                                        }
                                                        ?>
                                                        <span><i></i>Layer3</span>
                                                    </label>
                                                    <label class="fancy-radio">
                                                        <?php
                                                        if ($fuse_transport_layer == "0") {
                                                            echo '<input name="transport_layer" onclick="change_transport_layer()" value="0" checked="checked" type="radio" required>';
                                                        } else {
                                                            echo '<input name="transport_layer" onclick="change_transport_layer()" value="0" type="radio" required>';
                                                        }
                                                        ?>
                                                        <span><i></i>Layer2</span>
                                                    </label>
                                                </td>
                                            </tr>
					    
					    <table id="id_fuse_layer2_port">
					    	<tr>
                                                	<td>Bridge Port</td>
                                                	<td style='padding-left: 100px'>
                                                    		<select required name="port_name" id="id_port_name" onchange="set_interface_name()" class="form-control input-sm">
                                                    		<?php
								$port_num = exec("uci get fuse.globals.port_name");
                                                         	foreach (get_network_devices() as $intf) {
                                                         		if(strtolower($port_num) == strtolower($intf)) {
                                                              	      		echo '<option onclick="set_interface_name()" selected="selected" value="'.$intf.'">'.strtoupper($intf).'</option>';
                                                         		} else {
                                                        	      		echo '<option onclick="set_interface_name()" value="'.$intf.'">'.strtoupper($intf).'</option>';
                                                     	    		}
                                                         	}
						    		?>
	                                                    	</select>
                                                 	</td>
                                            	   </tr>
						</tr>
						<tr>
                       			    		<td>VLAN ID</td>
                       			    		<td style='padding-left: 100px'><input type="number"  name="vlan_id" min="1" max="4094" id="id_vlan_id" class="form-control" value="<?php echo exec("uci get fuse.globals.vlan_id"); ?>" ></td>
               					</tr>
                                        	<tr>
                                             </table>
                                        </table>
                                        <table id="id_failover_routing_mode_row">
                                            <tr>
                                                <td colspan="2">Set Priority Order<td>
                                            </tr>
                                            <?php
                                            $wan_interface = exec("uci get fuse.globals.wan_interface");
                                            $fu_failover_int = exec("uci get fuse.globals.fu_failover_int");
                                            foreach ($configured_fuse_interfaces as $all_fuse_interface) { 
                                                $priority = array_search($all_fuse_interface, show_zone_networks($fu_failover_int));
                                                if (in_array($all_fuse_interface, show_zone_networks($wan_interface))) {
                                                    continue;
                                                } else if (in_array($all_fuse_interface, show_zone_networks($fu_failover_int))) {
                                                    echo "<tr>";
                                                    echo "<td>".strtoupper($all_fuse_interface)." Priority</td>";
                                                    echo "<td><input type='number' id='".$all_fuse_interface."_priority_id' name='priority[".$all_fuse_interface."]' class='form-control' value=".($priority+1)."></td>";
                                                    echo "</tr>";
                                                } else {
                                                    echo "<tr>";
                                                    echo "<td>".strtoupper($all_fuse_interface)." Priority</td>";
                                                    echo "<td><input type='number' id='".$all_fuse_interface."_priority_id' name='priority[".$all_fuse_interface."]' class='form-control'></td>";
                                                    echo "</tr>";
                                                }
                                            }
                                            $fuse_priority = array_search("bond0", show_zone_networks($fu_failover_int));
                                            echo "<tr>";
                                            echo "<td>FUSE Interface Priority</td>";
                                            echo "<td style='padding-left: 25px'><input type='number' id='bond0_priority_id' name='priority[bond0]' class='form-control' value=".($fuse_priority+1)."></td>";
                                            echo "</tr>";
                                            ?>
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
                        <h3 class="panel-title">AnexFUSE Configuration</h3>
                    </div>
                    <div class="panel-body no-padding">
                        <table class="table">
                        <tbody>
                            <tr>
                                <td>Configuration Sync</td>
                                <td>
                                    <?php
                                        $sync_status = exec("uci get fuse.globals.success");
                                        if ($sync_status == "1") {
                                            echo "Success";
                                        } else {
                                            echo "Error";
                                        }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Mode</td>
                                <td> 
                                    <?php
                                        $fuse_mode = exec("uci get fuse.globals.fuse_mode");
                                        if ($fuse_mode == "0") {
                                            echo "Combined Throughput";
                                        } else if ($fuse_mode == "4") {
                                            echo "Master Slave";
                                        } else if ($fuse_mode == "3") {
                                            echo "High Fault Tolerance";
                                        } else {
                                            echo "Unknown";
                                        }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Tunnel Mode</td>
                                <td> 
                                    <?php
                                        $fuse_mode = exec("uci get fuse.globals.split_tunnel");
                                        if ($fuse_mode == "1") {
                                            echo "Split Tunnel";
                                        } else if ($fuse_mode == "0") {
                                            echo "Full Tunnel";
                                        }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>FUSE Overlay Network</td>
                                <td>
                                    <samp>
                                    <?php
					$fuse_network_layer= exec("uci get fuse.globals.transport_layer");
                                        
					if ($fuse_network_layer == "1") {
                                            echo exec("uci get fuse.globals.bond_client_network");
                                        } else if ($fuse_mode == "0") {
                                            echo "Layer 2";
                                        }

                                    ?>
                                    </samp>
                                </td>
                            </tr>
			                <tr>
                                <td>FUSE Master Status</td>
                                <td>
                                    <samp>
                                    <?php
					$fuse_network_layer= exec("uci get fuse.globals.transport_layer");
					$Master_status = exec("mwan3 interfaces | grep bond0 | cut -d ' ' -f 5");
					if ($fuse_network_layer == "1")
					{
                                        
                                        	if($Master_status == "online") {
                                            		echo '<span class="label label-success">Online</span>';
                                        	} else if($Master_status == "offline"){
                                            		echo '<span class="label label-danger">Offline</span>';
                                        	} else if($Master_status == "error"){
                                            		echo '<span class="label label-danger">Error</span>';
                                        	} else if($Master_status == "unknown"){
                                                	echo '<span class="label label-danger">Unknown</span>';
                                        	}
					}
					else
					{
						$tx= exec("cat /sys/class/net/bond0/statistics/tx_bytes");
                                                $rx= exec("cat /sys/class/net/bond0/statistics/rx_bytes");
                                                $prev_tx= exec("cat /tmp/bond_tx");
                                                $prev_rx= exec("cat /tmp/bond_rx");
                                                if($tx > $prev_tx && $rx > $prev_rx) {
                                                        echo '<span class="label label-success">Online</span>';
                                                } else {
                                                        echo '<span class="label label-danger">Offline</span>';
                                                }
                                                exec("echo $tx > /tmp/bond_tx");
                                                exec("echo $rx > /tmp/bond_rx");
					}
                                    ?>
                                    </samp>
                                </td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                    
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                    <div class="panel-heading">
                        <h3 class="panel-title">FUSE Interfaces <div class="pull-right"><a href="/fuse/interface.php"><i class="fa fa-2x fa-plus"></i></a></div></h3>
                    </div>
                    <div id="rules_div_id" class="content">
                        <table class="table">
                        <thead>
                            <tr>
                            <th>Interface</th>
                            <th>Connectivity</br>Status</th>
                            <th>Interface</br>Status</th>
                            <th>Ping IPs</th>
                            <th>Min</br>Responses</th>
                            <th>Min Ping</br>Responses</th>
                            <th>Ping</br>Timeout</th>
                            <th>Ping</br>Interval</th>
                            <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($configured_fuse_interfaces as $configured_fuse_interface) {
                            echo '<tr>';
                            echo '<td>'.strtoupper($configured_fuse_interface).'</td>';
                            if ($fuse_interface_status[$configured_fuse_interface] == "online") {
                                echo '<td><span class="label label-success" id="'.$intf.'_status">'.ucfirst($fuse_interface_status[$configured_fuse_interface]).'</span></td>';
                            } else {
                                echo '<td><span class="label label-danger" id="'.$intf.'_status">'.ucfirst($fuse_interface_status[$configured_fuse_interface]).'</span></td>';
                            }
                            echo '<td>'.show_enabled_disabled(exec("uci get fuse.".$configured_fuse_interface.".enabled")).'</td>';
                            echo '<td>';
                            foreach (show_zone_networks(exec("uci get fuse.".$configured_fuse_interface.".track_ip")) as $track_ip) {
                                echo ''.$track_ip.'</br>';
                            }
                            echo '</td>';
                            echo '<td>'.exec("uci get fuse.".$configured_fuse_interface.".reliability").'</td>';
                            echo '<td>'.exec("uci get fuse.".$configured_fuse_interface.".count").'</td>';
                            echo '<td>'.exec("uci get fuse.".$configured_fuse_interface.".timeout").'</td>';
                            echo '<td>'.exec("uci get fuse.".$configured_fuse_interface.".interval").'</td>';
                            echo '<td><a href="/fuse/interface.php?interface='.$configured_fuse_interface.'"><i class="fa fa-edit"></i></a> &nbsp; &nbsp; <a href="/fuse/interface.php?action=delete&interface='.$configured_fuse_interface.'"><i class="fa fa-times"></i></a></td>';
                            echo '</tr>';
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
<?php endblock() ?>

<script type="text/javascript">
<?php echo 'var internet_via="'.exec("uci get fuse.globals.fu_def_internet").'";'; ?>
if (internet_via == "1") {
    $('#id_failover_routing_mode_row').hide();
} else if (internet_via == "0") {
    $('#id_failover_routing_mode_row').show();
}

function change_internet_via() {
    var internet_via_update = $("input[name='fu_def_internet']:checked").val();
    if (internet_via_update == "1") {
        $('#id_failover_routing_mode_row').hide();
    } else if (internet_via_update == "0") {
        $('#id_failover_routing_mode_row').show();
    }
}
</script>

<script type="text/javascript">
<?php echo 'var fu_transport_layer="'.exec("uci get fuse.globals.transport_layer").'";'; ?>
if (fu_transport_layer == "1") {
    $('#id_fuse_layer2_port').hide();
} else if (fu_transport_layer == "0") {
    $('#id_fuse_layer2_port').show();
}

function change_transport_layer() {
    var transport_layer_update = $("input[name='transport_layer']:checked").val();
    if (transport_layer_update == "1") {
        $('#id_fuse_layer2_port').hide();
    } else if (transport_layer_update == "0") {
        $('#id_fuse_layer2_port').show();
    }
}
</script>
