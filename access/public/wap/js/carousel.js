window.addEventListener("load", function () {
    function pi(el) {
        return parseInt(el);
    }

    function setBannner() {
        var proportion = pi($("#banner_ad .slider>ul>li>a>img:first-child").css("width")) / pi($("#banner_ad .slider>ul>li>a>img:first-child").css("height"));
        // var height=Math.floor(pi($("body").css("width"))/proportion);
        var height     = 120;
        $("#banner_ad .slider>ul>li").each(function () {
            $(this).css({
                "width" : $("body").css("width"),
                "height": height + "px"
            });
            $(this).find("img").css("visibility", 'visible');
        });
        $("#banner_ad").css("height", height + "px");
        $("#banner_ad .slider").css({
            "width" : $("body").css("width"),
            "height": height + "px"
        });
        $("#banner_ad .slider>ul").css("width", pi($("#banner_ad .slider>ul>li").length) * pi($("body").css("width")));
        $(".slider").yxMobileSlider({width: pi($("body").css("width")), height: height, during: 5000});
    }

    setBannner();
}, false);