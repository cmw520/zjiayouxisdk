$(".getCode").click(function f2() {
    var email = $("#email").val();
    if (email.trim() !== "") {
        if (checkEmail(email)) {
            var url       = $("#ajaxUrl").val();
            var data      = {};
            data['email'] = email;
            _getCode(this, 30, f2, 1000, ".error_box", "#fff", "#000");
            sendData(url, data, function (result) {
                if (result.state === "fail") {
                    showMsg(".error_box", "验证码发送失败，请稍后再试")
                } else {
                    showMsg(".error_box", "验证码发送成功");
                    $(".confim_change>button").prop("disabled", false);
                }
            })
        } else {
            showMsg(".error_box", "邮箱格式有误,请重新输入..")
        }
    } else {
        showMsg(".error_box", "邮箱格式不能为空..")
    }
});
$(".confim_change>button").click(function () {
    var email    = $("#email").val();
    var code     = $("#code").val();
    var password = $("#password").val();
    if (checkEmail(email)) {
        if (code.trim() !== "") {
            if (password.trim() !== "") {
                var url       = $("#ajaxUrl").val();
                var data      = {};
                data['email'] = email;
                data['code']  = code;
                sendData(url, data, succ);
                function succ(result) {
                    if (result.state == "fail") {
                        showMsg(".error_box", "验证码发送失败..");
                    } else {
                        window.location.href = result.url;
                    }
                }
            } else {
                showMsg(".error_box", "平台密码不能为空..")
            }

        } else {
            showMsg(".error_box", "验证码不能为空..")
        }
    } else {
        showMsg(".error_box", "邮箱格式有误,请重新输入..")
    }
});