$(init_index);
var gmap;
var box;
var map_event;
var marker;
var arrow;

function init_index() {
	$.ajaxSetup({
		beforeSend: function(){$("#p_ajax").show();},
    success: function(){$("#p_ajax").hide();},
    complete: function(){$("#p_ajax").hide();}
  });
	init_map();
	init_input();
  init_upload();
	$("#canvas").css('visibility','visible');
	$("#but-update").click(function(){$("#all-now").val(0); load_markers();});
	$("#but-search").click(globalSearch);
  $("#fileMultiUploader").hover(function(){$("#but-upload").addClass('but-hover');},function(){$("#but-upload").removeClass('but-hover');});
  $("#fileMultiUploader").click(function(){$("#my-photos").empty();});
  load_photos();
	$('div.close').live('click', function(){
		$('.overlay').fadeOut();
	});
}

function load_photos() {
  $("#all-now").val('0');
  load_markers();
  $("#my-photos").load('inc/main.php?do=load_my_photos',{start:0});
}

function init_input() {
	$(".blur").focus(function(){$(this).removeClass("blur").addClass("focus");});
	$(".blur").blur(function(){$(this).removeClass("focus").addClass("blur");}); 
	$("#photo_info .content .close").click(function(){
    $("#photo_info").fadeOut("slow",function(){
      $("#photo_info .files").empty();
      $("#but-update").click();
      $("#photo_info .controls").hide();
      $("#photo_info .content").hide();
      $("#photo_info").show();
    });
  });
  box = $("#bigphoto").overlay({api:true, zIndex: 81, fadeInSpeed: 'slow', speed: 0, closeOnClick: false}); 
}

function init_map() {
	var h = $(window).height();
	$('#map').height(h).jmap('init', {'mapType':'hybrid','mapCenter':[startLat, startLng],'mapZoom':4,'mapControl':'large','mapEnableType':true}, function(map,el,opts) {
    gmap = map;
  });
  gmap.enableScrollWheelZoom();
  $(window).resize(function(){
    var h = $(window).height();
    $("#map").height(h).jmap('CheckResize')
  });
	GEvent.addListener(gmap, 'zoomend', function() {
		//load_markers();
	});
  var icon = new GIcon(G_DEFAULT_ICON);
	icon.image = "img/marker-yellow.png";
	var zero = new GLatLng(56, 30);
  arrow = new GMarker(zero, {draggable: false, icon:icon});  
}

function init_upload() {
  $("ul.tabs").tabs("div.panes > div");
  $('#fileMulti').uploadify({'uploader':'plugin/uploadify/uploadify.swf','script':'inc/main.php','cancelImg':'plugin/uploadify/cancel.png','width':'100','height':'30','multi':true,'auto':false, 'fileExt': '*.jpeg;*. jpg;*.png;*.JPG;*.JPEG;*.PNG','fileDesc': 'Image files (.jpg, .png)','queueID': 'upload-files','hideButton': true, 'wmode': 'transparent', 'scriptData': {user:cur_user},
    'onSelectOnce': function() {
      $("#photo_info .files").html('');
      $("#upload-buts").show();
    },
    'onComplete': function(event, queueID, fileObj, response, data){
      $("#photo_info .files").append(response);
    },
    'onAllComplete': function(event, data) {
    	$("#upload-buts").hide();
    	$("#photo_info .controls").hide();
      $("#photo_info .content").show();
      gmap.clearOverlays();
      $("#my-photos").load('inc/main.php?do=load_my_photos',{start:0});
      init_input();
      count_all();
    }
  });  
}

function close_ajax() {
  setTimeout('$("#p_info").slideUp("slow");',2500);
}

