<?php
session_start();

if ( !isset($_POST["jobname"]))
{
   echo "<script language='javascript' type='text/javascript'>";
   echo "location.replace('/unet/copycenter')";
   echo "</script>";
	die();
}


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

<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> <a href="/unet/copycenter/copy.php">Spiral Bind</a> >> Spiral Bind Results</p>

<table width=70% align="center">
<tr>
   <td align="left"><strong>Job Name</strong></td>
   <td align="left"><? echo $_POST["jobname"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Date Due</strong></td>
   <td align="left"><? echo $_POST["date1x"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Phone</strong></td>
   <td align="left"><? echo $_POST["phone"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Department</strong></td>
   <td align="left"><? echo $_POST["department"]; ?></td>
</tr>
<tr>
	<td align="left"><strong>Account</strong></td>
	<td align="left"><? echo $_POST["account"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>File</strong></td>
   <td align="left"><? echo $fileName; ?></td>
</tr>
<tr>
   <td align="left"><strong>Quantity</strong></td>
   <td align="left"><? echo $_POST["quantity"]; ?></td>
</tr>
<tr>
	<td align="left"><strong>Double Sided</strong></td>
	<td align="left"><?
		if ($_POST["duplex"] == "on" )
			$duplex = "Yes";
		else
			$duplex = "No";
		echo $duplex;
		?>
	</td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>




<tr>
	<td align="left"><strong>Spiral Color</strong></td>
	<td align="left"><? echo $_POST["spiral"]; ?></td>
</tr>
<tr>
	<td align="left"><strong>Spiral Side</strong></td>
	<td align="left"><? echo $_POST["side"]; ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>



<tr>
   <td align="left"><strong>Pick up/Deliver</strong></td>
   <td align="left"><? echo $_POST["transport"]; ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>



<tr>
	<td align="left"><strong>Document in color</strong></td>
	<td align="left">
		<? if ($_POST["docink"] == "on")
				$docink = "Yes";
			else
				$docink = "No";
			echo $docink;
		?>
	</td>
</tr>
<tr>
	<td align="left"><strong>Front cover color</strong></td>
	<td align="left"><? 
		if ( $_POST["colorcover"] == "on" )
		{
			$colorcover = "Yes";
		}
		else
		{	
			$colorcover = "No";
		}
		echo $colorcover;	
	 	?>
	</td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<tr>
	<td align="left"><strong>Cardstock front cover</strong></td>
	<td align="left">
		<?
			if ( $_POST["cardcover"] == "on" )
			{
				$cardcover = "Yes";
				echo $_POST["frontcard"];
			}
			else
			{
				echo "No";
				$cardcover = "No";
			}
		?>
	</td>
</tr>
<tr>
	<td align="left"><strong>Plastic Cover</strong</td>
	<td align="left">
		<?
			if ( $_POST["plasticcover"] == "on" )
			{
				$plasticcover = "Yes";
			}
			else
			{
				$plasticcover = "No";
			}
			echo $plasticcover;
		?>
	</td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>



<tr>
	<td align="left"><strong>Black back cover</strong></td>
	<td align="left">
		<? 
			if ( $_POST["blackback"] == "on" )
			{
				$blackback = "Yes";
			}
			else
			{
				$blackback = "No";
			}
			echo $blackback;
		?>
	</td>
</tr>
<tr>
	<td align="left"><strong>Cardstock back cover</strong></td>
	<td align="left">
		<?
			if ( $_POST["back"] == "on" )
			{
				$back = "Yes";
				echo $_POST["backcard"];
			}
			else
			{	
				$back = "No";
				echo "No";
			}
		?>
	</td>
</tr>
<?
	$comments = "";
	if ($_POST["comments"] != "Additional comments")
	{
?>
<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<tr>
	<td align="left"><strong>Comments</strong></td>
	<td align="left"><? $comments = stripslashes($_POST["comments"]); echo $comments; ?> </td>
</tr>

<?
	}
?>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
	<td>
		<form><input type="button" value="Cancel" onclick="window.location.href='/unet/copycenter/index.php'"></form>
	</td>
	<td>
		<form method="post" action="accept_spiral.php">
			<?
				foreach ( $_POST as $key => $value)
				{
			?>
				<input type="hidden" name="<? echo $key; ?>" value="<? echo $value; ?>">
			<?
				}
			?>
			<input type="hidden" name="jobtype" value="spiralbind">
			<input type="hidden" name="comments2" value="<? echo $comments; ?>">
			<input type="hidden" name="randName" value="<? echo $newName; ?>">
			<input type="hidden" name="fileName" value="<? echo $fileName; ?>">
			<input type="hidden" name="ink2" value="<? echo $docink; ?>">		
			<input type="hidden" name="duplex2" value="<? echo $duplex; ?>">
			<input type="hidden" name="colorcover2" value="<? echo $colorcover; ?>">
			<input type="hidden" name="cardcover2" value="<? echo $cardcover; ?>">
			<input type="hidden" name="plasticcover2" value="<? echo $plasticcover; ?>">
			<input type="hidden" name="blackback2" value="<? echo $blackback; ?>">
			<input type="hidden" name="back2" value="<? echo $back; ?>">
				
			<input type="submit" value="Accept">
		</form>
	</td>
</tr>
</table>
<?
include_once("../include/footer.php");

?>
</tr>
