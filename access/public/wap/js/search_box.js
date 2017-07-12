$(".icon_search>span").click(function () {
    if ($(".search_box").css("display") === "block") {
        $(".search_box").slideUp();
    } else {
        $(".search_box").slideDown();
    }
});
$(".search_container>button").click(function () {
    var keyWord = $(".search_container>input").val();
    console.log(keyWord);
    if (keyWord.trim() !== "") {
        sendData("1.php", {"keyWords": keyWord}, function (data) {
            if (data.num === "12345678912") {
                alert("����ɹ�...");
            }
        }, function () {
            console.log(arguments)
        });
    } else {
        alert("��������Ϸ����...");
    }
});