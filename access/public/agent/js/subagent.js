function succHeader(headerText) {
    var main       = $("body")[0];
    main.innerHTML = headerText + main.innerHTML;
    $("nav .main_nav li").eq(1).addClass("active").siblings().removeClass("active");
}

function locatePage_queryForm(page) {
    sendData("{:U('user/ucenter/do_post')}", {"page": page}, function (data) {
        alert("页数已提交....");
    })
}