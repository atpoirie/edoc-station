<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
   <link rel="stylesheet" type="text/css" href="css/copycenter.css"/>
   <link rel="stylesheet" type="text/css" href="../include/ncu.css"/>
</HEAD>
<?php


session_start();
//require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";

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


$aJob->get_all_paper_colors();
$aJob->get_all_paper_sizes();
$aJob->get_all_paper_types();
$aJob->get_all_paper_combinations();
?>
<div id="jobbox">
    <h4 style="margin-top:0px; margin-bottom:5px;">New paper properties</h4>
    <form method="POST" action="admin.php?type=paper&function=sizeadd">
        <label>Size</label>
        <input type="text" name="size"/> 
        <input type="image" name="nothing" value="size" src="image/plus-icon.png" style="height:20px; width:20px;"/>
        <div></div>
    </form>
    <form method="POST" action="admin.php?type=paper&function=typeadd">
        <label>Type</label>
        <input type="text" name="type"/> 
        <input type="image" name="nothing" value="type" src="image/plus-icon.png" style="height:20px; width:20px;"/>
        <div></div>
    </form>
    <form method="post" action="admin.php?type=paper&function=coloradd">
        <label>Color</label>
        <input type="text" name="color"/> 
        <input type="image" name="nothing" value="color" src="image/plus-icon.png" style="height:20px; width:20px;"/>
        <div></div>
    </form>
</div>
<br/>
<div id="jobbox">
    <h4 style="margin-top:0px;margin-bottom:5px;">New paper combination</h4>
    <form method="POST" action="admin.php?type=paper&function=combinationadd">
                <label>Size</label>
                <select name="size">
                    <? for($i=0; $i<count($aJob->paperSizes); $i++)
                        echo '<option value="'.$aJob->paperSizes[$i]['papersize_index'].'">'.$aJob->paperSizes[$i]['papersize'].'</option>';
                    
                    ?>
                </select><div></div>
                <label>Type</label>
                <select name="type">
                    <? for($i=0; $i<count($aJob->paperTypes); $i++)
                            echo '<option value="'.$aJob->paperTypes[$i]['papertype_index'].'">'.$aJob->paperTypes[$i]['papertype'].'</option>';
                    ?>
                </select><div></div>
                <label>Color</label>
                <select name="color">
                    <? for ($i=0; $i<count($aJob->paperColors); $i++)
                            echo '<option value="'.$aJob->paperColors[$i]['papercolor_index'].'">'.$aJob->paperColors[$i]['papercolor'].'</option>';
                    ?>
                </select> 
                <input type="image" name="nothing" src="image/plus-icon.png" style="height:20px; width:20px;"/><div></div>
            </form>
            
        </div>
        <br/>
        <div id="jobbox">
            <h4 style="margin-top:0px;margin-bottom:5px;">Current paper combinations</h4>
            <form method="post" action="admin.php?type=paper&function=combinationdel">
            <select name="id" style="width:400px;">
                <?foreach($aJob->paperCombinations as $combo) { ?>
                <option value="<?echo $combo['paper_index']?>">
                    <?echo $combo['papersize'];
                    for($i=0; $i<20-strlen($combo['papersize']); $i++)
                        echo "&nbsp";
                    echo $combo['papertype'];
                    for($i=0; $i<20-strlen($combo['papertype']); $i++)
                        echo "&nbsp";
                    echo $combo['papercolor'];
                ?>
                </option>
                <?}?>
            </select>
            <input type="image" src="image/cancel-icon.png" style="height:20px;width:20px;" value="remove"/>
            </form>
            
        </div>
        <div id="jobbox">
            <h4 style="margin-top:0px;margin-bottom:5px;">Remove Paper</h4>   
            <br>
            <form method="post" action="admin.php?type=paper&function=colordel">
                <label>Paper Color</label>
                <select name="color" style="display:inline;">
            <? foreach($aJob->paperColors as $color ) { ?>
                <option style="padding-left:30px;" value="<?echo $color['papercolor']?>"><?echo $color['papercolor']?> </option>                    
            <?}?>
                </select>
                 <input type="image" src="image/cancel-icon.png" style="height:20px; width:20px;" value="Remove">                  
            </form>
            <br/>
            <br/>
            <form method="post" action="admin.php?type=paper&function=typedel">       
                <label>Paper Type</label>
                <select name="type">
            <? foreach($aJob->paperTypes as $type ) { ?>
                <option style="padding-left:30px;" value="<?echo $type['papertype']?>"><?echo $type['papertype']?> </option>                    
            <?}?>
                </select>
                <input type="image" src="image/cancel-icon.png" style="height:20px; width:20px;" value="Remove"> 
            </form>
            <br/>
            <br/>
            <form method="post" action="admin.php?type=paper&function=sizedel">
                <label>Paper Size</label>
                <select name="size">
                    <?foreach($aJob->paperSizes as $size) { ?>
                    <option style="padding-left:30px;" value="<?echo $size['papersize']?>"><?echo $size['papersize']?></option>
                    <?}?>
                </select>
                <input type="image" src="image/cancel-icon.png" style="height:20px; width:20px;" value="Remove">
            </form>
        </div>
