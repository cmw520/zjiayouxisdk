$(".info_list>li>.gender>div").each(function () {

    $(this).click(function () {

        $(this).siblings().find("i").removeClass("active");

        $(this).find("i").addClass("active");

    });

});

var TimeFn;

$(".info_list>li>span").each(function () {

    this.dbClick = false;

    $(this).click(function () {

        if (this.dbClick) {

            if (! $(this).parent().hasClass("noCan")) {

                var input = $("<input type='text'/>");

                console.log($(this).html());

                input.val($(this).html());

                $(this).parent().append(input);

                $(this).remove();

            }

        }

        this.dbClick = true;

        console.log("click");

        // 取消上次延时未执行的方法

        clearTimeout(TimeFn);

        //执行延时

        TimeFn = setTimeout(function () {

            this.dbClick = false;

        }, 300);

    });

});

$(".confim_change>button").click(function () {

    var name = $("#name>input").val();

    var gender = $("#gender").find(".active").parent().siblings("span").attr("data-id");

    var email = $("#email>input").val();

    if (! name) {
        name = $("#name>span").html()
    }

    if (! email) {
        email = $("#email>span").html()
    }

    sendData("1.php", {"name": name, "gender": gender, "email": email}, function () {

        alert("已发送数据..");

    })

});

//  预览图片
function seeImg(el, imgBox) {
    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }

    el.on("change", function () {
        var objUrl = getObjectURL(el[0].files[0]);
        if (objUrl) {
            imgBox.attr("src", objUrl);
            imgBox.attr("class", "fileImg");
        }
    })
}

$('.headImage>img').click(function () {
    $('#head_img').trigger('click');
    var el     = $('#head_img');
    var imgBox = $('#showimg');
    seeImg(el, imgBox);
});

