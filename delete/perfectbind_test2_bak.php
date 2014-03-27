<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/perfectclass.php";

$pagetitle = "Online Copy Center";
$quicklinks = true;
ncu_forcesecure();
ncu_forceauth();

require_once("../include/header.php");


$uname = ncu_getusername();
if (ncu_isstudent($uname)) {
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}

$_SESSION['Perfect'] = time();
$aJob = new Perfect($uname);
$aJob->getEarliest();

if(isset($_POST['clear']) && $_POST['clear'] == 'reset') {
    if (isset($_COOKIE['Perfect'])) {
        setcookie('Perfect', '');
    }
} elseif (isset($_COOKIE['Perfect'])) {
    $aJob->un_serialize($_COOKIE['Perfect']);
}

?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></SCRIPT>
<script type="text/javascript" src="script/jobfeatures.js"></script>
<link rel="stylesheet" type="text/css" href="css/anytime.css" />
<SCRIPT type="text/javascript" src="script/anytime.js"></SCRIPT>



<SCRIPT language="javascript">
    function document_size() {
        sizeRadio = document.getElementsByName("documentsize");
        template = document.getElementById("templatesize");
        var docSize;
        for (var i=0; i<sizeRadio.length; i++) {
            if(sizeRadio[i].checked)
                docSize=sizeRadio[i].value;
        }

        if (docSize == 'Small') {
            template.href = "template/Small.pub";
            template.innerHTML = "Small";
        } else if (docSize == 'Medium') {
            template.href = "template/Medium.pub";
            template.innerHTML = "Medium";
        } else if (docSize == 'Large') {
            template.href = "template/Large.pub";
            template.innerHTML = "Large";
        }
    }

    $(document).ready(function(){
        $("#perfectForm").validate();
    });
    
    function addSeparator() {
        ele = document.getElementById("pageseparator");
        ele.style.display = "block";
    }

</script>


<div id="inner_container">
    <p style="font-size:12px;"><a href="/unet/copycenter/landing.php">Home</a> >> Press Bind</p>
    <div><form method="post" action="perfectbind_test2.php"><button id="clear" type="submit" value="reset" name="clear">Clear</button></form>
        <form name="spiral" id="aForm" method="post" action="perfectbind_verify2.php" enctype="multipart/form-data">
            <input type="hidden" name="submitdate" value="<?php echo date("Ymd G:i:00"); ?>" >
            <h4>Job Information</h4>
            <label>Phone Number</label>
            <input type="text" name="phone" size="25" class="input required" minlength="4"
                   <? echo (isset($aJob->phone) ? 'value="'.$aJob->phone.'"' : "")?> />
            <br/>
            <br/>
            <label>Job Name</label>
            <input type="text" name="jobname" SIZE=25 id="jobname" class="input required" minlength="2"
                   <? echo (isset($aJob->jobName) ? 'value="'.$aJob->jobName.'"' : "") ?> />
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
    echo '<option value="' . $aDepartment . '" ';
    if (isset($aJob->departmentCharge) && $aJob->departmentCharge == $aDepartment) {
        echo 'selected = "selected"';
    } elseif (isset($aJob->departmentChargeDefault) && $aJob->departmentChargeDefault == $aDepartment) {
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
                <?php echo (isset($aJob->pageCount) ? 'Value="' . $aJob->pageCount . '"' : 'Value=""') ?>/>

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
            <h4>Document Options</h4>
            <label>Document size</label>
            <span id="optional">
                Small:<input name="documentsize" style="width:25px;" type="radio" value="Small" id="documentsize" onchange="document_size()"/>  
                Medium:<input name="documentsize" style="width:25px;" type="radio" value="Medium" id="documentsize" onchange="document_size()" checked/>  
                Large:<input name="documentsize" style="width:25px;" type="radio" value="Large" id="documentsize" onchange="document_size()"/>
            </span>
            <br>
            <br>
            <label>Paper Type</label>
            <span id="optional">
                Regular:<input name="papertype" style="width:40px;" type="radio" checked/> Perforated:<input name="papertype" style="width:40px;" type="radio"/>
            
            <br>
            <br>           
           
            
            
            
            <span id="seperator"></span>
            <h4>Cover Options</h4>
            <label>Cover in color ink</label>
            <input type="checkbox" name="coverink"/>
            <br>
            <br>
            <label>Cover Template</label>
            <a id="templatesize" href="tempalte/Medium.pub">Medium</a>
            <br>
            <br>
            <label>Upload cover</label>
            <input type="file" name="cover" size="25" class="required"/>
            <br>
            <label></label>Please only publisher or word documents.
            <br/>
            <br/>

            
            <span id="seperator"></span>
            <h4>Section separators</h4>
            <label>Enable Seperators</label>
            <input type="checkbox" value="seperatorenable"/>
            <br>
            <label>Paper Type</label>
            <select name="seperatortype">
                <option value="type">Separator type</option>
            </select>
            <br>
            <label>Paper Color</label>
            <select name="seperatorcolor">
                <option value="color">Separator color</option>
            </select>
            <br>
            <label>Separator 1</label>
            <span id="optional">Place after page #:</span>
            <input type="text" name="seperator1page" class="number" maxlength="4" style="width:40px;" />
            <span id="optional">Print on?</span>
            <input type="checkbox" name="seperator1print" />
            <br>
            <label>Separator 2</label>
            <span id="optional">Place after page #:</span>
            <input type="text" name="separator1page" class="number" maxlength="4" style="width:40px;" />
                        <span id="optional">Print on?</span>
            <input type="checkbox" name="separator2print" value="Separator 2 print on" />
            <br>
            <label>Separator 3</label>
            <span id="optional">Place after page #:</span>
            <input type="text" name="separator1page" class="number" maxlength="4" style="width:40px;" />
                        <span id="optional">Print on?</span>
            <input type="checkbox" name="separator3print" />

            
            
            
            
            <span id="seperator"></span>


            <br>
            <textarea rows="5" cols="40" name="comments">Additional comments</textarea>

            <button value="Submit" style="margin-top:40px; margin-right:10px;">Submit</button>
            <br>
            <br>
            <span></span>
        </FORM>
