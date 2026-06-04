



var eval_ip ="null";
var eval_mac="null";
var id ="null";
var verror = "null";


function checkIP(ipaddrs) {
  if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddrs)) {
    return true;
  }
  return false;
}


function checkMACaddr(a){
var nums= /^(([A-Fa-f0-9]{2}[:]){5}[A-Fa-f0-9]{2}[,]?)+$/;
if(a.match(nums))
{
 return true;
  }
else
{
  return false;
 }
}


function check(obj)
{
  id = obj.id;
  if(id == "ID") {
    eval_ip = "ip" + "_addr";
    eval_mac = "mac" + "_addr";
    verror = "verrors";

  }  validate(eval_ip,eval_mac,id,verror);
}


function validate()
{
   document.getElementById(verror).innerHTML="";

  ip_address = document.getElementById(eval_ip).value;
  mac_address = document.getElementById(eval_mac).value;

var result = checkIP(ip_address);
var result1= checkMACaddr(mac_address);

  ipname=document.getElementById(eval_ip).name;
  macname=document.getElementById(eval_mac).name;

  IP_ADDRESS= ipname.replace("_"," ");
  MAC_ADDRESS = macname.replace("_"," ");

  var field_names=[IP_ADDRESS,MAC_ADDRESS];
  var values_array=[ip_address,mac_address];
  var incorrect_fields = [];

     if(!result)
    {
    incorrect_fields.push(IP_ADDRESS);

    }

if(!result1)
    {
      incorrect_fields.push(MAC_ADDRESS);
    }


  document.getElementById("alertwindow").style.display = "";


  if(incorrect_fields.length == 0) {
    // No Errors. Lets clear the alert window and remove the content
    document.getElementById("myForm").submit();
    document.getElementById("alertwindow").style.display = "none";
    document.getElementById(verror).innerHTML = "";
    return;
  } else if(incorrect_fields.length == 1) {
    // 1 Error. Show message
    document.getElementById(verror).innerHTML += incorrect_fields[0] + " is incorrect";
  }
  else if (incorrect_fields.length > 1) {
    // Many Errors, build sentence
    var pen_error_field = incorrect_fields.slice(0, incorrect_fields.length-1);
     document.getElementById(verror).innerHTML += pen_error_field.join(', ');
    document.getElementById(verror).innerHTML += " and " + incorrect_fields[incorrect_fields.length-1] + " are incorrect";
  }
}
