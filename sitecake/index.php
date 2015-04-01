<?php include "sitecake/server/sitecake_entry.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords"  content="SiteCake,demo template, demo CMS, page, editor, drag, drop, easy, simple" />
<meta name="description" content="SiteCake, simple CMS for small scale websites - Demo Template" />
<meta name="allow-search" content="yes" />
<meta name="robots" content="all, index, follow" />
<meta name="copyright" content="SiteCake" />
<title>SiteCake, simple CMS for small scale websites - Demo Template</title>
<link href="css/reset.css" rel="stylesheet" type="text/css" />
<link href="css/screen.css" rel="stylesheet" type="text/css" />
<link href="css/rounded.css" rel="stylesheet" type="text/css" /> 
<script type="text/javascript" src="js/jquery-1.5.1.min.js"></script> 
<script type="text/javascript">
	$(function() {
		if ( window.sitecakeGlobals && sitecakeGlobals.editMode !== true ) {
			$.getScript('js/rounded.js');
		}
	});
</script>

<!--[if IE]><link href="css/ie.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/pngfix.js"></script><![endif]-->
<link rel="Shortcut Icon" href="favicon.ico" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
</head>
<body>
<div class="wrapper">

	<div id="left">
		<div class="logo">
			<p><a href="index.php">Tourist Demo</a></p>
		</div>
	</div><!--left-->


	<div id="content">
	
		<ul class="main-nav">
			<li><a href="index.php">Home</a></li>
			<li><a href="venice.php">Destinations</a></li>
			<li><a href="#">Contact</a></li>
		</ul>
		
		<div class="social"> 
			 <a href="http://twitter.com/sitecake">
				<img src="images/icon-twitter.gif" width="26" height="25" />
			</a> 
			<a href="http://facebook.com/sitecake" target="_blank">
				<img src="images/icon-facebook.gif" width="26" height="25" />
			</a>
			<p>Follow us</p>
		</div>
		
		<div class="sc-content-article article">
			<h1>Travel With Us</h1>
			<h3 class="gray">Selected destinations from seasoned travelers</h3>
		</div>
		
		<div style="clear:both;"></div>
		
	</div><!--content-->
		
		
		
	<div class="full-width" >
	
		<div id="featured" class="sc-content-featured"> 
		<img src="images/feature.jpg" width="768" height="330" class="rounded"/>
			<h2>Destination of the Month: Santorini</h2>
		</div><!--featured-->
		
		<div id="home-columns">
			<div class="sc-content-hc1 home-column"> <img src="images/travel1.jpg" width="242" height="169" />
				<h3 class="blue">Mallorca, Spain</h3>
				<p>Nonsequi dellabo riberunte sam quia iminven delest, tet officabo. Conse sunt essi dipsa volore volutem ea prae.</p>
			</div>
			
			<div class="sc-content-hc2 home-column"> <img src="images/travel3.jpg" width="242" height="169" />
				<h3 class="blue">Santorini, Greece</h3>
				<p>Nonsequi dellabo riberunte sam quia iminven delest, tet officabo. Conse sunt essi dipsa volore volutem ea prae.</p>
			</div>
			
			<div class="sc-content-hc3 home-column" style="margin-right:0;"> <img src="images/travel2.jpg" width="242" height="169" />
				<h3 class="blue">Venice, Italy</h3>
				<p>Nonsequi dellabo riberunte sam quia iminven delest, tet officabo. Conse sunt essi dipsa volore volutem ea prae.</p>
			</div>

		<div style="clear:both;"></div>

		</div><!--home-columns-->
		
		
		<div id="footer">
			<p><a href="index.php">Home</a> / <a href="venice.php">Destination</a> / <a href="#">Contact</a> / <a href="#" class="sc-login">Admin Login</a><br />
				This is not real site. Only demo to show how SiteCake CMS is functioning. All rights reserved by SiteCake.com</p>
		</div><!--footer-->
		
	</div><!--full-width-->
	
	<div style="clear:both;"></div>

</div><!--wrapper-->

</body>
</html>