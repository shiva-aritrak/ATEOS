<?php include '/www/lib/sessioncheck.php' ?>

<head>
  <title>Routing | BGP</title>
</head>

<?php include '/www/sidenav.php' ?>
<!-- Load any Libraries if required/written -->
<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/routing.php' ?>

<?php

$filled_bgp_route_no = "99";

if(count($_POST) > 0) {
  $bgp_route_no = $_POST["bgp_route_no"];
  $status = $_POST["status"];
  $local_as_num = $_POST["local_as_num"];
  $local_router_identifier = $_POST["local_router_identifier"];
  $remote_nets = $_POST["remote_nets"];
  $remote_ids = $_POST["remote_ids"];
  $remote_as_nums = $_POST["remote_as_nums"];
  configure_bgp_route($bgp_route_no, $status, $local_as_num, $local_router_identifier, $remote_nets, $remote_ids, $remote_as_nums);
}

if(count($_GET) > 0) {
  $action = $_GET["action"];
  $filled_bgp_route_no = $_GET["bgp_route_no"];

  if ( $action == "delete" ) {
    delete_bgp_config($filled_bgp_route_no, $route_id - 1);
    echo '<script>window.location.href = "bgp.php";</script>';
    exit;
  }
}

$route_id = 0;
$out = "";
$ret = 99;
$html_out = "";

exec("uci show bgp.@router[".$route_id."]" , $out, $ret);
while ( $ret == 0 ) {
  $html_out = $html_out.'<tr>';
  $html_out = $html_out.'  <td>'.($route_id+1).'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get bgp.@router[".$route_id."].local_as_num").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get bgp.@router[".$route_id."].local_router_identifier").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get bgp.@router[".$route_id."].remote_router_identifier").'</td>';
  $html_out = $html_out.'  <td>'.exec("uci get bgp.@router[".$route_id."].remote_as_num").'</td>';
  $html_out = $html_out.'  <td>'.str_replace(" ", ", ", exec("uci get bgp.@router[".$route_id."].remote_nets")).'</td>';
  $html_out = $html_out.'  <td><a href="bgp.php?action=edit&bgp_route_no='.$route_id.'"><i class="fa fa-edit"></i></a> &nbsp;&nbsp; <a href="bgp.php?action=delete&bgp_route_no='.$route_id.'"><i class="fa fa-times"></i></a></td>';
  $html_out = $html_out.'</tr>';

  ++$route_id;
  exec("uci show bgp.@router[".$route_id."]" , $out, $ret);
}

