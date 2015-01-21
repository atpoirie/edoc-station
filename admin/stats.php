<?php

session_start();
require_once "/var/authscripts/ncu_auth.inc";
$quicklinks = True;

//ncu_forcesecure();
//ncu_forceauth();
$uname = ncu_getusername();

include_once("../include/header.php");

if ( ncu_isstudent($uname))
{
   echo "This system is for NCU employees only";
   require_once("../include/footer.php");
   die();
}

$sage = ncu_sage_unet_menu();
$query = "SELECT username from unet_mc_admin WHERE username = '$uname'";
$res =& $sage->query($query);
if ( $res->numRows() != 1 )
{
	echo "You are not a Online Copy Center administrator";
	require_once("../include/footer.php");
	die();
}


//*************************************
//Data for Chart 1 & 4 & 5
//*************************************
$copyMonthTotal;
$spiralBindMonthTotal;
$currentMonth = date('m');
$foldMonthTotal;
$laminateMonthTotal;
$lastminuteMonthTotal; //jobs that were submitted with a duedate less than 4 hours from submission date

//1-19-2012 Added by ATP
//This calculates a date 9 months before time() for us to use so we make sure to only get the last 6 months of data
//Previously we were only matching based on the month which only worked for the first year. 
//Another possibility was to just delete old records, but they might be nice for future comparison
$oldestPossible = date('Y-m-d H:i:s.000', mktime(0,0,0,date("n"-9), date("j"), date("y")));


