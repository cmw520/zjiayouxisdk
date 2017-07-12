var charge_subid = 0;
function do_check_account() {
    if ($("#palyer-account").val().trim() !== "") {
        $("#game-account").css("display", "none");
        sendData(url_check_member_account_post, {"name": $("#palyer-account").val()}, function (data) {
//            alert(data.msg);
            if (data.error === '1') {
                showMsg("#game-account", data.msg, "red");
            } else if (data.error === '0') {
                showMsg("#game-account", data.msg, "green");
            }
        });
    } else {
        $("#game-account").css("display", "block");
        //showMsg("#game-account","帐号不能为空..","red");
        $("#palyer-account").focus();
    }
}

function check_account() {
    do_check_account();
}

$(".top-up-amount > input").change(setPay);
$(".top-up-discount > input").change(setPay);

function setPay() {
//            alert('hi');
    var v    = $(".top-up-amount > input").val();
    var rate = $(".top-up-discount > input").val();
    if (rate !== 0 && rate !== '' && v !== 0 && v !== '') {
        var v_r = Math.round(100 * v * rate) / 100;
        $(".top-up-pay > input").val(v_r);
    }
}

function precision2(rate) {
    return Math.round(100 * rate) / 100;
}

function bind_click() {
    $("#pop-content>table tr").click(function () {
        var gameName = $(this).children(".game-name").children("span").html();
        var gameId   = $(this).children(".game-id").children("span").html();
        var rate     = $(this).attr("discount");
        $(".game-user-up").css("display", "block");
        $(".display1>.game-name>.choose-results").css("display", "block");
        $(".display1>.game-name>.choose-results").children("i").html(gameName);
        $(".display1>.game-name>.choose-results>input").val(gameId);

        $(".top-up-discount > input").val(precision2(rate));

        $(".popup_close").click();
        setPay();
        //$("#pop1").css("display", "none");
    });
}

function bind_click_sub() {
    $(".sub_select_tr").click(function () {
        var name = $(this).attr("data-agentName");
        $("#choosen_subname").text(name);
        charge_subid = $(this).attr("data-agentID");
        $(".popup_close").click();
    });
}

$("#charge_for_sub").click(function () {
    var a       = $("#cfs_amount").val();
    var paypwd  = $("#paypwd2").val();
    var subid   = charge_subid;
    var subname = $("#choosen_subname").text();

    if ($.trim(paypwd) === '') {
        $("#pay-pwd2").text("支付密码不能为空");
        $("#pay-pwd2").show();
        return;
    }

    if ($.trim(a) === '') {
        $("#pay-pwd2").text("支付金额不能为空");
        $("#pay-pwd2").show();
        return;
    }

    if (charge_subid === 0) {
        $("#pay-pwd2").text("请选择下级代理");
        $("#pay-pwd2").show();
        return;
    }

    $.post(url_check_paypwd_post, {"paypwd": paypwd}, function (data) {
        if (data.error === '0') {
            $("#pay-pwd2").text("");
            show_confirm_order("给下级代理充值", a, subid, 0, url_charge_for_sub_post, subid);
        } else if (data.error === '1') {
            $("#pay-pwd2").text("支付密码错误，请重新输入".data.msg);
            $("#pay-pwd2").show();
            return;
        }
    });

//        $(".mysubmit").click(handle_charge_sub);
});

function handle_charge_sub() {
//        alert(charge_subid);
//        return;
//        var a=$("#cfs_amount").val();
//        var paypwd=$("#paypwd2").val();
//        var subid=charge_subid;
//        var subname=$("#choosen_subname").text();
//        $.post(url_charge_for_sub_post,{"amount":a,"paypwd":paypwd},function(data){
//            
//        });
}

function show_confirm_order(text, amount, account, gameId, action, subid) {
    $(".coContent ul .payFor").text(text);
    $(".coContent ul .orderPrice .value i").text(amount);
    $(".coContent ul .needPay i").text(amount);

    $("#os_amount").val(amount);
    $("#os_mem_name").val(account);
    $("#os_gameid").val(gameId);
    $("#os_subid").val(subid);
    $("#confirm_form").attr("action", action);
    $("#order_confirm_btn").click();
}

