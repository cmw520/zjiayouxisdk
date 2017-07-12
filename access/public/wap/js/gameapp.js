var GameApp = {
    lastver      : 10007
    ,
    lastverstring: "1.0.07"
    ,
    request      : function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r   = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }
    ,
    verstring    : function () {
        var u = navigator.userAgent;
        if (u.split(" ")[u.split(" ").length - 2] == "Gamezxs") {
            var ver = u.split(" ")[u.split(" ").length - 1];
            return ver;
        }
    }
    ,
    ver          : function () {
        var u = navigator.userAgent;
        if (u.split(" ")[u.split(" ").length - 2] == "Gamezxs") {
            var ver = u.split(" ")[u.split(" ").length - 1];
            return parseInt(ver.split(".")[0]) * 10000 + parseInt(ver.split(".")[1]) * 100 + parseInt(ver.split(".")[2]);
        }
        ;
    }
    ,
    isapp        : function () {
        var u = navigator.userAgent;
        if (u.split("Gamezxs").length > 1) {
            return true;
        }
        else {
            return false;
        }
    }
    ,
    uuid         : function () {
        var u = navigator.userAgent;
        if (u.split("uuid ").length > 1) {
            return u.split("uuid ")[1].split(" ")[0]
        }
        ;
    }
    ,
    phonenum     : function () {
        var u = navigator.userAgent;
        if (u.split("phoneNum ").length > 1) {
            var p = u.split("phoneNum ")[1].split(" ")[0];
            if (p.split("+86").length > 1)p = p.split("+86")[1];
            if (p.length > 6)return p;
        }
        ;
    }
    ,
    isios        : function () {
        var u = navigator.userAgent;
        if (u.split("uuid ios").length > 1) {
            return true;
        }
        else {
            return false;
        }
    }
    ,
    isjiuju      : function () {
        var u = navigator.userAgent;
        if (u.split(" jiuju ").length > 1) {
            return true;
        }
        else {
            return false;
        }
    }
}

