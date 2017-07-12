/**
 * ctx 为应用的名称，在jsp的页面JavaScript代码中定义
 */
//获取弹框图片验证码
function getPhoneCodeRegister(){
	var obj = $("#sendPhoneMesBtn");
	unbindClickMethod(obj);
	var phonenum = $('#phone').val();
	if(phonenum=="" || phonenum=="请输入手机号"){
		alert("请先输入手机号码！");
		$('#phone').focus();
		bindClickMethod(obj,getPhoneCodeRegister);
		return;
	} else if(!IsMatch(phonenum,"mobilephone")){
		alert("手机号格式不正确！");
		$('#phone').focus();
		bindClickMethod(obj,getPhoneCodeRegister);
		return false;
	}
	$.post(ctx + "/account/regcode2",{"phone":phonenum},function(data){
		if("success" in data){
			if(data.success == true){
				$("#checkCode").val("");
				$("#code-img img:eq(0)").attr("src",data.base64Img);
				$("#key").val(data.key);
				popupbox('code-pop');
				$("#checkCode").val("");
				$("#checkCode").focus();
			}
		} else{
			alert(data.msg);
		}
	});
}
//刷新图片验证码
function getVerificationCode(){
	var phone = $('#phone').val(); 
	$.post(ctx + "/account/regcode2",{"phone":phone},function(data){
		if("success" in data){
			if(data.success == true){
				$("#code-img img:eq(0)").attr("src",data.base64Img);
				$("#key").val(data.key);
				$("#checkCode").val("");
				$("#checkCode").focus();
			}
		}
	});
}
//关闭弹出框
function closeCodePop(){
	closebox('#code-pop');
	bindClickMethod($("#sendPhoneMesBtn"), getPhoneCodeRegister);
}
//取消点击事件
function unbindClickMethod(obj){
	$(obj).attr("onclick","");
	$(obj).unbind("click");
	$(obj).bind("click",function(){})
	$(obj).css("background", "#666");
}
//绑定点击事件
function bindClickMethod(obj,func){
	$(obj).css("background", "");
	$(obj).click(function(){func();});
}
//检查图片验证码，并发送短信验证码
function sendRegMes(){
	var key = $("#key").val();
	var code = $("#checkCode").val();
	var phone = $('#phone').val();
	if(code.length < 4){
		alert("请输入正确的图形验证码");
		$("#checkCode").focus();
	} else{//检查图片验证码并发送短信验证码
		$.ajax({
				url: ctx + "/account/pic-code-validation2",
				type: 'post',
				data: {"key":key, "code":code, "phone":phone},
				dataType: 'json',
				cache: false,
				success:function(data){
					if("success" in data){
						if(data.success == true){
							$("#key").val("success");
							//关闭弹框，并进行倒计时
							closebox('#code-pop');
							//倒计时开始
							var o = $("#sendPhoneMesBtn");
							var num = 60;
							o.html(num +"秒后可重新发送");
							var interval=setInterval(function(){
								o.html(--num +"秒后可重新发送");
								if(num == 0){
									bindClickMethod(o, getPhoneCodeRegister);
									o.html("发送手机验证码");
									clearInterval(interval);
									num=60;
								}
							},1000);
							$("#verificationcode").val("");
							$("#verificationcode").focus();
						} else if(data.msg == "EXIST"){
							alert("该手机号或用户已存在，请使用其他手机号");
							closeCodePop();
						} else{
							alert(data.msg);
							$("#checkCode").val("");
							getVerificationCode();
							$("#checkCode").focus();
						}
					} else{
						alert("系统异常，请刷新页面重试");
					}
				}
		});
	}
}

//用户协议
function changeAgreement(){
	if($("#i-agreed").is(".on")){
		$("#i-agreed").removeClass("on");
		$("#btn-reg-f").css("background", "#666");
		$("#btn-reg-f").html("请接受用户协议");
		unbindClickMethod($("#btn-reg-f"));
	} else{
		$("#i-agreed").addClass("on");
		$("#btn-reg-f").css("background", "");
		$("#btn-reg-f").html("下 一 步");
		bindClickMethod($("#btn-reg-f"), firstStepReg);
	}
}

//第一步手机验证成功，进行第二步设置账号
function firstStepReg(){
	var code = $("#verificationcode").val();
	var phone = $("#phone").val();
	
	if(phone == "" || phone == "请输入手机号"){
		alert("请输入手机号码");
		$("#phone").focus();
		return false;
	} else if(code == "" || code == "请输入手机验证码"){
		alert("请输入手机验证码");
		$("#verificationcode").focus();
		return false;
	} else if(!/^\d{6}$/.test(code)){
		alert("手机验证码为6位数字");
		$("#verificationcode").val("");
		$("#verificationcode").focus();
		return false;
	}
	//检查短信验证码
	$.ajax({
		url: ctx + "/account/phone-code-validation",
		type: 'post',
		data: {"code":code, "phone":phone},
		dataType: 'json',
		cache: false,
		success:function(data){
			if(data.success == true){
				$("#username").val(phone);
				$("#div_1").hide();
				$("#div_2").show();
			} else if(data.msg == "EXIST"){
				alert("该手机号或用户已存在，请使用其他手机号");
				location = location;
			} else{
				alert(data.msg);
				$("#verificationcode").focus();
			}
		}
	});
}

function insertUser(){
	unbindClickMethod($("#commit"));
	
	var phone = $("#phone").val();
	var password = $.trim($("#password").val());
	var userpass = $.trim($("#userpass").val());
	var code = $("#verificationcode").val();
	
	if(password == ""){
		alert("密码为空！");
		$('#password').focus();
		bindClickMethod($("#commit"), insertUser);
		return false;
	}
	if(userpass == ""){
		alert("请再次确认密码！");
		$('#userpass').focus();
		bindClickMethod($("#commit"), insertUser);
		return false;
	}
	if(password != userpass){
		alert("两次密码不一致！");
		$('#userpass').val("");
		$('#userpass').focus();
		bindClickMethod($("#commit"), insertUser);
		return false;
	}
	if(!/^(?![0-9]+$)(?![a-zA-Z]+$)\w{6,20}$/.test(userpass)){
		alert("请输入6到20位由英文字母和数字组成的密码");
		$("#userpass").val("");
		$('#password').val("");
		$('#password').focus();
		bindClickMethod($("#commit"), insertUser);
		return false;
	}
	
	$.post(ctx + "/account/reg2",{"phone":phone, "code":code, "password":password},function(data){
		if(data.success == true){
			$("#div_2").hide();	$("#div_3").show();
			var num = 4; var o = $("#back");
			var interval=setInterval(function(){
				o.html("带你熟悉芒果玩（"+ num-- +"秒...）");
				if(num == 0){				
					clearInterval(interval);
					window.location.href = ctx + "/guide";
				}
			},1000);
			return true;
		} else{
			alert(data.msg);
			bindClickMethod($("#commit"), insertUser);
			return false;
		}
	});
}


function checkUser(){
	var phone = $("#phone").val();
	var username = $.trim($("#username").val());
	
	$.post(ctx + "/account/user-validation",{"phone":phone, "username":username},function(data){
		if(data.success == true){
			return;
		} else if(data.success == false){
			alert(data.msg);
			$("#username").val("");
			$("#username").focus();
			return;
		} else{
			return;
		}
	});
}