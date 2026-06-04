<?php include '/www/lib/sessioncheck.php' ?>

<head>
	<title>VRRP & HA</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
include '/www/lib/common.php';
include '/www/lib/vrrplib.php';

if(count($_POST) > 0) {
	$status = $_POST["status"];
    $instance_no = $_POST["instance_no"];
    $vrrp_state = $_POST["vrrp_state"];
    $vrrp_interface = $_POST["vrrp_interface"];
    $vrrp_router_id = $_POST["vrrp_router_id"];
    $virtual_ip = $_POST["virtual_ip"];
    $unicast_src_ip = $_POST["unicast_src_ip"];
    $vrrp_priority = $_POST["vrrp_priority"];
    $nopreempt = $_POST["nopreempt"];
    $unicast = $_POST["unicast"];
    $unicast_peer_ip = $_POST["unicast_peer_ip"];

	set_vrrp_ha($instance_no, $status, $vrrp_state, $vrrp_interface, $vrrp_router_id, $virtual_ip, $unicast_src_ip, $vrrp_priority, $nopreempt, $unicast_peer_ip, $unicast);
}
$instance_no="99";

if(count($_GET) > 0) {
  $instance_no = $_GET["instance"];
  $action = $_GET["action"];

  if ($action == "delete") {
    delete_vrrp_instance($instance_no);
    echo '<script>window.location.href = "/network/vrrp.php";</script>';
    exit;
  }
}

?>

<?php startblock('contentbar') ?>

