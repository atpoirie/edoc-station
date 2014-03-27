<?php
session_start();
require_once "/var/authscripts/ncu_auth.inc";
require_once "/var/www/unet2/copycenter/include/jobclass.php";
require_once "/var/www/unet2/copycenter/include/copyclass.php";

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

$copy = new Copy($uname);

include_once("../include/header.php");

function get_earliest() {
    $hour = date("G");
    $minute = date("i");
    $stamp = time();

    if ($hour >= 13)
        $stamp += 122400 - ($hour * 60 * 60); //(33*60*60) - G*60*60
    elseif ($hour < 8)
        $stamp += (11 - $hour) * 60 * 60;
    else
        $stamp += 5 * 60 * 60;

    if (date("N", $stamp) > 5)
        $stamp += 24 * 60 * 60;
    if (date("N", $stamp) > 5)
        $stamp += 24 * 60 * 60;

    $earliest = date("Y", $stamp) . ",";
    $earliest .= date("n", $stamp) - 1;
    $earliest .= date(",j,G", $stamp);
    $earliest .= ",0,0,0";

    return $earliest;
}
?>

<style type="text/css">
    #inner_container{
        position:relative;
        padding:0px;
        margin:0 auto;   
        padding-left: 5px;
    }

    select {
        line-height: 1;
    }

    ul, li{
        margin: 0;
        padding: 0;
        list-style-type:none;
    }

    form label{
        margin: 5px 5px;
    }

    input {
        width: 200px;
        font-size:1em;
        outline:0;
        line-height: 1;   
    }

    select {
        box-shadow: 0 0 5px #EBE6D9 inset;
        width: 200px;
        font-size: 1em;
        outline: 0;
    }

    input:focus {
        border: 1px solid #9D6E56;
        border-top-color: #9D6E56;
        border-bottom-color: #9D6E56;
        box-shadow: 0 0 5px #9D6E56;
    }

    select:focus {
        border: 1px solid #9D6E56;
        border-top-color: #9D6E56;
        border-bottom-color: #9D6E56;
        box-shadow: 0 0 5px #9D6e56;
    }

    label {
        display: inline-block;
        width: 130px;
        color:#555;
    }

    #ncu_content span{
        padding: 3px 0px 8px 40px;
        display: block;
        background-color: #B84702;
        border-radius: 5px;
        margin-top:5px;
    }

    button {
        background: #EBE6D9; /* Old browsers */
        background: -moz-linear-gradient(top, #EBE6D9 0%, #EBE6D9 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#EBE6D9), color-stop(100%,#EBE6D9)); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top, #EBE6D9 0%,#EBE6D9 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top, #EBE6D9 0%,#EBE6D9 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top, #EBE6D9 0%,#EBE6D9 100%); /* IE10+ */
        background: linear-gradient(top, #EBE6D9 0%,#EBE6D9 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#EBE6D9', endColorstr='#EBE6D9',GradientType=0 ); /* IE6-9 */
        border:1px solid #9D6E56;
        border-top-color:#9D6E56;
        border-bottom-color:#9D6E56;
        color:#9D6E56;
        text-shadow:0 1px 0 #9D6E56;
        font-size:.875em;
        padding:8px 15px;
        float: right;
        width:150px;
        border-radius:20px;
        box-shadow:0 1px 0 #bbb, 0 1px 0 #9D6E56 inset;
    }
    button:active {
        background: #9D6E56; /* Old browsers */
        background: -moz-linear-gradient(top, #9D6E56 0%, #9D6E56 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#9D6E56), color-stop(100%,#9D6E56)); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top, #9D6E56 0%,#9D6E56 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top, #9D6E56 0%,#9D6E56 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top, #9D6E56 0%,#9D6E56 100%); /* IE10+ */
        background: linear-gradient(top, #9D6E56 0%,#9D6E56 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#9D6E56', endColorstr='#9D6E56',GradientType=0 ); /* IE6-9 */
        box-shadow:none;
        color:#EBE6D9;
        text-shadow:0 -1px 0 #1e3c5e;
    }

    input.error, select.error {
        border: 2px solid red;
        background-color: #FFFFD5;
        margin: 0px;
        color: red;
    }

    div.formError {
        display: none;
        color: #FF0000;
    }

    div.error {
        color: red;
    }

    label.error {
        padding-left: 100px;
        width: 300px;
        display: block;
        color: red;
        font-style: italic;
        font-weight: normal;
    }

</style>

<SCRIPT type="text/javascript" src="../include/jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="../include/jquery.validate.js"></SCRIPT>
<link rel="stylesheet" type="text/css" href="anytime.css" />
<script type="text/javascript" src="anytime.js"></script>



<SCRIPT language="javascript">
    var papersize = new Array();
    var papertype = new Array();
    var papercolor = new Array();
    var sizetotype = new Array();
    var sizetypecolor = new Array();
    var sizetype = new Array();
    var emptyString = /^\s*$/;

<?php
$res = & $copy->sql->query("SELECT * FROM unet_mc_papersize");
while ($row = $res->fetchRow()) {
    echo 'papersize[' . $row['papersize_index'] . '] = "' . $row['papersize'] . '"';
    echo "\n";
}
echo "\n";

$res = & $copy->sql->query("SELECT * FROM unet_mc_papertype");
while ($row = $res->fetchRow()) {
    echo 'papertype[' . $row['papertype_index'] . '] = "' . $row['papertype'] . '"';
    echo "\n";
}
echo "\n";

$res = & $copy->sql->query("SELECT * FROM unet_mc_papercolor");
while ($row = $res->fetchRow()) {
    echo 'papercolor[' . $row['papercolor_index'] . '] = "' . $row['papercolor'] . '"';
    echo "\n";
}
echo "\n";

$res = & $copy->sql->query("SELECT distinct paper_size, paper_type FROM unet_mc_paper");
$i = 0;
while ($row = $res->fetchRow()) {
    echo 'sizetype[' . $i . '] = "' . $row['paper_size'] . ',' . $row['paper_type'] . '"';
    echo "\n";
    $i++;
}
echo "\n";

$res = & $copy->sql->query("SELECT * FROM unet_mc_paper");
while ($row = $res->fetchRow()) {
    echo 'sizetypecolor[' . $row['paper_index'] . '] = "' . $row['paper_size'] . ',' . $row['paper_type'] . ',' . $row['paper_color'] . '"';
    echo "\n";
}

echo "\n";
?>

    function set_papertype()
    {
        var size = document.copy.papersize;
        var type = document.copy.papertype;
        var color = document.copy.papercolor;
        var typeinfo = new Array();
        var sizevalue;	

        type.options.length = 0;
        color.options.length = 0;
        type.options[0] = new Option("Select Paper Type");
        color.options[0] = new Option("Select Paper Color");
        color.disabled = true;

        for (var i=0; i < size.options.length; i++)
        {
            if (size.options[i].selected)
                sizevalue = size.options[i].value;
        }

        type.disabled = false;
        var pattern = new RegExp("^"+sizevalue);
        for ( var i=0; i < sizetype.length; i++)
        {
            if (pattern.test(sizetype[i]))
            {
                typeinfo = sizetype[i].split(",");
                type.options[type.options.length] = new Option(papertype[typeinfo[1]], typeinfo[1]);
            }
        }
			
    }

    function set_papercolor()
    {
        var size = document.copy.papersize;
        var type = document.copy.papertype;
        var color = document.copy.papercolor;
        var colorinfo = new Array();
        var sizevalue;
        var typevalue;
	
        color.options.length = 0;
        color.options[0] = new Option("Select Paper Color");
        color.disabled = false;

        for (var i=0; i < size.options.length; i++)
        {
            if (size.options[i].selected)
                sizevalue = size.options[i].value;
        }
	
        for (var i=0; i < type.options.length; i++)
        {
            if (type.options[i].selected)
                typevalue = type.options[i].value;
        }
	
        var pattern = new RegExp("^"+sizevalue+","+typevalue+",");
        for ( var i=0; i < sizetypecolor.length; i++)
        {
            if (pattern.test(sizetypecolor[i]))
            {
                colorinfo = sizetypecolor[i].split(",");
                color.options[color.options.length] = new Option(papercolor[colorinfo[2]], colorinfo[2]);
            }
        }

    }

    function set_folding()
    {
        var folding = document.copy.folding;
        var laminate = document.copy.laminate;
        var cut = document.copy.cut;
        var foldingval;
        var staple = document.copy.staple;
        var stapleval;
        var uncollate = document.copy.collate;
        var punch = document.copy.punch;

        foldingval = folding.selectedIndex;
	
	
        if (foldingval != 0)
        {
            laminate.checked = false;
            laminate.disabled = true;
            cut[0].selected = true;
            staple[0].selected = true;
            staple.disabled = true;
            cut.disabled = true;
            uncollate.checked = false;
            uncollate.disabled = true;
            punch.checked = false;
            punch.disabled = true;	
        }
        else
        {
            laminate.disabled = false;
            punch.disabled = false;
            uncollate.disabled = false;
            cut[0].checked = true;
            cut.disabled = false;
            staple.disabled = false;
            stapleval = staple.selectedIndex;
            if (stapleval != "0")
                set_staple();
        }
    }

    function set_staple()
    {
        var staple = document.copy.staple;
        var laminate = document.copy.laminate;
        var uncollate = document.copy.collate;
        var punch = document.copy.punch;
        var cut = document.copy.cut;
        var stapleval;
        var folding = document.copy.folding;

        stapleval = staple.selectedIndex;
	
	
        if (stapleval == 3 || stapleval == 2)
        {
            laminate.checked = false;
            laminate.disabled = true;
            uncollate.checked = false;
            uncollate.disabled = true;
            punch.checked = false;	
            punch.disabled = true;
            cut[0].selected = true;
            cut.disabled = true;
            folding.disabled = true;
        }

        if (stapleval == 1)
        {
            laminate.checked = false;
            laminate.disabled = true;
            uncollate.checked = false;
            uncollate.disabled = true;
            punch.checked = false;
            punch.disabled = false;
            cut.disabled = true;
            folding.disabled = true;
        }
        if (stapleval == 0)
        {
            laminate.disabled = false;
            uncollate.disabled = false;
            punch.disabled = false;
            cut.disabled = false;
            folding.disabled = false;
            set_folding();
        }	
    }

    function set_cut()
    {
        var punch = document.copy.punch;
        var uncollate = document.copy.collate;
        var cut = document.copy.cut;
        var folding = document.copy.folding;
        var staple = document.copy.staple;
        var cutval;

        cutval = cut.selectedIndex;
	
	
        if (cutval != 0)
        {
            punch.checked = false;
            punch.disabled = true;
            uncollate.checked = false;
            uncollate.disabled = true;
            folding.disabled = true;
            staple.disabled = true;
		
        }
        else
        {
            punch.disabled = false;
            uncollate.disabled = false;
            folding.disabled = false;
            staple.disabled = false;
        }
    }

    $(document).ready(function(){
        $("#copyForm").validate();
    });

</script>

<div id="inner_container">
    <p style="font-size:12px;"><a href="/unet/copycenter/index.php">Home</a> >> Copy</p>
    <div>
        <form name="copy" id="copyForm" method="post" action="submit_copy.php" enctype="multipart/form-data">
            <input type="hidden" name="submitdate" value="<?php echo date("Ymd G:i:00") ?>" />
            <label>Job Name</label>
            <input type="text" name="jobname" SIZE=25 id="jobname" class="required" minlength="2"
                   <?php echo (isset($copy->jobname) ? 'Value="' .$copy->jobname. '"' : 'Value=""') ?>/>
            <br>
            <br>
            <label>Select File</label>
            <input type="file" name="upload" size=25 class="required"
                   <?php echo (isset($copy->filename) ? 'value="' .$copy->filename. '"' : 'value=""') ?>/>

            <br>
            <br>
            <label>Original Pages</label>

            <input type="text" name="pages" SIZE=25 id="pages" class="required number" maxlength="4" 
                   <?php echo (isset($copy->pagecount) ? 'Value="' . $copy->pagecount . '"' : 'Value=""') ?>/>

            <br>
            <br>
            <label>Quantity</label>
            <input type="text" name="quantity" size=16 class="required number" maxlength="5"
                   <?php echo (isset($copy->quantity) ? 'Value="' . $copy->quantity . '"' : 'Value=""') ?>/>
            <br>
            <br>

            <label>Date Due</label>
            <input type="text" name="date1x" id="date1x" size="20" class="required"
                   <?php echo (isset($copy->duedate) ? 'Value="' . $copy->duedate . '"' : 'Value=""') ?>/>
            <script type="text/javascript">
                AnyTime.picker( "date1x",
                { 	format: "%m/%d/%Z %h%p", 
                    firstDOW: 1,
                    earliest: new Date(<? echo get_earliest(); ?>) } );
            </script>
            <br>
            <br>
            <label>Phone #</label>
            <input type="text" name="phone" SIZE=16 class="required" 
                   <? echo (isset($copy->phone) ? 'Value="' . $copy->phone . '"' : 'Value=""') ?>/>
            <br>
            <br>

            <label>Department</label>
            <select name="department" class="input required">
                <option 
                <?php
                echo (isset($copy->department) ?
                        'Value="' . $copy->department . '" selected="selected">' . $copy->department . '</option>' : 'Value="">Select Department</option>');
                $res = & $copy->sql->query("SELECT * from unet_mc_dept ORDER BY department");
                while ($row = $res->fetchRow()) {
                    echo "<option value=\"" . $row['department'] . "\">" . $row['department'] . "</option>";
                    echo "\n";
                }
                ?>
        </select>
        <br>
        <br>
        <label>Account #</label>
        <input type="text" name="account" size=13 class="number" 
        <? echo (isset($copy->account) ? 'Value="' . $copy->account . '"' : 'Value=""') ?>
               /><font style="font-size:12px;">(optional)</font>
        <br>
        <br>

        <span></span>
        <br>
        <strong>Paper Selection</strong>
        <br>

        <label>Paper Size</label>
        <select name="papersize" onchange="set_papertype()" class="required">
            <?php
            $first = true;
            $res = & $copy->sql->query("SELECT * FROM unet_mc_papersize");
            while ($row = $res->fetchRow()) {
                if ($first) {
                    $sizeindex = $row['papersize_index'];
                    $first = false;
                }
                echo '<option value="' . $row['papersize_index'] . '">' . $row['papersize'] . '</option>';
            }
            ?>
        </select>
        <br>
        <label>Paper Type</label>

        <select name="papertype" onchange="set_papercolor()" class="required">
            <?php
            $first = true;
            $res = & $copy->sql->query("SELECT DISTINCT paper_type FROM unet_mc_paper WHERE paper_size = '$sizeindex'");
            while ($row = $res->fetchRow()) {
                if ($first) {
                    $typeindex = $row['paper_type'];
                    $first = false;
                }
                $subres = & $copy->sql->query("SELECT * FROM unet_mc_papertype WHERE papertype_index = '" . $row['paper_type'] . "'");
                $subrow = $subres->fetchRow();
                echo '<option value="' . $subrow['papertype_index'] . '">' . $subrow['papertype'] . '</option>';
            }
            ?>      
        </select>
        <br>

        <label>Paper Color</label>
        <select name="papercolor" class="required">
            <?php
            $res = & $copy->sql->query("SELECT DISTINCT paper_color FROM unet_mc_paper WHERE paper_size = '$sizeindex' AND paper_type = '$typeindex'");
            while ($row = $res->fetchRow()) {
                $subres = & $copy->sql->query("SELECT * FROM unet_mc_papercolor WHERE papercolor_index = '" . $row['paper_color'] . "'");
                $subrow = $subres->fetchRow();
                echo '<option value="' . $subrow['papercolor_index'] . '">' . $subrow['papercolor'] . '</option>';
            }
            ?></select>
        <br>
        <br>
        <span></span>
        <br>
        <label>Double Sided</label>
        <input type="checkbox" name="duplex"/>
        <br>
        <br>
        <label>Print in</label>
        <select name="ink">
            <option value="Black/White">Black/White</option>
            <option value="Color">Color</option>
        </select>

        <br>
        <br>
        <label>Folding</label>
        <select name="folding" onChange="set_folding();">
            <option value="No Folding">No Folding</option>
            <option value="C Fold">"C" Fold</option>
            <option value="Z Fold">"Z" Fold</option>
            <option value="Gate Fold">Gate Fold</option>
            <option value="Half Fold">Half Fold</option>
        </select>
        <br>
        <br>


        <label>Stapling</label>
        <select name="staple" onchange="set_staple();"/>
        <option value="No Staple">No Staple</option>
        <option value="Left Corner Staple">Left Corner Staple</option>
        <option value="Right Corner Staple">Right Corner Staple</option>
        <option value="Saddle Stitch">Saddle Stitch</option>
        </select>
        <br>
        <br>

        <label>Cutting</label>
        <select name="cut" onchange="set_cut();"/>
        <option value="No Cutting">No Cutting</option>
        <option value="Cut into 1/4">Cut into 1/4</option>
        <option value="Cut into 1/2">Cut into 1/2</option>
        <option value="Cut to bleed">Cut to bleed</option>
        </select>
        <br>
        <span></span>
        <br>
        <label>Laminate</label>
        <input type="checkbox" name="laminate"/>
        
        <br>
        <label>UnCollate</label>
        <input type="checkbox" name="collate"/>
        
        <br>
        <label>Hole Punch</label>
        <input type="checkbox" name="punch"/>
        
        <br>
        <label>Pick up</label>
        <input type="radio" name="transport" value="Pick-up" />
        
        <br>
        <label>Deliver</label>
        <input type="radio" name="transport" value="Deliver" checked/>
        
        <span></span>
        <br>
        <textarea rows="5" cols="40" name="comments">Additional comments</textarea>
        <br>
        <br>
             
            <button type="submit" value="Submit">Submit</button>
            <br>
            <br>
            <span></span>
    </FORM>

</div>
</div>
<?php
require_once("../include/footer.php");
