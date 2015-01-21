
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

if (ncu_isstudent($uname) || !$aJob->isAdmin) {
    include_once("include/header.php");
    echo "You are not authorized to view this page";
    require_once("../include/footer.php");
    return;
}

$last6Months = array();
for ($i=0; $i<7; $i++) {
    $last6Months[] = array(date('Y', mktime(0, 0, 0, date("m")-$i, 1, date("Y"))), date('m', mktime(0, 0, 0, date("m")-$i, 1, date("Y"))));
}


$invoice = array();
$total = array();
foreach($last6Months as $month) {
    if (!in_array($month[1], $invoice))
            $invoice[$month[1]] = array();
    $res = $aJob->sql->query("SELECT jobid, jobname, username, submitdate, departmentcharge, cost from unet_copycenter_job 
    WHERE DATEPART(yyyy, submitdate) = '$month[0]'
    AND DATEPART(mm, submitdate) = '$month[1]'
            AND cost IS NOT NULL
            AND cost <> '0'
    ORDER BY departmentcharge");
    if (PEAR::isError($res)) {
            return;
        }
    while($row = $res->fetchRow()) {
        if (array_key_exists($row['departmentcharge'], $invoice[$month[1]])) {
            $invoice[$month[1]][$row['departmentcharge']][] = $row;
           
            $total[$row['departmentcharge']] += $row['cost'];
        } else {
            $invoice[$month[1]][$row['departmentcharge']] = array();
            $invoice[$month[1]][$row['departmentcharge']][] = $row;
            $total[$row['departmentcharge']] = $row['cost'];

        }
    }
}

foreach($last6Months as $month) {
?>

    <h2 style="margin-top:0px; margin-bottom:5px;"><? echo date('F', mktime(0,0,0,$month[1],1,date("Y")))?> </h2>
    <? 
        foreach($invoice[$month[1]] as $department=>$jobs){
            ?><label style="width:250px;margin-left:15px;"><? echo $department ?></label><span style="float:right;padding-right: 60px;">Total: $<? echo money_format('%i', $total[$department]) ?></span><br><div style="width:100%;height: 10px;"></div><?
            for($i=0;$i<count($jobs); $i++) {
                echo "<span style='padding-left:30px; padding-right:10px; width:160px; float:left;'>
                    <a href='admin.php?id=".$jobs[$i]['jobid']."'>".$jobs[$i]['jobid']." - ".$jobs[$i]['jobname']."</a></span>
                    <span style='width:80px; float:left; font-size:13px;'>".$jobs[$i]['username']."</span>
                    <span style='width:140px; float:left; font-size:13px;'>".$jobs[$i]['submitdate']."</span>
                        <span style='width:80px; float:left; font-size:13px;'>$".money_format('%i', $jobs[$i]['cost'])."</span>
                            <br><br>";
            }
          
        
    }
    ?>
            <div id="seperator"></div> 

<?
}

?>
