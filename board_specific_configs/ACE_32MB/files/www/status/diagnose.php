<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Diagnose</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php

$raw_iface = "anex";
if(count($_GET) > 0) {
  $iface = $_GET["link"];
  if ($iface == "wan1") {
    $raw_iface = "eth0.2";
  } else if ($iface == "wan2") {
    $raw_iface = "eth0.3";
  } else if ($iface == "lan") {
    $raw_iface = "eth0.1";
  } else if ($iface == "usb0") {
    $out = "";
    $ret = 1;
    exec("uci show network.3g", $out, $ret);
    if ($ret == 0) {
      $raw_iface = "3g-3g";
    }
    exec("uci show network.usb0", $out, $ret);
    if ($ret == 0) {
      $raw_iface = "usb0";
    }
  } else {
    $raw_iface = $iface;
  }
}

?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">
                  <?php
                  echo strtoupper($iface)." Diagnosis";
                  ?>
                </h3>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">IP Connectivity Check</h3>
              </div>
              <div class="panel-body">
                <?php
                echo '<pre>';
                $output = "";

                if(count($_GET) > 0) {
                  exec("timeout -t 10 ping -I ".$raw_iface." -c 5 8.8.8.8", $output, $ip_ping_ret);
                  foreach ($output as $out_line) {
                    echo $out_line."\n";
                  }
                }
                echo '</pre>';
                if ($ip_ping_ret) {
                  echo "<p>Could not connect via an IP Address</p>";
                } else {
                  echo "<p>IP Connectivity Check Successful</p>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Host Connectivity Check</h3>
              </div>
              <div class="panel-body">
                <?php
                $output = "";
                echo '<pre>';
                if(count($_GET) > 0) {
                  exec("timeout -t 10 ping -I ".$raw_iface." -c 5 google.com", $output, $host_ping_ret);
                  foreach ($output as $out_line) {
                    echo $out_line."\n";
                  }
                }
                echo '</pre>';
                if ($host_ping_ret) {
                  echo "<p>Could not connect via an Hostname</p>";
                } else {
                  echo "<p>Host Connectivity Check Successful</p>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">DNS Lookup Check</h3>
              </div>
              <div class="panel-body">
                <?php
                $output = "";
                echo '<pre>';

                if(count($_GET) > 0) {
                  exec("timeout -t 5 nslookup google.com", $output, $dns_lookup_ret);
                  foreach ($output as $out_line) {
                    echo trim($out_line)."\n";
                  }
                }
                echo '</pre>';
                if ($dns_lookup_ret) {
                  echo "<p>Could not resolve a domain name</p>";
                } else {
                  echo "<p>DNS Lookup Successful</p>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Summary</h3>
              </div>
              <div class="panel-body">
                <?php
                if ($ip_ping_ret && $host_ping_ret) {
                  echo "<p>No IP connectivity</p>";
                } else if ($ip_ping_ret && !$host_ping_ret) {
                  echo "<p>Name resolution is failing. IP connectivity is established but DNS lookups are failing. Check your DNS settings</p>";
                } else if ($dns_lookup_ret) {
                  echo "<p>DNS resolution is failing. Unable to lookup domain names</p>";
                } else if (!$ip_ping_ret && !$host_ping_ret && !$dns_lookup_ret) {
                  echo "<p>Connectivity check successful and working</p>";
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