?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <h3 class="page-title"></h3>
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">BGP</h3>
              </div>
              <div class="panel-body">
                <form enctype="multipart/form-data" id="bgp_route_form" action="bgp.php" method="post">
                  <input type="hidden" id="bgp_route_no_id" name="bgp_route_no" value="<?php if (count($_GET) > 0) { echo $filled_bgp_route_no; } else { echo "-1"; } ?>">
                  <table>
                    <tr>
                      <td>Status</td>
                      <td>
                        <label class="fancy-radio">
                          <?php
                          $route_status = exec("uci get bgp.@router[".$filled_bgp_route_no."].status");
                          if ($route_status == "1") {
                            echo '<input name="status" value="1" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="1" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Enabled</span>
                        </label>
                        <label class="fancy-radio">
                          <?php
                          if ($route_status == "0") {
                            echo '<input name="status" value="0" checked="checked" type="radio" required>';
                          } else {
                            echo '<input name="status" value="0" type="radio" required>';
                          }
                          ?>
                          <span><i></i>Disabled</span>
                        </label>
                      </td>
                    </tr>
                    <tr>
                      <td>Local AS Number</td>
                      <td><input required min=100 max=65534 type="number" id="local_as_num_id" name="local_as_num" class="form-control" value="<?php  echo exec("uci get bgp.@router[".$filled_bgp_route_no."].local_as_num");?>"></td>
                      </td>
                    </tr>
                    <tr>
                      <td>Local Router ID</td>
                      <td><input required type="text" pattern="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" title="Must contain a valid IP Address" id="local_router_identifier_id" name="local_router_identifier" class="form-control" value="<?php  echo exec("uci get bgp.@router[".$filled_bgp_route_no."].local_router_identifier");?>"></td>
                    </tr>
                    <tr>
                        <td>Remote Router ID/AS Number</td>
                        <td class="router_id_list">
                            <?php
                                $get = exec("uci get bgp.@router[".$filled_bgp_route_no."].remote_id_as_nums");
                                foreach (explode("' '", $get) as $remote_id_as_num) {
                                    $remote_id_as_num_sept = explode(' ', trim($remote_id_as_num, "'") );
                                    echo '<input required type="text" name="remote_ids[]" class="form-control" value="'.$remote_id_as_num_sept[0].'">';
                                }
                            ?>
                        </td>
                        <td class="router_as_list">
                            <?php
                                $get = exec("uci get bgp.@router[".$filled_bgp_route_no."].remote_id_as_nums");
                                foreach (explode("' '", $get) as $remote_id_as_num) {
                                    $remote_id_as_num_sept = explode(' ', trim($remote_id_as_num, "'") );
                                    echo '<input required type="text" name="remote_as_nums[]" class="form-control" value="'.$remote_id_as_num_sept[1].'">';
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><button class="add_bgp_id_as_nums" type="button"><i class="fa fa-plus"></i> Add ID and AS</button></td><td><button class="delete_bgp_id_as_nums" type="button"><i class="fa fa-times"></i> Remove ID and AS</button></td>
                    </tr>
                    <tr>
                        <td>Networks</td>
                        <td class="networks_list">
                            <?php
                                $remote_nets = exec("uci get bgp.@router[".$filled_bgp_route_no."].remote_nets");
                                foreach (explode(" ", $remote_nets) as $remote_net) {
                                    echo '<div><input type="text" name="remote_nets[]" class="form-control" value="'.$remote_net.'"><a href="#" class="delete"><i class="fa fa-times"></i></a></div>';
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td><button class="add_bgp_network" type="button"><i class="fa fa-plus"></i> Add Network</button></td>
                    </tr>
                  </table>
                  <br>
                  <br>
                  <div class="row">
                    <div class="col-md-6">
                      <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                    <div class="col-md-6">
                      <button type="button" class="btn btn-danger">Reset</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>



        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Current BGP Configuration</h3>
              </div>
              <div class="panel-body">
                <div id="bgp_route_form_div_id" class="content">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Local AS</th>
                        <th>Local Identifier</th>
                        <th>Remote Identifier</th>
                        <th>Remote AS</th>
                        <th>Networks</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        echo $html_out;
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
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
$(document).ready(function() {
  var bgp_nw_wrapper = $(".networks_list");
  var bgp_nw_add_button = $(".add_bgp_network");

  $(bgp_nw_add_button).click(function(e) {
    e.preventDefault();
    $(bgp_nw_wrapper).append('<br><div><input required type="text" name="remote_nets[]" class="form-control"><a href="#" class="delete"><i class="fa fa-times"></i></a></div>');
  });

  $(bgp_nw_wrapper).on("click", ".delete", function(e) {
    e.preventDefault();
    $(this).parent('div').remove();
  })

  var bgp_id_wrapper = $(".router_id_list");
  var bgp_as_wrapper = $(".router_as_list");
  var bgp_idas_add_button = $(".add_bgp_id_as_nums");
  var bgp_idas_del_button = $(".delete_bgp_id_as_nums");

  $(bgp_idas_add_button).click(function(e) {
    e.preventDefault();
    $(bgp_id_wrapper).append('<input required type="text" name="remote_ids[]" class="form-control">');
    $(bgp_as_wrapper).append('<input required type="text" name="remote_as_nums[]" class="form-control">');
  });

  $(bgp_idas_del_button).click(function(e) {
    e.preventDefault();
    $(bgp_id_wrapper).children().last().remove();
    $(bgp_as_wrapper).children().last().remove();
  });

});
</script>
<?php endblock() ?>
