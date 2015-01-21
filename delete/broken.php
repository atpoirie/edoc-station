<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/copyclass.php";
require_once "./include/pressclass.php";

$pagetitle = "Online Copy Center";
$quicklinks = true;
//ncu_forcesecure();
//ncu_forceauth();



$uname = ncu_getusername();
include_once("../include/header.php");
if (ncu_isstudent($uname)) {
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}
?>

<div id="inner_container">
    <br>
    <br>
    <h4 style="color:red">Whoops, it looks like you are using Internet Explorer</h4>
    Unfortunately Internet Explorer is unable to handle the complexities of this site, you will need to use a more powerful browser such as Firefox or Chrome but NOT Safari
    <?
    include_once("../include/footer.php");
?>