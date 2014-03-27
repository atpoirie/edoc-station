<?php
/********************
*Copy Center Admins *
*********************/
session_start();
require_once "/var/authscripts/ncu_auth.inc";
$pagetitle = "Online Copy Center";
$quicklinks = true;
ncu_forcesecure();
ncu_forceauth();

require_once("../include/header.php");

$uname = ncu_getusername();
if ( ncu_isstudent($uname) )
{
   echo "This system is for NCU employees only";
   require_once("../include/footer.php");
   return;
}

$sage = ncu_sage_unet_menu();
$query = "SELECT username from unet_mc_admin WHERE username = '$uname'";
$res =& $sage->query($query);
if ( $res->numRows() != 1 )
{
	die("<font color=red>Access Denied</font>");
}

if ( isset($_POST["add"]) )
{
	$query = "INSERT INTO unet_mc_admin (username) VALUES ('".$_POST["add"]."')";
	$sage->query($query);
}

if ( isset($_GET["delete"]) )
{
	$query = "DELETE FROM unet_mc_admin WHERE user_index = '". 
		$_GET["delete"] . "'";
	$sage->query($query);
}	

?>

<style type="text/css">
.demo
{
	border:4px solid #b84702;
	border-top:20px solid #b84702;
	margin:1em;
	padding:1em 2em;
	font-size:20px;
	text-align:left;
}
</style>

<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a>

<form method="POST" action="user.php">
<p class="demo">
	Add User
	<input type="text" name="add">
<input type="image" name="submit" src="plus-icon.png">
</p>
</form>

<p class="demo">
<?
	$query = "SELECT * FROM unet_mc_admin";
	$res =& $sage->query($query);
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		echo "<a href=\"user.php?delete=" .
			$row["user_index"] . 
			"\"><img src=\"delete-icon.png\" style=\"border:0px; vertical-align:text-top;\"></a>" .
			$row["username"] .
			"<br><br>";
	}
?>
</p>
