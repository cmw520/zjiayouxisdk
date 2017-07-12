/** *****输入框***** */
$(".inputBox").focus(function () {
    $(".error_box").html("");
});

$("#accode").blur(function () {
    var accode = $(this).val();
    if (accode == null || accode == undefined || accode == '') {
        showMsg(".error_box", "请输入邀请码");
        return false;
    } else {
        $(".error_box").html("");
    }
});

// 忘记密码
$(".confim_change>button").click(function () {
    var accode    = $("#accode").val();
    var data      = {};
    var url       = $("#ajaxUrl").val();
    var form_data = {
        accode: accode
    };
    sendData(url, form_data, succ);
    function succ(result) {
        if ('success' == result.state) {
            huosdk_writeAg(result.info);
            huosdk_closeweb();
        } else {
            showMsg(".error_box", result.info);
        }
    }
});