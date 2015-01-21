<?php

session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/spiralclass.php";

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
if (!isset($_SESSION['Spiral']) || (time() - $_SESSION['Spiral']) > (60*60)) {
 
     header('Location: /unet/copycenter/spiral.php?session=invalid');
 
} else
    $_SESSION['Spiral'] = time();

$aJob = new Spiral($uname);


$aJob->jobType = "Spiral";

if (isset($_POST['submitdate'])) {
    $aJob->submitDate = $_POST['submitdate'];    
} else {
 
     header('Location: /unet/copycenter/spiral.php?submitdate=blank');
 
}
if (isset($_POST['phone'])) {
    $aJob->phone = $_POST['phone'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?phone=blank');
 
}
if (isset($_POST['jobname'])) {
    $aJob->jobName = $_POST['jobname'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?jobname=blank');
 
}
if (isset($_POST['departmentcharge'])) {
    $aJob->departmentCharge = $_POST['departmentcharge'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?departmentcharge=blank');
 
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
elseif ($_COOKIE['Spiral']) {
    $temp = unserialize($_COOKIE['Spiral']);
    $aJob->filename = $temp['filename'];
    $aJob->randname = $temp['randname'];
    if (isset($temp['update']))
        $aJob->update = $temp['update'];
    if (isset($temp['jobId']))
        $aJob->jobId = $temp['jobId'];
} else{
 
     header('Location: /unet/copycenter/spiral.php?file=blank');
 
}
if (isset($_POST['pagecount'])) {
    $aJob->pageCount = $_POST['pagecount'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?pagecount=blank');
 
}
if (isset($_POST['quantity'])) {
    $aJob->quantity = $_POST['quantity'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?quantity=blank');
 
}
if (isset($_POST['date1x'])) {
    $aJob->dueDate = $_POST['date1x'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?date=blank');
 
}
if (isset($_POST['transport'])) {
    $aJob->transport = $_POST['transport'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?transport=blank');
 
}
if (isset($_POST['departmentdeliver'])) {
    $aJob->departmentDeliver = $_POST['departmentdeliver'];
}
if (isset($_POST['confidential']) && $_POST['confidential'] == 'on') {
    $aJob->confidential = 'Yes';
} else {
    $aJob->confidential = 'No';
}
if (isset($_POST['bindside'])) {
    $aJob->bindSide = $_POST['bindside'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?bindside=blank');
 
}
if (isset($_POST['bindcolor'])) {
    $aJob->bindColor = $_POST['bindcolor'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?bindcolor=blank');
 
}
if (isset($_POST['duplex']) && $_POST['duplex'] == 'on') {
    $aJob->duplex = 'Yes';
} else {
    $aJob->duplex = 'No';
}
if (isset($_POST['cut']) && $_POST['cut'] == 'on'){
    $aJob->cutting = 'Yes';
} else {
    $aJob->cutting = 'No';
}
if (isset($_POST['color']) && $_POST['color'] == 'on') {
    $aJob->colorInk = 'Yes';
} else
    $aJob->colorInk = 'No';

if (isset($_POST['papersize'])) {
    $aJob->paperSize = $_POST['papersize'];
}
if (isset($_POST['papertype'])) {
    $aJob->paperType = $_POST['papertype'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?papertype=blank');
 
}
if (isset($_POST['papercolor'])) {
    $aJob->paperColor = $_POST['papercolor'];
} else {
 
     header('Location: /unet/copycenter/spiral.php?papercolor=blank');
 
}
if (isset($_POST['frontplastic']) && $_POST['frontplastic'] == 'on') {
    $aJob->frontPlastic = 'Yes';
} else {
    $aJob->frontPlastic = 'No';
}
if (isset($_POST['frontpresentation']) && $_POST['frontpresentation'] == 'on') {
    $aJob->frontPresentation = 'Yes';
} else {
    $aJob->frontPresentation = 'No';
}
if (isset($_POST['frontcolorink']) && $_POST['frontcolorink'] == 'on') {
    $aJob->frontColorInk = 'Yes';
} else {
    $aJob->frontColorInk = 'No';
}
if (isset($_POST['coverpapertype'])) {
    $aJob->frontPaperType = $_POST['coverpapertype'];
} //else {
 
 //    header('Location: /unet/copycenter/spiral.php?coverpapertype=blank');
 
//}
if (isset($_POST['coverpapercolor'])) {
    $aJob->frontPaperColor = $_POST['coverpapercolor'];
}// else {
 
//     header('Location: /unet/copycenter/spiral.php?coverpapercolor=blank');
 
//}
if (isset($_POST['backplastic']) && $_POST['backplastic'] == 'on') {
    $aJob->backPlastic = 'Yes';
} else {
    $aJob->backPlastic = 'No';
}
if (isset($_POST['backpresentation']) && $_POST['backpresentation'] == 'on') {
    $aJob->backPresentation = 'Yes';
} else {
    $aJob->backPresentation = 'No';
}
if(isset($_POST['backblankpage']) && $_POST['backblankpage'] == 'on') {
    $aJob->backBlankPage = 'Yes';
} else {
    $aJob->backBlankPage = 'No';
}
if (isset($_POST['backpapertype'])) {
    $aJob->backPaperType = $_POST['backpapertype'];
} //else {
 
//     header('Location: /unet/copycenter/spiral.php?backpapertype=blank');
 
//}
if (isset($_POST['backpapercolor'])) {
    $aJob->backPaperColor = $_POST['backpapercolor'];
}//else {
 
  //   header('Location: /unet/copycenter/spiral.php?backpapercolor=blank');
 
//}
if (isset($_POST['comments']) && $_POST['comments'] != 'Additional comments') {
    $aJob->comments = $_POST['comments'];
} else {
    $aJob->comments = "";
}
$aJob->status = "In the queue";
$aJob->serialize();
$time = 60 * 60 * 24 * 2 + time();
setcookie('Spiral', $aJob->serial, $time);
include_once("include/header.php");
?>
<link rel="stylesheet" type="text/css" href="css/copycenter.css">

<div id="inner_container">

    <p style="font-size:12px;"><a href="<?echo ($aJob->isAdmin) ? 'admin.php">Admin' :'index.php">Home' ?></a> >> <a href="spiral.php">Spiral Bind</a> >> Spiral Bind Confirmation</p>
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
            <br/>
            <label>Bind Side</label>
            <span id="verify"><? echo $aJob->bindSide ?> </span>
            <br/>
            <br/>
            <label>Bind Color</label>
            <span id="verify"><? echo $aJob->bindColor ?></span>
            <br/>
            <br/>



            <span id="seperator"></span>
            <h4>Document options</h4>

            <label>Double Sided</label>
            <span id="verify"><? echo $aJob->duplex ?></span>
            <br/>
            <br/>

            <label>Cut in half</label>
            <span id="verify"><? echo $aJob->cutting ?></span>
            <br/>
            <br/>
            <label>Printed in color</label>
            <span id="verify"><? echo $aJob->colorInk?> </span>

            <br/>
            <br/>
            <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
            <br>
            <span id="verify" style="margin-left:20px;"><? echo $aJob->paperType ?></span>
            <span id="verify" style="margin-right:180px; float:right;"><? echo $aJob->paperColor ?></span>
            <br/>
            <br/>




            <span id="seperator"></span>
            <h4>Front Cover options</h4>
            <label>Plastic cover</label>
            <span id="verify"><? echo $aJob->frontPlastic ?> </span>

            <br/>
            <br/>

            <label>Black presentation cover</label>
            <span id="verify"> <? echo $aJob->frontPresentation ?></span> 
                   
            <span id="optional">*cannot be printed on, additional charge</span>
            <br/>
            <br/>

            <label>Print in color</label>
            <span id="verify"><? echo $aJob->frontColorInk ?></span>

            <br/>
            <br/>
            
            <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
            <br/>
            <span id="verify" style="margin-left:20px"><? echo $aJob->frontPaperType ?></span>
            <span id="verify" style="margin-right:180px; float:right;"><? echo $aJob->frontPaperColor ?></span>
            <br/>
            <br/>




            <span id="seperator"></span>
            <h4>Back Cover options</h4>
            <label>Plastic cover</label>
            <span id="verify"><? echo $aJob->backPlastic ?></span>
            <br/>
            <br/>
            <label>Black presentation cover</label>
            <span id="verify"><? echo $aJob->backPresentation ?></span>
               
            <span id="optional">*cannot be printed on, additional charge</span>
            <br/>
            <br/>
            <label>Insert blank page</label>
            <span id="verify"><? echo $aJob->backBlankPage ?></span>
                  
            <br/>
            <br/>
            <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
            <br/>
            <span id="verify" style="margin-left:20px;"><? echo $aJob->backPaperType ?></span>
            <span id="verify" style="margin-right:180px; float:right;"><? echo $aJob->backPaperColor ?></span>
            <br/>
            <br/>
           <span id="seperator"></span>
            <br/>
            <?php if ($aJob->comments != "") {
                echo "<label>Comments</label>";
                echo '<span id="verify">' . $aJob->comments . '</span>';
            } ?>

            <form method="post" action="index.php">
                <input type="hidden" name="newjob" value="Spiral"/>
            <button type="submit" value="Spiral" name="order">Place Order</button>
            <br/>
        </FORM>
        
    </div>
    <br/><br/><br/><br/>
    <br/><br/><br/><br/>
</div>
<?php

require_once("../include/footer.php");



