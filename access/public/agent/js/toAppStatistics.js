function succHeader(headerText) {
    $("body").prepend(headerText);
    $("nav .main_nav li").eq(1).addClass("active").siblings().removeClass("active");

}

function searchDown() {
    var stime = $("#stime").val();
    var etime = $("#etime").val();
    if ((stime.trim() !== "") && (etime.trim() !== "")) {
        sendData("{:U('user/ucenter/do_post')}", {"stime": stime, "etime": etime}, function (data) {
            alert("数据已发送....");
        })
    } else {
        alert("请正确选择要查询的日期...");
    }
}
window.addEventListener("load", function () {
    $(".app-tab-top>ul>li").each(function (i) {
        this.index = i;
        $(this).click(function () {
            $(this).addClass("on").siblings().removeClass("on");
            $(".app-tab-bot>div").eq(this.index).css("display", "block").siblings().css("display", "none");
        });
    });
}, false)