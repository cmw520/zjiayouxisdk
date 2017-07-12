var huosdk_deviceType = 'android';
var sUserAgent        = navigator.userAgent.toLowerCase();
huosdk_deviceType     = sUserAgent.match(/ipad/i) == "ipad" ? 'ipad' : 'android';
huosdk_deviceType     = sUserAgent.match(/iphone/i) == "iphone" ? 'iphone' : 'android';

function sendData(url, data, succ, err, type, dataType, conentType) {
    if (! type) {
        type = "POST"
    }
    if (! err) {
        err = ""
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
        url     : url, // 目标地址
        data    : data,
        success : succ,
        error   : err
    });
}
// 获取短信验证码
function _getCode(el, time, back, interval, msgBox, bgColor, color) {
    var code = $(el);
    if (! time) {
        time = 120
    }
    if (! interval) {
        interval = 1000
    }
    if (! bgColor) {
        bgColor = "#aaa"
    }
    if (! color) {
        color = "#fff"
    }
    showMsg(msgBox, "验证码已发送", "red");
    var time1     = time;
    var codeback  = code.css("background-color");
    var codeColor = code.css("color");
    code.css("background-color", bgColor);
    code.css("color", color);
    code.unbind("click", back);
    time1 --;
    code.html("剩余" + time1 + "s");
    code.addClass("msgs1");
    var t = setInterval(function () {
        time1 --;
        code.html("剩余" + time1 + "s");
        if (time1 == 0) {
            clearInterval(t);
            code.html("重新获取");
            code.removeClass("msgs1");
            code.css("background-color", codeback);
            code.css("color", codeColor);
            code.bind("click", back);
            //showMsg(msgBox, "请尽快完成验证码，五分钟内有效..", "red");
        }
    }, interval)
}
function showMsg(el, text, color) {
    if (! color) {
        color = "red"
    }
    $(el).html(text);
    $(el).css("color", color);
}

// 验证手机
function checkMobile(mobile) {
    if (mobile.match(/^[1][34578][0-9]{9}$/)) {
        return true;
    } else {
        return false;
    }
}

function checkEmail(email) {
    if (email.match(/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/)) {
        return true;
    } else {
        return false;
    }
}
function checkUser(username) {
    var regcellpwd = /^[0-9A-Za-z]{6,16}$/g;
    return regcellpwd.test(username);
}

function changePwd(newPwd, confim) {
    var newPwd = $(newPwd).val();
    var confim = $(confim).val();
    if (newPwd.trim() !== confim.trim()) {
        return false;
    }
    return true;
}

//调用原生切换账号
function huosdk_changeaccount(type) {
    if ('logout' == type) {
        showMobileAlert('确定退出', '是', '否', '', huosdk_logout);
    } else {
        showMobileAlert('确定切换账号', '是', '否', '', huosdk_logout);
    }
}

//web调用原生打电话
function huosdk_ringup(phone) {
    var phonestr = String(phone);
    window.huosdk.openPhone(phonestr);
}

function GoToSet() {
    location.href = "prefs:root=General&path=ManagedConfigurationList";
}

//web调用原生拷贝字符串
function huosdk_copystr(str) {
    var copystr = String(str);
    window.huosdk.copyString(copystr);
}
//web调用原生打开QQ
function huosdk_openqq(qq) {
    var qqstr = qq.toString();
    window.huosdk.openQq(qqstr);
}
//web调用原生打开QQ
function huosdk_writeAg(accode) {
    var accodestr = accode.toString();
    window.huosdk.writeAgentgame(accodestr);
}

//web调用原生打开QQ群
function huosdk_openqqgroup(qqgroup, qqkeystr) {
    var qqgroupstr = qqgroup.toString();
    if ('android' == huosdk_deviceType) {
        qqgroupstr = qqkeystr.toString();
    }
    window.huosdk.joinQqgroup(qqgroupstr);
}

//调用原生关闭页面
function huosdk_closeweb() {
    huosdk_backgame();
}

//成功回调
function huosdk_backgame() {
    window.huosdk.closeWeb();
}

