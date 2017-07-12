function sendData(url, data, succ, err, type, dataType, conentType) {
    if (! type) {
        type = "POST"
    }
    if (! url) {
        throw new Error("url is not find...");
    }
    if (! dataType) {
        dataType = "JSON"
    }
    if (! conentType) {
        conentType = "application/x-www-form-urlencoded"
    }
    $.ajax({
        type    : type,
        dataType: dataType,
        url     : url, //目标地址
        data    : data,
        error   : err,
        success : succ
    });
}

function succ() {
    alert("已成功请求到数据..");
}

//获取短信验证码
function _getCode(el, time, msgboxId, back, interval) {
    if (msgboxId[0] === ".") {
        var a = $(msgboxId)[0];
    } else if (msgboxId[0] === "#") {
        var a = $(msgboxId);
    }
    sendDate("1.php", "", succ, "", "post", "json");
    var code = $(el);
    if (! time) {
        time = 30
    }
    if (! interval) {
        interval = 1000
    }
    var time1    = time;
    var codeback = code.css("background-color");
    code.css("background-color", "#aaa");
    code.unbind("click", back);
    time1 --;
    code.html(time1 + "秒");
    code.addClass("msgs1");
    var t = setInterval(function () {
        time1 --;
        code.html(time1 + "秒");
        if (time1 == 0) {
            clearInterval(t);
            a.css("display", "none");
            a.parent().css("height", 0);
            code.html("重新获取");
            code.removeClass("msgs1");
            code.css("background-color", codeback);
            code.bind("click", back);
        } else if (time1 < time && time1 > 0) {
            a.parent().css("height", "25px");
            a.css("display", "block");
        }
    }, interval)
}
