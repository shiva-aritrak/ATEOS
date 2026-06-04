<?php
function repeater_disable()
{
	exec("uci set wireless.radio0.enabled='0'");
}

function repeater_enable($ap_ssid, $ap_encryption, $ap_key, $client_ssid, $client_encryption, $client_key)
{
	//wireless file
	exec("uci set wireless.radio0=wifi-device");
	exec("uci set wireless.radio0.enabled='1'");
	exec("uci set wireless.radio0.htmode='HT20'");
        exec("uci set wireless.radio0.hwmode='11g'");
        exec("uci set wireless.radio0.channel='11'");

	exec("uci set wireless.default_radio0.=wifi-iface");
	exec("uci set wireless.default_radio0.device='radio0'");
	exec("uci set wireless.default_radio0.network='lan'");
	exec("uci set wireless.default_radio0.mode='ap'");
	exec("uci set wireless.default_radio0.ssid='".$ap_ssid."'");
	exec("uci set wireless.default_radio0.encryption=".$ap_encryption);
	if($ap_encryption == none)
	{
		exec("uci delete wireless.default_radio0.key");
	}
	else
	{
		exec("uci set wireless.default_radio0.key=".$ap_key);
	}

	exec("uci set wireless.default_radio1.=wifi-iface");
	exec("uci set wireless.default_radio1.device='radio0'");
	exec("uci set wireless.default_radio1.network='wan4'");
	exec("uci set wireless.default_radio1.mode='sta'");
	exec("uci set wireless.default_radio1.ssid='".$client_ssid."'");
	exec("uci set wireless.default_radio1.encryption=".$client_encryption);
	if($client_encryption==none)
	{
		exec("uci delete wireless.default_radio1.key");
	}
	else
	{
		exec("uci set wireless.default_radio1.key=".$client_key);
	}

	//network file
	exec("uci set network.wan4=interface");
	exec("uci set network.wan4.proto='dhcp'");
	exec("uci set network.repeater_bridge=interface");
	exec("uci set network.repeater_bridge.proto='relay'");
	exec("uci set network.repeater_bridge.network='lan wan4'");

	//firewall file
	exec("uci set firewall.@zone[0].network='lan repeater_bridge wan4");

	exec("uci commit");
	exec("/etc/init.d/network reload");
}
?>
