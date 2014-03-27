<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
$pagetitle = "Paper Administration";
$quicklinks = true;
ncu_forcesecure();
ncu_forceauth();

require_once("../include/header.php");

?>
<style type="text/css">
.demo
{
   border:3px solid #b84702;
   border-top:20px solid #b84702;
   margin:1em;
   padding:0em;
	border-spacing:8px;
   font-size:17px;
   text-align:left;
}


.combinations
{
   border:3px solid #b84702;
   border-top:20px solid #b84702;
   margin:1em;
   padding:2px;
   border-spacing:40px 1px;
   font-size:17px;
   text-align:left;
		
}

</style>


<?

$uname = ncu_getusername();
$sage = ncu_sage_unet_menu();
$query = "SELECT username from unet_mc_admin WHERE username = '$uname'";
$res =& $sage->query($query);
if ( $res->numRows() != 1 )
{
	die("<font color=red>Access Denied</font>");
}

if ( isset($_POST["addpaper"]) )
{
	$size = $_POST["size"];
	$type = $_POST["type"];
	$color = $_POST["color"];
	$sizevalue = $sage->query("SELECT papersize from unet_mc_papersize WHERE papersize_index = '$size'")->fetchRow();
	$typevalue = $sage->query("SELECT papertype from unet_mc_papertype WHERE papertype_index = '$type'")->fetchRow();
	$colorvalue = $sage->query("SELECT papercolor from unet_mc_papercolor WHERE papercolor_index = '$color'")->fetchRow();	
	$query = "INSERT INTO unet_mc_paper (paper_size, paper_type, paper_color) VALUES ('$size', '$type', '$color')";
	$res = $sage->query($query);

	if (PEAR::isError($res))
		echo "<p><font color=\"red\">There was an error adding the paper selection $sizevalue[0] - $typevalue[0] - $colorvalue[0]</font></p>";
	else
		echo "<p>Successfully added the paper selection $sizevalue[0] - $typevalue[0] - $colorvalue[0]</p>";
}

if ( isset($_POST["addcolor"]) )
{
	$color = $_POST["addcolor"];
	$query = "INSERT INTO unet_mc_papercolor (papercolor) VALUES ('$color')";
	$res = $sage->query($query);
	
	if (PEAR::isError($res))
		echo "<p><font color=\"red\">There was an error adding the paper color $color</font></p>";
	else
		echo "<p>Successfully added paper color $color</p>";
}

if ( isset($_POST["addtype"]) )
{
	$type = $_POST["addtype"];
	$query = "INSERT INTO unet_mc_papertype (papertype) VALUES ('$type')";
	$res = $sage->query($query);
	
	if (PEAR::isError($res))
		echo "<p><font color=\"red\">There was an error adding the paper type $type</font></p>";
	else
		echo "<p>Successfully added paper type $type</p>";
}

if ( isset($_POST["addsize"]) )
{
	$size = $_POST["addsize"];
	$query = "INSERT INTO unet_mc_papersize (papersize) VALUES ('$size')";
	$res = $sage->query($query);
	
	if (PEAR::isError($res))
		echo "<p><font color=\"red\">There was an error adding the paper size $size</font></p>";
	
	else
		echo "<p>Successfully added paper size $size</p>";
}

if ( isset($_GET["delpaper"]) )
{
	$paper = $_GET["delpaper"];
	$description = "SELECT papersize, papertype, papercolor FROM unet_mc_paper
   JOIN unet_mc_papersize
   ON unet_mc_paper.paper_size = unet_mc_papersize.papersize_index
   JOIN unet_mc_papertype
   ON unet_mc_paper.paper_type = unet_mc_papertype.papertype_index
   JOIN unet_mc_papercolor
   ON unet_mc_paper.paper_color = unet_mc_papercolor.papercolor_index
   WHERE paper_index = '$paper'";

	$res = $sage->query($description);
	$row = $res->fetchRow();
	$size = $row[0];
	$type = $row[1];
	$color = $row[2];

	$query = "DELETE FROM unet_mc_paper WHERE paper_index = '$paper'";
	$res = $sage->query($query);

	if (PEAR::isError($res))
		echo "<p>There was an error deleting the paper $size - $type - $color</p>";
	else
		echo "<p>Deleted the paper selection $size - $type - $color</p>";
}

