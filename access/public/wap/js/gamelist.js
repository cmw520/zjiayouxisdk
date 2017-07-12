$(".Mango14>li").each(function () {
    if ($(this).find("img").eq(1).attr("src") === "images/13.png") {
        $(this).find("img").eq(1).css({"height": "4px", "margin-top": "4px"});
    }
});