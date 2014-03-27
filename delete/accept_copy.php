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
<p>Thank you for submitting your copy request using the Online Copy Center.</p>
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
$phone = ms_escape_string($_POST["phone"]);
$department = $_POST["department"];
$account = ms_escape_string($_POST["account"]);
$quantity = ms_escape_string($_POST["quantity"]);
$folding = $_POST["folding2"];
$ink = $_POST["ink"];
$staple = $_POST["staple2"];
$cut = $_POST["cut2"];
$punch = $_POST["punch2"];
$papersize = $_POST["papersize2"];
$papertype = $_POST["papertype2"];
$papercolor = $_POST["papercolor2"];
$transport = $_POST["transport"];
$comments = ms_escape_string(stripslashes($_POST["comments2"]));
$fileName = ms_escape_string($_POST["fileName"]);
$randName = $_POST["randName"];
$collate = $_POST["collate2"];
$duplex = $_POST["duplex2"];
$laminate = $_POST["laminate2"];
$pages = $_POST["pages"];

//$query = "SELECT jobid FROM unet_mc_copy where filename='$fileName' AND randname='$randName'";
//$res =& $sage->query($query);

//if ($res->numRows())
//{
//	$row = $res->fetchRow();
//	$jobnumber = $row[0];
//}

//else
//{

	$res =& $sage->query("SELECT MAX(jobid) as job FROM unet_mc_jobid");
	if ( $res->numRows() != 1 )
	   $jobnumber = 1;
	else
	{
	   $row = $res->fetchRow();
	   $jobnumber = $row[0] + 1;
	}

   $query = "INSERT INTO unet_mc_jobid (jobtype, duedate) VALUES ('copy', '$duedate')";
   syslog(1, $query);
   $res =& $sage->query($query);
   if (PEAR::isError($res))
      die($res->getMessage());

	$query = "INSERT INTO unet_mc_copy (jobid, username, jobname, submitdate, duedate, phone, 
			department, quantity, folding, transport, ink, staple, cut, papersize, papertype, 
			papercolor, comments, filename, randname, collation, duplex, laminate, 
			punch, account, pages) VALUES 
		('$jobnumber', '$username', '$jobname', '$submitdate', 
		'$duedate', '$phone', '$department', '$quantity', '$folding',
		'$transport', '$ink', '$staple', '$cut', '$papersize', '$papertype',
		'$papercolor', '$comments', '$fileName', '$randName', '$collate', 
		'$duplex', '$laminate', '$punch', '$account', '$pages')";

	syslog(1, $query);

	$res =& $sage->query($query);

//}
//echo $query;




$msg = "<html>
			<body>
			<H2>Online Copy Center - Copy Confirmation</H2>
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
					<td><strong>Email:</strong></td>
					<td><a href=\"mailto:".$username."@northcentral.edu\">$username@northcentral.edu</td>
			
<tr>
   <td align=\"left\"><strong>File</strong></td>
   <td align=\"left\">$fileName</td>
</tr>
<tr>
	<td align=\"left\"><strong>Number of Pages</strong></td>
	<td align=\"left\">$pages</td>
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
	<td aling=\"left\">$account</td>
</tr>
<tr>
   <td align=\"left\"><strong>Quantity</strong></td>
   <td align=\"left\">$quantity</td>
</tr>

<tr height=\"6\"></tr>
<tr height=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"3\"></td></tr><tr height=\"6\"></tr>
<tr height=\"6\"></tr>

<tr>
   <td align=\"center\"><strong>Paper Size</strong></td>
   <td align=\"center\"><strong>Paper Type</strong></td>
   <td align=\"center\"><strong>Paper Color</strong></td>
</tr>
<tr>
   <td align=\"center\">$papersize</td>
   <td align=\"center\">$papertype</td>
   <td align=\"center\">$papercolor</td>
</tr>


<tr height=\"6\"></tr>
<tr height=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"3\"></td></tr><tr height=\"6\"></tr>
<tr height=\"6\"></tr>

<tr>
   <td align=\"left\"><strong>Double Sided</strong></td>
   <td align=\"left\">$duplex</td>
</tr>
<tr>
   <td align=\"left\"><strong>Print in</strong></td>
   <td align=\"left\">$ink</td>
</tr>

<tr>
   <td align=\"left\"><strong>Folding</strong></td>
   <td align=\"left\">$folding</td>
</tr>
<tr>
   <td align=\"left\"><strong>Staple</strong></td>
   <td align=\"left\">$staple</td>
</tr>
<tr>
   <td align=\"left\"><strong>Cutting</strong></td>
   <td align=\"left\">$cut</td>
</tr>


<tr height=\"6\"></tr>
<tr height=\"2\" bgcolor=\"#b84702\"><td height=\"2\" colspan=\"3\"></td></tr>
<tr height=\"6\"></tr>


<tr>
   <td align=\"left\"><strong>Laminate</strong></td>
   <td align=\"left\">$laminate</td>
</tr>
<tr>
   <td align=\"left\"><strong>UnCollate</strong></td>
   <td align=\"left\">$collate</td>
</tr>
<tr>
   <td align=\"left\"><strong>Hole Punch</strong></td>
   <td align=\"left\">$punch</td>
</tr>

<tr>
   <td align=\"left\"><strong>Pick up/Deliver</strong></td>
   <td align=\"left\">$transport</td>
</tr>


";

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
