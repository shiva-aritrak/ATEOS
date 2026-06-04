<?php

include_once 'common.php';

function get_int_from_on_off($on_off_str)
{
  if ($on_off_str == "on") {
    return "1";
  } else {
    return "0";
  }
}


function set_ids_config($enabled, $interface, $local_nets) {
  exec("uci set snort.snort.enabled='".$enabled."'");
  exec("uci set snort.snort.listen_interface='".$interface."'");
  $ifname = exec("uci get network.".$interface.".ifname");
  exec("uci set snort.snort.interface='".$ifname."'");
  exec("uci delete snort.snort.local_nets");
  foreach($local_nets as $local_net) {
    exec("uci add_list snort.snort.local_nets='".$local_net."'");
  }

  $local_nets_str = implode(",", $local_nets);
  $new_home_nets = "ipvar HOME_NET ".$local_nets_str." \n";

  $prev_config_handle = fopen("/etc/snort/snort.conf", "r");
  $new_config_handle = fopen("/tmp/snort.conf", 'w');
  if ($prev_config_handle) {
      while (($line = fgets($prev_config_handle)) !== false) {

          if(strpos($line, "ipvar HOME_NET") !== false) {
            fwrite($new_config_handle, $new_home_nets);
          } else {
            fwrite($new_config_handle, $line);
          }
      }
      fclose($prev_config_handle);
      fclose($new_config_handle);
      copy('/tmp/snort.conf', '/etc/snort/snort.conf');
  }

  exec("logger -t webdebug new_home_nets: ".$new_home_nets);

  exec('uci commit snort');
  exec("sync");
  exec("/etc/init.d/snort restart &");
}

function set_firewall_config() {
  exec('uci commit firewall');
  exec("sync");
  exec("/etc/init.d/firewall reload");
}

function set_firewall_defaults($incoming_traffic, $forwarding_traffic, $outgoing_traffic, $drop_invalid, $tcp_window_scaling, $synflood_protect, $tcp_syncookies, $tcp_ecn, $tcp_westwood, $synflood_burst, $flow_offloading, $flow_offloading_hw)
{
  $pre = "uci set firewall.@defaults[0].";

  exec($pre."input='".$incoming_traffic."'");
  exec($pre."output='".$outgoing_traffic."'");
  exec($pre."forward='".$forwarding_traffic."'");

  exec($pre."drop_invalid='".get_int_from_on_off($drop_invalid)."'");
  exec($pre."synflood_protect='".get_int_from_on_off($synflood_protect)."'");
  exec($pre."tcp_syncookies='".get_int_from_on_off($tcp_syncookies)."'");
  exec($pre."tcp_ecn='".get_int_from_on_off($tcp_ecn)."'");
  exec($pre."tcp_westwood='".get_int_from_on_off($tcp_westwood)."'");
  exec($pre."tcp_window_scaling='".get_int_from_on_off($tcp_window_scaling)."'");
  exec($pre."synflood_burst='".$synflood_burst."'");
  exec($pre."flow_offloading='".get_int_from_on_off($flow_offloading)."'");
  exec($pre."flow_offloading_hw='".get_int_from_on_off($flow_offloading_hw)."'");

  exec('uci commit firewall');
  exec("sync");
  exec("/etc/init.d/firewall restart");
}

function clear_zone($zone_no)
{
  exec("uci delete firewall.@zone[".$zone_no."].family");
  exec("uci delete firewall.@zone[".$zone_no."].name");
  exec("uci delete firewall.@zone[".$zone_no."].network");
  exec("uci delete firewall.@zone[".$zone_no."].input");
  exec("uci delete firewall.@zone[".$zone_no."].forward");
  exec("uci delete firewall.@zone[".$zone_no."].output");
  exec("uci delete firewall.@zone[".$zone_no."].network");
  exec("uci delete firewall.@zone[".$zone_no."].subnet");
  exec("uci delete firewall.@zone[".$zone_no."].masq");
  exec("uci delete firewall.@zone[".$zone_no."].v6nat");
  exec("uci delete firewall.@zone[".$zone_no."].mtu_fix");
  exec("uci commit");
}

function move_zone_up($filled_zone_no)
{
  exec("uci reorder firewall.@zone[".$filled_zone_no."]='".strval(intval($filled_zone_no))."'");
  set_firewall_config();
}

