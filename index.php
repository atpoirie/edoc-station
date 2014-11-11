<?php
session_start();
////require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/copyclass.php";
require_once "./include/pressclass.php";
require_once "./include/perfectclass.php";
require_once "./include/spiralclass.php";
//require_once "./include/tapeclass.php";

$pagetitle = "Online Copy Center";
$quicklinks = true;
ncu_forcesecure();
ncu_forceauth();



$uname = ncu_getusername();

if (ncu_isstudent($uname)) {
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}


//For handling job submissions
if (isset($_POST['newjob']) && isset($_SESSION[$_POST['newjob']]) && (time() - $_SESSION[$_POST['newjob']]) < (60 * 60)) {
    
    $sessionid = $_SESSION[$_POST['newjob']];
    $aJob = new $_POST['newjob']($uname);  //The class name is included in $_POST['newjob']
    $aJob->un_serialize($_COOKIE[$_POST['newjob']]);  //A cookie with the class name of what we are doing holds a serialized copy of the job information
    if (isset($aJob->update) && $aJob->update == '1') { //This is an update to an existing job
        syslog(LOG_INFO, "UPDATING $aJob->jobId");
        $aJob->calculate_cost();
        $aJob->update_job();
        $action = "updated";
        
    } else { //This is a new job
        $aJob->calculate_cost();
        $aJob->flag = "Open";
        $aJob->store_job();
        $aJob->email_job();
        $action = "submitted";
    }
    $_SESSION[$_POST['newjob']] = '0';  //Clear out session information incase user clicks refresh
    setcookie($_POST['newjob'], "", time());
}

//Clear all cookies out and start fresh
if (isset($_COOKIE['Copy'])) {
    setcookie("Copy", "", time()-3600);
}
if (isset($_COOKIE['Press'])) {
    setcookie("Press", "", time()-3600);
}
if (isset($_COOKIE['Spiral'])) {
    setcookie("Spiral", "", time()-3600);
}
if (isset($_COOKIE['Perfect'])){
    setcookie("Perfect", "", time()-3600);
}
$cJob = new Job($uname);
if ($cJob->isAdmin) {
    header('Location: /unet/copycenter/admin.php');
}
include_once("include/header.php");
?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>


<div id="inner_container">
    <br/>
    <br/>
        <?php echo (isset($aJob->jobName) ? "<h2>$aJob->jobName was $action succesfully</h2>" : "<h2>Welcome to the Online Copy Center</h2>") ?>
    <div style="padding-left: 10px; float:left; width: 240px;">
        <a style="font-size:20px;" href="copy.php"><img src="image/copier-icon.png"> Copy</a>

        <br>
        <br>
        <a style="font-size:20px;" href="press.php"><img src="image/press-icon2.jpg"><div style="padding-left:32px; display:inline"></div>Press Bind</a>
        <br>
        <br>
        <a style="font-size:20px;" href="spiral.php"><img src="image/spiral-icon.jpg"> Spiral Bind</a>

        <br/>
        <br/>
        <a style="font-size:20px;" href="tape.php"><img src="image/tape_icon.jpg"> Tape Bind</a>
    </div>
    <div style="float:right; width:260px;">
        <a href="myjobhistory.php" style="font-size:20px;"><img src="image/printer-icon.png"/>  My Jobs</a>
        <br/>
        
            <br/>
        <ul>
            <li>Jobs submitted after hours will not be processed until the following business day.</li>
            <br/>
            

            <li>4 hour minimum time required for all copy jobs</li>
<br/>


            <li>24 hour minimum time required for all binding jobs</li>

            <div style="display:block; height:10px;"></div>
            To contact us:
            <ul>
                <li style="padding-left: 20px;padding-bottom: 0px;"><a href="/email/765/field_email">Email Us</a></li>
                <li style="padding-left: 20px;">Call: 612-343-4443</li>
            </ul>
        </ul>
  
    </div>
</div>
<?
include_once("../include/footer.php");
?>
