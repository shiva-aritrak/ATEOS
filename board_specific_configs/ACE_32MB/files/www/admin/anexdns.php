<?php include '/www/lib/sessioncheck.php' ?>
<head>
  <title>Administration | AnexDNS</title>
</head>
<?php include '/www/sidenav.php' ?>
<?php include '/www/lib/system.php' ?>

<?php

if(count($_POST) > 0) {
   
  $status = $_POST["status"];
  $anexdns_domain = $_POST["anexdns_domain"];
  set_anexdns_config($status, $anexdns_domain);
}
?>

<?php startblock('contentbar') ?>
<div class="main">
  <div class="main-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <div class="col-md-12">
            <div class="panel">
              <div class="panel-body">
                <div class="panel-heading">
                  <h3 class="panel-title">AnexDNS</h3>
                </div>
                <div class="panel-body">
                  <form autocomplete="off" enctype="multipart/form-data" id="dns_form" action="anexdns.php" method="post">
                    <table>
                      <tr>
                        <td>Status</td>
                        <td>
                          <label class="fancy-radio">
                            <?php
                            $anexdns = exec("uci get anexgate.dns.status");
                            if ($anexdns == "1") {
                              echo '<input name="status" value="1" checked="checked" type="radio" required>';
                            } else {
                              echo '<input name="status" value="1" type="radio" required>';
                            }
                            ?>
                            <span><i></i>Enabled</span>
                          </label>
                          <label class="fancy-radio">
                            <?php
                            if ($anexdns == "0") {
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
                        <td>AnexDNS Server Address</td>
                        <td><input required type="text" id="anexdns_domain_id" name="anexdns_domain" class="form-control" value="<?php  echo exec("uci get anexgate.dns.domain");?>"></td>
                      </tr>
                    </table>
                    <br/>
                    <div class="row">
                      <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Save</button>
                      </div>
                      <div class="col-md-6">
                        <button type="button" class="btn btn-danger">Clear</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading">
              <h3 class="panel-title">AnexDNS Configuration</h3>
            </div>
            <div class="panel-body no-padding">
              <table class="table">
                <tbody>
                  <tr>
                    <td>Configuration Sync</td>
                    <td>
                      <?php
                      $anexdns = exec("uci get anexgate.dns.success");
                      if ($anexdns == "1") {
			echo '<span class="label label-success">Success</span>';
                      } else {
			echo '<span class="label label-danger">Sync Error</span>';
                      }
                      ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Connect Domain</td>
                    <td>
                      <samp>
                        <?php
                        echo exec("uci get anexgate.dns.domain");
                        ?>
                      </samp>
                    </td>
                  </tr>
                  <tr>
                    <td>Primary DNS Server</td>
                    <td>
                      <samp>
                        <?php
                        echo exec("uci get dhcp.@dnsmasq[0].server | cut -d ' ' -f 1");
                        ?>
                      </samp>
                    </td>
                  </tr>
                  <tr>
                    <td>Secondary DNS Server</td>
                    <td>
                      <samp>
                        <?php
                        echo exec("uci get dhcp.@dnsmasq[0].server | cut -d ' ' -f 2");
                        ?>
                      </samp>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endblock() ?>
