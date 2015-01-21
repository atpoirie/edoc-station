<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
</HEAD>
<?php


session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";

//ncu_forcesecure();
//ncu_forceauth();
$uname = ncu_getusername();
$aJob = new Job($uname);

if (ncu_isstudent($uname) || !$aJob->isAdmin) {
    include_once("include/header.php");
    echo "You are not authorized to view this page";
    require_once("../include/footer.php");
    return;
}

$userarray = $aJob->get_admins();

?>

<div id="jobbox">
    <form method="post" action="admin.php?type=user&function=add">
        <h4 style="margin-top:0px;margin-bottom:5px;">Add Administrator</h4>
        <label>Username:</label><input type="text" name="name"/>
        <input type="image" name="nothing" src="image/plus-icon.png" style="height:20px; width:20px;"/>
    </form>
</div>
<div id="jobbox">
    <h4 style="margin-top:0px;margin-bottom:5px;">Remove Administrator</h4>
    <? foreach($userarray as $user) { ?>
    <label style="padding-top:15px;margin-top:15px;"></label><?echo $user?> <a href="admin.php?type=user&function=delete&name=<? echo $user?>"><img src="image/cancel-icon.png" style="height:20px; width:20px;"></a><br/>
        
    <?}?>
</div>
