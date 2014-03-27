<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
</HEAD>
<?php


session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";

ncu_forcesecure();
ncu_forceauth();
$uname = ncu_getusername();
$aJob = new Job($uname);

if (!$aJob->isAdmin) {
    include_once("../include/header.php");
    echo "You are not authorized to view this page";
    require_once("../include/footer.php");
    return;
}

$cJob = new Job($uname);
?>
 <div id="jobbox">
            <h4 style="margin-top:0px;margin-bottom:5px;">Add Department</h4>
            <form method="post" action="admin.php?type=department&function=departmentadd">
                <label style="width:150px;">Department Name</label>
                <input type="text" name="department"/>
                <input type="image" name="nothing" src="image/plus-icon.png" style="height:20px; width:20px;"/>
            </form>
        </div>
        <div id="jobbox">
            <h4 style="margin-top:0px;margin-bottom:5px;">Remove Department</h4>
            <? for ($i=0; $i<count($cJob->departmentList); $i++) {
                echo "<label style='width:250px;'>".$cJob->departmentList[$i];
                echo '<img src="image/money-icon.png">';
                echo '<img src="image/walk-icon.png"></label>';
                echo '<a href="admin.php?type=department&function=departmentdel&name='.$cJob->departmentList[$i].'"><img src="image/cancel-icon.png"/></a><br/><br/>';
            }
            ?>
        </div>
