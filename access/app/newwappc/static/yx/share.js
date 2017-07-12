var yxshare = {};

yxshare.funcs = {};
yxshare.ui    = {};

yxshare.ui.alert = function (txt) {
    layer.alert(txt, {shift: 7});
};

yxshare.ui.alert2 = function (txt) {
    layer.alert(txt, {shift: 7}, function () {
        location.reload();
    });
};
yxshare.ui.alert3 = function (txt, url) {
    layer.alert(txt, {shift: 7}, function () {
        location.href = url;
        layer.closeAll();
    });
};

yxshare.ui.redirect_delay = function (url, time) {
    setTimeout(function () {
        location.href = url;
    }, time);
}

yxshare.ui.notice  = function (txt) {
    layer.msg(txt, {
            shift : 7,
            offset: '20px',
            area  : '300px'
        }
    );
};
yxshare.ui.notice2 = function (txt) {
    layer.msg(txt, {shift: 7});
};

yxshare.ui.confirm = function (txt, func1) {
    layer.confirm(
        txt, {
            shift: 7,
            btn  : ['确定', '取消']
        },
        function () {
            func1();
            layer.closeAll();
        },
        function () {
        }
    );
};

yxshare.funcs.validatePhone = function (phone) {
    if ((/^1[34578]\d{9}$/.test(phone))) {
        return true;
    } else {
        return false;
    }
};

yxshare.funcs.validateUsername = function (v) {
    if ((/^[a-zA-Z]{1}[a-zA-Z0-9]{5,29}$/.test(v))) {
        return true;
    } else {
        return false;
    }
};

yxshare.funcs.validatePassword = function (str) {
    var patrn = /^(\w){6,20}$/;
    if (! patrn.exec(str)) return false;
    return true;
};