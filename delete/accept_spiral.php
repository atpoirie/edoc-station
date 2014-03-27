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
?>
<p>Thank you for submitting your spiral bind request using the Online Copy Center.</p>
<p>You will receive an email shortly with the details of this order for your record</p>
<a href="/unet/copycenter">Return to the Copy Center home</a>
<?
include_once("../include/footer.php");
$uploads_dir = '/var/www/unet/copycenter/job_files';
$sage = ncu_sage_unet_menu();

$submitdate = $_POST["submitdate"];
$jobname = ms_escape_string($_POST["jobname"]);
$jobtype = $_POST["jobtype"];
$username = ncu_getusername();
$displayname = ncu_getdisplayname($uname);
$duedate = $_POST["date1x"];
$phone = $_POST["phone"];
$department = $_POST["department"];
$account	= $_POST["account"];
$quantity = $_POST["quantity"];
$ink = $_POST["ink2"];
$transport = $_POST["transport"];
$comments = ms_escape_string(stripslashes($_POST["comments2"]));
$fileName = ms_escape_string($_POST["fileName"]);
$randName = $_POST["randName"];
$spiralcolor = $_POST["spiral"];
$spiralside = $_POST["side"];
$colorcover = $_POST["colorcover2"];
$cardstockcover = $_POST["cardcover2"];
$cardstockcovercolor = $_POST["frontcard"];
$plasticcover = $_POST["plasticcover2"];
$blackback = $_POST["blackback2"];
$cardstockback = $_POST["back2"];
$cardstockbackcolor = $_POST["backcard"];
$duplex = $_POST["duplex2"];

$query = "SELECT jobid FROM unet_mc_spiral where filename='$fileName' AND randname='$randName'";
$res =& $sage->query($query);

if ($res->numRows())
{
	$row = $res->fetchRow();
	$jobnumber = $row[0];
}
else
{
	$res =& $sage->query("SELECT MAX(jobid) as job FROM unet_mc_jobid");
	$row = $res->fetchRow();
	$jobnumber = $row[0] + 1;

	$query = "INSERT INTO unet_mc_spiral (jobid, username, jobname, submitdate, duedate, phone, 
			department, quantity, transport, ink, spiralcolor, spiralside, colorcover, 
			cardstockcover, cardstockcolor, plasticcover, blackback, cardstockback, 
			cardstockbackcolor, comments, filename, randname, duplex, account) VALUES 
		('$jobnumber', '$username', '$jobname', '$submitdate', '$duedate', '$phone', 
		'$department', '$quantity', '$transport', '$ink', '$spiralcolor',
		'$spiralside', '$colorcover', '$cardstockcover', '$cardstockcovercolor',
		'$plasticcover', '$blackback', '$cardstockback', '$cardstockbackcolor',
		'$comments', '$fileName', '$randName', '$duplex', '$account')";

	$res =& $sage->query($query);
	if (PEAR::isError($sage))
		die("Database Error - $sage->getMessage()");
	
	$query = "INSERT INTO unet_mc_jobid (jobtype, duedate) VALUES ('spiral', '$duedate')";
	$res =& $sage->query($query);
	if (PEAR::isError($sage))
		die("Database Error - $sage->getMessage()");
}

$msg = "<html>
			<body>
			<H2>Online Copy Center - Spiral Binding Confirmation</H2>
			<table width=\"650px\" style=\"border:solid 1px #b84702\">
				<tr width=\"100%\">
					<td colspan=\"3\"></td>
				</tr>
				<tr>
					<td><strong>Order ID:</strong></td>
					<td>$jobnumber</td>
					<td></td>
				</tr>
				<tr>
					<td><strong>Job Name:</strong></td>
					<td>$jobname</td>
					<td></td>
				</tr>
<tr>
	<td><strong>Customer:</strong></td>
	<td>$displayname</td>
</tr>	
<tr>
	<td><strong>Email:</strong></td>
	<td><a href=\"mailto:".$username."@northcentral.edu\">$username@northcentral.edu</td>