<div class="main">
	<div class="main-content">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">VRRP & HA</h3>
							</div>
							<div class="panel-body">
								<div id="content1" class="content">
									<form autocomplete="off" enctype="multipart/form-data" id="vrrp_ha_form" action="vrrp.php" method="post">
                                    <input type="hidden" id="instance_id" name="instance_no" value="<?php if (count($_GET) > 0) { echo $instance_no; } else { echo "-1"; } ?>">
										<div>
											<table>
                                                <tr>
													<td>Status</td>
													<td>
														<label class="fancy-radio">
															<?php
															$vrrp_status = exec("uci get keepalived.@vrrp_instance[".$instance_no."].enabled");
															if ($vrrp_status == "1") {
																echo '<input name="status" value="1" checked="checked" type="radio" required>';
															} else {
																echo '<input name="status" value="1" type="radio" required>';
															}
															?>
															<span><i></i>Enabled</span>
														</label>
														<label class="fancy-radio">
															<?php
															if ($vrrp_status == "0") {
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
													<td>State</td>
                                                    <td>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                $state = exec("uci get keepalived.@vrrp_instance[".$instance_no."].state");
                                                                if ($state == "MASTER") {
                                                                        echo '<input name="vrrp_state" value="1" checked="checked" type="radio">';
                                                                } else {
                                                                        echo '<input name="vrrp_state" value="1" type="radio">';
                                                                }
                                                                ?>
                                                                <span><i></i>Master</span>
                                                        </label>
                                                        <label class="fancy-radio">
                                                                <?php
                                                                if ($state == "BACKUP") {
                                                                        echo '<input name="vrrp_state" value="0" checked="checked" type="radio">';
                                                                } else {
                                                                        echo '<input name="vrrp_state" value="0" type="radio">';
                                                                }
                                                                ?>
                                                                <span><i></i>Backup</span>
                                                        </label>
													</td>
												</tr>
												<tr>
													<td>Interface</td>
													<td>
														<select required name="vrrp_interface" id="id_vrrp_interface" class="form-control input-sm">
															<?php
                                                            $interface = exec("uci get keepalived.@vrrp_instance[".$instance_no."].virtual_ipaddress");
															foreach (get_configured_link_interfaces() as $intf) {
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
													<td>Virtual Router ID</td>
                                                    <td><input required type="text" id="vrrp_router_id_id" name="vrrp_router_id" class="form-control" value="<?php echo exec("uci get keepalived.@vrrp_instance[".$instance_no."].virtual_router_id"); ?>"></td>
												</tr>
                                                <tr>
													<td>Priority</td>
													<td><input required type="text" id="vrrp_priority" name="vrrp_priority" class="form-control" value="<?php echo exec("uci get keepalived.@vrrp_instance[".$instance_no."].priority"); ?>"></td>
												</tr>
												<tr>
													<td>No-Preempt</td>
													<td>
                                                        <label class="fancy-checkbox">
                                                        <?php
                                                        $nopreempt = exec("uci get keepalived.@vrrp_instance[".$instance_no."].nopreempt");
                                                        if ($nopreempt == "1") {
                                                            echo '<input name="nopreempt" checked=checked type="checkbox">';
                                                        } else {
                                                            echo '<input name="nopreempt" type="checkbox">';
                                                        }
                                                        ?>
                                                        <span></span>
                                                        </label>
                                                    </td>
                                                <tr>
													<td>Virtual IP</td>
													<td><input required type="text" id="virtual_ip_id" name="virtual_ip" class="form-control" value="<?php echo exec("uci get keepalived.@ipaddress[".$instance_no."].address"); ?>"></td>
												</tr>
                                                <tr>
													<td>Unicast only</td>
													<td>
                                                        <label class="fancy-checkbox">
                                                        <?php
                                                        $unicast = exec("uci get keepalived.@vrrp_instance[".$instance_no."].unicast");
                                                        if ($unicast == "1") {
                                                            echo '<input name="unicast" onclick="change_unicast()" checked=checked type="checkbox">';
                                                        } else {
                                                            echo '<input name="unicast" onclick="change_unicast()" type="checkbox">';
                                                        }
                                                        ?>
                                                        <span></span>
                                                        </label>
                                                    </td>
                                                </tr>
						<tr id="tr_unicast_src_ip">
													<td>Unicast SRC IP</td>
													<td><input required type="text" id="unicast_src_ip_id" name="unicast_src_ip" class="form-control" value="<?php echo exec("uci get keepalived.@vrrp_instance[".$instance_no."].unicast_src_ip"); ?>"></td>
												</tr>
                                                <tr id="tr_unicast_peer_ip">
													<td>Unicast PEER IP</td>
													<td><input required type="text" id="unicast_peer_ip_id" name="unicast_peer_ip" class="form-control" value="<?php echo exec("uci get keepalived.@vrrp_instance[".$instance_no."].unicast_peer"); ?>"></td>
												</tr>
											</table>
											<br/>
											<div class="row">
												<div class="col-md-6">
													<button type="submit" class="btn btn-primary">Save</button>
												</div>
												<div class="col-md-6">
													<button type="reset" class="btn btn-danger">Clear</button>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-8">
					<div class="panel">
						<div class="panel-body">
							<div class="panel-heading">
								<h3 class="panel-title">Current VRRP Interfaces</h3>
							</div>
							<div class="panel-body">
								<table class="table">
									<thead>
										<tr>
											<th>Interface</th>
											<th>Status</th>
                                            <th>State</th>
                                            <th>Virtual IP</th>
											<th>Router ID</th>
											<th>Priority</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php
                                        $instance_id = 0;
                                        $out = "";
                                        $ret = 99;
                                        exec("uci show keepalived.@vrrp_instance[".$instance_id."]" , $out, $ret);
										while ( $ret == 0 ) {
											echo "<tr>";
                                            echo "<td><kbd>".exec("uci get keepalived.@vrrp_instance[".$instance_id."].virtual_ipaddress")."</kbd></td>";
                                            echo "<td>".show_enabled_disabled(exec("uci get keepalived.@vrrp_instance[".$instance_id."].enabled"))."</td>";
                                            echo "<td>".strtoupper(exec("uci get keepalived.@vrrp_instance[".$instance_id."].state"))."</td>";
                                            echo "<td>".strtoupper(exec("uci get keepalived.@ipaddress[".$instance_id."].address"))."</td>";
                                            echo "<td>".strtoupper(exec("uci get keepalived.@vrrp_instance[".$instance_id."].virtual_router_id"))."</td>";
                                            echo "<td>".strtoupper(exec("uci get keepalived.@vrrp_instance[".$instance_id."].priority"))."</td>";
                                            echo '<td><a href="vrrp.php?action=edit&instance='.$instance_id.'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a href="vrrp.php?action=delete&instance='.$instance_id.'"><i class="fa fa-times"></i></a></td>';
                                            echo "</tr>";

                                            ++$instance_id;
                                            exec("uci show keepalived.@vrrp_instance[".$instance_id."]" , $out, $ret);
										}
											?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-3">
				    <div class="panel">
            				<div class="panel-heading">
                    				<h3 class="panel-title">VRRP HA Status</h3>
            				</div>
            				<div class="panel-body">
                    				<ul class="list-unstyled list-justify">
                            			<li>Configured State: 
                            				<?php $constate = exec("uci get keepalived.@vrrp_instance[0].state");
                                			if($constate == "MASTER") {
                                    				echo '<span id="con_state" class="label label-success">'.$constate.'</span>';
                                			} else {
                                    				echo '<span id="con_state" class="label label-danger">'.$constate.'</span>';
                                			}
                            				?>
                            			</li>
                            			<li>Current State: 
                            				<?php $curstate = exec("uci get keepalived.@global_defs[0].cur_state");
                                			if($curstate == "MASTER") {
                                    				echo '<span id="cur_state" class="label label-success">'.$curstate.'</span>';
                                			} else {
                                    				echo '<span id="cur_state" class="label label-danger">'.$curstate.'</span>';
                                			}
                            				?>
                            			</li>
                    				</ul>
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
<?php echo 'var unicast="'.exec("uci get keepalived.@vrrp_instance[".$instance_no."].unicast").'";'; ?>

if (unicast == "1"){
	$('#tr_unicast_src_ip').show();                                                                                  
        $('#tr_unicast_peer_ip').show();                                                                                                   
        document.getElementById('unicast_src_ip_id').setAttribute('required', 'required');                                   
        document.getElementById('unicast_peer_ip_id').setAttribute('required', 'required');  
}else{
	$('#tr_unicast_src_ip').hide();                                                                                 
        $('#tr_unicast_peer_ip').hide();                                                                     
        document.getElementById('unicast_src_ip_id').removeAttribute('required', 'required');                              
        document.getElementById('unicast_peer_ip_id').removeAttribute('required', 'required');  
}

function change_unicast() {
    var unicast_update = $("input[name='unicast']:checked").val();  
    if (unicast_update == "on") {
        $('#tr_unicast_src_ip').show();
	$('#tr_unicast_peer_ip').show();
	document.getElementById('unicast_src_ip_id').setAttribute('required', 'required');
        document.getElementById('unicast_peer_ip_id').setAttribute('required', 'required');
    } else {
        $('#tr_unicast_src_ip').hide();
	$('#tr_unicast_peer_ip').hide();
	document.getElementById('unicast_src_ip_id').removeAttribute('required', 'required');                                                                                                                                                    
        document.getElementById('unicast_peer_ip_id').removeAttribute('required', 'required');
    }
}
</script>