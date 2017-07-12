//登录系统
function loginUser(ctx) {
    var username  = $("#username").val();
    var userpass  = $("#userpass").val();
    var checkCode = $("#checkCode").val();
    if (username == "" || username == "请输入用户名/手机号") {
        alert("请输入用户名/手机号！");
        $('#username').focus();
        return false;
    }
    if (userpass == "") {
        alert("请输入密码！");
        $('#pwd_0').focus();
        $('#userpass').focus();
        return false;
    }
    if (checkCode == "" || checkCode == "请输入验证码") {
        alert("请输入验证码！");
        $('#checkCode').focus();
        return false;
    }
    if (checkCode.length < 4) {
        alert("请输入4位验证码！");
        $('#checkCode').focus();
        return false;
    }
    $.get(ctx + "/account/code", {"checkCode": checkCode}, function (data) {
        if (data == "Y") {
            loginU(ctx, username, userpass);
            return true;
        } else {
            alert("验证码错误！");
            $("#register_checkCodeImg").click();
            $('#checkCode').val("");
            $('#checkCode').focus();
            return false;
        }
    });
}
function loginU(ctx, username, userpass) {
    var loginUrl = ctx + "/account";
    var backUrl  = urlRequest.QueryString("backUrl");
    var url      = location.href;
    $.post(loginUrl, {"username": username, "pwd": userpass}, function (data) {
        if ("success" in data) {
            if (url.length - url.lastIndexOf(loginUrl) == loginUrl.length) {
                url = ctx + "/user/game";
            } else if (backUrl != null && backUrl != "" && backUrl.toUpperCase != "NULL") {
                url = backUrl;
            }
            location.href = url;
            return true;
        } else if ("fail" in data) {
            alert(data.fail);
        } else {
            alert("系统错误");
        }
        $("#register_checkCodeImg").click();
        $('#checkCode').val("");
        $('#checkCode').blur();
        $('#userpass').val("");
        $('#userpass').focus();
    });
}