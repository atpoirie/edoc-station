<?php
session_start();

require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/copyclass.php";

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
$_SESSION['Copy'] = time();
$aJob = new Copy($uname);
$aJob->get_earliest();
$edit = FALSE;

if(isset($_POST['clear']) && $_POST['clear'] == 'reset') {
    if (isset($_COOKIE['Copy'])) {
        setcookie('Copy', '', 0, '/unet/copycenter/');
    }
} elseif (isset($_COOKIE['Copy'])) {
    $aJob->un_serialize($_COOKIE['Copy']);
    $edit = TRUE;
}

include_once("../include/header.php");
?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>

<SCRIPT type="text/javascript" src="../include/jquery.js"></script>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></script>
<script type="text/javascript" src="script/jobfeatures.js"></script>
<link rel="stylesheet" type="text/css" href="css/anytime.css"/>
<script type="text/javascript" src="./script/anytime.js"></script>


<SCRIPT language="javascript">
    <?
    include_once("./script/paperselection.php");
    ?>
    $(document).ready(function(){
        $("#aForm").validate();
    });

</script>


<div id="inner_container">
    
    <p style="font-size:12px;"><a href="<?echo ($aJob->isAdmin) ? 'admin.php">Admin' :'index.php">Home' ?></a> >> Copy
                                  <? echo $edit ? '>> Job: '.$aJob->jobId : ''?></p>
    <div><form method="post" action="copy.php"><button id="clear" type="submit" value="reset" name="clear">Clear</button></form>
        <form name="copy" id="aForm" method="post" action="copy_verify.php" enctype="multipart/form-data">
            <input type="hidden" name="submitdate" value="<?php echo date("Ymd G:i:00") ?>" />
            <h4>Job Information</h4>
          <label>Phone Number</label>
        <input type="text" name="phone" size="25" class="input required" minlength="4" maxlength="14"
               <? echo (isset($aJob->phone) ? 'value="'.$aJob->phone.'"' : "") ?> />
        <br/>
        <br/>
        <label>Job Name</label>
        <input type="text" name="jobname" SIZE=25 id="jobname" class="input required" minlength="2" maxlength="40"
               <? echo (isset($aJob->jobName) ? 'value="'.$aJob->jobName.'"' : "") ?>/>
        <br/>
        <br/>

        <label>Charge to</label>
        <select name="departmentcharge" id="departmentcharge" onchange="departmentCharge()" class="input required">
            <option value="">Select Department</option>
            <option value="Personal Copies"
                    <? echo (isset($aJob->departmentCharge) && $aJob->departmentCharge == 'Personal Copies' ? 'selected="selected"' : '')?>
                        >Personal Copies</option>
            <option value="Account"
                    <? echo (isset($aJob->departmentCharge) && $aJob->departmentCharge == 'Account' ? 'selected="selected"' : '')?>
                    >Account</option>
            <? 
            foreach ($aJob->departmentList as $aDepartment) {
                echo '<option value="' . $aDepartment .'" ';
                if (isset($aJob->departmentCharge) && $aJob->departmentCharge == $aDepartment) {
                    echo 'selected = "selected"';
                }
                elseif (isset($aJob->departmentChargeDefault) && $aJob->departmentChargeDefault == $aDepartment) {
                    echo 'selected = "selected"';
                }
                echo '>' . $aDepartment . '</option>';
                echo "\n";
            }
            ?>
            
        </select>
        <br/>
        <div id="account" style="display: none">
        <br/>
        <label style="padding-left: 15px">Account #</label>
        <input name="account" id="accountnumber" type="text" size="25" maxlength="12" class="input digit"
               <? echo (isset($aJob->account) ? 'value="'.$aJob->account.'"' : "") ?> />
        <br/>
        </div>
        <br/>
            <label>Select File</label>
            <?php if (isset($aJob->filename)) {
                echo "<span>$aJob->filename</span>";
            } else { ?>
            <input type="file" name="upload" size=25 class="required" style="width: 305px"/>
            <? } ?>
            <br>
            <br>
            <label></label><label>Number of pages</label>
            
            <input type="text" name="pagecount" SIZE=25 id="pages" class="required number" maxlength="4" style="width:40px; margin-left:-50px;"
                   <?php echo (isset($aJob->pageCount) ? 'Value="' . $aJob->pageCount . '"' : '') ?>/>

            <br>
            <br>
            <label>Quantity</label>
            <input type="text" name="quantity" size=16 class="required number" maxlength="5"
                   <?php echo (isset($aJob->quantity) ? 'Value="' . $aJob->quantity . '"' : 'Value=""') ?>/>
            <br>
            <br>

            <label>Date Due</label>
            <input type="text" name="date1x" id="date1x" size="20" class="required"
                   <?php echo (isset($aJob->dueDate) ? 'Value="' . $aJob->dueDate . '"' : 'Value=""') ?>/>
            <script type="text/javascript">
                AnyTime.picker( "date1x",
                { 	format: "%m/%d/%Z %l:00%p", 
                    firstDOW: 1,
                    earliest: new Date(<? echo $aJob->earliest; ?>) } );
            </script>
            <br/>
            <br/>
            

            
            
            <span id="seperator"></span>
            <h4>Delivery Mode</h4>
            <label>Pick up</label>
            <input type="radio" name="transport" id="transport" value="Pick-up" onClick="deliverTo()"
                   <? echo (isset($aJob->transport) && $aJob->transport == 'Pick-up' ? 'checked' : '') ?>/>
            <span id="hiddenoptional" 
                  <? echo (isset($aJob->departmentCharge) && $aJob->departmentCharge == 'Personal Copies' ? '' : 'style="display: none"') ?> >
                      <font color="red">*Pay at pickup*</font></span>

            <br>
            <label>Deliver</label>
            <input type="radio" name="transport" id="transport" value="Deliver" onClick="deliverTo()"
                   <? echo (isset($aJob->transport) && $aJob->transport == 'Deliver' ? 'checked' : '') ?>
                   <? echo (isset($aJob->departmentCharge) && $aJob->departmentCharge == 'Personal Copies' ? 'Disabled' : '') ?>/>
            <br>
            <div id="deliverto" 
                 <? echo (isset($aJob->transport) && $aJob->transport == 'Pick-up' ? 'style="display:none"' : '') ?> >
                <label style="padding-left:15px;">Deliver to</label>
                <select name="departmentdeliver" id="departmentdeliver">
                    <option value="">Select Department</option>
                    <? 
                    foreach ($aJob->departmentList as $aDepartment) {
                        echo '<option value="' . $aDepartment .'" ';
                        if (isset($aJob->departmentDeliver) && $aJob->departmentDeliver == $aDepartment) {
                            echo 'selected = "selected"';
                        }
                        elseif (isset($aJob->departmentDeliverDefault) && $aJob->departmentDeliverDefault == $aDepartment) {
                            echo 'selected = "selected"';
                        }
                        echo '>' . $aDepartment . '</option>';
                        echo "\n";
                    }
                    ?>
                </select>
                <br/>
            </div>

            <label>Confidential/Test</label>
            <input type="checkbox" name="confidential" id="confidential"
                   <? echo (isset($aJob->confidential) && $aJob->confidential == 'Yes' ? 'checked' : '') ?> />
            <br/>
            <br/>





            <span id="seperator"></span>
            
            <h4>Paper Selection</h4>
            <label>Paper Size</label>
            <select name="papersize" onchange="set_papertype()" class="required">
                <?php
                $first = true;
                $res = & $aJob->sql->query("SELECT papersize_index, papersize FROM unet_mc_papersize");
                while ($row = $res->fetchRow()) {
                    if ($first) {
                        $sizeindex = $row['papersize_index'];
                        $first = false;
                    }
                    echo '<option value="' . $row['papersize_index'] . '"';
                    if(isset($aJob->paperSize) && $aJob->paperSize == $row['papersize']) {
                        echo 'selected="selected"';
                        $sizeindex = $row['papersize_index'];
                    }
                    echo '>' . $row['papersize'] . '</option>';
                }
                ?>
            </select>
            <br>
            <label>Paper Type</label>

            <select name="papertype" onchange="set_papercolor()" class="required">
                <?php
                $first = true;
                $res = & $aJob->sql->query("SELECT DISTINCT paper_type FROM unet_mc_paper WHERE paper_size = '$sizeindex'");
      
                while ($row = $res->fetchRow()) {
                    if ($first) {
                        $typeindex = $row['paper_type'];
                        $first = false;
                    }
                    $subres = & $aJob->sql->query("SELECT * FROM unet_mc_papertype WHERE papertype_index = '" . $row['paper_type'] . "'");
                    $subrow = $subres->fetchRow();
                    echo '<option value="' . $subrow['papertype_index'] . '" ';
                    if (isset($aJob->paperType) && $aJob->paperType == $subrow['papertype']){
                        echo 'selected="selected"';
                        $typeindex = $row['paper_type'];
                    }
                    echo '>' . $subrow['papertype'] . '</option>';
                }
                ?>      
            </select>
            <br>

            <label>Paper Color</label>
            <select name="papercolor" class="required">
                <?php
                $res =  $aJob->sql->query("SELECT DISTINCT paper_color FROM unet_mc_paper WHERE paper_size = '$sizeindex' AND paper_type = '$typeindex'");
                while ($row = $res->fetchRow()) {
                    $subres = & $aJob->sql->query("SELECT * FROM unet_mc_papercolor WHERE papercolor_index = '" . $row['paper_color'] . "'");
                    $subrow = $subres->fetchRow();
                    echo '<option value="' . $subrow['papercolor_index'] . '" ';
                    if (isset($aJob->paperColor) && $aJob->paperColor == $subrow['papercolor']) {
                        echo 'selected="selected"';
                    }
                    echo '>' . $subrow['papercolor'] . '</option>';
                }
                ?></select>
            <br>
            <br>
            
            
            
            
            
            
            <span id="seperator"></span>
            <h4>Document Options</h4>
            <label>Double Sided</label>
            <input type="checkbox" name="duplex"
                   <? echo (isset($aJob->duplex) && $aJob->duplex == 'Yes' ? 'checked' : "") ?>/>
            <br>

            <label>Print in color</label>
            <input type="checkbox" name="color"
                   <? echo (isset($aJob->colorInk) && $aJob->colorInk == 'Yes' ? 'checked' : "") ?>/>

            <br>
            <br>
            <label>Folding</label>
            <select name="folding" onChange="set_folding();">
                <option value="No Folding" <? echo (isset($aJob->folding) && $aJob->folding == 'No Folding' ? 'selected' : "") ?>   >No Folding</option>
                <option value="C Fold" <? echo (isset($aJob->folding) && $aJob->folding == 'C Fold' ? 'selected' : "") ?>           >"C" Fold</option>
                <option value="Z Fold" <? echo (isset($aJob->folding) && $aJob->folding == 'Z Fold' ? 'selected' : "") ?>           >"Z" Fold</option>
                <option value="Gate Fold" <? echo (isset($aJob->folding) && $aJob->folding == 'Gate Fold' ? 'selected' : "") ?>    >Gate Fold</option>
                <option value="Half Fold" <? echo (isset($aJob->folding) && $aJob->folding == 'Half Fold' ? 'selected' : "") ?>    >Half Fold</option>
            </select>
            <br>
            <br>


            <label>Stapling</label>
            <select name="staple" onchange="set_staple();"/>
            <option value="No Staple" 
                <? echo (isset($aJob->stapling) && $aJob->stapling == 'No Staple' ? 'selected' : "") ?>
                    >No Staple</option>
            <option value="Left Corner" 
                <? echo (isset($aJob->stapling) && $aJob->stapling == 'Left Corner' ? 'selected' : "") ?>  
                    >Left Corner</option>
            <option value="Right Corner" 
                <? echo (isset($aJob->stapling) && $aJob->stapling == 'Right Corner' ? 'selected' : "") ?> 
                    >Right Corner</option>
            <option value="Saddle Stitch" 
                <? echo (isset($aJob->stapling) && $aJob->stapling == 'Saddle Stitch' ? 'selected' : "") ?>
                    >Saddle Stitch</option>
            </select>
            <br>
            <br>

            <label>Cutting</label>
            <select name="cut" onchange="set_cut();"/>
      <option value="No Cutting"
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'No Cutting' ? 'selected' : "") ?>
              >No Cutting</option>
      <option value="Cut into 1/4"
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut into 1/4' ? 'selected' : "") ?>
              >Cut into 1/4</option>
      <option value="Cut into 1/2" 
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut into 1/2' ? 'selected' : "") ?>
              >Cut into 1/2</option>
      <option value="Cut into 1/3" 
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut into 1/3' ? 'selected' : "") ?>
              >Cut into 1/3</option>
      <option value="Cut into 1/6" 
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut into 1/6' ? 'selected' : "") ?>
              >Cut into 1/6</option>
      <option value="Cut into 1/8"
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut into 1/8' ? 'selected' : "") ?>
              >Cut into 1/8</option>
      <option value="Cut into 1/10" 
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut into 1/10' ? 'selected' : "") ?>
              >Cut into 1/10</option>
      <option value="Cut to bleed" 
              <? echo (isset($aJob->cutting) && $aJob->cutting == 'Cut to bleed' ? 'selected' : "") ?>
              >Cut to bleed</option>
            </select>
            <br/>
            <br>
            <label>Laminate</label>
            <input type="checkbox" name="laminate" onchange="set_laminate();"
                   <? echo (isset($aJob->laminate) && $aJob->laminate == 'Yes' ? 'checked' : "") ?>/>

            <br>
            <label>UnCollate</label>
            <input type="checkbox" name="collate"/>

            <br>
            <label>Hole Punch</label>
            <input type="checkbox" name="punch" onchange="set_hole_punch();"
                   <? echo (isset($aJob->holePunch) && $aJob->holePunch == 'Yes' ? 'checked' : "") ?>/>

            <br>
            <br/>
            
            
            
            
            
            <span id="seperator"></span>
            <br/>
            <textarea rows="5" cols="40" name="comments"><? echo (isset($aJob->comments) && $aJob->comments != "" ? $aJob->comments : "Additional comments") ?></textarea>

            <button type="submit" value="submit" name="copyForm" style="margin-top:40px; margin-right:10px;">Submit</button>
            <br>
            <br>
            <span></span>
        </FORM>

    </div>
</div>
<?php
require_once("../include/footer.php");
