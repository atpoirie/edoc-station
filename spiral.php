<?php
session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/spiralclass.php";

$pagetitle = "Online Copy Center";
$quicklinks = true;
//ncu_forcesecure();
//ncu_forceauth();

require_once("include/header.php");


$uname = ncu_getusername();
if (ncu_isstudent($uname)) {
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}

$_SESSION['Spiral'] = time();
$aJob = new Spiral($uname);
$aJob->getEarliest();
$edit=FALSE;

if (isset($_POST['clear']) && $_POST['clear'] == 'reset') {
    if (isset($_COOKIE['Spiral'])) {
        setcookie('Spiral', '');
    }
} elseif (isset($_COOKIE['Spiral'])) {
    $aJob->un_serialize($_COOKIE['Spiral']);
    $edit=TRUE;
}
?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></SCRIPT>
<script type="text/javascript" src="script/jobfeatures.js"></script>
<link rel="stylesheet" type="text/css" href="css/anytime.css" />
<SCRIPT type="text/javascript" src="script/anytime.js"></SCRIPT>


<script language="javascript">
    
    function bind_side() {
        var bindside = document.getElementById("bindside");
        
        var selected = bindside.options[bindside.selectedIndex].value;
        
        var cut = document.getElementById("cut");
        
        if (selected == '11 inch'){
            cut.checked = false;
            cut.disabled = true;   
        }
        if (selected == '8 1/2 inch') {
            cut.disabled = false;           
        }
            
    }
    function change_paper_type(typeid, colorid) {

        var typeElement = document.getElementById(typeid);
        var colorElement = document.getElementById(colorid);
        var typeSelected = typeElement.options[typeElement.selectedIndex].value;


        var papertype = new Array();
        var papercolor = new Array();
        var typecolor = new Array();

<?
$res = & $aJob->sql->query("SELECT * FROM unet_mc_papertype WHERE pressbind IS NOT NULL");
while ($row = $res->fetchRow()) {
    echo 'papertype[' . $row['papertype_index'] . '] = "' . $row['papertype'] . '";';
    echo "\n";
}
echo "\n";

$res = & $aJob->sql->query("SELECT * FROM unet_mc_papercolor");
while ($row = $res->fetchRow()) {
    echo 'papercolor[' . $row['papercolor_index'] . '] = "' . $row['papercolor'] . '";';
    echo "\n";
}
echo "\n";

/* ATP - I'm cheating here a bit, technically there might be a paper size that isn't 8 1/2 x 11 
 * but uses the same paper type and is in a color that isn't stocked for 8 1/2 x 11.  For instance
 * if 11x17 Cardstock maroon existed, we might have an issue.
 */
$res = & $aJob->sql->query("SELECT * FROM unet_mc_paper");
while ($row = $res->fetchRow()) {
    echo 'typecolor[' . $row['paper_index'] . '] = "' . $row['paper_type'] . ',' . $row['paper_color'] . '";';
    echo "\n";
}

echo "\n";
?>
        var pattern = new RegExp(typeSelected);
        var paper_index;
        for(var i=0; i < papertype.length; i++) {
            if (pattern.test(papertype[i]))
                paper_index = i;
        }
        var colors = new Array();
        var colorIndex;
        pattern = new RegExp("^"+paper_index+",");
        for(var j=0; j< typecolor.length; j++) {
            if ( pattern.test(typecolor[j])) {
                colorIndex = typecolor[j].split(",");
                colors.push(papercolor[colorIndex[1]]);
            }
        }
        colorElement.options.length = 0;
        for (var k=0; k < colors.length; k++) {
            var aColor = document.createElement("option");
            aColor.text = colors[k];
            aColor.value = colors[k];
            try {
                colorElement.add(aColor, colorElement.options[null]);
            } catch (e) {
                colorElement.add(aColor, null);
            }
        }
        
    }
    
    function front_presentation() {
        var frontPresentation = document.aForm.frontpresentation;
        var frontPaperType = document.aForm.coverpapertype;
        var frontPaperColor = document.aForm.coverpapercolor;
        var frontColorInk = document.aForm.frontcolorink;
        var none = document.createElement("option");
        none.text = "none";
        none.value = "none";
        var aVariable = document.createElement("option");
        aVariable.text = "none";
        aVariable.value = "none";
        if (frontPresentation.checked == true) {  
            
            try {
                frontPaperType.add(aVariable, frontPaperType.options[null]);
            } catch(e) {
                frontPaperType.add(aVariable, null);
            }
            try {
                frontPaperColor.add(none, frontPaperColor.options[null]);
            } catch(e) {
                frontPaperColor.add(none, null);
            }
            for(var i=0; i<frontPaperType.length; i++) {
                if(frontPaperType[i].value == "none")
                    frontPaperType[i].selected = true;
            }
            for(var i=0; i<frontPaperColor.length; i++) {
                if(frontPaperColor[i].value == "none")
                    frontPaperColor[i].selected = true;
            }
            frontPaperType.disabled = true;
            frontPaperColor.disabled = true;
            frontColorInk.disabled = true;
        } else {
            frontPaperType.disabled = false;
            frontPaperColor.disabled = false;
            frontColorInk.disabled = false;
            for (var i=0; i<frontPaperType.length; i++) {
                if(frontPaperType[i].value == "none")
                    frontPaperType.remove(i);
            }
            for (var i=0; i<frontPaperColor.length; i++) {
                if(frontPaperColor[i].value == "none")
                    frontPaperColor.remove(i);
            }
        }
    }
    function front_color_ink() {
        var frontPresentation = document.aForm.frontpresentation;
        var frontColorInk = document.aForm.frontcolorink;
        if(frontColorInk.checked == true) {
            frontPresentation.disabled = true;
        } else
            frontPresentation.disabled = false;
    }
    function back_presentation() {
        var backPresentation = document.aForm.backpresentation;
        var backPaperType = document.aForm.backpapertype;
        var backPaperColor = document.aForm.backpapercolor;
        var backBlankPage = document.aForm.backblankpage;
        var none = document.createElement("option");
        none.text = "none";
        none.value = "none";
        var aVariable = document.createElement("option");
        aVariable.text = "none";
        aVariable.value = "none";
        if (backPresentation.checked == true) {   
            backBlankPage.disabled = true;
            try {
                backPaperType.add(aVariable, backPaperType.options[null]);
            } catch(e) {
                backPaperType.add(aVariable, null);
            }
            try {
                backPaperColor.add(none, backPaperColor.options[null]);
            } catch(e) {
                backPaperColor.add(none, null);
            }
            for(var i=0; i<backPaperType.length; i++) {
                if(backPaperType[i].value == "none")
                    backPaperType[i].selected = true;
            }
            for(var i=0; i<backPaperColor.length; i++) {
                if(backPaperColor[i].value == "none")
                    backPaperColor[i].selected = true;
            }
            backPaperType.disabled = true;
            backPaperColor.disabled = true;
        } else {
            backBlankPage.disabled = false;
            backPaperType.disabled = false;
            backPaperColor.disabled = false;
            for (var i=0; i<backPaperType.length; i++) {
                if(backPaperType[i].value == "none")
                    backPaperType.remove(i);
            }
            for (var i=0; i<backPaperColor.length; i++) {
                if(backPaperColor[i].value == "none")
                    backPaperColor.remove(i);
            }
        }
    }
    
    function back_blank_page() {
        var backBlankPage = document.aForm.backblankpage;
        var backPresentation = document.aForm.backpresentation;
        var backPageOptions = document.getElementById('backpageoptions');
        var backPaperType = document.aForm.backpapertype;
        var backPaperColor = document.aForm.backpapercolor;
        var none1 = document.createElement("option");
        var none2 = document.createElement("option");
        none1.text = "none";
        none2.text = "none";
        none1.value = "none";
        none2.value = "none";
        
        if (backBlankPage.checked == true) {
            for (var i=0; i<backPaperType.length; i++) {
                if (backPaperType[i].value == "none")
                    backPaperType.remove(i);
            }
            for (var i=0; i<backPaperColor.length; i++) {
                if (backPaperColor[i].value == "none")
                    backPaperColor.remove(i);
            }
            backPresentation.disabled = true;
            backPageOptions.style.display = "block";
            
        } else {
            try { 
                backPaperType.add(none1, backPaperType.options[null]);
            } catch (e) {
                backPaperType.add(none1, null);
            }
            try {
                backPaperColor.add(none2, backPaperColor.options[null]);
            } catch (e) { 
                backPaperColor.add(none2, null);
            }
            for(var i=0; i<backPaperType.length; i++) {
                if(backPaperType[i].value == "none")
                    backPaperType[i].selected = true;
            }
            for (var i=0; i<backPaperColor.length; i++) {
                if(backPaperColor[i].value == "none")
                    backPaperColor[i].selected = true;
            }
            backPageOptions.style.display = "none";
            backPresentation.disabled = false;
        }
    }
    
    $(document).ready(function(){
        $("#aForm").validate();
    });

    
