<?php

session_start();
$pagetitle = "Job ID: ".$_GET['job'];
$quicklinks = False;

//ncu_forcesecure();
//ncu_forceauth();

require_once("/var/authscripts/ncu_auth.inc");
ncu_forcesecure();
ncu_forceauth();
$uname = ncu_getusername();

if ( ncu_isstudent($uname))
{
	echo "This system is for NCU employees only";
	require_once("../include/footer.php");
	die();
}

$sage = ncu_sage_unet_menu();
$jobid = $_GET['job'];
$jobtype = lookup_jobtype($jobid);
$file_array = array();


if ( strtolower($jobtype) == "copy")
{
//	build_download($jobid, "unet_mc_copy");
	if ( isset($_GET['print']) && $_GET['print']=='Y' )
		copy_template($jobid, True);
	else
{
		require_once("../include/header.php");
		copy_template($jobid);
}
}
elseif ( strtolower($jobtype) == "spiral")
{
//	build_download($jobid, "unet_mc_spiral");
	
	if ( isset($_GET['print']) && $_GET['print'] == 'Y' )
		spiral_template($jobid, True);
	else
	{
		require_once("../include/header.php");
		spiral_template($jobid);
	}
}
elseif ( strtolower($jobtype) == "perfect")
{
//	build_download($jobid, "unet_mc_perfect");
        if ( isset($_GET['print']) && $_GET['print'] == 'Y')
            perfect_template($jobid, True);
        else {
            require_once("../include/header.php");
            perfect_template($jobid);
        }
} elseif ( strtolower($jobtype) == "tape") {
    if ( isset($_GET['print']) && $_GET['print'] == 'Y')
        tape_template($jobid, True);
    else {
        require_once("../include/header.php");
        tape_template($jobid, True);
    }
}
else
	echo "Job not found";


if ( isset($_GET['print']) )
{
?>
  <strong>JOB ID:</strong>
<?
	echo $jobid;
}
else
{
?>
	<div style="float: left"><a href="/unet/copycenter/index.php">Home</a> >> <a href="jobs.php">Open Jobs</a> >> Job <? echo $jobid; ?></div>
	<div style="float: right"><a href="/unet/copycenter/view.php?job=<? echo $jobid ?>&print=Y" target='blank'>Print</a></div>
<?
}

function lookup_jobtype($jobid)
{
	global $sage;
	$query = "SELECT * FROM unet_mc_jobid WHERE jobid = '$jobid'";

	$res =& $sage->query($query);
	if ( $res->numRows() == 1)
	{
		$row = $res->fetchRow();
		return $row[1];
	}
	
	else
		return False;
}


function build_download($jobid, $jobtable)
{
	global $sage;
	$query = "SELECT filename, randname from $jobtable";
	$res =& $sage->query($query);
	$row = $res->fetchRow();
	
	$fparts = explode(".", $row[0]);
	$fext = $fparts[count($fparts) - 1];
	$randname = $row[1];
	$filename = $row[0];
	$fpath = "/var/www/unet/copycenter/job_files/";
	$filesize = filesize($fpath . $randname);
	$mtype = mime_content_type($fpath . $randname);

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: $mtype");
	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . $filesize);
	$file = @fopen($fpath . $randname, "rb");
	if ($file) 
	{
		while (!feof($file))
		{
			print(fread($file, 1024*8));
			flush();
			if (connection_status()!=0)
			{
				@fclose($file);
				die();
			}
		}
		@fclose($file);	
	}
}



/******************
 SPIRAL TEMPLATE  *
*******************
This template takes a jobid, assuming that the job is a spiral bind job
and looks in the ITdb.unet_mc_spiral table for the matching jobid

If the jobid is not found it is assumed that the job doesn't exist
Else the job information is displayed in the same form as is emailed
*/

