<?php include '/www/lib/sessioncheck.php' ?>
<head><title>Firewall | Domain Filtering</title></head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/dhcplib.php' ?>

<?php
if(count($_POST) > 0) {
  $mode = $_POST["mode"];
  $domain_names = $_POST["domain_names"];
  set_dns_filter_ipset($mode, $domain_names);
}

$domain_names = explode(" ", exec("uci get dhcp.filter.domain"));
$out = "";
$ret = 99;
?>

<?php startblock('contentbar') ?>
<div class="main">
  <!-- MAIN CONTENT -->
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-body">
              <div class="panel-heading">
                <h3 class="panel-title">Filter</h3>
              </div>
              <div class="panel-body">
                <div id="content1" class="content">
                  <form id="filter_form" action="filter.php" method="post">
                    <div>
                      <table class="filter_table">
                        <tr>
                          <td>Mode</td>
                          <td>
                            <label class="fancy-radio">
                              <?php
			      $ret = exec("uci get dhcp.filter.mode");
                              if ($ret == "whitelist") {
                                echo '<input name="mode" value="whitelist" checked="checked" type="radio" required>';
                              } else {
                                echo '<input name="mode" value="whitelist" type="radio" required>';
                              }
                              ?>
                              <span><i></i>Whitelist</span>
                            </label>
                            <label class="fancy-radio">
                              <?php
                              if ($ret != "whitelist") {
                                echo '<input name="mode" value="blacklist" checked="checked" type="radio" required>';
                              } else {
                                echo '<input name="mode" value="blacklist" type="radio" required>';
                              }
                              ?>
                              <span><i></i>Blacklist</span>
                            </label>
                          </td>
                        </tr>
                        <?php
                        foreach ($domain_names as $domain_name) {
                            echo '<tr>';
                            echo '  <td></td>';
                            echo '  <td><input pattern="^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$" type="text" name="domain_names[]" class="form-control" value="'.$domain_name.'"></td>';
                            echo '</tr>';
                        }
                        ?>
                      </table>
                      <br>
                      <button class="add_domain_button" type="button"><i class="fa fa-plus"></i> Add Domain</button>
                      <br>
                      <br>
                      <p>Domain Filter is applied to all users in the network, DNS for all endpoints should be set to the IP address of this device</p>
                      <br>
                      <br>
                      <div class="row">
                        <div class="col-md-6">
                          <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                        <div class="col-md-6">
                          <button type="reset" class="btn btn-danger">Clear</button>
                        </div>
                      </div>
                    </div>
                  </form>
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
  var wrapper = $(".filter_table");
  var add_button = $(".add_domain_button");

  var x = 1;
  $(add_button).click(function(e) {
    e.preventDefault();
    $(wrapper).append('<tr><td></td><td><input pattern="^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$" type="text" name="domain_names[]" class="form-control"><a href="#" class="delete"><i class="fa fa-cross"></i></a></td></tr>');
  });

  $(wrapper).on("click", ".delete", function(e) {
    e.preventDefault();
    $(this).parent('tr').remove();
  })
});
</script>
<?php endblock() ?>