/*********确认支付按钮被点击***********/
$(".display1 .top-up-btn").live("click", function () {

    var player_account = $("#palyer-account").val();
    var amount         = $(".display1 .top-up-amount>input").val();
    var payPwd1        = $("#payPwd1").val();

    if ($(".display1 .choose-results").children("i").html()) {
        if (player_account.trim() !== "") {
            if ((amount.trim() !== "") && (amount > 0)) {
                if (payPwd1.trim() !== "") {
                    var gameId     = $("#gameId").val();
                    var pay_amount = $(".top-up-pay > input").val();

                    sendData(url_charge_for_member_post,
                        {
                            "gameId"        : gameId,
                            "amount"        : amount,
                            "player_account": player_account,
                            "payPwd1"       : payPwd1
                        },
                        function (data) {
                            if (data.error === '0') {
                                showMsg("#pay-pwd1", '', "red");

                                $(".coContent ul .payFor").text("给玩家充值");
                                $(".coContent ul .orderPrice .value i").text(pay_amount);
                                $(".coContent ul .needPay i").text(pay_amount);

                                $("#os_amount").val(pay_amount);
                                $("#os_mem_name").val(player_account);
                                $("#os_gameid").val(gameId);
                                $("#confirm_form").attr("action", url_order_member_post);
                                $("#order_confirm_btn").click();

                            } else {
                                showMsg("#pay-pwd1", data.msg, "red");
                            }
                        });
                } else {
                    showMsg("#pay-pwd1", "支付密码不能为空", "red")
                }
            } else {
                showMsg("#pay-pwd1", "充值金额有误", "red")
            }
        } else {
            showMsg("#pay-pwd1", "游戏帐号不能为空", "red")
        }
    } else {
        showMsg("#pay-pwd1", "请先选择游戏..", "red")
    }
});

$("#mysubmit").click(function () {
    $("#confirm_form").submit();
});

$(".display1 .top-up-amount").live("click", function () {
    $("#game-account").css("display", "none");
    $("#pay-pwd1").css("display", "none");
})
$(".display1 .pay-pwd").live("click", function () {
    $("#game-account").css("display", "none");
    $("#pay-pwd1").css("display", "none");
})

