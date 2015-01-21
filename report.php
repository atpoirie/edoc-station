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

$month = $_GET['month'];
$year = date(Y);
if ($month > date(n))
    $year -= 1;


$invoice = array();
$total = array();

    $res = $aJob->sql->query("SELECT jobid, jobname, username, completedate, departmentcharge, account, cost from unet_copycenter_job 
    WHERE DATEPART(yyyy, completedate) = '$year'
    AND DATEPART(mm, completedate) = '$month'
            AND cost IS NOT NULL
            AND cost <> '0'
            AND flag = 'Complete'
    ORDER BY departmentcharge");
    if (PEAR::isError($res)) {
            return;
        }
    while($row = $res->fetchRow()) {
        if ($row['departmentcharge'] == 'Account') {
            $aKey = $row['departmentcharge']."-".$row['account'];
        } else {
            $aKey = $row['departmentcharge'];
        }
        if (array_key_exists($aKey, $invoice)) {
            $invoice[$aKey][] = $row;
            $total[$aKey] += $row['cost'];
        } else {
            if ($row['departmentcharge'] == 'Account') {
                $invoice[$row['departmentcharge']."-".$row['account']] = array();
                $invoice[$row['departmentcharge']."-".$row['account']][] = $row;
                $total[$row['departmentcharge']."-".$row['account']] = $row['cost'];
            } else {
                $invoice[$row['departmentcharge']] = array();
                $invoice[$row['departmentcharge']][] = $row;
                $total[$row['departmentcharge']] = $row['cost'];
            }
        }
    }

?>
<HTML>
    <HEAD><link rel="stylesheet" type="text/css" href="css/copycenter.css"/></head>
    <BODY>
    <h2 style="margin-top:0px; margin-bottom:5px;">Copy Center Report for <? echo date("F", mktime(0, 0, 0, $month, 1, 0, 0)) . " " . $year; ?></h2>
    <br>
    <span id="seperator"></span>

    <? 
        foreach($invoice as $department=>$jobs){
            ?>
    <br>
    <label style="width:500px;margin-left:15px;"><? echo $department ?></label>
    <span style="font-weight: bold;">Total: $<? echo money_format('%i', $total[$department]) ?></span>
    <br>
    <div style="width:100%;height: 10px;"></div><?
            for($i=0;$i<count($jobs); $i++) {
                echo "<span style='padding-left:30px; padding-right:10px; width:200px; float:left;'>
                    <a href='admin.php?type=job&function=view&job=".$jobs[$i]['jobid']."'>".$jobs[$i]['jobid']." - ".$jobs[$i]['jobname']."</a>
                        </span>
                    <span style='width:130px; float:left; font-size:13px;'>".ncu_getdisplayname($jobs[$i]['username'])."</span>
                    <span style='width:100px; float:left; font-size:13px;'>".preg_replace('/ .*$/', ' ', $jobs[$i]['completedate'])."</span>
                    <span style='width:80px; float:left; font-size:13px;'>$".money_format('%i', $jobs[$i]['cost'])."</span> 
                    <br><br>";
            }
            echo '<span id="seperator"></span>';
    }

    
    ?>
    </BODY>
</HTML>