function move_zone_down($filled_zone_no)
{
  exec("uci reorder firewall.@zone[".$filled_zone_no."]='".strval(intval($filled_zone_no)+2)."'");
  set_firewall_config();
}


function move_rule_up($filled_rule_no)
{
  exec("uci reorder firewall.@rule[".$filled_rule_no."]='".strval(intval($filled_rule_no))."'");
  set_firewall_config();
}

function move_rule_down($filled_rule_no)
{
  exec("uci reorder firewall.@rule[".$filled_rule_no."]='".strval(intval($filled_rule_no)+2)."'");
  set_firewall_config();
}

function create_firewall_zone($zone_no, $zone_name, $src_zones, $subnet, $incoming_packets, $forwarding_packets, $outgoing_packets, $masquerade, $v6nat, $mtu_fix)
{
  if ($zone_no != "-1") {
    clear_zone($zone_no);
  } else {
    exec("uci add firewall zone");
  }

  $pre = "uci set firewall.@zone[".$zone_no."].";
  exec("uci set firewall.@zone[".$zone_no."]=zone");

  exec($pre."name=".$zone_name);
  exec($pre."network=".$zone_name);

  exec($pre."input=".$incoming_packets);
  exec($pre."forward=".$forwarding_packets);
  exec($pre."output=".$outgoing_packets);

  exec("uci delete firewall.@zone[".$zone_no."].network");
  foreach ($src_zones as $src_zone) {
    exec("uci add_list firewall.@zone[".$zone_no."].network='".$src_zone."'");
  }

  if ($subnet != "0.0.0.0/0" && $subnet) {
    foreach (explode(",", $subnet) as $subnet_single) {
      exec("uci add_list firewall.@zone[".$zone_no."].subnet='".$subnet_single."'");
    }
  } else {
    exec("uci delete firewall.@zone[".$zone_no."].subnet");
  }

  exec($pre."v6nat=".get_int_from_on_off($v6nat));
  exec($pre."masq=".get_int_from_on_off($masquerade));
  exec($pre."mtu_fix=".get_int_from_on_off($mtu_fix));

  set_firewall_config();
}

