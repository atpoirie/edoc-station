<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "include/jobclass.php";
require_once "include/copyclass.php";

$pagetitle = "Online Copy Center";
$quicklinks = true;
ncu_forcesecure();
ncu_forceauth();



$uname = ncu_getusername();
if (ncu_isstudent($uname)) {
    include_once("../include/header.php");
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}
if (!isset($_SESSION['Copy']) || (time() - $_SESSION['Copy']) > (60*60)) {
    
   header('Location: /unet/copycenter/copy.php?session=invalid');

} else
    $_SESSION['Copy'] = time();

$aJob = new Copy($uname);


$aJob->jobType = "Copy";
if (isset($_POST['submitdate'])) {
    $aJob->submitDate = $_POST['submitdate'];    
} else {
    
   header('Location: /unet/copycenter/copy.php?submitdate=blank');

}
if (isset($_POST['phone'])) {
    $aJob->phone = $_POST['phone'];
} else {
    
   header('Location: /unet/copycenter/copy.php?phone=blank');

}
if (isset($_POST['jobname'])) {
    $aJob->jobName = $_POST['jobname'];
} else {
    
   header('Location: /unet/copycenter/copy.php?jobname=blank');

}
if (isset($_POST['departmentcharge'])) {
    $aJob->departmentCharge = $_POST['departmentcharge'];
} else {
    
   header('Location: /unet/copycenter/copy.php?departmentcharge=blank');

}
if (isset($_POST['account'])) {
    $aJob->account = $_POST['account'];
}

if (isset($_FILES['upload']['name'])) {
    $tmp_name = $_FILES["upload"]["tmp_name"];
    $aJob->filename = $_FILES["upload"]["name"];
    $aJob->randname = md5(rand() * time());
    move_uploaded_file($tmp_name, $aJob->uploadDir."/".$aJob->randname);
} 
elseif ($_COOKIE['Copy']) {
    $temp = unserialize($_COOKIE['Copy']);
    $aJob->filename = $temp['filename'];
    $aJob->randname = $temp['randname'];
    if (isset($temp['update']))
        $aJob->update = $temp['update'];
    if (isset($temp['jobId']))
        $aJob->jobId = $temp['jobId'];
} else{
   header('Location: /unet/copycenter/copy.php');
}
if (isset($_POST['pagecount'])) {
    $aJob->pageCount = $_POST['pagecount'];
} else {  
   header('Location: /unet/copycenter/copy.php?pagecount=blank');
}
if (isset($_POST['quantity'])) {
    $aJob->quantity = $_POST['quantity'];
} else {
    
   header('Location: /unet/copycenter/copy.php?quantity=blank');

}
if (isset($_POST['date1x'])) {
    $aJob->dueDate = $_POST['date1x'];
} else {
    
   header('Location: /unet/copycenter/copy.php?date=blank');

}
if (isset($_POST['transport'])) {
    $aJob->transport = $_POST['transport'];
} else {
    
   header('Location: /unet/copycenter/copy.php?transport=blank');

}
if (isset($_POST['departmentdeliver'])) {
    $aJob->departmentDeliver = $_POST['departmentdeliver'];
}
if (isset($_POST['confidential']) && $_POST['confidential'] == 'on') {
    $aJob->confidential = 'Yes';
} else {
    $aJob->confidential = 'No';
}
if (isset($_POST['papersize'])) {
    $aJob->get_paper_size_by_id($_POST['papersize']);
} else {
    
   header('Location: /unet/copycenter/copy.php?papersize=blank');

}
if (isset($_POST['papertype'])) {
    $aJob->get_paper_type_by_id($_POST['papertype']);
} else {
    
   header('Location: /unet/copycenter/copy.php?papertype=blank');

}
if (isset($_POST['papercolor'])) {
    $aJob->get_paper_color_by_id($_POST['papercolor']);
} else {
    
   header('Location: /unet/copycenter/copy.php?papercolor=blank');

}
if (isset($_POST['duplex']) && $_POST['duplex'] == 'on') {
    $aJob->duplex = 'Yes';
} else {
    $aJob->duplex = 'No';
}
if (isset($_POST['color']) && $_POST['color'] == 'on') {
    $aJob->colorInk = 'Yes';
} else
    $aJob->colorInk = 'No';
