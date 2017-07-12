//document.querySelector(".back a img").onclick=function(event){
//    event.preventDefault();
//    window.history.back();
//}
//var main_title=document.getElementsByClassName("main_title")[0];
//main_title.style.transform="translateX(-"+parseInt(window.getComputedStyle(main_title).width)/2+"px)";

/*************返回按钮****************/
$(".back_btn span").click(function () {
    window.history.back();
    location.reload();
//location.href="/";
});
/**********邮箱图标*************/
if ($(".small_icon>p").html() > 0) {
    $(".small_icon>p").css("display", "inline-block");
} else {
    $(".small_icon>p").css("display", "none");
}
/***********网游分类 li宽度***************/
$("#fl_list_down>.fl_list_down li").each(function (i) {
    if (i % 2 == 0) {
        $(this).css("margin-right", "15px");
    }

    $(this).css("width", (parseInt($(".fl_list_down").css("width")) - 20) / 2 + "px");
});

/***********header文字居中 *******************/
//$(".text-center").css("transform","translateX(-"+parseInt($(".text-center").css("width"))/2+"px)");
/**********抢礼包的li宽度**************/
$(".li_choose li").css("width", (parseInt($(".li_choose").css("width")) - 4) / 2 + "px");
