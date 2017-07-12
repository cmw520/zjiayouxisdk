$(".alert_box>div").each(function () {
    $(this).css("transform", "translateX(-" + parseInt($(this).css("width")) / 2 + "px)");
});
/*************点击眼睛时显示密码**************/
$(".pass_box>.pass_input>b").each(function () {
    $(this).click(function () {
        var text    = $(this).siblings("input").attr("placeholder");
        var val     = $(this).siblings("input").val();
        var input   = document.createElement("input");
        input.value = val;
        input.setAttribute("placeholder", text);
        if ($(this).siblings("input").attr("type") === "password") {
            input.setAttribute("type", "text");
        } else {
            input.setAttribute("type", "password");
        }
        $(this).siblings("input").remove();
        $(this).parent().append(input);
    });
});
/*************密码输入框获取焦点**************/
$(".pass_box>.pass_input>input").each(function () {
    $(this).focus(function () {
        $(".error_box").val('');
    });
});
/****************修改手机取消按钮**************/
$(".phone1 .cacle_btn>.cacle").click(function () {
    $(".alert_phone").css("display", "none");
    $(".alert_box").css("display", "none");
});
/****************修改密码的下一步按钮**************/
$(".change_pwd .cacle_btn .next").click(function () {
    var url        = uppwd;
    var old_pwd    = $(".pass_box>.old_pwd>input").val();
    var new_pwd    = $(".pass_box>.new_pwd>input").val();
    var confirm    = $(".pass_box>.confirm>input").val();
    var regcellpwd = /^[0-9A-Za-z-`=\\\[\];',.\/~!@#$%^&*()_+|{}:"<>?]{6,16}$/g;
    if (false == regcellpwd.test(new_pwd)) {
        showMsg(".error_msg", '密码必须由6-16位的数字、字母、符号组成');
        return false;
    }
    var form_data = {
        oldpwd   : old_pwd,
        newpwd   : new_pwd,
        verifypwd: confirm,
        action   : "updatepwd"
    };
    if (old_pwd.trim() !== "") {
        if ((new_pwd === confirm) && (new_pwd !== "")) {
            $.ajax({
                type    : "POST",
                url     : url,
                data    : form_data,
                error   : function (XMLHttpRequest, textStatus, errorThrown) {
                    showMsg(".error_msg", '读取超时，网络错误');
                },
                dataType: "json",
                success : function (result) {
                    if ('fail' == result.state) {
                        if (result.url) {
                            window.location.href = result.url;
                        } else {
                            showMsg(".error_msg", result.info);
                        }
                    } else {
                        window.location.href = result.url;
                    }
                }
            });
            return false;
        } else {
            $(".change_pwd .cacle_btn").css("margin-top", "0px");
            showMsg(".error_box", "两次密码输入不一致或不能为空..")
        }
    } else {
        $(".change_pwd .cacle_btn").css("margin-top", "0px");
        showMsg(".error_box", "旧密码不能为空..")
    }
});
/***************修改手机**************/
$(".phone_num>.change_phone").click(function () {
    var phone = $(".phone_num .change_phone>.icon_right>span").html();
    var url   = checkmobile;
    if (checkMobile($(".phone_num .change_phone>.icon_right>span")), "html") {
        $(".alert_box").css("display", "block");
        $(".alert_phone").css("display", "block");
        sendData(url, {"phone": phone}, function (data) {
            if (data === 1) {
                $(".alert_phone .phone1").css("display", "block");
            } else if (data === 2) {
                $(".alert_phone .phone2").css("display", "block");
                $(".alert_phone").css("height", "219px");
            }
        });
        $(".alert_phone").css("transform", "translateX(-" + parseInt($(".alert_phone").css("width")) / 2 + "px)");
    }
});
/***************解绑手机获取验证码**************/
$(".phone1 .code_input>button").click(function f1() {
    var phone = $(".verification>p>span").html();
    var url   = verify_mmsend;
    $(".alert_box .phone1 .cacle_btn > .next").removeClass("noCan").removeAttr("disabled");
    _getCode(".phone1 .code_input>button", 30, f1, 1000, ".phone_msg1");
    sendData(url, {"phone": phone}, function (data) {
        if (data.status == 1) {
            showMsg(".phone_msg1", "验证码已发送..")
        }
        showMsg(".phone_msg1", "验证码发送失败..")
    })
});
/****************解绑手机的下一步按钮**************/
$(".phone1 .next").click(function () {
    var code  = $(".phone1 .code_input>input").val();
    var phone = $(".verification>p>span").html();
    if (code.trim() !== "") {
        sendData(vurl, {"phone": phone, "code": code}, function (data) {
            if (data.status === '1') {
                $(".alert_phone .phone1").css("display", "none");
                $(".alert_phone .phone2").css("display", "block");
                $(".alert_phone").css("height", "219px");
            }
            showMsg(".phone_msg1", data.msg)
        })
    } else {
        showMsg(".phone_msg1", "验证码不能为空..")
    }
});
/****************填写新手机的获取验证码**************/
$(".phone2 .code_input>button").click(function phone2_code() {
    var phone = $(".phone2 .new_phone >input").val();
    var url   = verify_mmsend;
    if (checkMobile($(".phone2 .new_phone>input"))) {
        $(".alert_box .phone2 .cacle_btn > .next").removeClass("noCan").removeAttr("disabled");
        if (phone.trim() !== "") {
            sendData(url, {"phone": phone}, function (data) {
                if (data.status == 1) {
                    _getCode(".phone2 .code_input>button", 120, phone2_code, 1000, ".phone2 .phone_msg2")
                } else {
                    showMsg(".phone_msg2", "验证码发送失败..")
                }
            })
        } else {
            showMsg(".phone2 .phone_msg2", "手机号不能为空...")
        }
    } else {
        showMsg(".phone2 .phone_msg2", "请填写正确的手机号码...")
    }
});
/****************新手机的下一步**************/
$(".phone2 .next").click(function () {
    var phone = $(".phone2 .new_phone >input").val();
    var code  = $(".phone2 .code_input>input").val();
    if (phone.trim() !== "") {
        if (code.trim() !== "") {
            sendData(vurl, {"phone": phone, "code": code}, function (data) {
                if (data.status === '1') {
                    alert("验证成功...");
                    $(".phone2").hide();
                    $(".phone3").show();
                    var time  = 4;
                    var timer = setInterval(function () {
                        time --;
                        $(".phone3>h3>span").html(time);
                        if (time < 1) {
                            clearInterval(timer);
                            $(".phone3").hide();
                            $(".alert_box").hide();
                        }
                    }, 1000);
                }
                showMsg(".phone_msg2", "验证码错误...")
            })
        } else {
            showMsg(".phone_msg2", "验证码不能为空...")
        }
    } else {
        showMsg(".phone_msg2", "手机号不能为空...")
    }

});
/****************x绑定成功的关闭按钮**************/
$(".phone3 .cacle_btn .cacle").click(function () {
    $(".phone3").css("display", "none");
    $(".alert_box").css("display", "none");
});
/****************x绑定成功的关闭按钮**************/
$(".phone2 .cacle_btn .cacle").click(function () {
    $(".phone2").css("display", "none");
    $(".alert_box").css("display", "none");
});
/***************修改密码**************/
$(".phone_num>.pwd").click(function () {
    $(".alert_box").css("display", "block");
    $(".alert_box>.change_pwd").css("display", "block");
    $(".alert_box>.change_pwd").css("transform", "translateX(-" + parseInt($(".alert_phone").css("width")) / 2 + "px)");
});
$(".change_pwd .cacle_btn .cacle").click(function () {
    $(".change_pwd").css("display", "none");
    $(".alert_box").css("display", "none");
});

$(".input_name>b").click(function () {
    $(this).prev().val("");
})
/*****************修改昵称的下一步和取消按钮******************/
$(".change_name .cacle_btn .next").click(function () {
    var name = $(".change_name #usname").val();
    if (name.trim() !== "") {
        sendData($("#changenick").val(), {"newName": name}, function (data) {
            if (data.status == 1) {
                alert("修改成功");
                $(".alert_box .change_name").hide();
                $(".alert_box").hide();
                window.location.href = '';
            }
        })
    } else {
        showMsg(".change_name .error_box", "新昵称不能为空..");
        $(".change_name .cacle_btn").css("margin-top", "0px");
    }
});
$(".change_name .cacle_btn .cacle").click(function () {
    $(".alert_box .change_name").hide();
    $(".alert_box").hide();
});
//修改密码的X清除输入框内容
$(".change_name .input_name>b").click(function () {
    $("#usname").val('');
});
$("#usname").focus(function () {
    $(".change_name .error_box").html('');
});
$(".user_accounts .change_name").click(function () {
    $(".alert_box").show();
    $("#usname").val('');
    $(".alert_box>.change_name").show();
    $(".alert_box>.change_name").css("transform", "translateX(-" + parseInt($(".alert_box>.change_name").css("width")) / 2 + "px)");
});
function checkMobile(el, type) {
    if (! type) {
        var sMobile = $(el).val();
    } else {
        var sMobile = $(el).html();
    }

    if (! (/^1[3|4|5|8]\d{9}$/.test(sMobile))) {
        $(el).focus();
        return false;
    }
    return true;
}
