<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
</HEAD>
<?php


session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";

//ncu_forcesecure();
//ncu_forceauth();
$uname = ncu_getusername();
$aJob = new Job($uname);

if (!$aJob->isAdmin) {
    include_once("include/header.php");
    echo "You are not authorized to view this page";
    require_once("../include/footer.php");
    return;
}

//*************************************
//Data for Chart 1 & 4 & 5
//*************************************
$copyMonthTotal;
$spiralBindMonthTotal;
$perfectBindMonthTotal;
$tapeBindMonthTotal;
$pressBindMonthTotal;
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
    $jobs = array('Copy'=>0, 'Spiral'=>0, 'Perfect'=>0, 'Press'=>0, 'Tape'=>0);
   $month = date('m', mktime(0,0,0,date("n")-$i, date("j"), date("y")));
   $oldestPossible = date('Y-m-d H:i:s.000', mktime(0,0,0,date("n"-9), date("j"), date("y")));
   $res =& $aJob->sql->query("SELECT jobtype, count(*) as total from unet_copycenter_job
      WHERE datepart(mm, completedate) = '$month'
      AND completedate > '$oldestPossible'
           GROUP BY jobtype");
   while($row = $res->fetchRow()){
       $jobs[$row['jobtype']] = $row['total'];
       }
           
   $copyMonthTotal .= $jobs['Copy'].", ";
   $perfectBindMonthTotal .= $jobs['Perfect'].", ";
   $tapeBindMonthTotal .= $jobs['Tape'].", ";
   $pressBindMonthTotal .= $jobs['Press'].", ";
   $spiralBindMonthTotal .= $jobs['Spiral'].", ";
   
   
   $res =& $aJob->sql->query("SELECT count(*) as total FROM unet_copycenter_job
                        INNER JOIN
                        unet_copycenter_copy on unet_copycenter_job.jobid = unet_copycenter_copy.id
                        AND datepart(mm, unet_copycenter_job.completedate) = '$month'
                        AND unet_copycenter_job.completedate > '$oldestPossible'
                        AND unet_copycenter_copy.laminate = 'Yes'");
   $row = $res->fetchRow();
   $laminateMonthTotal.=$row['total']. ", ";

//   $res =& $aJob->sql->query("SELECT count(*) as total FROM unet_copycenter_job 
//                        INNER JOIN
//                        unet_copycenter_copy on unet_copycenter_job.jobid = unet_copycenter_copy.id
//                        AND datepart(mm, unet_copycenter_job.completedate) = '$month'
//                        AND unet_copycenter_job.completedate > '$oldestPossible'
//                        AND unet_copycenter_copy.folding != 'No Folding'
//                        AND unet_copycenter_copy.folding != 'none' ");
//
//   $row = $res->fetchRow();
//   $foldMonthTotal.=$row['total']. ", ";

   $res =& $aJob->sql->query("select count(*) total from unet_copycenter_job 
      WHERE duedate - submitdate < '1900-01-01 04:00:00.000' 
      AND datepart(mm, submitdate) = '$month'
      AND submitdate > '$oldestPossible'");
   $row = $res->fetchRow();
   $lastminuteMonthTotal .= $row['total']. ", ";
}

//***********************************
//Data for Chart 2
//***********************************
$userMostJobs = "1";
$userMostJobsTotal= "";
$time = time() - (60*60*24*30);
$oneMonthAgo = date("Ymd", $time);
$query = "SELECT TOP 10 username, count(username) as occurences 
         FROM 
            unet_copycenter_job 
            WHERE
            unet_copycenter_job.completedate > '$oneMonthAgo'
            AND username != ''
            GROUP BY username
             ORDER BY occurences DESC";
$res =& $aJob->sql->query($query);

while ( $row = $res->fetchRow())
{
   if ($userMostJobs == "1")
   {
      $userMostJobs = "'".$row['username']."'";
      $userMostJobsTotal = $row['occurences'];
   }
   else
   {
      $userMostJobs .= ", '".$row['username']."'";
      $userMostJobsTotal .= ", ".$row['occurences'];
   }
}



//***************************************
//Data for Chart 3
//***************************************
$departmentMostJobs = "1";
$departmentMostJobsTotal= "";
$time = time() - (60*60*24*30);
$oneMonthAgo = date("Ymd", $time);
$query = "SELECT TOP 10 departmentcharge, count(departmentcharge) as occurences 
         FROM 
            unet_copycenter_job 
            WHERE
            unet_copycenter_job.completedate > '$oneMonthAgo'
            AND departmentcharge != ''
            GROUP BY departmentcharge
             ORDER BY occurences DESC";
$res =& $aJob->sql->query($query);
while ( $row = $res->fetchRow())
{
   if ($departmentMostJobs == "1")
   {
      $departmentMostJobs = "'".$row['departmentcharge']."'";
      $departmentMostJobsTotal = $row['occurences'];
   }
   else
   {
      $departmentMostJobs .= ", '".$row['departmentcharge']."'";
      $departmentMostJobsTotal .= ", ".$row['occurences'];
   }
}


//*********************************
//Data for chart 4
//*********************************

?>

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
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
         name: 'Copy',
         data: [<? echo $copyMonthTotal; ?>]
      },
      {
         name: 'Spiral',

         data: [<? echo $spiralBindMonthTotal; ?>]
      },
      {
          name: 'Press',
          data: [<? echo $pressBindMonthTotal; ?>]
      },
      {
          name: 'Tape',
          data: [<? echo $tapeBindMonthTotal; ?>]
      },
      {
          name: 'Perfect',
          data: [<? echo $perfectBindMonthTotal; ?>]
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
            text: 'Jobs Submitted'
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
         text: 'Laminating Jobs'
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
      series: [ 
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

<div id="chart-container-1" style="width: 100%; height: 300px"></div>                                   
<div id="spacer" style="width: 100%; height: 10PX; background-color: #D0C6AD"></div>
<div id="chart-container-2" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10px; background-color: #D0C6AD"></div>
<div id="chart-container-3" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10px; background-color: #D0C6AD"></div>
<div id="chart-container-4" style="width: 100%; height: 300px"></div>
<div id="spacer" style="width: 100%; height: 10px; background-color: #D0C6AD"></div>
<div id="chart-container-5" style="width: 100%; height: 300px"></div>



