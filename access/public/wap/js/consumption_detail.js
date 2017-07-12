$(".loading_more>.btn_top").click(function () {
    $("html,body").animate({scrollTop: 0}, 500);
});
// ysTabs("nav ul>li",".tabs>div","active");
$("nav ul>li").each(function (i) {
    this.index = i;
    if (i == 0) {
        $(".tabs>div").eq(i).show()
    }
    $(this).click(function () {
        $(this).addClass("active").siblings().removeClass("active");
        $(".tabs>div").eq(this.index).show().siblings().hide();
        $(".main_model_box").css("height", $("body").css("height"));
        $("#ajax_idx_more").attr('rel', 2);
    });
});

$(function () {
    var unlock1 = true;
    $(window).scroll(function () {
        var winH = $(window).height();
        var scrH = $(window).scrollTop();
        var htmH = $(document).height() - 100;
        if (winH + scrH >= htmH) {
            var obj = $("#ajax_idx_more");
            if ($(obj).length <= 0)
                return;
            ajaxidxmore(obj);
        }
    });
    function ajaxidxmore(obj) {
        if (! unlock1) {
            return;
        }
        var page = $(obj).attr("rel");

        if (! isNaN(page)) {
            unlock1     = false;
            var query   = {"p": page};
            var data_id = $(".active").attr('data-id');
            if (data_id == '1') {
                var url      = $("#paySuccessData").val();
                var str      = $(".list_text").html();
                var obj_html = $(".list_text");
            } else {
                var url      = $("#payFailData").val();
                var str      = $(".gameCharge").html();
                var obj_html = $(".gameCharge");
            }
            $.post(url, query, function (data) {
                var top = $(document).scrollTop();
                $(obj).attr("rel", data.page);
                $(document).scrollTop(top);
                var content = data.content;
                $.each(content, function (n, v) {
                    str += '<ul class="item"><li><b>游戏名：</b><span>"' + v.name + '"</span></li><li><b>消费：</b><span><i>"' + v.real_amount + '"</i>元</span></li><li><b>消费情况：</b><span><i>"' + v.status + '"</i></span></li><li><b>订单号：</b><span>"' + v.order_id + '"</span></li><li><b>消费时间：</b><span>"' + v.create_time + '"</span></li></ul>';
                });
                if (data.page != "end") {
                    unlock1 = true;
                    $(obj).html("加载中...");
                    $(obj_html).html(str);
                } else {
                    unlock1 = true;
                    $(obj_html).html(str);
                    $(obj).html("已到最后...");
                }
            }, "json")
        }
    }
})