var focus_el;
function edit_photo(id) {
  $("#photo_info .img_container").removeClass("sel");
  $("#photo_info div[gid='"+id+"']").addClass("sel");
	$("#photo_info .controls").hide().load('inc/main.php?do=show_edit_photo',{id:id},function(){
		$("#photo_info .controls").show();
    gmap.clearOverlays();
    // if lat and lng present set a marker, otherwise bind a click on the map
    var curLat = $('#photo_info .controls input[name="lat"]').val();
    var curLng = $('#photo_info .controls input[name="long"]').val();
    if ((curLat != 0)||(curLng !=0)) {
    	var curMarker = new GLatLng(curLat, curLng);
    	getCoords(gmap, curMarker);
      gmap.panTo(marker.getPoint());
    } else	map_event = GEvent.addListener(gmap, "click", setPoint);
		$("#photo_info .controls input").focus(function(){focus_el = $(this);});
	});
}

function open_search() {
	$("#helper").html("<input class='input' type='text' name='str' placeholder='enter city, country or postal code separated by comma' style='padding: 4px; font-size: 14px; width: 400px;'> <button class='b_submit' type='submit' onclick='search_loc();'>Search</button> <br> <div id='search_res'></div> ")
}

function search_loc() {
	var q = $("#helper input[name='str']").val();
	$('#map').jmap('SearchAddress', {'query': q, 'returnType': 'getLocations'}, function(result, options) {
		var html = '<ul>';
  	var valid = Mapifies.SearchCode(result.Status.code);
    if (valid.success) {
    	$.each(result.Placemark, function(i, point){		
    		html += '<li><a href="javascript:void(0)" onclick="search_sel(\''+point.Point.coordinates[1]+'\',\''+point.Point.coordinates[0]+'\')">'+point.address+'</a></li>';
      });
    } else {
       html += '<li>Address not found</li>';
    }
    $("#search_res").html(html+'</ul>');
  });
}

function search_sel(lat, lng) {
	$("#photo_info .controls input[name='lat']").val(lat);
	$("#photo_info .controls input[name='long']").val(lng);
	var new_loc = new GLatLng(lat, lng);
	if (marker) {
		marker.setLatLng(new_loc);
		gmap.panTo(new_loc);	
	} else {
		setPoint(gmap, new_loc);	
    gmap.panTo(new_loc);
	}
	var text = $("#search_res a").text().split(',');
  var html = '<ul>Click on the link to paste';
  html += '<li>';
  $.each(text,function(k){
  html += '<a href="javascript:void(0)" onclick="insert_addr(\''+$.trim(text[k])+'\')">'+$.trim(text[k])+'</a>, ';
});
  html += '</li></ul>';
  $("#search_res").html(html);  
}

function save_photo() {
  $("#photo_info .controls").hide();
	var data = {};
	$("#photo_info :input").each(function(){
		data[$(this).attr('name')] = $(this).val();
	});
	if ((data['lat']!=0) && (data['long']!=0))
		$("#photo_info .files .sel .dot").show();
	$.post('inc/main.php?do=save_photo',data,function(reply){
    $("#p_info").text(reply).slideDown("slow",close_ajax);
	});
}

function setPoint(overlay, latlng) {
	GEvent.removeListener(map_event);
	getCoords(overlay, latlng);
}

function getCoords(overlay, latlng) {
  if (latlng != null) {
	  var str = latlng.toString();
	  var lat = str.substring(str.indexOf('(')+1,str.indexOf(','));
	  var lng = str.substring(str.indexOf(',')+2,str.indexOf(')'));
	  $("#photo_info .controls input[name='lat']").val(lat);
	  $("#photo_info .controls input[name='long']").val(lng);
		search_addr(lat, lng);
  	var icon = new GIcon(G_DEFAULT_ICON);
		icon.image = "img/marker-yellow.png";
    marker = new GMarker(latlng, {draggable: true, icon:icon});  
    gmap.addOverlay(marker);
    GEvent.addListener(marker, "dragend", function() {
      var point = marker.getPoint();
      gmap.panTo(point);
      $("#photo_info .controls input[name='lat']").val(point.lat());
      $("#photo_info .controls input[name='long']").val(point.lng());
      search_addr(point.lat(), point.lng());
    });
	}
}

