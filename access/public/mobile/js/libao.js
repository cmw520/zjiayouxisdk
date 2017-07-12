window.addEventListener("load", function () {
    /********领取礼包关闭按钮*******/
    $(".msg_box>.close_btn").click(function () {
        $(".msg_box").hide();
        $(".getBox").hide();
//        location.reload();
    });
    var canUpdate = false;
    $(".getGift").each(function () {
        $(this).click(function () {
            if (parseInt($(".getBox").css("width")) > 900) {
                $(".msg_box").css("margin-top", "200px");
            } else if (window.innerWidth > 414) {
                $(".msg_box").css("margin-top", "100px");
            }
            var url    = $(this).siblings('.getGiftUrl').val();
            var app_id = $(this).siblings('.app_id').val();
            var giftid = $(this).siblings('.giftid').val();
            console.log(url + "---" + app_id + "---" + giftid);
            var me = this;
            sendData(url, {'app_id': app_id, 'giftid': giftid}, function (data) {
                if (data.state == 'success') {
                    $(".getstatus").html('领取成功');
                    $(".tips").html(data.info);
                    $(".giftcode").html('礼包码：<span style="color:red">' + data.giftcode + '</span>');
                    //$(".getBox").show();
                    //$(".getBox .msg_box").show();
                    huosdk_copystr(data.giftcode);
                    if (! $(me).hasClass('canDel')) {
                        $(me).parent().parent().remove();
                    } else {
                        canUpdate = true;
                    }
                } else {
                    $(".giftcode").html('<span style="color:red">' + data.info + '</span>');
                    $(".getBox").show();
                    $(".getBox .msg_box").show();
                }
            });
        });
    });
    //复制 礼包码
    $(".copyGift").each(function (i) {
        $(this).attr("id", "foo" + i);
        $(this).click(function (event) {
            event.preventDefault();
            if (parseInt($(".getBox").css("width")) > 900) {
                $(".copy_box").css("margin-top", "200px");
            } else if (window.innerWidth > 414) {
                $(".copy_box").css("margin-top", "100px");
            }
            huosdk_copystr($(this).attr('data-clipboard-text'));
            //$(".getBox").show();
            //$(".getBox .msg_box").hide();
            //$(".getBox .copy_box").show();
        });
    });
    $(".getBox").click(function () {
        $(this).hide();
        $(".copy_box").hide();
        if (canUpdate) {
            location.href = location.href + Math.random();
        }
    });
    $(".footer_nav").css("max-width", $("body").css("width"))
}, false)