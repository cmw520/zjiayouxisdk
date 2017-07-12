window.addEventListener("load", function () {
    /********复制按钮*******/
    var clipboard;
    $(".box>.item>.right>a").each(function (i) {
        $(this).attr("id", "foo" + i);
        $(this).click(function (event) {
            event.preventDefault();
            if (parseInt($(".getBox").css("width")) > 900) {
                $(".copy_box").css("margin-top", "200px");
            }
            huosdk_copystr($(this).attr('data-clipboard-text'));
            $(".getBox").show();
            $(".getBox .copy_box").show();
        });
    });
    $(".getBox").click(function () {
        $(this).hide();
        $(".copy_box").hide();
    });
    $(".footer_nav").css("max-width", $("body").css("width"))
}, false)