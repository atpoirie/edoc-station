<?php
session_start();

if ( !isset($_POST["jobname"]))
{
	echo "<script language='javascript' type='text/javascript'>";
	echo "location.replace('/unet/copycenter')";
	echo "</script>";
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
	<td align="left"><strong>Number of Pages</td>
	<td align="left"><? echo $_POST["pages"]; ?></td>
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
	<td align="left"><? echo $_POST['department']; ?></td>
</tr>
<tr>
	<td align="left"><strong>Account</strong></td>
	<td align="left"><? echo $_POST['account']; ?></td>
</tr>
<tr>
	<td align="left"><strong>Quantity</strong></td>
	<td align="left"><? echo $_POST["quantity"]; ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<tr>
   <td align="center"><strong>Paper Size</strong></td>
   <td align="center"><strong>Paper Type</strong></td>
   <td align="center"><strong>Paper Color</strong></td>
</tr>
<?

//******************************************************
//The following if else statements are meant to do
//checking to make sure that a paper type and color 
//were selected since the fancy auto change lists based
//on selection on the main copy page fail to force a 
//selection
//This attempts to default the selection to the first 
//available paper type and color based on the 
//unet_mc_paper table.
//******************************************************

$res =& $sage->query("SELECT papersize from unet_mc_papersize WHERE papersize_index = '".$_POST['papersize']."'");
$row = $res->fetchRow();
$papersize = $row[0];

if (isset($_POST['papertype']) && $_POST['papertype'] != 'Select Paper Type')
{
	$res =& $sage->query("SELECT papertype from unet_mc_papertype WHERE papertype_index = '".$_POST['papertype']."'");
	$row = $res->fetchRow();
	$papertype = $row[0];
}

else
{
	$res =& $sage->query("SELECT TOP 1 paper_type from unet_mc_paper WHERE paper_size = '".$_POST['papersize']."' ORDER BY paper_index");
	if (PEAR::isError($res))
		die($res->getMessage());
	$row = $res->fetchRow();
	$papertype_index = $row[0];
	$_POST['papertype'] = $row[0];
	$res =& $sage->query("SELECT papertype from unet_mc_papertype WHERE papertype_index = '$papertype_index'");
	if (PEAR::isError($res))
		die($res->getMessage());
	$row = $res->fetchRow();
	$papertype = $row[0];
}

if (isset($_POST['papercolor']) && $_POST['papercolor'] != '' && $_POST['papercolor'] != 'Select Paper Color')
{
	$res =& $sage->query("SELECT papercolor from unet_mc_papercolor WHERE papercolor_index = '".$_POST["papercolor"]."'");
	$row = $res->fetchRow();
	$papercolor = $row[0];
}

else
{
	$res =& $sage->query("SELECT TOP 1 paper_color 
								FROM unet_mc_paper 
								WHERE 
									paper_size='".$_POST['papersize']."' 
								AND 
									paper_type='".$_POST['papertype']."' 
								ORDER BY paper_index");
	if (PEAR::isError($res))
		die ($res->getMessage());

	$row = $res->fetchRow();
	$papercolor_index = $row[0];
	$res =& $sage->query("SELECT papercolor 
								FROM unet_mc_papercolor
								WHERE
									papercolor_index = '$papercolor_index'");
	$row = $res->fetchRow();
	$papercolor = $row[0];
}


?>

<tr>
   <td align="center"><? echo $papersize ?></td>
   <td align="center"><? echo $papertype ?></td>
   <td align="center"><? echo $papercolor ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<tr>
	<td align="left"><strong>Double Sided</strong></td>
	<td align="left">
	<? if ($_POST["duplex"]== "on" )
			$duplex = "Yes";
		else
			$duplex = "No";
		echo $duplex;
	 ?> 
	</td>
</tr>

<tr>
   <td align="left"><strong>Print in</strong></td>
   <td align="left"> <? echo $_POST["ink"]; ?>
   </td>
</tr>

<tr>
	<td align="left"><strong>Folding</strong></td>
	<td align="left">
	<? $folding = none;
		if (isset($_POST["folding"]))
			$folding = $_POST["folding"];

		echo $folding; 
	?></td>
</tr>


<tr>
   <td align="left"><strong>Staple</strong></td>
   <td align="left"><?
      $staple = "No Staple";
      if ( isset($_POST["staple"]))
         $staple = $_POST["staple"];
      echo $staple;
   ?></td>


<? $cut = "No Cutting";
   if (isset($_POST["cut"]))
      $cut = $_POST["cut"];
?>

<tr>
   <td align="left"><strong>Cutting</strong></td>
   <td align="left"><? echo $cut; ?>
   </td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr><tr height="6"></tr>
<tr height="6"></tr>

<tr>
   <td align="left"><strong>Laminate</strong></td>
   <td align="left">
	<? if($_POST["laminate"]=="on")
			$laminate = "Yes";
		else
			$laminate = "No";
		echo $laminate;
	 ?> 
	</td>
</tr>
<tr>
   <td align="left"><strong>UnCollate</strong></td>
   <td align="left">
	<? if ($_POST["collate"]=="on")
			$collate = "Yes";
		else
			$collate = "No"; 
		echo $collate;
	?> 
	</td>
</tr>
<tr>
   <td align="left"><strong>Hole Punch</strong></td>
   <td align="left">
	<? if ($_POST["punch"]=="on")
			$punch = "Yes";
		else
			$punch = "No"; 
		echo $punch;
	?> 
	</td>
</tr>
<tr>
   <td align="left"><strong>Pick up/Deliver</strong></td>
   <td align="left"><? echo $_POST["transport"]; ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<? 
	$comments = "";
	if ($_POST["comments"] != "Additional comments")
	{
		
?>
<tr>
	<td align="left"><strong>Comments</strong></td>
	<td align="left"><? $comments = stripslashes($_POST["comments"]); echo $comments; ?> </td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>
<?
	}
?>

<tr>
	<td><form><input type="button" value="Cancel" onclick="window.location.href='/unet/copycenter/copy.php'"></form>
	</td>
	<td>
		<form method="post" action="accept_copy.php">
			<?
				foreach( $_POST as $key => $value)
				{
			?>
					<input type="hidden" name="<? echo $key; ?>" value="<? echo strip_tags($value); ?>">
			<?
				}
			?>		
			<input type="hidden" name="jobtype" value="copy">	
			<input type="hidden" name="papercolor2" value="<? echo $papercolor; ?>">
			<input type="hidden" name="papersize2" value="<? echo $papersize; ?> ">
			<input type="hidden" name="papertype2" value="<? echo $papertype; ?> ">
			<input type="hidden" name="fileName" value="<? echo $fileName; ?>">
			<input type="hidden" name="punch2" value="<? echo $punch; ?>">
			<input type="hidden" name="collate2" value="<? echo $collate; ?>">
			<input type="hidden" name="laminate2" value="<? echo $laminate; ?>">
			<input type="hidden" name="duplex2" value="<? echo $duplex; ?>">
			<input type="hidden" name="randName" value="<? echo $newName; ?>">
			<input type="hidden" name="comments2" value="<? echo strip_tags($comments); ?>">
			<input type="hidden" name="folding2" value="<? echo $folding; ?>">
			<input type="hidden" name="cut2" value="<? echo $cut; ?>">
			<input type="hidden" name="staple2" value="<? echo $staple; ?>">
			<input type="submit" value="Accept">
		
		</form>
	</td>
</tr>
</table>
<?
include_once("../include/footer.php");

?>
