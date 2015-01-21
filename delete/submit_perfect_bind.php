<?php

session_start();

require_once("/var/authscripts/ncu_auth.inc");
$pagetitle = "Online Copy Center";
$quicklinks = true;

//ncu_forcesecure();
//ncu_forceauth();

require_once("../include/header.php");

$uname = ncu_getusername();
if ( ncu_isstudent($uname) )
{
   echo "This system is for NCU employees only";
   require_once("../include/footer.php");
   return;
}


$uploads_dir = '/var/www/unet/copycenter/job_files';

$tmp_name = $_FILES["upload"]["tmp_name"];
$fileName = $_FILES["upload"]["name"];
$newName = md5(rand() * time());
move_uploaded_file($tmp_name, "$uploads_dir/$newName");

$sage = ncu_sage_unet_menu();
?>
<h3>Verify Information</h3>

<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> <a href="/unet/copycenter/copy.php">Copy</a> >> Copy Results</p>

<table width=70% align="center">
<tr>
   <td align="left"><strong>Job Name</strong></td>
   <td align="left"><? echo $_POST["jobname"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>File</strong></td>
   <td align="left"><? echo $fileName; ?></td>
</tr>
<tr>
   <td align="left"><strong>Date Due</strong></td>
   <td align="left"><? echo $_POST["date1x"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Phone</strong></td>
   <td align="left"><? echo $_POST["phone"]; ?></td>
</tr>

