<?php
session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/copyclass.php";
require_once "./include/perfectclass.php";
require_once "./include/pressclass.php";
require_once "./include/spiralclass.php";
require_once "./include/tapeclass.php";


$pagetitle = "Online Copy Center";
$quicklinks = true;
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
//Check if the GET variable is set with a "type" of function we should
//work on
//If the type is job then we assume the following functions apply
//     Complete
//     Cancel
//     print job ticket
//     view job
//     edit job
//     set printed by
//     get printed by
//   These are defined the GET variable "function"
if (isset($_GET['type']) && $_GET['type'] == 'job') {
    
    $aJob = new Job($uname);
    $aJob->get_job_by_id($_GET['job']);
    //Do things related to a job here
    switch ($_GET['function']) {
        case 'completed':  //Mark complete
            //If the printedby and stored information has been set, we've been here before
            if (isset($_POST['printedby']) && $_POST['printedby'] != '' && isset($_POST['stored']) && $_POST['stored'] != ''){
                $aJob->printedBy = $_POST['printedby'];
                $aJob->stored = $_POST['stored'];
                $aJob->printed();
            }
            if ($aJob->printedBy != '' && $aJob->stored != '') {  // We want to force people to mark printedby and stored before completing.
                 $bJob = new $aJob->jobType($uname);
                $bJob->get_job_by_id($_GET['job']);
                $bJob->email_job("Job Completed:");
                $aJob->complete();
            } else {
                //The job hasn't been marked with a printed by yet, force that now.
                include_once('../include/header.php');
            
            ?>
                <SCRIPT type="text/javascript" src="../include/jquery.js"></script>
                <SCRIPT type="text/javascript" src="../include/jquery.validate.js"></script>
                <link rel="stylesheet" type="text/css" href="css/copycenter.css"/>
                <div id="inner_container">
                    <div>
                        <br/><br/>
                        <?//We have to tweak this next line a bit, as we want the logic to flow back into the completed function
                        //  since that's what the user originally asked for
                        ?>
                        <form name="printedby" method="post" action="admin.php?type=job&function=completed&job=<?echo $aJob->jobId ?>">
                            
                            <label>Printed By:</label>
                            <input type="text" name="printedby" class="input required">
                            <label>Stored:</label>
                            <input type="text" name="stored" class="input required">
                            <input type="image" name="nothing" value="size" src="image/complete.png" style="height:30px; width:30px;"/>
                        </form></div>
                </div>
            <?
                die();
                break;
            }
                
            break;
        case 'cancel':  //Cancel the job
            $aJob->cancel($uname);
            break;
        case 'printticket': //Print the job ticket
            $bJob = new $aJob->jobType($uname);
            $bJob->get_job_by_id($_GET['job']);
            echo "<!DOCTYPE html><head>\n";
            echo '<meta charset="utf-8">';
            echo '<meta http-equiv="X-UA-Compatible">';
            echo '<script language="javascript">function printpage() { window.print(); window.close(); }</script>';
            echo '</head><body onload="printpage()">';
            $bJob->view_job();
            echo $bJob->view;
            die();
            break;
        case 'view':  //View the job
            $bJob = new $aJob->jobType($uname);
            $bJob->get_job_by_id($_GET['job']);
            $bJob->view_job();
            include_once("include/header.php");
            ?>
            <div id="inner_container">
                <br/><br/>
                <a href="/unet/copycenter/admin.php"><img src="./image/back-icon.png">Back</a>
                <span style="margin-left:50px"></span>
                <a href="/unet/copycenter/admin.php?type=job&function=printticket&job=<? echo $_GET['job'] ?>" target="_blank"><img src="./image/printer-icon.png">Print Ticket</a>
                <span style="margin-left:50px"></span>
                <a href="/unet/copycenter/admin.php?type=job&function=getprintedby&job=<? echo $_GET['job'] ?>"><img src="./image/closed-icon.png">Mark Printed</a>
                <br/><br/>
                <?
                echo $bJob->view;
                echo "</div>";
                die();
            break;
        case 'edit':  //Open the job for editing
            $bJob = new $aJob->jobType($uname);
            $bJob->get_job_by_id($_GET['job']);
            $bJob->update = "1";
            $time = 60 * 60 * 24 * 2 + time();
            $bJob->serialize();
            setcookie($bJob->jobType, $bJob->serial, $time, '/unet/copycenter/');
            header("Location: /unet/copycenter/".strtolower($bJob->jobType).".php");
            break;
        case 'setprintedby':  //Stores the user and location of the printed job but does not mark it complete
            if (isset($_POST['printedby']) && $_POST['printedby'] != '' && isset($_POST['stored']) && $_POST['stored'] != '') {
                $aJob->printedBy = $_POST['printedby'];
                $aJob->stored = $_POST['stored'];
                $aJob->printed();
                break;
            } else {
                include_once('../include/header.php');
            
            ?>
                <SCRIPT type="text/javascript" src="../include/jquery.js"></script>
                <SCRIPT type="text/javascript" src="../include/jquery.validate.js"></script>
                <link rel="stylesheet" type="text/css" href="css/copycenter.css"/>
                <div id="inner_container">
                    <div>
                        <br/><br/>
                        <form name="printedby" method="post" action="admin.php?type=job&function=setprintedby&job=<?echo $aJob->jobId ?>">
                            <label>Printed By:</label>
                            <input type="text" name="printedby" class="input required">
                            <label>Stored:</label>
                            <input type="text" name="stored" class="input required">
                            <input type="image" name="nothing" value="size" src="image/complete.png" style="height:30px; width:30px;"/>
                        </form></div>
                </div>
                <?
                die();
                break;
            }
        case 'getprintedby':  //Displays the form upon the user pressing the Printed button asking for a name and storage location
            include_once('../include/header.php');
            
            ?>
                <SCRIPT type="text/javascript" src="../include/jquery.js"></script>
                <SCRIPT type="text/javascript" src="../include/jquery.validate.js"></script>
                <link rel="stylesheet" type="text/css" href="css/copycenter.css"/>
                <div id="inner_container">
                    <div>
                        <br/><br/>
                        <form name="printedby" method="post" action="admin.php?type=job&function=setprintedby&job=<?echo $_GET['job'] ?>">
                            <label>Printed By:</label>
                            <input type="text" name="printedby" class="input required">
                            <label>Stored:</label>
                            <input type="text" name="stored" class="input required">
                            <input type="image" name="nothing" value="size" src="image/complete.png" style="height:30px; width:30px;"/>
                        </form></div>
                </div>
            <?
            die();
            break;
    }
} elseif (isset($_GET['type']) && $_GET['type'] == 'paper') {
    //Do paper related tasks here
    $aJob = new Job($uname);
    switch($_GET['function']){
        case 'sizeadd':
            $aJob->add_paper_size($_POST['size']);
            break;
        case 'sizedel':
            $aJob->delete_paper_size($_POST['size']);
            break;
        case 'typeadd':
            $aJob->add_paper_type($_POST['type']);
            break;
        case 'typedel':
            $aJob->delete_paper_type($_POST['type']);
            break;
        case 'coloradd':
            $aJob->add_paper_color($_POST['color']);
            break;
        case 'colordel':
            $aJob->delete_paper_color($_POST['color']);
            break;
        case 'combinationadd':
            $aJob->add_paper_combination($_POST['size'], $_POST['type'], $_POST['color']);
            break;
        case 'combinationdel':
            $aJob->delete_paper_combination($_POST['id']);
            break;
    }
} elseif (isset($_GET['type']) && $_GET['type'] == 'department') {
    //Do department related tasks here'
    $aJob = new Job($uname);
    switch ($_GET['function']) {
        case 'departmentadd':
            $aJob->add_department($_POST['name']);
            break;
        case 'departmentdel':
            $aJob->delete_department($_GET['name']);
            break;
    }
} elseif (isset($_GET['type']) && $_GET['type'] == 'report') {
    switch ($_GET['function']) {
        case 'view':
            echo "<!DOCTYPE html><head>\n";
            echo '<meta charset="utf-8">';
            echo '<meta http-equiv="X-UA-Compatible">';
            echo '<script language="javascript">function printpage() { window.print(); window.close(); }</script>';
            echo '</head><body onload="printpage()" style="width:700px;">';
            include("report.php");
            die();
            break;
    }
} elseif (isset($_GET['type']) && $_GET['type'] == 'user') {
    $aJob = new Job($uname);
    //We had a request to add or delete an administrator
    switch($_GET['function']) {
        case 'add':
            $aJob->add_admin($_POST['name']);
            break;
        case 'delete':
            $aJob->delete_admin($_GET['name']);
            break;
    }
}


