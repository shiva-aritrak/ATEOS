<?php include '/www/lib/sessioncheck.php' ?>

<?php
if(count($_GET) > 0) {
  $to_run_cmd = "";
  $action = $_GET["action"];
  if ($action == "ping") {
    $to_run_cmd = "/bin/ping -c 5 8.8.8.8";
  } else if ($action == "dom_ping") {
    $to_run_cmd = "/bin/ping -c 5 google.com";
  } else if ($action == "dnslookup") {
    $to_run_cmd = "/usr/bin/nslookup google.com";
  } else if ($action == "routing") {
    $to_run_cmd = "/usr/sbin/ip route";
  } else if ($action == "traceroute") {
    $to_run_cmd = "/bin/traceroute -q 1 -n 8.8.8.8";
  }
  $ret = 1;
  exec($to_run_cmd, $output, $ret);
  $out = implode("</br>", $output);
  $payload = json_encode( array( "status"=> $ret, "data"=> $out ) );
  echo $payload;
  exit;
}
?>

<head>
  <title>Administration | System Management</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/system.php' ?>


<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">Troubleshoot Summary </h3>
              <div class="right">
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Test</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Routing Table Summary</td>
                    <td id="routing_sum_status"><span class="label label-success"><i class='fa fa-spinner fa-spin'></i> Running</span></td>
                  </tr>
                  <tr>
                    <td>Ping by Domain</td>
                    <td id="dom_ping_sum_status"><span class="label label-success"><i class='fa fa-spinner fa-spin'></i> Running</span></td>
                  </tr>
                  <tr>
                    <td>Ping by IP</td>
                    <td id="ip_ping_sum_status"><span class="label label-success"><i class='fa fa-spinner fa-spin'></i> Running</span></td>
                  </tr>
                  <tr>
                    <td>Domain Lookup</td>
                    <td id="lookup_sum_status"><span class="label label-success"><i class='fa fa-spinner fa-spin'></i> Running</span></td>
                  </tr>
                  <tr>
                    <td>Traceroute</td>
                    <td id="trace_sum_status"><span class="label label-success"><i class='fa fa-spinner fa-spin'></i> Running</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">Routing Table</h3>
              <div class="right">
                <button id="routing_table_button" type="button" class="btn"></button>
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body">
              <p id="routing_table_result">
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">Domain Ping </h3>
              <div class="right">
                <button id="dom_ping_button" type="button" class="btn"></button>
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body">
              <p id="dom_ping_result">
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">IP Ping</h3>
              <div class="right">
                <button id="ip_ping_button" type="button" class="btn"></button>
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body">
              <p id="ping_result">
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">Traceroute</h3>
              <div class="right">
                <button id="trace_button" type="button" class="btn"></button>
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body">
              <p id="trace_result">
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">Domain Lookup</h3>
              <div class="right">
                <button id="lookup_button" type="button" class="btn"></button>
                <button type="button" class="btn-toggle-collapse"><i class="fa fa-chevron-up"></i></button>
                <button type="button" class="btn-remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="panel-body">
              <p id="lookup_result">
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>

<?php startblock('scriptblock') ?>
<script>

var dom_ping = false;
var ip_ping = false;
var lookup = false;
var traceroute = false;

$(document).ready(function() {

  $("#dom_ping_button").html("<i class='fa fa-spinner fa-spin'></i> Running...");
  $("#ip_ping_button").html("<i class='fa fa-spinner fa-spin'></i> Running...");
  $("#trace_button").html("<i class='fa fa-spinner fa-spin'></i> Running...");
  $("#lookup_button").html("<i class='fa fa-spinner fa-spin'></i> Running...");

  $.ajax({
    url: "troubleshoot.php",
    type: "get",
    data: {
      action: "ping",
    },
    dataType: 'json',
    success: function(response) {
      $("#ping_result").html( response.data );
      ip_ping = response.status;
      $("#ip_ping_button").html("<i class='fa fa-check-circle'></i> Success");
      $("#ip_ping_sum_status").html('<span class="label label-success">Success</span>');
    },
    error: function(xhr) {
      alert("Error" + xhr.resonseText);
      $("#ip_ping_sum_status").html('<span class="label label-danger">Failed</span>');
    }
  });

  $.ajax({
    url: "troubleshoot.php",
    type: "get",
    data: {
      action: "routing",
    },
    dataType: 'json',
    success: function(response) {
      $("#routing_table_result").html( response.data );
      // r = response.status;
      $("#routing_table_button").html("<i class='fa fa-check-circle'></i> Success");
      $("#routing_sum_status").html('<span class="label label-success">Success</span>');
    },
    error: function(xhr) {
      alert("Error" + xhr.resonseText);
      $("#routing_sum_status").html('<span class="label label-danger">Failed</span>');
    }
  });

  $.ajax({
    url: "troubleshoot.php",
    type: "get",
    data: {
      action: "dom_ping",
    },
    dataType: 'json',
    success: function(response) {
      $("#dom_ping_result").html( response.data );
      $("#dom_ping_button").html("<i class='fa fa-check-circle'></i> Success");
      dom_ping = response.status;
      $("#dom_ping_sum_status").html('<span class="label label-success">Success</span>');
    },
    error: function(xhr) {
      alert("Error" + xhr.resonseText);
      $("#dom_ping_sum_status").html('<span class="label label-danger">Failed</span>');
    }
  });

  $.ajax({
    url: "troubleshoot.php",
    type: "get",
    data: {
      action: "dnslookup",
    },
    dataType: 'json',
    success: function(response) {
      $("#lookup_result").html( response.data );
      lookup = response.status;
      $("#lookup_button").html("<i class='fa fa-check-circle'></i> Success");
      $("#lookup_sum_status").html('<span class="label label-success">Success</span>');
    },
    error: function(xhr) {
      alert("Error" + xhr.resonseText);
      $("#lookup_sum_status").html('<span class="label label-danger">Failed</span>');
    }
  });


  $.ajax({
    url: "troubleshoot.php",
    type: "get",
    data: {
      action: "traceroute",
    },
    dataType: 'json',
    success: function(response) {
      $("#trace_result").html( response.data );
      traceroute = response.status;
      $("#trace_button").html("<i class='fa fa-check-circle'></i> Success");
      $("#trace_sum_status").html('<span class="label label-success">Success</span>');
    },
    error: function(xhr) {
      alert("Error" + xhr.resonseText);
      $("#trace_sum_status").html('<span class="label label-danger">Failed</span>');
    }
  });

});
</script>
<?php endblock('scriptblock') ?>
