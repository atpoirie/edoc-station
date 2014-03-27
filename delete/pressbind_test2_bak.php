<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/pressclass.php";

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

$_SESSION['Press'] = time();
$aJob = new Press($uname);
$aJob->getEarliest();

if(isset($_POST['clear']) && $_POST['clear'] == 'reset') {
    if (isset($_COOKIE['Press'])) {
        setcookie('Press', '');
    }
} elseif (isset($_COOKIE['Press'])) {
    $aJob->un_serialize($_COOKIE['Press']);
}

?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css"/>

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></SCRIPT>
<script type="text/javascript" src="script/jobfeatures.js"></script>
<link rel="stylesheet" type="text/css" href="css/anytime.css" />
<SCRIPT type="text/javascript" src="script/anytime.js"></SCRIPT>


<script type="text/javascript">
    function page_count() {
        var pages = document.getElementById("pages");
        var cut = document.getElementById("cut");
        var bindside = document.getElementById("bindside");
        var bindcolor = document.getElementById("bindcolor");
        bindside.disabled = true;
        bindcolor.disabled = true;
        bindside.options.length = 0;
        bindcolor.options.length = 0;
        var selectIndex;
        var selectSide = document.createElement("option");
        selectSide.text = "Select Side";
        selectSide.value = "";
        var shortSide = document.createElement("option");
        shortSide.text = "8 1/2 inch";
        shortSide.value = "8 1/2 inch";
        var longSide = document.createElement("option");
        longSide.text = "11 inch";
        longSide.value = "11 inch";
         
        var colorBlack = document.createElement("option");
        colorBlack.text = "Black";
        colorBlack.value = "Black";
        
        var colorWhite = document.createElement("option");
        colorWhite.text = "White";
        colorWhite.value = "White";
        
        if (pages.value < 250) {
            if ( cut.checked == true) {
                try {
                    bindside.add(shortSide, bindside.options[null]);
                } catch (e) {
                    bindside.add(shortSide, null);      
                }
                try {
                    bindcolor.add(colorBlack, bindcolor.options[null]);
                } catch (e) {
                    bindcolor.add(colorBlack, null);
                }
                bindcolor.disabled = false;
            }
            else {
                try {
                    bindside.add(selectSide, bindside.options[null]);
                } catch (e) {
                    bindside.add(selectSide, null);
                }
                try {
                    bindside.add(shortSide, bindside.options[null]);
                } catch (e) {
                    bindside.add(shortSide, null);      
                }
                try {
                    bindside.add(longSide, bindside.options[null]);
                } catch (e) {
                    bindside.add(longSide, null);
                }
            }
            bindside.disabled = false;
        }
        else if (pages.value >= 250 && pages.value <= 500) {
            try {
                bindside.add(longSide, bindside.options[null]);
            } catch (e) {
                bindside.add(longSide, null);
            }
            bindside.disabled = false;
            try {
                bindcolor.add(colorBlack, bindcolor.options[null]);
            } catch (e) {
                bindcolor.add(colorBlack, null);
            }
            bindcolor.disabled = false;
            cut.checked = false;
            cut.disabled = true;
        }
        else {
            alert("Sorry, we can only accept documents that are less than 500 pages.");
        }
    }
    function bind_side() {
        var bindside = document.getElementById("bindside");
        var bindcolor = document.getElementById("bindcolor");
        var selected = bindside.options[bindside.selectedIndex].value;
        var pages = document.getElementById("pages");
        var cut = document.getElementById("cut");
        bindcolor.options.length = 0;
        var colorBlack = document.createElement("option");
        colorBlack.text = "Black";
        colorBlack.value = "Black";
        
        var colorWhite = document.createElement("option");
        colorWhite.text = "White";
        colorWhite.value = "White";

        if (selected == '11 inch'){
            cut.checked = false;
            cut.disabled = true;
            try {
                bindcolor.add(colorBlack, bindcolor.options[null]);
            } catch (e) {
                bindcolor.add(colorBlack, null);
            }
            try {
                bindcolor.add(colorWhite, bindcolor.options[null]);
            } catch (e) {
                bindcolor.add(colorWhite, null);
            }
            bindcolor.disabled = false;
            
        }
        if (selected == '8 1/2 inch') {
            cut.disabled = false;
            try {
                bindcolor.add(colorBlack, bindcolor.options[null]);
            } catch (e) {
                bindcolor.add(colorBlack, null);
            }
            bindcolor.disabled = false;
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
    $(document).ready(function(){
        $("#aForm").validate();
    });

    
</script>

<div id="inner_container">
    <p style="font-size:12px;"><a href="/unet/copycenter/landing.php">Home</a> >> Press Bind</p>
    <div><form method="post" action="pressbind_test2.php"><button id="clear" type="submit" value="reset" name="clear">Clear</button></form>
        <form name="aForm" id="aForm" method="post" action="pressbind_verify2.php" enctype="multipart/form-data">
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

            <input type="text" name="pagecount" SIZE=25 id="pages" class="required number" maxlength="4" style="width:40px; margin-left:-50px;" onchange="page_count();"
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
            <br>
            <label>Bind Side</label>
            <select name="bindside" class="input required" id="bindside" onchange="bind_side()"
            <? if(isset($aJob->bindSide) && isset($aJob->pageCount)) {
                echo ">";
                if ($aJob->pageCount < 250 ) {
                    if ($aJob->bindSide == '8 1/2 inch') {
                        echo '<option vlaue="8 1/2 inch" selected="selected">8 1/2 inch</option>
                            <option value="11 inch">11 inch</option>';
                    } else {
                        echo '<option value="11 inch" selected="selected">11 inch</option>
                            <option value="8 1/2 inch">8 1/2 inch</option>';
                    }
                } else {
                    if ($aJob->bindSide == '8 1/2 inch') {
                        echo '<option vlaue="8 1/2 inch" selected="selected">8 1/2 inch</option>';
                    } else {
                        echo '<option value="11 inch" selected="selected">11 inch</option>';
                    }
                }
            } else {
                echo 'disabled="disabled">';
                echo '<option value="">Choose Side</option>';
            }
            ?>

            </select>
            <br>
            <br>
            <label>Bind Color</label>
            <select name="bindcolor" class="input required" id="bindcolor" 
            <? if(isset($aJob->bindSide) && isset($aJob->bindColor)) {
                echo ">";
                if($aJob->bindSide == '8 1/2 inch' || $aJob->pageCount > 250) {
                    echo '<option value="Black">Black</option>';
                } elseif ($aJob->bindColor == 'Black') {
                    echo '<option value="Black">Black</option>
                    <option value="White">White</option>';
                } else {
                    echo '<option value="White">White</option>
                        <option value="Black">Black</option>';
                }
                echo "</select>";
            } else {
?>
            disabled="disabled">
                <option value="">Choose color</option>
                <option value="Black">Black</option>
                <option value="White">White</option>
            </select>
            <? } ?>
            <br>
            <br>



            <span id="seperator"></span>
            <h4>Document options</h4>

            <label>Double Sided</label>
            <input type="checkbox" name="duplex"
                   <? echo (isset($aJob->duplex) && $aJob->duplex == 'Yes' ? 'checked' : "") ?>/>
            <br>

            <label>Cut in half</label>
            <input type="checkbox" name="cut" id="cut"
                <? if ((isset($aJob->pageCount) && $aJob->pageCount > 250) || (isset($aJob->bindSide) && $aJob->bindSide == '11 inch')) {
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
foreach ($aJob->defaultPaperType as $type) {
    if (isset($aJob->paperType) && $aJob->paperType == $type){
        echo '<option value="' . $type . '" selected="selected">' . $type . '</option>';
    } else {
        echo '<option value="' . $type . '">' . $type . '</option>';
    }
}
?>
            </select>
            <select name="papercolor" id="papercolor" style="margin-left:95px;">
                <?
                if (isset($aJob->paperType) && $aJob->paperType != "") {
                    $aJob->getPaperColor($aJob->paperType);
                }
                foreach ($aJob->defaultPaperColor as $color) {
                    if ($aJob->paperColor == $color) {
                        echo '<option value="' . $color . '" selected="selected">' . $color . '</option>';
                    } else {
                        echo '<option value="' . $color . '">'.$color.'</option>';
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
                   <? if(isset($aJob->frontPlastic)) {
                       if ( $aJob->frontPlastic == "Yes") {
                           echo "checked";
                       }
                   } else {
                       echo "checked";
                   } ?> 
                   />
            <br>

            <label>Black presentation cover</label>
            <input type="checkbox" name="frontpresentation" 
                   <? echo (isset($aJob->frontPresentation) && $aJob->frontPresentation == 'Yes' ? "checked" : "")?> />
            <span id="optional">*cannot be printed on, additional charge</span>
            <br>

            <label>Print in color</label>
            <input type="checkbox" name="frontcolorink" 
                   <? echo (isset($aJob->frontColorInk) && $aJob->frontColorInk == "Yes" ? "checked" : "")?> />
            <br>

            <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
            <br>
            <select name="coverpapertype" id="coverpapertype" style="margin-left:20px;" onchange="change_paper_type('coverpapertype', 'coverpapercolor')">
<?
foreach ($aJob->defaultPaperType as $type) {
    if (isset($aJob->frontPaperType) && $aJob->frontPaperType == $type){
        echo '<option value="' . $type . '" selected="selected">' . $type . '</option>';
    } else {
        echo '<option value="' . $type . '">' . $type . '</option>';
    }
}
?>
            </select>
            <select name="coverpapercolor" id="coverpapercolor" style="margin-left:95px;">
                <?
                if (isset($aJob->frontPaperType) && $aJob->frontPaperType != "") {
                    $aJob->getPaperColor($aJob->frontPaperType);
                }
                foreach ($aJob->defaultPaperColor as $color) {
                    if ($aJob->frontPaperColor == $color) {
                        echo '<option value="' . $color . '" selected="selected">' . $color . '</option>';
                    } else {
                        echo '<option value="' . $color . '">'.$color.'</option>';
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
                <? if(isset($aJob->backPlastic)) {
                    if ( $aJob->backPlastic == "Yes") {
                        echo "checked";
                    }
                } else {
                    echo "checked";
                } ?> 
                />

            <br/>
            <label>Black presentation cover</label>
            <input type="checkbox" name="backpresentation" id="blackback" 
                   <? echo (isset($aJob->backPresentation) && $aJob->backPresentation == "Yes" ? "checked" : "") ?>/>
            <span id="optional">*cannot be printed on, additional charge</span>

            <br/>
            <label>Insert blank page</label>
            <input type="checkbox" name="backblankpage" 
                   <? echo (isset($aJob->backBlankPage) && $aJob->backBlankPage == "Yes" ? "checked" : "") ?> />
            <br/>
            <br/>
            <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
            <br/>
            <select name="backpapertype" id="backpapertype" style="margin-left:20px;" onchange="change_paper_type('backpapertype', 'backpapercolor')">
                <?
foreach ($aJob->defaultPaperType as $type) {
    if (isset($aJob->backPaperTypee) && $aJob->backPaperType == $type){
        echo '<option value="' . $type . '" selected="selected>' . $type . '</option>';
    } else {
        echo '<option value="' . $type . '">' . $type . '</option>';
    }
}
?>
            </select>
            <select name="backpapercolor" id="backpapercolor" style="margin-left:95px;">
                <?
                if (isset($aJob->backPaperType) && $aJob->backPaperType != "") {
                    $aJob->getPaperColor($aJob->backPaperType);
                }
                foreach ($aJob->defaultPaperColor as $color) {
                    if ($aJob->backPaperColor == $color) {
                        echo '<option value="' . $color . '" selected="selected">' . $color . '</option>';
                    } else {
                        echo '<option value="' . $color . '">'.$color.'</option>';
                    }
                }
                ?>
            </select>
            <br/>
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




    