function search_addr(lat, lng) {
	$('#map').jmap('SearchAddress', {
      'query': new GLatLng(lat, lng),
      'returnType': 'getLocations'
  },function(result, options) {
    	var html = '<ul>';
	  	var valid = Mapifies.SearchCode(result.Status.code);
      if (valid.success) {
        html += 'Click on the link to paste';
        $.each(result.Placemark, function(i, point){
        	if (i < 4) {
						var places = point.address.split(',');	
						html += '<li>';
        		$.each(places,function(k){
							html += '<a href="javascript:void(0)" onclick="insert_addr(\''+$.trim(places[k])+'\')">'+$.trim(places[k])+'</a>, ';
        		});
          	html += '</li>';
        	}        		
        });
      } else
    		html = 'No addresses found';
    	$("#helper").html(html+'</ul>');
  });
}

function insert_addr(txt) {
	focus_el.val(txt);
}

function load_markers(what) {
	if (what == undefined) what = 'markers';
	$("#nav-btns").show();
  var start = parseInt($("#all-now").val());
  var bounds = gmap.getBounds();
  var sw = bounds.getSouthWest();
  var ne = bounds.getNorthEast();
  gmap.clearOverlays();
  gmap.addOverlay(arrow);
  arrow.hide();
  $.ajax({type: "POST", url: "inc/main.php?do=get_"+what, dataType: "xml", data: {mix: sw.lng(), max: ne.lng(), miy: sw.lat(), may: ne.lat(), start: start}, success: function(xml) {  
  	$("#browse").html('');
    $(xml).find("marker").each(function(){
      var xml = $(this);
      var point = new GLatLng(xml.attr('lat'),xml.attr('lng'));
      if (bounds.contains(point) == true) {
	      var xmlMarker = new GMarker(point);
	      gmap.addOverlay(xmlMarker);
	      var html = '<div id="map-box">'+xml.text()+'</div>';
				GEvent.addListener(xmlMarker, "mouseout", function() {
					$("#photo-preview").hide();
				});
				GEvent.addListener(xmlMarker, "mouseover", function(point) {
					var markerOffset = gmap.fromLatLngToContainerPixel(point);
					$("#photo-preview").html(html).css({ top:markerOffset.y-150, left:markerOffset.x-50}).show();
				});
				GEvent.addListener(xmlMarker, "click", function() {
					open_photo(xml.attr('id'));
				});
	      $("#browse").append(xml.text());
      }
    });
    $("#browse .mark_img").hover(function(){
			var lat = $(this).attr('lat')-0.0001;
			var lng = $(this).attr('lng')-0.0001;
			var point = new GLatLng(lat,lng);
			arrow.setLatLng(point);
			arrow.show();
  	},function(){
			arrow.hide();
  	});
  }});
}

function open_photo(id) {
  $("#bigphoto .photo").load('inc/main.php?do=open_photo&id='+id,function(){
    $("#fs_photo").lightbox();
    $("img[overlay='true']").hide();
    $("#bigphoto .info").load('inc/main.php?do=photo_info&id='+id,function(){
      $("#bigphoto").hover(function(){$("#bigphoto .info").fadeIn();},function(){$("#bigphoto .info").fadeOut();});
      box.load();  
    });
  });
}


function globalSearch() {
	$("#nav-btns").hide();
	var type = $("#search-opts input:checked").val();
	var q = $("#explore .inp-text").val();
	$("#browse").load('inc/main.php?f=globSearch',{type:type, q:q},function(){
		parseSearchResults();
	});
}

function parseSearchResults() {
	gmap.clearOverlays();
	$("#browse .mark_img").each(function(){
		var lat = $(this).attr('lat');
		var lng = $(this).attr('lng');
		var point = new GLatLng(lat,lng);
    var foundMarker = new GMarker(point);
	  gmap.addOverlay(foundMarker);
	  var html = '<div id="map-box">'+$(this).parent().html()+'</div>';
		foundMarker.bindInfoWindowHtml(html);
		var id = $(this).parent().attr('gid');
		$(this).attr('onclick','');
		$(this).click(showFound);
	});
}

function showFound() {
	var lat = $(this).attr('lat');
	var lng = $(this).attr('lng');
	var id = $(this).parent().attr('gid');
	var point = new GLatLng(lat,lng);
	gmap.panTo(point);
	open_photo(id);
}

