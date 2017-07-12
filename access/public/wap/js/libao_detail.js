var clipboard = new Clipboard('#copytext');

clipboard.on('success', function (e) {
    alert("成功复制")
});

clipboard.on('error', function (e) {
    alert("复制失败");
});
$(".model_box>.box>.close_btn>.close").click(function () {
    $(".model_box>.box").hide();
    $(".model_box").hide();
});