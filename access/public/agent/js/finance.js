function setAmount(obj) {
    var amount  = $('#amount').val();
    var mymoney = parseFloat($("#mymoney").val());
    var count   = $("#count").val();
    $("#error-amount").hide();
    if ($.trim(amount) == '') {
        $("#withdrawMoney").html("0.00");
        return;
    }
    amount = parseFloat(amount);

    if (amount < withdraw_base) {

        $("#error-amount").html("提现金额不能小于" + withdraw_base + "元");
        $("#error-amount").show();
        //$('#amount').val("");
        $('#amount').focus();
        calcFee();
        return;
    } else if (amount > mymoney) {
        $("#error-amount").html("提现金额不能大于账户余额");
        $("#error-amount").show();
        //$('#amount').val("");
        $('#amount').focus();
        calcFee();
        return;
    } else if (amount - 999999999 > 0) {
        $('#amount').val(0);
        calcFee();
        return;
    } else {
        $('#amount').val(parseFloat(amount).toFixed(2));
        calcFee();
    }
}
//计算手续费
function calcFee() {
    var count  = $("#txcount").val();			//获取支付笔数
    var amount = $("#amount").val();			//获取提现金额
    var fee    = $("#withdrawFee");					//获取手续费对象
    var money  = $("#withdrawMoney");			//获取手续费对象
    //判断金额是否为空
    if (amount == "") {
        fee.html("0.00");
        money.html("0.00");
        return false;
    }
    amount  = /^\d+\.?\d{0,2}/.exec(amount)
    //前台手续费计算，后台再计算
    var num = parseFloat(amount);
    if (count > 0) {
        var calcfee = num < 200 ? 2.00 : num * 0.01;
        if (num == 0) {
            fee.html("0.00");
            money.html("0.00");
        } else {
            fee.html(toDecimal2(calcfee.toFixed(2)));
            money.html(toDecimal2((num - calcfee).toFixed(2)));
        }
    } else {
        money.html(toDecimal2(num.toFixed(2)));
    }
    $("#amount").val(amount);
}
//保留两位小数
function toDecimal2(x) {
    var f = parseFloat(x);
    if (isNaN(f)) {
        return false;
    }
    var f  = Math.round(x * 100) / 100;
    var s  = f.toString();
    var rs = s.indexOf('.');
    if (rs < 0) {
        rs = s.length;
        s += '.';
    }
    while (s.length <= rs + 2) {
        s += '0';
    }
    return s;
}