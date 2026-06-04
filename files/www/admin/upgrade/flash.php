<?php include '/www/lib/sessioncheck.php' ?>
<?php

$filename = '/tmp/pupgrade.bin';

if (file_exists($filename)) {
  $fname = "pupgrade.bin";
  $flash = "sysupgrade -v /tmp/".$fname;
  exec($flash);
} else {
  $fname = "upgrade.bin";
  $flash = "sysupgrade -n -v /tmp/".$fname;
  exec($flash);
}

?>
