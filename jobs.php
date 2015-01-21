<?php

session_start();
//require_once "/var/authscripts/ncu_auth.inc";
$quicklinks = True;
require_once("include/header.php");

//ncu_forcesecure();
//ncu_forceauth();
$uname = ncu_getusername();

if ( ncu_isstudent($uname))
{
   echo "This system is for NCU employees only";
   require_once("../include/footer.php");
   die();
}


$sage = ncu_sage_unet_menu();

if ( isset($_GET["complete"]) )
	complete_job($_GET["complete"]);

if ( isset($_GET["status"]) && $_GET["status"] == "open" )
	display_uncomplete();

elseif ( isset($_GET["status"]) && $_GET["status"] == "closed" )
	display_complete();

else
	display_uncomplete();


function display_uncomplete()
{
?>
   <style type="text/css">
   .demo
   {
      border:4px solid #b84702;
      border-top:20px solid #b84702;
      margin:10px;
      font-size:17px;
      text-align:left;
   }
	</style>

	<table align="center" class="demo" width="90%" cellspacing="10">
   	<tr>
   	   <th align="left">Open Jobs</th>
   	   <th></th>
   	   <th align="right"><a href="jobs.php?status=closed">Closed Jobs</a></th>
	   </tr>
	</table>

	<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> Open Jobs</p>
<?

	global $sage;
	$query = "SELECT * FROM unet_mc_jobid WHERE completed <> 'Y' or completed IS NULL ORDER BY duedate";
	$res =& $sage->query($query);
	while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		if ($row["jobtype"] == "copy" )
			display_copy($row["jobid"]);
		elseif ($row["jobtype"] == "spiral" )
			display_spiral($row["jobid"]);
		elseif ($row["jobtype"] == "perfect" )
			display_perfect($row["jobid"]);
	}	

}

function display_complete()
{
?>
   <style type="text/css">
   .demo
   {
      border:4px solid #b84702;
      border-top:20px solid #b84702;
      margin:10px;
      font-size:17px;
      text-align:left;
   }
   </style>

   <table align="center" class="demo" width="90%" cellspacing="10">
      <tr>
         <th align="left"><a href="jobs.php?status=open">Open Jobs</th>
         <th></th>
         <th align="right">Closed Jobs</th>
      </tr>
   </table>

   <p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> Closed Jobs</p>
<?

   global $sage;

//Only query last 40 days of completed jobs, 3456000 = 40 days
	$timestamp = time() - 3456000;
	$datestamp = date('Ymd', $timestamp);
   $query = "SELECT * FROM unet_mc_jobid WHERE completed = 'Y' AND completed_date > '$datestamp' ORDER BY completed_date DESC";
   $res =& $sage->query($query);
   while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
   {
      if ($row["jobtype"] == "copy" )
         display_copy($row["jobid"]);
      elseif ($row["jobtype"] == "spiral" )
         display_spiral($row["jobid"]);
      elseif ($row["jobtype"] == "perfect" )
         display_perfect($row["jobid"]);
   }

}


function complete_job($jobid)
{
	global $sage;
	$query = "SELECT * FROM unet_mc_jobid WHERE jobid = '$jobid'";
	$res =& $sage->query($query);
	if ( $res->numRows() == 1 )
	{
			$query = "UPDATE unet_mc_jobid SET completed = 'Y', completed_date = GetDate() WHERE jobid = '$jobid'";
			$res =& $sage->query($query);
			if (PEAR::isError($db))
				die($res->getMessage());
			else
				echo "<p>Job $jobid has been marked completed</p>";
	}
	else
	{
		echo "Job $jobid could not be found";
	}
}


function display_copy($jobid)
{
global $sage;

$res =& $sage->query("SELECT * FROM unet_mc_copy WHERE jobid = '$jobid'");

$row = $res->fetchRow();

$resComplete =& $sage->query("SELECT * FROM unet_mc_jobid WHERE jobid = '$jobid'");
$rowComplete = $resComplete->fetchRow();

$date = substr($row[4], 0, 11);
$submitted = substr($row[3], 0, 11);
?>
<table class="demo" align="center"  width=90%>
   <tr>
      <td><strong>ID: </strong><a href="view.php?job=<? echo $row[0]; ?>"><?echo $row[0]; ?></a></td>
      <td style="padding:6px;"><strong>Jobname: </strong><? echo $row[2]; ?></td>
   </tr>
   <tr>
      <td><strong>Jobtype: </strong>Copy</td>
      <td style="padding:6px;"><strong>Submitted: </strong><? echo $submitted; ?></td>
   <tr>
      <td><strong>Requestor: </strong><? echo ncu_getdisplayname($row[1]); ?></td>
      <td style="padding:6px;"><strong>Due: </strong><? echo $date; ?></td>
   </tr>
	<tr>
	<?
		if ( isset($_GET["status"]) && $_GET["status"] == "closed")
		{
				$complete = substr($rowComplete[4], 0, 11);
	?>
				<td><strong>Completed: </strong><? echo $complete; ?></td>
	<?
		}
		else
		{
	?>
      	<td><a href="jobs.php?complete=<? echo $row[0]; ?>"><img src="complete.png" alt="Mark this job completed" title="Mark this job completed" style="border:0px;"/></a></td>
	<?
     	}
	?>
		 <td><a href="view.php?job=<? echo $row[0]; ?>"><img src="view.png" alt="View job" title="View job details" style="border:0px;"/></a></td>
   </tr>
</table>
<?

}


function display_spiral($jobid)
{
	global $sage;
	$res =& $sage->query("SELECT * FROM unet_mc_spiral WHERE jobid = '$jobid'");
	$row = $res->fetchRow();
	$date = substr($row[4], 0, 11);
	$submitted = substr($row[3], 0, 11);
	?>
	<table class="demo" align="center" width=90% >
	   <tr>
	      <td><strong>ID: </strong><a href="view.php?job=<? echo $row[0]; ?>"><?echo $row[0]; ?></a></td>
	      <td style="padding:6px;"><strong>Jobname: </strong><? echo $row[2]; ?></td>
	   </tr>
	   <tr>
	      <td><strong>Jobtype: </strong>Spiral Bind</td>
	      <td style="padding:6px;"><strong>Submitted: </strong><? echo $submitted; ?></td>
	   <tr>
	      <td><strong>Requestor: </strong><? echo ncu_getdisplayname($row[1]); ?></td>
	      <td style="padding:6px;"><strong>Due: </strong><? echo $date; ?></td>
	   </tr>
	   <tr>
	      <td><a href="jobs.php?complete=<? echo $row[0]; ?>"><img src="complete.png"  alt="Mark this job completed" title="Mark this job completed" style="border:0px;"/></a></td>
	      <td><a href="view.php?job=<? echo $row[0]; ?>"><img src="view.png" alt="View job" title="View job details" style="border:0px;"/></a></td>
	   </tr>
	</table>
	<?
}



function display_perfect($jobid)
{


}


