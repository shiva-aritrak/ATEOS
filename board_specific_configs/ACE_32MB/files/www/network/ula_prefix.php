<?php include '/www/lib/sessioncheck.php' ?>

<?php include '/www/lib/common.php' ?>
<?php include '/www/lib/networks.php' ?>

<?php
if(count($_POST) > 0)
{
    $ula_prefix = $_POST["ula_prefix"];
    set_ula_prefix($ula_prefix);
    echo '<script>window.location.href = "wan6.php";</script>';
    exit;
}
?>