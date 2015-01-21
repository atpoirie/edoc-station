<?php

session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/perfectclass.php";

$pagetitle = "Online Copy Center";
$quicklinks = true;
//ncu_forcesecure();
//ncu_forceauth();



$uname = ncu_getusername();
if (ncu_isstudent($uname)) {
    include_once("include/header.php");
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}
if (!isset($_SESSION['Perfect']) || (time() - $_SESSION['Perfect']) > (60*60)) {
    header('Location: /unet/copycenter/perfect.php?session=invalid');
} else
    $_SESSION['Perfect'] = time();

$aJob = new Perfect($uname);


$aJob->jobType = "Perfect";

if (isset($_POST['submitdate'])) {
    $aJob->submitDate = $_POST['submitdate'];    
} else {
    header('Location: /unet/copycenter/perfect.php?submitdate=blank');
}
if (isset($_POST['phone'])) {
    $aJob->phone = $_POST['phone'];
} else {
    header('Location: /unet/copycenter/perfect.php?phone=blank');
}
if (isset($_POST['jobname'])) {
    $aJob->jobName = $_POST['jobname'];
} else {
    header('Location: /unet/copycenter/perfect.php?jobname=blank');
}
if (isset($_POST['departmentcharge'])) {
    $aJob->departmentCharge = $_POST['departmentcharge'];
} else {
    header('Location: /unet/copycenter/perfect.php?departmentcharge=blank');
}
if (isset($_POST['account'])) {
    $aJob->account = $_POST['account'];
}

if (isset($_FILES['upload']['name'])) {
    $tmp_name = $_FILES["upload"]["tmp_name"];
    $aJob->filename = $_FILES["upload"]["name"];
    $aJob->randname = md5(rand() * time());
    move_uploaded_file($tmp_name, "$aJob->uploadDir/$aJob->randname");
} 
elseif ($_COOKIE['Perfect']) {
    $temp = unserialize($_COOKIE['Perfect']);
    $aJob->filename = $temp['filename'];
    $aJob->randname = $temp['randname'];
    if (isset($temp['update']))
        $aJob->update = $temp['update'];
    if (isset($temp['jobId']))
        $aJob->jobId = $temp['jobId'];
} else{
    header('Location: /unet/copycenter/perfect.php?file=blank');
}
if (isset($_POST['pagecount'])) {
    $aJob->pageCount = $_POST['pagecount'];
} else {
    header('Locatin: /unet/copycenter/perfect.php?pagecount=blank');
}
if (isset($_POST['quantity'])) {
    $aJob->quantity = $_POST['quantity'];
} else {
    header('Location: /unet/copycenter/perfect.php?quantity=blank');
}
if (isset($_POST['date1x'])) {
    $aJob->dueDate = $_POST['date1x'];
} else {
    header('Location: /unet/copycenter/perfect.php?date=blank');
}
if (isset($_POST['transport'])) {
    $aJob->transport = $_POST['transport'];
} else {
    header('Location: /unet/copycenter/perfect.php?transport=blank');
}
if (isset($_POST['departmentdeliver'])) {
    $aJob->departmentDeliver = $_POST['departmentdeliver'];
}
if (isset($_POST['confidential']) && $_POST['confidential'] == 'on') {
    $aJob->confidential = 'Yes';
} else {
    $aJob->confidential = 'No';
}
if (isset($_POST['documentsize'])) {
    $aJob->docSize = $_POST['documentsize'];
} else {
    header('Location: /unet/copycenter/perfect.php?documentsize=blank');
}
if (isset($_POST['papertype'])) {
    $aJob->paperType = $_POST['papertype'];
} else {
    header('Location: /unet/copycenter/perfect.php?papertype=blank');
}
if (isset($_POST['coverink']) && $_POST['coverink'] == 'on') {
    $aJob->coverColorInk = 'Yes';
} else
    $aJob->coverColorInk = 'No';
