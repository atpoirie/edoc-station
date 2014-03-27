/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
<?php 
    header("content-type: application/x-javascript");
    include_once('../include/jobclass.php');
    $paperselection = new Job();
    print<<<END
   var papersize = new Array();
    var papertype = new Array();
    var papercolor = new Array();
    var sizetotype = new Array();
    var sizetypecolor = new Array();
    var sizetype = new Array();
    var emptyString = /^\s*$/;
END;


$res = & $paperselection->sql->query("SELECT * FROM unet_mc_papersize");
while ($row = $res->fetchRow()) {
    echo 'papersize[' . $row['papersize_index'] . '] = "' . $row['papersize'] . '"';
    echo "\n";
}
echo "\n";

$res = & $paperselection->sql->query("SELECT * FROM unet_mc_papertype");
while ($row = $res->fetchRow()) {
    echo 'papertype[' . $row['papertype_index'] . '] = "' . $row['papertype'] . '"';
    echo "\n";
}
echo "\n";

$res = & $paperselection->sql->query("SELECT * FROM unet_mc_papercolor");
while ($row = $res->fetchRow()) {
    echo 'papercolor[' . $row['papercolor_index'] . '] = "' . $row['papercolor'] . '"';
    echo "\n";
}
echo "\n";

$res = & $paperselection->sql->query("SELECT distinct paper_size, paper_type FROM unet_mc_paper");
$i = 0;
while ($row = $res->fetchRow()) {
    echo 'sizetype[' . $i . '] = "' . $row['paper_size'] . ',' . $row['paper_type'] . '"';
    echo "\n";
    $i++;
}
echo "\n";

$res = & $paperselection->sql->query("SELECT * FROM unet_mc_paper");
while ($row = $res->fetchRow()) {
    echo 'sizetypecolor[' . $row['paper_index'] . '] = "' . $row['paper_size'] . ',' . $row['paper_type'] . ',' . $row['paper_color'] . '"';
    echo "\n";
}

echo "\n";

print <<<END
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
        
END;
?>