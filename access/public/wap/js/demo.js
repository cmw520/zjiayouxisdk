$(function () {
    //获取短信验证码
    var validCode = true;
    $(".msgs").click(function () {
        var time = 30;
        var a    = document.getElementById("a");
        var code = $(this);
        if (validCode) {
            validCode = false;
            code.addClass("msgs1");

            var t = setInterval(function () {
                time --;
                code.html(time + "秒");
                if (time == 0) {
                    clearInterval(t);
                    a.style.display           = "none";
                    a.parentNode.style.height = 0;

                    code.html("重新获取");
                    validCode = true;
                    code.removeClass("msgs1");

                }
                else if (time < 30 && time > 0) {
                    a.parentNode.style.height = "25px";
                    a.style.display           = "block";

                }

            }, 1000)
        }
    })
})