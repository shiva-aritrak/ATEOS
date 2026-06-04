<?php

function no_of_clients() {
        $clients = shell_exec("cat /proc/1/task/1/net/arp");
        $clients = explode("\n", $clients);
        $clients = count($clients) - 1;
        return $clients;
}

// shows no of tcp connections active currently
function no_tcp_con() {
	$tcp_connections = shell_exec("cat /proc/net/nf_conntrack");
	$tcp_connections = explode("\n", $tcp_connections);
	$tcp_connections = count($tcp_connections);
	$tcp_connections = $tcp_connections - 1;
	return $tcp_connections;
}

// shows cpu usage of router
function  cpu_usage() {
	$stat = file('/proc/stat');
	$info = explode(" ", preg_replace("!cpu +!", "", $stat[0]));
	$usage = (($info[0] + $info[2])*100/($info[0]+$info[2]+$info[3]));
	$usage = round($usage);
	return $usage;
}

function get_link_status($interface)
{
	$myfile = fopen("/tmp/sysinfo/board_name", "r") or die("Unable to open file!");
	$board_name = trim(fgets($myfile));
	fclose($myfile);

	if ($board_name == "yentek-default-string" || $board_name == "baytrail-baytrail" || $board_name == "sangfor-5jm5100" || $board_name == "to-be-filled-by-o-e-m-to-be-filled-by-o-e-m" || $board_name == "default-string-default-string" || $board_name == "intel-cnction-iaf") {
		$ifname = exec("uci get network.".$interface.".ifname");
		$linkstat = exec("cat /sys/class/net/".$ifname."/operstate");
		return $linkstat;
	} else {
		$intfname = exec("uci get network.".$interface.".ifname");
		if($intfname == "eth1") {
			$linkstat = exec("cat /sys/class/net/".$intfname."/operstate");
			return $linkstat;
		}
		if($interface == "lan_br0") {
			$ports = exec("uci get network.".$interface.".ports");

            $ports_arr = explode(" ", $ports);
            foreach($ports_arr as $port) {
				$vlan_id = exec("uci get network.".$port.".port_no");
                // $port_num = explode(" ", exec("swconfig dev switch0 vlan ".$vlan_id." get ports"))[0];
                $portsel = "swconfig dev switch0 port ".$vlan_id." get link";
                $linkstat = exec($portsel);
                $linkstat = explode(" ",$linkstat);
                $linkstat = $linkstat[1];
                $linkstat = explode(":",$linkstat);
                $linkstat = $linkstat[1];
                if($linkstat == "up") {
                        return $linkstat;
                }
            }
            return "down";
		}
		$port_num = exec("uci get network.".$interface.".port_num");
		$vlan_id = exec("uci get network.".$port_num.".port_no");

		// $port_num = explode(" ", exec("swconfig dev switch0 vlan ".$vlan_id." get ports"))[0];
		$portsel = "swconfig dev switch0 port ".$vlan_id." get link";
		$linkstat = exec($portsel);
		$linkstat = explode(" ",$linkstat);
		$linkstat = $linkstat[1];
		$linkstat = explode(":",$linkstat);
		$linkstat = $linkstat[1];
		return $linkstat;
	}

	$ifname = exec("uci get network.".$interface.".ifname");
	$status = exec("devstatus ".$ifname." | jsonfilter -e '@.carrier'");
	return $status;
}


// shows
function port_packet_info_rec($portno,$infoselector) {
	for ($port = 1; $port <= 6; $port++) {
		$revgood = "swconfig dev switch0 port ".$port." get recv_good";
		$revbad = "swconfig dev switch0 port ".$port." get recv_bad";

		$recvpgood[$port] = exec($revgood);
		$recvpbad[$port] = exec($revbad);
	}

	$packinfo = array($recvpbad[$portno],$recvpgood[$portno]);
	return $packinfo[$infoselector];
}

function no_of_vpn_clients() {
	return exec("uci show openvpn | awk -F'.' '{print $2}' | grep client | grep -v '=' | sort -u | wc -l");
}

function total_transfer_counters() {
	$out = exec("awk '/:/ { print($1,$2, $10) }' < /proc/net/dev |grep 'eth0:'");
	return explode(" ",$out);
}

function port_packet_info_trans($portno,$infoselector) {
	for ($port = 1; $port <= 6; $port++) {
		$trangood = "swconfig dev switch0 port ".$port." get tr_good";
		$tranbad = "swconfig dev switch0 port ".$port." get tr_bad";

		$tranpgood[$port] = exec($trangood);
		$tranpbad[$port] = exec($tranbad);

	}

	$packinfo = array($tranpbad[$portno],$tranpgood[$portno]);
	return $packinfo[$infoselector];
}

