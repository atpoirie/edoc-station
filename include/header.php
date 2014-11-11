<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<HTML>
<HEAD>
   <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
	<link rel="icon" type="image/png" href="https://www.northcentral.edu/unet/include/favicon.ico">
	<link rel="stylesheet" type="text/css" href="https://www.northcentral.edu/unet/include/ncu.css" />
	<script src="/unet/include/jquery.js"></script>
	<script src="/unet/include/jquery.tweet.js"></script>
	<script type="text/javascript">
		jQuery(function($){
			$(".tweet").tweet({
				username: "ncuit",
				avatar_size: 1,
				count: 4,
				loading_text: "Loading tweets...",
				template: function(i){ 
					var temp;
					temp = i["text"] + "<br>" + i["time"] + ' - ' + i["reply"] + ' - ' + i["retweet_url"] + ' - ' + i["fav"];
					return temp;
				}
			});
		});
	</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-30781745-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

  <?php
	$studentmenu = "/unet/student.php";
	$employeemenu = "/unet/employee.php";


  echo "<TITLE>Unet - $pagetitle</TITLE>"
  ?>

</HEAD>
<BODY>

<map name="header">

	<area alt="Unet Main" shape="rect" coords="30, 5, 220, 70" href="/">

	<area alt="Unet Guy" shape="rect" coords="480, 15, 630, 60" href="/unet">
</map>

<div id="ncu_header">
</div>
<div id="ncu_bar">
</div>
<div id="container">
<div id="ncu_sidebar">
<ul class="level1">
	<?if ( isset($menu) && $menu == "Student" ) { ?>
		<li class="level2"><a class="ncu_sidebar active" href="/unet/student.php">Student Menu</a></li>
	<? } else {?>
		<li class="level2"><a class="ncu_sidebar" href="/unet/student.php">Student Menu</a></li>
	<? } if ( isset($menu) && $menu == "Employee" ) { ?>
		<li class="level2"><a class="ncu_sidebar active" href="/unet/employee.php">Employee Menu</a></li>
	<? } else { ?>
		<li class="level2"><a class="ncu_sidebar" href="/unet/employee.php">Employee Menu</a></li>
	<? } 
	include("otherlinks.php");?>

</ul>
<hr>

<br/>
<div class="tweet">
<a href="http://twitter.com/intent/user?screen_name=ncuit"><img src="https://s3.amazonaws.com/twitter_production/profile_images/82807390/images_normal.jpg" align="middle"></a>
<strong><big>Tech Support</big></strong>
</div>
</div>
<div id="ncu_content">
<div id="ncu_login">
<?php

include_once "/var/authscripts/ncu_auth.inc";

if(! ncu_loginpages() ){
	if ( $uname = ncu_getusername() ) {
		echo "Logged in as <strong>$uname</strong>.\n";
		echo "<a href='/unet/login/logout.php'>Logout</a>\n";
	}
	else {
		echo "Not logged in.\n";
		echo "<a href='/unet/login/login.php?beta=yes'>Login</a>\n";
	}
}
?>
</div>
<!--<hr>-->
<?php

require_once "DB.php";

$dsn = array(
	'phptype' => 'mssql',
	'username' => 'aa_unet_menu',
	'password' => 'Mp1+Alk4TZEyuQ3hP1Nh',
	'hostspec' => 'sage.northcentral.edu',
	'database' => 'ITdb',
	);

$options = array();

$menudb =& DB::connect($dsn, $options);
if (PEAR::isError($menudb)) {
	die($db->getMessage());
}





function printMenu($db, $name, $showhelp){
	$query = "exec unet_getMenu_header '$name'";
	$res =& $db->query($query);
	if (PEAR::isError($res)) {
		die($res->getmessage());
	}
	$heading = "";
	echo "<br>";
	while ($row =& $res->fetchRow() ){
		if (isset($row[3]) && $row[3] != "NULL" ){	
			if (isset($heading) && $heading != $row[3]) {
				$heading = $row[3];
				echo "</ul>";
				echo ($row[4] ? "<img src='/unet/include/".$row[4]."'/>" : "");
				echo "<strong class='level1'>$heading</strong>";
				echo "<ul>\n";
	
			}
		}
		
		$item = $row[0];
		$url = $row[1];
		$helpURL = $row[2];
		echo "<li><a href='$url'>$item</a></li>\n";
		if ($showhelp){
			echo " <a href='$helpURL'><em>More Information</em></a>";
		}
	}
}
?>