if ( isset($_POST["delcolor"]) )
{
}

if ( isset($_POST["deltype"]) )
{
}

if ( isset($_POST["delsize"]) )
{
}

$querySize = "SELECT * from unet_mc_papersize";
$resSize = $sage->query($querySize);
$queryType = "SELECT * FROM unet_mc_papertype";
$resType = $sage->query($queryType);
$queryColor = "SELECT * FROM unet_mc_papercolor";
$resColor = $sage->query($queryColor);

$queryCombinations = "SELECT paper_index, papersize, papertype, papercolor FROM unet_mc_paper
   JOIN unet_mc_papersize
   ON unet_mc_paper.paper_size = unet_mc_papersize.papersize_index
   JOIN unet_mc_papertype
   ON unet_mc_paper.paper_type = unet_mc_papertype.papertype_index
   JOIN unet_mc_papercolor
   ON unet_mc_paper.paper_color = unet_mc_papercolor.papercolor_index
	ORDER BY papersize DESC";
$resCombinations = $sage->query($queryCombinations);

?>
<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> Paper Administration</p>


<strong>New Paper Properties</strong><br>
<table class="demo">
   <tr>
      <form method="POST" action="paper.php">
      <td align="left">Size</td>
      <td align="left"><input type="text" name="addsize"></td>
      <td align="left"><input type="image" name="submit" src="plus-icon.png"></td>
      </form>
   </tr>
   <tr>
      <form method="POST" action="paper.php">
      <td align="left">Type</td>
      <td align="left"><input type="text" name="addtype"></td>
      <td align="left"><input type="image" name="submit" src="plus-icon.png"></td>
      </form>
   </tr>
   <tr>
      <form method="POST" action="paper.php">
      <td align="left">Color</td>
      <td align="left"><input type="text" name="addcolor"></td>
      <td align="left"><input type="image" name="submit" src="plus-icon.png"></td>
      </form>
   </tr>
</table>



<strong>New Paper Combination</strong><br>
<form method="POST" action="paper.php">
	<input type="hidden" name="addpaper" value="1">
<table class="demo">
	<tr>
		<td align="left">Size</td>
		<td align="left">Type</td>
		<td align="left">Color</td>
		<td></td>
	</tr>
	<tr>
		<td align="left"><select name="size">
			<option value="">Select Size</option>
			<? 
				while($size = $resSize->fetchRow(DB_FETCHMODE_ASSOC))
				{
					echo "<option value=\"".$size["papersize_index"]."\">".$size["papersize"]."</option>";
				}
			?>
		</td>
		<td align="left"><select name="type">
			<option vlaue="">Select Type</option>
			<?
				while($type = $resType->fetchRow(DB_FETCHMODE_ASSOC))
				{
					echo "<option value=\"".$type["papertype_index"]."\">".$type["papertype"]."</option>";
				}
			?>
		</td>
		<td align="left"><select name="color">
			<option vlaue="">Select Color</option>
			<?
				while($color = $resColor->fetchRow(DB_FETCHMODE_ASSOC))
				{
					echo "<option value=\"".$color["papercolor_index"]."\">".$color["papercolor"]."</option>";
				}
			?>
		</td>
		<td align="left"><input type="image" name="submit" src="plus-icon.png"></td>
	</tr>
</table>
</form>


<strong>Current Paper Selections</strong>
<table class="combinations">
	<tr>
		<th align="left">Paper Size</th>
		<th align="left">Paper Type</th>
		<th align="left">Paper Color</th>
		<th></th>
	</tr>
	<tr height="4" bgcolor="#b84702"><td height="4" colspan="4"></td></tr>

	<?
		while($combination = $resCombinations->fetchRow(DB_FETCHMODE_ASSOC) )
		{
	?>
			<tr>
				<td align="left"><? echo $combination["papersize"]; ?></td>
				<td align="left"><? echo $combination["papertype"]; ?></td>
				<td align="left"><? echo $combination["papercolor"]; ?></td>
				<td align="left"><a href="paper.php?delpaper=<? echo $combination["paper_index"]; ?>"><img src="delete-icon.png" border="0"></a></td>
			</tr>
			<tr height="2" bgcolor="#b84702"><td height="2" colspan="4"></td></tr>
	<?
		}
	?>
</table>



<?

 
	



?>

