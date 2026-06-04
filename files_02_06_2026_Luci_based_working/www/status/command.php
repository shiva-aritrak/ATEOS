<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>System Command</title>
</head>

<?php include '/www/sidenav.php' ?>

<?php
if(count($_POST) > 0) {
  $command = $_POST["command"];
  $sanitized = str_replace(";", '#', $_POST["command_arg"]);
  $sanitized = str_replace("|", '#', $sanitized);
  $sanitized = str_replace("&", '#', $sanitized);
  $sanitized = str_replace("...", '#', $sanitized);
  $command_arg = array_shift(explode('#', $sanitized));

  $output = "";
  $permitted_commands = array("ping", "ping6", "ifconfig", "lsusb", "traceroute", "nslookup", "iproute", "ipsec status", "at", "lldp", "systemcurdns", "ip4neigh", "ip6neigh", "ip6route", "conntrack", "arp", "netstat", "link status", "Firewall Rules", "Dynamic Route Logs", "Dynamic Route6 Logs", "port status", "Interface Statistics");
  if (in_array($command, $permitted_commands))
  {
    $to_run_cmd = "";
    if ($command == "ping") {
      $to_run_cmd = "/bin/ping -c 5 ".$command_arg;
    } else if ($command == "ping6" ) {
      $to_run_cmd = "/bin/ping6 -c 5 ".$command_arg;
    } else if ($command == "ifconfig" ) {
      $to_run_cmd = "/sbin/ifconfig ".$command_arg;
    } else if ($command == "lsusb" ) {
      $to_run_cmd = "/usr/bin/lsusb ".$command_arg;
    } else if ($command == "traceroute" ) {
      $to_run_cmd = "/bin/traceroute -n ".$command_arg;
    } else if ($command == "nslookup") {
      $to_run_cmd = "/usr/bin/nslookup ".$command_arg;
    } else if ($command == "arp") {
      $to_run_cmd = "cat /proc/net/arp".$command_arg;
    } else if ($command == "netstat") {
      $to_run_cmd = "/bin/netstat ".$command_arg;
    } else if ($command == "Firewall Rules") {
      $to_run_cmd = "/usr/sbin/iptables -vnL ".$command_arg;
    } else if ($command == "iproute") {
      $to_run_cmd = "/sbin/route -n ".$command_arg;
    } else if ($command == "ipsec status") {
      $to_run_cmd = "/usr/sbin/ipsec statusall";
    } else if ($command == "link status") {
      $to_run_cmd = "/usr/sbin/ethtool ".$command_arg;
    } else if ($command == "at") {
      $to_run_cmd = "/bin/at-cmd".$command_arg;
    } else if ($command == "lldp") {
      $to_run_cmd = "lldpcli show neighbors details ".$command_arg;
    } else if ($command == "systemcurdns") {
      $to_run_cmd = "/bin/cat /tmp/resolv.conf.auto";
    } else if ($command == "ip4neigh") {
      $to_run_cmd = "ip -4 neigh";
    } else if ($command == "ip6neigh") {
      $to_run_cmd = "ip -6 neigh";
    } else if ($command == "ip6route") {
      $to_run_cmd = "ip -6 route";
    } else if ($command == "conntrack") {
      $to_run_cmd = "conntrack -L";
    } else if ($command == "Dynamic Route Logs") {
      $to_run_cmd = '/usr/bin/tail -n 200 /tmp/bird4.log | grep "<*>"';
    } else if ($command == "Dynamic Route6 Logs") {
      $to_run_cmd = '/usr/bin/tail -n 200 /tmp/bird6.log | grep "<*>"';
    } else if ($command == "port status") {
      $port_num = exec("uci -q get network.port".$command_arg.".port_no");
      $to_run_cmd = "/sbin/swconfig dev switch0 port ".$port_num." get link | cut -d ' ' -f 2,3,4";
    }
    if ($command == "Interface Statistics") {
      exec("/bin/ubus call network.device status > /tmp/stats.json");
      $json = file_get_contents('/tmp/stats.json');
      $data = json_decode($json, true);
    }
    if ($command == "port status" && $command_arg) {
      exec($to_run_cmd, $output);
      $output[1] = "auto-negotiation:on port:twisted-pair";
      $output = str_replace(' ', "<br>", $output);
    } else {
      exec($to_run_cmd, $output);
    }
  }
}
?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <h3 class="page-title">System Command</h3>
      <div class="row">
        <div class="col-md-4">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title"></h3>
              </div>
              <div class="panel-body">
                <div id="system_command_form_div_id" class="content">
                  <form autocomplete="off" id="system_command_form" action="command.php" method="post">
                    <div>
                      <table>
                        <tr>
                          <td>Command</td>
                          <td>
                            <select required id="command_id" class="form-control" name="command">
                              <option <?php if ($command == "ping") { echo "selected=selected"; } ?> value="ping">ping IPv4</option>
                              <option <?php if ($command == "ping6") { echo "selected=selected"; } ?> value="ping6">ping IPv6</option>
                              <option <?php if ($command == "traceroute") { echo "selected=selected"; } ?> value="traceroute">traceroute</option>
                              <option <?php if ($command == "lsusb") { echo "selected=selected"; } ?> value="lsusb">lsusb</option>
                              <option <?php if ($command == "ifconfig") { echo "selected=selected"; } ?> value="ifconfig">ifconfig</option>
                              <option <?php if ($command == "nslookup") { echo "selected=selected"; } ?> value="nslookup">nslookup</option>
                              <option <?php if ($command == "iproute") { echo "selected=selected"; } ?> value="iproute">iproute</option>
                              <option <?php if ($command == "ipsec status") { echo "selected=selected"; } ?> value="ipsec status">ipsec status</option>
                              <option <?php if ($command == "at") { echo "selected=selected"; } ?> value="at">modem-command</option>
                              <option <?php if ($command == "lldp") { echo "selected=selected"; } ?> value="lldp">LLDP Neighbors</option>
                              <option <?php if ($command == "systemcurdns") { echo "selected=selected"; } ?> value="systemcurdns">System Current DNS</option>
                              <option <?php if ($command == "ip4neigh") { echo "selected=selected"; } ?> value="ip4neigh">IPv4 Neighbors</option>
                              <option <?php if ($command == "ip6neigh") { echo "selected=selected"; } ?> value="ip6neigh">IPv6 Neighbors</option>
                              <option <?php if ($command == "ip6route") { echo "selected=selected"; } ?> value="ip6route">IPv6 Route</option>
                              <option <?php if ($command == "conntrack") { echo "selected=selected"; } ?> value="conntrack">Current Connections</option>
                              <option <?php if ($command == "arp") { echo "selected=selected"; } ?> value="arp">ARP Table</option>
                              <option <?php if ($command == "netstat") { echo "selected=selected"; } ?> value="netstat">Netstat</option>
                              <option <?php if ($command == "link status") { echo "selected=selected"; } ?> value="link status">Link Status</option>
                              <option <?php if ($command == "port status") { echo "selected=selected"; } ?> value="port status">Port Status</option>
                              <option <?php if ($command == "Firewall Rules") { echo "selected=selected"; } ?> value="Firewall Rules">Firewall Rules</option>
                              <option <?php if ($command == "Dynamic Route Log") { echo "selected=selected"; } ?> value="Dynamic Route Logs">Dynamic Route Logs</option>
                              <option <?php if ($command == "Dynamic Route6 Log") { echo "selected=selected"; } ?> value="Dynamic Route6 Logs">Dynamic Route6 Logs</option>
                              <option <?php if ($command == "Interface Statistics") { echo "selected=selected"; } ?> value="Interface Statistics">Interface Statistics</option>
                            </select>
                          </td>
                        </tr>
                        <tr>
                          <td>Argument</td>
                          <td><input type="text" id="command_arg_id" name="command_arg" class="form-control"></td>
                        </tr>
                      </table>
                      <br/>
                      <div class="row">
                        <div class="col-md-6">
                          <button type="submit" class="btn btn-primary">Run</button>
                        </div>
                        <div class="col-md-6">
                          <button type="button" class="btn btn-danger">Reset</button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
        if(count($_POST) > 0) {
          echo '<div id="command_result_panel_id" class="col-md-8">';
          echo '  <div class="panel panel-headline">';
          echo '    <div class="panel-heading">';
          echo '      <h3 class="panel-title">Result</h3>';
          echo '    </div>';
          echo '    <div class="panel-body">';
          echo '      <h4 id="command_name_header_id">'.$command.' '.$command_arg.'</h4>';
          echo '      <pre id="command_output_id">';
          if ($command == "Interface Statistics") {
								echo "<table border='1' cellpadding='6' cellspacing='0'>";
								echo "<tr><th></th>";
								$allStats = [];
								foreach ($data as $iface => $details) {
    									if (isset($details['statistics'])) {
        									foreach ($details['statistics'] as $statKey => $_) {
            										$allStats[$statKey] = true;
        									}
    									}
								}
								$allStats = array_keys($allStats);
								foreach ($data as $iface => $details) {
    									echo "<th>" . htmlspecialchars($iface) . "</th>";
								}
								echo "</tr>";
								foreach ($allStats as $stat) {
    									echo "<tr>";
    									echo "<td>" . htmlspecialchars($stat) . "</td>";
    									foreach ($data as $iface => $details) {
        									$value = isset($details['statistics'][$stat]) ? $details['statistics'][$stat] : 'N/A';
        									echo "<td>" . htmlspecialchars($value) . "</td>";
    									}
    									echo "</tr>";
								}
								echo "</table>";
          } else {
              foreach ($output as $value) {
              echo $value."\n";
            }
          }
          echo '</pre>';
          echo '    </div>';
          echo '  </div>';
          echo '  <!-- END PANEL HEADLINE -->';
          echo '</div>';
        }
        ?>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>