<?
include("login.php");
check_login();
if (isset($GLOBALS['CURUSER'])) $CURUSER = $GLOBALS['CURUSER'];

if (!empty($_FILES)) upload_photos();

function get_full_path($id) {
	$tmp = mysql_fetch_assoc(mysql_query("SELECT date, ext FROM photos WHERE id = $id"));
	$date = getdate(strtotime($tmp['date']));
	$path = "../files/$date[year]/$date[mon]/$date[mday]/{$id}.{$tmp[ext]}";
	return $path;
}

function get_thumb_path($id) {
  $tmp = mysql_fetch_assoc(mysql_query("SELECT date, ext FROM photos WHERE id = $id"));
  $date = getdate(strtotime($tmp['date']));
  $path = "../files/$date[year]/$date[mon]/$date[mday]/${id}_t.{$tmp[ext]}";
  return $path;
}

function geo_frac2dec($str) {
	@list($n,$d) = explode('/',$str);
	if (!empty($d)) return $n/$d;
	return $str;
}

function geo_exif2gmap($fracs) {
	return	geo_frac2dec($fracs[0]) +
		geo_frac2dec($fracs[1]) / 60 +
		geo_frac2dec($fracs[2]) / 3600;
}

function upload_photos() {
  //adding to database
  $date = date("Y-m-d");
  $owner = $_REQUEST['user'];
  $title = $_FILES['Filedata']['name'];
  $temp_file = $_FILES['Filedata']['tmp_name'];
  $info = pathinfo($title);
  $year = date("Y");
  $exif = @exif_read_data($temp_file,'GPS');
  if (empty($exif['GPSLatitude'])) {
  	$lat = 0; $lng = 0;
		$dot = 'hid';	
  } else {
	  $lat = geo_exif2gmap($exif['GPSLatitude']);
	  $lng = geo_exif2gmap($exif['GPSLongitude']);
	  if ($exif['GPSLatitudeRef']=='S') $lat *= -1;
	  if ($exif['GPSLongitudeRef']=='W') $lng *= -1;
  }
  mysql_query("INSERT INTO photos VALUES(null, '$owner','$date','$title','','$year','','','','$lat','$lng','$info[extension]')") or die("database error");
  $id = mysql_insert_id();
  $target = get_full_path($id);
  @mkdir(dirname($target), 0755, true);
  @move_uploaded_file($temp_file,$target);
  make_thumb($id);
	$target = str_replace('..','',$target);
  //showing uploaded photos
  $thumb = str_replace(basename($target),str_replace('.','_t.',basename($target)),$target);
  print("<div class='img_container' gid='$id' onclick=\"edit_photo('$id');\" style='background: url($thumb) no-repeat;background-position: center;'><img class='dot $dot' src='img/blue-dot.png'></div>");
}

