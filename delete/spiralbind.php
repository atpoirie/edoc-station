<?php


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

function get_earliest()
{
	$hour = date("g");
	
	$stamp = time();

	if ( $hour >= 16 )
		$stamp += ((23 - $hour) + 8) * 60 * 60;
	elseif ( $hour < 8 && $hour > 0 )
		$stamp += (8 - $hour) * 60 * 60;

	$stamp += 36 * 60 * 60;
	if ( date("N", $stamp ) > 5)
		$stamp += 24 * 60 * 60;
	if ( date("N", $stamp) > 5 )
		$stamp += 24 * 60 * 60;

	$earliest .= date("Y", $stamp) . ",";
	$earliest .= date("n", $stamp) - 1;
	$earliest .= date(",j,G", $stamp);
	$earliest .= ",0,0,0";
	return $earliest;
}

$sage = ncu_sage_unet_menu();


//If the user has used the copy center before
//look up their previous phone and department and 
//populate the form with that data ahead of time
$query = "SELECT TOP 1 phone, department FROM unet_mc_copy 
         WHERE username = '$uname' ORDER BY jobid";
$res =& $sage->query($query);
$row = $res->fetchRow();
$prePhone = $row[0];
$preDepartment = $row[1];


$res =& $sage->query(
	"SELECT papercolor FROM unet_mc_paper
	JOIN unet_mc_papersize
	ON unet_mc_paper.paper_size = unet_mc_papersize.papersize_index
	JOIN unet_mc_papertype
	ON unet_mc_paper.paper_type = unet_mc_papertype.papertype_index
	JOIN unet_mc_papercolor
	ON unet_mc_paper.paper_color = unet_mc_papercolor.papercolor_index
	WHERE papersize = '8 1/2 x 11'
	AND papertype = 'cardstock'");

$cardcolor = array();

while ( $row = $res->fetchRow(DB_FETCHMODE_ASSOC))
{
	$cardcolor[] = $row[papercolor]; 
}
?>

<style type="text/css">
td.field input.error, td.field select.error, tr.errorRow td.field input,tr.errorRow td.field select {
   border: 2px solid red;
   background-color: #FFFFD5;
   margin: 0px;
   color: red;
}

tr td.field div.formError {
   display: none;
   color: #FF0000;
}

tr.errorRow td.field div.formError {
   display: block;
   font-weight: normal;
}

div.error {
   color: red;
}

div.error a {
   color: #336699;
   font-size: 12px;
   text-decoration: underline
}

label.error {
   display: block;
   color: red;
   font-style: italic;
   font-weight: normal;
}

form table td {
   padding: 5px;
}

</style>


<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></SCRIPT>
<link rel="stylesheet" type="text/css" href="anytime.css" />
<SCRIPT type="text/javascript" src="anytime.js"></SCRIPT>

<script language="javascript">
function select_all()
{
	var comment = document.spiralbind.comments;
	comment.focus();
	comment.select();
}

$(document).ready(function(){
	$("#spiralbind").validate();
	$("#showhide").hide();
});

function toggle_cardstock_front()
{
	var cover = document.spiralbind.cardcover;
	var cardcover = document.spiralbind.frontcard;
   
	if ( cover.checked )
		cardcover.disabled = false;
	else
		cardcover.disabled = true;
}

function toggle_cardstock_back()
{
	var back = document.spiralbind.back;
	var cardback = document.spiralbind.backcard;

	if ( back.checked)
		cardback.disabled = false;
	else
		cardback.disabled = true;
}

function update_cardstock_color()
{
	var frontcolor = document.spiralbind.frontcard;
	var backcolor = document.spiralbind.backcard;
	var cardback = document.spiralbind.back;
	var frontcolorval = frontcolor.options[frontcolor.selectedIndex];
	var backcolorval = backcolor.options[backcolor.selectedIndex];
	
	if (cardback.checked)
	{
			backcolor.selectedIndex = frontcolor.selectedIndex;
	}
}

function update_cardback()
{
	var cardback = document.spiralbind.backcard;
	var black = document.spiralbind.blackback; 
	var back = document.spiralbind.back;

	if ( black.checked)
	{
		back.disabled = true;
		cardback.disabled = true;
	}
	else
	{
		back.disabled = false;
		cardback.disabled = false;
	}

}

function colorpaper()
{
	var coloredpaper = document.spiralbind.coloredpaper;
	if ( coloredpaper.checked )
	{
		$("#showhide").show();
		for (i=0; i<document.spiralbind.elements.length; i++)
		{
			document.spiralbind.elements[i].disabled = true;
		}
		coloredpaper.disabled = false;	
	}
	else
	{
		$("#showhide").hide();
		for (i=0; i<document.spiralbind.elements.length; i++)
		{
			document.spiralbind.elements[i].disabled = false;
		}
		update_cardback();
		toggle_cardstock_front();
		toggle_cardstock_back();
	}
}
</script>

<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> Spiral Bind</p>
<div>
<form name="spiralbind" id="spiralbind" method="post" action="submit_spiralbind.php" enctype="multipart/form-data">
<input type="hidden" name="submitdate" value="<?php echo date("Ymd G:i:00"); ?>" >
<table width=95% align="center">
<tr>
   <td align="left">Job Name</td>
   <td align="left" class="field" colspan="2"><input type="text" name="jobname" SIZE=25 id="jobname" class="input required" minlength="2"/></td>
</tr>

<tr>
   <td align="left">Date Due</td>
   <td colspan="2" align="left" class="field"><input type="text" NAME="date1x" id="date1x" size="20" class="input required"/>
  	<td></td>
	<script type="text/javascript">
		AnyTime.picker( "date1x",
		{ format: "%m/%d/%Z %h%p",
			firstDOW: 1,
			earliest: new Date(<? echo get_earliest(); ?>)
		});
	</script>
</tr>
<tr>
   <td align="left">Phone #</td>
   <td align="left" class="field"><input type="text" name="phone" SIZE=11 class="required"/></td>
   <td></td>
</tr>
<tr>
   <td align="left">Department</td>
   <td colspan="2" align="left" class="field"><select name="department" class="input required">
      <option value="">Select Department</option>
   <?php
      $res =& $sage->query("SELECT * from unet_mc_dept ORDER BY department");
		while ($row = $res->fetchRow())
		{
			if ($preDepartment == $row[1])
				echo "<option value=\"$row[1]\" selected=\"selected\">$row[1]</option>";
			else
				echo "<option value=\"$row[1]\">$row[1]</option>";
		}
	?>
         </select>
   </td>
</tr>
<tr>
	<td align="left">Account #</td>
	<td align="left" class="field"><input type="text" name="account" size=13 class="number"/><font style="font-size:12px;">(optional)</font></td>
	<td></td>
</tr>
<tr>
   <td align="left">Select Document</td>
   <td colspan="2" class="field"><input type="file" name="upload" id="upload" size=25 class="required"/></td>
	<td></td>
</tr>
<tr>
   <td align="left">Quantity</td>
   <td align="left" class="field"><input type="text" name="quantity" value="1" size=4 class="required number"/></td>
   <td></td>
</tr>
<tr>
	<td align="left">Double Sided</td>
	<td align="left"><input type="checkbox" name="duplex"></td>
	<td></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>
<tr>

<tr>
   <td align="left">Spiral Color</td>
   <td align="left"><select name="spiral" class="input required">
         <option value="">Choose color</option>
         <option value="Navy">Navy</option>
         <option value="White">White</option>
         <option value="Black">Black</option>
         <option value="Clear">Clear</option>
      </select>
   </td>
   <td></td>
</tr>

<tr>
	<td align="left">Spiral Side</td>
	<td align="left"><select name="side" class="input required">
		<option value="">Choose Side</option>
		<option value="Long">Long Side</option>
		<option value="Short">Short Side</option>
		</select>
	</td>
	<td></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>
<tr>
   <td align="left">Pick up</td>
   <td align="left"><input type="radio" name="transport" value="Pick-up" /></td>
   <td></td>
</tr>
<tr>
   <td align="left">Deliver</td>
   <td align="left"><input type="radio" name="transport" value="Deliver" checked/></td>
   <td></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
	<td align="left">Document in color</td>
	<td align="left"><input type="checkbox" name="docink"></td>
	<td></td>
</tr>
<tr>
   <td align="left">Front cover in color</td>
   <td align="left"><input type="checkbox" name="colorcover" value="Cover in color" id="cover"></td>
   <td></td>
</tr>
<tr>
	<td align="left">Printed on color paper</td>
	<td align="left"><input type="checkbox" name="coloredpaper" id="coloredpaper" onchange="colorpaper();"></td>
	<td></td>
</tr>
<div class="showhide">
<tr id="showhide">
	<td align="left" colspan="3"><h2><font color="red">This option is not available online, please bring a copy of your document to the Mail Center</font></h2></td>
</tr>
</div>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
	<td align="left">Cardstock front cover</td>
	<td align="left"><input type="checkbox" name="cardcover" id="cardcover" onchange="toggle_cardstock_front();"></td>
	<td align="left">
		<select name="frontcard" id="showhidefront" class="input required" disabled onchange="update_cardstock_color();">
			<option value="">Select Color</option>
			<?php
			for ( $i=0; $i<count($cardcolor); $i++ )
			{
				echo "<option value=\"$cardcolor[$i]\">$cardcolor[$i]</option>";
			}
			?>
			</select>
		</td>
</tr>
<tr>
	<td align="left">Plastic cover</td>
	<td align="left"><input type="checkbox" name="plasticcover" checked/></td>
	<td></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
   <td align="left">Black back cover</td>
   <td align="left"><input type="checkbox" name="blackback" id="blackback" value="Black back cover" onchange="update_cardback();"></td>
   <td></td>
</tr>

<tr>
   <td align="left">Cardstock back cover</td>
   <td align="left"><input type="checkbox" name="back" id="back" onchange="toggle_cardstock_back();"></td>
   <td align="left">
		<select name="backcard" id="showhideback" class="input required" disabled>
			<option value="">Select Color</option>
			<?php
			for ( $i=0; $i<count($cardcolor); $i++ )
         {
            echo "<option value=\"$cardcolor[$i]\">$cardcolor[$i]</option>";
         }
         ?>
		</select>
	</td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<tr>
	<td colspan="3"><textarea rows="5" cols="40" name="comments" onClick="select_all();">Additional comments</textarea></td>
</tr>
<tr>
	<td colspan="3" align="center"><input type="submit" value="Submit"></td>
</tr>
</form>
</table>
</div>
<DIV ID="testdiv1" STYLE="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></DIV>

<?php
require_once("../include/footer.php");

                                                                                                       
