<?php
include("login.php");
check_login();
if (isset($GLOBALS['CURUSER'])) $CURUSER = $GLOBALS['CURUSER'];
if (($CURUSER[id] == '1') || ($CURUSER[id] == '2')) $CURUSER[admin] = true; else $CURUSER[admin] = false;

if (!$CURUSER[admin]) die;

if ($_GET[f]=='lastUsers') show_last_users();
if ($_GET[f]=='userInfo') show_user_information();
if ($_GET[f]=='userPhotos') show_user_photos();
if ($_GET[f]=='editUser') show_edit_user();
if ($_GET[f]=='editPhoto') show_edit_photo();
if ($_GET[f]=='saveUser') save_edit_user();
if ($_GET[f]=='savePhoto') save_edit_photo();
if ($_GET[f]=='delUser') delete_user();
if ($_GET[f]=='delPhoto') delete_photo();

function get_full_path($id) {
	$tmp = mysql_fetch_assoc(mysql_query("SELECT date, ext FROM photos WHERE id = '$id'"));
	$date = getdate(strtotime($tmp['date']));
	$path = "../files/$date[year]/$date[mon]/$date[mday]/$id.$tmp[ext]";
	return $path;
}

function get_thumb_path($id) {
  $tmp = mysql_fetch_assoc(mysql_query("SELECT date, ext FROM photos WHERE id = '$id'"));
  $date = getdate(strtotime($tmp['date']));
  $path = "../files/$date[year]/$date[mon]/$date[mday]/${id}_t.$tmp[ext]";
  return $path;
}

function show_last_users() {
	print("<ul>");
	$res = mysql_query("SELECT id, first_name, last_name FROM users ORDER BY id DESC LIMIT 30");
	while ($tmp = mysql_fetch_assoc($res)) {
		print("<li><a href='javascript:void(0)' onclick='showInfo(\"$tmp[id]\")'>$tmp[first_name] $tmp[last_name]</a></li>");
	}
	print("</ul>");
}

function dropdown_letters() {
	foreach(range('A','Z') as $k => $v) {
		print("<option>$v</option>");
	}
}

function show_user_information() {
	$id = mysql_real_escape_string($_GET[id]);
	$tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE id = '$id'"));
	print("<input type='hidden' name='id' value='$id'><b>$tmp[first_name] $tmp[last_name]</b> ($tmp[email]) - $tmp[country]<br><br>");
}

function show_user_photos() {
	$id = mysql_real_escape_string($_GET[id]);
	$res = mysql_query("SELECT * FROM photos WHERE owner = $id ORDER BY id DESC");
  $html = '';
  while ($tmp = mysql_fetch_assoc($res)) {
    $t = get_thumb_path($tmp[id]);
    if (!file_exists($t)) $t = get_full_path($tmp[id]);
    $html .= "<div class='mark_img' title='$tmp[title]' style='background: url($t) no-repeat center center' onclick='editPhoto(\"$tmp[id]\");'></div>";
  }
  print($html);
}

function show_edit_user() {
	$id = mysql_real_escape_string($_GET[id]);
	$tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE id = '$id'"));
	print("<input type='hidden' name='id' value='$tmp[id]'>
		     First Name<br /><input type='text' name='fname' value='$tmp[first_name]'><br>
		     Last Name<br /><input type='text' name='lname' value='$tmp[last_name]'><br>
		     E-mail<br /><input type='text' name='email' value='$tmp[email]'><br>
		     Country<br /><input type='text' name='country' value='$tmp[country]'><br>
		     <button onclick='saveUser()'>Save</button><button onclick='delUser()'>Delete</button>");
}

function show_edit_photo() {
	$id = mysql_real_escape_string($_GET[id]);
	$t = get_thumb_path($id);
	if (!file_exists($t)) $t = get_full_path($id);
	$tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM photos WHERE id = '$id'"));
	print("<input type='hidden' name='id' value='$tmp[id]'>
				 <center><img src='$t'/></center><br />
				 Title<br /><input type='text' name='title' value='$tmp[title]'><br />
				 Year<br /><input type='text' name='year' value='$tmp[year]'><br />
				 Country<br /><input type='text' name='country' value='$tmp[country]'><br />
				 City<br /><input type='text' name='city' value='$tmp[city]'><br />
				 Postal Code<br /><input type='text' name='zip' value='$tmp[postcode]'><br />
				 Description<br /><textarea rows=5 name='descr'>$tmp[descr]</textarea><br />
				 <button onclick='savePhoto()'>Save</button><button onclick='delPhoto()'>Delete</button>");
}

function save_edit_user() {
	foreach($_POST as $k => $v)
		$$k = mysql_real_escape_string($v);
	mysql_query("UPDATE users SET first_name = '$fname', last_name = '$lname', email = '$email', country = '$country' WHERE id = '$id'") or die("error occured");
	print("saved successfully");
}

function save_edit_photo() {
	foreach($_POST as $k => $v)
			$$k = mysql_real_escape_string($v);
		mysql_query("UPDATE photos SET title = '$title', year = '$year', country = '$country', city = '$city', postcode = '$zip', descr = '$descr' WHERE id = '$id'") or die("error occured");
		print("saved successfully");
}

function delete_user() {
	$id = mysql_real_escape_string($_POST[id]);
	mysql_query("DELETE FROM users WHERE id = '$id'");
	print("deleted successfully");
}

function delete_photo() {
	$id = mysql_real_escape_string($_POST[id]);
	mysql_query("DELETE FROM photos WHERE id = '$id'");
	print("deleted successfully");
}

?>