if (isset($_POST['folding'])) {
    $aJob->folding = $_POST['folding'];
} else {
    $aJob->folding = "No Folding";
}
if (isset($_POST['staple'])) {
    $aJob->stapling = $_POST['staple'];
} else {
    $aJob->stapling = "No Staple";
}
if (isset($_POST['cut'])) {
    $aJob->cutting = $_POST['cut'];
} else {
    $aJob->cutting = "No Cutting";
}
if (isset($_POST['laminate']) && $_POST['laminate'] == 'on') {
    $aJob->laminate = 'Yes';
} else
    $aJob->laminate = 'No';
if (isset($_POST['collate']) && $_POST['collate'] == 'on') {
    $aJob->collate = 'Yes';
} else {
    $aJob->collate = 'No';
}
if (isset($_POST['punch']) && $_POST['punch'] == 'on') {
    $aJob->holePunch = 'Yes';
} else {
    $aJob->holePunch = 'No';
}
if (isset($_POST['comments']) && $_POST['comments'] != 'Additional comments') {
    $aJob->comments = $_POST['comments'];
}
$aJob->status = "In the queue";
$aJob->serialize();
$time = 60 * 60 * 24 * 2 + time();
setcookie('Copy', $aJob->serial, $time);
include_once("../include/header.php");
?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css">

<div id="inner_container">

    <p style="font-size:12px;"><a href="/unet/copycenter/<?echo ($aJob->isAdmin) ? 'admin.php">Admin' :'<a href="index.php">Home' ?></a> >> <a href="/unet/copycenter/copy.php">Copy</a> >> Copy Confirmation</p>
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
            
            <h4>Paper Selection</h4>
            <label>Paper Size</label>
            <span id="verify"><?php echo $aJob->paperSize ?></span>
            
            <br/>
            <br/>
            <label>Paper Type</label>
            <span id="verify"><?php echo $aJob->paperType ?></span>
            
            <br/>
            <br/>
            <label>Paper Color</label>
            <span id="verify"><?php echo $aJob->paperColor ?></span>
            <br/>
            <br/>
            
            
            
            
            
            
            <span id="seperator"></span>
            <h4>Document Options</h4>
            <label>Double Sided</label>
                <span id="verify">
                <?php echo $aJob->duplex ?>
                </span>

            <br/>
            <br/>

            <label>Print in color</label>
            <span id="verify">
                <?php echo $aJob->colorInk ?>
            </span>
            <br/>
            <br/>
            <label>Folding</label>
            <span id="verify">
                <?php echo $aJob->folding ?>
            </span>
            <br/>
            <br/>
            <label>Stapling</label>
            <span id="verify">
                <?php echo $aJob->stapling ?>
            </span>
            <br/>
            <br/>

            <label>Cutting</label>
            <span id="verify">
                <?php echo $aJob->cutting ?>
            </span>
            <br/>
            <br/>
            <label>Laminate</label>
            <span id="verify">
                <?php echo $aJob->laminate ?>
            </span>
            <br/>
            <br/>
            <label>UnCollate</label>
            <span id="verify">
                <?php echo $aJob->collate ?>
            </span>
            <br/>
            <br/>
            <label>Hole Punch</label>
            <span id="verify">
                <?php echo $aJob->holePunch ?>
            </span>
            <br/>
            <br/>
            <span id="seperator"></span>
            <br/>
            <?php if ($aJob->comments != "") {
                echo "<label>Comments</label>";
                echo '<span id="verify">' . $aJob->comments . '</span>';
            } ?>

            <form method="post" action="index.php">
            <input type="hidden" name="newjob" value="Copy"/>
            <button type="submit" value="Copy" name="order"/>Place Order</button>
            <br/>
        </FORM>
        
    </div>
    <br/><br/><br/><br/>
    <br/><br/><br/><br/>
</div>
<?php

require_once("../include/footer.php");
