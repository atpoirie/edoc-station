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

papertype[1] = "normal";
papertype[2] = "cardstock";

papercolor[1] = "white";
papercolor[2] = "blue";
papercolor[3] = "buff";
papercolor[4] = "goldenrod";
papercolor[5] = "green";
papercolor[6] = "ivory";
papercolor[7] = "pink";
papercolor[8] = "purple";
papercolor[9] = "yellow";
papercolor[10] = "cherry";
papercolor[11] = "salmon";
papercolor[12] = "orange";
papercolor[13] = "bright blue";
papercolor[14] = "turquoise";
papercolor[15] = "lemon";
papercolor[16] = "grass green";
papercolor[17] = "red";
papercolor[18] = "natural";
papercolor[19] = "lipstick";
papercolor[20] = "gray sandstone";
papercolor[21] = "clay";
papercolor[22] = "buttercream";
papercolor[23] = "white skystone";
papercolor[24] = "gray linen";
papercolor[26] = "Letterhead";
papercolor[27] = "Presidents Letterhead";
papercolor[28] = "Own Paper";
papercolor[29] = "Glossy White";
papercolor[30] = "College of Ministry";
papercolor[31] = "College of Missions";
papercolor[32] = "College of Arts & Science";
papercolor[33] = "College of Fine Arts";
papercolor[34] = "NCU Letterhead";
papercolor[35] = "Institute of Bible & Theology";
papercolor[36] = "2nd Sheet";
papercolor[37] = "gray";

typecolor[1] = "1,1";
typecolor[2] = "1,2";
typecolor[3] = "1,3";
typecolor[4] = "1,4";
typecolor[5] = "1,5";
typecolor[6] = "1,6";
typecolor[7] = "1,7";
typecolor[8] = "1,8";
typecolor[9] = "1,1";
typecolor[89] = "2,12";
typecolor[90] = "20,1";
typecolor[14] = "2,1";
typecolor[15] = "1,9";
typecolor[16] = "1,10";
typecolor[60] = "11,1";
typecolor[55] = "1,1";
typecolor[81] = "13,29";
typecolor[82] = "1,37";
typecolor[74] = "4,34";
typecolor[75] = "4,30";
typecolor[76] = "4,31";
typecolor[17] = "1,11";
typecolor[31] = "3,15";
typecolor[19] = "2,2";
typecolor[20] = "2,5";
typecolor[21] = "2,6";
typecolor[22] = "2,7";
typecolor[23] = "2,9";
typecolor[73] = "4,27";
typecolor[32] = "3,16";
typecolor[33] = "3,17";
typecolor[62] = "12,28";
typecolor[77] = "4,32";
typecolor[78] = "4,33";
typecolor[79] = "4,35";
typecolor[80] = "4,36";

        var pattern = new RegExp(typeSelected);
        var paper_index;
        for(var i=0; i < papertype.length; i++) {
            if (pattern.test(papertype[i]))
                paper_index = i;
        }
        var colors = new Array();
        var coloridentity;
        pattern = new RegExp("^"+paper_index+",");
        for(var j=0; j< typecolor.length; j++) {
            if ( pattern.test(typecolor[j])) {
                coloridentity = typecolor[j].split(",");
                colors.push(papercolor[colorididentity[1]]);
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
        $("#showhide").hide();
    });