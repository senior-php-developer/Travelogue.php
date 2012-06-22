<?php
include("inc/admin.php");
if (isset($GLOBALS['CURUSER']))	$CURUSER = $GLOBALS['CURUSER'];
if (!$CURUSER[admin]) die;

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
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="css/ie.css">
<![endif]-->
<title>GEOPast Admin Area</title>
<script type="text/javascript">

</script>
</head>
<!-- BODY -->
<body>
<div id="canvas">
	<div id="user-list">
		<input type="text"> <select><?=dropdown_letters();?></select><br>
		<div id="user-names"></div>
		<div id="edit-panel"></div>
	</div>
	
	<div id="manage-panel">
		<div id="user-info"></div>
		<div id="user-photos"></div>
	</div>
	
	<div class="clr">&nbsp;</div>
</div>
<!-- JAVASCRIPT -->
<script type="text/javascript" src="js/jquery/jquery.js"></script>
<script type="text/javascript" src="js/jquery/jquery.tools.js"></script>
<script type="text/javascript" src="js/admin.js"></script>
<!--[if lt IE 8]>
<script type="text/javascript" src="js/ie8.js"></script>
<![endif]-->
</body>
</html>