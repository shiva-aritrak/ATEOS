<?php

function set_vrrp_ha($instance_no, $status, $vrrp_state, $vrrp_interface, $vrrp_router_id, $virtual_ip, $unicast_src_ip, $vrrp_priority, $nopreempt, $unicast_peer_ip, $unicast)
{

    $global = exec("uci get keepalived.@global_defs[0]");
    if ($global != "global_defs") {
        exec("uci add keepalived global_defs");
        exec("uci set keepalived.@global_defs[0].enable_script_security='1'");
        exec("uci set keepalived.@global_defs[0].script_user='root'");
    }

    $group = exec("uci get keepalived.@vrrp_sync_group[0]");
    if ($group != "vrrp_sync_group") {
        exec("uci add keepalived vrrp_sync_group");
        exec("uci set keepalived.@vrrp_sync_group[0].name='ace_group'");
    }
    if ($instance_no == "-1") {
        exec("uci add keepalived vrrp_instance");
        exec("uci add keepalived ipaddress");
        $instance_no = intval(exec("uci show keepalived | grep '=vrrp_instance' | wc -l")) - 1;
    }

    exec("uci del_list keepalived.@vrrp_sync_group[0].group='ace".$instance_no."'");
    exec("uci add_list keepalived.@vrrp_sync_group[0].group='ace".$instance_no."'");
    exec("uci set keepalived.@ipaddress[".$instance_no."].name='".$vrrp_interface."'");
    exec("uci set keepalived.@ipaddress[".$instance_no."].address='".$virtual_ip."'");

    exec("uci set keepalived.@vrrp_instance[".$instance_no."].enabled='".$status."'");
    exec("uci set keepalived.@vrrp_instance[".$instance_no."].name='ace".$instance_no."'");
    if ($vrrp_state) {
        exec("uci set keepalived.@vrrp_instance[".$instance_no."].state='MASTER'");
    } else {
        exec("uci set keepalived.@vrrp_instance[".$instance_no."].state='BACKUP'");
    }
    $iface_type = exec("uci -q get network.".$vrrp_interface.".type");
    if ($iface_type == "bridge") {
        $raw_interface = "br-".$vrrp_interface."";
    } else {
        $raw_interface = exec("uci get network.".$vrrp_interface.".ifname");
    }
    exec("uci set keepalived.@vrrp_instance[".$instance_no."].interface='".$raw_interface."'");

    exec("uci delete keepalived.@vrrp_instance[".$instance_no."].virtual_ipaddress");
    exec("uci add_list keepalived.@vrrp_instance[".$instance_no."].virtual_ipaddress='".$vrrp_interface."'");

    exec("uci set keepalived.@vrrp_instance[".$instance_no."].virtual_router_id='".$vrrp_router_id."'");

    if ($nopreempt == "on") {
        exec("uci set keepalived.@vrrp_instance[".$instance_no."].nopreempt='1'");
    } else {
        exec("uci delete keepalived.@vrrp_instance[".$instance_no."].nopreempt");
    }
    exec("uci set keepalived.@vrrp_instance[".$instance_no."].priority='".$vrrp_priority."'");
    exec("uci set keepalived.@vrrp_instance[".$instance_no."].advert_int='1'");
    exec("uci set keepalived.@vrrp_instance[".$instance_no."].debug='4'");
    
    if ($unicast == "on") {
        exec("uci set keepalived.@vrrp_instance[".$instance_no."].unicast='1'");
        exec("uci set keepalived.@vrrp_instance[".$instance_no."].unicast_src_ip='".$unicast_src_ip."'");
        exec("uci delete keepalived.@vrrp_instance[".$instance_no."].unicast_peer");
        exec("uci add_list keepalived.@vrrp_instance[".$instance_no."].unicast_peer='".$unicast_peer_ip."'");
    } else {
        exec("uci set keepalived.@vrrp_instance[".$instance_no."].unicast='0'");
        exec("uci delete keepalived.@vrrp_instance[".$instance_no."].unicast_src_ip");
        exec("uci delete keepalived.@vrrp_instance[".$instance_no."].unicast_peer");
    }

    exec("uci commit keepalived");
    exec("/bin/sync");
    exec("sleep 5");
    exec("/etc/init.d/keepalived restart");
}

function delete_vrrp_instance($instance_no)
{
    exec("uci delete keepalived.@ipaddress[".$instance_no."]");
    exec("uci delete keepalived.@vrrp_instance[".$instance_no."]");
    exec("uci del_list keepalived.@vrrp_sync_group[0].group='ace".$instance_no."'");
    exec("uci delete keepalived.@global_defs[0].cur_state");
    exec("uci commit keepalived");
    exec("/bin/sync");
    exec("sleep 5");
    exec("/etc/init.d/keepalived restart");
}

?>