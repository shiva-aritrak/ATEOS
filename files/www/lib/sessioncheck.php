<?php
$SESSION_WHITELIST = array('/status/bandwidth.php', '/admin/troubleshoot.php', '/admin/services.php', '/status/command.php');
session_start();
if (!isset($_SESSION['username'])) {
  header('Location: /login.php');
  exit;
}
else if ($_SESSION['is_admin'] == "no") {
  if (count($_GET) > 0 || count($_POST) > 0 || count($_FILES) > 0) {
    if (!in_array($_SERVER['SCRIPT_NAME'], $SESSION_WHITELIST)) {
      exec("logger -t debug 'access to page not allowed, logging out'" );
      session_start();
      unset($_GET);
      unset($_POST);
      setcookie(session_name(), "", time() - 3600);
      session_destroy();
      session_write_close();
      header('Location: /login.php?error=3');
      exit;
    } 
  }
}
?>