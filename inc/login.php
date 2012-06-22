<?php
include("db.php");
require_once('recaptchalib.php');

function check_login() {
  $user = $_COOKIE['user'];
  $password = $_COOKIE['pass'];
  $res = mysql_query("SELECT * FROM users WHERE id = '$user' AND password = '$password'");
  if (mysql_num_rows($res) > 0) {
    $GLOBALS['CURUSER'] = mysql_fetch_assoc($res);
  }
}

function show_countries() {
	$countries = array('Albania','Bhutan','Cairo','Denmark','Egypt','France');
	print('<select name="country">');
	foreach($countries as $k => $v)
		print("<option>$v</option>");
	print('</select>');
}

function register() {
	foreach ($_POST as $key => $value) {
    $$key = mysql_real_escape_string($value);
  } 
  $privatekey = "6LfMuQYAAAAAAML4yxRj0tDya0_xtBm_VUGyJaj0";
	$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"],$recaptcha_challenge_field,$recaptcha_response_field);
	if (!$resp->is_valid) die ("captcha entered incorrectly");
  $passhash = md5(md5($mail).md5($pass));
  mysql_query("INSERT INTO users VALUES (null, '$mail','$passhash', '0', '$fname', '$lname', '$country', '0')") or die("database error");
  $id = mysql_insert_id();
  // writing email
  $to = $mail;
  $subject = "<GEO PHOTOS> - registration confirmation";
  $headers = "From: noreply@geophotos.com\r\n"."MIME-Version: 1.0\r\n"."Content-type: text/html; charset=iso-8859-1\r\n";
  $body = 'To complete your registration please follow this <a href="http://geopast.airy.me/inc/login.php?do=confirm&user='.$id.'&str='.$passhash.'">link</a>.<br> Here is your registration information, do not lose it.<br> e-mail:'.$mail.'<br> password:'.$pass;
  if (mail($to,$subject,$body,$headers))
  	print("confirmation e-mail sent");
  else
  	print("couldn't register, try later");
}

function confirm_reg() {
	$hash = $_GET['str'];
	$user = $_GET['user'];
	$realhash = mysql_fetch_assoc(mysql_query("SELECT password FROM users WHERE id = $user"));
	if ($realhash['password'] == $hash) {
		mysql_query("UPDATE users SET active = '1' WHERE id = '$user'");
		setcookie("user", $user, 0x7fffffff, "/");
    setcookie("pass", $hash, 0x7fffffff, "/");
		header('Location: ../index.php');
	}
}

function login() {
  $mail = mysql_real_escape_string($_POST['mail']);
  $pass = mysql_real_escape_string($_POST['pass']);
  $password = md5(md5($mail).md5($pass));
  $res = mysql_query("SELECT id FROM users WHERE email = '$mail' AND password = '$password' AND active = '1' LIMIT 1");  
  if (mysql_num_rows($res) > 0) {
  	$tmp = mysql_fetch_assoc($res);
    setcookie("user", $tmp['id'], 0x7fffffff, "/");
    setcookie("pass", $password, 0x7fffffff, "/");
    print("login successful");
  } else {
		$res2 = mysql_query("SELECT * FROM users WHERE email = '$mail' AND password = '$password' LIMIT 1");
		if (mysql_num_rows($res2) > 0)
			print("account not activated");
		else
			print("password incorrect");
  } 
}

function logout() {
  setcookie("user", "", 0x7fffffff, "/");
  setcookie("pass", "", 0x7fffffff, "/");
  print("logout successful");
}

function reset_pwd() {
	$mail = mysql_real_escape_string($_POST['mail']);
	$confirmstr = generator();
	$subject = "<GEO PHOTOS> - password reset confirmation";
	mysql_query("UPDATE users SET `change` = '$confirmstr' WHERE email = '$mail'");
	$res = mysql_query("SELECT id FROM users WHERE email = '$mail'");
	if (mysql_num_rows($res) == 0) die("wrong email provided");
  $tmp = mysql_fetch_assoc($res);
	foreach($tmp as $key => $val)
		$$key = $val;
	$body = "Confirmation code to change password for $mail is: $confirmstr";
	$headers = "From: noreply@geophotos.com\r\n"."MIME-Version: 1.0\r\n"."Content-type: text/html; charset=iso-8859-1\r\n";
	if (mail($mail,$subject,$body,$headers))
		print('confirmation code sent to email');
	else print('error sending email'); 
}

function generator() {
   list($usec, $sec) = explode(' ', microtime());
   srand((float) $sec + ((float) $usec * 100000));
   $validchars = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
   $password  = "";
   $counter   = 0;
   while ($counter < 8) {
     $actChar = substr($validchars, rand(0, strlen($validchars)-1), 1);
     if (!strstr($password, $actChar)) {
        $password .= $actChar;
        $counter++;
     }
   }
   return $password;
}

function change_pwd() {
	foreach($_POST as $key => $val)
		$$key = mysql_real_escape_string($val);
	$res = mysql_query("SELECT id FROM users WHERE email = '$mail' AND `change` = '$code' LIMIT 1");
	if (mysql_num_rows($res) > 0)	{
		$tmp = mysql_fetch_assoc($res);
		$password = md5(md5($mail).md5($pass)); 
		mysql_query("UPDATE users SET password = '$password' WHERE id = ".$tmp['id']);
		setcookie("user", $tmp['id'], 0x7fffffff, "/");
    setcookie("pass", $password, 0x7fffffff, "/");
		print("ok");
	} else
		print("incorrect confirmation code");
}

if ($_GET['do']=='register') register();
if ($_GET['do']=='confirm') confirm_reg();
if ($_GET['do']=='login') login();
if ($_GET['do']=='logout') logout();
if ($_GET['do']=='reset') reset_pwd();
if ($_GET['do']=='change') change_pwd();
?>