window.addEventListener("load", function () {
    /**************给玩家充值导航栏切换*****************/
    $(".search-results .input-tab ul li").each(function (i) {
        this.index = i;
        $(this).live("click", function () {
            $(this).addClass("on").siblings().removeClass("on");
            $(".item-tab-all>.item-tab-con").eq(this.index).css("display", "block").siblings().css("display", "none");
        });
    });

    $("#topAmount .right-amout>ul>li").each(function () {
        $(this).click(function () {
            var money = $("#topAmount .right-amout >ul >li.amount-entered>input");
            $(this).addClass("on").siblings().removeClass("on");
            money.val($(this).children("i").html())
            $("#recharge-amount").html($(money).val() + "元");
        });
    });
    $("#topAmount>.right-amout>ul>li.amount-entered>input").keyup(function () {
        $("#display3 #recharge-amount").html($(this).val() + "元");
    });

    /***************帐户余额立即支付按钮****************/
    $("#pay-btn1").click(function () {
//        var account=$("#display3>.top-up-account:first-child>.right");
        var money = $("#display3 #recharge-amount").html().slice(0, - 1);
//        alert(payway+" "+money);
//        return;
        if (money > 0) {

            $(".coContent ul .payFor").text("账户余额充值");
            $(".coContent ul .orderPrice .value i").text(money);
            $(".coContent ul .needPay i").text(money);
//                  $(".coContent ul .orderNumbers .value").text(data.orderid);    
            if (payway === 'zfb') {
                $(".coContent ul .payW .value").text("支付宝");
            } else if (payway === 'unionpay') {
                $(".coContent ul .payW .value").text("银联");
            }

            $("#os_amount").val(money);
            $("#os_payway").val(payway);

//                    alert(money+" "+payway);
            $("#confirm_form").attr("action", url_order_balance_post);
            $("#order_confirm_btn").click();

        } else {
            showMsg("#display3>.pay-amount>.error-msg", "充值金额有误...", "red")
        }

    });
    /***********选择游戏**************/
//            $(".game-name .choose-btn").click(function () {
//                chooseBtn();
//            });

    function chooseBtn() {
        $("#pop1").css("display", "block");
        $(".game-user-up").css("display", "none");
        $("#game-account").css("display", "none");
        $("#pay-pwd1").css("display", "none");
        $("#pop-content>table tr").each(function () {
            $(this).live("click", function () {
                var gameName = $(this).children(".game-name").children("span").html();
                var gameId   = $(this).children(".game-id").children("span").html();
                if ($(".search-results>.input-tab>ul>li.on").html() === "给玩家充值") {
                    $(".game-user-up").css("display", "block");
                    $(".display1>.game-name>.choose-results").css("display", "block");
                    $(".display1>.game-name>.choose-results").children("i").html(gameName);
                    $(".display1>.game-name>.choose-results>input").val(gameId);
                } else if ($(".search-results>.input-tab>ul>li.on").html() === "给下级代理充值") {
                    $(".channel-top-up").css("display", "block");
//                            $("#display4>.game-name>.choose-results").css("display", "block");
//                            $("#display4>.game-name>.choose-results>i").html(gameName);
//                            $("#display4>.game-name>.choose-results>input").val(gameId);
                } else if ($(".search-results>.input-tab>ul>li.on").html() === "兑换平台币") {
                    $(".search-results4").css("display", "block");
                    $("#display4>.game-name>.choose-results").css("display", "block");
                    $("#display4>.game-name>.choose-results>i").html(gameName);
                    $("#display4>.game-name>.choose-results>input").val(gameId);
                }
                ;
                $("#pop1").css("display", "none");
            });
        });

    }

    /**************兑换平台币选择游戏按钮*********************/
    $(".dui_choose_btn").click(function () {
        chooseBtn();
    });
    /******************************************/
    $("#display4 .top-up-amount>.right>ul>.li").each(function () {
        $(this).click(function () {
            var money    = $("#display4 #topUpamount .right>ul>li.amount-entered>.amount-input1");
            var discount = $("#display4 .discount>p>i").html();
            $(this).addClass("on").siblings().removeClass("on");
            money.val($(this).children("i").html());
            $("#display4 .getMoney>p").html(Math.round($(money).val() / ("0." + discount)) + "元");
        });
    });
    $("#display4 .top-up-amount>.right>ul>.amount-entered>input").keyup(function () {
        var money    = $("#display4 #topUpamount .right>ul>li.amount-entered>.amount-input1");
        var discount = $("#display4 .discount>p>i").html();
        $("#display4 .getMoney>p").html(Math.round($(money).val() / ("0." + discount)) + "元");
    });
    /***************兑换  确认按钮**********************/
    $("#pay-btn2").click(function () {
        var payPass = $("#paypwd4").val();
        var gameId  = $("#display4>.game-name>.choose-results>input").val();
        var money   = $("#display4 .amount-input1").val();
        if (money > 0) {
            if (gameId.trim() !== "") {
                if (payPass.trim() !== "") {
                    sendData("{:U('user/ucenter/do_post')}", {"payPass": payPass}, function (data) {
                        if (true) {
                            window.location.href = "order.html"
                        } else {
                            showMsg("#pay-pwd4", "支付密码不正确..", "red")
                        }
                    })
                } else {
                    showMsg("#pay-pwd4", "支付密码不能为空..", "red")
                }
            } else {
                showMsg("#pay-pwd4", "请选择游戏..", "red")
            }
        } else {
            showMsg("#pay-pwd4", "充值金额有误..", "red")
        }
    });
}, false)

function closebox() {
    $('#pop1').hide();
    $('.game-user-up').show();
}

$(".search-btn").click(function () {
    $('#queryForm').submit();
});

$('.default_popup').popup({
    beforeOpen: bind_click
});

$('.sub_popup').popup({
    beforeOpen: bind_click_sub
});