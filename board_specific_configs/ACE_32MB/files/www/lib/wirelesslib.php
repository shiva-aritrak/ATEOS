<?php

function set_config()
{
	exec("uci commit wireless");
	exec("/etc/init.d/network reload");
	exec("wifi");
}

function get_wireless_ssids()
{
	$out = array();
	$ret = 0;
	exec("iwinfo phy0 scan | grep ESSID | cut -d':' -f2 | tr -d '\"'", $out, $ret);
	return $out;
}

function set_wireless_config($wifi_mode, $wifi_status, $ssid, $txpower, $channel, $hwmode, $isolate, $ssid_hidden, $chanbw, $beacon_int, $rts, $frag, $encryption, $passkey, $lan_interface, $mac_filter_mode, $mac_addresses, $auth_server, $auth_port, $auth_secret)
{
	try {
		if ($wifi_mode == "2.4") {
			$radio = "radio0";
			$htmode = 'HT20';
			exec("uci set wireless.default_".$radio.".ifname='wlan0'");
		} else if ($wifi_mode == "5") {
			$htmode = 'VHT80';
			$radio = "radio1";
			exec("uci set wireless.default_".$radio.".ifname='wlan1'");
		}

		$iface = "default_".$radio;

		if ($wifi_status == "0") {
			exec("uci set wireless.".$radio.".disabled='0'");
			exec("uci set wireless.".$radio.".enabled='1'");
		}
		else if ($wifi_status == "1") {
			exec("uci set wireless.".$radio.".disabled='1'");
			exec("uci set wireless.".$radio.".enabled='0'");
		}

		exec("uci set wireless.".$radio.".channel='".$channel."'");
		exec("uci set wireless.".$radio.".hwmode='".$hwmode."'");
		exec("uci set wireless.".$radio.".diversity='1'");
		exec("uci set wireless.".$radio.".txpower='".$txpower."'");
		exec("uci set wireless.".$radio.".htmode='".$htmode."'");

		exec("uci set wireless.".$iface.".device='".$radio."'");
		exec("uci set wireless.".$iface.".ssid='".$ssid."'");
		
		exec("uci set wireless.".$iface.".encryption='".$encryption."'");
		
		if ($encryption == "none") {
			exec("uci delete wireless.".$iface.".key");
			exec("uci delete wireless.".$iface.".ieee8021x");
			exec("uci delete wireless.".$iface.".auth_server");
			exec("uci delete wireless.".$iface.".auth_port");
			exec("uci delete wireless.".$iface.".auth_secret");
		} else if ($encryption == "wpa2" || $encryption == "wpa2+tkip" || $encryption == "wpa2+aes" || $encryption == "wpa2+ccmp" || $encryption == "wpa2+tkip+aes" || $encryption == "wpa2+tkip+ccmp"){
			exec("uci delete wireless.".$iface.".key");
			exec("uci set wireless.".$iface.".ieee8021x='1'");
			exec("uci set wireless.".$iface.".auth_server='".$auth_server."'");
			exec("uci set wireless.".$iface.".auth_port='".$auth_port."'");
			exec("uci set wireless.".$iface.".auth_secret='".$auth_secret."'");
		} else {
			exec("uci set wireless.".$iface.".key='".$passkey."'");
			exec("uci delete wireless.".$iface.".ieee8021x");
			exec("uci delete wireless.".$iface.".auth_server");
			exec("uci delete wireless.".$iface.".auth_port");
			exec("uci delete wireless.".$iface.".auth_secret");
		}
		
		exec("uci set wireless.".$iface.".chanbw='".$chanbw."'");
		exec("uci set wireless.".$iface.".beacon_int='".$beacon_int."'");
		exec("uci set wireless.".$iface.".rts='".$rts."'");
		exec("uci set wireless.".$iface.".frag='".$frag."'");

		exec("uci set wireless.".$iface.".network='".$lan_interface."'");
		exec("uci set wireless.".$iface.".isolate='".$isolate."'");
		exec("uci set wireless.".$iface.".hidden='".$ssid_hidden."'");

		if ($mac_filter_mode) {
			exec("uci set wireless.".$iface.".macfilter='".$mac_filter_mode."'");
		} else {
			exec("uci delete wireless.".$iface.".macfilter");
		}

		if ($mac_addresses) {
			exec("uci delete wireless.".$iface.".maclist");
			foreach($mac_addresses as $mac_address) {
				if ($mac_address != '') {
					exec("uci add_list wireless.".$iface.".maclist='".$mac_address."'");
				}
			}
		} else {
			exec("uci delete wireless.".$iface.".maclist");
		}
		set_config();
	}
	catch(Exception $e) {
		exec("logger -t Wireless Exception". $e);
	}
}


