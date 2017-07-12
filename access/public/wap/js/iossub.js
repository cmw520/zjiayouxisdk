var page_name = "Huosdk IOS 助手";

var libiao      = document.getElementById("thelist_e");
var zcd         = document.getElementById("scroller_e");
var ss_w        = 250 * libiao.children.length;
zcd.style.width = ss_w + "px";

var myScroll;
var myScroll_e;
function loaded() {
    myScroll_e = new iScroll('wrapper_e', {
        hScroll : true,
        vScroll : false,
        pullLock: true,
        needTip : true
    });

}
window.addEventListener("DOMContentLoaded", loaded, false);

function doLocation(url) {
    var uaa = navigator.userAgent.toLowerCase();
    if (uaa.indexOf('micromessenger') > 0) {
        $("#zhezhao").show();
        $("#zhezhao").click(function () {
            $("#zhezhao").hide();
        });
    }
    var a = document.createElement("a");

    if (! a.click) {
        window.location = url;
        return;
    }
    a.setAttribute("href", url);
    a.style.display = "none";
    document.body.appendChild(a);
    a.click();
}
function dlll() {

    var dlurl    = "dl.ttzhushou.com";
    var filename = "channel/qudao/" + $('#qdid').val() + "/" + $('#filename').val();
    if (GetRequestValue("type") != undefined) {
        dlurl = GetRequestValue("type") + ".le890.com";

        if (GetRequestValue("type").indexOf("dlpub") > - 1) {
            filename = $('#filename').val().substring(0, 8) + "/" + $('#filename').val();
        } else {
            filename = $('#filename').val();
        }

    }
    if (GetRequestValue("psh") != undefined) {

        filename = GetRequestValue("psh") + "/" + $('#filename').val().substring(0, 8) + "/" + $('#filename').val();

    }
    var url = "itms-services://?action=download-manifest&url=" +
        encodeURIComponent("https://www.ttzhushou.com/PlistByParameter/"
            + $('#bindID').val() + "/" + $('#version').val() + "/" +
            encodeURIComponent($('#name').val()) + "/" +
            encodeURIComponent(dlurl) + "/" +
            encodeURIComponent(filename) + ".plist");

    doLocation(url);
}
function dll() {
    window.location.href = "http://www.ttzhushou.com/?c=cabd";

}

function onSuccess() {
    var isSuccess = 1;
}
function onFail() {
    window.location.href = "http://www.ttzhushou.com/"
}
function exportsApp(url, onSuccess, onFail) {
    // 创建一个iframe
    var ifr                    = document.createElement('IFRAME');
    ifr.src                    = url;
    // 飘出屏幕外
    ifr.style.position         = 'absolute';
    ifr.style.left             = '-1000px';
    ifr.style.top              = '-1000px';
    ifr.style.width            = '1px';
    ifr.style.height           = '1px';
    // 设置一个1秒的动画用于检查客户端是否被调起
    ifr.style.webkitTransition = 'all 1s';
    document.body.appendChild(ifr);
    // 记录起始时间
    var last = Date.now();
    setTimeout(function () {
        // 监听动画完成时间
        ifr.addEventListener('webkitTransitionEnd', function () {
            document.body.removeChild(ifr);//alert(Date.now() - last);
            if (Date.now() - last <= 2000) {//Date.now() - last实际值会略大点1s
                // 如果动画执行时间在预设范围内，就认为没有调起客户端				
                if (typeof onFail === 'function') {
                    onFail();
                }
            } else if (typeof onSuccess === 'function') {
                // 动画执行超过预设范围，认为调起成功
                onSuccess();
            }
        }, false);
        // 启动动画
        ifr.style.left = '-10px';
    }, 0);
}
;

$(document).ready(function () {
    if (t === 3) {
        $(".banner").css("background-image", "url(http://img.ttzhushou.com/app/share/banner3.png)");
        $("#footinfo").html("更多好玩的精品苹果游戏<br>尽在" + page_name);
    }
})