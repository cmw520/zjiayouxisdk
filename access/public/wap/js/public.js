window.addEventListener("load", function () {
    $(".btn_top").hide();
    $(".main_model_box").css("min-height", $('html').css("height"))
    // $(".loading_more").css("top",$("body").css("height"));
    /********关闭按钮*******/
    $(".header>.main_layout>.close_btn>img").click(function () {

    });
    /********返回按钮*******/
    $(".header>.main_layout>.back_btn>img").click(function () {
        window.history.back();
    });
    /********返回顶部按钮*******/
    $(".loading_more>.btn_top").click(function () {
        $("html,body").animate({scrollTop: 0}, 500);
    });
    window.onscroll = function () {
        if ($(window).scrollTop() > parseInt(window.innerHeight) - 300) {
            $(".btn_top").css({
                "display" : "block",
                "position": "fixed",
                "top"     : parseInt(window.innerHeight) - 90 + "px",
                "right"   : "10px"
            });
        } else {
            $(".btn_top").css({"display": "none", "position": "absolute", "top": "0px", "right": "10px"});

        }
    };
    $("input").click(function () {
        $(".error_box").html("");
    });

}, false)
//原生标签页切换
function ysTabs(children, targetEl, className) {
    if (! className) {
        className = "current"
    }
    var tag                  = document.querySelectorAll(children); //获取Tag下的li，即Tag标签
    var content              = document.querySelectorAll(targetEl); //获取Tag标签对应的内容
    content[0].style.display = "block"; //默认显示第一个标签的内容
    var len                  = tag.length;
    for (var i = 0; i < len; i ++) {
        tag[i].index   = i; //设置对象的INDEX属性，方便下面调用
        tag[i].onclick = function () {
            for (var n = 0; n < len; n ++) {
                tag[n].className         = "";
                content[n].style.display = "none";
            }
            tag[this.index].className         = className;
            content[this.index].style.display = "block";
        }
    }
}
//  滚动加载
function bottomLoad(bottomFunc) {
    $(window).scroll(function () {
        var range       = 0;             //距下边界长度/单位px
        var srollPos    = $(window).scrollTop();    //滚动条距顶部距离(页面超出窗口的高度)
        var totalheight = parseFloat($(window).height()) + parseFloat(srollPos);
        if (($(document).height() - range) <= totalheight) {
            bottomFunc();
        }
    });
}