function show_edit_photo() {
	$id = mysql_real_escape_string($_POST[id]);
	$tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM photos WHERE id = '$id'"));
	if ($tmp)
		print("<input type='hidden' name='action' value='edit'>");
	else 
		print("<input type='hidden' name='action' value='add'>");
	print("<input type='hidden' name='id' value='$id'>
				<table>
				<tr>
					<td colspan=2>Title<br><input type='text' name='title' value='$tmp[title]' style='width: 99%;'></td>
					<td>Year<br><input type='text' name='year' value='$tmp[year]' style='width: 80px;'></td>
					<td rowspan=3>Description<br><textarea name='descr' style='vertical-align: baseline; height: 110px; width: 220px;'>$tmp[descr]</textarea></td>
				</tr>
			   <tr>
			   	 <td>Country<br><input type='text' name='country' value='$tmp[country]'></td>
			   	 <td>City<br><input type='text' name='city' value='$tmp[city]'></td>
			   	 <td>Zip Code<br><input type='text' name='zip' value='$tmp[postcode]' style='width: 80px;'></td>
			   </tr>
			   <tr>
			   	 <td>Latitude<br><input type='text' name='lat' value='$tmp[lat]'></td>
			   	 <td>Longitude<br><input type='text' name='long' value='$tmp[long]'></td>
			   	 <td style='vertical-align: bottom;'><img src='img/search.png' onclick='open_search()' style='margin:5px; cursor: pointer;'><img src='img/save.png' onclick='save_photo()' style='margin:5px; cursor: pointer;'></td>
			   </tr>
			   </table>
			   
			  <div id='helper'>
			  	<ul>
			  		<li>Input title for a photo instead of it's filename</li>
			  		<li>Provide description which tells about story behind the photo</li>
			  		<li>Enter year or decade ('1950s') which corresponds to events on the photo</li>
			  		<li>Type first characters of country or city and select the result from list</li>
			  		<li>Click on the map to precise the location of event and fill lat and long fields</li>
			  	</ul>
			  </div>
			  <br clear='both'>
				 ");
}

function save_photo() {
	foreach($_REQUEST as $k => $v) {
		$$k = mysql_real_escape_string($v);
  }
	mysql_query("UPDATE photos SET title = '$title', descr = '$descr', year = '$year', country = '$country', city = '$city', postcode = '$zip', lat = '$lat', `long` = '$long' WHERE id = '$id'") or die("database error");
	print("changes saved");
}

function make_thumb($id) {
  $jpeg_quality = 70;
  
  // source and destination
  $src = get_full_path($id);
  $dst = str_replace(basename($src),str_replace('.','_t.',basename($src)),$src);
  $system = explode('.',basename(strtolower($src)));
  
  //dimensions
  list($w, $h) = getimagesize($src);
  if ($w > $h) {
  	//$targ_w = 100; $targ_h = 100*($h/$w); 
  	$main_side = $h;
  	$src_x = ($w - $h) / 2;
  	$src_y = 0;
  }
  if ($w < $h) {
  	//$targ_h = 100; $targ_w = 100*($w/$h); 
  	$main_side = $w;
  	$src_x = 0;
  	$src_y = ($h - $w) / 2;
  }
  
  if ($w == $h) {
  	//$targ_w = 100; $targ_h = 100;
  	$main_side = $w;
  	$src_x = 0;
  	$src_y = 0;
  }
  
  //creating image
  if (preg_match('/jpg|jpeg/',$system[1]))
    $img_r=imagecreatefromjpeg($src);
  if (preg_match('/png/',$system[1])) 
    $img_r=imagecreatefrompng($src);
  
  $dst_r = ImageCreateTrueColor(100, 100);
  imagecopyresampled($dst_r,$img_r,0,0,$src_x,$src_y,100,100,$main_side,$main_side);
  
  if (preg_match('/jpg|jpeg/',$system[1]))
    imagejpeg($dst_r,$dst,$jpeg_quality);
  if (preg_match('/png/',$system[1])) 
    imagepng($dst_r,$dst);
}

function get_markers() {
	$start = 0;
	if (!empty($_REQUEST[start])) $start = mysql_real_escape_string($_REQUEST[start]);
	header('Content-Type: application/xml; charset=UTF-8');
  foreach ($_POST as $k => $v) 
		$$k = mysql_real_escape_string($v);
	if ($max < $mix) $max = 180;
	if ($may < $miy) $may = 180;
  $res = mysql_query("SELECT u.first_name, u.last_name, p.* FROM photos p, users u WHERE p.lat > '$miy' AND p.lat < '$may' AND p.long > '$mix' AND p.long < '$max' AND p.lat <> 0 AND p.long <> 0 AND p.owner = u.id ORDER BY id DESC LIMIT 80");
	
  if (mysql_num_rows($res) > 0) {
    print('<?xml version="1.0" encoding="utf-8" ?><markers>');  
    while ($tmp = mysql_fetch_assoc($res)) {
      $t = get_thumb_path($tmp[id]);
      $title = $tmp[title];
      if (strlen($tmp[title]) > 22) $title = substr($tmp[title],0,20).'..';
      if (!file_exists($t)) $t = get_full_path($tmp[id]);
      $html = htmlentities("<div class='mark_img' style='background: url($t) no-repeat center center' onclick='open_photo(\"$tmp[id]\");' title='$tmp[title]' lat='$tmp[lat]' lng='$tmp[long]'></div>");
      print('<marker lng="'.$tmp[long].'" lat="'.$tmp[lat].'" id="'.$tmp[id].'">'.$html.'</marker>');  
    }
    print('</markers>');
  }  
}

function get_countries() {
	header('Content-Type: application/xml; charset=UTF-8');
	$res = mysql_query("SELECT id, lat, `long` FROM photos GROUP BY city");
		
  if (mysql_num_rows($res) > 0) {
    print('<?xml version="1.0" encoding="utf-8" ?><markers>');  
    while ($tmp = mysql_fetch_assoc($res)) {
      $t = get_thumb_path($tmp[id]);
      $title = $tmp[title];
      if (strlen($tmp[title]) > 22) $title = substr($tmp[title],0,20).'..';
      if (!file_exists($t)) $t = get_full_path($tmp[id]);
      $html = htmlentities("<div class='mark_img' style='background: url($t) no-repeat center center' onclick='open_photo(\"$tmp[id]\");' title='$tmp[title]' lat='$tmp[lat]' lng='$tmp[long]'></div>");
      print('<marker lng="'.$tmp[long].'" lat="'.$tmp[lat].'" id="'.$tmp[id].'">'.$html.'</marker>');  
    }
    print('</markers>');
  }  
}

function load_photo_info() {
  $id = mysql_real_escape_string($_REQUEST[id]);
  $url = get_full_path($id);
  print("<a id='fs_photo' href='".$url."'><img src='".$url."' style='max-height: 450px; max-width: 640px; border: solid 1px #000'></a>");
}

function show_photo_info() {
  $id = mysql_real_escape_string($_REQUEST[id]);
  $tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM photos WHERE id = '$id'"));
	$descr = stripslashes($tmp['descr']);
  print("$tmp[country] <br> $tmp[city] $tmp[postcode]<br><br><b>$tmp[title]</b><p>$descr</p>");
}

function load_my_photos() {
  $user = $_REQUEST[user];
  $start = mysql_real_escape_string($_POST[start]);
  $res = mysql_query("SELECT id, title FROM photos WHERE owner = '$user' ORDER BY id DESC LIMIT $start, 40");
  $html = '';
  while ($tmp = mysql_fetch_assoc($res)) {
    $t = get_thumb_path($tmp[id]);
    if (!file_exists($t)) $t = get_full_path($tmp[id]);
    $html .= "<div class='mark_img' title='$tmp[title]' style='background: url($t) no-repeat center center' onclick='edit_photo(\"$tmp[id]\");'></div>";
  }
  print($html);
}

function get_count() {
  if ($_GET[type]) {
    $user = $_REQUEST[user];
    $tmp = mysql_fetch_assoc(mysql_query("SELECT COUNT(id) as count FROM photos WHERE owner = '$user'"));
  } else {
    foreach($_POST as $k => $v) $$k = mysql_real_escape_string($v);
    $tmp = mysql_fetch_assoc(mysql_query("SELECT COUNT(p.id) as count FROM photos p, users u WHERE p.lat > '$miy' AND p.lat < '$may' AND p.long > '$mix' AND p.long < '$max' AND p.lat <> 0 AND p.long <> 0 AND p.owner = u.id"));
  }
  print($tmp[count]);
}

function global_search() {
	$type = $_POST[type];
	$q = mysql_real_escape_string($_POST[q]);
	switch ($type) {
		case 'loc': $sql_where = "(p.country LIKE '%$q%' or p.city LIKE '%$q%' or p.postcode LIKE '%$q%')"; break;
		case 'date': $sql_where = "(p.year LIKE '%$q%')"; break;
		case 'author': $sql_where = "(u.first_name LIKE '%$q%' or u.last_name LIKE '%$q%') ORDER BY u.id ASC"; break;
	}
	$res = mysql_query("SELECT DISTINCT p.id as id, p.country as country, p.year as year, p.title as title, p.city as city, p.postcode as zip, p.lat as lat, p.long as lng, u.first_name as fname, u.last_name as lname FROM photos p, users u WHERE p.owner = u.id AND $sql_where LIMIT 20");
	
	if (mysql_num_rows($res) == 0) {
		print("Nothing was found :("); 
		die;
	}
	while ($tmp = mysql_fetch_assoc($res)) {
		$t = get_thumb_path($tmp[id]);
    if (!file_exists($t)) $t = get_full_path($tmp[id]);
    $title = $tmp[title];
    if (strlen($tmp[title]) > 22) $title = substr($tmp[title],0,20).'..';
    $html .= "<div gid='$tmp[id]'><div class='mark_img' title='$tmp[title]' style='background: url($t) no-repeat center center' onclick='open_photo(\"$tmp[id]\");' lat='$tmp[lat]' lng='$tmp[lng]'></div></div>";
	}
	print($html);
}

if ($_GET['do']=='show_edit_photo') show_edit_photo();
if ($_GET['do']=='save_photo') save_photo();
if ($_GET['do']=='get_markers') get_markers();
if ($_GET['do']=='get_countries') get_countries();
if ($_GET['do']=='open_photo') load_photo_info();
if ($_GET['do']=='photo_info') show_photo_info();
if ($_GET['do']=='load_my_photos') load_my_photos();
if ($_GET['do']=='get_count') get_count();
if ($_GET[f]=='globSearch') global_search();

?>