include_once("include/header.php");

?>
<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>
<link rel="stylesheet" type="text/css" href="./css/jquery-ui-1.9.2.custom.css"/>
<script type="text/javascript" src="./script/jquery-ui-1.9.2.custom.js"></script>
<script>
    $(function() {
        $( "#tabs" ).tabs();
    });
</script>
<br/>
<br/>


    <?

        display_tabs();


function display_tabs() {
    ?>
<div id="tabs">
    <ul>
        <li><a href="openjobs.php">Open</a></li>
        <li><a href="completedjobs.php">Completed</a></li>
        <li><a href="stats.php">Statistics</a></li>
        <li><a href="paper.php">Paper</a></li>
        <li><a href="department.php">Departments</a></li>
        <li><a href="user.php">Users</a></li>
        <li><a href="#tabs-7">Reports</a></li>
        <li><a href="#tabs-8">Create Job</a></li>

    </ul>
        <div id="inner_container">
    
            <div id="tabs-7">
                <form method="GET" action="admin.php">
                    <label>Select A Month</label>
                    <select name="month">
                        <? for ($i=1; $i < 13; $i++) {
                            $mon = date("F", mktime(0, 0, 0, $i+1, 0, 0, 0));
                            echo '<option value="'.$i.'">'.$mon.'</option>';
                        }
                        ?>
                    </select>
                    <input type="hidden" name="type" value="report"/>
                    <input type="hidden" name="function" value="view"/>
                    <input type="image" name="nothing" value="something" src="image/complete.png" style="height:20px; width:20px;"/>
                </form>
            </div>

    <div id="tabs-8">
        <a style="font-size:20px;" href="copy.php"><img src="image/copier-icon.png"> Copy</a>
        <br>
        <br>
        <a style="font-size:20px;" href="press.php"><img src="image/press-icon2.jpg"><div style="padding-left:32px; display:inline"></div>Press Bind</a>
        <br>
        <br>
        <a style="font-size:20px;" href="spiral.php"><img src="image/spiral-icon.jpg"> Spiral Bind</a>
        <br>
        <br>
        <a style="font-size:20px;" href="perfect.php"><img src="image/book-icon.png"> Perfect Bind</a>
        <br/>
        <br/>
        <a style="font-size:20px;" href="tape.php"><img src="image/tape_icon.jpg"> Tape Bind</a>



</div>
</div>
</div>
<?
include_once("../include/footer.php");
}

