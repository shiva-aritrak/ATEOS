<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>AnexSpot | Users</title>
</head>

<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/hotspot.php' ?>

<?php

function getNiceDuration($durationInSeconds) {
    $duration = '';
    $days = floor($durationInSeconds / 86400);
    $durationInSeconds -= $days * 86400;
    $hours = floor($durationInSeconds / 3600);
    $durationInSeconds -= $hours * 3600;
    $minutes = floor($durationInSeconds / 60);
    $seconds = $durationInSeconds - $minutes * 60;
  
    if($days > 0) {
      $duration .= $days . ' Days';
    }
    if($hours > 0) {
      $duration .= ' ' . $hours . ' Hours';
    }
    if($minutes > 0) {
      $duration .= ' ' . $minutes . ' Minutes';
    }
    if($seconds > 0) {
      $duration .= ' ' . $seconds . ' Seconds';
    }
    return $duration;
}

function formatBytes($bytes, $precision = 2) { 
    $bytes = $bytes*1024;
  
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
  
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
  
    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    $bytes /= (1 << (10 * $pow)); 
  
    return round($bytes, $precision) . ' ' . $units[$pow]; 
  } 

  
$instance_no="99";

if(count($_GET) > 0) {
  $instance_no = $_GET["instance"];
  $action = $_GET["action"];

  if ($action == "logout") {
    $ip = $_GET["ip"];
    logout_anexspot_user($instance_no, $ip);
    echo '<script>window.location.href = "users.php?instance='.$instance_no.'";</script>';
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
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">User Summary</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table table-condensed">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>MAC Address</th>
                                        <th>IP Address</th>
                                        <th>Authenticated Status</th>
                                        <th>Session ID</th>
                                        <th>Session Duration</th>
                                        <th>Download / Limit</th>
                                        <th>Upload / Limit </th>
                                        <th>Download Bandwidth (bps)</th>
                                        <th>Upload Bandwidth (bps)</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = 0;
                                    $out = array();
                                    exec("ndsctl -s /tmp/sock".$instance_no.".sock json", $out, $ret);
                                    $json_out = implode("", $out);
                                    $clients = json_decode($json_out, true);

                                    $counter = 1;
                                    if ($clients["clients"]) {
                                        foreach ($clients["clients"] as $user_mac => $user) {
                                                echo '<tr>';
                                                echo '<td>'.$counter.'</td>';
                                                echo '<td><samp>'.$user_mac.'</samp></td>';
                                                echo '<td><samp>'.$user["ip"].'</samp></td>';
                                                if ($user["state"] == 'Authenticated') {
                                                    echo '<td><span class="label label-primary">Authenticated</span></td>';
                                                } else if ($user["state"] == "Preauthenticated") {
                                                    echo '<td><span class="label label-danger">Preauthenticated</span></td>';
                                                } else {
                                                    echo '<td><span class="label label-danger">Not Authenticated</span></td>';
                                                }
                                                echo '<td>'.$user["custom"].'</td>';
						if ($user["session_end"] == 'null') {
							echo '<td>Unlimited</td>';
						} else {
                                                	echo '<td>'.getNiceDuration($user["session_end"] - $user["session_start"]).'</td>';
						}
                                                echo '<td>'.formatBytes($user["download_this_session"]).' / '.formatBytes($user["download_quota"]).'</td>';
                                                echo '<td>'.formatBytes($user["upload_this_session"]).' / '.formatBytes($user["upload_quota"]).'</td>';
                                                echo '<td>'.$user["download_rate_limit_threshold"].'</td>';
                                                echo '<td>'.$user["upload_rate_limit_threshold"].'</td>';
                                                echo '<td><a href="users.php?instance='.$instance_no.'&action=logout&ip='.$user["ip"].'"><i class="fa fa-sign-out"></i></a></td>';
                                                echo '</tr>';
                                            $counter = $counter + 1;
                                        }
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
<?php endblock() ?>