//切换账号
function huosdk_logout() {
    window.huosdk.changeAccount();
}

$(document).on('click', '#movies li', function () {
    $(this).toggleClass('selected');
});

/*可定制的弹出框*/
function showMobileAlert(title, lbtn, rbtn, operation, succ, data, font) {
    var once = false;
    if (! title) {
        title = '是否退出?'
    }
    if (! lbtn && lbtn !== 'null') {
        lbtn = '是'
    }
    if (! rbtn && rbtn !== 'null') {
        rbtn = '否'
    }
    if (lbtn === 'null') {
        lbtn = '';
        once = true;
    }
    if (rbtn === 'null') {
        rbtn = '', once = true
    }
    var flag        = document.createDocumentFragment();
    var style       = document.createElement('style');
    var boxWidth    = Math.floor(parseInt($("html").css("width")) * 0.9 / 2);
    style.innerHTML = "#mobileAlert{width:100%;height:100%;position:fixed;top:0;left:0;background-color:rgba(0,0,0,.5);display:none}#mobileAlert>.box{width:90%;max-width:400px;background-color:#fff;margin:0 auto;position:absolute;top:200px;left:50%;border-radius:8px;margin-left:-" + boxWidth + "px}#mobileAlert>.box>.title{padding:30px 0;text-align:center;font-size:16px;font-family:'微软雅黑','microsoft YaHei'}#mobileAlert>.box>ul{border-top:1px solid #F3F3F3}#mobileAlert>.box>ul>li{float:left;width:50%;height:40px;line-height:40px;text-align:center;font-size:16px}#mobileAlert>.box>ul>li>a{font-family:'微软雅黑','microsoft YaHei'}#mobileAlert>.box>#show2>li{width:100%}#mobileAlert .box > ul.center>li{width:100%} ";
    flag.appendChild(style);
    var huosdk_div       = document.createElement('div');
    huosdk_div.id        = 'mobileAlert';
    huosdk_div.innerHTML = '<div class="box">\
        <p class="title">确定退出？</p>\
    <ul class=' + (once == false ? "" : "center") + '>\
            <li class="lbtn" style="-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;g:border-box;border-right:1px solid #F3F3F3">\
                <a href="#">是</a>\
                </li>\
        <li class="rbtn"><a href="#">否</a></li>\
        </ul>\
        </div>';

    flag.appendChild(huosdk_div);
    if (document.querySelectorAll('#mobileAlert').length > 0) {
        document.body.removeChild(document.querySelector('#mobileAlert'));
    }
    document.body.appendChild(flag);
    document.querySelector("#mobileAlert").style.fontSize    = font;
    document.querySelector("#mobileAlert .title").innerHTML  = title;
    document.querySelector("#mobileAlert .lbtn a").innerHTML = lbtn;
    document.querySelector("#mobileAlert .rbtn a").innerHTML = rbtn;
    if ('left' == operation) { //单个按钮有事件
        document.querySelector("#mobileAlert .rbtn").style.display = 'none';
        document.querySelector("#mobileAlert .lbtn").onclick       = function () {
            document.querySelector("#mobileAlert").style.display = 'none';
            succ(data);
        };
    } else if ('right' == operation) { //单个按钮无事件
        document.querySelector("#mobileAlert .lbtn").style.display = 'none';
        document.querySelector("#mobileAlert .rbtn").onclick       = function () {
            document.querySelector("#mobileAlert").style.display = 'none';
        };
    } else {
        document.querySelector("#mobileAlert .rbtn").onclick = function () {
            document.querySelector("#mobileAlert").style.display = 'none';
        };
        document.querySelector("#mobileAlert .lbtn").onclick = function () {
            succ(data);
        };
    }
    if (parseInt($("html").css("width")) * 0.9 > 400) {
        $("#mobileAlert .box").css("margin-left", "-200px");
    } else {
        $("#mobileAlert .box").css("margin-left", "-" + parseInt($("html").css("width")) * 0.9 / 2 + "px");
    }
    $("#mobileAlert .box").css("top", (window.innerHeight - 117) / 2 + "px");
    document.querySelector("#mobileAlert").style.display = 'block';
}