function display_job($id) {
    global $uname;
    $aJob = new Job($uname);
    $aJob->get_job_type_by_id($id);
    $bJob = new $aJob->jobType($uname);
    $bJob->get_job_by_id($id);
    $bJob->view_job();
    ?>

<div id="inner_container">
    <a href="/unet/copycenter/admin.php"><img src="./image/back-icon.png">Back</a>
    <span style="margin-left:50px"></span>
    <a href="/unet/copycenter/admin.php?id=<? echo $id ?>&print=1" target="_blank"><img src="./image/printer-icon.png">Print Ticket</a>
    <span style="margin-left:50px"></span>
    <a href="/unet/copycenter/admin.php?id=<? echo $id ?>&printed=1"><img src="./image/closed-icon.png">Mark Printed</a>
    <br/><br/>
    <?
    echo $bJob->view;
    echo "</div></div>";
    
}

function print_job($id) {
    global $uname;
    $aJob = new Job($uname);
    $aJob->get_job_by_id($id);
    $bJob = new $aJob->jobType($uname);
    $bJob->get_job_by_id($id);
    echo "<!DOCTYPE html><head>\n";
    echo '<meta charset="utf-8">';
    echo '<meta http-equiv="X-UA-Compatible">';
    echo '<script language="javascript">function printpage() { window.print(); window.close(); }</script>';
    echo '</head><body onload="printpage()">';
    $bJob->view_job();
    echo $bJob->view;
    die();
    
}

function mark_printed($id) {
    global $uname;
    $aJob = new Job($uname);
    ?>
    <div id="inner_container">
    <div><form name="printed" method="post" action="admin.php">
            <label>Printed By:</label>
            <input type="text" name="who">
            <label>Stored:</label> 
            <input type="text" name="where">
            <input type="hidden" name="job" value="<?echo $id?>">
            <input type="submit" name="notify" value="Mark printed and send email">
            <input type="submit" name="notify" value="Mark printed only">
            <button name="submit" type="submit" value="submit"><img src="./image/complete.png"></button>
        </form>
    </div>
    </div>
    <?
}


