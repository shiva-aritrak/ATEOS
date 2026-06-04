<?php

$MAX_VPN_CLIENTS = exec("uci get anexgate.license.ssl_vpn_max");
$MAX_GRE_CLIENTS = exec("uci get anexgate.license.gre_vpn_max");
$MAX_IPSEC_TUNNELS = exec("uci get anexgate.license.ipsec_vpn_max");
$MAX_LAN_CLIENTS = exec("uci get anexgate.license.user_limit");
$SUPPORTED_PROTOCOLS = array('tcp','udp','icmp','esp','ah','sctp','all');
$PORT_FORWARD_SUPPORTED_PROTOCOLS = array('tcp','udp','tcp/udp', 'all');
$DEVICE_MODEL = exec("uci get anexgate.license.model");

$DAYS_OF_WEEK = array('Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun');

$IPSEC_ENC_ALG_OPTIONS = array('3DES', 'DES', 'AES128', 'AES192', 'AES256', 'AES128GCM8', 'AES128GCM12', 'AES128GCM16', 'AES256GCM8', 'AES256GCM12', 'AES256GCM16', 'CAMELLIA128', 'CAMELLIA192', 'CAMELLIA256', 'CAMELLIA128CTR', 'CAMELLIA192CTR', 'CAMELLIA256CTR', 'CHACHA20POLY1305');
$IPSEC_DH_GROUPS = array("None", "modp768","modp1024","modp1536","modp2048","modp3072", "ecp192", "ecp224", "ecp256", "ecp384", "curve25519", "curve448");
$IPSEC_P1_HASH_ALG_OPTIONS = array('None', 'MD5','SHA1', 'SHA256', 'SHA384', 'SHA512', 'PRFMD5', 'PRFSHA1', 'PRFAESXCBC', 'PRFAESCMAC', 'PRFSHA256', 'PRFSHA384', 'PRFSHA512');
$IPSEC_P2_HASH_ALG_OPTIONS = array('None', 'MD5','SHA1', 'SHA256', 'SHA384', 'SHA512', 'PRFMD5', 'PRFSHA1', 'PRFAESXCBC', 'PRFAESCMAC', 'PRFSHA256', 'PRFSHA384', 'PRFSHA512');
$IPSEC_DPD_ACTIONS = array('None','Clear', 'Hold', 'Restart');
$OPENVPN_CIPHERS = array("None", "DES-CBC", "RC2-CBC", "BF-CBC", "AES-128-CBC", "AES-192-CBC", "AES-256-CBC");
$OPENVPN_AUTH_ALG = array("None", "SHA1", "SHA256", "SHA512");

$WIRELESS_CHANNEL_WIDTH = array("5", "10", "20");
$WIRELESS_50_CHANNEL_WIDTH = array("20", "40", "80");
$WIRELESS_24_MIN_MODES = array("none", "802.11b", "802.11g", "802.11n");
$WIRELESS_50_MIN_MODES = array("none", "802.11n", "802.11ac");
$WIRELESS_24_HW_MODES = array("11b", "11g", "11n");
$WIRELESS_50_HW_MODES = array("11a", "11ac");
$WIRELESS_ENCRYPTION_CHOICES = array( "none" => "No Encryption", "psk+tkip+ccmp" => "WPA (TKIP, AES)", "psk+tkip+aes" => "WPA (TKIP, CCMP)", "psk+tkip" => "WPA (TKIP)", "psk+aes" => "WPA (AES)", "psk" => "WPA", "psk2+tkip+ccmp" => "WPA2 (TKIP, CCMP)", "psk2+tkip+aes" => "WPA2 (TKIP, AES)", "psk2+tkip" => "WPA2 (TKIP)", "psk2+aes" => "WPA2 (AES)", "psk2" => "WPA2", "psk-mixed+tkip+ccmp" => "WPA/WPA2 Mixed (TKIP, CCMP)", "psk-mixed+tkip+aes" => "WPA/WPA2 Mixed (TKIP, AES)", "psk-mixed+tkip" => "WPA/WPA2 Mixed (TKIP)", "psk-mixed+aes" => "WPA/WPA2 Mixed (AES)", "psk-mixed" => "WPA/WPA2 Mixed", "sae" => "WPA3", "sae-mixed" => "WPA2/WPA3 Personal (PSK/SAE) Mixed", "wpa2" =>	"WPA2 Enterprise", "wpa2+ccmp" =>	"WPA2 Enterprise (CCMP)", "wpa2+tkip" =>	"WPA2 Enterprise (TKIP)", "wpa2+aes" =>	"WPA2 Enterprise (AES)", "wpa2+tkip+aes" =>	"WPA2 Enterprise (TKIP, AES)", "wpa2+tkip+ccmp" =>	"WPA2 Enterprise (TKIP, CCMP)", "owe" => "Opportunistic Wireless Encryption (OWE)");