function create_firewall_rule($rule_no, $status, $rule_name, $src_zone, $src_type, $src, $src_port, $dst_zone, $dest, $dest_port, $protocol, $applications, $daysofweek, $rule_start_date, $rule_start_time, $rule_end_date, $rule_end_time, $target_action, $src_ccs, $dst_ccs)
{
  $not_tor = true;

  if ($rule_no == "-1") {
    exec("uci add firewall rule");
    $rule_no = intval(exec("uci show firewall | grep '=rule' | wc -l")) - 1;
  }

  if($applications) {
    exec("uci set firewall.apprule".$rule_no."=ipset");
    exec("uci set firewall.apprule".$rule_no.".name='apprule".$rule_no."'");
    exec("uci set firewall.apprule".$rule_no.".enabled='1'");
    exec("uci set firewall.apprule".$rule_no.".storage='hash'");
    exec("uci set firewall.apprule".$rule_no.".match='dest_net'");
    $domains_json = file_get_contents('/usr/lib/appsdb.json');
    $domains_db = json_decode($domains_json, true);

    exec("uci delete firewall.apprule".$rule_no.".apps");
    exec("ipset -! create apprule".$rule_no." hash:net");
    exec("ipset -! flush apprule".$rule_no."");
    foreach($applications as $application) {
      $app_name = str_replace(array(".", "-"), "_", $application);
      if ($application == "netify.tor") {
        $not_tor = false;
        exec("uci add_list firewall.apprule".$rule_no.".apps='".$application."'");
        exec("uci set firewall.apprule".$rule_no.".loadfile='/usr/lib/torlist.txt'");
      } else if ($application) {
        exec("uci add_list firewall.apprule".$rule_no.".apps='".$application."'");
        foreach($domains_db[$application] as $domain) {
          $records = dns_get_record($domain, DNS_A);
          foreach($records as $record) {
            if($record["type"] == "A") {
              exec("ipset -! add apprule".$rule_no." ".$record["ip"]);
            }
          }
        }
      } else {
        exec("uci delete firewall.apprule".$rule_no);
      }
    }
    exec("uci commit firewall");
  }

  $extra_str="";
  $src_extra=false;
  $src_extra_str="";
  $dst_extra=false;
  $dst_extra_str="";

  $pre = "uci set firewall.@rule[".$rule_no."].";
  $pre_del = "uci delete firewall.@rule[".$rule_no."].";

    if ($applications) {
      exec($pre."ipset='apprule".$rule_no."'");
    } else {
      exec($pre_del."ipset");
    }

  exec($pre."enabled='".$status."'");
  exec($pre."name=".$rule_name);

  exec($pre."name=".$rule_name);
  exec($pre."src=".$src_zone);

  if ($src_type == "mac") {
    exec($pre_del."src_ip");
    exec($pre."src_mac='".$src."'");
  } else {
    exec($pre_del."src_mac");
    exec($pre."src_ip='".$src."'");
  }

  if ($src_port) {
    exec($pre."src_port='".$src_port."'");
  } else {
    exec($pre_del."src_port");
  }

  exec($pre_del."src_ccs");
  if ($src_ccs) {
    foreach($src_ccs as $src_cc) {
      exec("uci add_list firewall.@rule[".$rule_no."].src_ccs='".$src_cc."'");
    }
    $src_extra = true;
    $src_extra_str = " --src-cc ".implode(",", $src_ccs);
  } else {
    exec($pre_del."src_ccs");
  }

  exec($pre_del."dst_ccs");
  if ($dst_ccs) {
    foreach($dst_ccs as $dst_cc) {
      exec("uci add_list firewall.@rule[".$rule_no."].dst_ccs='".$dst_cc."'");
    }
    $dst_extra = true;
    $dst_extra_str = " --dst-cc ".implode(",", $dst_ccs);
  } else {
    exec($pre_del."dst_ccs");
  }

  if($src_extra) {
    $extra_str = " -m geoip ".$src_extra_str;
  } 

  if($dst_extra) {
    $extra_str = " -m geoip ".$dst_extra_str;
  }

  if ($src_extra && $dst_extra) {
    $extra_str = " -m geoip ".$src_extra_str." ".$dst_extra_str;
  }

  if ($extra_str) {
    exec("uci set firewall.@rule[".$rule_no."].extra='".$extra_str."'");
  } else {
    exec($pre_del."extra");
  }

  if ($protocol) {
    exec($pre."proto=".$protocol);
  } else {
    exec($pre_del."proto");
  }
  if ($dst_zone) {
    exec($pre."dest=".$dst_zone);
  } else {
    exec($pre_del."dest");
  }
  if ($dest) {
    exec($pre."dest_ip='".$dest."'");
  } else {
    exec($pre_del."dest_ip");
  }

  if ($dest_port) {
    exec($pre."dest_port='".$dest_port."'");
  } else {
    exec($pre_del."dest_port");
  }

  if ($daysofweek) {
    exec($pre."weekdays='".implode(" ", $daysofweek)."'");
  } else {
    exec($pre_del."weekdays");
  }
  
  if ($rule_start_date) {
    exec($pre."start_date=".$rule_start_date);
  } else {
    exec($pre_del."start_date");
  }
  if ($rule_end_date) {
    exec($pre."stop_date=".$rule_end_date);
  } else {
    exec($pre_del."stop_date");
  }
  if ($rule_start_time) {
    exec($pre."start_time=".$rule_start_time);
  } else {
    exec($pre_del."start_time");
  }
  if ($rule_end_time) {
    exec($pre."stop_time=".$rule_end_time);
  } else {
    exec($pre_del."stop_time");
  }
  if ($target_action) {
    exec($pre."target=".$target_action);
  } else {
    exec($pre_del."target");
  }
  set_firewall_config();

  if($applications) {
    foreach($applications as $application) {
      if($application && $application != "netify.tor") {
        if (file_exists("/usr/lib/appsdb.json")) {
          $domains_json = file_get_contents('/usr/lib/appsdb.json');
          $domains_db = json_decode($domains_json, true);
          foreach($domains_db[$application] as $domain) {
            $records = dns_get_record($domain, DNS_A);
            foreach($records as $record) {
              if($record["type"] == "A") {
                exec("ipset add ".$application." ".$record["ip"]);
              }
            }
          }
        }
        exec("/etc/init.d/dpiagent restart");
        sleep(2);
        exec("/etc/init.d/netifyd restart");
      }
    }
  }
}

function create_firewall_forwardings($fw_no, $src_zones, $dest_zones)
{
  if ($fw_no == "-1") {
    exec("uci add firewall forwarding");
    // exec("uci delete firewall.@forwarding[".$fw_no."]");
  }

  $pre = "uci set firewall.@forwarding[".$fw_no."].";
  exec("uci set firewall.@forwarding[".$fw_no."]=forwarding");
  exec($pre."src=".$src_zones);
  exec($pre."dest=".$dest_zones);

  set_firewall_config();
}