function enable_client($ssid, $encryption, $wireless_password, $metric) {
	exec("uci set wireless.radio1.disabled='1'");

	exec("uci set wireless.radio0=wifi-device");
	exec("uci set wireless.radio0.disabled='0'");
	exec("uci set wireless.radio0.channel='auto'");

	exec("uci delete wireless.default_radio0.mode");
	exec("uci delete wireless.default_radio0.ssid");
	exec("uci delete wireless.default_radio0.diversity");
	exec("uci delete wireless.default_radio0.txantenna");
	exec("uci delete wireless.default_radio0.rxantenna");
	exec("uci delete wireless.default_radio0.network");
	exec("uci delete wireless.default_radio0.encryption");
	exec("uci delete wireless.default_radio0.key1");
	exec("uci delete wireless.default_radio0.key");

	exec("uci set wireless.default_radio0=wifi-iface");
	exec("uci set wireless.default_radio0.device='radio0'");
	exec("uci set wireless.default_radio0.network='wlan0'");
	exec("uci set wireless.default_radio0.ifname='wlan0'");
	exec("uci set wireless.default_radio0.mode='sta'");
	exec("uci set wireless.default_radio0.ssid='".$ssid."'");
	exec("uci set wireless.default_radio0.encryption='".$encryption."'");
	exec("uci set wireless.default_radio0.key='".$wireless_password."'");

	exec("uci set network.wlan0=interface");
	exec("uci set network.wlan0.proto='dhcp'");
	if ($metric) {
		exec("uci set network.wlan0.metric='$metric'");
	} else {
		exec("uci delete network.wlan0.metric");
	}
	exec("uci commit");
	exec("sync");
	exec("wifi");
}

function disable_client()
{
	exec("uci delete network.wlan0");
	exec("uci set wireless.radio0.disabled='1'");
	exec("uci commit network");
	exec("sync");
	set_config();
}


//connected clients

function get_connected_clients_wifi()
{
	$connected_devices = array();
	$connected_macs = "";
	$ret = 0;
	exec("iw dev wlan0 station dump | grep Station | cut -f 2 -s -d\" \"", $connected_macs, $ret);
	if ($file = fopen("/tmp/dhcp.leases", "r"))
	{
		$line_counter = 1;
		$line=array();
		while(!feof($file))
		{
			$line = (fgets($file));
			$elements = explode(" ", $line);
			if(sizeof($elements) > 3) {
				if (in_array($elements[1], $connected_macs)) {
					date_default_timezone_set('Asia/Kolkata');
					$dt = new DateTime("@".$elements[0]);
					array_push($connected_devices, array($dt->format('Y-m-d H:i:s'), $elements[1] ,$elements[2], $elements[3]));
				}
			}
			$line_counter += 1;
		}
		fclose($file);
	}
	return $connected_devices;
}

function repeater_disable()
{
	exec("uci delete wireless.default_radio1");
	exec("uci delete network.repbr0");
	exec("uci delete network.wlan0");
	exec("uci set wireless.radio0.disabled='1'");
	exec("uci commit");
	exec("sync");
	exec("/etc/init.d/network reload");
}

function repeater_enable($repeater_status, $ap_ssid, $ap_encryption, $ap_wireless_password, $client_ssid, $client_encryption, $wireless_password, $part_of_network, $metric)
{
	exec("uci delete wireless.radio0.disabled");

	exec("uci set wireless.radio0=wifi-device");
	exec("uci set wireless.radio0.disabled='0'");
	exec("uci set wireless.radio0.channel='auto'");

	exec("uci set wireless.default_radio0=wifi-iface");
	exec("uci set wireless.default_radio0.device='radio0'");
	exec("uci set wireless.default_radio0.network='".$part_of_network."'");
	exec("uci set wireless.default_radio0.ifname='wlan0'");
	exec("uci set wireless.default_radio0.mode='ap'");
	exec("uci set wireless.default_radio0.ssid='".$ap_ssid."'");
	exec("uci set wireless.default_radio0.encryption='".$ap_encryption."'");

	if ($ap_encryption == "none") {
		exec("uci delete wireless.default_radio0.key");
	} else {
		exec("uci set wireless.default_radio0.key='".$ap_wireless_password."'");
	}

	exec("uci set wireless.default_radio1=wifi-iface");
	exec("uci set wireless.default_radio1.device='radio0'");
	exec("uci set wireless.default_radio1.ifname='wlan1'");
	exec("uci set wireless.default_radio1.network='wlan1'");
	exec("uci set wireless.default_radio1.mode='sta'");
	exec("uci set wireless.default_radio1.ssid='".$client_ssid."'");
	exec("uci set wireless.default_radio1.encryption='".$client_encryption."'");
	exec("uci set wireless.default_radio1.part_of_network='".$part_of_network."'");

	if($client_encryption=="none") {
		exec("uci delete wireless.default_radio1.key");
	} else {
		exec("uci set wireless.default_radio1.key='".$wireless_password."'");
	}

	exec("uci set network.wlan1=interface");
	exec("uci set network.wlan1.proto='dhcp'");
	if ($metric) {
		exec("uci set network.wlan1.metric='$metric'");
	} else {
		exec("uci delete network.wlan1.metric");
	}
	exec("uci set network.repbr0=interface");
	exec("uci set network.repbr0.proto='relay'");
	exec("uci set network.repbr0.network='".$part_of_network." wlan1'");

	exec("uci commit");
	exec("sync");
	set_config();
}
?>