$WIRELESS_24_CHANNELS = array("auto", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11");
$WIRELESS_50_CHANNELS = array("auto", "36", "40", "44", "48", "52", "56", "60", "64", "100", "104", "108", "112", "116", "120", "124", "128", "132", "136", "140");
$SUPPORTED_TIMEZONES = array("ACST-9:30", "AEST-10", "AWST-8", "CST-8", "EAT-3", "EET-2", "EST5", "GMT0", "HKT-8", "IST-5:30", "JST-9", "KST-9", "MSK-3", "MST7", "PKT-5", "UTC0", "WIB-7", "WIT-9", "WITA-8");
$P1_AUTH_METHODS = array( "psk" => "Pre Shared Key", "rsasig" => "RSA Signature", "xauth_psk_server" => "XAuth PSK", "xauth_rsa_server" => "XAuth RSA" );
$NWMON_ACTIONS = array( "none" => "Log Only", "nwrestart" => "Restart Network", "reinit" => "Restart Interface", "reboot" => "Reboot Device" );
$INTERFACE_METRICS = array( "wan1" => "50", "wan2" => "51", "3g" => "52", "usb0" => "53" , "wwan0" => "54" , "wwan1" => "55" );

$SNMP_SECURITY_MODES = array( "0" => "None", "1" => "Authorized", "2" => "Private" );
$SNMP_AUTH_MODES = array("MD5", "SHA");
$SNMP_ENC_MODES = array("DES", "AES");

$WLAN_MAC_FILTERING_MODES = array('disable' => 'None', 'allow' => "Whitelist MACs", "deny" => "Blacklist MACs" );
$OSPF_AUTH_TYPES = array('None' => 'None');
$IPV6_SUFFIX_CHOICES = array('' => "None", '::1' => 'Default (::1)', 'eui64' => "EUI-64", "random" => "Random", "custom" => "Custom" );
$LLDP_CLASSES = array( "1" => "Generic Endpoint", "2" => "Media Endpoint", "3" => "Communication Device Endpoints", "4" => "Network Connectivity Device" );

$IPV6_REQPREFIX_CHOICES = array('auto' => 'Automatic', 'no' => "Disabled", "custom" => "Custom" );
$IPV6_REQADDR_CHOICES = array('try' => 'Try', 'force' => "Force", "none" => "Disabled");

$PDPTYPE_CHOICES = array('IP' => "IPv4 Only", 'IPV6' => "IPv6 Only", 'IPV4V6' => "IPv4+IPv6 DS");
$IP_PROTO_CHOICES = array('ipv4' => "IPv4", 'ipv6' => "IPv6");

$LOG_EXPORT_PROTOCOLS = array('tcp','udp');

$IPSEC_DEBUG_LEVELS = array('-1' => "No Log", '0' => "Basic Audit Log", '1' => "Log Errors", '2' => "Log Control Flow", '3' => "Log Raw Content", '4' => "Log all (Even Secrets)");

$DHCPV6_SERVER_MODES = array("0" => "Stateless Only", "1" => "Stateless + Stateful", "2" => "Stateful Only");
$DHCPV6_MODES = array("disabled" => 'Disabled', "server" => "Server Mode", "relay" => 'Relay Mode');
$DHCPV6_RA_MODES = array("disabled" => 'Disabled', "server" => "Server Mode", "relay" => 'Relay Mode');
$DHCPV6_RA_ROUTE_PREFERENCE = array("disabled" => 'Disabled', "high" => 'High', "medium" => "Medium", "low" => 'Low');
$DHCPV6_NDP_MODES = array("disabled" => 'Disabled', "relay" => 'Relay Mode');
$SIM_NETWORK_PROVIDERS = array("405861" => "JIO 4G Karnataka IN", "40444" => "Idea Karnataka IN", "40445" => "Airtel Karnataka IN", "40486" => "Vodafone Karnataka IN", "40471" => "CellOne Karnataka IN");

$SNMPENCRYPT_TYPES = array("noAuthNoPriv" => "noAuthNoPriv", "authNoPriv" => "authNoPriv", "authPriv" => "authPriv");
$SNMPAUTHPROTOCOLS = array("SHA" => "SHA", "MD5" => "MD5");
$SNMPPRIVPROTOCOLS = array("AES" => "AES", "DES" => "DES");

$RULES_APP_LIST = array(
    "AFP" => "AFP (P)",
    "AJP" => "AJP (P)",
    "AMQP" => "AMQP (P)",
    "AVAST" => "AVAST (P)",
    "AVASTSecureDNS" => "AVASTSecureDNS (P)",
    "netify.accuweather" => "Accuweather (A)",
    "netify.adobe" => "Adobe (A)",
    "netify.adobe-ads" => "Adobe Ads (A)",
    "netify.adsafeprotected" => "AD Safe Protected (A)",
    "netify.agafurretor-com" => "Agafurretor (A)",
    "netify.airbnb" => "Airbnb (A)",
    "netify.akamai" => "Akamai (A)",
    "netify.alibaba" => "Alibaba (A)",
    "Alibaba/Cloud" => "Alibaba Cloud (P)",
    "netify.alipay" => "Alipay (A)",
    "netify.amazon" => "Amazon (A)",
    "netify.amazon-advertising" => "Amazon Advertising (A)",
    "netify.amazon-alexa" => "Amazon Alexa (A)",
    "netify.amazon-aws" => "Amazon AWS (A)",
    "netify.amazon-cloudfront" => "Amazon Cloudfront (A)",
    "netify.amazon-devices" => "Amazon Devices (A)",
    "netify.amazon-prime" => "Amazon Prime (A)",
    "AmongUs" => "AmongUs (P)",
    "netify.android" => "Android (A)",
    "netify.apple-icloud" => "Apple iCloud (A)",
    "netify.apple-id" => "Apple ID (A)",
    "netify.apple-itunes" => "Apple iTunes (A)",
    "netify.apple-mail" => "Apple Mail (A)",
    "netify.apple-siri" => "Apple Siri (A)",
    "Apple/Push" => "Apple Push (P)",
    "netify.appnexus" => "Appnexus (A)",
    "Armagetron" => "Armagetron (P)",
    "netify.autodesk" => "Autodesk (A)",
    "netify.avast" => "Avast (A)",
    "netify.avira" => "Avira (A)",
    "netify.azure" => "Azure (A)",
    "netify.azure-front-door" => "Azure Front Door (A)",
    "netify.bbc" => "BBC (A)",
    "BGP" => "BGP (P)",
    "BJNP" => "BJNP (P)",
    "netify.badoo" => "Badoo (A)",
    "netify.baidu" => "Baidu (A)",
    "netify.barracuda" => "Barracuda (A)",
    "netify.bing" => "Bing (A)",
    "BitTorrent" => "BitTorrent (P)",
    "netify.bitdefender" => "Bitdefender (A)",
    "netify.braintree" => "Braintree (A)",
    "netify.bytedance" => "Bytedance (A)",
    "CAPWAP" => "CAPWAP (P)",
    "CHECKMK" => "CHECKMK (P)",
    "netify.cnn" => "CNN (A)",
    "COAP" => "COAP (P)",
    "CSGO" => "CSGO (P)",
    "netify.candy-crush" => "Candy Crush (A)",
    "netify.casalemedia" => "Casalemedia (A)",
    "Cassandra" => "Cassandra (P)",
    "CheckPointHA" => "CheckPointHA (P)",
    "netify.cisco-umbrella" => "Cisco Umbrella (A)",
    "Cisco/HSRP" => "Cisco HSRP (P)",
    "Cisco/Skinny" => "Cisco Skinny (P)",
    "Cisco/VPN" => "Cisco VPN (P)",
    "Citrix" => "Citrix (P)",
    "netify.cloudflare" => "Cloudflare (A)",
    "netify.coinbase" => "Coinbase (A)",
    "Collectd" => "Collectd (P)",
    "netify.conduit-toolbar" => "Conduit Toolbar (A)",
    "netify.connectwise" => "Connectwise (A)",
    "Corba" => "Corba (P)",
    "netify.crashlytics" => "Crashlytics (A)",
    "netify.crestron" => "Crestron (A)",
    "netify.criteo" => "Criteo (A)",
    "Crossfire" => "Crossfire (P)",
    "netify.crowdstrike" => "Crowdstrike (A)",
    "CryNetwork" => "CryNetwork (P)",
    "DHCP" => "DHCP (P)",
    "DHCPv6" => "DHCPv6 (P)",
    "DNP3" => "DNP3 (P)",
    "DNS" => "DNS (P)",
    "DNSCrypt" => "DNSCrypt (P)",
    "DRDA" => "DRDA (P)",
    "DTLS" => "DTLS (P)",
    "netify.datto" => "Datto (A)",
    "netify.dazn" => "Dazn (A)",
    "netify.deep-instinct" => "Deep Instinct (A)",
    "netify.deezer" => "Deezer (A)",
    "netify.der-spiegel" => "Der Spiegel (A)",
    "Diameter" => "Diameter (P)",
    "netify.digicert" => "Digicert (A)",
    "netify.discord" => "Discord (A)",
    "netify.disney" => "Disney (A)",
    "netify.disney-plus" => "Disney Plus (A)",
    "netify.disqus" => "Disqus (A)",
    "DoH" => "DoH (P)",
    "DoQ" => "DoQ (P)",
    "DoT" => "DoT (P)",
    "netify.docker" => "Docker (A)",
    "netify.docusign" => "Docusign (A)",
    "Dofus" => "Dofus (P)",
    "netify.doubleverify" => "Doubleverify (A)",
    "netify.dropbox" => "Dropbox (A)",
    "Dropbox" => "Dropbox (P)",
    "netify.dyndns" => "Dyndns (A)",
    "EAQ" => "EAQ (P)",
    "EGP" => "EGP (P)",
    "netify.ebay" => "Ebay (A)",
    "ElasticSearch" => "ElasticSearch (P)",
    "netify.epic-games" => "Epic Games (A)",
    "netify.espn" => "Espn (A)",
    "netify.f-secure" => "F Secure (A)",
    "FIX" => "FIX (P)",
    "FTP/C" => "FTP/C (P)",
    "FTP/D" => "FTP/D (P)",
    "FTP/S" => "FTP/S (P)",
    "netify.facebook" => "Facebook (A)",
    "FastCGI" => "FastCGI (P)",
    "netify.fastly" => "Fastly (A)",
    "netify.fifa" => "Fifa (A)",
    "netify.firebase" => "Firebase (A)",
    "netify.firefox" => "Firefox (A)",
    "netify.fitbit" => "Fitbit (A)",
    "netify.freelancer" => "Freelancer (A)",
    "GRE" => "GRE (P)",
    "GTP" => "GTP (P)",
    "GTP/C" => "GTP/C (P)",
    "GTP/P" => "GTP/P (P)",
    "GTP/U" => "GTP/U (P)",
    "Genshin/Impact" => "Genshin Impact (P)",
    "Git" => "Git (P)",
    "netify.github" => "Github (A)",
    "netify.gitlab" => "Gitlab (A)",
    "netify.gmail" => "Gmail (A)",
    "Gnutella" => "Gnutella (P)",
    "netify.google-ads" => "Google Ads (A)",
    "netify.google-authentication" => "Google Authentication (A)",
    "netify.google-cloud" => "Google Cloud (A)",
    "netify.google-hosted" => "Google Hosted (A)",
    "netify.google-maps" => "Google Maps (A)",
    "netify.google-play" => "Google Play (A)",
    "Google/Meet/Duo" => "Google Meet/Duo (P)",
    "netify.goto" => "Goto (A)",
    "netify.grammarly" => "Grammarly (A)",
    "netify.gravatar" => "Gravatar (A)",
    "netify.gsuite" => "Gsuite (A)",
    "Guildwars" => "Guildwars (P)",
    "H323" => "H323 (P)",
    "HP/VirtGrp" => "HP VirtGrp (P)",
    "HTTP" => "HTTP (P)",
    "HTTP/Connect" => "HTTP/Connect (P)",
    "HTTP/Proxy" => "HTTP/Proxy (P)",
    "HTTP/S" => "HTTP/S (P)",
    "HalfLife2" => "HalfLife2 (P)",
    "netify.helpshift" => "Helpshift (A)",
    "netify.hsbc" => "Hsbc (A)",
    "netify.huawei" => "Huawei (A)",
    "netify.hulu" => "Hulu (A)",
    "IAX" => "IAX (P)",
    "ICMP" => "ICMP (P)",
    "ICMPv6" => "ICMPv6 (P)",
    "IEC60870/5/104" => "IEC60870/5/104 (P)",
    "IGMP" => "IGMP (P)",
    "IMAP" => "IMAP (P)",
    "IMAP/S" => "IMAP/S (P)",
    "IPP" => "IPP (P)",
    "IPSEC" => "IPSEC (P)",
    "IPinIP" => "IPinIP (P)",
    "IRC" => "IRC (P)",
    "IRC/S" => "IRC/S (P)",
    "IceCast" => "IceCast (P)",
    "netify.icloud-private-relay" => "iCloud Private Relay (A)",
    "netify.iheartradio" => "iHeart Radio (A)",
    "netify.indeed" => "Indeed (A)",
    "netify.inmobi" => "Inmobi (A)",
    "netify.instagram" => "Instagram (A)",
    "netify.intuit" => "Intuit (A)",
    "KISMET" => "KISMET (P)",
    "KakaoTalk/Voice" => "KakaoTalk Voice (P)",
    "netify.kaspersky" => "Kaspersky (A)",
    "Kerberos" => "Kerberos (P)",
    "netify.khan-academy" => "Khan Academy (A)",
    "Kontiki" => "Kontiki (P)",
    "netify.kwai" => "Kwai (A)",
    "LDAP" => "LDAP (P)",
    "LISP" => "LISP (P)",
    "LLMNR" => "LLMNR (P)",
    "netify.lastfm" => "Lastfm (A)",
    "netify.lendingtree" => "Lendingtree (A)",
    "netify.lets-encrypt" => "Lets Encrypt (A)",
    "netify.lijit" => "Lijit (A)",
    "netify.likee" => "Likee (A)",
    "netify.limelight-networks" => "Limelight Networks (A)",
    "Line/Call" => "Line Call (P)",
    "netify.linkedin" => "Linkedin (A)",
    "netify.linux-mint" => "Linux Mint (A)",
    "LotusNotes" => "LotusNotes (P)",
    "MDNS" => "MDNS (P)",
    "MGCP" => "MGCP (P)",
    "netify.mlb" => "MLB (A)",
    "MPEG/Dash" => "MPEG/Dash (P)",
    "MPEGTS" => "MPEGTS (P)",
    "MQTT" => "MQTT (P)",
    "MQTT/S" => "MQTT/S (P)",
    "netify.msn" => "MSN (A)",
    "MSSQL/TDS" => "MSSQL/TDS (P)",
    "MYSQL" => "MYSQL (P)",
    "netify.magnite" => "Magnite (A)",
    "netify.mailchimp" => "Mailchimp (A)",
    "netify.malwarebytes" => "Malwarebytes (A)",
    "netify.mapbox" => "Mapbox (A)",
    "MapleStory" => "MapleStory (P)",
    "netify.mastercard" => "Mastercard (A)",
    "netify.mcafee" => "Mcafee (A)",
    "Megaco" => "Megaco (P)",
    "Memcached" => "Memcached (P)",
    "Meraki/Cloud" => "Meraki Cloud (P)",
    "netify.microsoft" => "Microsoft (A)",
    "netify.microsoft-authentication" => "Microsoft Authentication (A)",
    "netify.microsoft-onedrive" => "Microsoft Onedrive (A)",
    "netify.minecraft" => "Minecraft (A)",
    "Mining" => "Mining (P)",
    "netify.miui" => "Miui (A)",
    "netify.moatads" => "Moatads (A)",
    "Modbus" => "Modbus (P)",
    "MongoDB" => "MongoDB (P)",
    "Munin" => "Munin (P)",
    "NAT/PMP" => "NAT/PMP (P)",
    "NATS" => "NATS (P)",
    "netify.nba" => "NBA (A)",
    "NETBIOS" => "NETBIOS (P)",
    "netify.nfl" => "NFL (A)",
    "NFS" => "NFS (P)",
    "netify.nhl" => "NHL (A)",
    "NNTP" => "NNTP (P)",
    "NNTP/S" => "NNTP/S (P)",
    "NOE" => "NOE (P)",
    "netify.nsa" => "NSA (A)",
    "netify.ntp" => "NTP (A)",
    "NTP" => "NTP (P)",
    "netify.napster" => "Napster (A)",
    "NestLog" => "NestLog (P)",
    "NetFlow" => "NetFlow (P)",
    "netify.netflix" => "Netflix (A)",
    "netify.netify" => "Netify (A)",
    "netify.new-relic" => "New Relic (A)",
    "netify.new-york-times" => "New York Times (A)",
    "netify.nintendo" => "Nintendo (A)",
    "Nintendo" => "Nintendo (P)",
    "netify.nist" => "Nist (A)",
    "OOKLA" => "OOKLA (P)",
    "OSPF" => "OSPF (P)",
    "netify.office-365" => "Office 365 (A)",
    "OpenVPN" => "OpenVPN (P)",
    "netify.openwrt" => "Openwrt (A)",
    "Oracle" => "Oracle (P)",
    "netify.outbrain" => "Outbrain (A)",
    "netify.outlook" => "Outlook (A)",
    "PGM" => "PGM (P)",
    "PGSQL" => "PGSQL (P)",
    "PIM" => "PIM (P)",
    "POP3" => "POP3 (P)",
    "POP3/S" => "POP3/S (P)",
    "PPStream" => "PPStream (P)",
    "PPTP" => "PPTP (P)",
    "netify.palo-alto-networks" => "Palo Alto Networks (A)",
    "netify.panda-security" => "Panda Security (A)",
    "netify.pandora" => "Pandora (A)",
    "netify.paypal" => "Paypal (A)",
    "netify.philips" => "Philips (A)",
    "netify.pinterest" => "Pinterest (A)",
    "netify.playstation" => "Playstation (A)",
    "netify.plex" => "Plex (A)",
    "netify.pluralsight" => "Pluralsight (A)",
    "netify.pubnub" => "Pubnub (A)",
    "netify.pusher" => "Pusher (A)",
    "QQ" => "QQ (P)",
    "QQLive" => "QQLive (P)",
    "QUIC" => "QUIC (P)",
    "netify.quora" => "Quora (A)",
    "RADIUS" => "RADIUS (P)",
    "RDP" => "RDP (P)",
    "RPC" => "RPC (P)",
    "RSH" => "RSH (P)",
    "RSYNC" => "RSYNC (P)",
    "RTCP" => "RTCP (P)",
    "RTMP" => "RTMP (P)",
    "RTP" => "RTP (P)",
    "RTSP" => "RTSP (P)",
    "RX" => "RX (P)",
    "RakNet" => "RakNet (P)",
    "netify.rakuten" => "Rakuten (A)",
    "netify.rapid7" => "Rapid7 (A)",
    "netify.red-hat" => "Red Hat (A)",
    "netify.reddit" => "Reddit (A)",
    "Redis" => "Redis (P)",
    "RemoteScan" => "RemoteScan (P)",
    "netify.reverse-dns" => "Reverse Dns (A)",
    "Riot/Games" => "Riot Games (P)",
    "netify.roblox" => "Roblox (A)",
    "netify.rspamd" => "Rspamd (A)",
    "S7comm" => "S7comm (P)",
    "SAP" => "SAP (P)",
    "SCTP" => "SCTP (P)",
    "SD/RTN" => "SD/RTN (P)",
    "SFlow" => "SFlow (P)",
    "SIP" => "SIP (P)",
    "SIP/S" => "SIP/S (P)",
    "SMBv1" => "SMBv1 (P)",
    "SMBv23" => "SMBv23 (P)",
    "SMPP" => "SMPP (P)",
    "SMTP" => "SMTP (P)",
    "SMTP/S" => "SMTP/S (P)",
    "SNMP" => "SNMP (P)",
    "SOAP" => "SOAP (P)",
    "SOCKS" => "SOCKS (P)",
    "SOMEIP" => "SOMEIP (P)",
    "SSDP" => "SSDP (P)",
    "SSH" => "SSH (P)",
    "STUN" => "STUN (P)",
    "SYSLOG" => "SYSLOG (P)",
    "SYSLOG/S" => "SYSLOG/S (P)",
    "netify.salesforce" => "Salesforce (A)",
    "netify.samsung-tv" => "Samsung Tv (A)",
    "netify.senderscore" => "Senderscore (A)",
    "netify.sharepoint" => "Sharepoint (A)",
    "netify.shopee" => "Shopee (A)",
    "netify.shopify" => "Shopify (A)",
    "SignalCall" => "SignalCall (P)",
    "netify.sina" => "Sina (A)",
    "netify.siriusxm" => "Siriusxm (A)",
    "Skype/Teams" => "Teams (P)",
    "Skype/Teams/Call" => "Teams Call (P)",
    "netify.skyticket" => "Skyticket (A)",
    "netify.slack" => "Slack (A)",
    "netify.smartadserver" => "Smartadserver (A)",
    "netify.snapchat" => "Snapchat (A)",
    "Snapchat/Call" => "Snapchat Call (P)",
    "netify.softether" => "Softether (A)",
    "netify.sophos" => "Sophos (A)",
    "netify.soundcloud" => "Soundcloud (A)",
    "netify.spameatingmonkey" => "Spam Eating Monkey (A)",
    "netify.spamhaus" => "Spamhaus (A)",
    "netify.spotify" => "Spotify (A)",
    "Spotify" => "Spotify (P)",
    "netify.stack-overflow" => "Stack Overflow (A)",
    "netify.stackexchange" => "Stack Exchange (A)",
    "Starcraft" => "Starcraft (P)",
    "netify.steam" => "Steam (A)",
    "Steam" => "Steam (P)",
    "netify.supercell" => "Supercell (A)",
    "netify.symantec" => "Symantec (A)",
    "Syncthing" => "Syncthing (P)",
    "TFTP" => "TFTP (P)",
    "TINC" => "TINC (P)",
    "TLS" => "TLS (P)",
    "netify.tor" => "TOR (A)",
    "TPLINK/SHP" => "TPLINK/SHP (P)",
    "TVUplayer" => "TVUplayer (P)",
    "netify.taboola" => "Taboola (A)",
    "Tailscale" => "Tailscale (P)",
    "Targus/Dataspeed" => "Targus/Dataspeed (P)",
    "TeamSpeak" => "TeamSpeak (P)",
    "TeamViewer" => "TeamViewer (P)",
    "netify.telegram" => "Telegram (A)",
    "Telegram" => "Telegram (P)",
    "Telnet" => "Telnet (P)",
    "Teredo" => "Teredo (P)",
    "netify.tesla" => "Tesla (A)",
    "netify.thomson-reuters" => "Thomson Reuters (A)",
    "netify.threema" => "Threema (A)",
    "Threema" => "Threema (P)",
    "TiVo/Connect" => "TiVo/Connect (P)",
    "netify.tidal" => "Tidal (A)",
    "netify.tiktok" => "Tiktok (A)",
    "netify.tinder" => "Tinder (A)",
    "TocaBoca" => "TocaBoca (P)",
    "TruPhone" => "TruPhone (P)",
    "netify.tumblr" => "Tumblr (A)",
    "Tuya/LP" => "Tuya/LP (P)",
    "netify.twitch" => "Twitch (A)",
    "netify.twitter" => "Twitter (A)",
    "UBNTAC2" => "UBNTAC2 (P)",
    "netify.ubiquiti" => "Ubiquiti (A)",
    "netify.ubisoft" => "Ubisoft (A)",
    "netify.ubuntu" => "Ubuntu (A)",
    "UltraSurf" => "UltraSurf (P)",
    "netify.unity" => "Unity (A)",
    "Unknown" => "Unknown (P)",
    "VHUA" => "VHUA (P)",
    "VMWARE" => "VMWARE (P)",
    "VNC" => "VNC (P)",
    "VRRP" => "VRRP (P)",
    "VXLAN" => "VXLAN (P)",
    "Viber" => "Viber (P)",
    "netify.vimeo" => "Vimeo (A)",
    "netify.visa" => "Visa (A)",
    "netify.visualstudio" => "Visualstudio (A)",
    "netify.vivo" => "Vivo (A)",
    "WSD" => "WSD (P)",
    "Warcraft3" => "Warcraft3 (P)",
    "netify.webroot" => "Webroot (A)",
    "Websocket" => "Websocket (P)",
    "netify.weibo" => "Weibo (A)",
    "WhatsApp" => "WhatsApp (P)",
    "WhatsApp/Call" => "WhatsApp/Call (P)",
    "netify.whatsapp" => "Whatsapp (A)",
    "Whois/DAS" => "Whois/DAS (P)",
    "netify.wikimedia" => "Wikimedia (A)",
    "netify.wikipedia" => "Wikipedia (A)",
    "netify.windows" => "Windows (A)",
    "netify.windows-update" => "Windows Update (A)",
    "WireGuard" => "WireGuard (P)",
    "netify.wish" => "Wish (A)",
    "WoKungFu" => "WoKungFu (P)",
    "WoW" => "WoW (P)",
    "XDMCP" => "XDMCP (P)",
    "XMPP" => "XMPP (P)",
    "netify.xbox" => "Xbox (A)",
    "Xbox" => "Xbox (P)",
    "netify.xbox-live" => "Xbox Live (A)",
    "netify.xero" => "Xero (A)",
    "Xiaomi" => "Xiaomi (P)",
    "netify.yahoo" => "Yahoo (A)",
    "netify.yahoo-ads" => "Yahoo Ads (A)",
    "netify.yahoo-mail" => "Yahoo Mail (A)",
    "netify.youtube" => "Youtube (A)",
    "Z39/50" => "Z39/50 (P)",
    "ZMQ" => "ZMQ (P)",
    "ZOOM" => "ZOOM (P)",
    "Zabbix" => "Zabbix (P)",
    "netify.zattoo" => "Zattoo (A)",
    "Zattoo" => "Zattoo (P)",
    "netify.zendesk" => "Zendesk (A)"
);


function dashboard_sim_devices()
{
  $out = "";
  $ret = 0;
  exec("ls /dev/ttyUSB*", $out, $ret);
  if (count($out) == 12 || count($out) == 16) {
    return array ("wwan0", "wwan1", "wwan2", "wwan3");
  } else if (count($out) >= 9 && count($out) <= 12) {
    return array ("wwan0", "wwan1", "wwan2");
  } else if (count($out) >= 6 && count($out) <= 8) {
    return array ("wwan0", "wwan1");
  } else if (count($out) >= 3 && count($out) <= 4) {
    return array ("wwan0");
  } else {
    return array ("");
  }
}

function lookup_wifi_devices($dev)
{
  $out = exec("uci get wireless.default_".$dev.".device");
  if ($dev == $out) {
    return '1';
  } else {
    return '0';
  }
}

function lookup_usb_devices($dev)
{
  $out = "";
  $ret = 0;
  exec("ls /dev/ttyUSB*", $out, $ret);
  if ($dev == "wwan0") {
    if (count($out) == 3 || count($out) == 4 || count($out) == 6 || count($out) == 8 || count($out) == 9 || count($out) == 12 || count($out) == 16) {
      return '1';
    }
    else {
      return '0';
    }
  }

  else if ($dev == "wwan1") {
    if (count($out) == 6 || count($out) == 8  || count($out) == 9 || count($out) == 12 || count($out) == 16) {
      return '1';
    }
    else {
      return '0';
    }
  }

  else if ($dev == "wwan2") {
    if (count($out) == 9 || count($out) == 12 || count($out) == 16) {
      return '1';
    }
    else {
      return '0';
    }
  }
  else if ($dev == "wwan3") {
    if (count($out) == 12 || count($out) == 16) {
      return '1';
    }
    else {
      return '0';
    }
  }
  else {
    return '0';
  }
}

function show_qos_proto($proto)
{
  if(!$proto) {
    return "TCP/UDP";
  }
  return strtoupper($proto);
}

function show_qos_hosts($host)
{
  if(!$host) {
    return "Anywhere";
  }
  return strtoupper($host);
}

function show_qos_ports($ports)
{
  if(!$ports) {
    return "All";
  }
  return strtoupper($ports);
}

function show_force_route($on_link_status)
{
  if ($on_link_status == "1" ) {
    return "Yes";
  }
  else {
    return "No";
  }
}

function show_route_enabled_disabled($string_status)
{
  if ($string_status == "route" ) {
    return "Enabled";
  }
  else {
    return "Disabled";
  }
}

function show_enabled_disabled($string_boolean)
{
  if ($string_boolean == "1" ) {
    return "Enabled";
  }
  else {
    return "Disabled";
  }
}

function show_disabled_enabled($string_boolean)
{
  if ($string_boolean == "1" ) {
    return "Disabled";
  }
  else {
    return "Enabled";
  }
}


function show_firewall_rule_enabled_disabled($string_boolean)
{
  if ($string_boolean == "no" ) {
    return "Disabled";
  }
  else {
    return "Enabled";
  }
}

function get_wwan_device($wwan_interface)
{
	$out = "";
	$ret = 0;
  exec("ls /dev/ttyUSB*", $out, $ret);
  if ($wwan_interface == "wwan0") {
		return "/dev/ttyUSB2";
	}
	else if ($wwan_interface == "wwan1") {
		if (count($out) == 6) {
			return "/dev/ttyUSB5";
		} else if (count($out) == 8) {
			return "/dev/ttyUSB6";
		} else if (count($out) == 9) {
                        return "/dev/ttyUSB5";
		} else if (count($out) == 12) {
                        return "/dev/ttyUSB5";
		} else if (count($out) == 16) {
                        return "/dev/ttyUSB6";
		}
		return "/dev/null";
	} else if ($wwan_interface == "wwan2") {
		if (count($out) == 9) {
			return "/dev/ttyUSB8";
		} else if (count($out) == 12) {
			return "/dev/ttyUSB8";
		} else if (count($out) == 16) {
                        return "/dev/ttyUSB10";
		}
		return "/dev/null";
	} else if ($wwan_interface == "wwan3") {
		if (count($out) == 16) {
			return "/dev/ttyUSB14";
		} else if (count($out) == 12) {
			return "/dev/ttyUSB11";
		}
		return "/dev/null";
	}
}

// EC200T-ttyUSB0,1,2,3,4,5 (Network: /dev/ttyUSB2, /dev/ttyUSB5) Signal:gcom -d /dev/ttyUSB1 , gcom -d /dev/ttyUSB4
// EC20/25-ttyUSB0,1,2,3,4,5,6,7(Network: /dev/ttyUSB2, /dev/ttyUSB6)Signal:gcom -d /dev/ttyUSB3 , gcom -d /dev/ttyUSB7
function get_signal_wwan_device($wwan_interface)
{
	$out = "";
	$ret = 0;
  exec("ls /dev/ttyUSB*", $out, $ret);
	if ($wwan_interface == "wwan0")  {
		if (count($out) == 3) {
			return "/dev/ttyUSB1";
		} else if (count($out) == 4) {
			return "/dev/ttyUSB3";
		} else if (count($out) == 6) {
                        return "/dev/ttyUSB1";
		} else if (count($out) == 8) {
                        return "/dev/ttyUSB3";
		} else if (count($out) == 9) {
                        return "/dev/ttyUSB1";
		} else if (count($out) == 12) {
                        return "/dev/ttyUSB1";
		} else if (count($out) == 16) {
                        return "/dev/ttyUSB3";
		}
		return "/tmp/doesnotexist";
	}
	else if ($wwan_interface == "wwan1") {
		if (count($out) == 6) {
			return "/dev/ttyUSB4";
		} else if (count($out) == 8) {
			return "/dev/ttyUSB7";
		} else if (count($out) == 9) {
                        return "/dev/ttyUSB4";
		} else if (count($out) == 12) {
                        return "/dev/ttyUSB4";
		} else if (count($out) == 16) {
                        return "/dev/ttyUSB7";
		}
		return "/tmp/doesnotexist";
	}
	else if ($wwan_interface == "wwan2") {
		if (count($out) == 9) {
			return "/dev/ttyUSB7";
		} else if (count($out) == 12) {
			return "/dev/ttyUSB7";
		} else if (count($out) == 16) {
                        return "/dev/ttyUSB11";
		}
		return "/tmp/doesnotexist";
	}
	else if ($wwan_interface == "wwan3") {
		if (count($out) == 16) {
			return "/dev/ttyUSB15";
		} else if (count($out) == 12) {
			return "/dev/ttyUSB10";
		}
		return "/tmp/doesnotexist";
	}
}


function show_up_down($string_boolean)
{
  if ($string_boolean == "1" ) {
    return "Up";
  }
  else {
    return "Down";
  }
}

function show_yes_no($string_boolean)
{
  if ($string_boolean == "1" ) {
    return "Yes";
  }
  else {
    return "No";
  }
}

function show_no_yes($string_boolean)
{
  if ($string_boolean == "1" ) {
    return "No";
  }
  else {
    return "Yes";
  }
}

function get_wdm_device_from_interface($interface)
{
  if ($interface == "wwan0") {
    $device = "cdc-wdm0";
  } else if ($interface == "wwan1") {
    $device = "cdc-wdm1";
  }
  return $device;
}

function get_phone_number($interface)
{
  $device = get_signal_wwan_device($interface);
  $out_str = exec("timeout 5 at-cmd ".$device." AT+CNUM | sed -n '2p' | cut -d ',' -f2| sed 's/\"//g'");
  return $out_str;
}

function get_connected_network($interface)
{
  $device = get_signal_wwan_device($interface);
  $out = exec("timeout 5 at-cmd ".$device." AT+COPS?|sed -n '2p'|cut -d '\"' -f2");
  if (strpos($out, 'COPS') !== false) {
    $out="Searching..";
  }
  return $out;
}

function get_signal_strength($interface)
{
  $device = get_signal_wwan_device($interface);
  $out_str = exec("timeout 5 at-cmd ".$device." AT+CSQ|sed -n '2p'|cut -d ':' -f 2| cut -d ',' -f 1|sed 's/ //g'");
  $out_int = number_format($out_str);
  $dBm = 2 * $out_int - 113;
  return $dBm;
}

function get_imei($interface)
{
  $device = get_signal_wwan_device($interface);
  $imei = exec("timeout 5 at-cmd ".$device." AT+GSN| head -2  |tail -1");
  return $imei;
}

function get_sim_iccid($interface)
{
  $device = get_signal_wwan_device($interface);
  $iccid = exec("timeout 5 at-cmd ".$device."  AT+QCCID| grep -e '+QCCID:' | cut -d ' '  -f2");
  return $iccid;
}

function get_gps_location($interface)
{
  $device = get_signal_wwan_device($interface);
  exec("timeout 5 at-cmd ".$device." AT+QGPS=1");
  sleep(20);
  $tries=0;
  while ($tries < 3) {
    $gps_data = exec("timeout 5 at-cmd ".$device."  AT+QGPSLOC=2| grep '+QGPSLOC: ' | cut -d ' ' -f2-");
    if ($gps_data) {
      break;
    } else {
      $tries++;
    }
  }
  return $gps_data;
}

function get_network_info($interface)
{
  $device = get_signal_wwan_device($interface);
  $network_info = exec("timeout 5 at-cmd ".$device." AT+QNWINFO | grep -e '+QNWINFO:' | cut -d ' '  -f2-");
  return explode(',', $network_info);
}

function get_registration_status($interface)
{
  $ret = 0;
  $out = "";
  $device = get_wdm_device_from_interface($interface);
  exec("timeout 5 uqmi -d /dev/".$device." --get-serving-system", $out, $ret);
  $out_str = implode("", $out);
  return json_decode(utf8_decode($out_str), true)['registration'];
}

function get_data_status($interface)
{
  $device = get_wdm_device_from_interface($interface);
  $out = exec("timeout 5 uqmi -d /dev/".$device." --get-data-status");
  return trim($out, '"');
}

function get_shasum($file_path)
{
  $out = exec("sha256sum ".$file_path);
  return array_shift(explode(' ', $out));
}

function get_network_devices()
{
  $out = "";
  exec("uci show network | grep -E '=port' | grep 'network.port' | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function get_lan_interfaces()
{
  $out = "";
  exec("uci show network | grep -E '=interface' | grep -v loopback | grep lan | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function get_wan_interfaces()
{
  $out = "";
  exec("uci show network | grep -E '=interface' | grep -v loopback | grep -v wwan | grep wan | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function get_dhcp_server_interfaces()
{
  $out = "";
  exec("uci show dhcp | grep -E '=dhcp' | grep -v '_p' | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}


function get_configured_monitor_interfaces()
{
  $out = "";
  exec("uci show datamon | grep -E '=interface' | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function get_configured_link_interfaces()
{
  $out = "";
  exec("uci show network | grep -E '=interface' | grep -v loopback | grep -v tun | awk -F'[\.=]'  '{print $2}' | sort", $out);
  return $out;
}

function get_configured_bgp4()
{
  $out = "";
  exec("uci show bird4 | grep -E '=bgp' | awk -F'[\.=]'  '{print $2}' | sort", $out);
  return $out;
}

function get_configured_bgp6()
{
  $out = "";
  exec("uci show bird6 | grep -E '=bgp' | awk -F'[\.=]'  '{print $2}' | sort", $out);
  return $out;
}

function get_ospf_interfaces($v46)
{
  $out = "";
  if ($v46 == "0") {
    exec("uci show bird4 | grep -E '=ospf_interface' | awk -F'[\.=]' '{print $2}' | sort", $out);
  } else {
    exec("uci show bird6 | grep -E '=ospf_interface' | awk -F'[\.=]' '{print $2}' | sort", $out);
  }
  return $out;
}

function get_ospf_areas($v46)
{
  $out = "";
  if ($v46 == "0") {
    exec("uci show bird4 | grep -E '=ospf_area' | awk -F'[\.=]' '{print $2}' | sort", $out);
  } else {
    exec("uci show bird6 | grep -E '=ospf_area' | awk -F'[\.=]' '{print $2}' | sort", $out);
  }
  return $out;
}

function get_ospf_networks($v46)
{
  $out = "";
  if ($v46 == "0") {
    exec("uci show bird4 | grep -E '=ospf_networks' | awk -F'[\.=]' '{print $2}' | sort", $out);
  } else {
    exec("uci show bird6 | grep -E '=ospf_networks' | awk -F'[\.=]' '{print $2}' | sort", $out);
  }
  return $out;
}

function get_configured_internet_interfaces()
{
  $out = "";
  exec("uci show network | grep -E '=interface' | grep -v loopback | grep -v tun | grep -v lan | grep -v _static | awk -F'[\.=]'  '{print $2}' | sort", $out);
  return $out;
}

function get_configured_interfaces()
{
  $out = "";
  exec("uci show network | grep -E '=interface' | grep -v loopback | grep -v _static | awk -F'[\.=]'  '{print $2}' | sort", $out);
  return $out;
}

function get_configured_ipsec()
{
  $out = "";
  exec("uci show ipsec | grep -E '=tunnel' | awk -F'=' '{print $1}' | awk -F'.' '{print $2}'", $out);
  return $out;
}

function get_configured_ipsec_sas()
{
  $out = "";
  exec("grep conn /var/ipsec/ipsec.conf | cut -d' ' -f2 | sort -u", $out);
  return $out;
}

function get_ipsec_status($tunnel_name)
{
    $out = exec("ipsec status ".$tunnel_name."_remote-".$tunnel_name." | grep ".$tunnel_name." | head -1 | awk '{print $2}'");
    return $out;
}

function get_ipsec_duration($tunnel_name)
{
    $out = exec("ipsec status ".$tunnel_name."_remote-".$tunnel_name." | grep ".$tunnel_name." | head -1 | awk '{print $3\" \"$4}'");
    return $out;
}

function get_tunnel_ip($tunnel_name)
{
  $out = exec("ifconfig | grep -A 1 ".$tunnel_name." | grep inet | awk '{print $2}' | awk -F':' '{print $2}'", $out);
  return $out;
}

function get_tunnel_gw_ip($tunnel_name)
{
  $out = exec("ifconfig | grep -A 1 ".$tunnel_name." | grep inet | awk '{print $3}' | awk -F':' '{print $2}'", $out);
  return $out;
}

function get_fuse_interfaces()
{
  $out = "";
  exec("uci show fuse | grep -E \"=interface\" | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function get_balanced_interfaces()
{
  $out = "";
  exec("uci show mwan3 | grep -E \"=interface\" | awk -F'[\.=]'  '{print $2}'", $out);
  return $out;
}

function get_all_routing_tables()
{
  $routing_tables = array();
  $lines = file("/etc/iproute2/rt_tables", FILE_IGNORE_NEW_LINES);
  foreach($lines as $line) {
    if (substr( $line, 0, 1 ) !== "#") {
      array_push($routing_tables, $line);
    }
  }
  return $routing_tables;
}

function get_time_from_seconds($seconds)
{
  try {
    $dt1 = new DateTime("@0");
    $dt2 = new DateTime("@$seconds");
    return $dt1->diff($dt2)->format('%ad %hh and %im');
  }
  catch(Exception $e) {
    return "";
  }
}

function get_configured_zones()
{
  $out = "";
  exec("uci show firewall | grep zone | grep \"name=\" | awk -F\"['=]\"  '{print $3}'", $out);
  array_push($out, "");
  return $out;
}

function get_ddns_options()
{
  $out = "";
  exec("cat /etc/ddns/services | awk -F'\"' '{print $2}'  | sort", $out);
  return $out;
}

function show_zone_networks($zone_networks)
{
  return explode(" ", $zone_networks);
}

function show_action($action_string)
{
  if ($action_string == "ACCEPT") {
    return '<span class="label label-primary">ACCEPT</span>';
  } else if ($action_string == "REJECT") {
    return '<span class="label label-danger">REJECT</span>';
  } else if ($action_string == "DROP") {
    return '<span class="label label-danger">DROP</span>';
  }

}

function ipv6_2long(string $ipaddress) {
  $pton = @inet_pton($ipaddress);
  if (!$pton) { return false; }
  $number = '';
  foreach (unpack('C*', $pton) as $byte) {
    $number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
  }
  return base_convert(ltrim($number, '0'), 2, 10);
}

?>
