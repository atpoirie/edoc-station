<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
</HEAD>
<?php

session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/copyclass.php";
require_once "./include/perfectclass.php";
require_once "./include/pressclass.php";
require_once "./include/spiralclass.php";
require_once "./include/tapeclass.php";
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

$openJobs = new Job($uname);
$openJobs->get_open_jobs();
 if (isset($message)) {
            echo $message;
        }
        if (isset($openJobs->jobList) && count($openJobs->jobList) >= 1) {
            foreach ($openJobs->jobList as $jobs) {
                ?>
                    <div id="jobbox">
                    <label>Name:</label>
                    <div><a href="admin.php?type=job&function=view&job=<? echo $jobs['jobid'] ?>">
                        <? echo $jobs['jobname'] ?></a></div>
                    <br/>

                    <label>ID:</label>
                    <div><? echo $jobs['jobid'] ?></div>
                    
                    <label>Status:</label>
                    <div><? echo $jobs['status'] ?></div>
                    <br/>

                    <label>Type:</label>
                    <div><? echo $jobs['jobtype'] ?></div>
                    <label>Due:</label>
                    <div><? echo $jobs['duedate'] ?></div>
                    <br/>
                    <label>Requestor:</label>
                    <div><? echo ncu_getdisplayname($jobs['username']); ?></div>
                    <? if ($jobs['confidential'] == 'Yes') { ?><label></label>
                    <div style="display:block; float:right" ><img src="image/confidential-icon.jpg"></div> <? } ?>
                    <br/>
                    <br/>
                    <a style="margin-right:20px;" href="admin.php?type=job&function=completed&job=<?echo $jobs['jobid']?>"><img src="image/complete.png">Complete</a>
                    
                    <a style="margin-right:20px;" href="admin.php?type=job&function=edit&job=<? echo $jobs['jobid'] ?>"><img src="image/view.png">Edit</a>
                    
                    <a href="admin.php?type=job&function=cancel&job=<? echo $jobs['jobid'] ?>"><img src="image/cancel-icon.png">Cancel</a>
                </div>

                <?
            }
        } else {
            ?> 
<div id="jobbox">
    <h3>There are no open jobs</h3>
</div>
<?
        } ?>