for($i=6; $i>=0; $i--)
{
	$month = date('m', mktime(0,0,0,date("n")-$i, date("j"), date("y")));
	$oldestPossible = date('Y-m-d H:i:s.000', mktime(0,0,0,date("n"-9), date("j"), date("y")));
	$res =& $sage->query("SELECT count(*) from unet_mc_jobid WHERE jobtype='copy' 
		AND datepart(mm, completed_date) = '$month'
		AND completed_date > '$oldestPossible'");
	$row = $res->fetchRow();
	$copyMonthTotal.=$row[0].", ";
		
	$res =& $sage->query("SELECT count(*) FROM unet_mc_jobid WHERE jobtype='spiral' 
		AND datepart(mm, completed_date) = '$month'
		AND completed_date > '$oldestPossible'");
	$row = $res->fetchRow();
	$spiralBindMonthTotal.=$row[0]. ", ";	
	
	$res =& $sage->query("SELECT count(*) FROM unet_mc_jobid 
								INNER JOIN
								unet_mc_copy on unet_mc_jobid.jobid = unet_mc_copy.jobid
								WHERE jobtype='copy' 
								AND datepart(mm, unet_mc_jobid.completed_date) = '$month'
								AND unet_mc_jobid.completed_date > '$oldestPossible'
								AND unet_mc_copy.laminate = 'Yes'");
	$row = $res->fetchRow();
	$laminateMonthTotal.=$row[0]. ", ";

   $res =& $sage->query("SELECT count(*) FROM unet_mc_jobid 
                        INNER JOIN
                        unet_mc_copy on unet_mc_jobid.jobid = unet_mc_copy.jobid
                        WHERE jobtype='copy' 
                        AND datepart(mm, unet_mc_jobid.completed_date) = '$month'
								AND unet_mc_jobid.completed_date > '$oldestPossible'
                        AND unet_mc_copy.folding != 'No Folding'
								AND unet_mc_copy.folding != 'none' ");

   $row = $res->fetchRow();
   $foldMonthTotal.=$row[0]. ", ";

	$res =& $sage->query("select count(*) from unet_mc_copy 
		WHERE duedate - submitdate < '1900-01-01 04:00:00.000' 
		AND datepart(mm, submitdate) = '$month'
		AND submitdate > '$oldestPossible'");
	$row = $res->fetchRow();
	$lastminuteMonthTotal .= $row[0]. ", ";
}


//***********************************
//Data for Chart 2
//***********************************
$userMostJobs = "1";
$userMostJobsTotal= "";
$time = time() - (60*60*24*30);
$oneMonthAgo = date("Ymd", $time);
$query = "SELECT TOP 10 username, count(JOBS.username) as occurences 
			FROM 
				unet_mc_jobid 
				INNER JOIN 
				(
					SELECT jobid, username FROM unet_mc_copy
					UNION ALL
					SELECT jobid, username FROM unet_mc_spiral
               )
               JOBS
               ON ( JOBS.jobid = unet_mc_jobid.jobid )
            WHERE
            unet_mc_jobid.completed_date > '$oneMonthAgo'
            AND username != ''
            GROUP BY JOBS.username
             ORDER BY occurences DESC";
$res =& $sage->query($query);

while ( $row = $res->fetchRow())
{
	if ($userMostJobs == "1")
	{
		$userMostJobs = "'".$row[0]."'";
		$userMostJobsTotal = $row[1];
	}
	else
	{
		$userMostJobs .= ", '".$row[0]."'";
		$userMostJobsTotal .= ", ".$row[1];
	}
}



//***************************************
//Data for Chart 3
//***************************************
$departmentMostJobs = "1";
$departmentMostJobsTotal= "";
$time = time() - (60*60*24*30);
$oneMonthAgo = date("Ymd", $time);
$query = "SELECT TOP 10 department, count(JOBS.department) as occurences 
			FROM 
				unet_mc_jobid 
				INNER JOIN 
					(
					SELECT jobid, department FROM unet_mc_copy
					UNION ALL
					SELECT jobid, department FROM unet_mc_spiral
					)
					JOBS
					ON ( JOBS.jobid = unet_mc_jobid.jobid )
				WHERE
				unet_mc_jobid.completed_date > '$oneMonthAgo'
  				AND department != ''
				GROUP BY JOBS.department
  				 ORDER BY occurences DESC";
$res =& $sage->query($query);
while ( $row = $res->fetchRow())
{
   if ($departmentMostJobs == "1")
   {
      $departmentMostJobs = "'".$row[0]."'";
      $departmentMostJobsTotal = $row[1];
   }
   else
   {
      $departmentMostJobs .= ", '".$row[0]."'";
      $departmentMostJobsTotal .= ", ".$row[1];
   }
}


//*********************************
//Data for chart 4
//*********************************



?>

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jscharts/adapters/mootools-adapter.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jscharts/highcharts.js"></SCRIPT>

<SCRIPT language="javascript">

var chart1; //Completed Jobs by month
var chart2; //Top users - past 30 days
var chart3; //Top departments - past 30 days
var chart4; //Laminate & fold copy jobs by month
var chart5; //Last minute jobs - less than 4 hour turnaround

//Chart 1 - Completed Jobs by month
$(document).ready(function() {
	chart1 = new Highcharts.Chart({
		chart: {
			renderTo: 'chart-container-1',
			defaultSeriesType: 'column'
		},
		title: {
			text: 'Completed Jobs'
		},
		xAxis: {
			categories: [<?php
				for($i=6; $i>=0; $i--)
				{
   				$month = date('M', mktime(0,0,0,date("m")-$i, date("d"), date("Y")));
					echo "'" . $month . "',";
				}
			?>]	
		},
		yAxis: {
			min: 0,
			title: {
				text: 'Number of Jobs'	
			}
		},
		series: [{
			name: 'Copies',
			data: [<? echo $copyMonthTotal; ?>]
		},
		{
			name: 'Spiral Bind',
			data: [<? echo $spiralBindMonthTotal; ?>]
		}]
	});
});

//Chart 2 - Top Users - last 30 days
$(document).ready(function() {
	chart2 = new Highcharts.Chart({
		chart: {
			renderTo: 'chart-container-2',
			defaultSeriesType: 'bar'
		},
		title: {
			text: 'Top Users last 30 days'
		},
		xAxis: {
			categories: [<?echo $userMostJobs; ?>],
			title: {
				text: 'Username'
			}
		},
		yAxis: {
			min: 0,
			title: {
				text: 'Jobs Submitted',
			}
		},
		series: [{
			name: 'Jobs',
			data: [<? echo $userMostJobsTotal; ?>]
		}]
	});
});


//Chart 3 - Top Departments - last 30 days
$(document).ready(function() {
   chart3 = new Highcharts.Chart({
      chart: {
         renderTo: 'chart-container-3',
         defaultSeriesType: 'bar'
      },
      title: {
         text: 'Top Departments last 30 days'
      },
      xAxis: {
         categories: [<?echo $departmentMostJobs; ?>],
         title: {
            text: 'Department'
         }
      },
      yAxis: {
         min: 0,
         title: {
            text: 'Jobs Submitted',
         }
      },
      series: [{
         name: 'Jobs',
         data: [<? echo $departmentMostJobsTotal; ?>]
      }]
   });
});

//Chart 4 - Laminate and folding jobs by month
$(document).ready(function() {
   chart1 = new Highcharts.Chart({
      chart: {
         renderTo: 'chart-container-4',
         defaultSeriesType: 'column'
      },
      title: {
         text: 'Folding and Laminating Jobs'
      },
      xAxis: {
			categories: [<?php
            for($i=6; $i>=0; $i--)
            {
               $month = date('M', mktime(0,0,0,date("m")-$i, date("d"), date("Y")));
               echo "'" . $month . "',";
            }
         ?>] 
		},
      yAxis: {
         min: 0,
         title: {
            text: 'Number of Jobs'  
         }
      },
      series: [{
         name: 'Folding',
         data: [<? echo $foldMonthTotal; ?>]
      },
      {
         name: 'Laminating',
         data: [<? echo $laminateMonthTotal; ?>]
      }]
   });
});


//Chart 5 - last minute jobs with less than 4 hour turnaround time
$(document).ready(function() {
   chart5 = new Highcharts.Chart({
      chart: {
         renderTo: 'chart-container-5',
         defaultSeriesType: 'column'
      },
      title: {
         text: 'Last Minute Jobs'
      },
		subtitle: {
			text: 'Less than 4 hour turnaround'
		},
      xAxis: {
         categories: [<?php
            for($i=6; $i>=0; $i--)
            {
               $month = date('M', mktime(0,0,0,date("m")-$i, date("d"), date("Y")));
               echo "'" . $month . "',";
            }
         ?>]   
      },
      yAxis: {
         min: 0,
         title: {
            text: 'Number of Jobs'  
         }
      },
      series: [{
         name: 'Jobs',
         data: [<? echo $lastminuteMonthTotal; ?>]
      }]
   });
});



</script>

<br>
<p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> Stats</p>

<div id="chart-container-1" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10PX; background-color: #D0C6AD"></div>
<div id="chart-container-2" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10px; background-color: #D0C6AD"></div>
<div id="chart-container-3" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10px; background-color: #D0C6AD"></div>
<div id="chart-container-4" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10px; background-color: #D0C6AD"></div>
<div id="chart-container-5" style="width: 100%; height: 300px"></div>


<?php
require_once("../include/footer.php");

