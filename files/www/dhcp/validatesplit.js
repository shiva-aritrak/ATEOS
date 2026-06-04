var eval_ip ="null";
var eval_dom="null";
var id ="null";
var verror = "null";


function checkIP(ipaddrs) {
  if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddrs)) {
    return true;
  }
  return false;
}


function checkDOMNAME(a){
var nums= /^((?!-))(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1}(\.(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1})*\.(xn--)?([a-z]{1,61}|[a-z]\.[a-z])$/;
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
    eval_dom = "dom" + "_name";
    verror = "verrors";

  }  validate(eval_ip,eval_dom,id,verror);
}


function validate()
{
   document.getElementById(verror).innerHTML="";

  ip_address = document.getElementById(eval_ip).value;
  dom_name = document.getElementById(eval_dom).value;

var result = checkIP(ip_address);
var result1= checkDOMNAME(dom_name);

  ipname=document.getElementById(eval_ip).name;
  domname=document.getElementById(eval_dom).name;

  IP_ADDRESS= ipname.replace("_"," ");
  DOM_NAME = domname.replace("_"," ");

  var field_names=[IP_ADDRESS,DOM_NAME];
  var values_array=[ip_address,dom_name];
  var incorrect_fields = [];

     if(!result)
    {
    incorrect_fields.push(IP_ADDRESS);

    }

if(!result1)
    {
      incorrect_fields.push(DOM_NAME);
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
 