</script>

<div id="inner_container">
    <p style="font-size:12px;"><a href="<? echo ($aJob->isAdmin) ? 'admin.php">Admin' : 'index.php">Home' ?></a> >> Spiral Bind
                                  <? echo $edit ? '>> Job: '.$aJob->jobId : ''?></p>
                                  <div><form method="post" action="spiral.php"><button id="clear" type="submit" value="reset" name="clear">Clear</button></form>
            <form name="aForm" id="aForm" method="post" action="spiral_verify.php" enctype="multipart/form-data">
                <input type="hidden" name="submitdate" value="<?php echo date("Ymd G:i:00"); ?>" >
                <h4>Job Information</h4>
                <label>Phone Number</label>
                <input type="text" name="phone" size="25" class="input required" minlength="4" maxlength="14"
                       <? echo (isset($aJob->phone) ? 'value="' . $aJob->phone . '"' : "") ?> />
                <br/>
                <br/>
                <label>Job Name</label>
                <input type="text" name="jobname" SIZE=25 id="jobname" class="input required" minlength="2" maxlength="40"
                       <? echo (isset($aJob->jobName) ? 'value="' . $aJob->jobName . '"' : "") ?> />
                <br/>
                <br/>

                <label>Charge to</label>
                <select name="departmentcharge" id="departmentcharge" onchange="departmentCharge()" class="input required">
                    <option value="">Select Department</option>
                    <option value="Personal Copies"
                    <? echo (isset($aJob->departmentCharge) && $aJob->departmentCharge == 'Personal Copies' ? 'selected="selected"' : '') ?>
                            >Personal Copies</option>
                    <option value="Account"
                    <? echo (isset($aJob->departmentCharge) && $aJob->departmentCharge == 'Account' ? 'selected="selected"' : '') ?>
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
                           <? echo (isset($aJob->account) ? 'value="' . $aJob->account . '"' : "") ?> />
                    <br/>
                </div>
                <br/>
                <label>Select File</label>
                <?php
                if (isset($aJob->filename)) {
                    echo "<span>$aJob->filename</span>";
                } else {
                    ?>
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
                            echo '<option value="' . $aDepartment . '" ';
                            if (isset($aJob->departmentDeliver) && $aJob->departmentDeliver == $aDepartment) {
                                echo 'selected = "selected"';
                            } elseif (isset($aJob->departmentDeliverDefault) && $aJob->departmentDeliverDefault == $aDepartment) {
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
                <br>
                <label>Spiral Side</label>
                <select name="bindside" class="input required" id="bindside" onchange="bind_side();">
                    <option value="">Select Side</option>
                    <option value="8 1/2 inch" <? echo (isset($aJob->bindSide) && $aJob->bindSide == '8 1/2 inch' ? 'selected' : '') ?>>8 1/2 inch</option>
                    <option value="11 inch" <? echo (isset($aJob->bindSide) && $aJob->bindSide == '11 inch' ? 'selected' : '') ?>>11 inch</option>
                </select>
                <br/>
                <br/>
                <label>Spiral Color</label>
                <select name="bindcolor" class="input required" id="bindcolor">
                    <option value="">Choose color</option>
                    <option value="Navy" <? echo (isset($aJob->bindColor) && $aJob->bindColor == 'Navy' ? 'selected' : '') ?>>Navy</option>
                    <option value="White" <? echo (isset($aJob->bindColor) && $aJob->bindColor == 'White' ? 'selected' : '') ?>>White</option>
                    <option value="Black" <? echo (isset($aJob->bindColor) && $aJob->bindColor == 'Black' ? 'selected' : '') ?>>Black</option>
                    <option value="Clear" <? echo (isset($aJob->bindColor) && $aJob->bindColor == 'Clear' ? 'selected' : '') ?>>Clear</option>
                </select>
                <br/>
                <br/>



                <span id="seperator"></span>
                <h4>Document options</h4>

                <label>Double Sided</label>
                <input type="checkbox" name="duplex"
                       <? echo (isset($aJob->duplex) && $aJob->duplex == 'Yes' ? 'checked' : "") ?>/>
                <br>

                <label>Cut in half</label>
                <input type="checkbox" name="cut" id="cut"
                <?
                if ((isset($aJob->pageCount) && $aJob->pageCount > 250) || (isset($aJob->bindSide) && $aJob->bindSide == '11 inch')) {
                    echo 'disabled="disabled"';
                }
                ?>
                       />
                <br>
                <label>Printed in color</label>
                <input type="checkbox" name="color"
<? echo (isset($aJob->colorInk) && $aJob->colorInk == 'Yes' ? 'checked' : "") ?>/>

                <br>
                <br>
                <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
                <br>
                <select name="papertype" id="papertype" style="margin-left:20px;" onchange="change_paper_type('papertype', 'papercolor');">
                    <?
                    //More cheating.  They want Normal 28Lb. to be the default for document paper type and cardstock be the default for the other options
                    //This wasn't in the initial design so it's being hacked here.  I think by setting the type to Normal if a type hasn't been chosen will
                    //cause Normal to display correctly and have the correct colors listed.
                    if (isset($aJob->paperType))
                        $defaultType = $aJob->paperType;
                    else
                        $defaultType = "normal";
                    foreach ($aJob->defaultPaperType as $type) {
                        if (isset($defaultType) && $defaultType == $type) {
                            echo '<option value="' . $type . '" selected="selected">' . $type . '</option>';
                        } else {
                            echo '<option value="' . $type . '">' . $type . '</option>';
                        }
                    }
                    ?>
                </select>
                <select name="papercolor" id="papercolor" style="margin-left:95px;">
                    <?
                    if (isset($defaultType) && $defaultType != "") {
                        $aJob->getPaperColor($defaultType);
                    }
                    foreach ($aJob->defaultPaperColor as $color) {
                        if ($aJob->paperColor == $color) {
                            echo '<option value="' . $color . '" selected="selected">' . $color . '</option>';
                        } else {
                            echo '<option value="' . $color . '">' . $color . '</option>';
                        }
                    }
                    ?>
                </select>
                <br/>
                <br/>




                <span id="seperator"></span>
                <h4>Front Cover options</h4>
                <label>Plastic cover</label>
                <input type="checkbox" name="frontplastic"
                <?
                if (isset($aJob->frontPlastic)) {
                    if ($aJob->frontPlastic == "Yes") {
                        echo "checked";
                    }
                } else {
                    echo "checked";
                }
                ?> 
                       />
                <br>

                <label>Black presentation cover</label>
                <input type="checkbox" name="frontpresentation" id="frontpresentation" onchange="front_presentation()"
<? echo (isset($aJob->frontPresentation) && $aJob->frontPresentation == 'Yes' ? "checked" : "") ?> 
<? echo (isset($aJob->frontColorInk) && $aJob->frontColorInk == 'Yes' ? 'disabled="disabled"' : "") ?> />
                <span id="optional">*cannot be printed on, additional charge</span>
                <br>

                <label>Print in color</label>
                <input type="checkbox" name="frontcolorink" onchange="front_color_ink()"
<? echo (isset($aJob->frontColorInk) && $aJob->frontColorInk == "Yes" ? "checked" : "") ?>
<? echo (isset($aJob->frontPresentation) && $aJob->frontPresentation == 'Yes' ? 'disabled="disabled"' : "") ?>/>
                <br>

                <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
                <br>
                <select name="coverpapertype" id="coverpapertype" style="margin-left:20px;" onchange="change_paper_type('coverpapertype', 'coverpapercolor')"
                            <? echo ( isset($aJob->frontPresentation) && $aJob->frontPresentation == "Yes" ? 'disabled="disabled"' : '') ?>>
                            <?
                            echo ( isset($aJob->frontPresentation) && $aJob->frontPresentation == "Yes" ? '<option value="none" selected="selected">none</option>' : '');
                            foreach ($aJob->defaultPaperType as $type) {
                                if (isset($aJob->frontPaperType) && $aJob->frontPaperType == $type) {
                                    echo '<option value="' . $type . '" selected="selected">' . $type . '</option>';
                                } else {
                                    echo '<option value="' . $type . '">' . $type . '</option>';
                                }
                            }
                            ?>
                </select>
                <select name="coverpapercolor" id="coverpapercolor" style="margin-left:95px;"
                            <? echo ( isset($aJob->frontPresentation) && $aJob->frontPresentation == "Yes" ? 'disabled="disabled"' : '') ?>>
                            <?
                            if (isset($aJob->frontPaperType) && $aJob->frontPaperType != "") {
                                $aJob->getPaperColor($aJob->frontPaperType);
                            }
                            if (isset($aJob->frontPresentation) && $aJob->frontPresentation == "Yes") {
                                echo '<option value="none" selected="selected">none</option>';
                            }
                       
                            foreach ($aJob->defaultPaperColor as $color) {
                                if ($aJob->frontPaperColor == $color) {
                                    echo '<option value="' . $color . '" selected="selected">' . $color . '</option>';
                                } else {
                                    echo '<option value="' . $color . '">' . $color . '</option>';
                                }
                            }
                            
                            ?>
                </select>
                <br>
                <br>




                <span id="seperator"></span>
                <h4>Back Cover options</h4>
                <label>Plastic cover</label>
                <input type="checkbox" name="backplastic" id="back" 
                <?
                if (isset($aJob->backPlastic)) {
                    if ($aJob->backPlastic == "Yes") {
                        echo "checked";
                    }
                } else {
                    echo "checked";
                }
                ?> 
                       />

                <br/>
                <label>Black presentation cover</label>
                <input type="checkbox" name="backpresentation" id="backpresentation" onchange="back_presentation()"
                <? echo (isset($aJob->backPresentation) && $aJob->backPresentation == "Yes" ? "checked" : "");
                echo (isset($aJob->backBlankPage) && $aJob->backBlankPage == "Yes" ? 'disabled="disabled"' : ""); ?>/>
                <span id="optional">*cannot be printed on, additional charge</span>

                <br/>
                <label>Insert blank page</label>
                <input type="checkbox" name="backblankpage" onchange="back_blank_page()"
<? echo (isset($aJob->backBlankPage) && $aJob->backBlankPage == "Yes" ? "checked" : "") ?>
<? echo (isset($aJob->backPresentation) && $aJob->backPresentation == "Yes" ? 'disabled="disabled"' : '') ?>/>
                <br/>
                <div id="backpageoptions" 
                        <? echo (isset($aJob->backBlankPage) && $aJob->backBlankPage == 'Yes' ? '' : 'style="display: none"') ?>>
                    <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
                    <br/>
                    <select name="backpapertype" id="backpapertype" style="margin-left:20px;" onchange="change_paper_type('backpapertype', 'backpapercolor')"
                            <? echo (isset($aJob->backPresentation) && $aJob->backPresentation == 'Yes' ? 'disabled="disabled"' : '') ?>>
                        <?
                        foreach ($aJob->defaultPaperType as $type) {
                            if (isset($aJob->backPaperTypee) && $aJob->backPaperType == $type) {
                                echo '<option value="' . $type . '" selected="selected>' . $type . '</option>';
                            } else {
                                echo '<option value="' . $type . '">' . $type . '</option>';
                            }
                        }
                        if (isset($aJob->backBlankPage) && $aJob->backBlankPage == "No" || !isset($aJob->backBlankPage)) {
                            echo '<option value="none" selected="selected">none</option>';
                        }
                        ?>
                    </select>
                    <select name="backpapercolor" id="backpapercolor" style="margin-left:95px;"
                            <? echo (isset($aJob->backPresentation) && $aJob->backPresentation == 'Yes' ? 'disabled="disabled"' : '') ?>>
                        <?
                        if (isset($aJob->backPaperType) && $aJob->backPaperType != "") {
                            $aJob->getPaperColor($aJob->backPaperType);
                        }
                        foreach ($aJob->defaultPaperColor as $color) {
                            if ($aJob->backPaperColor == $color) {
                                echo '<option value="' . $color . '" selected="selected">' . $color . '</option>';
                            } else {
                                echo '<option value="' . $color . '">' . $color . '</option>';
                            }
                        }
                        if (isset($aJob->backBlankPage) && $aJob->backBlankPage == "No" || !isset($aJob->backBlankPage)) {
                            echo '<option value="none" selected="selected">none</option>';
                        }
                        ?>
                    </select>
                    <br/>

                </div>
                <br/>


                <span id="seperator"></span>
                <br>
                <textarea rows="5" cols="40" name="comments" onClick="select_all();"><? echo (isset($aJob->comments) && $aJob->comments != "" ? $aJob->comments : "Additional comments") ?></textarea>
                <button type="submit" value="Submit" name="pressForm" style="margin-top:40px; margin-right:10px;">Submit</button>
            </form>
            <br>
            <br>
            </div>
            <DIV ID="testdiv1" STYLE="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></DIV>
</div>
<?php
require_once("../include/footer.php");

