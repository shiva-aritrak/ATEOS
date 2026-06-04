<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | Services</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/system.php' ?>

<?php
if(count($_GET) > 0) {
  $service = $_GET["service"];
  if ($service == "ipsec") {
    exec("/bin/kill -9 $(/bin/pidof charon)");
    exec("/bin/kill -9 $(/bin/pidof starter)");
    exec("/etc/init.d/ipsec restart &");
    exec("/usr/sbin/ipsec restart &");
  } else if ($service == "network") {
    exec("/etc/init.d/network restart &");
  } else if ($service == "sslvpn") {
    exec("/etc/init.d/openvpn restart &");
  } else if ($service == "dhcp") {
    exec("/etc/init.d/dnsmasq restart &");
  } else if ($service == "loadbalancer") {
    exec("mwan3 restart &");
  } else if ($service == "firewall") {
    exec("/etc/init.d/firewall restart &");
  } else if ($service == "qos") {
    exec("/etc/init.d/qos restart &");
  } else if ($service == "dashboard") {
    exec("/etc/init.d/uhttpd restart &");
  } else if ($service == "anexconnect") {
    exec("/etc/init.d/autossh restart &");
  } else if ($service == "anexfuse") {
    exec("/etc/init.d/fuse restart &");
  } else if ($service == "wireless") {
    exec("wifi &");
  } else if ($service == "anexspot") {
    exec("/etc/init.d/opennds stop &");
    exec("sleep 3");
    exec("/etc/init.d/opennds start &");
  } else if ($service == "dpiagent") {
    exec("/etc/init.d/netifyd restart &");
    exec("sleep 1");
    exec("/etc/init.d/dpiagent restart &");
  } else if ($service == "netflow") {
    exec("/etc/init.d/netflow restart &");
  } else if ($service == "dynamicrouting") {
    exec("/etc/init.d/bird4 restart &");
    exec("/etc/init.d/bird6 restart &");
  }
  echo '<script>window.location.href = "/admin/services.php";</script>';
  exit;
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
                <h3 class="panel-title">Services</h3>
              </div>
              <div class="panel-body">
                <table class="table table-condensed">
                    <tr>
                      <td>Networking</td>
                      <td><a href="/admin/services.php?service=network"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>Firewall</td>
                      <td><a href="/admin/services.php?service=firewall"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>Wireless LAN</td>
                      <td><a href="/admin/services.php?service=wireless"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>DHCP/DNS</td>
                      <td><a href="/admin/services.php?service=dhcp"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>QoS</td>
                      <td><a href="/admin/services.php?service=qos"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>Routing/Load Balancer</td>
                      <td><a href="/admin/services.php?service=loadbalancer"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>SSL VPN</td>
                      <td><a href="/admin/services.php?service=sslvpn"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>IPSec VPN</td>
                      <td><a href="/admin/services.php?service=ipsec"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>Web GUI</td>
                      <td><a href="/admin/services.php?service=dashboard"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>AnexConnect</td>
                      <td><a href="/admin/services.php?service=anexconnect"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>AnexFuse</td>
                      <td><a href="/admin/services.php?service=anexfuse"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>AnexSpot</td>
                      <td><a href="/admin/services.php?service=anexspot"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>Flow/NAT Log Exporter</td>
                      <td><a href="/admin/services.php?service=netflow"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                    <tr>
                      <td>Application Identification Engine</td>
                      <td><a href="/admin/services.php?service=dpiagent"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
		    <tr>
                      <td>Dynamic Routing</td>
                      <td><a href="/admin/services.php?service=dynamicrouting"><button type="submit" class="btn btn-primary btn-sm">Restart</button></a></td>
                    </tr>
                  </table>
                  <br/>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endblock() ?>
