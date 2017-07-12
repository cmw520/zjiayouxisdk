function succHeader(headerText) {
    $("body").prepend(headerText);
    $("nav .main_nav li").eq(2).addClass("active").siblings().removeClass("active");
}
function get(xhrName, url, succ) {
    var xhrName = new xhr();
    if (xhrName) {
        xhrName.open("GET", url, true);
        xhrName.onreadystatechange = function (data) {
            if (xhrName.readyState == 4) {
                if (xhrName.status == 200) {
                    var headerText = xhrName.responseText;
                    succ(headerText)
                } else {

                }
            }
        }
        xhrName.send(null);
    }
}
get("header", "user_center_header.html", succHeader);
get("footer", "user_center_footer.html", succFoot);
get("aside", "aside.html", succAside);
function succFoot(data) {
    $("body").append(data)
}
function succAside(data) {
    $(".mygameCenter").html(data + $(".mygameCenter").html());
    $(".page-left .left-content ul li").eq(0).children("ul").children("li").eq(1).addClass("on").siblings().removeClass("on");
}