function delete_firewall_zone($zone_no)
{
  exec("uci delete firewall.@zone[".$zone_no."]");
  set_firewall_config();

}

function delete_firewall_rule($rule_no)
{
  $ipset=exec("uci get firewall.@rule[".$rule_no."].ipset");
  if ($ipset) {
    exec("uci delete firewall.apprule".$rule_no);
  }
  exec("uci delete firewall.@rule[".$rule_no."]");
  set_firewall_config();
}

function delete_firewall_forwardings($fw_no)
{
  exec("uci delete firewall.@forwarding[".$fw_no."]");
  set_firewall_config();
}


function set_port_forwarding($port_fw_no, $src_zone, $src_ip, $src, $src_port, $dest, $dest_port, $protocol, $int_zone)
{
  if ($port_fw_no == "-1") {
    exec("uci add firewall redirect");
  }

  $pre = "uci set firewall.@redirect[".$port_fw_no."].";

  exec($pre."src='".$src_zone."'");
  exec($pre."src_ip='".$src_ip."'");
  exec($pre."src_dip='".$src."'");
  exec($pre."src_dport='".$src_port."'");
  exec($pre."dest='".$int_zone."'");
  exec($pre."dest=lan");
  exec($pre."dest_ip='".$dest."'");
  exec($pre."dest_port='".$dest_port."'");
  exec($pre."proto='".$protocol."'");
  exec($pre."target='DNAT'");

  set_firewall_config();
}

function delete_port_forwardings($port_fw_no)
{
  exec("uci delete firewall.@redirect[".$port_fw_no."]");
  set_firewall_config();
}

function set_snat_rule($snat_no, $src_zone, $src_ip, $src_port, $dest_zone, $dest_ip, $dest_port, $snat_ip, $protocol) {

  if ($snat_no == "-1") {
    exec("uci add firewall redirect");
  }

  $pre = "uci set firewall.@redirect[".$snat_no."].";
  exec($pre."src='".$src_zone."'");
  exec($pre."dest='".$dest_zone."'");
  exec($pre."src_ip='".$src_ip."'");
  exec($pre."src_port='".$src_port."'");
  exec($pre."dest_ip='".$dest_ip."'");
  exec($pre."dest_port='".$dest_port."'");
  exec($pre."src_dip='".$snat_ip."'");
  exec($pre."proto='".$protocol."'");
  exec($pre."target='SNAT'");

  set_firewall_config();
}


function delete_snat_rule($snat_id)
{
  exec("uci delete firewall.@redirect[".$snat_id."]");
  set_firewall_config();
}