</tr>			
<tr>
   <td align=\"left\"><strong>File</strong></td>
   <td align=\"left\">$fileName</td>
</tr>
<tr>
   <td align=\"left\"><strong>Date Due</strong></td>
   <td align=\"left\">$duedate</td>
</tr>
<tr>
   <td align=\"left\"><strong>Phone</strong></td>
   <td align=\"left\">$phone</td>
</tr>
<tr>
   <td align=\"left\"><strong>Department</strong></td>

   <td align=\"left\">$department</td>
</tr>
<tr>
	<td align=\"left\"><strong>Account</strong></td>
	<td align=\"left\">$account</td>
</tr>
<tr>
   <td align=\"left\"><strong>Quantity</strong></td>
   <td align=\"left\">$quantity</td>
</tr>
<tr>
	<td align=\"left\"><strong>Double Sided</strong></td>
	<td align=\"left\">$duplex</td>
</tr>


<tr height=\"6\"></tr>
<tr heigh=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"2\"></td></tr>
<tr heigh=\"6\"></tr>


<tr>
   <td align=\"left\"><strong>Spiral Color</strong></td>
   <td align=\"left\">$spiralcolor</td>
</tr>
<tr>
	<td align=\"left\"><strong>Spiral Side</strong></td>
	<td align=\"left\">$spiralside</td>
</tr>

<tr height=\"6\"></tr>
<tr heigh=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"2\"></td></tr>
<tr heigh=\"6\"></tr>


<tr>
   <td align=\"left\"><strong>Pick up/Deliver</strong></td>
   <td align=\"left\">$transport</td>
</tr>


<tr height=\"6\"></tr>
<tr heigh=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"2\"></td></tr>
<tr heigh=\"6\"></tr>


<tr>
   <td align=\"left\"><strong>Document in color</strong></td>
   <td align=\"left\">$ink</td>
</tr>
<tr>
   <td align=\"left\"><strong>Front cover in color</strong></td>
   <td align=\"left\">$colorcover</td>
</tr>


<tr height=\"6\"></tr>
<tr heigh=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"2\"></td></tr>
<tr heigh=\"6\"></tr>


<tr>
	<td align=\"left\"><strong>Cardstock front cover</strong></td>
	<td align=\"left\">$cardstockcover</td>
</tr>
<tr>
	<td align=\"left\"><strong>Cardstock front cover color</strong></td>
	<td align=\"left\">$cardstockcovercolor</td>
</tr>
<tr>
	<td align=\"left\"><strong>Plastic Cover</strong></td>
	<td align=\"left\">$plasticcover</td>
</tr>


<tr height=\"6\"></tr>
<tr heigh=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"2\"></td></tr>
<tr heigh=\"6\"></tr>



<tr>
   <td align=\"left\"><strong>Black back Cover</strong></td>
   <td align=\"left\">$blackback</td>
</tr>
<tr>
	<td align=\"left\"><strong>Cardstock back cover</strong></td>
	<td align=\"left\">$cardstockback</td>
</tr>
<tr>
	<td align=\"left\"><strong>Cardstock back cover color</strong></td>
	<td align=\"left\">$cardstockbackcolor</td>
</tr>";



 if ( $comments != "" )
 {
		$msg .= "<tr height=\"6\"></tr>
			<tr height=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"3\"></td></tr>
			<tr height=\"6\"></tr>
			<tr>
				<td align=\"left\"><strong>Comments</strong></td>
				<td align=\"left\">$comments</td>
			</tr>";
}

$msg .= "</table></body></html>";

$toaddr = $username."@northcentral.edu";
$fromaddr = "mailcent@northcentral.edu";


//echo $msg;
mail($toaddr, "Order ID: $jobnumber", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: mailcent@northcentral.edu\n");

mail("mailcent@northcentral.edu", "Order ID: $jobnumber", $msg, "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: $toaddr\n");

