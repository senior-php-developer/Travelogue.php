$(init_login);

function init_login() {
	$("#reg").click(open_reg);
	$("#login").click(open_login);
	$("#logout").click(logout);
	$("#d_reg .b_submit").click(register);
	$("#d_login #b_login").click(login);
	$("#d_login #b_reset").click(reset_pwd);
	$("#d_login #b_change").click(change_pwd);

	$("#d_reg .b_cancel").click(function(){	$("#d_reg").fadeOut("slow");});
	$("#d_login .b_cancel").click(function(){	$("#d_login").fadeOut("slow");});
}

function open_reg() {
	var w = $(window).width()*0.5-100;
	var h = $(window).height()*0.5-200;
	$("#d_reg input").val('');
	$("#d_reg").css('top',h+'px').css('left',w+'px').fadeIn("slow",Recaptcha.reload);
}

function open_login() {
	var w = $(window).width()*0.5-100;
	var h = $(window).height()*0.5-100;
	$("#d_login input").val('');
	$("#d_login span#password").show();
	$("#d_login span#password span").text('Your Password'); 
	$("#d_login span#code").hide();
	$("#b_change").hide();
	$("#b_reset").hide();
	$("#b_login").show();
	$("#d_login a").show();
	$("#d_login").css('top',h+'px').css('left',w+'px').fadeIn("slow");
}

function register() {
	if (($("#d_reg input[name='mail']").val() == '') || ($("#d_reg input[name='pass']").val() == '')) {
		$("#p_info").text("fill all neccessary fields").slideDown("slow",close_ajax);
		return;
	}
	if ($("#recaptcha_response_field").val() == '') {
		$("#p_info").text("provide captcha solution").slideDown("slow",close_ajax);
		return;
	}
	var data = {};
	$("#d_reg :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	$.post('inc/login.php?do=register',data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		$("#d_reg :input").val('');
		$("#d_reg").fadeOut('slow');
	});
}

function login() {
	if (($("#d_login input[name='mail']").val() == '') || ($("#d_login input[name='pass']").val() == '')) {
		$("#p_info").text("login information is missing").slideDown("slow",close_ajax);
		return;
	}
	var data = {};
	$("#d_login :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	$.post('inc/login.php?do=login',data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		if (reply == 'login successful')
			setTimeout('location.href="index.php"',2000);  
	});
}

function logout() {
	$.post('inc/login.php?do=logout',function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		setTimeout('location.href="index.php"',2000);  
	});
}

function show_reset_dlg() {
	$("#b_login").hide();
	$("#b_reset").show();
	$("#d_login a").hide();
	$("#d_login span#password").hide();
}

function reset_pwd() {
	var mail = $("#d_login input[name='mail']").val();
	if (mail == '') return;
	$.post('inc/login.php?do=reset',{mail: mail},function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		show_change_pwd();
	});
}

function show_change_pwd() {
	$("#b_reset").hide();	
	$("#b_change").show();  
	$("#d_login span#password").find("span").text('Enter a new password').end().find("input").val('').end().show(); 
	$("#d_login span#code").show();
}

function change_pwd() {
	var data = {};
	$("#d_login :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	$.post('inc/login.php?do=change',data,function(reply){
		if (reply == 'ok')
			window.location.reload(true);
		else
			$("#p_info").text(reply).slideDown("slow",close_ajax);
	});	
}