function boot_uptime() {
	$buptime = file('/proc/uptime');
	$buptime = $buptime[0];
	$buptime = explode(" ",$buptime);
	$seconds = $buptime[0];
	$seconds = round($seconds);
	$dt1 = new DateTime("@0");
	$dt2 = new DateTime("@$seconds");
	return $dt1->diff($dt2)->format('%a days %h hours and %i minutes');
}

function cpu_info($infoselect) {
	$cpustat = file('/proc/loadavg');
	$cpustat = $cpustat[0];
	$cpu_stat_info = explode(" ",$cpustat);
	$cpu_stat_info[2];
	$cpu_stat_info[0];
	$cpu_avg = ($cpu_stat_info[0] +  $cpu_stat_info[1] +  $cpu_stat_info[2])/3;
	$cpu_avg = round($cpu_avg,2);
	$no_of_processes = explode("/",$cpu_stat_info[3]);
	$no_of_processes = $no_of_processes[0];
	$cpu_info_selector = array($cpu_stat_info[0],$cpu_stat_info[2],$cpu_avg,$no_of_processes);
	return $cpu_info_selector[$infoselect];
}

function ramstatus($opt) {
	$ramstat = file('/proc/meminfo');
	$tmpinfo = $ramstat;
	$ramstat = $ramstat[$opt];
	$ramstat = preg_split('/\s+/', $ramstat);
	$tmpinfo =  preg_split('/\s+/', $tmpinfo[0]);
	$prec = ($ramstat[1]/$tmpinfo[1])*100;
	$prec = round($prec);
	echo $prec;
}

function no_of_ps() {
	$ps = shell_exec("ps");
	$ps = explode("\n", $ps);
	$ps = count($ps);
	$ps = $ps - 1;
	return $ps;
}

function iface_network_usage($iface){
	$raw_iface = "anex";
	if ($iface == "usb0") {
		$out = "";
		$ret = 1;
		exec("uci show network.3g", $out, $ret);
		if ($ret == 0) {
			$raw_iface = "3g-3g";
		}
		exec("uci show network.usb0", $out, $ret);
		if ($ret == 0) {
			$raw_iface = exec('uci get network.usb0.ifname');
		}
	} else if (substr( $iface, 0, 4 ) === "bond") {
		$raw_iface = $iface;
	} else if (substr( $iface, 0, 4 ) === "l2tp") {
		$raw_iface = "l2tp-".$iface;
	} else if (substr( $iface, 0, 4 ) === "wwan") {
		$raw_iface = "3g-".$iface;
	} else if ($iface == "fuse_br") {
		$raw_iface = "bond0";
	} else {
		$port_num = exec("uci get network.".$iface.".port_num");
		$raw_iface_tmp = exec("uci get network.".$port_num.".ifname");
		$proto_type = exec("uci get network.".$iface.".proto");
		$iface_type = exec("uci get network.".$iface.".type");

		$raw_iface = $raw_iface_tmp;
		if ($iface_type == "bridge") {
			$raw_iface = "br-".$iface;
		} else if ($proto_type == "3g") {
			$raw_iface = "3g-".$iface;
		}
	}
	if ($raw_iface) {
		$iface_usage = exec("ifstat -b -i ".$raw_iface." 0.1 1");
		$iface_usage = trim($iface_usage);
		$iface_usage = explode(' ',$iface_usage);
		$uploads = end($iface_usage);
		$downloads = $iface_usage[0];
		$iface_info = array( (round($uploads/1000, 2)), (round($downloads/1000, 2)) );
		return $iface_info;
	} else {
		return array ( 0, 0 );
	}
}

function ipsec_stats($tunnel_name) {

	$ipsec_out_1 = exec('ipsec stroke statusall-nb '.$tunnel_name.' | sed -n -e "s|\(.*\)\({.*}\):.*, \([0-9]\+\) bytes_i .*, \([0-9]\+\) bytes_o .*, rekeying in \([0-9]\+\) \([[:alpha:]]\+\)$|\1 \3 \4|p"');
	$ipsec_out_pre = explode(" ", $ipsec_out_1);

	sleep(3);
	$ipsec_out_2 = exec('ipsec stroke statusall-nb '.$tunnel_name.' | sed -n -e "s|\(.*\)\({.*}\):.*, \([0-9]\+\) bytes_i .*, \([0-9]\+\) bytes_o .*, rekeying in \([0-9]\+\) \([[:alpha:]]\+\)$|\1 \3 \4|p"');
	$ipsec_out_post = explode(" ", $ipsec_out_2);

	return array ( number_format((($ipsec_out_post[2]-$ipsec_out_pre[2])*8/(1000*1000*3)), 2) , number_format((($ipsec_out_post[1]-$ipsec_out_pre[1])*8/(1000*1000*3)), 2) );
}

?>


