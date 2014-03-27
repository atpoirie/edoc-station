<?php
header("content-type: application/x-javascript");

print <<<END
   function select_all()
    {
        var comment = document.spiralbind.comments;
        comment.focus();
        comment.select();
    }
    function toggle_cardstock_front()
    {
        var cover = document.spiralbind.cardcover;
        var cardcover = document.spiralbind.frontcard;
   
        if ( cover.checked )
            cardcover.disabled = false;
        else
            cardcover.disabled = true;
    }

    function toggle_cardstock_back()
    {
        var back = document.spiralbind.back;
        var cardback = document.spiralbind.backcard;

        if ( back.checked)
            cardback.disabled = false;
        else
            cardback.disabled = true;
    }

    function update_cardstock_color()
    {
        var frontcolor = document.spiralbind.frontcard;
        var backcolor = document.spiralbind.backcard;
        var cardback = document.spiralbind.back;
        var frontcolorval = frontcolor.options[frontcolor.selectedIndex];
        var backcolorval = backcolor.options[backcolor.selectedIndex];
	
        if (cardback.checked)
        {
            backcolor.selectedIndex = frontcolor.selectedIndex;
        }
    }

    function update_cardback()
    {
        var cardback = document.spiralbind.backcard;
        var black = document.spiralbind.blackback; 
        var back = document.spiralbind.back;

        if ( black.checked)
        {
            back.disabled = true;
            cardback.disabled = true;
        }
        else
        {
            back.disabled = false;
            cardback.disabled = false;
        }

    }

    function colorpaper()
    {
        var coloredpaper = document.spiralbind.coloredpaper;
        if ( coloredpaper.checked )
        {
            $("#showhide").show();
            for (i=0; i<document.spiralbind.elements.length; i++)
            {
                document.spiralbind.elements[i].disabled = true;
            }
            coloredpaper.disabled = false;	
        }
        else
        {
            $("#showhide").hide();
            for (i=0; i<document.spiralbind.elements.length; i++)
            {
                document.spiralbind.elements[i].disabled = false;
            }
            update_cardback();
            toggle_cardstock_front();
            toggle_cardstock_back();
        }
    }
END;
?>