function spiral_template($jobid, $print = False)
{
	global $sage;
	$query = "SELECT jobid, username, jobname, convert(varchar, submitdate, 100) as submitdate,
      convert(varchar, duedate, 100) as duedate, phone, department, quantity, 
		transport, ink, spiralcolor, spiralside, colorcover, cardstockcover, 
		cardstockcolor, plasticcover, blackback, cardstockback, cardstockbackcolor, 
		comments, filename, randname, duplex, account from unet_mc_spiral WHERE jobid = '$jobid'";
	$res =& $sage->query($query);
	if ( $res->numRows() != 1)
		die("No job found");
	else
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
if ( $print )
{
?>


	<script language='JavaScript'>
	 window.onload = window.print()
	</script>
<?
}
?>
<table width=70% align="center">
<tr>
   <td align="left"><strong>Job Name</strong></td>
   <td align="left"><? echo $row["jobname"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Date Due</strong></td>
   <td align="left"><? echo $row["duedate"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Phone</strong></td>
   <td align="left"><? echo $row["phone"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Department</strong></td>
   <td align="left"><? echo $row["department"]; ?></td>
</tr>
<tr>
	<td align="left"><strong>Account</strong></td>
	<td align="left"><? echo $row["account"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>File</strong></td>
   <td align="left"><a href="download.php?jobid=<? echo $jobid; ?>"><? echo $row["filename"]; ?></a></td>
</tr>
<tr>
   <td align="left"><strong>Quantity</strong></td>
   <td align="left"><? echo $row["quantity"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Double Sided</strong></td>
   <td align="left"><? echo $row["duplex"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Spiral Color</strong></td>
   <td align="left"><? echo $row["spiralcolor"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Spiral Side</strong></td>
   <td align="left"><? echo $row["spiralside"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Pick up/Deliver</strong></td>
   <td align="left"><? echo $row["transport"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Document in color</strong></td>
   <td align="left"><? echo $row["ink"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Front cover color</strong></td>
   <td align="left"><? echo $row["colorcover"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Cardstock front cover</strong></td>
   <td align="left"><? echo $row["cardstockcover"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Plastic Cover</strong</td>
   <td align="left"><? echo $row["plasticcover"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Black back cover</strong></td>
   <td align="left"><? echo $row["blackback"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Cardstock back cover</strong></td>
   <td align="left"><? echo $row["cardstockback"]; ?></td>
</tr>
<tr>
	<td align="left"><strong>Cardstock back color</strong></td>
	<td align="left"><? echo $row["cardstockbackcolor"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Comments</strong></td>
   <td align="left"><? echo $row["comments"]; ?></td>
</tr>

<?
}



/****************
Copy Template *
*****************
This template takes a jobid, assuming that the job is a copy job 
and looks in the ITdb.unet_mc_copy table for the matching jobid

If the jobid is not found it is assumed that the job doesn't exist
Else the job information is displayed in the same form as is emailed
*/

function copy_template($jobid, $print = False)
{
	global $sage;
	$query = "SELECT jobid, username, jobname, convert(varchar, submitdate, 100) as submitdate,
		convert(varchar, duedate, 100) as duedate, phone, department, quantity, folding, transport,
		ink, staple, cut, papersize, papertype, papercolor, comments, filename, randname, collation, duplex,
		laminate, punch, account, pages from unet_mc_copy WHERE jobid = '$jobid'";
	$res =& $sage->query($query);
	if ( $res->numRows() != 1)
		die("No job found");
	else
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
if ( $print )
{
?>


   <script language='JavaScript'>
    window.onload = window.print()
   </script>
<?
}
?>
<table width=70% align="center">
<tr>
   <td align="left"><strong>Job Name</strong></td>
   <td align="left"><? echo $row["jobname"]; ?></td>
</tr>
<tr>
	<td align="left"><strong>Customer</strong></td>
	<td align="left"><? echo ncu_getdisplayname($row["username"]); ?></td>
</tr>
<tr>
	<td align="left"><strong>Email</strong></td>
	<td align="left"><a href="mailto:<? echo $row["username"]."@northcentral.edu?subject=Copy Center Job $jobid\">".$row["username"]; ?>@northcentral.edu</a></td>
</tr>
<tr>
   <td align="left"><strong>File</strong></td>
 <td align="left"><a href="download.php?jobid=<? echo $jobid; ?>"><? echo $row["filename"]; ?></a></td>
</tr>
<tr>
	<td align="left"><strong>Number of Pages</td>
	<td align="left"><? echo $row["pages"]; ?></td
</tr>
<tr>
	<td align="left"><strong>Submitted on</strong></td>
	<td align="left"><? echo $row["submitdate"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Date Due</strong></td>
   <td align="left"><? echo $row["duedate"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Phone</strong></td>
   <td align="left"><? echo $row["phone"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Department</strong></td>
   <td align="left"><? echo $row['department']; ?></td>
</tr>
<tr>
   <td align="left"><strong>Account</strong></td>
   <td align="left"><? echo $row["account"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Quantity</strong></td>
   <td align="left"><? echo $row["quantity"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Double Sided</strong></td>
   <td align="left"><? echo $row["duplex"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Folding</strong></td>
   <td align="left"><? echo $row["folding"]; ?></td>
</tr>
<tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr><tr height="6"></tr>
<tr>
   <td align="left"><strong>Pick up/Deliver</strong></td>
   <td align="left"><? echo $row["transport"]; ?></td>
</tr>
<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>
<tr>
   <td align="left"><strong>Ink</strong></td>
   <td align="left"> <? echo $row["ink"]; ?></td>
</tr>
<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
   <td align="left"><strong>Staple</strong></td>
   <td align="left"><? echo $row["staple"]; ?></td>
</tr>
<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
   <td align="left"><strong>Cutting</strong></td>
   <td align="left"><? echo $row["cut"]; ?></td>
</tr>
<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
   <td align="left"><strong>Laminate</strong></td>
   <td align="left"><? echo $row["laminate"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>UnCollate</strong></td>
   <td align="left"><? echo $row["collation"]; ?></td>
</tr>
<tr>
   <td align="left"><strong>Hole Punch</strong></td>
   <td align="left"><? echo $row["punch"]; ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>

<tr>
   <td align="center"><strong>Paper Size</strong></td>
   <td align="center"><strong>Paper Type</strong></td>
   <td align="center"><strong>Paper Color</strong></td>
</tr>
<tr>
   <td align="center"><? echo $row["papersize"] ?></td>
   <td align="center"><? echo $row["papertype"] ?></td>
   <td align="center"><? echo $row["papercolor"] ?></td>
</tr>

<tr height="6"></tr>
<tr height="2" bgcolor="#b84702"><td height="2" colspan="3"></td></tr>
<tr height="6"></tr>


<tr>
   <td align="left"><strong>Comments</strong></td>
   <td align="left"><? echo $row["comments"] ?> </td>
</tr>

<?

}