$GEOIP_COUNTRIES = array( 
  "A1" => "Anonymous Proxy" ,
  "A2" => "Satellite Provider" ,
  "AD" => "Andorra" ,
  "AE" => "United Arab Emirates" ,
  "AF" => "Afghanistan" ,
  "AG" => "Antigua and Barbuda" ,
  "AI" => "Anguilla" ,
  "AL" => "Albania" ,
  "AM" => "Armenia" ,
  "AN" => "Netherlands Antilles" ,
  "AO" => "Angola" ,
  "AP" => "Asia/Pacific Region" ,
  "AQ" => "Antarctica" ,
  "AR" => "Argentina" ,
  "AS" => "American Samoa" ,
  "AT" => "Austria" ,
  "AU" => "Australia" ,
  "AW" => "Aruba" ,
  "AX" => "Aland Islands" ,
  "AZ" => "Azerbaijan" ,
  "BA" => "Bosnia and Herzegovina" ,
  "BB" => "Barbados" ,
  "BD" => "Bangladesh" ,
  "BE" => "Belgium" ,
  "BF" => "Burkina Faso" ,
  "BG" => "Bulgaria" ,
  "BH" => "Bahrain" ,
  "BI" => "Burundi" ,
  "BJ" => "Benin" ,
  "BM" => "Bermuda" ,
  "BN" => "Brunei Darussalam" ,
  "BO" => "Bolivia" ,
  "BR" => "Brazil" ,
  "BS" => "Bahamas" ,
  "BT" => "Bhutan" ,
  "BV" => "Bouvet Island" ,
  "BW" => "Botswana" ,
  "BY" => "Belarus" ,
  "BZ" => "Belize" ,
  "CA" => "Canada" ,
  "CC" => "Cocos (Keeling) Islands" ,
  "CD" => "Congo, The Democratic Republic of the" ,
  "CF" => "Central African Republic" ,
  "CG" => "Congo" ,
  "CH" => "Switzerland" ,
  "CI" => "Cote D'Ivoire" ,
  "CK" => "Cook Islands" ,
  "CL" => "Chile" ,
  "CM" => "Cameroon" ,
  "CN" => "China" ,
  "CO" => "Colombia" ,
  "CR" => "Costa Rica" ,
  "CU" => "Cuba" ,
  "CV" => "Cape Verde" ,
  "CX" => "Christmas Island" ,
  "CY" => "Cyprus" ,
  "CZ" => "Czech Republic" ,
  "DE" => "Germany" ,
  "DJ" => "Djibouti" ,
  "DK" => "Denmark" ,
  "DM" => "Dominica" ,
  "DO" => "Dominican Republic" ,
  "DZ" => "Algeria" ,
  "EC" => "Ecuador" ,
  "EE" => "Estonia" ,
  "EG" => "Egypt" ,
  "EH" => "Western Sahara" ,
  "ER" => "Eritrea" ,
  "ES" => "Spain" ,
  "ET" => "Ethiopia" ,
  "EU" => "Europe" ,
  "FI" => "Finland" ,
  "FJ" => "Fiji" ,
  "FK" => "Falkland Islands (Malvinas)" ,
  "FM" => "Micronesia, Federated States of" ,
  "FO" => "Faroe Islands" ,
  "FR" => "France" ,
  "GA" => "Gabon" ,
  "GB" => "United Kingdom" ,
  "GD" => "Grenada" ,
  "GE" => "Georgia" ,
  "GF" => "French Guiana" ,
  "GG" => "Guernsey" ,
  "GH" => "Ghana" ,
  "GI" => "Gibraltar" ,
  "GL" => "Greenland" ,
  "GM" => "Gambia" ,
  "GN" => "Guinea" ,
  "GP" => "Guadeloupe" ,
  "GQ" => "Equatorial Guinea" ,
  "GR" => "Greece" ,
  "GS" => "South Georgia and the South Sandwich Islands" ,
  "GT" => "Guatemala" ,
  "GU" => "Guam" ,
  "GW" => "Guinea-Bissau" ,
  "GY" => "Guyana" ,
  "HK" => "Hong Kong" ,
  "HN" => "Honduras" ,
  "HR" => "Croatia" ,
  "HT" => "Haiti" ,
  "HU" => "Hungary" ,
  "ID" => "Indonesia" ,
  "IE" => "Ireland" ,
  "IL" => "Israel" ,
  "IM" => "Isle of Man" ,
  "IN" => "India" ,
  "IO" => "British Indian Ocean Territory" ,
  "IQ" => "Iraq" ,
  "IR" => "Iran, Islamic Republic of" ,
  "IS" => "Iceland" ,
  "IT" => "Italy" ,
  "JE" => "Jersey" ,
  "JM" => "Jamaica" ,
  "JO" => "Jordan" ,
  "JP" => "Japan" ,
  "KE" => "Kenya" ,
  "KG" => "Kyrgyzstan" ,
  "KH" => "Cambodia" ,
  "KI" => "Kiribati" ,
  "KM" => "Comoros" ,
  "KN" => "Saint Kitts and Nevis" ,
  "KP" => "Korea, Democratic People's Republic of" ,
  "KR" => "Korea, Republic of" ,
  "KW" => "Kuwait" ,
  "KY" => "Cayman Islands" ,
  "KZ" => "Kazakhstan" ,
  "LA" => "Lao People's Democratic Republic" ,
  "LB" => "Lebanon" ,
  "LC" => "Saint Lucia" ,
  "LI" => "Liechtenstein" ,
  "LK" => "Sri Lanka" ,
  "LR" => "Liberia" ,
  "LS" => "Lesotho" ,
  "LT" => "Lithuania" ,
  "LU" => "Luxembourg" ,
  "LV" => "Latvia" ,
  "LY" => "Libyan Arab Jamahiriya" ,
  "MA" => "Morocco" ,
  "MC" => "Monaco" ,
  "MD" => "Moldova, Republic of" ,
  "ME" => "Montenegro" ,
  "MG" => "Madagascar" ,
  "MH" => "Marshall Islands" ,
  "MK" => "Macedonia" ,
  "ML" => "Mali" ,
  "MM" => "Myanmar" ,
  "MN" => "Mongolia" ,
  "MO" => "Macau" ,
  "MP" => "Northern Mariana Islands" ,
  "MQ" => "Martinique" ,
  "MR" => "Mauritania" ,
  "MS" => "Montserrat" ,
  "MT" => "Malta" ,
  "MU" => "Mauritius" ,
  "MV" => "Maldives" ,
  "MW" => "Malawi" ,
  "MX" => "Mexico" ,
  "MY" => "Malaysia" ,
  "MZ" => "Mozambique" ,
  "NA" => "Namibia" ,
  "NC" => "New Caledonia" ,
  "NE" => "Niger" ,
  "NF" => "Norfolk Island" ,
  "NG" => "Nigeria" ,
  "NI" => "Nicaragua" ,
  "NL" => "Netherlands" ,
  "NO" => "Norway" ,
  "NP" => "Nepal" ,
  "NR" => "Nauru" ,
  "NU" => "Niue" ,
  "NZ" => "New Zealand" ,
  "OM" => "Oman" ,
  "PA" => "Panama" ,
  "PE" => "Peru" ,
  "PF" => "French Polynesia" ,
  "PG" => "Papua New Guinea" ,
  "PH" => "Philippines" ,
  "PK" => "Pakistan" ,
  "PL" => "Poland" ,
  "PM" => "Saint Pierre and Miquelon" ,
  "PR" => "Puerto Rico" ,
  "PS" => "Palestinian Territory, Occupied" ,
  "PT" => "Portugal" ,
  "PW" => "Palau" ,
  "PY" => "Paraguay" ,
  "QA" => "Qatar" ,
  "RE" => "Reunion" ,
  "RO" => "Romania" ,
  "RS" => "Serbia" ,
  "RU" => "Russian Federation" ,
  "RW" => "Rwanda" ,
  "SA" => "Saudi Arabia" ,
  "SB" => "Solomon Islands" ,
  "SC" => "Seychelles" ,
  "SD" => "Sudan" ,
  "SE" => "Sweden" ,
  "SG" => "Singapore" ,
  "SH" => "Saint Helena" ,
  "SI" => "Slovenia" ,
  "SJ" => "Svalbard and Jan Mayen" ,
  "SK" => "Slovakia" ,
  "SL" => "Sierra Leone" ,
  "SM" => "San Marino" ,
  "SN" => "Senegal" ,
  "SO" => "Somalia" ,
  "SR" => "Suriname" ,
  "ST" => "Sao Tome and Principe" ,
  "SV" => "El Salvador" ,
  "SY" => "Syrian Arab Republic" ,
  "SZ" => "Swaziland" ,
  "TC" => "Turks and Caicos Islands" ,
  "TD" => "Chad" ,
  "TF" => "French Southern Territories" ,
  "TG" => "Togo" ,
  "TH" => "Thailand" ,
  "TJ" => "Tajikistan" ,
  "TK" => "Tokelau" ,
  "TL" => "Timor-Leste" ,
  "TM" => "Turkmenistan" ,
  "TN" => "Tunisia" ,
  "TO" => "Tonga" ,
  "TR" => "Turkey" ,
  "TT" => "Trinidad and Tobago" ,
  "TV" => "Tuvalu" ,
  "TW" => "Taiwan" ,
  "TZ" => "Tanzania, United Republic of" ,
  "UA" => "Ukraine" ,
  "UG" => "Uganda" ,
  "UM" => "United States Minor Outlying Islands" ,
  "US" => "United States" ,
  "UY" => "Uruguay" ,
  "UZ" => "Uzbekistan" ,
  "VA" => "Holy See (Vatican City State)" ,
  "VC" => "Saint Vincent and the Grenadines" ,
  "VE" => "Venezuela" ,
  "VG" => "Virgin Islands, British" ,
  "VI" => "Virgin Islands, U.S." ,
  "VN" => "Vietnam" ,
  "VU" => "Vanuatu" ,
  "WF" => "Wallis and Futuna" ,
  "WS" => "Samoa" ,
  "YE" => "Yemen" ,
  "YT" => "Mayotte" ,
  "ZA" => "South Africa" ,
  "ZM" => "Zambia" ,
  "ZW" => "Zimbabwe" );

?>


