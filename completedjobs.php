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
ncu_forcesecure();
ncu_forceauth();
$uname = ncu_getusername();
$aJob = new Job($uname);

if (!$aJob->isAdmin) {
    include_once("include/header.php");
    echo "You are not authorized to view this page";
    require_once("../include/footer.php");
    return;
}

$jobhistory = new Job($uname);
        $jobhistory->get_closed_jobs();

        if (isset($message)) {
            echo $message;
        }
        if (isset($jobhistory->jobList) && count($jobhistory->jobList) >= 1) {
            foreach ($jobhistory->jobList as $jobs) {
                ?>
                        <div id="jobbox">
                    <label>Name:</label>
                    <div><a href="admin.php?type=job&function=view&job=<? echo $jobs['jobid'] ?>">
                        <? echo $jobs['jobname'] ?></a></div>
                    <br/>

                    <label>ID:</label>
                    <div><? echo $jobs['jobid'] ?></div>
                    <?if (strpos($jobs['status'], 'Cancel') === FALSE ) { ?>
                    <label>Completed By:</label>
                    <? } else { ?>
                    <label>Canceled By:</label>
                    <? } ?>
                    <div><? echo $jobs['printedby'] ?></div>
                    <br/>

                    <label>Type:</label>
                    <div><? echo $jobs['jobtype'] ?></div>
                    <label>Stored:</label>
                    <div><? echo $jobs['stored'] ?></div>
                    <br/>
                    <label>Requestor:</label>
                    <div><? echo ncu_getdisplayname($jobs['username']); ?></div>
                    <? 
                    if(strpos($jobs['status'], 'Cancel') === FALSE ){?>
                    <label>Completed On:</label>
                    <? } else { ?>
                    <label>Canceled On:</label>
                    <? } ?>
                    <div><? echo $jobs['completedate'] ?></div>
                    <br/>

                    <br/>
                    <? if ($jobs['status'] == 'Printed') { ?>
                    <a href="admin.php?id=<? echo $jobs['jobid'] ?>&email=1"><img src="image/email-icon.png">Send Email</a>
                    <?} else { ?>
                    <a href="admin.php?type=job&function=edit&job=<? echo $jobs['jobid'] ?>"><img src="image/view.png">Edit</a>
                    <? } ?>
                    <div></div>
                    <?if(strpos($jobs['status'], 'Cancel') === FALSE){?>
                    <a href="admin.php?type=job&function=cancel&job=<? echo $jobs['jobid'] ?>"><img src="image/cancel-icon.png">Cancel</a>
                    <? } ?>
                </div>

                <?
            }
        } ?>