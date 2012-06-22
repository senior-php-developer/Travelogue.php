$(adminInit);

function adminInit() {
	$("#user-names").load('inc/admin.php?f=lastUsers');
}

function showInfo(id) {
	$("#user-info").load('inc/admin.php?f=userInfo&id='+id);
	$("#user-photos").load('inc/admin.php?f=userPhotos&id='+id);
	$("#edit-panel").load('inc/admin.php?f=editUser&id='+id);
}

function editPhoto(id) {
	$("#edit-panel").load('inc/admin.php?f=editPhoto&id='+id);
}

function saveUser() {
	var data = {};
	$("#edit-panel :input").each(function(){
		data[$(this).attr('name')] = $(this).val();
	});
	$.post('inc/admin.php?f=saveUser',data,function(reply){
		alert(reply);
	});
}

function savePhoto() {
	var data = {};
	$("#edit-panel :input").each(function(){
		data[$(this).attr('name')] = $(this).val();
	});
	$.post('inc/admin.php?f=savePhoto',data,function(reply){
		alert(reply);
	});
}

function delUser() {
	var id = $("#edit-panel input[name='id']").val();
	if (confirm("Delete this user?"))
	$.post('inc/admin.php?f=delUser',{id:id},function(reply) {
		alert(reply);
		$("#manage-panel").html('');
		$("#edit-panel").html('');
		$("#user-names").load('inc/admin.php?f=lastUsers');
	});
}

function delPhoto() {
	var id = $("#edit-panel input[name='id']").val();
	if (confirm("Delete this photo?"))
	$.post('inc/admin.php?f=delPhoto',{id:id},function(reply) {
		alert(reply);
		var user = $("#user-info input").val();
		showInfo(user);		
	});
}