if (isset($_FILES['cover']['name'])) {
    $tmp_name = $_FILES["cover"]["tmp_name"];
    $aJob->coverName = $_FILES["cover"]["name"];
    $aJob->coverRandName = md5(rand() * time());
    move_uploaded_file($tmp_name, "$aJob->uploadDir/$aJob->coverRandName");
} 
elseif ($_COOKIE['Perfect']) {
    $temp = unserialize($_COOKIE['Perfect']);
    $aJob->coverName = $temp['coverName'];
    $aJob->randname = $temp['coverRandName'];
} else{
    header('Location: /unet/copycenter/perfect.php?file=blank');
}

if (isset($_POST['comments']) && $_POST['comments'] != 'Additional comments') {
    $aJob->comments = $_POST['comments'];
} else {
    $aJob->comments = "";
}
$aJob->serialize();
$time = 60 * 60 * 24 * 2 + time();
setcookie('Perfect', $aJob->serial, $time);
include_once("include/header.php");
?>
<link rel="stylesheet" type="text/css" href="css/copycenter.css">

<div id="inner_container">

    <p style="font-size:12px;"><a href="/unet/copycenter/newjob.php">Home</a> >> <a href="/unet/copycenter/perfect.php">Perfect Bind</a> >> Perfect Bind Confirmation</p>
    <div>

            <h4>Job Information</h4>
          <label>Phone Number</label>
        <span id="verify"><?php echo $aJob->phone ?></span>
        <br/>
        <br/>
        <label>Job Name</label>
        <span id="verify"><?php echo $aJob->jobName ?></span>
        <br/>
        <br/>
        <?php echo ($aJob->departmentCharge != '' ? '<label>Charge to</label><span id="verify"> '.$aJob->departmentCharge.'</span><br/><br/>' : '') ?>

        <?php if ($aJob->departmentCharge == 'Account') { ?>
        <div id="account">
        <label style="padding-left: 15px">Account #</label>
        <span id="verify"><?php echo $aJob->account  ?></span>
        <br/>
        <br/>
        </div>
        <? } ?>
        
            <label>File</label>
            <span id="verify"><?php echo $aJob->filename ?></span>

            <br>
            <br>
            <label>Number of pages</label>
            <span id="verify"><?php echo $aJob->pageCount ?></span>

            <br>
            <br>
            <label>Quantity</label>
            <span id="verify"><?php echo $aJob->quantity ?></span>
            <br>
            <br>

            <label>Date Due</label>
            <span id="verify"><?php echo $aJob->dueDate ?></span>
            <br/>
            <br/>
            
            <span id="seperator"></span>
            <h4>Delivery Mode</h4>
            <?php if($aJob->transport == 'Pick-up') {
                echo "<label>Pick-up</label><span id=\"verify\">I will pick-up from Copy and Mailing Services</span>";

            } else {
                echo "<label>Deliver</label><span id=\"verify\"> To " . $aJob->departmentDeliver . " on rounds</span>";

            } ?>
            <br/>
            <br/>
            <label>Confidential</label>
            <span id="verify">
                <?php echo $aJob->confidential ?>
            </span>
            <br/>
            <br/>




            <span id="seperator"></span>
            <h4>Document options</h4>

            <label>Document Size</label>
            <span id="verify"><? echo $aJob->docSize ?></span>
            <br/>
            <br/>

            <label>Paper Type</label>
            <span id="verify"><? echo $aJob->paperType ?></span>
            <br/>
            <br/>
           




            <span id="seperator"></span>
            <h4>Cover options</h4>
            <label>Cover in color ink</label>
            <span id="verify"><? echo $aJob->coverColorInk ?> </span>

            <br/>
            <br/>

            <label>Cover file</label>
            <span id="verify"> <? echo $aJob->coverName ?></span> 
            <br/>
            <br/>

           
           <span id="seperator"></span>
            <br/>
            <?php if ($aJob->comments != "") {
                echo "<label>Comments</label>";
                echo '<span id="verify">' . $aJob->comments . '</span>';
            } ?>

            <form method="post" action="newjob.php">
                <input type="hidden" name="newjob" value="Perfect"/>
            <button type="submit" value="Press" name="order">Place Order</button>
            <br/>
        </FORM>
        
    </div>
    <br/><br/><br/><br/>
    <br/><br/><br/><br/>
</div>
<?php

require_once("../include/footer.php");



