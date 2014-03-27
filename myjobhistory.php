<?php

session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/copyclass.php";
require_once "./include/perfectclass.php";
require_once "./include/pressclass.php";
require_once "./include/spiralclass.php";


$pagetitle = "Online Copy Center";
$quicklinks = true;
ncu_forcesecure();
ncu_forceauth();
$uname = ncu_getusername();

if (isset($_GET['view']) && $_GET['view'] != '') {
    $aJob = new Job($uname);
    $aJob->get_job_type_by_id($_GET['view']);
    $bJob = new $aJob->jobType($uname);
    $bJob->get_job_by_id($_GET['view']);
    if ($bJob->username == $uname || $aJob->isAdmin()){
        if (preg_match('/Complete/', $bJob->flag) || preg_match('/Cancel/', $bJob->flag) ) {
            $bJob->view_job();
            require_once("../include/header.php");
            ?>
            <link rel="stylesheet" type="text/css" href="css/copycenter.css"/>
            <div id="inner_container">
            
            <br/><br/>
                <a href="/unet/copycenter/myjobhistory.php"><img src="./image/back-icon.png">Back</a>
            <br/><br/>
            <?
            echo $bJob->view;
            echo "</div>";
        } else {
            $bJob->update = "1";
            $bJob->serialize();
            $time = 60 * 60 * 24 * 2 + time();
            setcookie($bJob->jobType, $bJob->serial, $time, '/unet/copycenter/');
            header("Location: /unet/copycenter/".strtolower($bJob->jobType).".php");
        }
    } else {
        $msg = "<p>Whoops! It doesn't appear that you are the owner of this job $uname</p>";
        display_jobs($msg);
    }
} elseif (isset($_GET['cancel']) && $_GET['cancel'] != '') {
    $aJob = new Job($uname);
    $aJob->get_job_by_id($_GET['cancel']);
    if ($aJob->username == $uname) {
        $aJob->cancel($uname);
        display_jobs("<p>Job $bJob->jobName was canceled</p>");
        
    } else {
        display_jobs("<p>Whoops! It doesn't appear that you are the owner of this job</p>");
    }
} else {
    display_jobs();
}
    

function display_jobs($message = "") {

require_once("../include/header.php");


$uname = ncu_getusername();
if (ncu_isstudent($uname)) {
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}

?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>

<div id="inner_container">

<? 

    $jobhistory = new Job($uname);
    $jobhistory->get_job_by_username();?>
    
    <p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> My Jobs</p>
    <? if (isset($message)) {
        echo $message;
    }
    if (isset($jobhistory->jobList) && count($jobhistory->jobList) >= 1) {
        foreach($jobhistory->jobList as $aJob ) {
            ?>
            <div id="jobbox">
        <label>Name:</label>
        <div style="font-size:medium"><? echo $aJob['jobname'] ?></div>
        <br/>
        <br/>
        <label>ID:</label>
        <div><? echo $aJob['jobid'] ?></div>
        <label>Status:</label>
        <div><? echo $aJob['status'] ?></div>
        <br/>
        <br/>
        <label>Type:</label>
        <div><? echo $aJob['jobtype'] ?></div>
        <label>Due:</label>
        <div><? echo $aJob['duedate'] ?></div>
        <br/>
        <br/>
        <a href="myjobhistory.php?view=<? echo $aJob['jobid'] ?>"><img src="image/view.png">View</a>
    <div></div>
    <a href="myjobhistory.php?cancel=<? echo $aJob['jobid'] ?>"><img src="image/cancel-icon.png">Cancel</a>
    </div>

    <?
        }
    }
    echo "</div><br/><br/>";
    include_once("../include/footer.php");
    
    
    
}?>