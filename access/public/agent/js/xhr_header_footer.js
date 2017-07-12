function succHeader(headerText) {
    $("body").prepend(headerText);
    $("nav .main_nav li").eq(1).addClass("active").siblings().removeClass("active");
}
get("xhr1", "user_center_header.html", succHeader);
get("xhr2", "aside.html", succAside);
get("xhr2", "user_center_footer.html", succFoot);
function succAside(data) {
    $(".page-content").html(data + $(".page-content").html());
    $(".page-left .left-content ul li").each(function () {
        $(this).removeClass("on")
    });
    $(".page-left .left-content ul>li.user-management>ul>li").eq(2).addClass("on").siblings().removeClass("on");
}
function succFoot(data) {
    $("body>.main").html($("body>.main").html() + data)
}