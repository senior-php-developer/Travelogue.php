<?php
include("inc/main.php");
check_login();
if (isset($GLOBALS['CURUSER']))	$CURUSER = $GLOBALS['CURUSER'];

$start_loc = mysql_fetch_assoc(mysql_query("SELECT lat, `long` FROM photos WHERE lat <> 0 AND `long` <> 0 ORDER BY id DESC LIMIT 1"));
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Content-Language" content="en"/>
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Pragma" content="no-cache">
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="css/reset.css"/>
<link rel="stylesheet" type="text/css" href="css/main.css"/>
<link rel="stylesheet" type="text/css" href="css/lightbox.css"/>
<link rel="stylesheet" type="text/css" href="plugin/uploadify/uploadify.css"/>
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="css/ie.css">
<![endif]-->
<title><?=$title?></title>
<script type="text/javascript">
	var cur_user = <?=$CURUSER['id'] ? $CURUSER['id'] : '0'?>;
	var startLat = <?=$start_loc[lat] ? $start_loc[lat] : '50'?>;
	var startLng = <?=$start_loc[long]? $start_loc[long] : '30' ?>;
</script>
</head>
<!-- BODY -->
<body>
<div id="canvas" style="visibility: hidden;">
	<div id="map"></div>
	<div id="tool_box" class="brd10">
		<ul class="tabs"> 
			<li><a href="#" class="tc5">Explore</a></li> 
    	<li><a href="#" class="tc5">Contribute</a></li> 
		<? if ($CURUSER) {  ?>				
			<li><a href="#" class="tc5">Account</a></li> 
		<? } ?>
		</ul> 
 
 		<div class="panes">
 			<div id="explore">				
 				<div id="browse"></div>
				<button class="b_submit" id="but-update">Refresh</button>
 			</div> 
  
    	<div id="manage">
	 <? if (!$CURUSER) {  ?>
	 			<h4>Authorize!</h4><br/>
	 			Please <button id="login" class="b_submit">Sign In</button> or <button id="reg" class="b_submit">Sign Up</button>
 	 <? } else { ?>
	 		  <button class="b_submit active" id="but-upload">Upload</button><input type="file" name="fileMulti" id="fileMulti"/>
        
				<div id="my-photos"></div>
				<div id="upload-files"></div>
				<div id="upload-buts" style="display:none;"><button class="b_submit lt" id="but-start" onclick="$('#fileMulti').uploadifyUpload();">Start Upload</button><button class="b_submit rt" id="but-clear" onclick="$('#fileMulti').uploadifyClearQueue();$('#upload-buts').hide();">Clear Queue</button><div class="clr">&nbsp;</div></div>
				<hr noshade="noshade"/>
        <center><a href="javascript:void(0)" onclick="go_my_back()" class="but-back no-link">&lt;&lt;</a> | <a href="javascript:void(0)" onclick="go_my_forw()" class="but-forw">&gt;&gt;</a></center>
        <input type="hidden" id="my-count"/><input type="hidden" id="my-now" value="0"/>
	 <? } ?>
	    </div> 
		
	<? if ($CURUSER) {  ?>		
			<div id="account">
			<button class="b_submit" title="Logout" id="logout" class="rt ptr">Logout</button>
			</div>
	<? } ?>		
		</div>
	</div>
</div>
</body>

<!-- DIALOGS -->
<div class="d_box hid brd10" id="d_reg">
	Your e-mail<br/>
	<input type="text" class="blur" name="mail"/><br/>
	First name<br/>
	<input type="text" class="blur" name="fname"/><br/>
	Last name<br/>
	<input type="text" class="blur" name="lname"/><br/>
	Choose password<br/>
	<input type="password" class="blur" name="pass"/><br/>
	Country <? show_countries(); ?> <br/><br />
	<? require_once('inc/recaptchalib.php');
	 $publickey = "6LfMuQYAAAAAANwkeykCqj7_Truw2vW-bGHELgS8"; 
	 echo recaptcha_get_html($publickey);
	?>
	<button class="b_submit lt">Register</button> <button class="b_cancel rt">Cancel</button> 
</div>

<div class="d_box hid brd10" id="d_login">
	Your e-mail<br/>
	<input type="text" class="blur" name="mail"/><br/>
	<span id="password">
		<span>Your password</span><br/>
		<input type="password" class="blur" name="pass"/><br/>
	</span>
	<span class="hid" id="code">
		Provide confirmation code<br/>
		<input type="text" class="blur" name="code"/><br/>
	</span><br/>
	<button class="b_submit lt" id="b_login">Login</button> 
	<button class="b_submit lt hid" id="b_reset">Reset</button> 
	<button class="b_submit lt hid" id="b_change">Change</button> 
	<button class="b_cancel rt">Cancel</button><br/>
	<div class="clr"></div>
	<a href="#" onclick="show_reset_dlg(); return false;">Lost password?</a> 
</div>

<div id="photo_info">
  <div class="controls hid"></div>
  <div class="content hid"><div class="close"></div><div class="files"></div></div>
</div>

<div id="photo-edit" class="hid brd10"></div>

<div class="overlay brd10" id="bigphoto"><div class="content"><div class="info hid tc10"></div><div class="photo"></div></div></div>

<div id="photo-preview"></div>

<div class="hid bc5" id="p_info"></div>
<div class="hid" id="p_ajax"><img src="img/ajaxload.gif"/></div>

<!-- JAVASCRIPT -->
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAA8VI8LSA4OV5Tr1tOIoo0VRQSReJvjRv_JsPezJ3yQTlZN5ssUxT0z422NseBf4qGWo2lqmWxp5eZ5Q" type="text/javascript"></script>
<script type="text/javascript" src="js/jquery/jquery.js"></script>
<script type="text/javascript" src="js/jquery/jquery.jmap.js"></script>
<script type="text/javascript" src="js/jquery/jquery.tools.js"></script>
<script type="text/javascript" src="js/jquery/jquery.lightbox.js"></script>
<script type="text/javascript" src="js/jquery/jquery.corner.js"></script>
<script type="text/javascript" src="plugin/uploadify/swfobject.js"></script>
<script type="text/javascript" src="plugin/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript" src="js/main.js"></script>
<script type="text/javascript" src="js/login.js"></script>
<!--[if lt IE 8]>
<script type="text/javascript" src="js/ie8.js"></script>
<![endif]-->
</html>