<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "./include/jobclass.php";
require_once "./include/spiralclass.php";
$pagetitle = "Online Copy Center";
$quicklinks = true;
//ncu_forcesecure();
//ncu_forceauth();

require_once("../include/header.php");


$uname = ncu_getusername();
if (ncu_isstudent($uname)) {
    echo "This system is for NCU employees only";
    require_once("../include/footer.php");
    return;
}

$spiral = new Spiral();
$spiral->getEarliest();

$sage = ncu_sage_unet_menu();


//If the user has used the copy center before
//look up their previous phone and department and 
//populate the form with that data ahead of time
$query = "SELECT TOP 1 phone, department FROM unet_mc_copy 
         WHERE username = '$uname' ORDER BY jobid";
$res = & $sage->query($query);
$row = $res->fetchRow();
$prePhone = $row[0];
$preDepartment = $row[1];


$res = & $sage->query(
                "SELECT papercolor FROM unet_mc_paper
	JOIN unet_mc_papersize
	ON unet_mc_paper.paper_size = unet_mc_papersize.papersize_index
	JOIN unet_mc_papertype
	ON unet_mc_paper.paper_type = unet_mc_papertype.papertype_index
	JOIN unet_mc_papercolor
	ON unet_mc_paper.paper_color = unet_mc_papercolor.papercolor_index
	WHERE papersize = '8 1/2 x 11'
	AND papertype = 'cardstock'");

$cardcolor = array();

while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
    $cardcolor[] = $row[papercolor];
}
?>

<link rel="stylesheet" type="text/css" href="css/copycenter.css">

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></SCRIPT>
<link rel="stylesheet" type="text/css" href="css/anytime.css" />
<SCRIPT type="text/javascript" src="anytime.js"></SCRIPT>
<script type="text/javascript" src="spiral.php"></script>
<script language="javascript">


    $(document).ready(function(){
        $("#spiralbind").validate();
        $("#showhide").hide();
    });

</script>

<p style="font-size:12px;"><a href="/unet/copycenter/landing.php">Home</a> >> Spiral Bind</p>
<div>
    <form name="spiralbind" id="spiralbind" method="post" action="submit_spiralbind.php" enctype="multipart/form-data">
        <input type="hidden" name="submitdate" value="<?php echo date("Ymd G:i:00"); ?>" >
        <h4>Job Information</h4>
        <label>Phone Number</label>
        <input type="text" name="phone" size="25" class="input required" minlength="4"/>
        <br/>
        <br/>
        <label>Job Name</label>
        <input type="text" name="jobname" SIZE=25 id="jobname" class="input required" minlength="2"/>
        <br/>
        <br/>

        <label>Department to charge</label>
        <select name="departmentcharge">
            <option value="">Select Department</option>
        </select>
        <br/>
        <br/>
        <label>Account #</label>
        <input name="account" type="text" size="25" /><span id="optional">Optional</span>
        <br/>
        <br/>
        <label>Select Document</label>
        <input type="file" name="upload" id="upload" size=25 class="required"/>
        <br/>
        <br/>
        <label></label><label>Number of pages</label>
        <input style="width:30px; margin-left:-50px;" name="pagecount" size="3" class="required number"/>
        <br/>
        <br/>
        <label>Quantity</label>
        <input type="text" name="quantity" value="1" size=4 class="required number"/>
        <br/>
        <br/>
        <label>Date Due</label>
        <input type="text" NAME="date1x" id="date1x" size="20" class="input required"/>
        <script type="text/javascript">
            AnyTime.picker( "date1x",
            { format: "%m/%d/%Z %h%p",
                firstDOW: 1,
                earliest: new Date(<? echo $spiral->earliest; ?>)
            });
        </script>
        <br/>
        




        <span id="seperator"></span>
        <h4>Delivery Mode</h4>
        <label>Pick up</label>
        <input type="radio" name="transport" value="Pick-up" />
        <br/>

        <label>Deliver</label>
        <input type="radio" name="transport" value="Deliver" checked/>
        <br/>
        <label>Personal Copy</label>
        <input type="radio" name="transport" value="Personal"/><span id="optional">Pay at pickup</span>
        <br/>
        <label>Confidential/Test</label>
        <input type="checkbox" value="confidential" />
        <br/>
        <br/>



        
        
        
        
        
        <span id="seperator"></span>
        <br>
        <label>Spiral Color</label>
        <select name="spiral" class="input required">
            <option value="">Choose color</option>
            <option value="Navy">Navy</option>
            <option value="White">White</option>
            <option value="Black">Black</option>
            <option value="Clear">Clear</option>
        </select>
        <br>
        <br>
        <label>Spiral Side</label>
        <select name="side" class="input required">
            <option value="">Choose Side</option>
            <option value="Long">Long Side</option>
            <option value="Short">Short Side</option>
        </select>
        <br/>
        <br/>

        
        


        <span id="seperator"></span>
        <h4>Document Options</h4>
        <label>Double Sided</label>
        <input type="checkbox" name="duplex"/>
        <br/>
                        <label>Cut in half</label>
        <input type="checkbox" name="cut"/>
        <br>
        <label>Printed in color</label>
        <input type="checkbox" name="docink"/>

        <br>

        <br>
        <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
        <br>
        <select name="papertype" style="margin-left:20px;">
            <option value="normal">Normal</option>
            <option value="cardstock">Cardstock</option>
        </select>
        <select name="papercolor" style="margin-left:35px;">
            <option value="white">White</option>
        </select>
        <br/>
        <br/>
        
        
        
        
        
        <span id="seperator"></span>
        <h4>Front Cover options</h4>
         <label>Plastic cover</label>
        <input type="checkbox" name="plasticcover" value="platic cover" />
        <br/>
         <label>Black presentation cover</label>
        <input type="checkbox" name="presentationcover" value="presentation cover" />
        <br>

        <label>Print in color</label>
        <input type="checkbox" name="colorcover" value="Cover in color" id="cover" />
        <br>
        <br>
        <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
        <br>
        <select name="coverpapertype" style="margin-left:20px;">
            <option value="normal">Normal</option>
            <option value="cardstock">Cardstock</option>
        </select>
        <select name="coverpapercolor" style="margin-left:35px;">
            <option value="white">White</option>
        </select>
        <br/>
        <br/>
       
        
        
        
        
        <span id="seperator"></span>
        <h4>Back Cover Options</h4>
        <label>Plastic Cover</label>
        <input type="checkbox" name="backplastic" value="Plastic back cover"/>

        <br/>
        <label>Black presentation cover</label>
        <input type="checkbox" name="backpresentation" id="blackback" value="Presentation back cover"/>
        <br>
        <label>Insert blank page</label>
        <input type="checkbox" name="back" id="back" onchange="toggle_cardstock_back();"/>
        <br/>
        <br/>
          <label>Paper Type</label><span style="padding-left:100px;"></span><label>Paper Color</label>
        <br>
                <select name="backpapertype" style="margin-left:20px;">
            <option value="normal">Normal</option>
            <option value="cardstock">Cardstock</option>
        </select>
        <select name="backpapercolor" style="margin-left:95px;">
            <option value="white">White</option>
        </select>
        <br/>
        <br/>
        <span id="seperator"></span>
        <br>
        <textarea rows="5" cols="40" name="comments" onClick="select_all();">Additional comments</textarea>
        <button value="Submit" style="margin-top:40px; margin-right:10px;">Submit</button>
    </form>
</div>
<DIV ID="testdiv1" STYLE="position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></DIV>

<?php
require_once("../include/footer.php");



