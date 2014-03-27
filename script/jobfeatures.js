/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


    function deliverTo() {
        var deliveryType = document.getElementsByName("transport");  
        var deliverToElement = document.getElementById("deliverto");
        var departmentCharge = document.getElementById("departmentcharge");
        var departmentDeliver = document.getElementById("departmentdeliver");
        var value;
        var department;
        
        for (var i = 0; i < deliveryType.length; i++) {
            if (deliveryType[i].checked) {
                value = deliveryType[i].value;
            }
        }
        if (value == 'Deliver') {
  /*          for( var k=0; k < departmentCharge.length; k++ ) {
                if (departmentCharge[k].selected)
                    department = departmentCharge[k].value;
            } */
            department = departmentCharge.options[departmentCharge.selectedIndex].value;
            deliverToElement.style.display = "block";
            for( var j=0; j < departmentDeliver.length; j++ ) {
                if (departmentDeliver[j].value == department) {
                    departmentDeliver[j].selected = true;
                }
            }
            departmentDeliver.className = 'input required';
            $(document).ready(function(){
                $("#copyForm").validate();
            });
            
        } else {
            deliverToElement.style.display = "none";
            departmentDeliver.className = 'input';
            $(document).ready(function(){
                $("#copyForm").validate();
            });
        }
    }

    function departmentCharge() {
        var departmentCharge = document.getElementById("departmentcharge");
        var selected = departmentCharge.options[departmentCharge.selectedIndex].value;
        var deliveryType = document.getElementsByName("transport");
        var account = document.getElementById("account");
        var accountnumber = document.getElementById("accountnumber");
        var payfor = document.getElementById("hiddenoptional");
        
        if (selected == 'Personal Copies') {
            account.style.display = "none";
            payfor.style.display = "inline";
            for (var k = 0; k < deliveryType.length; k++) {
                if( deliveryType[k].value == "Pick-up") {
                    deliveryType[k].checked = true;
                } else {
                deliveryType[k].disabled = true;
                }
            }
            accountnumber.className = 'input';
            accountnumber.minlength = '0'; 
            $(document).ready(function(){
                $("#copyForm").validate();
            });

        } else if (selected == 'Account') {
            for (var k = 0; k < deliveryType.length; k++) {
                deliveryType[k].disabled = false;
            }
            account.style.display = "block";
            payfor.style.display = "none";
            accountnumber.className = 'input required digit';
            accountnumber.minlength = '9';
            $(document).ready(function(){
                $("#copyForm").validate();
            });
            
        } else if (selected == 'Praise Gathering' || selected == 'Delta Kappa' || selected == 'SMB' || selected == 'SIFE') {
             for (var k = 0; k < deliveryType.length; k++) {
                if( deliveryType[k].value == "Pick-up") {
                    deliveryType[k].checked = true;
                } else {
                deliveryType[k].disabled = true;
                }
            }
            account.style.display = "block";
            payfor.style.display = "none";
            accountnumber.className = 'input required digit';
            accountnumber.minlength = '9';
            $(document).ready(function(){
                $("#copyForm").validate();
            });
        } else {
            for (var k = 0; k < deliveryType.length; k++) {
                deliveryType[k].disabled = false;
            }
            account.style.display = "none";
            payfor.style.display = "none";
            accountnumber.className = 'input';
            accountnumber.minlength = '0';
            $(document).ready(function(){
                $("#copyForm").validate();
            });
        }
        deliverTo();
    }
