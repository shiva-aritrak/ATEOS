function checkIP(ipaddrs) {
  if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddrs)) {
    return true;
  }
  return false;
}


function checkSubMask(a) {

  var nums = /^(((128|192|224|240|248|252|254)\.0\.0\.0)|(255\.(0|128|192|224|240|248|252|254)\.0\.0)|(255\.255\.(0|128|192|224|240|248|252|254)\.0)|(255\.255\.255\.(0|128|192|224|240|248|252|254)))$/;
  if (a.match(nums)) {
    return true;
  } else {
    return false;
  }
}

function validate() {
  document.getElementById("verrors").innerHTML = "";

  local_ip_address = document.getElementById("local_ip_addr").value;
  remote_ip_address = document.getElementById("remote_ip_addr").value;
  ttl = document.getElementById("ttl").value;
  static_ip_address = document.getElementById("static_ip_addr").value;
  static_netmask = document.getElementById("static_subnet_mask").value;
  tunnel_target = document.getElementById("tunnel_target").value;
  tunnel_mask = document.getElementById("tunnel_subnet_mask").value;
  tunnel_gateway = document.getElementById("tunnel_gateway").value;


  localipname = document.getElementById("local_ip_addr").name;
  remoteipname = document.getElementById("remote_ip_addr").name;
  ttlname = document.getElementById("ttl").name;
  staticipname = document.getElementById("static_ip_addr").name;
  staticsubname = document.getElementById("static_subnet_mask").name;
  tuntargetname = document.getElementById("tunnel_target").name;
  tunsubname = document.getElementById("tunnel_subnet_mask").name;
  tungatename = document.getElementById("tunnel_gateway").name;


  LOCAL_IP_ADDRESS = localipname.replace(/_/g, " ");
  REMOTE_IP_ADDRESS = remoteipname.replace(/_/g, " ");
  STATIC_IP_ADDRESS = staticipname.replace(/_/g, " ");
  STATIC_SUBNET_MASK = staticsubname.replace(/_/g, " ");
  TUNNEL_TARGET_ADDRESS = tuntargetname.replace(/_/g, " ");
  TUNNEL_SUBNET_MASK = tunsubname.replace(/_/g, " ");
  TUNNEL_GATEWAY = tungatename.replace(/_/g, " ");


  var field_names1 = [LOCAL_IP_ADDRESS, REMOTE_IP_ADDRESS, STATIC_IP_ADDRESS, TUNNEL_TARGET_ADDRESS, TUNNEL_GATEWAY];
  var values_array1 = [local_ip_address, remote_ip_address, static_ip_address, tunnel_target, tunnel_gateway];

  var field_names2 = [STATIC_SUBNET_MASK, TUNNEL_SUBNET_MASK];
  var values_array2 = [static_netmask, tunnel_mask];

  var i, j, result, result1;
  var incorrect_fields = [];

  if (ttl < 0 ) {
    incorrect_fields.push(ttlname);
  } else if (ttl > 255) {
    incorrect_fields.push(ttlname);    
  }

  for (i = 0; i < 5; i++) {

    result = checkIP(values_array1[i]);
    if (!result) {
      incorrect_fields.push(field_names1[i]);
    }
  }

  for (j = 0; j < 2; j++) {
    result1 = checkSubMask(values_array2[j]);
    if (!result1) {
      incorrect_fields.push(field_names2[j]);
    }

  }

  document.getElementById("alertwindow").style.display = "";

  document.getElementById("verrors").innerHTML = "";

  if (incorrect_fields.length == 0) {
    // No Errors. Lets clear the alert window and remove the content
    document.getElementById("gre_vpn_form").submit();
    document.getElementById("alertwindow").style.display = "none";
    document.getElementById("verrors").innerHTML = "";
    return;
  } else if (incorrect_fields.length == 1) {
    // 1 Error. Show message
    document.getElementById("verrors").innerHTML += incorrect_fields[0] + " is incorrect.";
  } else if (incorrect_fields.length > 1) {
    // Many Errors, build sentence
    var pen_error_field = incorrect_fields.slice(0, incorrect_fields.length - 1);
    document.getElementById("verrors").innerHTML += pen_error_field.join(', ');
    document.getElementById("verrors").innerHTML += " and " + incorrect_fields[incorrect_fields.length - 1] + " are incorrect.";
  }
}
