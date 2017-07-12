//$("#copyBox").css("width",parseInt($(".code").css("width"))-parseInt($(".code>span").css("width"))-10+"px");
$(".model_box").css("width", window.innerWidth);
$(".model_box").css("height", window.innerHeight);
/*********领取按钮**********/
//function bindClick(){
//    $(".otn").each(function(){
//        if($(this).hasClass("over")){
//            $(this).html("查看详情");
//            $(this).css({"background":"#aaa"});
//        }
//           this.canClick=true;
//            $(this).die().click(function(event){
//                event.preventDefault();
//                if(this.canClick){
//                    if(!$(this).hasClass("over")){
//                        $(".model_box").show();
//                        $(".model_box>.box").show();
//                        $(this).css({"background":"#aaa"}).html("查看详情");
//                        $(this).removeAttr("href");
//                        $(this).addClass("over");
//                        return false;
//                    }else{
//                        $(this).removeAttr("href");
//                        window.location.href='libao_detail.html';
//                        $(this).unbind("click");
//                    }
//                }
//
//            });
//
//    });
//}
//bindClick();
function GetMore() {
    var index = $(".otn").length;
    sendData("2.php", {"index": index}, function (data) {
        var list = document.createDocumentFragment();
        /*********模拟数据**********/
        for (var k in data) {
            var v     = data[k];
            var count = random();//此处需改成v.width
            var el    = document.createElement("div");
            el.setAttribute("class", "lb-box");
            el.innerHTML = '<div class="lb-box-show">\
        <div class="pic"><a href="#"><img src=' + v.src + ' width="60" height="60"></a></div>\
        <div class="info">\
            <p class="tit"><a href="#">' + v.name + '</a></p>\
        <p class="time">发布时间：' + v.time + '</p>\
        <p class="jdt">\
        <span>\
        <b style="width:' + count + '%"></b>\
        </span>\
        <em>剩' + (100 - count) + '%</em>\
        </p>\
        </div>\
        <div class="btn-lq">\
        <a class="otn ' + v.over + '" href="#">抢礼包</a>\
        </div>';
            list.appendChild(el);
        }
        $(".lb-list").append(list);
        bindClick();
    });
}
function random() {
    return Math.floor(Math.random() * 100 + 1);
}
var clipboard = new Clipboard('#copytext');

clipboard.on('success', function (e) {
    alert("成功复制")
});

clipboard.on('error', function (e) {
    alert("复制失败");
});
