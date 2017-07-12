$(".li_choose li").each(function (i) {
    this.index = i;
    $(this).click(function () {
        $(this).addClass("hide").siblings().removeClass("hide");
        $(".libao_list1>div").eq(this.index).show().siblings().hide();
    });
});