$(function () {
    $("#tag0").show().siblings().hide();
    $('.article-list-tab>a').click(function (event) {
        var indexno = $('.article-list-tab>a').index(this);
        var tags    = "#tag" + indexno;
        $(this).addClass("cur").siblings().removeClass("cur");
        $(tags).css({"display": "block"}).siblings().css({"display": "none"});
    });
})
$(function () {
    $("#close-ad").click(
        function () {
            $(".bottom-ad").hide();
        });
})
