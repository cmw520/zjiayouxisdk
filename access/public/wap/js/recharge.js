var bnftype = $("#bnftype").val();
var rate    = $("#rate").val();
var rebate  = $("#rebate").val();
$("header>h3").css("transform", "translateX(-" + parseInt($("header>h3").css("width")) / 2 + "px)");
/*****充值金额*****/
$(".recharge_amount>.btn_group>li").each(function (i) {
    this.index = i + 1;
    if (this.index % 3 === 0) {
        $(this).css("margin-right", "0px");
    }
    $(this).click(function () {
        var money = $(this).html();
        var ptb   = money;
        $(".btn_group>input").val("");
        $(this).addClass("active").siblings().removeClass("active");
        if (bnftype == 1) {
            money = (money * rate).toFixed(0);
        } else if (bnftype == 2) {
            ptb = (money * rebate).toFixed(0);
        }
        $(".canGet>span").html(ptb);
        $(".fact>.paymoney").html(money);
    });
});
$(".btn_group>input").focus(function () {
    $(".canGet>span").html(0);
    $(".btn_group>.active").removeClass("active");
});
/*************充值金额输入******************/
$(".btn_group>input").keyup(function (event) {
    var input_value = $(this).val();
    var len         = $(this).val().length;
    var str         = "";
    for (var k in input_value) {
        var v = input_value[k];
        if ((v >= 0) && (v <= 9)) {
            str += v;
        }
    }
    if (str > 50000) {
        str = 50000
    }
    $(this).val(str);
    var money = str;
    var ptb   = money;
    if (bnftype == 1) {
        money = (money * rate).toFixed(0);
    } else if (bnftype == 2) {
        ptb = (money * rebate).toFixed(0);
    }
    $(".canGet>span").html(ptb);
    $(".fact>.paymoney").html(money);
});

/**************支付方式******************/
var topClick = 0;
$(".change_way>.way>li").each(function () {
    $(this).on('click', function (event) {
        if (new Date().getTime() - topClick > 3000) {
            topClick = new Date().getTime();
            if ($(".btn_group>.active").html()) {
                var count = $(".btn_group>.active").html();
            } else {
                var count = $(".btn_group>input").val();
                if (! ((0 < count) && (count <= 50000))) {
                    alert("输入的金额不对...");
                    return false;
                }
            }
            /*var form_data = {
             paytype: $(this).attr("data-way"),
             money: count,
             paytoken: $("#paytoken").val(),
             randnum: (''+Math.random()).replace(".",""),
             }; */
            $("#money").val(count);
            $("#randnum").val(('' + Math.random()).replace(".", ""));
            $("#paytype").val($(this).attr("data-way"));
            $("#payform").submit();
            //var vurl = $("#payform").attr("action");
            //sendData(vurl,form_data,preorder_succ,'',"POST","JSON");
        }
    });
});

//下单成功时调用原生支付
function preorder_succ(result) {
    if ('success' == result.state) {
        var txt = '' + result.payinfo;
        window.huosdk.huoPay(txt);
    } else {
        showMobileAlert(result.info, 'null', '确定');
    }
}

/*************立即冲值按钮******************/
$(".instant_recharge>button").click(function () {
    var money = $(".btn_group>.active").html();
});

