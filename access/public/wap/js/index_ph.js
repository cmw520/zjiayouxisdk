$(".Mango15>div:nth-child(5)>img").each(function () {
    if ($(this).attr("src") === "images/13.png") {
        $(this).css("height", "2px");
        $(this).css("transform", "translateY(6px)");
    }
})
function search_game() {
    var key = $("#game_text").val();
    if (key.trim() !== "") {
        sendData("1.php", {"keyWord": key}, function (data) {
            alert("成功");
        })
    } else {
        alert("搜索内容不能为空..");
    }
}