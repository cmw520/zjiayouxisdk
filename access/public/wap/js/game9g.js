var _czc                  = _czc || [];
window.addEventListener   = window.addEventListener || function (e, f) {
        window.attachEvent('on' + e, f);
    };
document.addEventListener = document.addEventListener || function (e, f) {
        document.attachEvent('on' + e, f);
    };
! function (a, b) {
    "function" == typeof define && (define.amd || define.cmd) ? define(function () {
        return b(a)
    }) : b(a, ! 0)
}(this, function (a, b) {
    function c(b, c, d) {
        a.WeixinJSBridge ? WeixinJSBridge.invoke(b, e(c), function (a) {
            g(b, a, d)
        }) : j(b, d)
    }

    function d(b, c, d) {
        a.WeixinJSBridge ? WeixinJSBridge.on(b, function (a) {
            d && d.trigger && d.trigger(a), g(b, a, c)
        }) : d ? j(b, d) : j(b, c)
    }

    function e(a) {
        return a = a || {}, a.appId = z.appId, a.verifyAppId = z.appId, a.verifySignType = "sha1", a.verifyTimestamp = z.timestamp + "", a.verifyNonceStr = z.nonceStr, a.verifySignature = z.signature, a
    }

    function f(a) {
        return {
            timeStamp: a.timestamp + "",
            nonceStr : a.nonceStr,
            "package": a.package,
            paySign  : a.paySign,
            signType : a.signType || "SHA1"
        }
    }

    function g(a, b, c) {
        var d, e, f;
        switch (delete b.err_code, delete b.err_desc, delete b.err_detail, d = b.errMsg, d || (d = b.err_msg, delete b.err_msg, d = h(a, d, c), b.errMsg = d), c = c || {}, c._complete && (c._complete(b), delete c._complete), d = b.errMsg || "", z.debug && ! c.isInnerInvoke && alert(JSON.stringify(b)), e = d.indexOf(":"), f = d.substring(e + 1)) {
            case"ok":
                c.success && c.success(b);
                break;
            case"cancel":
                c.cancel && c.cancel(b);
                break;
            default:
                c.fail && c.fail(b)
        }
        c.complete && c.complete(b)
    }

    function h(a, b) {
        var d, e, f, g;
        if (b) {
            switch (d = b.indexOf(":"), a) {
                case o.config:
                    e = "config";
                    break;
                case o.openProductSpecificView:
                    e = "openProductSpecificView";
                    break;
                default:
                    e = b.substring(0, d), e = e.replace(/_/g, " "), e = e.replace(/\b\w+\b/g, function (a) {
                        return a.substring(0, 1).toUpperCase() + a.substring(1)
                    }), e = e.substring(0, 1).toLowerCase() + e.substring(1), e = e.replace(/ /g, ""), - 1 != e.indexOf("Wcpay") && (e = e.replace("Wcpay", "WCPay")), f = p[e], f && (e = f)
            }
            g = b.substring(d + 1), "confirm" == g && (g = "ok"), "failed" == g && (g = "fail"), - 1 != g.indexOf("failed_") && (g = g.substring(7)), - 1 != g.indexOf("fail_") && (g = g.substring(5)), g = g.replace(/_/g, " "), g = g.toLowerCase(), ("access denied" == g || "no permission to execute" == g) && (g = "permission denied"), "config" == e && "function not exist" == g && (g = "ok"), b = e + ":" + g
        }
        return b
    }

    function i(a) {
        var b, c, d, e;
        if (a) {
            for (b = 0, c = a.length; c > b; ++ b)d = a[b], e = o[d], e && (a[b] = e);
            return a
        }
    }

    function j(a, b) {
        if (z.debug && ! b.isInnerInvoke) {
            var c = p[a];
            c && (a = c), b && b._complete && delete b._complete, console.log('"' + a + '",', b || "")
        }
    }

    function k() {
        if (! ("6.0.2" > w || y.systemType < 0)) {
            var b = new Image;
            y.appId = z.appId, y.initTime = x.initEndTime - x.initStartTime, y.preVerifyTime = x.preVerifyEndTime - x.preVerifyStartTime, C.getNetworkType({
                isInnerInvoke: ! 0,
                success      : function (a) {
                    y.networkType = a.networkType;
                    var c         = "https://open.weixin.qq.com/sdk/report?v=" + y.version + "&o=" + y.isPreVerifyOk + "&s=" + y.systemType + "&c=" + y.clientVersion + "&a=" + y.appId + "&n=" + y.networkType + "&i=" + y.initTime + "&p=" + y.preVerifyTime + "&u=" + y.url;
                    b.src         = c
                }
            })
        }
    }

    function l() {
        return (new Date).getTime()
    }

    function m(b) {
        t && (a.WeixinJSBridge ? b() : q.addEventListener && q.addEventListener("WeixinJSBridgeReady", b, ! 1))
    }

    function n() {
        C.invoke || (C.invoke = function (b, c, d) {
            a.WeixinJSBridge && WeixinJSBridge.invoke(b, e(c), d)
        }, C.on = function (b, c) {
            a.WeixinJSBridge && WeixinJSBridge.on(b, c)
        })
    }

    var o, p, q, r, s, t, u, v, w, x, y, z, A, B, C;
    if (! a.jWeixin)return o = {
        config                 : "preVerifyJSAPI",
        onMenuShareTimeline    : "menu:share:timeline",
        onMenuShareAppMessage  : "menu:share:appmessage",
        onMenuShareQQ          : "menu:share:qq",
        onMenuShareWeibo       : "menu:share:weiboApp",
        previewImage           : "imagePreview",
        getLocation            : "geoLocation",
        openProductSpecificView: "openProductViewWithPid",
        addCard                : "batchAddCard",
        openCard               : "batchViewCard",
        chooseWXPay            : "getBrandWCPayRequest"
    }, p = function () {
        var b, a = {};
        for (b in o)a[o[b]] = b;
        return a
    }(), q = a.document, r = q.title, s = navigator.userAgent.toLowerCase(), t = - 1 != s.indexOf("micromessenger"), u = - 1 != s.indexOf("android"), v = - 1 != s.indexOf("iphone") || - 1 != s.indexOf("ipad"), w = function () {
        var a = s.match(/micromessenger\/(\d+\.\d+\.\d+)/) || s.match(/micromessenger\/(\d+\.\d+)/);
        return a ? a[1] : ""
    }(), x = {initStartTime: l(), initEndTime: 0, preVerifyStartTime: 0, preVerifyEndTime: 0}, y = {
        version      : 1,
        appId        : "",
        initTime     : 0,
        preVerifyTime: 0,
        networkType  : "",
        isPreVerifyOk: 1,
        systemType   : v ? 1 : u ? 2 : - 1,
        clientVersion: w,
        url          : encodeURIComponent(location.href)
    }, z = {}, A = {_completes: []}, B = {state: 0, res: {}}, m(function () {
        x.initEndTime = l()
    }), C = {
        config                    : function (a) {
            z = a, j("config", a), m(function () {
                c(o.config, {verifyJsApiList: i(z.jsApiList)}, function () {
                    A._complete = function (a) {
                        x.preVerifyEndTime = l(), B.state = 1, B.res = a
                    }, A.success = function () {
                        y.isPreVerifyOk = 0
                    }, A.fail = function (a) {
                        A._fail ? A._fail(a) : B.state = - 1
                    };
                    var a = A._completes;
                    return a.push(function () {
                        z.debug || k()
                    }), A.complete = function (b) {
                        for (var c = 0, d = a.length; d > c; ++ c)a[c](b);
                        A._completes = []
                    }, A
                }()), x.preVerifyStartTime = l()
            }), z.beta && n()
        }, ready                  : function (a) {
            0 != B.state ? a() : (A._completes.push(a), ! t && z.debug && a())
        }, error                  : function (a) {
            "6.0.2" > w || (- 1 == B.state ? a(B.res) : A._fail = a)
        }, checkJsApi             : function (a) {
            var b = function (a) {
                var c, d, b = a.checkResult;
                for (c in b)d = p[c], d && (b[d] = b[c], delete b[c]);
                return a
            };
            c("checkJsApi", {jsApiList: i(a.jsApiList)}, function () {
                return a._complete = function (a) {
                    if (u) {
                        var c = a.checkResult;
                        c && (a.checkResult = JSON.parse(c))
                    }
                    a = b(a)
                }, a
            }())
        }, onMenuShareTimeline    : function (a) {
            d(o.onMenuShareTimeline, {
                complete: function () {
                    c("shareTimeline", {
                        title  : a.title || r,
                        desc   : a.title || r,
                        img_url: a.imgUrl,
                        link   : a.link || location.href
                    }, a)
                }
            }, a)
        }, onMenuShareAppMessage  : function (a) {
            d(o.onMenuShareAppMessage, {
                complete: function () {
                    c("sendAppMessage", {
                        title   : a.title || r,
                        desc    : a.desc || "",
                        link    : a.link || location.href,
                        img_url : a.imgUrl,
                        type    : a.type || "link",
                        data_url: a.dataUrl || ""
                    }, a)
                }
            }, a)
        }, onMenuShareQQ          : function (a) {
            d(o.onMenuShareQQ, {
                complete: function () {
                    c("shareQQ", {
                        title  : a.title || r,
                        desc   : a.desc || "",
                        img_url: a.imgUrl,
                        link   : a.link || location.href
                    }, a)
                }
            }, a)
        }, onMenuShareWeibo       : function (a) {
            d(o.onMenuShareWeibo, {
                complete: function () {
                    c("shareWeiboApp", {
                        title  : a.title || r,
                        desc   : a.desc || "",
                        img_url: a.imgUrl,
                        link   : a.link || location.href
                    }, a)
                }
            }, a)
        }, startRecord            : function (a) {
            c("startRecord", {}, a)
        }, stopRecord             : function (a) {
            c("stopRecord", {}, a)
        }, onVoiceRecordEnd       : function (a) {
            d("onVoiceRecordEnd", a)
        }, playVoice              : function (a) {
            c("playVoice", {localId: a.localId}, a)
        }, pauseVoice             : function (a) {
            c("pauseVoice", {localId: a.localId}, a)
        }, stopVoice              : function (a) {
            c("stopVoice", {localId: a.localId}, a)
        }, onVoicePlayEnd         : function (a) {
            d("onVoicePlayEnd", a)
        }, uploadVoice            : function (a) {
            c("uploadVoice", {localId: a.localId, isShowProgressTips: 0 == a.isShowProgressTips ? 0 : 1}, a)
        }, downloadVoice          : function (a) {
            c("downloadVoice", {serverId: a.serverId, isShowProgressTips: 0 == a.isShowProgressTips ? 0 : 1}, a)
        }, translateVoice         : function (a) {
            c("translateVoice", {localId: a.localId, isShowProgressTips: 0 == a.isShowProgressTips ? 0 : 1}, a)
        }, chooseImage            : function (a) {
            c("chooseImage", {
                scene   : "1|2",
                count   : a.count || 9,
                sizeType: a.sizeType || ["original", "compressed"]
            }, function () {
                return a._complete = function (a) {
                    if (u) {
                        var b = a.localIds;
                        b && (a.localIds = JSON.parse(b))
                    }
                }, a
            }())
        }, previewImage           : function (a) {
            c(o.previewImage, {current: a.current, urls: a.urls}, a)
        }, uploadImage            : function (a) {
            c("uploadImage", {localId: a.localId, isShowProgressTips: 0 == a.isShowProgressTips ? 0 : 1}, a)
        }, downloadImage          : function (a) {
            c("downloadImage", {serverId: a.serverId, isShowProgressTips: 0 == a.isShowProgressTips ? 0 : 1}, a)
        }, getNetworkType         : function (a) {
            var b = function (a) {
                var c, d, e, b = a.errMsg;
                if (a.errMsg = "getNetworkType:ok", c = a.subtype, delete a.subtype, c)a.networkType = c; else switch (d = b.indexOf(":"), e = b.substring(d + 1)) {
                    case"wifi":
                    case"edge":
                    case"wwan":
                        a.networkType = e;
                        break;
                    default:
                        a.errMsg = "getNetworkType:fail"
                }
                return a
            };
            c("getNetworkType", {}, function () {
                return a._complete = function (a) {
                    a = b(a)
                }, a
            }())
        }, openLocation           : function (a) {
            c("openLocation", {
                latitude : a.latitude,
                longitude: a.longitude,
                name     : a.name || "",
                address  : a.address || "",
                scale    : a.scale || 28,
                infoUrl  : a.infoUrl || ""
            }, a)
        }, getLocation            : function (a) {
            a = a || {}, c(o.getLocation, {type: a.type || "wgs84"}, function () {
                return a._complete = function (a) {
                    delete a.type
                }, a
            }())
        }, hideOptionMenu         : function (a) {
            c("hideOptionMenu", {}, a)
        }, showOptionMenu         : function (a) {
            c("showOptionMenu", {}, a)
        }, closeWindow            : function (a) {
            a = a || {}, c("closeWindow", {immediate_close: a.immediateClose || 0}, a)
        }, hideMenuItems          : function (a) {
            c("hideMenuItems", {menuList: a.menuList}, a)
        }, showMenuItems          : function (a) {
            c("showMenuItems", {menuList: a.menuList}, a)
        }, hideAllNonBaseMenuItem : function (a) {
            c("hideAllNonBaseMenuItem", {}, a)
        }, showAllNonBaseMenuItem : function (a) {
            c("showAllNonBaseMenuItem", {}, a)
        }, scanQRCode             : function (a) {
            a = a || {}, c("scanQRCode", {
                needResult: a.needResult || 0,
                scanType  : a.scanType || ["qrCode", "barCode"]
            }, function () {
                return a._complete = function (a) {
                    var b, c;
                    v && (b = a.resultStr, b && (c = JSON.parse(b), a.resultStr = c && c.scan_code && c.scan_code.scan_result))
                }, a
            }())
        }, openProductSpecificView: function (a) {
            c(o.openProductSpecificView, {pid: a.productId, view_type: a.viewType || 0}, a)
        }, addCard                : function (a) {
            var e, f, g, h, b = a.cardList, d = [];
            for (e = 0, f = b.length; f > e; ++ e)g = b[e], h = {card_id: g.cardId, card_ext: g.cardExt}, d.push(h);
            c(o.addCard, {card_list: d}, function () {
                return a._complete = function (a) {
                    var c, d, e, b = a.card_list;
                    if (b) {
                        for (b = JSON.parse(b), c = 0, d = b.length; d > c; ++ c)e = b[c], e.cardId = e.card_id, e.cardExt = e.card_ext, e.isSuccess = e.is_succ ? ! 0 : ! 1, delete e.card_id, delete e.card_ext, delete e.is_succ;
                        a.cardList = b, delete a.card_list
                    }
                }, a
            }())
        }, chooseCard             : function (a) {
            c("chooseCard", {
                app_id     : z.appId,
                location_id: a.shopId || "",
                sign_type  : a.signType || "SHA1",
                card_id    : a.cardId || "",
                card_type  : a.cardType || "",
                card_sign  : a.cardSign,
                time_stamp : a.timestamp + "",
                nonce_str  : a.nonceStr
            }, function () {
                return a._complete = function (a) {
                    a.cardList = a.choose_card_info, delete a.choose_card_info
                }, a
            }())
        }, openCard               : function (a) {
            var e, f, g, h, b = a.cardList, d = [];
            for (e = 0, f = b.length; f > e; ++ e)g = b[e], h = {card_id: g.cardId, code: g.code}, d.push(h);
            c(o.openCard, {card_list: d}, a)
        }, chooseWXPay            : function (a) {
            c(o.chooseWXPay, f(a), a)
        }
    }, b && (a.wx = a.jWeixin = C), C
});
(function () {
    var lastTime = 0;
    var vendors  = ['ms', 'moz', 'webkit', 'o'];
    for (var x = 0; x < vendors.length && ! window.requestAnimationFrame; ++ x) {
        window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
        window.cancelAnimationFrame  = window[vendors[x] + 'CancelAnimationFrame'] || window[vendors[x] + 'CancelRequestAnimationFrame']
    }
    ;
    if (! window.requestAnimationFrame)window.requestAnimationFrame = function (callback, element) {
        var currTime   = new Date().getTime();
        var timeToCall = Math.max(0, 16 - (currTime - lastTime));
        var id         = window.setTimeout(function () {
            callback(currTime + timeToCall)
        }, timeToCall);
        lastTime       = currTime + timeToCall;
        return id
    };
    if (! window.cancelAnimationFrame)window.cancelAnimationFrame = function (id) {
        clearTimeout(id)
    }
}());
document.addEventListener("DOMContentLoaded", function () {
    (function () {
        function require(path, parent, orig) {
            var resolved = require.resolve(path);
            if (null == resolved) {
                orig        = orig || path;
                parent      = parent || "root";
                var err     = new Error('Failed to require "' + orig + '" from "' + parent + '"');
                err.path    = orig;
                err.parent  = parent;
                err.require = true;
                throw err
            }
            var module = require.modules[resolved];
            if (! module.exports) {
                module.exports = {};
                module.client  = module.component = true;
                module.call(this, module.exports, require.relative(resolved), module)
            }
            return module.exports
        }

        require.modules   = {};
        require.aliases   = {};
        require.resolve   = function (path) {
            if (path.charAt(0) === "/")path = path.slice(1);
            var paths = [path, path + ".js", path + ".json", path + "/index.js", path + "/index.json"];
            for (var i = 0; i < paths.length; i ++) {
                var path = paths[i];
                if (require.modules.hasOwnProperty(path))return path;
                if (require.aliases.hasOwnProperty(path))return require.aliases[path]
            }
        };
        require.normalize = function (curr, path) {
            var segs = [];
            if ("." != path.charAt(0))return path;
            curr = curr.split("/");
            path = path.split("/");
            for (var i = 0; i < path.length; ++ i) {
                if (".." == path[i]) {
                    curr.pop()
                } else if ("." != path[i] && "" != path[i]) {
                    segs.push(path[i])
                }
            }
            return curr.concat(segs).join("/")
        };
        require.register  = function (path, definition) {
            require.modules[path] = definition
        };
        require.alias     = function (from, to) {
            if (! require.modules.hasOwnProperty(from)) {
                throw new Error('Failed to alias "' + from + '", it does not exist')
            }
            require.aliases[to] = from
        };
        require.relative  = function (parent) {
            var p = require.normalize(parent, "..");

            function lastIndexOf(arr, obj) {
                var i = arr.length;
                while (i --) {
                    if (arr[i] === obj)return i
                }
                return - 1
            }

            function localRequire(path) {
                var resolved = localRequire.resolve(path);
                return require(resolved, parent, path)
            }

            localRequire.resolve = function (path) {
                var c = path.charAt(0);
                if ("/" == c)return path.slice(1);
                if ("." == c)return require.normalize(p, path);
                var segs = parent.split("/");
                var i    = lastIndexOf(segs, "deps") + 1;
                if (! i)i = 0;
                path = segs.slice(0, i + 1).join("/") + "/deps/" + path;
                return path
            };
            localRequire.exists  = function (path) {
                return require.modules.hasOwnProperty(localRequire.resolve(path))
            };
            return localRequire
        };
        require.register("component-transform-property/index.js", function (exports, require, module) {
            var styles = ["webkitTransform", "MozTransform", "msTransform", "OTransform", "transform"];
            var el     = document.createElement("p");
            var style;
            for (var i = 0; i < styles.length; i ++) {
                style = styles[i];
                if (null != el.style[style]) {
                    module.exports = style;
                    break
                }
            }
        });
        require.register("component-has-translate3d/index.js", function (exports, require, module) {
            var prop = require("transform-property");
            if (! prop || ! window.getComputedStyle) {
                module.exports = false
            } else {
                var map        = {
                    webkitTransform: "-webkit-transform",
                    OTransform     : "-o-transform",
                    msTransform    : "-ms-transform",
                    MozTransform   : "-moz-transform",
                    transform      : "transform"
                };
                var el         = document.createElement("div");
                el.style[prop] = "translate3d(1px,1px,1px)";
                document.body.insertBefore(el, null);
                var val = getComputedStyle(el).getPropertyValue(map[prop]);
                document.body.removeChild(el);
                module.exports = null != val && val.length && "none" != val
            }
        });
        require.register("yields-has-transitions/index.js", function (exports, require, module) {
            exports = module.exports = function (el) {
                switch (arguments.length) {
                    case 0:
                        return bool;
                    case 1:
                        return bool ? transitions(el) : bool
                }
            };
            function transitions(el, styl) {
                if (el.transition)return true;
                styl = window.getComputedStyle(el);
                return ! ! parseFloat(styl.transitionDuration, 10)
            }

            var styl = document.body.style;
            var bool = "transition" in styl || "webkitTransition" in styl || "MozTransition" in styl || "msTransition" in styl
        });
        require.register("component-event/index.js", function (exports, require, module) {
            var bind       = window.addEventListener ? "addEventListener" : "attachEvent", unbind = window.removeEventListener ? "removeEventListener" : "detachEvent", prefix = bind !== "addEventListener" ? "on" : "";
            exports.bind   = function (el, type, fn, capture) {
                el[bind](prefix + type, fn, capture || false);
                return fn
            };
            exports.unbind = function (el, type, fn, capture) {
                el[unbind](prefix + type, fn, capture || false);
                return fn
            }
        });
        require.register("ecarter-css-emitter/index.js", function (exports, require, module) {
            var events     = require("event");
            var watch      = ["transitionend", "webkitTransitionEnd", "oTransitionEnd", "MSTransitionEnd", "animationend", "webkitAnimationEnd", "oAnimationEnd", "MSAnimationEnd"];
            module.exports = CssEmitter;
            function CssEmitter(element) {
                if (! (this instanceof CssEmitter))return new CssEmitter(element);
                this.el = element
            }

            CssEmitter.prototype.bind   = function (fn) {
                for (var i = 0; i < watch.length; i ++) {
                    events.bind(this.el, watch[i], fn)
                }
                return this
            };
            CssEmitter.prototype.unbind = function (fn) {
                for (var i = 0; i < watch.length; i ++) {
                    events.unbind(this.el, watch[i], fn)
                }
                return this
            };
            CssEmitter.prototype.once   = function (fn) {
                var self = this;

                function on() {
                    self.unbind(on);
                    fn.apply(self.el, arguments)
                }

                self.bind(on);
                return this
            }
        });
        require.register("component-once/index.js", function (exports, require, module) {
            var n          = 0;
            var global     = function () {
                return this
            }();
            module.exports = function (fn) {
                var id = n ++;
                var called;

                function once() {
                    if (this == global) {
                        if (called)return;
                        called = true;
                        return fn.apply(this, arguments)
                    }
                    var key = "__called_" + id + "__";
                    if (this[key])return;
                    this[key] = true;
                    return fn.apply(this, arguments)
                }

                return once
            }
        });
        require.register("yields-after-transition/index.js", function (exports, require, module) {
            var has        = require("has-transitions"), emitter = require("css-emitter"), once = require("once");
            var supported  = has();
            module.exports = after;
            function after(el, fn) {
                if (! supported || ! has(el))return fn();
                emitter(el).bind(fn);
                return fn
            }

            after.once = function (el, fn) {
                var callback = once(fn);
                after(el, fn = function () {
                    emitter(el).unbind(fn);
                    callback()
                })
            }
        });
        require.register("component-emitter/index.js", function (exports, require, module) {
            module.exports = Emitter;
            function Emitter(obj) {
                if (obj)return mixin(obj)
            }

            function mixin(obj) {
                for (var key in Emitter.prototype) {
                    obj[key] = Emitter.prototype[key]
                }
                return obj
            }

            Emitter.prototype.on = Emitter.prototype.addEventListener = function (event, fn) {
                this._callbacks = this._callbacks || {};
                (this._callbacks[event] = this._callbacks[event] || []).push(fn);
                return this
            };
            Emitter.prototype.once = function (event, fn) {
                var self        = this;
                this._callbacks = this._callbacks || {};
                function on() {
                    self.off(event, on);
                    fn.apply(this, arguments)
                }

                on.fn = fn;
                this.on(event, on);
                return this
            };
            Emitter.prototype.off  = Emitter.prototype.removeListener = Emitter.prototype.removeAllListeners = Emitter.prototype.removeEventListener = function (event, fn) {
                this._callbacks = this._callbacks || {};
                if (0 == arguments.length) {
                    this._callbacks = {};
                    return this
                }
                var callbacks = this._callbacks[event];
                if (! callbacks)return this;
                if (1 == arguments.length) {
                    delete this._callbacks[event];
                    return this
                }
                var cb;
                for (var i = 0; i < callbacks.length; i ++) {
                    cb = callbacks[i];
                    if (cb === fn || cb.fn === fn) {
                        callbacks.splice(i, 1);
                        break
                    }
                }
                return this
            };
            Emitter.prototype.emit         = function (event) {
                this._callbacks = this._callbacks || {};
                var args        = [].slice.call(arguments, 1), callbacks = this._callbacks[event];
                if (callbacks) {
                    callbacks = callbacks.slice(0);
                    for (var i = 0, len = callbacks.length; i < len; ++ i) {
                        callbacks[i].apply(this, args)
                    }
                }
                return this
            };
            Emitter.prototype.listeners    = function (event) {
                this._callbacks = this._callbacks || {};
                return this._callbacks[event] || []
            };
            Emitter.prototype.hasListeners = function (event) {
                return ! ! this.listeners(event).length
            }
        });
        require.register("yields-css-ease/index.js", function (exports, require, module) {
            module.exports = {
                "in"               : "ease-in",
                out                : "ease-out",
                "in-out"           : "ease-in-out",
                snap               : "cubic-bezier(0,1,.5,1)",
                linear             : "cubic-bezier(0.250, 0.250, 0.750, 0.750)",
                "ease-in-quad"     : "cubic-bezier(0.550, 0.085, 0.680, 0.530)",
                "ease-in-cubic"    : "cubic-bezier(0.550, 0.055, 0.675, 0.190)",
                "ease-in-quart"    : "cubic-bezier(0.895, 0.030, 0.685, 0.220)",
                "ease-in-quint"    : "cubic-bezier(0.755, 0.050, 0.855, 0.060)",
                "ease-in-sine"     : "cubic-bezier(0.470, 0.000, 0.745, 0.715)",
                "ease-in-expo"     : "cubic-bezier(0.950, 0.050, 0.795, 0.035)",
                "ease-in-circ"     : "cubic-bezier(0.600, 0.040, 0.980, 0.335)",
                "ease-in-back"     : "cubic-bezier(0.600, -0.280, 0.735, 0.045)",
                "ease-out-quad"    : "cubic-bezier(0.250, 0.460, 0.450, 0.940)",
                "ease-out-cubic"   : "cubic-bezier(0.215, 0.610, 0.355, 1.000)",
                "ease-out-quart"   : "cubic-bezier(0.165, 0.840, 0.440, 1.000)",
                "ease-out-quint"   : "cubic-bezier(0.230, 1.000, 0.320, 1.000)",
                "ease-out-sine"    : "cubic-bezier(0.390, 0.575, 0.565, 1.000)",
                "ease-out-expo"    : "cubic-bezier(0.190, 1.000, 0.220, 1.000)",
                "ease-out-circ"    : "cubic-bezier(0.075, 0.820, 0.165, 1.000)",
                "ease-out-back"    : "cubic-bezier(0.175, 0.885, 0.320, 1.275)",
                "ease-out-quad"    : "cubic-bezier(0.455, 0.030, 0.515, 0.955)",
                "ease-out-cubic"   : "cubic-bezier(0.645, 0.045, 0.355, 1.000)",
                "ease-in-out-quart": "cubic-bezier(0.770, 0.000, 0.175, 1.000)",
                "ease-in-out-quint": "cubic-bezier(0.860, 0.000, 0.070, 1.000)",
                "ease-in-out-sine" : "cubic-bezier(0.445, 0.050, 0.550, 0.950)",
                "ease-in-out-expo" : "cubic-bezier(1.000, 0.000, 0.000, 1.000)",
                "ease-in-out-circ" : "cubic-bezier(0.785, 0.135, 0.150, 0.860)",
                "ease-in-out-back" : "cubic-bezier(0.680, -0.550, 0.265, 1.550)"
            }
        });
        require.register("component-query/index.js", function (exports, require, module) {
            function one(selector, el) {
                return el.querySelector(selector)
            }

            exports = module.exports = function (selector, el) {
                el = el || document;
                return one(selector, el)
            };
            exports.all    = function (selector, el) {
                el = el || document;
                return el.querySelectorAll(selector)
            };
            exports.engine = function (obj) {
                if (! obj.one)throw new Error(".one callback required");
                if (! obj.all)throw new Error(".all callback required");
                one         = obj.one;
                exports.all = obj.all;
                return exports
            }
        });
        require.register("move/index.js", function (exports, require, module) {
            var after      = require("after-transition");
            var has3d      = require("has-translate3d");
            var Emitter    = require("emitter");
            var ease       = require("css-ease");
            var query      = require("query");
            var translate  = has3d ? ["translate3d(", ", 0)"] : ["translate(", ")"];
            module.exports = Move;
            var style      = window.getComputedStyle || window.currentStyle;
            Move.version   = "0.3.2";
            Move.ease      = ease;
            Move.defaults  = {duration: 500};
            Move.select    = function (selector) {
                if ("string" != typeof selector)return selector;
                return query(selector)
            };
            function Move(el) {
                if (! (this instanceof Move))return new Move(el);
                if ("string" == typeof el)el = query(el);
                if (! el)throw new TypeError("Move must be initialized with element or selector");
                this.el               = el;
                this._props           = {};
                this._rotate          = 0;
                this._transitionProps = [];
                this._transforms      = [];
                this.duration(Move.defaults.duration)
            }

            Emitter(Move.prototype);
            Move.prototype.transform = function (transform) {
                this._transforms.push(transform);
                return this
            };
            Move.prototype.skew      = function (x, y) {
                return this.transform("skew(" + x + "deg, " + (y || 0) + "deg)")
            };
            Move.prototype.skewX     = function (n) {
                return this.transform("skewX(" + n + "deg)")
            };
            Move.prototype.skewY     = function (n) {
                return this.transform("skewY(" + n + "deg)")
            };
            Move.prototype.translate = Move.prototype.to = function (x, y) {
                return this.transform(translate.join("" + x + "px, " + (y || 0) + "px"))
            };
            Move.prototype.translateX = Move.prototype.x = function (n) {
                return this.transform("translateX(" + n + "px)")
            };
            Move.prototype.translateY = Move.prototype.y = function (n) {
                return this.transform("translateY(" + n + "px)")
            };
            Move.prototype.scale             = function (x, y) {
                return this.transform("scale(" + x + ", " + (y || x) + ")")
            };
            Move.prototype.scaleX            = function (n) {
                return this.transform("scaleX(" + n + ")")
            };
            Move.prototype.matrix            = function (m11, m12, m21, m22, m31, m32) {
                return this.transform("matrix(" + [m11, m12, m21, m22, m31, m32].join(",") + ")")
            };
            Move.prototype.scaleY            = function (n) {
                return this.transform("scaleY(" + n + ")")
            };
            Move.prototype.rotate            = function (n) {
                return this.transform("rotate(" + n + "deg)")
            };
            Move.prototype.ease              = function (fn) {
                fn = ease[fn] || fn || "ease";
                return this.setVendorProperty("transition-timing-function", fn)
            };
            Move.prototype.animate           = function (name, props) {
                for (var i in props) {
                    if (props.hasOwnProperty(i)) {
                        this.setVendorProperty("animation-" + i, props[i])
                    }
                }
                return this.setVendorProperty("animation-name", name)
            };
            Move.prototype.duration          = function (n) {
                n = this._duration = "string" == typeof n ? parseFloat(n) * 1e3 : n;
                return this.setVendorProperty("transition-duration", n + "ms")
            };
            Move.prototype.delay             = function (n) {
                n = "string" == typeof n ? parseFloat(n) * 1e3 : n;
                return this.setVendorProperty("transition-delay", n + "ms")
            };
            Move.prototype.setProperty       = function (prop, val) {
                this._props[prop] = val;
                return this
            };
            Move.prototype.setVendorProperty = function (prop, val) {
                this.setProperty("-webkit-" + prop, val);
                this.setProperty("-moz-" + prop, val);
                this.setProperty("-ms-" + prop, val);
                this.setProperty("-o-" + prop, val);
                return this
            };
            Move.prototype.set               = function (prop, val) {
                this.transition(prop);
                this._props[prop] = val;
                return this
            };
            Move.prototype.add               = function (prop, val) {
                if (! style)return;
                var self = this;
                return this.on("start", function () {
                    var curr = parseInt(self.current(prop), 10);
                    self.set(prop, curr + val + "px")
                })
            };
            Move.prototype.sub               = function (prop, val) {
                if (! style)return;
                var self = this;
                return this.on("start", function () {
                    var curr = parseInt(self.current(prop), 10);
                    self.set(prop, curr - val + "px")
                })
            };
            Move.prototype.current           = function (prop) {
                return style(this.el).getPropertyValue(prop)
            };
            Move.prototype.transition        = function (prop) {
                if (! this._transitionProps.indexOf(prop))return this;
                this._transitionProps.push(prop);
                return this
            };
            Move.prototype.applyProperties   = function () {
                for (var prop in this._props) {
                    this.el.style.setProperty(prop, this._props[prop], "")
                }
                return this
            };
            Move.prototype.move              = Move.prototype.select = function (selector) {
                this.el = Move.select(selector);
                return this
            };
            Move.prototype.then  = function (fn) {
                if (fn instanceof Move) {
                    this.on("end", function () {
                        fn.end()
                    })
                } else if ("function" == typeof fn) {
                    this.on("end", fn)
                } else {
                    var clone         = new Move(this.el);
                    clone._transforms = this._transforms.slice(0);
                    this.then(clone);
                    clone.parent = this;
                    return clone
                }
                return this
            };
            Move.prototype.pop   = function () {
                return this.parent
            };
            Move.prototype.reset = function () {
                this.el.style.webkitTransitionDuration = this.el.style.mozTransitionDuration = this.el.style.msTransitionDuration = this.el.style.oTransitionDuration = "";
                return this
            };
            Move.prototype.end   = function (fn) {
                var self = this;
                this.emit("start");
                if (this._transforms.length) {
                    this.setVendorProperty("transform", this._transforms.join(" "))
                }
                this.setVendorProperty("transition-properties", this._transitionProps.join(", "));
                this.applyProperties();
                if (fn)this.then(fn);
                after.once(this.el, function () {
                    self.reset();
                    self.emit("end")
                });
                return this
            }
        });
        require.alias("component-has-translate3d/index.js", "move/deps/has-translate3d/index.js");
        require.alias("component-has-translate3d/index.js", "has-translate3d/index.js");
        require.alias("component-transform-property/index.js", "component-has-translate3d/deps/transform-property/index.js");
        require.alias("yields-after-transition/index.js", "move/deps/after-transition/index.js");
        require.alias("yields-after-transition/index.js", "move/deps/after-transition/index.js");
        require.alias("yields-after-transition/index.js", "after-transition/index.js");
        require.alias("yields-has-transitions/index.js", "yields-after-transition/deps/has-transitions/index.js");
        require.alias("yields-has-transitions/index.js", "yields-after-transition/deps/has-transitions/index.js");
        require.alias("yields-has-transitions/index.js", "yields-has-transitions/index.js");
        require.alias("ecarter-css-emitter/index.js", "yields-after-transition/deps/css-emitter/index.js");
        require.alias("component-event/index.js", "ecarter-css-emitter/deps/event/index.js");
        require.alias("component-once/index.js", "yields-after-transition/deps/once/index.js");
        require.alias("yields-after-transition/index.js", "yields-after-transition/index.js");
        require.alias("component-emitter/index.js", "move/deps/emitter/index.js");
        require.alias("component-emitter/index.js", "emitter/index.js");
        require.alias("yields-css-ease/index.js", "move/deps/css-ease/index.js");
        require.alias("yields-css-ease/index.js", "move/deps/css-ease/index.js");
        require.alias("yields-css-ease/index.js", "css-ease/index.js");
        require.alias("yields-css-ease/index.js", "yields-css-ease/index.js");
        require.alias("component-query/index.js", "move/deps/query/index.js");
        require.alias("component-query/index.js", "query/index.js");
        if (typeof exports == "object") {
            module.exports = require("move")
        } else if (typeof define == "function" && define.amd) {
            define(function () {
                return require("move")
            })
        } else {
            this["move"] = require("move")
        }
    })();
});
(function (root, factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define([], factory)
    } else if (typeof exports === 'object') {
        module.exports = factory()
    } else {
        root.viewportUnitsBuggyfill = factory()
    }
}(this, function () {
    'use strict';
    var initialized            = false;
    var options;
    var userAgent              = window.navigator.userAgent;
    var viewportUnitExpression = /([+-]?[0-9.]+)(vh|vw|vmin|vmax)/g;
    var forEach                = [].forEach;
    var dimensions;
    var declarations;
    var styleNode;
    var isBuggyIE              = /MSIE [0-9]\./i.test(userAgent);
    var isOldIE                = /MSIE [0-8]\./i.test(userAgent);
    var isOperaMini            = userAgent.indexOf('Opera Mini') > - 1;
    var isMobileSafari         = /(iPhone|iPod|iPad).+AppleWebKit/i.test(userAgent) && (function () {
            var iOSversion = userAgent.match(/OS (\d)/);
            return iOSversion && iOSversion.length > 1 && parseInt(iOSversion[1]) < 8
        })();
    var isBadStockAndroid      = (function () {
        var isAndroid = userAgent.indexOf(' Android ') > - 1;
        if (! isAndroid) {
            return false
        }
        ;
        var isStockAndroid = userAgent.indexOf('Version/') > - 1;
        if (! isStockAndroid) {
            return false
        }
        ;
        var versionNumber = parseFloat((userAgent.match('Android ([0-9.]+)') || [])[1]);
        return versionNumber <= 4.4
    })();
    if (! isBuggyIE) {
        isBuggyIE = ! ! navigator.userAgent.match(/Trident.*rv[ :]*11\./)
    }
    ;
    function debounce(func, wait) {
        var timeout;
        return function () {
            var context  = this;
            var args     = arguments;
            var callback = function () {
                func.apply(context, args)
            };
            clearTimeout(timeout);
            timeout = setTimeout(callback, wait)
        }
    };
    function inIframe() {
        try {
            return window.self !== window.top
        } catch (e) {
            return true
        }
    };
    function initialize(initOptions) {
        if (initialized) {
            return
        }
        ;
        if (initOptions === true) {
            initOptions = {force: true}
        }
        ;
        options                   = initOptions || {};
        options.isMobileSafari    = isMobileSafari;
        options.isBadStockAndroid = isBadStockAndroid;
        if (isOldIE || (! options.force && ! isMobileSafari && ! isBuggyIE && ! isBadStockAndroid && ! isOperaMini && (! options.hacks || ! options.hacks.required(options)))) {
            if (window.console && isOldIE) {
                console.info('viewport-units-buggyfill requires a proper CSSOM and basic viewport unit support, which are not available in IE8 and below')
            }
            ;
            return {
                init: function () {
                }
            }
        }
        ;
        options.hacks && options.hacks.initialize(options);
        initialized  = true;
        styleNode    = document.createElement('style');
        styleNode.id = 'patched-viewport';
        document.head.appendChild(styleNode);
        importCrossOriginLinks(function () {
            var _refresh = debounce(refresh, options.refreshDebounceWait || 100);
            window.addEventListener('orientationchange', _refresh, true);
            window.addEventListener('pageshow', _refresh, true);
            if (options.force || isBuggyIE || inIframe()) {
                window.addEventListener('resize', _refresh, true);
                options._listeningToResize = true
            }
            ;
            options.hacks && options.hacks.initializeEvents(options, refresh, _refresh);
            refresh()
        })
    };
    function updateStyles() {
        styleNode.textContent = getReplacedViewportUnits();
        styleNode.parentNode.appendChild(styleNode)
    };
    function refresh() {
        if (! initialized) {
            return
        }
        ;
        findProperties();
        setTimeout(function () {
            updateStyles()
        }, 1)
    };
    function findProperties() {
        declarations = [];
        forEach.call(document.styleSheets, function (sheet) {
            if (sheet.ownerNode.id === 'patched-viewport' || ! sheet.cssRules || sheet.ownerNode.getAttribute('data-viewport-units-buggyfill') === 'ignore') {
                return
            }
            ;
            if (sheet.media && sheet.media.mediaText && window.matchMedia && ! window.matchMedia(sheet.media.mediaText).matches) {
                return
            }
            ;
            forEach.call(sheet.cssRules, findDeclarations)
        });
        return declarations
    };
    function findDeclarations(rule) {
        if (rule.type === 7) {
            var value;
            try {
                value = rule.cssText
            } catch (e) {
                return
            }
            ;
            viewportUnitExpression.lastIndex = 0;
            if (viewportUnitExpression.test(value)) {
                declarations.push([rule, null, value]);
                options.hacks && options.hacks.findDeclarations(declarations, rule, null, value)
            }
            ;
            return
        }
        ;
        if (! rule.style) {
            if (! rule.cssRules) {
                return
            }
            ;
            forEach.call(rule.cssRules, function (_rule) {
                findDeclarations(_rule)
            });
            return
        }
        ;
        forEach.call(rule.style, function (name) {
            var value = rule.style.getPropertyValue(name);
            if (rule.style.getPropertyPriority(name)) {
                value += ' !important'
            }
            ;
            viewportUnitExpression.lastIndex = 0;
            if (viewportUnitExpression.test(value)) {
                declarations.push([rule, name, value]);
                options.hacks && options.hacks.findDeclarations(declarations, rule, name, value)
            }
        })
    };
    function getReplacedViewportUnits() {
        dimensions = getViewport();
        var css    = [];
        var buffer = [];
        var open;
        var close;
        declarations.forEach(function (item) {
            var _item  = overwriteDeclaration.apply(null, item);
            var _open  = _item.selector.length ? (_item.selector.join(' {\n') + ' {\n') : '';
            var _close = new Array(_item.selector.length + 1).join('\n}');
            if (! _open || _open !== open) {
                if (buffer.length) {
                    css.push(open + buffer.join('\n') + close);
                    buffer.length = 0
                }
                ;
                if (_open) {
                    open  = _open;
                    close = _close;
                    buffer.push(_item.content)
                } else {
                    css.push(_item.content);
                    open  = null;
                    close = null
                }
                ;
                return
            }
            ;
            if (_open && ! open) {
                open  = _open;
                close = _close
            }
            ;
            buffer.push(_item.content)
        });
        if (buffer.length) {
            css.push(open + buffer.join('\n') + close)
        }
        ;
        if (isOperaMini) {
            css.push('* { content: normal !important; }')
        }
        ;
        return css.join('\n\n')
    };
    function overwriteDeclaration(rule, name, value) {
        var _value;
        var _selectors = [];
        _value         = value.replace(viewportUnitExpression, replaceValues);
        if (options.hacks) {
            _value = options.hacks.overwriteDeclaration(rule, name, _value)
        }
        ;
        if (name) {
            _selectors.push(rule.selectorText);
            _value = name + ': ' + _value + ';'
        }
        ;
        var _rule = rule.parentRule;
        while (_rule) {
            _selectors.unshift('@media ' + _rule.media.mediaText);
            _rule = _rule.parentRule
        }
        ;
        return {selector: _selectors, content: _value}
    };
    function replaceValues(match, number, unit) {
        var _base   = dimensions[unit];
        var _number = parseFloat(number) / 100;
        return (_number * _base) + 'px'
    };
    function getViewport() {
        var vh = window.innerHeight;
        var vw = window.innerWidth;
        return {vh: vh, vw: vw, vmax: Math.max(vw, vh), vmin: Math.min(vw, vh)}
    };
    function importCrossOriginLinks(next) {
        var _waiting = 0;
        var decrease = function () {
            _waiting --;
            if (! _waiting) {
                next()
            }
        };
        forEach.call(document.styleSheets, function (sheet) {
            if (! sheet.href || origin(sheet.href) === origin(location.href) || sheet.ownerNode.getAttribute('data-viewport-units-buggyfill') === 'ignore') {
                return
            }
            ;
            _waiting ++;
            convertLinkToStyle(sheet.ownerNode, decrease)
        });
        if (! _waiting) {
            next()
        }
    };
    function origin(url) {
        return url.slice(0, url.indexOf('/', url.indexOf('://') + 3))
    };
    function convertLinkToStyle(link, next) {
        getCors(link.href, function () {
            var style   = document.createElement('style');
            style.media = link.media;
            style.setAttribute('data-href', link.href);
            style.textContent = this.responseText;
            link.parentNode.replaceChild(style, link);
            next()
        }, next)
    };
    function getCors(url, success, error) {
        var xhr = new XMLHttpRequest();
        if ('withCredentials' in xhr) {
            xhr.open('GET', url, true)
        } else if (typeof XDomainRequest !== 'undefined') {
            xhr = new XDomainRequest();
            xhr.open('GET', url)
        } else {
            throw new Error('cross-domain XHR not supported')
        }
        ;
        xhr.onload  = success;
        xhr.onerror = error;
        xhr.send();
        return xhr
    };
    return {
        version       : '0.5.4',
        findProperties: findProperties,
        getCss        : getReplacedViewportUnits,
        init          : initialize,
        refresh       : refresh
    }
}));
var Base64   = {
    table      : ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', '/'],
    UTF16ToUTF8: function (str) {
        var res = [], len = str.length;
        for (var i = 0; i < len; i ++) {
            var code = str.charCodeAt(i);
            if (code > 0x0000 && code <= 0x007F) {
                res.push(str.charAt(i))
            } else if (code >= 0x0080 && code <= 0x07FF) {
                var byte1 = 0xC0 | ((code >> 6) & 0x1F);
                var byte2 = 0x80 | (code & 0x3F);
                res.push(String.fromCharCode(byte1), String.fromCharCode(byte2))
            } else if (code >= 0x0800 && code <= 0xFFFF) {
                var byte1 = 0xE0 | ((code >> 12) & 0x0F);
                var byte2 = 0x80 | ((code >> 6) & 0x3F);
                var byte3 = 0x80 | (code & 0x3F);
                res.push(String.fromCharCode(byte1), String.fromCharCode(byte2), String.fromCharCode(byte3))
            } else if (code >= 0x00010000 && code <= 0x001FFFFF) {
            } else if (code >= 0x00200000 && code <= 0x03FFFFFF) {
            } else {
            }
        }
        ;
        return res.join('')
    },
    UTF8ToUTF16: function (str) {
        var res = [], len = str.length;
        var i   = 0;
        for (var i = 0; i < len; i ++) {
            var code = str.charCodeAt(i);
            if (((code >> 7) & 0xFF) == 0x0) {
                res.push(str.charAt(i))
            } else if (((code >> 5) & 0xFF) == 0x6) {
                var code2 = str.charCodeAt(++ i);
                var byte1 = (code & 0x1F) << 6;
                var byte2 = code2 & 0x3F;
                var utf16 = byte1 | byte2;
                res.push(Sting.fromCharCode(utf16))
            } else if (((code >> 4) & 0xFF) == 0xE) {
                var code2 = str.charCodeAt(++ i);
                var code3 = str.charCodeAt(++ i);
                var byte1 = (code << 4) | ((code2 >> 2) & 0x0F);
                var byte2 = ((code2 & 0x03) << 6) | (code3 & 0x3F);
                var utf16 = ((byte1 & 0x00FF) << 8) | byte2;
                res.push(String.fromCharCode(utf16))
            } else if (((code >> 3) & 0xFF) == 0x1E) {
            } else if (((code >> 2) & 0xFF) == 0x3E) {
            } else {
            }
        }
        ;
        return res.join('')
    },
    encode     : function (str) {
        if (! str) {
            return ''
        }
        ;
        var utf8 = this.UTF16ToUTF8(str);
        var i    = 0;
        var len  = utf8.length;
        var res  = [];
        while (i < len) {
            var c1 = utf8.charCodeAt(i ++) & 0xFF;
            res.push(this.table[c1 >> 2]);
            if (i == len) {
                res.push(this.table[(c1 & 0x3) << 4]);
                res.push('==');
                break
            }
            ;
            var c2 = utf8.charCodeAt(i ++);
            if (i == len) {
                res.push(this.table[((c1 & 0x3) << 4) | ((c2 >> 4) & 0x0F)]);
                res.push(this.table[(c2 & 0x0F) << 2]);
                res.push('=');
                break
            }
            ;
            var c3 = utf8.charCodeAt(i ++);
            res.push(this.table[((c1 & 0x3) << 4) | ((c2 >> 4) & 0x0F)]);
            res.push(this.table[((c2 & 0x0F) << 2) | ((c3 & 0xC0) >> 6)]);
            res.push(this.table[c3 & 0x3F])
        }
        ;
        return res.join('')
    },
    decode     : function (str) {
        if (! str) {
            return ''
        }
        ;
        var len = str.length;
        var i   = 0;
        var res = [];
        while (i < len) {
            code1 = this.table.indexOf(str.charAt(i ++));
            code2 = this.table.indexOf(str.charAt(i ++));
            code3 = this.table.indexOf(str.charAt(i ++));
            code4 = this.table.indexOf(str.charAt(i ++));
            c1    = (code1 << 2) | (code2 >> 4);
            c2    = ((code2 & 0xF) << 4) | (code3 >> 2);
            c3    = ((code3 & 0x3) << 6) | code4;
            res.push(String.fromCharCode(c1));
            if (code3 != 64) {
                res.push(String.fromCharCode(c2))
            }
            ;
            if (code4 != 64) {
                res.push(String.fromCharCode(c3))
            }
        }
        ;
        return this.UTF8ToUTF16(res.join(''))
    }
};
var CryptoJS = CryptoJS || function (u, p) {
        var d = {}, l = d.lib = {}, s = function () {
            }, t                      = l.Base = {
                extend   : function (a) {
                    s.prototype = this;
                    var c       = new s;
                    a && c.mixIn(a);
                    c.hasOwnProperty("init") || (c.init = function () {
                        c.$super.init.apply(this, arguments)
                    });
                    c.init.prototype = c;
                    c.$super         = this;
                    return c
                }, create: function () {
                    var a = this.extend();
                    a.init.apply(a, arguments);
                    return a
                }, init  : function () {
                }, mixIn : function (a) {
                    for (var c in a)a.hasOwnProperty(c) && (this[c] = a[c]);
                    a.hasOwnProperty("toString") && (this.toString = a.toString)
                }, clone : function () {
                    return this.init.prototype.extend(this)
                }
            },
            r = l.WordArray = t.extend({
                init       : function (a, c) {
                    a = this.words = a || [];
                    this.sigBytes = c != p ? c : 4 * a.length
                }, toString: function (a) {
                    return (a || v).stringify(this)
                }, concat  : function (a) {
                    var c = this.words, e = a.words, j = this.sigBytes;
                    a                                  = a.sigBytes;
                    this.clamp();
                    if (j % 4)for (var k = 0; k < a; k ++)c[j + k >>> 2] |= (e[k >>> 2] >>> 24 - 8 * (k % 4) & 255) << 24 - 8 * ((j + k) % 4); else if (65535 < e.length)for (k = 0; k < a; k += 4)c[j + k >>> 2] = e[k >>> 2]; else c.push.apply(c, e);
                    this.sigBytes += a;
                    return this
                }, clamp   : function () {
                    var a = this.words, c = this.sigBytes;
                    a[c >>> 2] &= 4294967295 <<
                        32 - 8 * (c % 4);
                    a.length              = u.ceil(c / 4)
                }, clone   : function () {
                    var a   = t.clone.call(this);
                    a.words = this.words.slice(0);
                    return a
                }, random  : function (a) {
                    for (var c = [], e = 0; e < a; e += 4)c.push(4294967296 * u.random() | 0);
                    return new r.init(c, a)
                }
            }), w = d.enc = {}, v = w.Hex = {
                stringify: function (a) {
                    var c = a.words;
                    a     = a.sigBytes;
                    for (var e = [], j = 0; j < a; j ++) {
                        var k = c[j >>> 2] >>> 24 - 8 * (j % 4) & 255;
                        e.push((k >>> 4).toString(16));
                        e.push((k & 15).toString(16))
                    }
                    return e.join("")
                }, parse : function (a) {
                    for (var c = a.length, e = [], j = 0; j < c; j += 2)e[j >>> 3] |= parseInt(a.substr(j,
                            2), 16) << 24 - 4 * (j % 8);
                    return new r.init(e, c / 2)
                }
            }, b = w.Latin1 = {
                stringify: function (a) {
                    var c = a.words;
                    a     = a.sigBytes;
                    for (var e = [], j = 0; j < a; j ++)e.push(String.fromCharCode(c[j >>> 2] >>> 24 - 8 * (j % 4) & 255));
                    return e.join("")
                }, parse : function (a) {
                    for (var c = a.length, e = [], j = 0; j < c; j ++)e[j >>> 2] |= (a.charCodeAt(j) & 255) << 24 - 8 * (j % 4);
                    return new r.init(e, c)
                }
            }, x = w.Utf8 = {
                stringify: function (a) {
                    try {
                        return decodeURIComponent(escape(b.stringify(a)))
                    } catch (c) {
                        throw Error("Malformed UTF-8 data");
                    }
                }, parse : function (a) {
                    return b.parse(unescape(encodeURIComponent(a)))
                }
            },
            q = l.BufferedBlockAlgorithm = t.extend({
                reset            : function () {
                    this._data       = new r.init;
                    this._nDataBytes = 0
                }, _append       : function (a) {
                    "string" == typeof a && (a = x.parse(a));
                    this._data.concat(a);
                    this._nDataBytes += a.sigBytes
                }, _process      : function (a) {
                    var c = this._data, e = c.words, j = c.sigBytes, k = this.blockSize, b = j / (4 * k), b = a ? u.ceil(b) : u.max((b | 0) - this._minBufferSize, 0);
                    a                                                                                       = b * k;
                    j                                                                                       = u.min(4 * a, j);
                    if (a) {
                        for (var q = 0; q < a; q += k)this._doProcessBlock(e, q);
                        q = e.splice(0, a);
                        c.sigBytes -= j
                    }
                    return new r.init(q, j)
                }, clone         : function () {
                    var a   = t.clone.call(this);
                    a._data = this._data.clone();
                    return a
                }, _minBufferSize: 0
            });
        l.Hasher = q.extend({
            cfg                 : t.extend(), init: function (a) {
                this.cfg = this.cfg.extend(a);
                this.reset()
            }, reset            : function () {
                q.reset.call(this);
                this._doReset()
            }, update           : function (a) {
                this._append(a);
                this._process();
                return this
            }, finalize         : function (a) {
                a && this._append(a);
                return this._doFinalize()
            }, blockSize        : 16, _createHelper: function (a) {
                return function (b, e) {
                    return (new a.init(e)).finalize(b)
                }
            }, _createHmacHelper: function (a) {
                return function (b, e) {
                    return (new n.HMAC.init(a,
                        e)).finalize(b)
                }
            }
        });
        var n    = d.algo = {};
        return d
    }(Math);
(function () {
    var u        = CryptoJS, p = u.lib.WordArray;
    u.enc.Base64 = {
        stringify: function (d) {
            var l = d.words, p = d.sigBytes, t = this._map;
            d.clamp();
            d = [];
            for (var r = 0; r < p; r += 3)for (var w = (l[r >>> 2] >>> 24 - 8 * (r % 4) & 255) << 16 | (l[r + 1 >>> 2] >>> 24 - 8 * ((r + 1) % 4) & 255) << 8 | l[r + 2 >>> 2] >>> 24 - 8 * ((r + 2) % 4) & 255, v = 0; 4 > v && r + 0.75 * v < p; v ++)d.push(t.charAt(w >>> 6 * (3 - v) & 63));
            if (l = t.charAt(64))for (; d.length % 4;)d.push(l);
            return d.join("")
        }, parse : function (d) {
            var l = d.length, s = this._map, t = s.charAt(64);
            t && (t = d.indexOf(t), - 1 != t && (l = t));
            for (var t = [], r = 0, w = 0; w <
            l; w ++)if (w % 4) {
                var v = s.indexOf(d.charAt(w - 1)) << 2 * (w % 4), b = s.indexOf(d.charAt(w)) >>> 6 - 2 * (w % 4);
                t[r >>> 2] |= (v | b) << 24 - 8 * (r % 4);
                r ++
            }
            return p.create(t, r)
        }, _map  : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/="
    }
})();
var QRCode;
(function () {
    function QR8bitByte(data) {
        this.mode       = QRMode.MODE_8BIT_BYTE;
        this.data       = data;
        this.parsedData = [];
        for (var i = 0, l = this.data.length; i < l; i ++) {
            var byteArray = [];
            var code      = this.data.charCodeAt(i);
            if (code > 0x10000) {
                byteArray[0] = 0xF0 | ((code & 0x1C0000) >>> 18);
                byteArray[1] = 0x80 | ((code & 0x3F000) >>> 12);
                byteArray[2] = 0x80 | ((code & 0xFC0) >>> 6);
                byteArray[3] = 0x80 | (code & 0x3F)
            } else if (code > 0x800) {
                byteArray[0] = 0xE0 | ((code & 0xF000) >>> 12);
                byteArray[1] = 0x80 | ((code & 0xFC0) >>> 6);
                byteArray[2] = 0x80 | (code & 0x3F)
            } else if (code > 0x80) {
                byteArray[0] = 0xC0 | ((code & 0x7C0) >>> 6);
                byteArray[1] = 0x80 | (code & 0x3F)
            } else {
                byteArray[0] = code
            }
            ;
            this.parsedData.push(byteArray)
        }
        ;
        this.parsedData = Array.prototype.concat.apply([], this.parsedData);
        if (this.parsedData.length != this.data.length) {
            this.parsedData.unshift(191);
            this.parsedData.unshift(187);
            this.parsedData.unshift(239)
        }
    };
    QR8bitByte.prototype = {
        getLength: function (buffer) {
            return this.parsedData.length
        }, write : function (buffer) {
            for (var i = 0, l = this.parsedData.length; i < l; i ++) {
                buffer.put(this.parsedData[i], 8)
            }
        }
    };
    function QRCodeModel(typeNumber, errorCorrectLevel) {
        this.typeNumber        = typeNumber;
        this.errorCorrectLevel = errorCorrectLevel;
        this.modules           = null;
        this.moduleCount       = 0;
        this.dataCache         = null;
        this.dataList          = []
    };
    function QRPolynomial(num, shift) {
        if (num.length == undefined)throw new Error(num.length + "/" + shift);
        var offset = 0;
        while (offset < num.length && num[offset] == 0)offset ++;
        this.num = new Array(num.length - offset + shift);
        for (var i = 0; i < num.length - offset; i ++)this.num[i] = num[i + offset]
    };
    function QRRSBlock(totalCount, dataCount) {
        this.totalCount = totalCount, this.dataCount = dataCount
    };
    function QRBitBuffer() {
        this.buffer = [], this.length = 0
    };
    QRCodeModel.prototype = {
        "addData"                      : function (data) {
            var newData = new QR8bitByte(data);
            this.dataList.push(newData), this.dataCache = null
        }, "isDark"                    : function (row, col) {
            if (row < 0 || this.moduleCount <= row || col < 0 || this.moduleCount <= col)throw new Error(row + "," + col);
            return this.modules[row][col]
        }, "getModuleCount"            : function () {
            return this.moduleCount
        }, "make"                      : function () {
            this.makeImpl(! 1, this.getBestMaskPattern())
        }, "makeImpl"                  : function (test, maskPattern) {
            this.moduleCount = this.typeNumber * 4 + 17, this.modules = new Array(this.moduleCount);
            for (var row = 0; row < this.moduleCount; row ++) {
                this.modules[row] = new Array(this.moduleCount);
                for (var col = 0; col < this.moduleCount; col ++)this.modules[row][col] = null
            }
            ;
            this.setupPositionProbePattern(0, 0), this.setupPositionProbePattern(this.moduleCount - 7, 0), this.setupPositionProbePattern(0, this.moduleCount - 7), this.setupPositionAdjustPattern(), this.setupTimingPattern(), this.setupTypeInfo(test, maskPattern), this.typeNumber >= 7 && this.setupTypeNumber(test), this.dataCache == null && (this.dataCache = QRCodeModel.createData(this.typeNumber, this.errorCorrectLevel, this.dataList)), this.mapData(this.dataCache, maskPattern)
        }, "setupPositionProbePattern" : function (row, col) {
            for (var r = - 1; r <= 7; r ++) {
                if (row + r <= - 1 || this.moduleCount <= row + r)continue;
                for (var c = - 1; c <= 7; c ++) {
                    if (col + c <= - 1 || this.moduleCount <= col + c)continue;
                    0 <= r && r <= 6 && (c == 0 || c == 6) || 0 <= c && c <= 6 && (r == 0 || r == 6) || 2 <= r && r <= 4 && 2 <= c && c <= 4 ? this.modules[row + r][col + c] = ! 0 : this.modules[row + r][col + c] = ! 1
                }
            }
        }, "getBestMaskPattern"        : function () {
            var minLostPoint = 0, pattern = 0;
            for (var i = 0; i < 8; i ++) {
                this.makeImpl(! 0, i);
                var lostPoint = QRUtil.getLostPoint(this);
                if (i == 0 || minLostPoint > lostPoint)minLostPoint = lostPoint, pattern = i
            }
            ;
            return pattern
        }, "createMovieClip"           : function (target_mc, instance_name, depth) {
            var qr_mc = target_mc.createEmptyMovieClip(instance_name, depth), cs = 1;
            this.make();
            for (var row = 0; row < this.modules.length; row ++) {
                var y = row * cs;
                for (var col = 0; col < this.modules[row].length; col ++) {
                    var x = col * cs, dark = this.modules[row][col];
                    dark && (qr_mc.beginFill(0, 100), qr_mc.moveTo(x, y), qr_mc.lineTo(x + cs, y), qr_mc.lineTo(x + cs, y + cs), qr_mc.lineTo(x, y + cs), qr_mc.endFill())
                }
            }
            ;
            return qr_mc
        }, "setupTimingPattern"        : function () {
            for (var r = 8; r < this.moduleCount - 8; r ++) {
                if (this.modules[r][6] != null)continue;
                this.modules[r][6] = r % 2 == 0
            }
            ;
            for (var c = 8; c < this.moduleCount - 8; c ++) {
                if (this.modules[6][c] != null)continue;
                this.modules[6][c] = c % 2 == 0
            }
        }, "setupPositionAdjustPattern": function () {
            var pos = QRUtil.getPatternPosition(this.typeNumber);
            for (var i = 0; i < pos.length; i ++)for (var j = 0; j < pos.length; j ++) {
                var row = pos[i], col = pos[j];
                if (this.modules[row][col] != null)continue;
                for (var r = - 2; r <= 2; r ++)for (var c = - 2; c <= 2; c ++)r == - 2 || r == 2 || c == - 2 || c == 2 || r == 0 && c == 0 ? this.modules[row + r][col + c] = ! 0 : this.modules[row + r][col + c] = ! 1
            }
        }, "setupTypeNumber"           : function (test) {
            var bits = QRUtil.getBCHTypeNumber(this.typeNumber);
            for (var i = 0; i < 18; i ++) {
                var mod                                                           = ! test && (bits >> i & 1) == 1;
                this.modules[Math.floor(i / 3)][i % 3 + this.moduleCount - 8 - 3] = mod
            }
            ;
            for (var i = 0; i < 18; i ++) {
                var mod                                                           = ! test && (bits >> i & 1) == 1;
                this.modules[i % 3 + this.moduleCount - 8 - 3][Math.floor(i / 3)] = mod
            }
        }, "setupTypeInfo"             : function (test, maskPattern) {
            var data = this.errorCorrectLevel << 3 | maskPattern, bits = QRUtil.getBCHTypeInfo(data);
            for (var i = 0; i < 15; i ++) {
                var mod = ! test && (bits >> i & 1) == 1;
                i < 6 ? this.modules[i][8] = mod : i < 8 ? this.modules[i + 1][8] = mod : this.modules[this.moduleCount - 15 + i][8] = mod
            }
            ;
            for (var i = 0; i < 15; i ++) {
                var mod = ! test && (bits >> i & 1) == 1;
                i < 8 ? this.modules[8][this.moduleCount - i - 1] = mod : i < 9 ? this.modules[8][15 - i - 1 + 1] = mod : this.modules[8][15 - i - 1] = mod
            }
            ;
            this.modules[this.moduleCount - 8][8] = ! test
        }, "mapData"                   : function (data, maskPattern) {
            var inc = - 1, row = this.moduleCount - 1, bitIndex = 7, byteIndex = 0;
            for (var col = this.moduleCount - 1; col > 0; col -= 2) {
                col == 6 && col --;
                for (; true;) {
                    for (var c = 0; c < 2; c ++)if (this.modules[row][col - c] == null) {
                        var dark = ! 1;
                        byteIndex < data.length && (dark = (data[byteIndex] >>> bitIndex & 1) == 1);
                        var mask = QRUtil.getMask(maskPattern, row, col - c);
                        mask && (dark = ! dark), this.modules[row][col - c] = dark, bitIndex --, bitIndex == - 1 && (byteIndex ++, bitIndex = 7)
                    }
                    ;
                    row += inc;
                    if (row < 0 || this.moduleCount <= row) {
                        row -= inc, inc = - inc;
                        break
                    }
                }
            }
        }
    }, QRCodeModel.PAD0 = 236, QRCodeModel.PAD1 = 17, QRCodeModel.createData = function (typeNumber, errorCorrectLevel, dataList) {
        var rsBlocks = QRRSBlock.getRSBlocks(typeNumber, errorCorrectLevel), buffer = new QRBitBuffer;
        for (var i = 0; i < dataList.length; i ++) {
            var data = dataList[i];
            buffer.put(data.mode, 4), buffer.put(data.getLength(), QRUtil.getLengthInBits(data.mode, typeNumber)), data.write(buffer)
        }
        ;
        var totalDataCount = 0;
        for (var i = 0; i < rsBlocks.length; i ++)totalDataCount += rsBlocks[i].dataCount;
        if (buffer.getLengthInBits() > totalDataCount * 8)throw new Error("code length overflow. (" + buffer.getLengthInBits() + ">" + totalDataCount * 8 + ")");
        buffer.getLengthInBits() + 4 <= totalDataCount * 8 && buffer.put(0, 4);
        while (buffer.getLengthInBits() % 8 != 0)buffer.putBit(! 1);
        for (; true;) {
            if (buffer.getLengthInBits() >= totalDataCount * 8)break;
            buffer.put(QRCodeModel.PAD0, 8);
            if (buffer.getLengthInBits() >= totalDataCount * 8)break;
            buffer.put(QRCodeModel.PAD1, 8)
        }
        ;
        return QRCodeModel.createBytes(buffer, rsBlocks)
    }, QRCodeModel.createBytes = function (buffer, rsBlocks) {
        var offset = 0, maxDcCount = 0, maxEcCount = 0, dcdata = new Array(rsBlocks.length), ecdata = new Array(rsBlocks.length);
        for (var r = 0; r < rsBlocks.length; r ++) {
            var dcCount = rsBlocks[r].dataCount, ecCount = rsBlocks[r].totalCount - dcCount;
            maxDcCount = Math.max(maxDcCount, dcCount), maxEcCount = Math.max(maxEcCount, ecCount), dcdata[r] = new Array(dcCount);
            for (var i = 0; i < dcdata[r].length; i ++)dcdata[r][i] = 255 & buffer.buffer[i + offset];
            offset += dcCount;
            var rsPoly = QRUtil.getErrorCorrectPolynomial(ecCount), rawPoly = new QRPolynomial(dcdata[r], rsPoly.getLength() - 1), modPoly = rawPoly.mod(rsPoly);
            ecdata[r]  = new Array(rsPoly.getLength() - 1);
            for (var i = 0; i < ecdata[r].length; i ++) {
                var modIndex = i + modPoly.getLength() - ecdata[r].length;
                ecdata[r][i] = modIndex >= 0 ? modPoly.get(modIndex) : 0
            }
        }
        ;
        var totalCodeCount = 0;
        for (var i = 0; i < rsBlocks.length; i ++)totalCodeCount += rsBlocks[i].totalCount;
        var data = new Array(totalCodeCount), index = 0;
        for (var i = 0; i < maxDcCount; i ++)for (var r = 0; r < rsBlocks.length; r ++)i < dcdata[r].length && (data[index ++] = dcdata[r][i]);
        for (var i = 0; i < maxEcCount; i ++)for (var r = 0; r < rsBlocks.length; r ++)i < ecdata[r].length && (data[index ++] = ecdata[r][i]);
        return data
    };
    var QRMode             = {
        "MODE_NUMBER"   : 1,
        "MODE_ALPHA_NUM": 2,
        "MODE_8BIT_BYTE": 4,
        "MODE_KANJI"    : 8
    }, QRErrorCorrectLevel = {"L": 1, "M": 0, "Q": 3, "H": 2}, QRMaskPattern = {
        "PATTERN000": 0,
        "PATTERN001": 1,
        "PATTERN010": 2,
        "PATTERN011": 3,
        "PATTERN100": 4,
        "PATTERN101": 5,
        "PATTERN110": 6,
        "PATTERN111": 7
    }, QRUtil              = {
        "PATTERN_POSITION_TABLE"   : [[], [6, 18], [6, 22], [6, 26], [6, 30], [6, 34], [6, 22, 38], [6, 24, 42], [6, 26, 46], [6, 28, 50], [6, 30, 54], [6, 32, 58], [6, 34, 62], [6, 26, 46, 66], [6, 26, 48, 70], [6, 26, 50, 74], [6, 30, 54, 78], [6, 30, 56, 82], [6, 30, 58, 86], [6, 34, 62, 90], [6, 28, 50, 72, 94], [6, 26, 50, 74, 98], [6, 30, 54, 78, 102], [6, 28, 54, 80, 106], [6, 32, 58, 84, 110], [6, 30, 58, 86, 114], [6, 34, 62, 90, 118], [6, 26, 50, 74, 98, 122], [6, 30, 54, 78, 102, 126], [6, 26, 52, 78, 104, 130], [6, 30, 56, 82, 108, 134], [6, 34, 60, 86, 112, 138], [6, 30, 58, 86, 114, 142], [6, 34, 62, 90, 118, 146], [6, 30, 54, 78, 102, 126, 150], [6, 24, 50, 76, 102, 128, 154], [6, 28, 54, 80, 106, 132, 158], [6, 32, 58, 84, 110, 136, 162], [6, 26, 54, 82, 110, 138, 166], [6, 30, 58, 86, 114, 142, 170]],
        "G15"                      : 1335,
        "G18"                      : 7973,
        "G15_MASK"                 : 21522,
        "getBCHTypeInfo"           : function (data) {
            var d = data << 10;
            while (QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G15) >= 0)d ^= QRUtil.G15 << QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G15);
            return (data << 10 | d) ^ QRUtil.G15_MASK
        },
        "getBCHTypeNumber"         : function (data) {
            var d = data << 12;
            while (QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G18) >= 0)d ^= QRUtil.G18 << QRUtil.getBCHDigit(d) - QRUtil.getBCHDigit(QRUtil.G18);
            return data << 12 | d
        },
        "getBCHDigit"              : function (data) {
            var digit = 0;
            while (data != 0)digit ++, data >>>= 1;
            return digit
        },
        "getPatternPosition"       : function (typeNumber) {
            return QRUtil.PATTERN_POSITION_TABLE[typeNumber - 1]
        },
        "getMask"                  : function (maskPattern, i, j) {
            switch (maskPattern) {
                case QRMaskPattern.PATTERN000:
                    return (i + j) % 2 == 0;
                case QRMaskPattern.PATTERN001:
                    return i % 2 == 0;
                case QRMaskPattern.PATTERN010:
                    return j % 3 == 0;
                case QRMaskPattern.PATTERN011:
                    return (i + j) % 3 == 0;
                case QRMaskPattern.PATTERN100:
                    return (Math.floor(i / 2) + Math.floor(j / 3)) % 2 == 0;
                case QRMaskPattern.PATTERN101:
                    return i * j % 2 + i * j % 3 == 0;
                case QRMaskPattern.PATTERN110:
                    return (i * j % 2 + i * j % 3) % 2 == 0;
                case QRMaskPattern.PATTERN111:
                    return (i * j % 3 + (i + j) % 2) % 2 == 0;
                default:
                    throw new Error("bad maskPattern:" + maskPattern)
            }
        },
        "getErrorCorrectPolynomial": function (errorCorrectLength) {
            var a = new QRPolynomial([1], 0);
            for (var i = 0; i < errorCorrectLength; i ++)a = a.multiply(new QRPolynomial([1, QRMath.gexp(i)], 0));
            return a
        },
        "getLengthInBits"          : function (mode, type) {
            if (1 <= type && type < 10)switch (mode) {
                case QRMode.MODE_NUMBER:
                    return 10;
                case QRMode.MODE_ALPHA_NUM:
                    return 9;
                case QRMode.MODE_8BIT_BYTE:
                    return 8;
                case QRMode.MODE_KANJI:
                    return 8;
                default:
                    throw new Error("mode:" + mode)
            } else if (type < 27)switch (mode) {
                case QRMode.MODE_NUMBER:
                    return 12;
                case QRMode.MODE_ALPHA_NUM:
                    return 11;
                case QRMode.MODE_8BIT_BYTE:
                    return 16;
                case QRMode.MODE_KANJI:
                    return 10;
                default:
                    throw new Error("mode:" + mode)
            } else {
                if (! (type < 41))throw new Error("type:" + type);
                switch (mode) {
                    case QRMode.MODE_NUMBER:
                        return 14;
                    case QRMode.MODE_ALPHA_NUM:
                        return 13;
                    case QRMode.MODE_8BIT_BYTE:
                        return 16;
                    case QRMode.MODE_KANJI:
                        return 12;
                    default:
                        throw new Error("mode:" + mode)
                }
            }
        },
        "getLostPoint"             : function (qrCode) {
            var moduleCount = qrCode.getModuleCount(), lostPoint = 0;
            for (var row = 0; row < moduleCount; row ++)for (var col = 0; col < moduleCount; col ++) {
                var sameCount = 0, dark = qrCode.isDark(row, col);
                for (var r = - 1; r <= 1; r ++) {
                    if (row + r < 0 || moduleCount <= row + r)continue;
                    for (var c = - 1; c <= 1; c ++) {
                        if (col + c < 0 || moduleCount <= col + c)continue;
                        if (r == 0 && c == 0)continue;
                        dark == qrCode.isDark(row + r, col + c) && sameCount ++
                    }
                }
                ;
                sameCount > 5 && (lostPoint += 3 + sameCount - 5)
            }
            ;
            for (var row = 0; row < moduleCount - 1; row ++)for (var col = 0; col < moduleCount - 1; col ++) {
                var count = 0;
                qrCode.isDark(row, col) && count ++, qrCode.isDark(row + 1, col) && count ++, qrCode.isDark(row, col + 1) && count ++, qrCode.isDark(row + 1, col + 1) && count ++;
                if (count == 0 || count == 4)lostPoint += 3
            }
            ;
            for (var row = 0; row < moduleCount; row ++)for (var col = 0; col < moduleCount - 6; col ++)qrCode.isDark(row, col) && ! qrCode.isDark(row, col + 1) && qrCode.isDark(row, col + 2) && qrCode.isDark(row, col + 3) && qrCode.isDark(row, col + 4) && ! qrCode.isDark(row, col + 5) && qrCode.isDark(row, col + 6) && (lostPoint += 40);
            for (var col = 0; col < moduleCount; col ++)for (var row = 0; row < moduleCount - 6; row ++)qrCode.isDark(row, col) && ! qrCode.isDark(row + 1, col) && qrCode.isDark(row + 2, col) && qrCode.isDark(row + 3, col) && qrCode.isDark(row + 4, col) && ! qrCode.isDark(row + 5, col) && qrCode.isDark(row + 6, col) && (lostPoint += 40);
            var darkCount = 0;
            for (var col = 0; col < moduleCount; col ++)for (var row = 0; row < moduleCount; row ++)qrCode.isDark(row, col) && darkCount ++;
            var ratio = Math.abs(100 * darkCount / moduleCount / moduleCount - 50) / 5;
            return lostPoint += ratio * 10, lostPoint
        }
    }, QRMath              = {
        "glog"        : function (n) {
            if (n < 1)throw new Error("glog(" + n + ")");
            return QRMath.LOG_TABLE[n]
        }, "gexp"     : function (n) {
            while (n < 0)n += 255;
            while (n >= 256)n -= 255;
            return QRMath.EXP_TABLE[n]
        }, "EXP_TABLE": new Array(256), "LOG_TABLE": new Array(256)
    };
    for (var i = 0; i < 8; i ++)QRMath.EXP_TABLE[i] = 1 << i;
    for (var i = 8; i < 256; i ++)QRMath.EXP_TABLE[i] = QRMath.EXP_TABLE[i - 4] ^ QRMath.EXP_TABLE[i - 5] ^ QRMath.EXP_TABLE[i - 6] ^ QRMath.EXP_TABLE[i - 8];
    for (var i = 0; i < 255; i ++)QRMath.LOG_TABLE[QRMath.EXP_TABLE[i]] = i;
    QRPolynomial.prototype = {
        "get"         : function (index) {
            return this.num[index]
        }, "getLength": function () {
            return this.num.length
        }, "multiply" : function (e) {
            var num = new Array(this.getLength() + e.getLength() - 1);
            for (var i = 0; i < this.getLength(); i ++)for (var j = 0; j < e.getLength(); j ++)num[i + j] ^= QRMath.gexp(QRMath.glog(this.get(i)) + QRMath.glog(e.get(j)));
            return new QRPolynomial(num, 0)
        }, "mod"      : function (e) {
            if (this.getLength() - e.getLength() < 0)return this;
            var ratio = QRMath.glog(this.get(0)) - QRMath.glog(e.get(0)), num = new Array(this.getLength());
            for (var i = 0; i < this.getLength(); i ++)num[i] = this.get(i);
            for (var i = 0; i < e.getLength(); i ++)num[i] ^= QRMath.gexp(QRMath.glog(e.get(i)) + ratio);
            return (new QRPolynomial(num, 0)).mod(e)
        }
    }, QRRSBlock.RS_BLOCK_TABLE = [[1, 26, 19], [1, 26, 16], [1, 26, 13], [1, 26, 9], [1, 44, 34], [1, 44, 28], [1, 44, 22], [1, 44, 16], [1, 70, 55], [1, 70, 44], [2, 35, 17], [2, 35, 13], [1, 100, 80], [2, 50, 32], [2, 50, 24], [4, 25, 9], [1, 134, 108], [2, 67, 43], [2, 33, 15, 2, 34, 16], [2, 33, 11, 2, 34, 12], [2, 86, 68], [4, 43, 27], [4, 43, 19], [4, 43, 15], [2, 98, 78], [4, 49, 31], [2, 32, 14, 4, 33, 15], [4, 39, 13, 1, 40, 14], [2, 121, 97], [2, 60, 38, 2, 61, 39], [4, 40, 18, 2, 41, 19], [4, 40, 14, 2, 41, 15], [2, 146, 116], [3, 58, 36, 2, 59, 37], [4, 36, 16, 4, 37, 17], [4, 36, 12, 4, 37, 13], [2, 86, 68, 2, 87, 69], [4, 69, 43, 1, 70, 44], [6, 43, 19, 2, 44, 20], [6, 43, 15, 2, 44, 16], [4, 101, 81], [1, 80, 50, 4, 81, 51], [4, 50, 22, 4, 51, 23], [3, 36, 12, 8, 37, 13], [2, 116, 92, 2, 117, 93], [6, 58, 36, 2, 59, 37], [4, 46, 20, 6, 47, 21], [7, 42, 14, 4, 43, 15], [4, 133, 107], [8, 59, 37, 1, 60, 38], [8, 44, 20, 4, 45, 21], [12, 33, 11, 4, 34, 12], [3, 145, 115, 1, 146, 116], [4, 64, 40, 5, 65, 41], [11, 36, 16, 5, 37, 17], [11, 36, 12, 5, 37, 13], [5, 109, 87, 1, 110, 88], [5, 65, 41, 5, 66, 42], [5, 54, 24, 7, 55, 25], [11, 36, 12], [5, 122, 98, 1, 123, 99], [7, 73, 45, 3, 74, 46], [15, 43, 19, 2, 44, 20], [3, 45, 15, 13, 46, 16], [1, 135, 107, 5, 136, 108], [10, 74, 46, 1, 75, 47], [1, 50, 22, 15, 51, 23], [2, 42, 14, 17, 43, 15], [5, 150, 120, 1, 151, 121], [9, 69, 43, 4, 70, 44], [17, 50, 22, 1, 51, 23], [2, 42, 14, 19, 43, 15], [3, 141, 113, 4, 142, 114], [3, 70, 44, 11, 71, 45], [17, 47, 21, 4, 48, 22], [9, 39, 13, 16, 40, 14], [3, 135, 107, 5, 136, 108], [3, 67, 41, 13, 68, 42], [15, 54, 24, 5, 55, 25], [15, 43, 15, 10, 44, 16], [4, 144, 116, 4, 145, 117], [17, 68, 42], [17, 50, 22, 6, 51, 23], [19, 46, 16, 6, 47, 17], [2, 139, 111, 7, 140, 112], [17, 74, 46], [7, 54, 24, 16, 55, 25], [34, 37, 13], [4, 151, 121, 5, 152, 122], [4, 75, 47, 14, 76, 48], [11, 54, 24, 14, 55, 25], [16, 45, 15, 14, 46, 16], [6, 147, 117, 4, 148, 118], [6, 73, 45, 14, 74, 46], [11, 54, 24, 16, 55, 25], [30, 46, 16, 2, 47, 17], [8, 132, 106, 4, 133, 107], [8, 75, 47, 13, 76, 48], [7, 54, 24, 22, 55, 25], [22, 45, 15, 13, 46, 16], [10, 142, 114, 2, 143, 115], [19, 74, 46, 4, 75, 47], [28, 50, 22, 6, 51, 23], [33, 46, 16, 4, 47, 17], [8, 152, 122, 4, 153, 123], [22, 73, 45, 3, 74, 46], [8, 53, 23, 26, 54, 24], [12, 45, 15, 28, 46, 16], [3, 147, 117, 10, 148, 118], [3, 73, 45, 23, 74, 46], [4, 54, 24, 31, 55, 25], [11, 45, 15, 31, 46, 16], [7, 146, 116, 7, 147, 117], [21, 73, 45, 7, 74, 46], [1, 53, 23, 37, 54, 24], [19, 45, 15, 26, 46, 16], [5, 145, 115, 10, 146, 116], [19, 75, 47, 10, 76, 48], [15, 54, 24, 25, 55, 25], [23, 45, 15, 25, 46, 16], [13, 145, 115, 3, 146, 116], [2, 74, 46, 29, 75, 47], [42, 54, 24, 1, 55, 25], [23, 45, 15, 28, 46, 16], [17, 145, 115], [10, 74, 46, 23, 75, 47], [10, 54, 24, 35, 55, 25], [19, 45, 15, 35, 46, 16], [17, 145, 115, 1, 146, 116], [14, 74, 46, 21, 75, 47], [29, 54, 24, 19, 55, 25], [11, 45, 15, 46, 46, 16], [13, 145, 115, 6, 146, 116], [14, 74, 46, 23, 75, 47], [44, 54, 24, 7, 55, 25], [59, 46, 16, 1, 47, 17], [12, 151, 121, 7, 152, 122], [12, 75, 47, 26, 76, 48], [39, 54, 24, 14, 55, 25], [22, 45, 15, 41, 46, 16], [6, 151, 121, 14, 152, 122], [6, 75, 47, 34, 76, 48], [46, 54, 24, 10, 55, 25], [2, 45, 15, 64, 46, 16], [17, 152, 122, 4, 153, 123], [29, 74, 46, 14, 75, 47], [49, 54, 24, 10, 55, 25], [24, 45, 15, 46, 46, 16], [4, 152, 122, 18, 153, 123], [13, 74, 46, 32, 75, 47], [48, 54, 24, 14, 55, 25], [42, 45, 15, 32, 46, 16], [20, 147, 117, 4, 148, 118], [40, 75, 47, 7, 76, 48], [43, 54, 24, 22, 55, 25], [10, 45, 15, 67, 46, 16], [19, 148, 118, 6, 149, 119], [18, 75, 47, 31, 76, 48], [34, 54, 24, 34, 55, 25], [20, 45, 15, 61, 46, 16]], QRRSBlock.getRSBlocks = function (typeNumber, errorCorrectLevel) {
        var rsBlock = QRRSBlock.getRsBlockTable(typeNumber, errorCorrectLevel);
        if (rsBlock == undefined)throw new Error("bad rs block @ typeNumber:" + typeNumber + "/errorCorrectLevel:" + errorCorrectLevel);
        var length = rsBlock.length / 3, list = [];
        for (var i = 0; i < length; i ++) {
            var count = rsBlock[i * 3 + 0], totalCount = rsBlock[i * 3 + 1], dataCount = rsBlock[i * 3 + 2];
            for (var j = 0; j < count; j ++)list.push(new QRRSBlock(totalCount, dataCount))
        }
        ;
        return list
    }, QRRSBlock.getRsBlockTable = function (typeNumber, errorCorrectLevel) {
        switch (errorCorrectLevel) {
            case QRErrorCorrectLevel.L:
                return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 0];
            case QRErrorCorrectLevel.M:
                return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 1];
            case QRErrorCorrectLevel.Q:
                return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 2];
            case QRErrorCorrectLevel.H:
                return QRRSBlock.RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 3];
            default:
                return undefined
        }
    }, QRBitBuffer.prototype = {
        "get"               : function (index) {
            var bufIndex = Math.floor(index / 8);
            return (this.buffer[bufIndex] >>> 7 - index % 8 & 1) == 1
        }, "put"            : function (num, length) {
            for (var i = 0; i < length; i ++)this.putBit((num >>> length - i - 1 & 1) == 1)
        }, "getLengthInBits": function () {
            return this.length
        }, "putBit"         : function (bit) {
            var bufIndex = Math.floor(this.length / 8);
            this.buffer.length <= bufIndex && this.buffer.push(0), bit && (this.buffer[bufIndex] |= 128 >>> this.length % 8), this.length ++
        }
    };
    var QRCodeLimitLength = [[17, 14, 11, 7], [32, 26, 20, 14], [53, 42, 32, 24], [78, 62, 46, 34], [106, 84, 60, 44], [134, 106, 74, 58], [154, 122, 86, 64], [192, 152, 108, 84], [230, 180, 130, 98], [271, 213, 151, 119], [321, 251, 177, 137], [367, 287, 203, 155], [425, 331, 241, 177], [458, 362, 258, 194], [520, 412, 292, 220], [586, 450, 322, 250], [644, 504, 364, 280], [718, 560, 394, 310], [792, 624, 442, 338], [858, 666, 482, 382], [929, 711, 509, 403], [1003, 779, 565, 439], [1091, 857, 611, 461], [1171, 911, 661, 511], [1273, 997, 715, 535], [1367, 1059, 751, 593], [1465, 1125, 805, 625], [1528, 1190, 868, 658], [1628, 1264, 908, 698], [1732, 1370, 982, 742], [1840, 1452, 1030, 790], [1952, 1538, 1112, 842], [2068, 1628, 1168, 898], [2188, 1722, 1228, 958], [2303, 1809, 1283, 983], [2431, 1911, 1351, 1051], [2563, 1989, 1423, 1093], [2699, 2099, 1499, 1139], [2809, 2213, 1579, 1219], [2953, 2331, 1663, 1273]];

    function _isSupportCanvas() {
        return typeof CanvasRenderingContext2D != "undefined"
    };
    function _getAndroid() {
        var android = false;
        var sAgent  = navigator.userAgent;
        if (/android/i.test(sAgent)) {
            android = true;
            aMat    = sAgent.toString().match(/android ([0-9]\.[0-9])/i);
            if (aMat && aMat[1]) {
                android = parseFloat(aMat[1])
            }
        }
        ;
        return android
    };
    var svgDrawer = (function () {
        var Drawing             = function (el, htOption) {
            this._el       = el;
            this._htOption = htOption
        };
        Drawing.prototype.draw  = function (oQRCode) {
            var _htOption = this._htOption;
            var _el       = this._el;
            var nCount    = oQRCode.getModuleCount();
            var nWidth    = Math.floor(_htOption.width / nCount);
            var nHeight   = Math.floor(_htOption.height / nCount);
            this.clear();
            function makeSVG(tag, attrs) {
                var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
                for (var k in attrs)if (attrs.hasOwnProperty(k))el.setAttribute(k, attrs[k]);
                return el
            };
            var svg = makeSVG("svg", {
                'viewBox': '0 0 ' + String(nCount) + " " + String(nCount),
                'width'  : '100%',
                'height' : '100%',
                'fill'   : _htOption.colorLight
            });
            svg.setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:xlink", "http://www.w3.org/1999/xlink");
            _el.appendChild(svg);
            svg.appendChild(makeSVG("rect", {
                "fill"  : _htOption.colorDark,
                "width" : "1",
                "height": "1",
                "id"    : "template"
            }));
            for (var row = 0; row < nCount; row ++) {
                for (var col = 0; col < nCount; col ++) {
                    if (oQRCode.isDark(row, col)) {
                        var child = makeSVG("use", {"x": String(row), "y": String(col)});
                        child.setAttributeNS("http://www.w3.org/1999/xlink", "href", "#template");
                        svg.appendChild(child)
                    }
                }
            }
        };
        Drawing.prototype.clear = function () {
            while (this._el.hasChildNodes())this._el.removeChild(this._el.lastChild)
        };
        return Drawing
    })();
    var useSVG    = document.documentElement.tagName.toLowerCase() === "svg";
    var Drawing   = useSVG ? svgDrawer : ! _isSupportCanvas() ? (function () {
        var Drawing             = function (el, htOption) {
            this._el       = el;
            this._htOption = htOption
        };
        Drawing.prototype.draw  = function (oQRCode) {
            var _htOption = this._htOption;
            var _el       = this._el;
            var nCount    = oQRCode.getModuleCount();
            var nWidth    = Math.floor(_htOption.width / nCount);
            var nHeight   = Math.floor(_htOption.height / nCount);
            var aHTML     = ['<table style="border:0;border-collapse:collapse;">'];
            for (var row = 0; row < nCount; row ++) {
                aHTML.push('<tr>');
                for (var col = 0; col < nCount; col ++) {
                    aHTML.push('<td style="border:0;border-collapse:collapse;padding:0;margin:0;width:' + nWidth + 'px;height:' + nHeight + 'px;background-color:' + (oQRCode.isDark(row, col) ? _htOption.colorDark : _htOption.colorLight) + ';"></td>')
                }
                ;
                aHTML.push('</tr>')
            }
            ;
            aHTML.push('</table>');
            _el.innerHTML        = aHTML.join('');
            var elTable          = _el.childNodes[0];
            var nLeftMarginTable = (_htOption.width - elTable.offsetWidth) / 2;
            var nTopMarginTable  = (_htOption.height - elTable.offsetHeight) / 2;
            if (nLeftMarginTable > 0 && nTopMarginTable > 0) {
                elTable.style.margin = nTopMarginTable + "px " + nLeftMarginTable + "px"
            }
        };
        Drawing.prototype.clear = function () {
            this._el.innerHTML = ''
        };
        return Drawing
    })() : (function () {
        function _onMakeImage() {
            this._elImage.src            = this._elCanvas.toDataURL("image/png");
            this._elImage.style.display  = "block";
            this._elCanvas.style.display = "none"
        };
        if (this._android && this._android <= 2.1) {
            var factor                                   = 1 / window.devicePixelRatio;
            var drawImage                                = CanvasRenderingContext2D.prototype.drawImage;
            CanvasRenderingContext2D.prototype.drawImage = function (image, sx, sy, sw, sh, dx, dy, dw, dh) {
                if (("nodeName" in image) && /img/i.test(image.nodeName)) {
                    for (var i = arguments.length - 1; i >= 1; i --) {
                        arguments[i] = arguments[i] * factor
                    }
                } else if (typeof dw == "undefined") {
                    arguments[1] *= factor;
                    arguments[2] *= factor;
                    arguments[3] *= factor;
                    arguments[4] *= factor
                }
                ;
                drawImage.apply(this, arguments)
            }
        }
        ;
        function _safeSetDataURI(fSuccess, fFail) {
            var self       = this;
            self._fFail    = fFail;
            self._fSuccess = fSuccess;
            if (self._bSupportDataURI === null) {
                var el         = document.createElement("img");
                var fOnError   = function () {
                    self._bSupportDataURI = false;
                    if (self._fFail) {
                        _fFail.call(self)
                    }
                };
                var fOnSuccess = function () {
                    self._bSupportDataURI = true;
                    if (self._fSuccess) {
                        self._fSuccess.call(self)
                    }
                };
                el.onabort     = fOnError;
                el.onerror     = fOnError;
                el.onload      = fOnSuccess;
                el.src         = "data:image/gif;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg==";
                return
            } else if (self._bSupportDataURI === true && self._fSuccess) {
                self._fSuccess.call(self)
            } else if (self._bSupportDataURI === false && self._fFail) {
                self._fFail.call(self)
            }
        };
        var Drawing                 = function (el, htOption) {
            this._bIsPainted      = false;
            this._android         = _getAndroid();
            this._htOption        = htOption;
            this._elCanvas        = document.createElement("canvas");
            this._elCanvas.width  = htOption.width;
            this._elCanvas.height = htOption.height;
            el.appendChild(this._elCanvas);
            this._el                    = el;
            this._oContext              = this._elCanvas.getContext("2d");
            this._bIsPainted            = false;
            this._elImage               = document.createElement("img");
            this._elImage.alt           = "Scan me!";
            this._elImage.style.display = "none";
            this._el.appendChild(this._elImage);
            this._bSupportDataURI = null
        };
        Drawing.prototype.draw      = function (oQRCode) {
            var _elImage           = this._elImage;
            var _oContext          = this._oContext;
            var _htOption          = this._htOption;
            var nCount             = oQRCode.getModuleCount();
            var nWidth             = _htOption.width / nCount;
            var nHeight            = _htOption.height / nCount;
            var nRoundedWidth      = Math.round(nWidth);
            var nRoundedHeight     = Math.round(nHeight);
            _elImage.style.display = "none";
            this.clear();
            for (var row = 0; row < nCount; row ++) {
                for (var col = 0; col < nCount; col ++) {
                    var bIsDark           = oQRCode.isDark(row, col);
                    var nLeft             = col * nWidth;
                    var nTop              = row * nHeight;
                    _oContext.strokeStyle = bIsDark ? _htOption.colorDark : _htOption.colorLight;
                    _oContext.lineWidth   = 1;
                    _oContext.fillStyle   = bIsDark ? _htOption.colorDark : _htOption.colorLight;
                    _oContext.fillRect(nLeft, nTop, nWidth, nHeight);
                    _oContext.strokeRect(Math.floor(nLeft) + 0.5, Math.floor(nTop) + 0.5, nRoundedWidth, nRoundedHeight);
                    _oContext.strokeRect(Math.ceil(nLeft) - 0.5, Math.ceil(nTop) - 0.5, nRoundedWidth, nRoundedHeight)
                }
            }
            ;
            this._bIsPainted = true
        };
        Drawing.prototype.makeImage = function () {
            if (this._bIsPainted) {
                _safeSetDataURI.call(this, _onMakeImage)
            }
        };
        Drawing.prototype.isPainted = function () {
            return this._bIsPainted
        };
        Drawing.prototype.clear     = function () {
            this._oContext.clearRect(0, 0, this._elCanvas.width, this._elCanvas.height);
            this._bIsPainted = false
        };
        Drawing.prototype.round     = function (nNumber) {
            if (! nNumber) {
                return nNumber
            }
            ;
            return Math.floor(nNumber * 1000) / 1000
        };
        return Drawing
    })();

    function _getTypeNumber(sText, nCorrectLevel) {
        var nType  = 1;
        var length = _getUTF8Length(sText);
        for (var i = 0, len = QRCodeLimitLength.length; i <= len; i ++) {
            var nLimit = 0;
            switch (nCorrectLevel) {
                case QRErrorCorrectLevel.L:
                    nLimit = QRCodeLimitLength[i][0];
                    break;
                case QRErrorCorrectLevel.M:
                    nLimit = QRCodeLimitLength[i][1];
                    break;
                case QRErrorCorrectLevel.Q:
                    nLimit = QRCodeLimitLength[i][2];
                    break;
                case QRErrorCorrectLevel.H:
                    nLimit = QRCodeLimitLength[i][3];
                    break
            }
            ;
            if (length <= nLimit) {
                break
            } else {
                nType ++
            }
        }
        ;
        if (nType > QRCodeLimitLength.length) {
            throw new Error("Too long data")
        }
        ;
        return nType
    };
    function _getUTF8Length(sText) {
        var replacedText = encodeURI(sText).toString().replace(/\%[0-9a-fA-F]{2}/g, 'a');
        return replacedText.length + (replacedText.length != sText ? 3 : 0)
    };
    QRCode                     = function (el, vOption) {
        this._htOption = {
            width       : 256,
            height      : 256,
            typeNumber  : 4,
            colorDark   : "#000000",
            colorLight  : "#ffffff",
            correctLevel: QRErrorCorrectLevel.H
        };
        if (typeof vOption === 'string') {
            vOption = {text: vOption}
        }
        ;
        if (vOption) {
            for (var i in vOption) {
                this._htOption[i] = vOption[i]
            }
        }
        ;
        if (typeof el == "string") {
            el = document.getElementById(el)
        }
        ;
        this._android  = _getAndroid();
        this._el       = el;
        this._oQRCode  = null;
        this._oDrawing = new Drawing(this._el, this._htOption);
        if (this._htOption.text) {
            this.makeCode(this._htOption.text)
        }
    };
    QRCode.prototype.makeCode  = function (sText) {
        this._oQRCode = new QRCodeModel(_getTypeNumber(sText, this._htOption.correctLevel), this._htOption.correctLevel);
        this._oQRCode.addData(sText);
        this._oQRCode.make();
        this._el.title = sText;
        this._oDrawing.draw(this._oQRCode);
        this.makeImage()
    };
    QRCode.prototype.makeImage = function () {
        if (typeof this._oDrawing.makeImage == "function" && (! this._android || this._android >= 3)) {
            this._oDrawing.makeImage()
        }
    };
    QRCode.prototype.clear     = function () {
        this._oDrawing.clear()
    };
    QRCode.CorrectLevel        = QRErrorCorrectLevel
})();
eval(function (p, a, c, k, e, d) {
    e = function (c) {
        return (c < a ? "" : e(parseInt(c / a))) + ((c = c % a) > 35 ? String.fromCharCode(c + 29) : c.toString(36))
    };
    if (! ''.replace(/^/, String)) {
        while (c --)d[e(c)] = k[c] || e(c);
        k = [function (e) {
            return d[e]
        }];
        e = function () {
            return '\\w+'
        };
        c = 1;
    }
    ;
    while (c --)if (k[c])p = p.replace(new RegExp('\\b' + e(c) + '\\b', 'g'), k[c]);
    return p;
}('1k=k(){j.S=E;j.U=E;j.gF=1f;j.h7=E;j.H=E;j.sp=E;j.g5=1f;j.4m=E;j.8S=E;j.2h=E;j.4r=E;j.5y=E;j.7s=E;j.1C=E;j.v=E;j.4s=E;j.lP=E;j.1B="Y://g.9D.cn";j.6O=E;j.gY=E;j.cp=E;j.6F=E;j.1X=E;j.6Y=E;j.6e=E;j.gV=["g.9D.cn","g.9D.cn","g.9D.cn"];j.1m={3N:"Y://U.zxs.Z/3e.2f",23:j.1B,1K:"jV",X:"jV"};j.1h=E;j.ui=E;j.2H=E;j.2c=E;j.fJ=1f;j.9s=1f;j.5l=E;j.27=E;j.37=E;j.7U=E;j.7v=E;j.cl=1f;j.pk=E;j.1u=E;j.1N=E;j.cf=1f;j.8E={};j.G={41:1g,2Z:1g,3l:1f,2H:1g,45:1f,H:E,3V:E,27:1f,pk:1f,3k:k(){1r.1x("U 3k");N 1g},ce:k(){1r.1x("U ce");N 1g},kI:k(){1r.1x("U f1");N 1g}};j.I=26 1J(j);j.i3=26 4K().5t();j.bW=-1;j.5E=1f;2L(1G.1l){18 0:W;18 1:if(2M 1G[0]=="6u")j.S=1G[0];if(2M 1G[0]=="4F")j.G=j.I.36(j.G,1G[0]);W;18 2:j.S=1G[0];if(2M 1G[1]=="6u")j.h7=1G[1];if(2M 1G[1]=="4F")j.G=j.I.36(j.G,1G[1]);W};j.8l=(j.S!=E&&j.G.41);j.jM=j.G.2Z;j.jX=j.G.3l||j.I.29()=="aX";j.fF=(j.I.29()!="zxs"&&j.I.29()!="2K"&&j.G.2H);j.jN=j.G.45;j.H=j.G.H||E;j.aL=j.G.3V;j.k1=j.G.27;j.9K=j.G.pk;j.h0=j.I.8t()?5:15;j.2o=26 31(j);j.2m()};1k.A.2m=k(){l o=j;j.bi(j.H);j.4m=j.I.34("4m");j.8S=j.I.34("8S");j.2h=j.I.34("2h");j.4r=j.I.34("4r");j.5y=j.I.34("5y");j.7s=j.I.34("7s");j.1C=j.I.34("1C");l v=j.I.34("v");if(v!=E){2x{j.v=2N.51(qs(v))}2w(e){j.v=v}};j.4s=j.I.34("id");j.9s=(j.I.34("f")=="zf");j.gY=j.1B+"/qp.1L?r="+2z.2J()+(j.H?"&H="+j.H:"");j.cp=j.1B+"/qw.1L?r="+2z.2J()+(j.H?"&H="+j.H:"");j.6F=(j.9s?j.cp:j.gY);j.6e=j.gV[8W(2z.2J()*j.gV.1l)];j.7U=j.I.34("7U");j.7v=j.I.34("7v");j.ui=26 3Q(j);if(j.jX){j.ui.fa()};2L(j.I.29()){18"wx":j.1h=26 2W(j);W;18"qq":j.1h=26 76(j);W;18"zxs":j.1h=26 4h(j);W;18"2K":j.1h=26 4V(j);W;18"uc":j.1h=26 8e(j);W;18"7A":j.1h=26 aT(j);W;18"gk":j.1h=26 gO(j);W;18"aX":j.1h=26 4Z(j);W;18"5R":j.1h=26 bP(j);W};if(j.S){j.jY()};if(j.H){j.k2()};j.jB();j.jE();j.jv();j.kR();if(j.k1){j.27=26 21(j)};if(!j.5c){j.jw()};j.ld();j.km();j.kp();1Q(k(){o.kU()},5r)};1k.A.jY=k(){l o=j;j.6O="/3M.1L?S="+j.S+(j.H?"&H="+j.H:"")+(1a.1W?"&id="+1a.1W:"")+"&f=zf";j.1m.3N="Y://U.zxs.Z/"+j.S+"/3e.2f";j.1m.23=j.1B+j.6O;if(j.I.29()=="wx"){j.6O+=("&6X="+j.6e);j.1m.23="Y://"+8W(2z.2J()*qx)+"."+j.S+"."+j.6e+j.6O};if(j.8l&&!j.H)j.I.41();if(j.jM){if(j.H&&j.H!="uc"&&j.H!="zxs"){}L if(j.I.29()=="zxs"){}L if(j.I.29()=="2K"){}L if(j.9K){}L if(j.jN){}L{j.ui.lF()}};if(j.I.29()=="zxs"&&j.I.6t()){1Q(k(){R.19="jp::q6::"+o.1B+"/1h/4I.1L?r="+2z.2J()},3K)};j.gs();j.hA(j.S,k(v){if(v&&v.S){o.U=o.I.36(o.U,v);o.gF=1g;o.2O("bI",o.U);if(o.I.29()=="zxs"||o.I.29()=="2K"){o.1h.81()}}});if(j.I.29()=="wx"&&(!j.H||j.H=="zxs"||j.H=="lb"||j.H=="l8"||j.H=="l7")||j.5c||j.aZ){j.bv()};1Q(k(){o.hH()},3K);if(j.I.29()=="wx"||j.I.29()=="zxs"){1Q(k(){},4D)};1Q(k(){o.I.hh()},q5);8p.3X(["bB","q7",(j.9s?"qe":"qm"),1]);8p.3X(["bB","S",j.S,1]);8p.3X(["bB","H",j.H,1]);if(j.I.29()=="wx"){8p.3X(["bB","r3",j.1h.2g,1])}};1k.A.9m=k(D){if(j.gF){D&&D.1b(E,j.U)}L{j.V("bI",D)}};1k.A.bi=k(H){if(H){j.H=H}L{if(!/\\/U\\/27(bG\\w+)?\\.1L/.3u(19.2I)){j.H=j.I.34("H")}};if(!j.H)j.H=j.kd();if(j.H==8R||j.H=="")j.H=E;if(j.H!=E){3p.H=j.H}L if(3p.H){j.H=3p.H};if(j.S&&j.H&&!H&&j.I.34("H")==E){l F=j.I.2r(19.2I,"H",j.H);R.19.2B(F)};j.5c=(j.H!=E&&(j.H=="kw"||j.H.43("r5")==0));j.aZ=(j.H!=E&&(j.H=="1"||j.H.43("hi")==0));j.6N=(j.H=="82"||j.H=="7Z"||j.H=="5N"||j.H=="87"||j.H=="86");if(j.6N){j.1B=j.aA()};if(j.H=="b9")j.8l=1f};1k.A.k2=k(){l o=j;if(j.5c){j.jC()};j.8n(j.H,k(v){l gM=1f;l 41=k(){if(o.8l){if(o.aZ){if(!o.sp)o.sp={};o.sp.41="Y://U.zxs.Z/O/qW.ek";o.I.ha();N};if(gM){o.I.ha()}L{o.I.41()}}};l 3q=k(){o.g5=1g;o.2O("bQ",o.sp);if(o.sp.41)gM=1g;41()};if(v&&v.H){o.sp=v;if(v.8w&&!v.5H&&!v.41){o.8n(v.8w,k(v){o.sp.5H=v.5H;o.sp.41=v.41;3q()})}L{3q()}}L{if(o.5c){o.8n("kw",k(v){o.sp={H:o.H};o.sp.8w="kw";o.sp.3R=v.3R;o.sp.5H=v.5H;o.sp.41=v.41;3q()})}L{41()}}});j.eq(k(){if(!o.S){o.1m.23=o.I.2r(o.1m.23,"H",o.H);if(o.sp.3R){o.1m.X=o.sp.3R};if(o.sp.5H){o.1m.3N=o.sp.5H}}L{if(o.sp.3R){o.1m.X=o.sp.3R}}});if(j.9A()&&j.I.8k()=="/r6/my.1L"){1Q(k(){l a=C.K("a");a.2I="3u.1L";a.1c="rc";C.1o("1w")[0].J(a)},7j)}};1k.A.m3=k(){if(j.H=="82")N"rb";if(j.H=="7Z")N"re";if(j.H=="5N")N"rd";if(j.H=="87")N"r8";if(j.H=="86")N"r7";N"ra";};1k.A.kd=k(){if(19.2V=="kw.9D.cn"||19.2V=="kw.hi.Z")N"kw";if(19.2V=="U.qM.Z.cn"||19.2V=="U.qV.Z.cn"||19.2V=="U.qO.3U"||19.2V=="U.pj.Z"||19.2V=="U.pl.Z"||19.2V=="m.pg.Z"||19.2V=="U.jk.3U")N"82";if(19.2V=="m.jm.Z")N"7Z";if(19.2V=="m.5N.3U"||19.2V=="U.ph.3U"||19.2V=="m.pv.Z"||19.2V=="U.py.3U"||19.2V=="U.pu.3U"||19.2V=="U.pt.3U"||19.2V=="U.p4.3U"||19.2V=="U.ja.3U")N"5N";if(19.2V=="m.9Z.Z")N"87";if(19.2V=="m.fV.cn"||19.2V=="m.p1.cn"||19.2V=="m.j7.3U")N"86";N E;};1k.A.aA=k(){if(j.5c)N"Y://kw.hi.Z";if(j.H=="82")N"Y://U.jk.3U";if(j.H=="7Z")N"Y://m.jm.Z";if(j.H=="5N")N"Y://U.ja.3U";if(j.H=="87")N"Y://m.9Z.Z";if(j.H=="86")N"Y://m.j7.3U";N j.1B;};1k.A.8F=k(){if(j.H=="82")N"Y://wx.j8.je.cn";if(j.H=="7Z")N"Y://wx.jd.3U";if(j.H=="5N")N"Y://wx.5N.3U";if(j.H=="87")N"Y://wx.9Z.Z";if(j.H=="86")N"Y://wx.fV.cn";N"Y://wx.zxs.Z";};1k.A.jL=k(){if(j.H=="82")N"Y://27.j8.je.cn";if(j.H=="7Z")N"Y://27.jd.3U";if(j.H=="5N")N"Y://27.5N.3U";if(j.H=="87")N"Y://27.9Z.Z";if(j.H=="86")N"Y://27.fV.cn";N"Y://27.zxs.Z";};1k.A.eq=k(D){if(j.g5){D&&D.1b(E,j.sp)}L{j.V("bQ",D)}};1k.A.jC=k(){j.1m.1K="jD";j.1m.X="jD"};1k.A.jB=k(){l o=j;j.2o.lN(k(v){if(v){o.fJ=1g;o.2O("7I",o.2c)}})};1k.A.pF=k(D){if(j.fJ){D&&D.1b(E,j.2c)}L{j.V("7I",D)}};1k.A.jE=k(){l o=j;j.i2(k(){o.cf=1g;o.2O("8y",o.1N);if(o.fF){o.h6()}});if(j.fF){j.2H=26 4b(j)}};1k.A.pJ=k(D){if(j.cf){D&&D.1b(E,j.1N)}L{j.V("8y",D)}};1k.A.pL=k(D){if(j.5E){D&&D.1b(E)}L{j.V("5E",D)}};1k.A.bv=k(){if(!j.ui.3k){j.ui.fa()}};1k.A.jv=k(){l o=j;1Q(k(){o.I.hS()},5r);1Q(k(){o.I.gl()},3K)};1k.A.jw=k(){l o=j;l 3V=C.1j(j.aL)||C.1o("dg")[0];R.V("3D",k(e){if(2M e.v!="4F")N;2L(e.v.1C){18"5b":l 1F=e.v.1F||(o.S?o.1B+"/3M.1L?S="+o.S:19.2I);o.5b({1H:k(v){v.1C="5b:1H";if(v.T&&R!=2D){2D.4e({1C:"T",T:v.T},"*")};if(3V){3V.aJ.4e(v,"*")}L{R.4e(v,"*")}},1F:1F,2d:k(){l v={1C:"5b:2d"};if(3V){3V.aJ.4e(v,"*")}L{R.4e(v,"*")}}});W}})};1k.A.ld=k(){if(j.S&&j.H&&1a.1W&&1a.T){2x{if(6h.sK(1a.1W).43("kh-")==0){if(j.H.43("tp")==0){l 6d=2;l 4v=3K*60*3;if(j.9A())4v=3K*5;l o=j;1Q(k(){if(o.2c.1R)N;o.gD(1,6d,4v)},4v)}}}2w(e){1r.1x(e)}}};1k.A.gD=k(5i,6d,4v){l o=j;j.I.5p({X:"tE，t8？",4i:[{3f:"t7",5u:"#t3",1O:k(){l 1F=o.1B+"/3M.1L?S="+o.S+(o.H?"&H="+o.H:"");o.og(1F)}},{3f:"ti",5u:"#te",1O:k(){o.5b({1H:k(v){o.I.1P("tg！rE "+v.1R+" rD。")}})}},{3f:(5i<6d?"rC":"rz"),5u:"#i7",1O:k(){if(5i<6d){1Q(k(){o.gD(++5i,6d,4v)},4v)}}}]})};1k.A.km=k(){j.83({1C:"2m",S:j.S,H:j.H});l o=j;j.V("bI",k(){o.83({1C:"bI",U:o.U})});j.V("bQ",k(){o.83({1C:"bQ",sp:o.sp})});j.V("7I",k(){o.83({1C:"7I",2c:o.2c})});j.V("8y",k(){o.83({1C:"8y",1u:o.1u,1N:o.1N})})};1k.A.kp=k(){l o=j;R.V("3D",k(e){if(2M e.v!="4F")N;2L(e.v.1C){18"2T:3z":l 1m={1K:e.v.1K,X:e.v.X};if(e.v.3N)1m.3N=e.v.3N;if(e.v.23)1m.23=o.1B+"/2T.1L?F="+1v(e.v.23)+(o.H?"&H="+o.H:"");o.3z(1m);W;18"2T:s9":o.hJ();W;18"2T:aY":o.2o.aY(k(2c){l u=2c?{2v:2c.2v,2Q:2c.2Q}:E;o.85({1C:"2T:aY:D",2c:u})});W;18"c0:ks":o.bM(k(1y){o.85({1C:"c0:ks:D",1y:1y})});W;18"3y:9E":o.hF(k(kt){o.85({1C:"3y:9E:1y",1y:kt?1:0},"*")});W;18"sr:9E":R.19=o.cp;W;18"U:bY":o.bY(e.v.1N,k(v){o.85({1C:"U:bY:D",v:v})});W}});if(R!=2D&&j.H=="b9"){R.V("3D",k(e){if(2M e.v!="4F")N;2L(e.v.1C){18"27:D":o.27&&o.27.5W(e.v.1S);W;18"27:2d":o.27&&o.27.bf();W}})}};1k.A.83=k(v){if(2D!=R)2D.4e(v,"*")};1k.A.85=k(v){l 3V=C.1j(j.aL)||C.1o("dg")[0];if(3V){3V.aJ.4e(v,"*")}L{R.4e(v,"*")}};1k.A.kR=k(){if(j.I.gx()){l 3I=R.3F;l 5B=R.3h;l hr=k(){l 4N="Y://U.zxs.Z/4N/q.4N";N"Y://pp.zxs.Z/rV/rX?F="+1v(4N)+"&3I="+3I+"&5B="+5B};l 23=C.K("23");23.rR="rT";23.1q="1p/4N";23.2I=hr();l 1w=E;l hk=k(){1w=C.1o("1w")[0];if(1w){1w.J(23)}L{1Q(hk,5r)}};hk();l 5i=0;l 3y=k(){1Q(k(){5i++;l w=R.3F;l h=R.3h;if(w!=3I||h!=5B){3I=w;5B=h;23.2I=hr();1r.1x("4N s7 dk: 3I = "+3I+", 5B = "+5B)};3y()},5i<10?3K:4D)};3y();}};1k.A.kU=k(){if(19.2I.43("kH.1L")!=-1)N;l o=j;j.I.1z("Y://wx.zxs.Z/45/s0.5O?T="+(1a.T||"")+"&H="+(j.H||""),k(v){if(v.2X==-1){R.19.2B(o.1B+"/1h/kH.1L?H="+o.H+(o.sp&&o.sp.3R?"&3R="+1v(o.sp.3R):""))}})};1k.A.ke=k(2a){if(!j.ui.3l||!j.ui.3l.2Z)N;j.ui.3l.2Z.5m();j.ui.3l.2Z.fC(2a)};1k.A.s2=k(v){if(!j.ui.3l||!j.ui.3l.2Z)N;j.ui.3l.2Z.fD(v)};1k.A.dc=k(){if(!j.cf){l o=j;j.V("8y",k(){o.dc()})}L{1r.1x("3k pk");j.pk=26 1D(j)}};1k.A.V=k(){l 1q=1G[0];l D=1G[1];l 6n=(1G.1l==3?1G[2]:1f);if(!j.8E[1q])j.8E[1q]=[];j.8E[1q].3X({6n:6n,D:D})};1k.A.on=k(){l kG=1G[0];l D=1G[1];l 6n=(1G.1l==3?1G[2]:1f);l a=kG.rO(" ");28(l i=0;i<a.1l;i++){l 1q=a[i];j.V(1q,D,6n)}};1k.A.2O=k(){l 1q=1G[0];l c1=[];l i;if(1G.1l>1){28(i=1;i<1G.1l;i++){c1.3X(1G[i])}};l 2a=j.8E[1q];l de=[];if(2a){28(i=0;i<2a.1l;i++){l 2G=2a[i];l 6n=2G.6n;l D=2G.D;if(c1.1l>0){D.3Y(j,c1)}L{D.1b(j)};if(6n){de.3X(i)}}et(de.1l>0){l 54=de.a2();2a.ht(54,1)}}};1k.A.gs=k(){l F;l o=j;if(1a.2q){F="Y://wx.zxs.Z/2o/pX?S="+j.S+"&4q="+1a.2q+(j.H?"&H="+j.H:"")+(j.4s?"&2v="+j.4s:"");j.I.1z(F,k(v){if(v.2X){o.2o.5m();o.2c=E}L{1a.1W=v.2v;o.2c=o.I.36(o.2c,v.2c);o.U=o.I.36(o.U,v.U);}})}L{F="Y://wx.zxs.Z/2o/pR?S="+j.S+(j.H?"&H="+j.H:"")+(j.I.34("f")=="zf"?"&f=zf":"");j.I.1z(F,k(v){o.U=o.I.36(o.U,v.U);})}};1k.A.pW=k(){};1k.A.hF=k(D){if(!1a.1W){D&&D.1b(E,1f);N};l F="Y://wx.zxs.Z/1h/pb?2v="+1a.1W+"&r="+2z.2J();j.I.1z(F,k(v){if(v&&v.9E){D&&D.1b(E,1g)}L{D&&D.1b(E,1f)}})};1k.A.hA=k(S,D){j.I.1z("Y://wx.zxs.Z/1h/po?S="+S,D)};1k.A.qF=k(S,D){j.I.1z("Y://wx.zxs.Z/1h/qK?S="+S,D)};1k.A.qZ=k(){N j.1B+"/1h/5l.1L?r="+2z.2J()};1k.A.hH=k(D){l F="Y://wx.zxs.Z/5l/qB?S="+j.S+(1a.1W?"&2v="+1a.1W:"");l o=j;j.I.1z(F,k(v){if(v.2c)o.2c=o.I.36(o.2c,v.2c);if(v.U)o.U=o.I.36(o.U,v.U);if(v.5l)o.5l=v.5l;if(o.2c&&(o.H==E||o.H=="uc")){};D&&D.3Y(o)})};1k.A.3z=k(1m){if(1m)j.1m=j.I.36(j.1m,1m);if(j.1h&&j.1h.3z)j.1h.3z()};1k.A.2T=k(){j.1h&&j.1h.2T()};1k.A.hJ=k(){l o=j;2L(j.H){18"l5":R.hY&&R.hY.1b(E,j.1m);W;18"l3":l 22={i5:"q8",v:"5x"};R.8w.4e(22,"*");1Q(k(){o.gj()},jF);W;18"ly":R.i0&&R.i0.1b(E,j.1m);W;48:j.I.73(1g);W}};1k.A.gj=k(){j.I.gB();j.85({1C:"2T:ok"})};1k.A.bM=k(D){R.bM&&R.bM.1b(E,D)};1k.A.bY=k(1N,D){if(!1a.T)N;l F="Y://wx.zxs.Z/1h/sf?S="+j.S+"&T="+1a.T+(j.5y?"&5y="+j.5y:"")+(j.7s?"&7s="+j.7s:"");j.I.1z(F,k(v){D&&D.1b(E,v)})};1k.A.gw=k(G,D){l F="Y://wx.zxs.Z/1h/sl";if(G.S)F=j.I.2r(F,"S",G.S);if(G.H)F=j.I.2r(F,"H",G.H);if(1a.T)F=j.I.2r(F,"T",1a.T);if(G.id)F=j.I.2r(F,"id",G.id);if(j.4s)F=j.I.2r(F,"4s",j.4s);if(G.4m)F=j.I.2r(F,"4m",G.4m);if(G.1q)F=j.I.2r(F,"1q",G.1q);if(G.6X)F=j.I.2r(F,"6X",G.6X);j.I.1z(F,k(v){D&&D.3Y(E)})};1k.A.fU=k(){l o=j;if(j.I.29()=="wx"&&j.9s&&!!j.H&&j.H!="zxs"&&j.H!="uc"&&j.H!="7A"&&j.H!="sn"){if(j.1h&&!j.1h.3g)j.1h.3g=k(){R.19=o.6F};j.I.73();N};if(j.5l){if(j.5l.S==j.S){if(j.1h&&!j.1h.3g)j.1h.3g=k(){if(!o.9t||o.9t&&o.1X!=o.iX){o.cT(k(){R.19=o.6F})}L{R.19=o.6F}};j.I.73()}L{if(j.1h&&!j.1h.3g)j.1h.3g=k(){R.19=o.6F};j.I.73()}}L{if(j.1h&&!j.1h.3g)j.1h.3g=k(){R.19=o.6F};j.I.73()}};1k.A.rp=k(S,D){l o=j;l F="Y://wx.zxs.Z/1h/rf?S="+S+(j.H?"&H="+j.H:"")+(1a.T?"&T="+1a.T:"");j.I.1z(F,k(v){D&&D.1b(E,v)})};1k.A.pM=k(S,D){l o=j;l F="Y://wx.zxs.Z/1h/pN?S="+S+(1a.T?"&T="+1a.T:"");j.I.1z(F,k(v){D&&D.1b(E,v)})};1k.A.mc=k(D){l o=j;if(1a.1W&&j.1X!=E&&j.1X>0){if(!j.9t||j.9t&&(j.fG=="pz"&&j.1X<j.fH||j.fG=="8u"&&j.1X>j.fH)){j.cT(k(v){if(v.1H){o.9t=1g;o.fG=v.b5;o.fH=v.q0||v.iY==-1?o.1X:v.iY;o.iX=o.1X;if(o.1h&&o.1h.6T)o.1h.6T.1b(E,v);D&&D.1b(E,v)}})}}};1k.A.cT=k(D){if(!1a.1W){N};if(j.1X==E||bJ(j.1X)){N};l 7U=(j.4s&&j.4s!=1a.1W?j.4s:"");l 7J="";if(7U&&!j.7J){7J="y";j.7J=1g};l 7v=(j.7v?"y":"");l a=[j.S,1a.1W,j.1X,1v(j.6Y),1v(j.1m.1K),7U,7J,7v];l v=6h.6Z(j.I.fN("pQ",a.oG("|")));l F="Y://wx.zxs.Z/5l/cT?v="+v+(j.4m?"&4m="+j.4m:"");l o=j;j.I.1z(F,k(v){if(v.1H){o.I.4O(v);D&&D.1b(E,v)}L{o.I.4O("p9")}})};1k.A.3q=k(){if(j.cl)N;1r.1x("U 3q");j.cl=1g;if(j.9K){if(j.8l){l 6U=26 4K()-j.i3;l 4v=pa-6U;if(4v<10)4v=10;l o=j;1Q(k(){o.dc()},4v)}L{j.dc()}}};1k.A.6W=k(v){1r.1x("U 6W: "+2N.3o(v));if(j.9K){j.pk.6W(v)}};1k.A.39=k(v){1r.1x("U f1: "+2N.3o(v));if(j.9K){j.pk.39(v)}};1k.A.i2=k(D){if(!1a.T)N;l o=j;j.I.1z("Y://pp.zxs.Z/im/pr?T="+1a.T,k(v){if(v&&v.1I){o.1u=v.1I;28(l i=0;i<v.2a.1l;i++){l 1N=v.2a[i];if(1N.1I==v.1I){o.1N=1N;W}};D&&D.1b(E)}})};1k.A.h6=k(){if(!1a.T||!j.1u)N;l o=j;l F="Y://pp.zxs.Z/im/pf?T="+1a.T+"&1I="+j.1u+"&r="+2z.2J();j.I.1z(F,k(v){l cw=v.5i;if(cw!=o.bW){o.2O("ai",cw);if(o.bW!=-1){o.2O("pn")};o.bW=cw};o.gW()})};1k.A.gW=k(){if(j.h4){8V(j.h4)};l o=j;j.h4=1Q(k(){o.h6()},o.h0*3K)};1k.A.58=k(fM){j.h0=fM||(j.I.8t()?5:15);j.gW()};1k.A.aq=k(1u,D){l F="Y://pp.zxs.Z/im/qD?1I="+1u;j.I.1z(F,k(v){if(v&&v.1I){D&&D.1b(E,v)}})};1k.A.oE=k(1q,6K,P,D){if(!1a.T)N;l F="Y://pp.zxs.Z/im/qy?T="+1a.T+"&1q="+1q+(6K?"&6K="+6K:"")+"&X="+1v(2N.3o(P));j.I.1z(F,k(v){D&&D.1b(E,v)})};1k.A.qk=k(D){if(!j.U)N;l 6K=E;6K=j.U.oF;l P={S:j.S,5k:j.U.oF,6O:j.6O,c7:j.U.qL,1p:j.1m.1K};j.oE(3,6K,P,D)};1k.A.oU=k(9h,X,D){if(!1a.T)N;l F="Y://pp.zxs.Z/im/qT?T="+1a.T+"&oP="+9h+"&X="+1v(X);j.I.1z(F,k(v){D&&D.1b(E,v)})};1k.A.pe=k(D){l o=j;j.I.1z("Y://pp.zxs.Z/im/ob",k(v){o.ui.o5(v,k(U){D&&D.1b(E,U)})})};1k.A.p0=k(2v,S,D){l F="Y://wx.zxs.Z/oZ/p7?2v="+2v+"&S="+S;j.I.1z(F,k(v){if(v.id){D&&D.1b(E,v)}})};1k.A.jP=k(D){if(!1a.T)N;l F="Y://wx.zxs.Z/37/p8?T="+1a.T;l o=j;j.I.1z(F,k(v){if(v&&v.37!=8R){o.37=v.37;D&&D.1b(E,v.37)}})};1k.A.8n=k(H,D){j.I.1z("Y://wx.zxs.Z/45/le?H="+H,k(v){D&&D.1b(E,v)})};1k.A.pE=k(1F){l 4P=j.1B+"/2o/3B.1h.1L?2y="+1v(1F);R.19="Y://wx.zxs.Z/45/o9?4P="+1v(4P)+(j.H?"&H="+j.H:"")};1k.A.og=k(1F){l 4P=j.1B+"/2o/3B.1h.1L?2y="+1v(1F);R.19="Y://wx.zxs.Z/45/o9?4P="+1v(4P)+(1a.T?"&T="+1a.T:"")+(j.H?"&H="+j.H:"")};1k.A.5b=k(G){l 3t={1H:E,1F:19.2I,2d:E};G=j.I.36(3t,G);if(j.2c){G.1C="5b";if(j.2c.1R){G.3x="1y"}L{G.3x="6z"}}L{G.1C="33";G.3x="6z"};j.ui.5C(G)};1k.A.is=k(1R,2b,D){l o=j;l F="Y://wx.zxs.Z/1h/33?1R="+1R+"&2b="+6h.6Z(2b);j.I.1z(F,k(v){if(!v){1P("bh");N};if(v.2X){2L(v.2X){18 sd:if(!o.al(1R,2b))N;D&&D.1b(E,"4z");W;18 sa:1P("sc");W;48:1P("bh");W}}L{D&&D.1b(E,"33",v)}})};1k.A.iA=k(1R,2b,3E,D){if(!3E){1P("9x");N};l o=j;j.fZ(1R,3E,k(1y){if(1y){l F="Y://wx.zxs.Z/1h/4z?1R="+1R+"&2b="+6h.6Z(2b)+(o.H?"&H="+o.H:"");o.I.1z(F,k(v){if(!v){1P("bz");N};if(v.2X){1P("bz："+v.4f+"（"+v.2X+"）")}L{v.1R=1R;D&&D.1b(E,v)}})}L{1P("dn")}})};1k.A.al=k(1R,2b){if(!1R||bJ(1R)||1R.1l!=11){1P("si");N 1f};if(2b.1l<6){1P("md");N 1f};N 1g};1k.A.j0=k(1R,2b,D){if(!j.al(1R,2b))N;j.I.1z("Y://wx.zxs.Z/1h/oW?1R="+1R,k(v){if(v&&v.oO==1){1P("s4，s8。")}L{D&&D.1b(E)}})};1k.A.iz=k(1R,2b,3E,D){if(!3E){1P("9x");N};l o=j;j.fZ(1R,3E,k(1y){if(1y){l F="Y://wx.zxs.Z/1h/rZ?1R="+1R+"&2b="+6h.6Z(2b)+"&T="+1a.T;o.I.1z(F,k(v){if(v&&v.2v){if(o.2c)o.2c.1R=1R;v.T=1a.T;v.1R=1R;D&&D.1b(E,v)}L if(v&&v.2X){1P("oT："+v.4f+"（"+v.2X+"）")}L{1P("oT")}})}L{1P("dn")}})};1k.A.iq=k(1R,2b,D){if(!j.al(1R,2b))N;j.I.1z("Y://wx.zxs.Z/1h/oW?1R="+1R,k(v){if(v&&v.oO==1){D&&D.1b(E,1g)}L{if(gy("ql，r4？")){D&&D.1b(E,1f)}}})};1k.A.iw=k(1R,2b,3E,D){if(!3E){1P("9x");N};l o=j;j.72(1R,2b,3E,k(v){if(v.1H){D&&D.1b(E)}L if(v.3d){1P(v.3d)}L{1P("qY")}})};1k.A.ix=k(1R,X,D){j.I.1z("Y://pp.zxs.Z/hf/gN?1C=2i&fR="+1R+"&id="+j.I.6C()+"&X="+X,k(v){D&&D.1b(E,v)})};1k.A.fZ=k(1R,3E,D){j.I.1z("Y://pp.zxs.Z/hf/gN?1C=3y&fR="+1R+"&3E="+3E,k(v){l 1y=(v&&v.pT==0)?1g:1f;D&&D.1b(E,1y)})};1k.A.72=k(1R,2b,3E,D){j.I.1z("Y://pp.zxs.Z/hf/pP?1R="+1R+"&2b="+6h.6Z(2b)+"&3E="+3E,k(v){D&&D.1b(E,v)})};1k.A.d8=k(O){O.1T="Y://wx.zxs.Z/eW/q2?id="+j.I.6C()};1k.A.iP=k(X,D){j.I.1z("Y://wx.zxs.Z/eW/gN?id="+j.I.6C()+"&X="+X,k(v){D&&D.1b(E,v)})};1k.A.pY=k(G){l 3t={1H:E,1F:19.2I,2d:E};G=j.I.36(3t,G);G.1C="33";G.3x="6z";j.ui.cb(G)};1k.A.kW=k(3T,2b){if(!3T||3T.1l<6){1P("pB，pK、sN，sv。");N 1f};if(2b.1l<6){1P("md");N 1f};N 1g};1k.A.fi=k(3T,2b,D){l o=j;l F="Y://wx.zxs.Z/1h/33?3T="+3T+"&2b="+6h.6Z(2b);j.I.1z(F,k(v){if(!v){1P("bh");N};if(v.2X){1P("bh："+v.4f+"（"+v.2X+"）")}L{D&&D.1b(E,v)}})};1k.A.kS=k(3T,2b,D){l o=j;l F="Y://wx.zxs.Z/1h/4z?3T="+1v(3T)+"&2b="+6h.6Z(2b)+(j.H?"&H="+j.H:"");j.I.1z(F,k(v){if(!v){1P("bz");N};if(v.2X){1P("bz："+v.4f+"（"+v.2X+"）")}L{D&&D.1b(E,v)}})};1k.A.9A=k(){N(j.I.34("me")=="y"||1a.me=="y"||j.1C=="4O"||1a.1W=="rv=="||1a.1W=="s1=="||1a.1W=="s3=="||1a.1W=="s6=="||1a.1W=="rS=="||1a.1W=="rQ=="||1a.1W=="rY=="||1a.1W=="sq==")};31=k(q){j.q=q;j.7e=1g};31.A.3y=k(G){l 3t={7H:"id",1C:E,1F:19.2I,1H:E,3b:E};G=j.q.I.36(3t,G);if(j.q.I.8k()=="/3M.1L"||j.q.I.8k().3v(/\\/[^\\/]+\\/U\\.1L/i)){j.7e=1f};if(j.mu()){G.3b&&G.3b.3Y(E);N};if(j.lM(G,1)){G.1H&&G.1H.3Y(E);N};if(1a.T){j.mv()};if(j.q.I.34("2o")=="3y"){if(!j.fL()){G.3b&&G.3b.3Y(E);N}};if(G.7H=="id"&&!1a.2q){j.b2(G)}L if(G.7H=="2c"&&!1a.T){j.b2(G)}L{j.mB(G)}};31.A.mB=k(G){l F="Y://wx.zxs.Z/1h/3y";if(G.7H=="id")F+="?4q="+1a.2q;if(G.7H=="2c")F+="?T="+1a.T;l o=j;j.q.I.1z(F,k(v){l bw=1f;if(v&&v.1H){if(G.1C=="4z"&&!v.T){bw=1f}L{bw=1g}};if(bw){o.gZ(v);G.1H&&G.1H.3Y(E)}L{if(o.7e){l F=q.1B+"/2o/3B.1h.1L?e5=y&2y="+1v(G.1F);R.19.2B(F)}L{o.5m();o.b2(G)}}})};31.A.mu=k(){if(3p.2X&&3p.4f){j.q.I.4O("2X = "+3p.2X+", 4f = "+3p.4f);3p.6c("2X");3p.6c("4f");j.5m();N 1g};N 1f};31.A.mv=k(){if(j.q.I.29()=="zxs"||j.q.I.29()=="2K"){l T=j.q.1h.6V();if(T!=E&&1a.T!=T){j.5m()}}};31.A.se=k(v){N 1g};31.A.gZ=k(v){if(v.2q)1a.2q=v.2q;if(v.T)1a.T=v.T;if(v.1W)1a.1W=v.1W;if(v.3O)1a.1W=v.3O;if(v.5L)1a.5L=v.5L;if(v.4w)1a.4w=v.4w};31.A.5m=k(){1a.6c("2q");1a.6c("T");1a.6c("1W");1a.6c("5L");1a.6c("4w")};31.A.fL=k(){l 2X=j.q.I.34("2X");l 4f=j.q.I.34("4f");l 2q=j.q.I.34("4q");l T=j.q.I.34("T");l 1W=j.q.I.34("1W");l 5L=j.q.I.34("5L");if(2X!=E||4f!=E){j.q.I.4O("fL: 2X = "+2X+", 4f = "+4f);j.5m();N 1f}L{if(2q)1a.2q=2q;if(T)1a.T=T;if(1W)1a.1W=1W;if(5L)1a.5L=5L;N 1g}};31.A.rs=k(G,v){};31.A.lM=k(G,fM){N 1f};31.A.b2=k(G){2L(G.7H){18"id":if(j.q.I.29()=="wx"){if(j.q.6N){j.g9(G.1F,"gc")}L{j.gh(G.1C,G.1F)}}L if(j.q.I.29()=="zxs"||j.q.I.29()=="2K"){j.q.1h.33(G.1F)}L if(j.q.I.29()=="5R"){j.q.1h.33(G)}L if(G.1C=="4z"){j.gL(G.1F)}L{G.3b&&G.3b.3Y(E)};W;18"2c":if(j.q.I.29()=="wx"){if(j.q.6N){j.g9(G.1F,"gc")}L{if(!1a.2q){j.gh(G.1C,G.1F)}L{j.lC(G.1F)}}}L if(j.q.I.29()=="zxs"||j.q.I.29()=="2K"){j.q.1h.33(G.1F)}L if(j.q.I.29()=="5R"){j.q.1h.33(G)}L if(G.1C=="4z"){j.gL(G.1F)}L{j.na(G.1F)};W}};31.A.lN=k(D){if(!1a.T){D&&D.1b(E,E)}L{l o=j;l F="Y://wx.zxs.Z/1h/8A?T="+1a.T;j.q.I.1z(F,k(v){if(v.2X){o.q.2c=E;D&&D.1b(E,E)}L{o.q.2c=o.q.I.36(o.q.2c,v);D&&D.1b(E,v)}})}};31.A.ri=k(D){if(!1a.T){D&&D.1b(E,E)}L{l F="Y://wx.zxs.Z/1h/rj?T="+1a.T;j.q.I.1z(F,k(v){D&&D.1b(E,v)})}};31.A.rm=k(1q,D){if(!1a.T){D&&D.1b(E,E)}L{l F="Y://wx.zxs.Z/1h/rH?1q="+1q+"&T="+1a.T;j.q.I.1z(F,k(v){D&&D.1b(E,v)})}};31.A.aY=k(){l id=j.q.4s;l D=E;2L(1G.1l){18 1:if(2M 1G[0]=="6u")id=1G[0];if(2M 1G[0]=="k")D=1G[0];W;18 2:id=1G[0];D=1G[1];W};if(id){l o=j;l F="Y://wx.zxs.Z/1h/8A?id="+id;j.q.I.1z(F,k(v){l 2c=E;if(v.2X){o.q.I.4O(v.4f)}L{2c=v;o.q.lP=2c};D&&D.1b(E,2c)})}L{D&&D.1b(E,E)}};31.A.rM=k(D){if(1a.4w){D&&D.1b(E,1a.4w);N};l 3B;if(j.q.6N){3B=j.q.1B+"/2o/45.lE.1L?2y="+1v(19.2I)+"&H="+j.q.H}L{3B=j.q.1B+"/2o/lE.1L?2y="+1v(19.2I)};l F=j.q.8F()+"/2o/3y?4P="+1v(3B);R.19.2B(F)};31.A.gh=k(1C,1F){if(j.7e){l 3B=j.q.1B+"/2o/3B.1h.1L?2y="+1v(1F)+(j.q.H?"&H="+j.q.H:"")}L{l 3B=j.q.I.2r(1F,"2o","3y")};l F="Y://wx.zxs.Z/2o/3y?4P="+1v(3B)+(1C?"&1C="+1C:"")+"&8o="+j.q.I.6C()+(j.q.H?"&H="+j.q.H:"");R.19.2B(F)};31.A.lC=k(1F,3b){if(j.7e){l 1H=j.q.1B+"/2o/3B.1h.1L?2y="+1v(1F)+(j.q.H?"&H="+j.q.H:"")}L{l 1H=j.q.I.2r(1F,"2o","3y")};3b=3b||j.q.1B;l F="Y://wx.zxs.Z/2o/8A?1H="+1v(1H)+"&3b="+1v(3b)+(j.q.H?"&H="+j.q.H:"");R.19.2B(F)};31.A.g9=k(2y,aR){l 1F;2L(aR){18"gc":l 4P=j.q.1B+"/2o/45.1L?2y="+1v(2y)+(j.q.H?"&H="+j.q.H:"");1F=j.q.8F()+"/2o/9P?4P="+1v(4P)+(j.q.H?"&H="+j.q.H:"");W;18"tu":l 1H=j.q.1B+"/2o/45.1L?2y="+1v(2y)+(j.q.H?"&H="+j.q.H:"");l 3b=j.q.1B;1F=j.q.8F()+"/2o/sW?1H="+1v(1H)+"&3b="+1v(3b)+(j.q.H?"&H="+j.q.H:"");W};l F="cu://45.sL.qq.Z/gs/sP/sR"+"?kF="+j.q.m3()+"&p5="+1v(1F)+"&pS=3E"+"&aR="+aR+"&q4=pH#q3";R.19.2B(F)};31.A.pw=k(v,D){l F="Y://wx.zxs.Z/45/qX?4q="+v.2q+"&H="+v.H;l o=j;j.q.I.1z(F,k(v){D&&D.1b(E,v)})};31.A.gL=k(1F){l o=j;1F=1F||j.q.1B;l F="Y://wx.zxs.Z/1h/4z?8o="+j.q.I.6C()+(j.q.H?"&H="+j.q.H:"");j.q.I.1z(F,k(v){if(o.7e){1F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(1F)+(o.q.H?"&H="+o.q.H:"")+(v.T?"&T="+v.T:"")+(v.2q?"&4q="+v.2q:"")+(v.3O?"&1W="+v.3O:"")}L{1F=o.q.I.2r(1F,"2o","3y");if(v.T)1F=o.q.I.2r(1F,"T",v.T);if(v.2q)1F=o.q.I.2r(1F,"4q",v.2q);if(v.3O)1F=o.q.I.2r(1F,"1W",v.3O)};R.19.2B(1F)})};31.A.na=k(1F){l F=j.q.1B+"/1h/33.1L?qv="+1v(1F);R.19=F};31.A.e5=k(1F){l F=j.q.1B+"/1h/e5.1L?4P="+1v(1F);R.19=F};31.A.pq=k(D){l id=j.q.4s;if(id&&1a.2q&&id!=1a.1W){l F="Y://wx.zxs.Z/2o/23?4q="+1a.2q+"&id="+id;l o=j;j.q.I.1z(F,k(v){l 1y=0;if(v.3d){o.q.I.4O(v);1y=-1}L{1y=v.p6};D&&D.1b(E,1y)})}L{D&&D.1b(E,-1)}};3Q=k(q){j.q=q;j.3k=E;j.2Z=E;j.3l=E;j.6q=E;j.m0()};3Q.A.m0=k(){l o=j;l dl=(2M R.6q==="mI"&&2M R.pC==="4F");l aI=k(){if(dl){o.6q=(R.6q==90||R.6q==-90)?"m6":"m5"}L{o.6q=(R.3F>R.3h)?"m6":"m5"};o.q.2O("bT",o.6q)};aI();if(dl){R.V("pV",aI,1f)}L{R.V("pU",aI,1f)}};3Q.A.lF=k(){if(!j.3k)j.3k=26 b3(j.q);if(!j.2Z)j.2Z=26 7G(j.q)};3Q.A.fa=k(){if(!j.3l)j.3l=26 68(j.q)};b3=k(q){j.q=q;l B=C.K("B");B.id="75";B.Q="75";l o=j;l 4x=k(e){o.53();o.q.ui.2Z.5x();e.1V()};B.V("1Y",4x);B.V("3L",4x);C.1o("1w")[0].J(B);l 5U=C.K("B");5U.Q="t0";5U.1i.2j="35";B.J(5U);j.q.V("ai",k(v){if(v>0){5U.1c=v;5U.1i.2j=""}L{5U.1c="";5U.1i.2j="35"}})};b3.A.5x=k(){l B=C.1j("75");if(B)B.1i.3w=j.q.I.3H(4)};b3.A.53=k(){l B=C.1j("75");if(B)B.1i.3w=j.q.I.3H(-20)};7G=k(q){j.q=q;j.6k=1f;j.2m()};7G.A.2m=k(){l B=C.K("B");B.id="7E";B.Q="7E";C.1o("1w")[0].J(B);l my=C.K("B");my.Q="sz";B.J(my);l 3s=C.K("O");3s.1T="Y://U.zxs.Z/48.2f";my.J(3s);l 2Q=C.K("B");my.J(2Q);l o=j;j.q.V("7I",k(){3s.1T=o.q.I.5V(o.q.2c.3n);2Q.1c=o.q.2c.2Q});l ul=C.K("ul");l 66;28(l i=1;i<=6;i++){l li=C.K("li");l O=C.K("O");l a=C.K("a");l 23=j.q.1B;2L(i){18 1:a.1c="sy";O.1T="Y://U.zxs.Z/O/2Z/sB.2f";23=j.q.1B+"/1h/my.1L?r="+2z.2J();W;18 2:a.1c="su";O.1T="Y://U.zxs.Z/O/2Z/st.2f";23=j.q.1B+"/1h/3D.1L?r="+2z.2J();66=C.K("B");66.1i.2j="35";li.J(66);W;18 3:a.1c="sw";O.1T="Y://U.zxs.Z/O/2Z/qP.2f";23=j.q.1B+"/1h/4I.1L?r="+2z.2J();W;18 4:a.1c="sI";O.1T="Y://U.zxs.Z/O/2Z/sJ.2f";23=j.q.1B+"/1h/sG.1L?r="+2z.2J();W;18 5:a.1c="sD";O.1T="Y://U.zxs.Z/O/2Z/sC.2f";23=j.q.1B+"/3M.1L?S=pk&r="+2z.2J();W;18 6:a.1c="sF";O.1T="Y://U.zxs.Z/O/2Z/ts.2f";23=j.q.1B+"/1h/5l.1L?r="+2z.2J();W};li.J(O);li.J(a);(k(23){l 7z=k(e){R.19=23;e.1V()};li.V("1Y",7z);li.V("3L",7z)})(23);ul.J(li)};B.J(ul);j.q.V("ai",k(v){if(v>0){66.1c=v;66.1i.2j=""}L{66.1c="";66.1i.2j="35"}});l gz=C.K("O");gz.1T="Y://U.zxs.Z/O/tn.2f";gz.Q="tm";B.J(gz);l f6=k(e){R.19=o.q.1B+"/tD.1L?r="+2z.2J();e.1V()};gz.V("1Y",f6);gz.V("3L",f6);j.q.V("7I",k(){if(o.q.2c.9E)gz.1i.2j="35"})};7G.A.t9=k(){if(1a.T){l F="Y://wx.zxs.Z/37/tb?1S=0&T="+1a.T;j.q.I.1z(F,k(v){if(v&&v.lB>0){l B=C.K("B");B.Q="7J";B.1c="+"+v.lB;C.1j("tk").J(B)}})}};7G.A.5x=k(){l B=C.1j("7E");B.Q="7E tc";l 1d=C.K("B");1d.id="ff";1d.Q="ff";C.1o("1w")[0].J(1d);l o=j;l 4x=k(e){o.53();o.q.ui.3k.5x();e.1V()};1d.V("1Y",4x);1d.V("3L",4x);j.6k=1g};7G.A.53=k(){l B=C.1j("7E");B.Q="7E";l 1d=C.1j("ff");if(1d)C.1o("1w")[0].1Z(1d);j.6k=1f};68=k(q){j.q=q;j.2m();j.b7=1f;j.b6=1f;j.fc=0;j.2Z=26 59(q)};68.A.2m=k(){l B=C.K("B");B.id="9i";B.Q="9i"+(R.3F>R.3h?" pc":"");C.1o("1w")[0].J(B);j.q.V("bT",k(){if(B)B.Q="9i"+(R.3F>R.3h?" pc":"")});l O=C.K("O");O.id="lD";B.J(O);j.9U("on");l o=j;B.V("1Y",k(e){o.fd(e)});B.V("tf",k(e){o.fb(e)});B.V("rG",k(e){o.eY(e)});B.V("3L",k(e){o.fd(e)});B.V("ry",k(e){o.fb(e)});B.V("rB",k(e){o.eY(e)})};68.A.9U=k(lJ){l O=C.1j("lD");O.1T="Y://U.zxs.Z/O/rN"+(j.q.aZ?"rP":"")+lJ+".2f"};68.A.fd=k(e){j.b7=1g;j.fc=26 4K().5t();e&&e.1V()};68.A.fb=k(e){if(!j.b7)N;l 8D=26 4K().5t();if(8D-j.fc<4l)N;j.b6=1g;l B=C.1j("9i");l aW=e.aU?e.aU[0].aW:(e.aW||e.rK);l b1=e.aU?e.aU[0].b1:(e.b1||e.rl);l 3w=aW-B.fm/2;l 2D=b1-B.5d/2;l 7W=R.3F-B.fm;l 7O=R.3h-B.5d;if(3w<0)3w=0;if(3w>7W)3w=7W;if(2D<0)2D=0;if(2D>7O)2D=7O;B.1i.3w=3w+"px";B.1i.2D=2D+"px";if(j.2Z.6k)j.2Z.fl();e&&e.1V()};68.A.eY=k(e){if(!j.b6)j.mz();j.b6=1f;j.b7=1f;e&&e.1V()};68.A.mz=k(){if(!j.2Z.6k){j.2Z.5x()}L{j.2Z.53()}};59=k(q){j.q=q;j.6k=1f;j.2m();j.mG()};59.A.2m=k(){l 1d=C.K("B");1d.id="9Q";1d.Q="9Q";1d.1i.2j="35";l o=j;1d.V("1Y",k(e){o.53();e.1V()});1d.V("3L",k(e){o.53();e.1V()});C.1o("1w")[0].J(1d);l B=C.K("B");B.id="7m";B.Q="7m"+(R.3F>R.3h?" pc":"");B.1i.2j="35";C.1o("1w")[0].J(B);j.q.V("bT",k(){if(B)B.Q="7m"+(R.3F>R.3h?" pc":"")});l bg=C.K("B");bg.Q="sj";B.J(bg);l ul=C.K("ul");ul.id="fA";B.J(ul)};59.A.mG=k(){l o=j;j.fC([{3e:"Y://U.zxs.Z/O/sk.2f",1p:"rU",1O:k(){o.q.ui.mh()}},{3e:"Y://U.zxs.Z/O/jR.2f",1p:"gU",1O:k(){R.19=o.q.aA()}}])};59.A.fC=k(2a){28(l i=0;i<2a.1l;i++){j.fD(2a[i])}};59.A.fD=k(v){l ul=C.1j("fA");if(!ul)N;l li=C.K("li");ul.J(li);l O=C.K("O");O.1T=v.3e;li.J(O);l 3C=C.K("3C");3C.1c=v.1p;li.J(3C);l o=j;li.V("1Y",k(e){o.7z(e,v.1O)});li.V("3L",k(e){o.7z(e,v.1O)})};59.A.5m=k(){l ul=C.1j("fA");if(ul)ul.1c=""};59.A.7z=k(e,D){j.53();D&&D.1b(E);e&&e.1V()};59.A.5x=k(){j.q.ui.3l.9U("bE");l 1d=C.1j("9Q");1d.1i.2j="";l B=C.1j("7m");B.1i.2j="";j.fl();j.6k=1g};59.A.fl=k(){l B=C.1j("7m");l 5v=j.q.I.6B(3);l 3l=C.1j("9i");l 3w=3l.rA;l 2D=3l.eu+3l.5d+5v;l 7W=R.3F-B.fm-5v;l 7O=R.3h-B.5d-5v;if(3w<5v)3w=5v;if(3w>7W)3w=7W;if(2D<5v)2D=5v;if(2D>7O)2D=7O;if(2D<3l.eu+3l.5d){2D=3l.eu-B.5d-5v;};B.1i.3w=3w+"px";B.1i.2D=2D+"px"};59.A.53=k(){j.q.ui.3l.9U("on");C.1j("7m").1i.2j="35";C.1j("9Q").1i.2j="35";j.6k=1f};3Q.A.4t=k(D){l B=C.K("B");B.id="5T";B.Q="5T";C.1o("1w")[0].J(B);l fn=k(e){D&&D.1b(E);e.1V()};B.V("1Y",fn);B.V("3L",fn)};3Q.A.5J=k(){l B=C.1j("5T");if(B)B.2e.1Z(B)};3Q.A.mh=k(G){l 3t={1K:"qI",56:j.q.5c?"Y://U.zxs.Z/O/r9.ek":"Y://U.zxs.Z/O/56.ek"};G=j.q.I.36(3t,G);j.eH();j.4t();l o=j;l B=C.K("B");B.id="eG";B.Q="eG";C.1o("1w")[0].J(B);l 3J=C.K("B");3J.Q="rL";3J.1c=G.1K;B.J(3J);l 2d=C.K("O");2d.1T="Y://U.zxs.Z/O/2n.2f";3J.J(2d);l 6i=C.K("B");6i.Q="rn";B.J(6i);l 56=C.K("O");56.Q="so";56.1T=G.56;6i.J(56);l 2n=k(e){o.eH();e.1V()};2d.V("1Y",2n);2d.V("3L",2n)};3Q.A.eH=k(){l B=C.1j("eG");if(B)B.2e.1Z(B);j.5J()};3Q.A.o5=k(2a,D){j.cO();j.4t();l 1t=C.K("B");1t.id="eE";1t.Q="eE";C.1o("1w")[0].J(1t);l p=C.K("p");p.1c="rh";1t.J(p);l ul=C.K("ul");1t.J(ul);j.eC(ul,2a,D);l o=j;l B=C.K("B");1t.J(B);l 5I=C.K("a");5I.1c="rI";5I.V("1Y",k(e){o.q.I.1z("Y://pp.zxs.Z/im/ob?bO=1",k(v){o.eC(ul,v,D)});e.1V()});B.J(5I);l 5M=C.K("a");5M.1c="cU";5M.V("1Y",k(e){o.cO();e.1V()});B.J(5M)};3Q.A.eC=k(ul,2a,D){ul.1c="";l o=j;28(l i=0;i<2a.1l;i++){(k(U){l li=C.K("li");ul.J(li);l O=C.K("O");O.1T=U.c7;li.J(O);l 3C=C.K("3C");3C.1c=U.5k;li.J(3C);li.V("1Y",k(e){o.cO();e.1V();D&&D.1b(E,U)})})(2a[i])}};3Q.A.cO=k(){l 1t=C.1j("eE");if(1t)1t.2e.1Z(1t);j.5J()};3Q.A.5C=k(G){j.7i();j.4t();l o=j;l 4p=R.3F>R.3h;l 1t=C.K("B");1t.id="c4";1t.Q="c4"+(4p?" pc":"");C.1o("1w")[0].J(1t);l 2F=C.K("B");2F.Q="hR";1t.J(2F);l 2n=C.K("O");2n.Q="hN";2n.1T="Y://U.zxs.Z/O/hE.2f";1t.J(2n);2L(G.3x){18"6z":l 63=C.K("4g");63.Q="cL";63.69="sQ";63.2E=G.1R||"";2F.J(63);l 4H=C.K("4g");4H.1q="2b";4H.Q="cL";4H.69=G.1C=="72"?"pd":"hQ";2F.J(4H);if(G.1C=="33"){l 7T=C.K("a");7T.Q="hW";7T.1c="pI？pZ";7T.2I="cc:eZ(0)";7T.V("1O",k(){if(63.2E)G.1R=63.2E;G.1C="72";o.5C(G)});2F.J(7T)};l 77=C.K("B");77.Q="cm";2L(G.1C){18"5b":77.1c="qR";W;18"72":77.1c="qU";W;48:77.1c="qg/qj";W};2F.J(77);77.V("1O",k(){G.1R=63.2E;G.2b=4H.2E;2L(G.1C){18"5b":o.q.j0(G.1R,G.2b,k(){G.3x="80";o.5C(G)});W;18"72":o.q.iq(G.1R,G.2b,k(ir){if(ir){G.3x="80";o.5C(G)}L{G.1C="4z";G.3x="80";o.5C(G)}});W;48:o.q.is(G.1R,G.2b,k(1C,v){G.1C=1C;if(1C=="33"){v.1R=G.1R;G.1H&&G.1H.1b(E,v);l F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(G.1F)+(v.2q?"&4q="+v.2q:"")+(v.T?"&T="+v.T:"")+(v.3O?"&1W="+v.3O:"")+(o.q.H?"&H="+o.q.H:"");R.19.2B(F)}L{G.3x="80";o.5C(G)}});W}});2n.V("1O",k(){o.7i();G.2d&&G.2d.1b(E)});W;18"80":l p=C.K("p");p.1c=(G.1C=="4z"?"qE！":"9x。")+"<br/>qz "+G.1R;2F.J(p);l B=C.K("B");B.Q="qb";2F.J(B);l 6p=C.K("4g");6p.Q="q9";6p.69="qd";B.J(6p);l 6E=C.K("B");6E.Q="qc";6E.1c="i4";B.J(6E);l 4C=C.K("B");4C.Q="qf";2F.J(4C);l 8K=C.K("B");8K.Q="qi";8K.1c=G.1C=="4z"?"qh":"r0";4C.J(8K);8K.V("1O",k(){l 1H=k(v){o.7i();G.1H&&G.1H.1b(E,v)};2L(G.1C){18"5b":o.q.iz(G.1R,G.2b,6p.2E,1H);W;18"4z":o.q.iA(G.1R,G.2b,6p.2E,k(v){1H(v);l F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(G.1F)+(v.2q?"&4q="+v.2q:"")+(v.T?"&T="+v.T:"")+(v.3O?"&1W="+v.3O:"")+(o.q.H?"&H="+o.q.H:"");R.19.2B(F)});W;18"72":o.q.iw(G.1R,G.2b,6p.2E,k(){1P("qH！qN。");G.1C="33";G.3x="6z";o.5C(G)});W}});l 9q=C.K("B");9q.Q="qS";9q.1c="cU";4C.J(9q);l cW=k(X){o.9J=1g;o.q.ix(G.1R,X,k(v){if(v.3d){if(v.3d=="3d eW"){o.95(2F,1f,k(X){cW(X)})}L{1P("i1")};o.9J=1f}L{if(v.3E==0){o.q.I.ox({2S:"dZ",2R:60,c6:k(2R){6E.1c=2R+"pi"},6M:k(){6E.1c="i4";o.9J=1f}})}L{1P("i1");o.9J=1f}}})};j.95(2F,1f,k(X){cW(X)});6E.V("1O",k(){if(o.9J)N;o.95(2F,1f,k(X){cW(X)})});9q.V("1O",k(){o.q.I.hg("dZ");G.3x="6z";o.5C(G)});2n.V("1O",k(){o.q.I.hg("dZ");o.7i();G.2d&&G.2d.1b(E)});W;18"1y":l 2k=C.K("B");2k.Q="kP";2k.1c="pO";2F.J(2k);l 6P=C.K("B");6P.Q="kr";6P.1c=j.q.2c.1R;2F.J(6P);l 1M=C.K("B");1M.Q="cm";1M.1c="ge";2F.J(1M);l 1H=k(){o.7i();l v={T:1a.T,1R:o.q.2c.1R};G.1H&&G.1H.1b(E,v)};1M.V("1O",1H);2n.V("1O",1H);W}};3Q.A.7i=k(){l 1t=C.1j("c4");if(1t)1t.2e.1Z(1t);j.5J()};3Q.A.95=k(2F,3y,D){l o=j;l 4B=C.K("B");4B.id="du";4B.Q="du";2F.J(4B);l 1K=C.K("3C");1K.Q="sM";1K.1c="9x：";4B.J(1K);l a=C.K("a");a.Q="ty";a.1c="ta";a.2I="cc:eZ(0)";4B.J(a);l O=C.K("O");O.Q="t6";4B.J(O);l 5h=C.K("4g");5h.Q="rk";5h.69="rg";4B.J(5h);l 1M=C.K("3C");1M.Q="sg";1M.1c="ge";4B.J(1M);o.q.d8(O);a.V("1O",k(){o.q.d8(O)});1M.V("1O",k(){l X=5h.2E;if(X=="")N;if(3y){o.q.iP(X,k(v){if(v.1y==1){o.e6();D&&D.1b(E,v)}L{1P("dn");5h.2E="";o.q.d8(O)}})}L{o.e6();D&&D.1b(E,X)}})};3Q.A.e6=k(){l 4B=C.1j("du");if(4B)4B.2e.1Z(4B)};3Q.A.cb=k(G){j.bK();j.4t();l o=j;l 4p=R.3F>R.3h;l 1t=C.K("B");1t.id="kA";1t.Q="c4"+(4p?" pc":"");C.1o("1w")[0].J(1t);l 2F=C.K("B");2F.Q="hR";1t.J(2F);l 2n=C.K("O");2n.Q="hN";2n.1T="Y://U.zxs.Z/O/hE.2f";1t.J(2n);2L(G.3x){18"6z":l 8g=C.K("4g");8g.Q="cL";8g.69="qG";8g.2E=G.3T||"";2F.J(8g);l 4H=C.K("4g");4H.1q="2b";4H.Q="cL";4H.69=G.1C=="33"?"hQ":"q1";2F.J(4H);if(G.1C=="33"){l 8b=C.K("a");8b.Q="hW";8b.1c="pD zxs pG？pA。";8b.2I="cc:eZ(0)";8b.V("1O",k(){G.1C="4z";o.cb(G)});2F.J(8b)};l 97=C.K("B");97.Q="cm";97.1c=G.1C=="33"?"rr":"sh";2F.J(97);97.V("1O",k(){G.3T=8g.2E;G.2b=4H.2E;2L(G.1C){18"33":if(!G.3T||!G.2b)N;o.q.fi(G.3T,G.2b,k(v){l F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(G.1F)+(v.2q?"&4q="+v.2q:"")+(v.T?"&T="+v.T:"")+(v.3O?"&1W="+v.3O:"")+(o.q.H?"&H="+o.q.H:"");R.19.2B(F)});W;18"4z":if(!o.q.kW(G.3T,G.2b))N;o.95(2F,1g,k(v){o.q.kS(G.3T,G.2b,k(v){G.3x="1y";o.cb(G)})});W}});2n.V("1O",k(){o.bK();G.2d&&G.2d.1b(E)});W;18"1y":l 2k=C.K("B");2k.Q="kP";2k.1c="rW！<br/>sm。";2F.J(2k);l 5h=C.K("B");5h.Q="kr";5h.1c=G.3T;2F.J(5h);l 1M=C.K("B");1M.Q="cm";1M.1c="sb";2F.J(1M);1M.V("1O",k(){o.bK();o.q.fi(G.3T,G.2b,k(v){l F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(G.1F)+(v.2q?"&4q="+v.2q:"")+(v.T?"&T="+v.T:"")+(v.3O?"&1W="+v.3O:"")+(o.q.H?"&H="+o.q.H:"");R.19.2B(F)})});W}};3Q.A.bK=k(){l 1t=C.1j("kA");if(1t)1t.2e.1Z(1t);j.5J()};21=k(q){j.q=q;j.v=E;j.6v=j.q.I.29()=="zxs"&&j.q.1h.74("2.1");j.aV=j.6v;j.aN=j.6v;j.8q=!j.6v&&j.q.I.29()!="wx"&&j.q.I.29()!="zxs"&&!j.q.I.fQ();if(j.q.I.lH())j.8q=1g;j.9o=1f;j.ui=26 5w(q);j.2m();j.kD();};21.A.2m=k(){l o=j;R.V("3D",k(e){if(2M e.v!="4F")N;if(e.v.1C==8R)N;if(e.v.1C=="27"){if(!e.v.T){1P("rq");N};e.v.4d=0;e.v.55=0;e.v.84=e.v.3c;o.v=e.v;o.3k()}});if(j.6v){R.rt=k(1y){o.aV=(1y==1)};R.rw=k(1y){o.aN=(1y==1)};R.rx=k(1y){o.5W(1y);o.ui.6a()};R.ru=k(){o.bf();o.ui.6a()}}};21.A.kD=k(){l o=j;j.q.eq(k(){if(o.q.I.29()=="wx"&&o.q.sp&&o.q.sp.80==0)o.8q=1g})};21.A.3k=k(){l o=j;l F="Y://wx.zxs.Z/1h/ro?T="+o.v.T;j.q.I.1z(F,k(v){if(v&&v.2v){if(v.ey&&v.ey!=o.q.H){o.q.bi(v.ey)};if(o.q.sp&&o.q.sp.1q==2){}L{o.q.H=v.H||E};o.8n(o.v.H,k(v){o.v.sp=v&&v.H?v:E;o.lh()})}L{l 8a=1f;l 5q="1a.T="+1a.T+"\\n"+"1a.1W="+1a.1W+"\\n"+"q.H="+o.q.H+"\\n"+"F="+19.2I;o.q.I.9F("rJ",0,5q,k(id){8a=1g;1P("lj（ji："+id+"）")});1Q(k(){if(!8a)1P("lj")},4D)}})};21.A.lh=k(){if(j.q.5c){l F="Y://wx.zxs.Z/kg/8A?T="+j.v.T;l o=j;j.q.I.1z(F,k(v){if(v&&v.T){o.jz(v.2v,v.T)}L{o.3q()}})}L if(j.q.H=="rF"){j.ju()}L if(j.q.H=="th"){j.ag()}L if(j.q.H=="ly"){j.ab()}L if(j.q.H=="tl"){j.ac()}L if(j.q.H=="tj"){j.a1()}L if(j.q.H=="t5"){j.ao()}L if(j.q.H=="t4"){j.ah()}L if(j.q.H=="tz"){j.az()}L if(j.q.H=="tw"){j.ay()}L if(j.q.H=="tx"){j.aD()}L if(j.q.H=="tA"){j.aC()}L if(j.q.H=="tB"){j.as()}L if(j.q.H=="tC"){j.av()}L if(j.q.H=="to"){j.9M()}L if(j.q.H=="tq"){j.9S()}L if(j.q.H=="tv"){j.9L()}L if(j.q.H=="l3"){j.a6()}L if(j.q.H=="tt"){j.a8()}L if(j.q.H=="sE"){j.bj()}L if(j.q.H=="l5"){j.bc()}L if(j.q.H=="sH"){j.ba()}L if(j.q.H=="sx"){j.be()}L if(j.q.H=="sA"){j.bC()}L if(j.q.H=="sX"){j.bF()}L if(j.q.H=="sU"){j.bD()}L if(j.q.H=="sV"){j.by()}L if(j.q.H=="sY"){j.bs()}L if(j.q.H=="b9"){j.jl()}L if(j.q.sp&&j.q.sp.1q==2||j.q.H=="t1"||j.q.H=="t2"||j.q.H=="sZ"){j.bq()}L if(j.q.6N){j.jh()}L{j.3q()}};21.A.3q=k(){j.ui.jU(j.v);j.ec();j.6v&&j.q.1h.bl()};21.A.8n=k(H,D){l o=j;l F="Y://27.zxs.Z/45/le?H="+H;j.q.I.1z(F,k(v){D&&D.1b(E,v)})};21.A.ec=k(){if(j.q.5c)N;if(j.v.H=="zg"||j.v.H=="jS")N;if(j.v.H=="sO")N;if(j.q.H&&j.q.H!="zxs"&&j.q.H!="lb"&&j.q.H!="l8"&&j.q.H!="l7")N;l o=j;l F="Y://37.zxs.Z/v2/sS?T="+j.v.T+"&H="+j.v.H+"&3c="+j.v.84;j.q.I.1z(F,k(v){v&&o.ui.kk(v)})};21.A.2u=k(D){l F="Y://27.zxs.Z/b5/sT"+"?3G="+j.v.3G+"&4c="+1v(j.v.4c)+"&3c="+j.v.3c+"&H="+j.v.H+"&46="+j.v.46;if(j.v.2v)F+="&2v="+j.v.2v;if(j.v.T)F+="&T="+j.v.T;if(j.v.49)F+="&49="+1v(j.v.49);if(j.v.D)F+="&aP="+1v(j.v.D);if(j.v.4R)F+="&4R="+1v(j.v.4R);if(j.q.H)F+="&89="+j.q.H;if(3p.5y)F+="&5y="+3p.5y;if(3p.eB)F+="&eB="+3p.eB;l o=j;j.q.I.1z(F,k(v){if(v&&v.2X==0){D&&D.1b(E,v)}L{1P(v.4f)}})};21.A.jz=k(2v,T){l o=j;j.2u(k(v){l F=o.q.aA()+"/U/27.1L"+"?3G="+o.v.3G+"&4c="+1v(o.v.4c)+"&3c="+o.v.3c+"&H="+o.v.H+"&46="+v.46;F+="&2v="+2v;F+="&T="+T;l D=o.q.1B+"/3M.1L?S="+o.v.sp.S+(o.q.H?"&H="+o.q.H:"");F+="&D="+1v(D);if(o.q.H)F+="&89="+o.q.H;R.19.2B(F)})};21.A.ju=k(){l o=j;j.2u(k(v){R.bV&&R.bV(o.v)})};21.A.ag=k(){l o=j;j.2u(k(v){R.ag&&R.ag(o.v)})};21.A.ab=k(){l o=j;j.2u(k(v){R.ab&&R.ab(o.v)})};21.A.ac=k(){l o=j;j.2u(k(v){R.ac&&R.ac(o.v)})};21.A.a1=k(){l o=j;j.2u(k(v){R.a1&&R.a1(o.v)})};21.A.ao=k(){l o=j;j.2u(k(v){R.ao&&R.ao(o.v)})};21.A.ah=k(){l o=j;j.2u(k(v){R.ah&&R.ah(o.v)})};21.A.az=k(){l o=j;j.2u(k(v){R.az&&R.az(o.v)})};21.A.ay=k(){l o=j;j.2u(k(v){R.ay&&R.ay(o.v)})};21.A.aD=k(){l o=j;j.2u(k(v){R.aD&&R.aD(o.v)})};21.A.aC=k(){l o=j;j.2u(k(v){R.aC&&R.aC(o.v)})};21.A.as=k(){l o=j;j.2u(k(v){R.as&&R.as(o.v)})};21.A.av=k(){l o=j;j.2u(k(v){R.av&&R.av(o.v)})};21.A.9M=k(){l o=j;j.2u(k(v){R.9M&&R.9M(o.v)})};21.A.9S=k(){l o=j;j.2u(k(v){R.9S&&R.9S(o.v)})};21.A.9L=k(){l o=j;j.2u(k(v){R.9L&&R.9L(o.v)})};21.A.a6=k(){l o=j;j.2u(k(v){R.a6&&R.a6(o.v)})};21.A.a8=k(){l o=j;j.2u(k(v){R.a8&&R.a8(o.v)})};21.A.bj=k(){l o=j;j.2u(k(v){R.bj&&R.bj(o.v)})};21.A.bc=k(){l o=j;j.2u(k(v){R.bc&&R.bc(o.v)})};21.A.ba=k(){l o=j;j.2u(k(v){R.ba&&R.ba(o.v)})};21.A.be=k(){l o=j;j.2u(k(v){R.be&&R.be(o.v)})};21.A.bC=k(){l o=j;j.2u(k(v){R.bC&&R.bC(o.v)})};21.A.bF=k(){l o=j;j.2u(k(v){R.bF&&R.bF(o.v)})};21.A.bD=k(){l o=j;j.2u(k(v){R.bD&&R.bD(o.v)})};21.A.by=k(){l o=j;j.2u(k(v){R.by&&R.by(o.v)})};21.A.bs=k(){l o=j;j.2u(k(v){R.bs&&R.bs(o.v)})};21.A.bq=k(){l o=j;j.2u(k(v){R.bq&&R.bq(o.v)})};21.A.jl=k(){l o=j;j.2u(k(v){if(R==2D)N;l 9W=v.46;l F="Y://wx.zxs.Z/b9/8A?T="+o.v.T;o.q.I.1z(F,k(v){if(v.3d){1P(v.3d);N};l bL={1C:"27",3G:o.v.3G,3c:o.v.3c,4c:o.v.4c,H:o.v.H,46:9W,T:v.T,89:o.q.H};R.8w.4e(bL,"*")})})};21.A.jh=k(){l 2y=j.q.I.mx();if(2y!=j.q.1B){l jg=19.2I.2B(2y,j.q.1B);R.19.2B(jg);N};l o=j;j.2u(k(v){o.jO(v.46)})};21.A.27=k(){2L(j.v.4d){18 1:j.8P();W;18 2:j.8U();W;18 3:j.k3();W}};21.A.4e=k(v){if(j.v.57){R.4e(v,"*")}L{l 3V=C.1j(j.q.aL)||C.1o("dg")[0];3V.aJ.4e(v,"*")}};21.A.5W=k(1y){j.4e({1C:"27:D",3G:j.v.3G,3c:j.v.3c,1S:1y,sp:j.v.sp})};21.A.bf=k(){j.4e({1C:"27:2d",sp:j.v.sp})};21.A.8P=k(){l F="Y://27.zxs.Z/fo/ev"+"?3G="+j.v.3G+"&4c="+1v(j.v.4c)+"&3c="+j.v.3c+"&H="+j.v.H+"&46="+j.v.46+"&4d="+j.v.4d+"&55="+j.v.55;if(j.v.2v)F+="&2v="+j.v.2v;if(j.v.T)F+="&T="+j.v.T;if(j.v.49)F+="&49="+1v(j.v.49);if(j.v.D)F+="&aP="+1v(j.v.D);if(j.v.4R)F+="&4R="+1v(j.v.4R);if(j.aV)F+="&mp=k9";if(j.8q)F+="&mp=p3";if(j.q.H)F+="&89="+j.q.H;if(1a.4w&&1a.4w!=1a.1W)F+="&4w="+1a.4w;l o=j;j.q.I.1z(F,k(v){if(v.3d){l 8a=1f;l 5q="1a.T="+1a.T+"\\n"+"1a.1W="+1a.1W+"\\n"+"1a.4w="+1a.4w+"\\n"+"q.H="+o.q.H+"\\n"+"F="+F;o.q.I.9F("p2",0,5q,k(id){8a=1g;1P(v.3d+"（ji："+id+"）")});1Q(k(){if(!8a)1P(v.3d)},4D);N};if(o.aV){o.q.1h.8P(v)}L if(o.8q){o.jQ(v,k(){o.5W(1)})}L{o.q.1h.79(v,k(2U){if(2U.c9=="79:ok"){o.5W(1)}L{1P("k0："+2U.c9);o.5W(-1)};o.ui.6a()})}})};21.A.8U=k(){l F="Y://27.zxs.Z/b5/27"+"?3G="+j.v.3G+"&4c="+1v(j.v.4c)+"&3c="+j.v.3c+"&H="+j.v.H+"&46="+j.v.46+"&4d="+j.v.4d+"&55="+j.v.55;if(j.v.2v)F+="&2v="+j.v.2v;if(j.v.T)F+="&T="+j.v.T;if(j.v.49)F+="&49="+1v(j.v.49);if(j.v.D)F+="&aP="+1v(j.v.D);if(j.v.4R)F+="&4R="+1v(j.v.4R);if(j.aN)F+="&mp=k9";if(j.q.H)F+="&89="+j.q.H;if(j.aN){l o=j;j.q.I.1z(F,k(v){if(v.3d){1P(v.3d);N};o.q.1h.8U(v.qQ)})}L{R.19=F}};21.A.k3=k(){l F="Y://27.zxs.Z/fo/ev"+"?3G="+j.v.3G+"&4c="+1v(j.v.4c)+"&3c="+j.v.3c+"&H="+j.v.H+"&46="+j.v.46+"&4d="+j.v.4d+"&55="+j.v.55;if(j.v.2v)F+="&2v="+j.v.2v;if(j.v.T)F+="&T="+j.v.T;if(j.v.49)F+="&49="+1v(j.v.49);if(j.v.D)F+="&aP="+1v(j.v.D);if(j.v.4R)F+="&4R="+1v(j.v.4R);if(j.q.H)F+="&89="+j.q.H;F+="&mp=kh";l o=j;j.q.I.1z(F,k(v){if(v.3d){1P(v.3d);N};R.19=v.qJ})};21.A.jQ=k(v,D){j.v.dr=v.dr;j.ui.6a();j.ui.k5(j.v);j.9o=1g;l o=j;l 3y=k(){if(!o.9o)N;l F="Y://27.zxs.Z/b5/3y?3G="+o.v.3G+"&T="+o.v.T;o.q.I.1z(F,k(v){if(v.3d)N;if(!!v&&!!v.3G&&v.1S==1){o.9o=1f;o.ui.aF();D&&D.1b(E)}L{1Q(3y,3K)}})};3y()};21.A.jO=k(46){l F=j.q.jL()+"/fo/ev"+"?3G="+j.v.3G+"&4c="+1v(j.v.4c)+"&3c="+j.v.3c+"&H="+j.v.H+"&46="+46;F+="&qA="+(1a.4w||1a.1W);if(j.v.49)F+="&49="+1v(j.v.49);l o=j;j.q.I.1z(F,k(v){if(v.3d){1P(v.3d);N};o.q.1h.79(v,k(2U){if(2U.c9=="79:ok"){o.5W(1)}L{1P("k0："+2U.c9);o.5W(-1)};o.ui.6a()})})};5w=k(q){j.q=q;l o=j;j.q.V("bT",k(){o.8c()})};5w.A.jU=k(v){if(!v.57){j.6a();j.q.ui.4t()};l o=j;l 4p=R.3F>R.3h;l B=C.K("B");B.id="5P";B.Q="5P"+(v.57?"":" b0")+(4p?" pc":"");C.1o("1w")[0].J(B);l 3J=C.K("B");3J.Q="k6";3J.1c="qC";B.J(3J);l 2d=C.K("O");2d.1T="Y://U.zxs.Z/O/2n.2f";3J.J(2d);l 7X=C.K("B");7X.Q="qr";B.J(7X);l 3e=C.K("O");3e.1T=v.3e||"Y://U.zxs.Z/3e.2f";7X.J(3e);3e.1i.2j="35";l 7K=C.K("7K");7X.J(7K);l tr,td;tr=C.K("tr");td=C.K("td");td.Q="f4";td.1c="qn";tr.J(td);td=C.K("td");td.Q="hq";td.1c=v.sp?v.sp.3R:"qo";tr.J(td);7K.J(tr);tr=C.K("tr");td=C.K("td");td.Q="f4";td.1c="qu";tr.J(td);td=C.K("td");td.Q="hq";td.1c=v.4c;tr.J(td);7K.J(tr);tr=C.K("tr");td=C.K("td");td.Q="f4";td.1c="qt";tr.J(td);td=C.K("td");td.Q="hq";td.1c=j.q.I.71(v.3c,{3j:"#9j",6f:1f,7a:1g,5o:1g});tr.J(td);7K.J(tr);l 37=C.K("B");37.Q="zw";37.1i.2j="35";B.J(37);l 7p=C.K("B");7p.Q="zz";37.J(7p);l 8j=C.K("4g");8j.id="jT";8j.1q="zy";7p.J(8j);l aB=C.K("3f");aB.zt("28","jT");aB.1c="zs";7p.J(aB);l b8=C.K("3C");b8.1c="（?hl）";7p.J(b8);l 6g=C.K("B");6g.Q="zv";6g.1c="zu?hl";37.J(6g);l 9H=C.K("B");9H.Q="zF";9H.1i.2j="35";9H.1c="zE：zH = 0.zG";B.J(9H);l 6D=C.K("B");6D.Q="zB";B.J(6D);l 9y=C.K("B");9y.Q="zA";6D.J(9y);l df=C.K("B");df.Q="zD";df.1c="zC";9y.J(df);l 9B=C.K("B");9B.Q="zr";9B.1c="dW "+j.q.I.71(v.84,{3j:"#9j",6f:1f,7a:1g,5o:1g});9y.J(9B);l 4d=C.K("B");4d.Q="ze";6D.J(4d);l 6s=C.K("O");6s.id="ed";6s.Q="ed";4d.J(6s);l el=k(e){o.8s(1);e.1V()};6s.V("1Y",el);6s.V("3L",el);l 8m=C.K("O");8m.id="ee";8m.Q="ee";4d.J(8m);l es=k(e){o.8s(2);e.1V()};8m.V("1Y",es);8m.V("3L",es);l 5G=C.K("O");5G.id="e2";5G.Q="e2";4d.J(5G);l eV=k(e){o.8s(3);e.1V()};5G.V("1Y",eV);5G.V("3L",eV);l 4Q=C.K("B");4Q.id="e0";4Q.Q="e0";4Q.1i.2j="35";B.J(4Q);l 5g=C.K("4g");5g.1q="zd";5g.Q="zi";5g.2E="zh";5g.V("1O",k(e){o.q.27.27()});B.J(5g);l 2n=k(e){if(!o.q.27.v.57)o.6a();o.q.27.bf();e.1V()};2d.V("1Y",2n);2d.V("3L",2n);if(v.3c>1&&v.H!="za"&&!j.q.5c&&v.H!="zg"&&v.H!="jS"){j.q.jP(k(){if(o.q.37==0)N;b8.1c="（"+o.q.I.71(o.q.37,{6f:1f,5o:1f})+"）";if(o.q.37==0){8j.z9=1g};6g.1i.2j="35";37.1i.2j="";o.8c();8j.V("1O",k(e){if(j.zc){v.55=(o.q.37<v.3c?o.q.37:v.3c-1);v.84=v.3c-v.55;6g.1c="dW "+o.q.I.71(v.55,{3j:"#9j",6f:1f,7a:1g,5o:1g});6g.1i.2j=""}L{v.55=0;v.84=v.3c;6g.1i.2j="35"};9B.1c="dW "+o.q.I.71(v.84,{3j:"#9j",6f:1f,7a:1g,5o:1g});o.q.27.ec()})})};if(j.q.I.29()=="wx"||j.q.27.6v||j.q.27.8q){j.8s(1);5G.1i.2j="35"}L{6s.1i.2j="35";j.8s(2);if(j.q.I.fQ()){}L{5G.1i.2j="35"};if(j.q.I.29()=="5R"){5G.1i.2j="35"}};j.8c()};5w.A.6a=k(){l B=C.1j("5P");if(B)B.2e.1Z(B);j.q.ui.5J()};5w.A.8s=k(1q){j.q.27.v.4d=1q;C.1j("ed").1T="Y://U.zxs.Z/O/kj"+(1q==1?"on":"bE")+".2f";C.1j("ee").1T="Y://U.zxs.Z/O/zb"+(1q==2?"on":"bE")+".2f";C.1j("e2").1T="Y://U.zxs.Z/O/kj"+(1q==3?"on":"bE")+".2f"};5w.A.kk=k(v){l B=C.1j("5P");l 4Q=C.1j("e0");if(!B)N;if(v.4Q){4Q.1c="zo"+v.zq+"，zp "+j.q.I.71(v.4Q,{3j:"#9j",6f:1f,7a:1g,5o:1g})+"。";4Q.1i.2j=""}L{4Q.1c="";4Q.1i.2j="35"};j.8c()};5w.A.k5=k(v){if(!v.57){j.aF();j.q.ui.4t()};l o=j;l 4p=R.3F>R.3h;l B=C.K("B");B.id="f7";B.Q="5P"+(v.57?"":" b0")+(4p?" pc":"");C.1o("1w")[0].J(B);l 3J=C.K("B");3J.Q="k6";3J.1c=j.q.I.29()=="wx"?"zk":"zj";B.J(3J);l 2d=C.K("O");2d.1T="Y://U.zxs.Z/O/2n.2f";3J.J(2d);l 6i=C.K("B");6i.Q="zm";B.J(6i);l 6x=C.K("B");6x.Q="zl";6i.J(6x);l 5K=o.q.I.6B(4p?30:60);l 56=26 ib(6x,{3I:5K,5B:5K});56.ii(v.dr);l 2n=k(e){if(!o.q.27.v.57)o.aF();o.q.27.9o=1f;e.1V()};2d.V("1Y",2n);2d.V("3L",2n);j.8c()};5w.A.aF=k(){l B=C.1j("f7");if(B)B.2e.1Z(B);j.q.ui.5J()};5w.A.8c=k(){l 4p=R.3F>R.3h;l B=C.1j("5P");if(B){B.Q="5P"+(j.q.27.v.57?"":" b0")+(4p?" pc":"");if(!j.q.27.v.57){1Q(k(){if(B)B.1i.2D=B.5d<R.3h?(R.3h-B.5d)/2+"px":0},50)}};l 8d=C.1j("f7");if(8d){8d.Q="5P"+(j.q.27.v.57?"":" b0")+(4p?" pc":"");if(!j.q.27.v.57){1Q(k(){if(B)8d.1i.2D=8d.5d<R.3h?(R.3h-8d.5d)/2+"px":0},50)}}};4b=k(q){j.q=q;j.2p={9O:10,7D:20,lR:30,2P:50};j.1A={lf:A4,A3:A6,A5:A0,zZ:A2,A1:Ac,Ab:Ae,Ad:A8,A7:Aa,A9:zY,zN:zM,zP:zO,lc:zJ,l0:zI,zL:zK,zV:zU,zX:zW,zR:zQ,zT:zS,z8:yp,yo:yr,kY:yq};j.ui=26 6m(q);j.2m()};4b.A.2m=k(){l o=j;j.q.V("ai",k(){if(o.q.8l&&!o.q.5E){o.q.V("5E",k(){o.an()})}L{o.an()}})};4b.A.an=k(){if(!1a.T)N;l F="Y://pp.zxs.Z/im/yl?T="+1a.T;l o=j;j.q.I.1z(F,k(v){if(v&&v.2a&&v.2a.1l>0){o.jI(v.2a)}})};4b.A.jI=k(2a){l 1t=E,1d=E,n=0;l o=j;28(l i=0;i<2a.1l;i++){l 22=2a[i];2L(22.1q){18 9:j.jG(22);W;18 13:j.jy(22);W;18 14:W;48:if(22.1q==12&&22.X.U.S=="jq"){j.jt(22);W};n++;if(n==1){j.q.ui.3k&&j.q.ui.3k.53();1t=C.K("B");1t.Q="yk";C.1o("1w")[0].J(1t);1d=C.K("B");1d.Q="yn";1t.J(1d)}(k(22){1Q(k(){l B=o.ui.kZ(22);1t.J(B);1Q(k(){l 39=k(){B.2e.1Z(B);if(1t.7r("eS").1l==0){1t.2e.1Z(1t);o.q.ui.3k&&o.q.ui.3k.5x()}};B.Q="eS gG";B.V("4o",39);B.V("4n",39);1d.Q+=" ei"},jF)},n*50)})(22);W}}};4b.A.jG=k(22){l o=j;j.ui.d4({1u:22.3Z,X:"{1N}ym："+22.X.3D,4i:[{1q:"8T",1O:k(){o.q.2H.eI(22,1);o.q.58()}},{1q:"eD",1O:k(){o.q.2H.eI(22,-1);o.q.58()}}]});j.q.58(3)};4b.A.jt=k(22){l o=j;j.ui.d4({1u:22.3Z,X:"{1N}yx。",4i:[{3f:"yw",1q:"8T",1O:k(){l jr=22.X.1X;R.19=q.1B+"/3M.1L?S=jq&v="+jr}},{3f:"yA",1q:"eD"}]})};4b.A.jy=k(22){if(22.X.2d){j.ui.eg();j.q.58()}L{l o=j;j.ui.d4({1u:22.3Z,X:"{1N}yz",4i:[{1q:"8T",1O:k(){o.q.2H.eJ(22,1);o.q.58()}},{1q:"eD",1O:k(){o.q.2H.eJ(22,-1);o.q.58()}}]});j.q.58(3)}};4b.A.51=k(22){l X="";2x{2L(22.1q){18 1:X=22.X;W;18 2:X="yt";W;18 3:X="ys";W;18 4:X="yv";W;18 5:X="[yu]";W;18 6:X=22.X.qa.X;W;18 7:X=22.X.yj;W;18 8:X="[y8]";W;18 9:X="y7："+22.X.3D;W;18 10:X="ya："+(22.X.3S==1?"8H":"8G");W;18 11:X=22.X.y4.X.1p;W;18 12:X=22.X.1K;W;18 13:X="[y3]";W;18 14:X="[y6]："+(22.X.3S==1?"8H":"8G");W;48:X="【y5】";W}}2w(e){1r.1x(e)};N X};4b.A.2i=k(P,1H,3b){l F="Y://pp.zxs.Z/im/yf?T="+1a.T+"&3D="+1v(2N.3o(P));j.q.I.1z(F,k(v){if(v&&v.1H){1H&&1H.1b(E,v)}L{3b=3b||k(){1P("yi")};3b.1b(E)}})};4b.A.lg=k(6l,5X,X,1H,3b){j.2i({ch:j.2p.7D,2l:j.1A.lf,6l:6l,5X:5X,X:X},1H,3b)};4b.A.mH=k(6l,5X,3D,1H,3b){j.2i({ch:j.2p.7D,2l:j.1A.lc,6l:6l,5X:5X,3D:3D},1H,3b)};4b.A.eI=k(22,3S){l P={ch:j.2p.7D,2l:j.1A.l0,6l:j.q.1u,5X:22.3Z,cY:22.X.cY,3S:3S};l o=j;j.2i(P,k(v){o.q.I.1P("yh"+(3S==1?"8H":"8G")+"yc。")})};4b.A.eJ=k(22,3S){l P={ch:j.2p.7D,2l:j.1A.kY,6l:j.q.1u,5X:22.3Z,cY:22.X.cY,3S:3S};l o=j;j.2i(P,k(v){if(3S==1){o.q.I.5p({1K:"hC",X:"yb，kn.."});l 2h=22.X.3m;R.19=o.q.1B+"/3M.1L?S=pk&2h="+2h}});j.q.58()};4b.A.lt=k(1u){R.19=j.q.1B+"/1h/2H.1L?3Z="+1u+"&r="+2z.2J()};6m=k(q){j.q=q};6m.A.kZ=k(22){l B=C.K("B");B.Q="eS gJ";l O=C.K("O");O.1T="Y://U.zxs.Z/48.2f";B.J(O);l o=j;j.q.aq(22.3Z,k(1N){22.l2=1N;O.1T=o.q.I.5V(1N.3n)});l 1p=C.K("B");1p.1c=j.q.2H.51(22);B.J(1p);B.V("1Y",k(e){o.dp(22.l2,1g);e.1V()});N B};6m.A.dp=k(1N,l6){l 1d=C.K("B");1d.Q="ye";C.1o("1w")[0].J(1d);l B=C.K("B");B.Q="l4 it";C.1o("1w")[0].J(B);l 4g=C.K("4g");4g.69=(l6?"yd：":"eL：")+1N.2Q;B.J(4g);l a=C.K("a");a.Q="yX";a.1c="eL";B.J(a);l 2k=C.K("a");2k.Q="yW";2k.1c="yZ";B.J(2k);l eO=k(){l 39=k(){B.2e.1Z(B);1d.2e.1Z(1d)};B.Q="l4 yY";B.V("4o",39);B.V("4n",39);1d.Q+=" ei"};l cH=1f;l o=j;l 2i=k(){if(cH)N;cH=1g;a.1c="……";o.q.2H.lg(o.q.1u,1N.1I,4g.2E,k(){a.1c="√";1Q(eO,7w)},k(){a.1c="eL";cH=1f})};4g.V("yT",k(e){if(e.yS==13){2i()}});a.V("1Y",k(e){2i();e.1V()});1d.V("1Y",k(e){eO();e.1V()});2k.V("1Y",k(e){o.q.2H.lt(1N.1I);e.1V()})};6m.A.yU=k(3Z){l o=j;l 1d=C.K("B");1d.id="em";1d.Q="em";1d.V("1Y",k(e){o.lr();e.1V()});C.1o("1w")[0].J(1d);l 1t=C.K("B");1t.id="d2";1t.Q="d2 z5";C.1o("1w")[0].J(1t);1Q(k(){1t.1i.z4="z7"},5r);l 3V=C.K("dg");3V.id="lu";3V.Q="lu";3V.1T=j.q.1B+"/1h/2H.1L?3Z="+3Z+"&r="+2z.2J();1t.J(3V)};6m.A.lr=k(){l 1d=C.1j("em");l 1t=C.1j("d2");if(1t){l 39=k(){1t.2e.1Z(1t);1d.2e.1Z(1d)};1t.Q="d2 z6";1t.V("4o",39);1t.V("4n",39)}};6m.A.d4=k(G){l 3t={1u:0,1N:E,X:"",4i:[]};G=j.q.I.36(3t,G);j.q.ui.3k&&j.q.ui.3k.53();l 1t=C.K("B");1t.id="eh";1t.Q="eh";C.1o("1w")[0].J(1t);l 1d=C.K("B");1d.id="ej";1d.Q="ej";1t.J(1d);l B=C.K("B");B.id="d6";B.Q="d6 gJ";1t.J(B);l O=C.K("O");O.Q="z1";O.1T="Y://U.zxs.Z/48.2f";B.J(O);l 1p=C.K("B");1p.Q="z0";B.J(1p);l o=j;l en=k(1N,X){O.1T=o.q.I.5V(1N.3n);1p.1c=X.2B("{1N}",1N.2Q+"<O 1T=\'Y://U.zxs.Z/O/"+(1N.8M==1?"iu":"iy")+".2f\' />")};if(G.1N){en(G.1N,G.X)}L if(G.1u){j.q.aq(G.1u,k(1N){en(1N,G.X)})};l 2t=C.K("B");2t.Q="z3";B.J(2t);28(l i=0;i<G.4i.1l;i++){(k(1M){l a=C.K("a");a.Q=(1M.1q=="8T"?"z2":"yR");a.1c=1M.3f||(1M.1q=="8T"?"8H":"8G");l 4x=k(e){1M.1O&&1M.1O.1b(E);o.eg();e.1V()};a.V("1Y",4x);a.V("3L",4x);2t.J(a)})(G.4i[i])}};6m.A.eg=k(){l 1t=C.1j("eh");l 1d=C.1j("ej");l B=C.1j("d6");if(B){l o=j;l 39=k(){1t.2e.1Z(1t);o.q.ui.3k&&o.q.ui.3k.5x()};B.Q="d6 gG";B.V("4o",39);B.V("4n",39);1d.Q+=" ei"}};1D=k(q){j.q=q;j.2C={aM:0,7y:1,88:2,9d:3,cP:4,fx:5,ck:6,9I:7};j.S=(j.q.S!=E&&j.q.S!="pk"?j.q.S:E);j.2h=j.q.2h;j.4r=j.q.4r;j.3m=E;j.6H=E;j.eX=1f;j.ll=(j.q.1C=="yG");j.bU=1f;j.lx();j.6I=(j.2h?1g:1f);j.kz=(j.q.4s?1g:1f);j.4U=0;j.1S=j.2C.aM;j.cC=0;j.7c=E;j.4I=[];j.3W=E;j.c5=1f;j.1U=[];j.fu=1f;j.cz=1f;j.c2=1f;j.cS=26 6y(q);j.ui=26 1E(q);j.io=26 1n(q,j.ui);j.io.dm()};1D.A.f9=k(S,bX){l o=j;l 1F=k(){R.19=o.q.1B+"/3M.1L?S="+S+"&2h="+o.2h};if(bX){1Q(1F,7j)}L{1F()}};1D.A.9p=k(S,2h,bX){3p.2h=2h;l o=j;l 1F=k(){R.19=o.q.1B+"/3M.1L?S="+S};if(bX){1Q(1F,7j)}L{1F()}};1D.A.lx=k(){if(j.S&&!j.2h&&3p.2h){j.3m=3p.2h;j.bU=1g};3p.6c("2h")};1D.A.42=k(){if(!j.7c)j.io.nk();if(!j.3W)j.io.nL();j.ui.4t();l o=j;1Q(k(){if(o.4r){o.ko()}L if(o.6I){o.ku()}L if(o.bU){o.kB()}L if(o.ll){o.fg()}L{o.3v()}},7w)};1D.A.3v=k(){j.1S=j.2C.88;j.ui.j4();j.io.lV();if(j.q.9A())N;l o=j;1Q(k(){if(o.1S==o.2C.88){if(o.S){if(!o.c5){o.lm()}}L{o.ln()}}},yF)};1D.A.8I=k(){j.2h=E;j.bU=1f;j.6I=1f;j.ui.4k("yI..");l o=j;1Q(k(){o.3v()},5r)};1D.A.n5=k(1e){j.c2=1f;j.q.2O("hG",1e);j.ui.oB(1e);l o=j;j.q.on("65",k(){l 5D=1Q(k(){if(!o.S&&o.cR().S){o.9p(o.cR().S,o.3m,1f)}L{o.5F()}},lo);o.q.on("cI",k(){8V(5D)},1g)},1g)};1D.A.lm=k(){j.ui.4k("e1..",E,1g);j.io.do()};1D.A.nh=k(1e,U){l o=j;l ok=k(){o.io.lY(1e.1I,U.S)};l 2d=k(){o.io.lZ(1e.1I);1Q(k(){if(o.1S==o.2C.88){o.io.do()}},4D)};j.ui.iB(1e,U,ok,2d)};1D.A.ng=k(){l o=j;1Q(k(){if(o.1S==o.2C.88){o.lq()}},7j)};1D.A.ni=k(1u,S){R.19=j.q.1B+"/3M.1L?S="+S};1D.A.nc=k(1e){j.c5=1g;j.ui.7F();l o=j;1Q(k(){o.c5=1f;if(o.1S==o.2C.88){o.3v()}},gi)};1D.A.lq=k(){j.ui.4k("e1...",E,1g);l o=j;1Q(k(){o.io.lW()},5r)};1D.A.ln=k(){j.io.lS();l 1e=j.ui.oA();1r.1x("yH："+1e.2Q);l o=j;1Q(k(){o.io.nv(1e.1I)},lo)};1D.A.9X=k(1u,S,4r){1r.1x("4r = "+4r);R.19=j.q.1B+"/3M.1L?S="+S+"&4r="+4r};1D.A.fg=k(){if(j.6I&&j.4U==j.q.1u){1r.1x("yC，yB");j.6G();j.io.bt()}L{1r.1x("yE，ky");if(j.3m)j.6G();j.io.nq()}};1D.A.bd=k(2h,4U){1r.1x("ky：3m = "+2h+", 4U = "+4U);j.2h=2h;j.6I=1g;j.4U=4U;j.io.bt()};1D.A.ku=k(){j.1S=j.2C.7y;j.ui.4k("yD");l o=j;1Q(k(){o.io.bt()},5r)};1D.A.n0=k(1S){j.1S=j.2C.9d;j.ui.9n();if(1S==1){j.5F()}L{l o=j;if(j.2h!=j.q.2h){j.ui.ds();j.ui.dK("kC",1f,k(){o.ui.f0(k(){o.ui.cJ()})});j.q.on("65",k(){o.ui.cV()},1g)}L{if(j.4U==j.q.1u){if(j.kz){j.ui.ds();j.ui.dK("kC",1f,k(){o.ui.f0(k(){o.ui.cJ()})});j.q.on("65",k(){o.ui.cV()},1g)}L{j.ui.cJ()}}L{l 5n=k(){o.ui.dv("yO",30,k(){o.ui.9v("yN");o.ui.cQ([{1p:"yQ",1O:k(){o.io.dX();o.ui.9r();5n();o.ui.9v("yP,yK")}},{1p:"cZ",1O:k(){R.19=o.q.1B+"/3M.1L?S=pk"}}])})};5n();j.io.dX()}};j.q.on("65",k(){o.5F()},1g)}};1D.A.mU=k(1y){if(1y==0){j.ui.4k("yJ")}L{j.ui.4k("yM")}};1D.A.kB=k(){j.1S=j.2C.7y;j.io.nw()};1D.A.mV=k(1S){j.1S=j.2C.9d;if(1S==1){j.5F()}L{l o=j;l 5n=k(){o.ui.dv("kn",30,k(){o.ui.9v("kE");o.ui.cQ([{1p:"kQ",1O:k(){o.ui.9r();5n()}},{1p:"cZ",1O:k(){R.19=o.q.1B+"/3M.1L?S=pk"}}])})};5n();j.q.on("65",k(){o.5F()},1g)}};1D.A.ko=k(){j.1S=j.2C.7y;j.io.nt(j.4r)};1D.A.nI=k(2h){j.1S=j.2C.9d;j.5F()};1D.A.bu=k(2a){j.4I=2a;j.q.2O("kN")};1D.A.bx=k(2a){j.4I=2a};1D.A.c8=k(){j.ui.c8();j.ui.ou();l o=j;j.ui.iI("yL",5,k(){o.io.nM()});j.q.on("kV",k(U){o.ui.cs();o.ui.op(U.S)},1g);j.q.on("cI",k(){o.ui.cs();o.ui.fs()},1g)};1D.A.aS=k(1U){j.3W=1U;j.q.2O("iZ",1U)};1D.A.am=k(P){1r.1x("BG：\\BH = "+(P.U?P.U.5k:"E")+", cq = "+P.cq+", 1S = "+P.1S);j.q.2O("BI",P.1S);j.3m=P.3m;j.6H=P.U||E;j.eX=P.cr;j.1S=j.2C.9d;j.4U=P.4U;j.cC=P.cq};1D.A.ae=k(1S){1r.1x("BF！");j.q.2O("65",1S)};1D.A.af=k(1U){j.hB(1U);1r.1x("BC：");28(l i=0;i<1U.1l;i++){l 1e=1U[i];1r.1x("1I = "+1e.1I+", 2Q = "+1e.2Q)}};1D.A.aa=k(1e,1S){1r.1x("BD："+1e.2Q);j.ar(1e)};1D.A.ak=k(1e){if(1e.1I==j.q.1u){j.3m=E;j.6H=E;j.1S=j.2C.7y;j.4U=0;j.cC=0;j.q.2O("e8")}L{1r.1x("BE");j.c2=1g;j.kq(1e)}};1D.A.kq=k(1e){if(j.1S==j.2C.fx||j.1S==j.2C.ck||j.1S==j.2C.9I||j.fu||j.cz)N;j.eR(1e);j.q.2O("cI");j.ui.4k(1e.2Q+"BJ");l o=j;1Q(k(){o.6G();o.8I()},5r)};1D.A.5F=k(){1r.1x("BO");j.q.2O("hw");j.ui.5F();j.ui.cM();l o=j;if(j.S){if(j.cR().S){j.ui.9k("kO",5,k(){o.6J()})}L{j.cz=1g;j.ui.6Q();l 5n=k(){o.ui.9k("BP",10,k(){o.ui.9v("kE");o.ui.cQ([{1p:"kQ",1O:k(){o.ui.9r();5n()}},{1p:"cZ",1O:k(){R.19=o.q.1B+"/3M.1L?S=pk"}}])})};5n();j.q.on("65",k(){o.cz=1f;o.ui.cM();o.ui.9k("kO",5,k(){o.6J()})},1g)}}L if(j.6H){j.ui.4k("BQ",E,1f);j.9p(j.6H.S,j.3m,1f)}L{j.c8()}};1D.A.6J=k(cl){1r.1x("BN");j.q.2O("hv");j.ui.6J();j.ui.a0();j.3q();};1D.A.ov=k(S){j.io.np(S)};1D.A.m8=k(1u,U){1r.1x((1u==0?"BK":"kJ "+1u)+" BL "+U.5k);j.fu=1g;j.q.2O("kV",U);if(j.6I){j.f9(U.S,1g)}L{l 2h=j.3m.2B("hD",U.S);j.9p(U.S,2h,1g)}};1D.A.3q=k(){j.io.nA();j.1S=j.2C.cP};1D.A.9m=k(1U){1r.1x("BM kT Br：");28(l i=0;i<1U.1l;i++){l 1e=1U[i];1r.1x("1I = "+1e.1I+": "+(1e.3q?"3q":"Bs"))}};1D.A.at=k(1S){1r.1x("Bt kT");j.1S=j.2C.fx;j.q.2O("d7")};1D.A.hd=k(){1r.1x("Bq");j.q.2O("iN");j.1S=j.2C.ck;if(j.q.G.3k){l 1y=j.q.G.3k.1b(E);1r.1x("U 3k: 1y = "+1y)}};1D.A.6W=k(v){j.io.e3(v)};1D.A.39=k(v){if(j.1S==j.2C.ck){j.io.e3(v);l 1y=j.q.G.ce.1b(E);1r.1x("U ce: "+1y)}};1D.A.nr=k(1S){1r.1x("Bn");j.1S=j.2C.9I;l 1y=j.q.G.kI.1b(E);1r.1x("U f1: 1y = "+1y);j.ui.mr()};1D.A.a9=k(P){1r.1x("Bo：");28(l i=0;i<P.1y.1l;i++){l 1e=P.1y[i];1r.1x("1I = "+1e.1I+", 1X = "+1e.1X)};1r.1x("6R = "+P.6R);j.q.2O("iM");j.ui.mo(P);j.ui.cM();j.q.58(3);l o=j;j.q.on("d9 e8",k(){o.q.58()})};1D.A.Bp=k(){j.q.2O("d9");j.io.ny()};1D.A.9Y=k(P){1r.1x("fy：id = "+P.3m+", U = "+P.U.5k+", kJ = "+P.1U+", 1S = "+P.1S+", cr = "+P.cr);l o=j;if(P.cr){j.6G();j.8I()}L if(P.1S==1){j.ui.6J();j.ui.9N(P.cq);j.ui.eU();j.3q();j.ui.4k("Bu",E,1g);j.q.on("d7",k(){o.ui.a0()})}L{j.6G();j.8I()}};1D.A.mJ=k(){j.io.nK();l o=j;j.q.on("kN",k(){o.ui.ij(k(U){o.io.bb(U.S)})},1g)};1D.A.mD=k(S){if(S){j.io.bb(S)}L{j.io.bb()}};1D.A.aE=k(1e,U){l o=j;if(1e.1I==j.q.1u){if(j.eX||j.c2){if(!U){j.ui.4k("Bz");1Q(k(){o.6G();o.8I()},5r)}L{R.19=j.q.1B+"/3M.1L?S="+U.S}}L{if(!U)j.1S=j.2C.cP;j.q.I.5p({1K:(U?"hC"+U.5k:"fy"),X:"BA...",6b:{1O:k(){o.io.nm()}}})}}L{j.q.I.5p({X:1e.2Q+(U?"BB"+U.5k:"By"),5Y:{3f:"8H",1O:k(){o.io.dY(1,U?U.S:E)}},6b:{3f:"8G",1O:k(){o.io.dY(-1,U?U.S:E)}}})}};1D.A.bk=k(1e){if(1e.1I==j.q.1u){j.1S=j.2C.9I}L{j.q.I.8O();j.q.I.2k(1e.2Q+"Bv")}};1D.A.bp=k(1e,3S,U){if(1e.1I==j.q.1u){if(3S==1){if(!U)j.1S=j.2C.cP;j.f8(U);if(U){j.q.I.2k("hO..")}}}L{if(3S==1){j.q.I.8O();j.f8(U);if(U){j.q.I.2k(1e.2Q+"Bw，hO..")}}L{j.1S=j.2C.9I;j.q.I.8O();j.q.I.2k(1e.2Q+"Bx")}}};1D.A.f8=k(U){if(U){}L{j.q.2O("d9");j.ui.6J();j.ui.eU();l o=j;j.q.on("d7",k(){o.ui.a0()})}};1D.A.m2=k(S){if(j.6I){j.f9(S)}L{l 2h=j.3m;if(j.3m.43("C6")!=-1){2h=j.3m.2B("hD",S)}L if(j.6H){2h=j.3m.2B(j.6H.S,S)}L{2h=S+"bG"+j.q.I.6w(10)};j.9p(S,2h,1f)}};1D.A.6G=k(){j.ui.8z();j.io.nz()};1D.A.hB=k(1U){j.1U=1U;28(l i=0;i<j.1U.1l;i++){l 1e=j.1U[i];1e.1X=1e.1X||0;1e.4T=1e.4T||0}};1D.A.ar=k(1e){1e.1X=1e.1X||0;1e.4T=1e.4T||0;j.1U.3X(1e)};1D.A.eR=k(1e){28(l i=0;i<j.1U.1l;i++){if(j.1U[i].1I==1e.1I){j.1U.ht(i,1)}}};1D.A.ef=k(1u){28(l i=0;i<j.1U.1l;i++){if(j.1U[i].1I==1u)N j.1U[i]};N E};1D.A.cR=k(){28(l i=0;i<j.1U.1l;i++){if(j.1U[i].1I!=j.q.1u)N j.1U[i]};N E};1D.A.Cb=k(1e){28(l i=0;i<j.1U.1l;i++){if(j.1U[i].1I=1e.1I)N 1g};N 1f};1D.A.7t=k(1u,1X,4T){28(l i=0;i<j.1U.1l;i++){l 1e=j.1U[i];if(1e.1I==1u){1e.1X=1X;1e.4T=4T;j.ui.7t(1e);N}}};1D.A.nP=k(2a){1r.1x("Ca："+2a.1l+"C7");j.cS.dk();28(l i=0;i<2a.1l;i++){j.cS.c0(2a[i])};j.cS.3k()};1D.A.2H=k(1N){if(j.q.I.29()=="zxs"||j.q.I.29()=="2K"){j.q.1h.2H(1N.1I)}L{if(j.q.2H){j.q.2H.ui.dp(1N)}L{R.19=j.q.1B+"/1h/2H.1L?3Z="+1N.1I+"&r="+2z.2J()}}};6y=k(q){j.q=q;j.dk()};6y.A.dk=k(){j.ct=[];j.cx=1f;j.2R=0;};6y.A.c0=k(P){l 4a={54:0,1e:P.1e,cj:P.v,39:1f};j.ct.3X(4a)};6y.A.3k=k(){1r.1x("C9：Cd");j.cx=1g;l hM=26 4K().5t();l o=j;hP(k(){o.2R=26 4K().5t()-hM;o.hu();if(o.cx){hP(1G.C8)}L{1r.1x("Cc")}})};6y.A.hu=k(){l dU=1f;28(l i=0;i<j.ct.1l;i++){l 4a=j.ct[i];if(4a.cj.1l==0)4a.39=1g;if(4a.39)Ce;et(!4a.39){l 3x=4a.cj[4a.54];if(3x.2R>j.2R){dU=1g;W};j.a7(4a.1e,3x);4a.54++;if(4a.54==4a.cj.1l){4a.39=1g;1r.1x("4a: "+i+" 39!")}}};if(!dU)j.cx=1f};6y.A.a7=k(1e,3x){1r.1x("C5："+1e.2Q+", "+2N.3o(3x));j.q.pk.7t(1e.1I,3x.1X,3x.4T)};1E=k(q){j.q=q;j.bR=E;l o=j;j.q.on("hG",k(){o.7F()});j.q.on("hw",k(){o.iU();o.iS();o.9r()});j.q.on("hv",k(){o.8z();o.7S();o.6Q()});j.q.on("d7",k(){o.9n();o.6Q()});j.q.on("iN",k(){o.9n();o.a4();o.6Q();o.5J()});j.q.on("iM",k(){o.cB();o.mn()});j.q.on("d9",k(){o.aO()});j.q.on("e8",k(){o.8z();o.cB();o.a4();o.6Q();o.aO();o.7S()});j.q.on("cI",k(){o.8z();o.cB();o.a4();o.6Q();o.7S();o.6r("c3")})};1E.A.4t=k(){l 1d=C.1j("cK");if(!1d){1d=C.K("B");1d.id="cK";1d.Q="cK";C.1o("1w")[0].J(1d)};1d.1i.2j=""};1E.A.5J=k(){l 1d=C.1j("cK");if(1d)1d.1i.2j="35"};1E.A.dK=k(1p,iO,D){l 1M=C.K("a");1M.Q="ea";C.1o("1w")[0].J(1M);1M.1c=1p;1M.V("1Y",k(e){if(iO)C.1o("1w")[0].1Z(1M);D&&D.1b(E);e.1V()})};1E.A.cQ=k(2t){l 5K=2t.1l;28(l i=0;i<5K;i++){l 1p=2t[i].1p;l D=2t[i].1O;l 1M=C.K("a");1M.Q="ea";1M.1i.BV=(j.q.I.9l(10)+j.q.I.6B((5K-i-1)*17))+"px";C.1o("1w")[0].J(1M);1M.1c=1p;(k(1M,D){1M.V("1Y",k(e){D&&D.1b(E);e.1V()})})(1M,D)}};1E.A.9r=k(){l 2t=C.BX(".ea");28(l i=2t.1l-1;i>=0;i--){l 1M=2t[i];1M.2e.1Z(1M)}};1E.A.iF=k(3r,D){j.4k(3r);if(3r==0){D&&D.1b(E);N};l o=j;j.bR=1Q(k(){o.iF(--3r,D)},3K)};1E.A.iE=k(3r,D){j.eF(3r);if(3r==0){D&&D.1b(E);N};l o=j;j.bR=1Q(k(){o.iE(--3r,D)},3K)};1E.A.Cf=k(){8V(j.bR)};1E.A.9k=k(X,3r,D){j.7S();l B=C.K("B");B.id="eb";B.Q="eb";C.1o("1w")[0].J(B);l 4J=C.K("B");4J.Q="iG o4";B.J(4J);l 3a=C.K("B");3a.Q="iJ 3a";4J.J(3a);l 4M=C.K("B");4M.Q="iG o0";B.J(4M);l 2A=C.K("B");2A.Q="iJ 2A";4M.J(2A);l 1p=C.K("B");1p.id="dT";1p.Q="dT";1p.1c=X;B.J(1p);3a.1i.7N="7g "+3r+"s 4u";3a.1i.7R="4G";3a.1i.7u="7g "+3r+"s 4u";3a.1i.7Q="4G";2A.1i.7u="7L "+3r+"s 4u";2A.1i.7Q="4G";2A.1i.7N="7L "+3r+"s 4u";2A.1i.7R="4G";2A.V("4o",D);2A.V("4n",D)};1E.A.7S=k(){l B=C.1j("eb");if(B)B.2e.1Z(B)};1E.A.iI=k(X,3r,D){j.cs();l B=C.K("B");B.id="dV";B.Q="dV";C.1o("1w")[0].J(B);l 4J=C.K("B");4J.Q="iH BR";B.J(4J);l 3a=C.K("B");3a.Q="j2 3a";4J.J(3a);l 4M=C.K("B");4M.Q="iH BS";B.J(4M);l 2A=C.K("B");2A.Q="j2 2A";4M.J(2A);l 1p=C.K("B");1p.id="j1";1p.Q="j1";1p.1c=X;B.J(1p);3a.1i.7N="7g "+3r+"s 4u";3a.1i.7R="4G";3a.1i.7u="7g "+3r+"s 4u";3a.1i.7Q="4G";2A.1i.7u="7L "+3r+"s 4u";2A.1i.7Q="4G";2A.1i.7N="7L "+3r+"s 4u";2A.1i.7R="4G";2A.V("4o",D);2A.V("4n",D)};1E.A.cs=k(){l B=C.1j("dV");if(B)B.2e.1Z(B)};1E.A.9v=k(X){l 1p=C.1j("dT");if(1p){l fz;X=X+"";if(X.1l==1){fz=j.q.I.3H(32)}L if(X.1l==2){fz=j.q.I.3H(16)}L if(X.1l==3){fz=j.q.I.3H(12)}L if(X.1l<=5){fz=j.q.I.3H(9)}L if(X.1l<=7){fz=j.q.I.3H(7)}L{fz=j.q.I.3H(6)};1p.1c=X;1p.1i.j6=fz}};1E.A.4k=k(X,3j,co){j.4t();l 2k=C.1j("7P");l 1p=C.1j("cv");if(!2k){2k=C.K("B");2k.id="7P";2k.Q="7P";C.1o("1w")[0].J(2k);1p=C.K("B");1p.id="cv";1p.Q="cv";C.1o("1w")[0].J(1p)};2k.1i.2j="";1p.1i.2j="";l fz;X=X+"";if(X.1l==1){fz=j.q.I.3H(32)}L if(X.1l==2){fz=j.q.I.3H(16)}L if(X.1l==3){fz=j.q.I.3H(12)}L if(X.1l<=5){fz=j.q.I.3H(9)}L if(X.1l<=7){fz=j.q.I.3H(7)}L{fz=j.q.I.3H(6)};1p.1c=X;1p.1i.j6=fz;1p.1i.3j=(3j?3j:"#BY");2k.Q=(co?"7P co":"7P")};1E.A.9n=k(){l 2k=C.1j("7P");if(2k)2k.1i.2j="35";l 1p=C.1j("cv");if(1p)1p.1i.2j="35"};1E.A.j4=k(){j.4k("e1",E,1g);j.5A(j.q.1N);j.6r("c3");if(j.q.pk.3W){1r.1x("93 BZ");j.93()}L{l o=j;j.q.on("iZ",k(){1r.1x("93 C0");o.93()},1g)}};1E.A.iU=k(){j.9n();j.6r("iV");j.6r("c3");j.dh()};1E.A.dv=k(X,3r,D){j.4t();j.5A(j.q.1N);l o=j;j.9k(X,3r,D)};1E.A.iS=k(){j.6r("iV");j.6r("c3");j.7S()};1E.A.ds=k(){j.cV();l 23=j.q.I.2r(j.q.1m.23,"2h",j.q.pk.2h);l 1t=C.K("B");1t.id="fe";1t.Q="fe";C.1o("1w")[0].J(1t);l 1K=C.K("b");1K.1c=j.q.1N.2Q+"BU！";1t.J(1K);l 2k=C.K("3C");2k.1c="AC";1t.J(2k);l 6x=C.K("B");1t.J(6x);l o=j;j.q.I.ms(23,k(F){l 5K=o.q.I.6B(50);l 56=26 ib(6x,{3I:5K,5B:5K});56.ii(F)});l X=C.K("p");X.1c="ih，ie"+j.q.pk.2h+"<br/>i8……";1t.J(X);j.q.3z({3N:j.q.1N.3n,23:23,1K:"ih，ie"+j.q.pk.2h+"，i8……",X:"AB"+j.q.1N.2Q+"AE"})};1E.A.cV=k(){l B=C.1j("fe");if(B)B.2e.1Z(B)};1E.A.cJ=k(){l o=j;j.q.I.6W({X:"AD",2R:30,cD:"65",cF:"Ay",cG:[{3f:"Ax",fT:1g},{3f:"cZ",5u:"#i7",1O:k(){R.19=o.q.1B+"/3M.1L?S=pk"}}]})};1E.A.f0=k(D){j.d0();l O=C.K("O");O.id="i6";O.Q="AA";O.1T="Y://U.zxs.Z/2T.2f";l o=j;O.V("1Y",k(e){o.d0();e.1V()});C.1o("1w")[0].J(O);if(j.q.1h){j.q.1h.3g=k(){o.d0();D&&D.1b(E)}}};1E.A.d0=k(){l O=C.1j("i6");if(O)O.2e.1Z(O)};1E.A.5A=k(1N){l 3A=(1N.1I==j.q.1u);l id="Az"+(3A?"47":"38");l B=C.1j(id);if(!B){B=j.au(id,1N);B.Q+=(3A?" AF":" AL");C.1o("1w")[0].J(B)}};1E.A.6r=k(id){l B=C.1j(id);if(B)B.2e.1Z(B)};1E.A.au=k(id,1N){l B=C.K("B");B.id=id;B.Q="AK"+(1N.1I==j.q.1u?" 47":"");l 3s=C.K("O");3s.Q="AN";B.J(3s);l 2S=C.K("B");2S.Q="AM";B.J(2S);l 2Q=C.K("3C");2S.J(2Q);l 8M=C.K("O");2S.J(8M);l 4X=C.K("B");4X.Q="AH";B.J(4X);3s.1T=j.q.I.5V(1N.3n);2Q.1c=1N.2Q;8M.1T="Y://U.zxs.Z/O/"+(1N.8M==1?"iu":"iy")+".2f";4X.1c=1N.4X;N B};1E.A.iB=k(1e,U,ok,2d){j.7F();l B=C.1j("8x");B=C.K("8x");B.id="8x";B.Q="8x";C.1o("1w")[0].J(B);l 3e=C.K("O");3e.Q="AG";B.J(3e);l 1p=C.K("B");1p.Q="AJ";B.J(1p);l 1M=C.K("B");1M.Q="AI";B.J(1M);l 8L=C.K("a");8L.Q="Aw";8L.1c="oR";1M.J(8L);l 8v=C.K("a");8v.Q="Ak";8v.1c="Aj";1M.J(8v);3e.1T=j.q.I.5V(1e.3n);1p.1c="Am"+1e.4X+"Al“"+1e.2Q+"”Ag《"+U.5k+"》？";l o=j;8L.V("1Y",k(e){e.1V();o.7F();ok&&ok.1b(E)});8v.V("1Y",k(e){e.1V();o.7F();2d&&2d.1b(E)})};1E.A.7F=k(){l B=C.1j("8x");if(B)C.1o("1w")[0].1Z(B)};1E.A.5F=k(){l B=C.K("B");B.id="f2";B.Q="f2";C.1o("1w")[0].J(B);l ps=C.K("B");ps.Q="Af";B.J(ps);l 38=E;l 1U=j.q.pk.1U;28(l i=0;i<1U.1l;i++){l 1e=j.q.pk.ef(1U[i].1I);l 3A=(1e.1I==j.q.1u);if(!3A)38=1e;l 1N=j.au("Ai"+(3A?"47":"38"),1e);1N.Q+=(3A?" Ah":" An");ps.J(1N)}};1E.A.8z=k(){l B=C.1j("f2");if(B)B.2e.1Z(B)};1E.A.ij=k(D){j.ci();l o=j;l 1d=C.K("B");1d.id="ft";1d.Q="ft";1d.V("1Y",k(e){o.ci();e.1V()});C.1o("1w")[0].J(1d);l B=C.K("B");B.id="fv";B.Q="fv";C.1o("1w")[0].J(B);if(!j.q.pk.4I)N;l ul=C.K("ul");B.J(ul);28(l i=0;i<j.q.pk.4I.1l;i++){l U=j.q.pk.4I[i];l li=C.K("li");l O=C.K("O");O.1T=U.c7;li.J(O);l p=C.K("p");p.1c=U.5k;li.J(p);if(U.ip){l 3f=C.K("3f");3f.1c=U.ip;p.J(3f)};l 3C=C.K("3C");3C.1c=U.As+"Av";li.J(3C);ul.J(li);(k(li,U){li.V("1O",k(e){o.ci();D&&D.1b(E,U);e.1V()})})(li,U)};l 4C=C.K("B");B.J(4C);l cd=C.K("a");cd.1c="Ap";4C.J(cd);cd.V("1Y",k(e){ul.ot-=o.q.I.9l(70);e.1V()});l 5g=C.K("a");5g.1c="Ao";4C.J(5g);5g.V("1Y",k(e){ul.ot+=o.q.I.9l(70);e.1V()})};1E.A.ci=k(){l B=C.1j("fv");if(B)B.2e.1Z(B);l 1d=C.1j("ft");if(1d)1d.2e.1Z(1d)};1E.A.c8=k(){j.fs();l B=C.K("B");B.id="bS";B.Q="bS";C.1o("1w")[0].J(B);l o=j;28(l i=0;i<j.q.pk.4I.1l;i++){l U=j.q.pk.4I[i];l 2G=C.K("B");2G.Q="oq";2G.1K=U.S;B.J(2G);l O=C.K("O");O.id="om"+U.S;O.1T=U.c7;2G.J(O);l 1p=C.K("B");1p.1c=U.5k;2G.J(1p);(k(2G,U){2G.V("1Y",k(e){o.q.pk.ov(U.S);e.1V()})})(2G,U)}};1E.A.ou=k(){j.fh=E;l fB=C.1j("bS").7r("oq");l n=fB.1l;l 5a=[];28(l i=0;i<n;i++){l O=fB[i].1o("O")[0];5a.3X(O)};l 54=-1;j.fj=k(){28(l i=0;i<n;i++){5a[i].1i.ol="#Bb"}};j.fq=k(O){O.1i.ol="#Ba"};l o=j;l fk=k(){if(o.fh)N;54++;if(54>=n)54=0;o.fj();o.fq(5a[54]);1Q(fk,7w)};fk()};1E.A.op=k(S){j.fj();j.fh=S;l O=C.1j("om"+S);if(O){j.fq(O);O.Q+=" ez ew"}};1E.A.fs=k(){l B=C.1j("bS");if(B)B.2e.1Z(B)};1E.A.93=k(){j.dh();if(!j.q.pk.3W||j.q.pk.3W.1l==0)N;j.3W=j.q.pk.3W.lw(0);l 1t=C.K("B");1t.id="eA";1t.Q="eA";C.1o("1w")[0].J(1t);l 6L=C.K("B");6L.id="8Y";6L.Q="8Y";1t.J(6L);l B=C.K("B");B.id="bH";B.Q="bH";1t.J(B);j.6S=-1;j.ep=3;j.62=E;l i=0;et(i<j.ep){j.er(i);i++};j.eo()};1E.A.er=k(i){l B=C.1j("bH");if(B){j.6S++;if(j.6S>=j.3W.1l)j.6S=0;l 1e=j.3W[j.6S];l O=C.K("O");O.1T=j.q.I.5V(1e.3n);O.1K=1e.1I;O.1i.3w=j.q.I.3H(20*i);B.J(O)}};1E.A.eo=k(){l B=C.1j("bH");if(B){j.er(j.ep);l da=1f;l Bd=E;l o=j;1Q(k(){if(!B.2e)N;l 5a=B.1o("O");28(l i=0;i<5a.1l;i++){5a[i].1i.3w=o.q.I.3H(20*(i-1))};l oz=5a[1];l 4A=5a[2];l oy=5a[3];l oH=4A.1K;if(o.62&&oH==o.62.1I){da=1g}L{4A.Q+=" Bc"};1Q(k(){if(!B.2e)N;B.1Z(5a[0]);if(da){o.4k("B7");l 6L=C.1j("8Y");6L.Q="8Y ez ew";4A.Q="ez ew";oz.Q="5z";oy.Q="5z";l 5z=k(){4A.bZ("4o",5z);4A.bZ("4n",5z);6L.Q="8Y 5z";4A.Q="5z";l 5A=k(){4A.bZ("4o",5A);4A.bZ("4n",5A);o.dh();if(o.62){o.5A(o.62)}};4A.V("4o",5A);4A.V("4n",5A)};4A.V("4o",5z);4A.V("4n",5z)}},7w)},4l);l 5D=1Q(k(){if(!da)o.eo()},3K);j.q.on("o2",k(){8V(5D)},1g)}};1E.A.oB=k(1e){if(!j.3W||j.3W.1l==0)N;l 61=j.6S+1;if(61>=j.3W.1l)61=0;j.3W[61]=1e;j.62=1e};1E.A.oA=k(){l 61=j.6S+1;if(61>=j.3W.1l)61=0;j.62=j.3W[61];N j.62};1E.A.dh=k(){l B=C.1j("eA");if(B)B.2e.1Z(B);j.q.2O("o2")};1E.A.6J=k(){l B=C.K("B");B.id="5Z";B.Q="5Z";C.1o("1w")[0].J(B);l bg=C.K("B");bg.id="nU";bg.Q="nU";B.J(bg);l 1U=j.q.pk.1U;28(l i=0;i<1U.1l;i++){j.ar(1U[i])};j.eF(j.q.pk.cC);j.nT()};1E.A.cB=k(){l B=C.1j("5Z");if(B)B.2e.1Z(B)};1E.A.nT=k(){l B=C.1j("eT");if(!B){B=C.K("B");B.id="eT";B.Q="eT";C.1j("5Z").J(B);l 4E=C.K("B");4E.id="oN";4E.Q="nX 47";B.J(4E);l 7d=C.K("B");7d.id="oY";7d.Q="nW 47";4E.J(7d);l 4S=C.K("B");4S.id="ml";4S.Q="nX 38";B.J(4S);l 7f=C.K("B");7f.id="mj";7f.Q="nW 38";4S.J(7f)}};1E.A.cM=k(){if(!j.q.pk.7c)N;l B=C.1j("cX");if(!B){l o=j;B=C.K("B");B.id="cX";B.Q="cX";C.1o("1w")[0].J(B);28(l i=0;i<j.q.pk.7c.1l;i++){l 7h=j.q.pk.7c[i];l O=C.K("O");O.1T=7h.F;B.J(O);(k(O,id){O.V("1Y",k(e){o.q.pk.io.nl(id);e.1V()})})(O,7h.id)}};B.1i.2j=""};1E.A.6Q=k(){l B=C.1j("cX");if(B)B.1i.2j="35"};1E.A.nx=k(1u,7h){l 3A=(1u==j.q.1u);l x=(3A?j.q.I.3H(2z.2J()*24):j.q.I.3H(60+2z.2J()*24));l y=j.q.I.iT(20+2z.2J()*30);l o6=(3A?1:-1)*j.q.I.6B(20);l of=(3A?"B8":"Be");l O=C.K("O");O.id="Bk"+26 4K().5t();O.Q="Bj";O.1T=7h.F;O.1i.3w=x;O.1i.2D=y;l a3=k(){if(!O)N;O.2e.1Z(O);O=E};C.1o("1w")[0].J(O);if(j.q.I.29()=="wx"&&j.q.I.gx()){O.Q+=" "+of;O.V("4o",a3);O.V("4n",a3)}L{1Q(k(){7l("#"+O.id).o7(1.25).6U("0.Bl").eK().o7(0.8).6U("0.1s").eK().Bg("4u").x(o6).6U("1.5s").eK().7q("Bi",0).6U("0.1s").a2().a2().a2().7M();1Q(a3,7j)},20)}};1E.A.a0=k(){if(!j.q.pk.S)N;l O=C.1j("9T");if(!O){O=C.K("O");O.id="9T";O.Q="9T";O.1T=j.q.1B+"/"+j.q.pk.S+"/B5.2f";C.1o("1w")[0].J(O)};O.1i.2j=""};1E.A.a4=k(){l O=C.1j("9T");if(O)O.1i.2j="35"};1E.A.hd=k(){};1E.A.eF=k(2R){l B=C.1j("aw");if(!B){B=C.K("B");B.id="aw";B.Q="aw";C.1j("5Z").J(B)};B.1c=2R};1E.A.9N=k(2R){l B=C.1j("aw");if(B)B.1c=2R};1E.A.ar=k(1e){l 1u=1e.1I;l B=C.1j("eQ"+1u);l 3s,2S,1X,1p;if(!B){B=C.K("B");B.id="eQ"+1u;B.Q="oL";C.1j("5Z").J(B);3s=C.K("O");3s.Q="oS";3s.1T=j.q.I.5V(1e.3n);B.J(3s);2S=C.K("B");2S.Q="oK";2S.1c=1e.2Q;B.J(2S);1X=C.K("B");1X.id="eM"+1u;1X.Q="oM";l 1K=C.K("B");1K.Q="AT";1K.1c="AS";1X.J(1K);1p=C.K("B");1p.Q="eN";1X.J(1p);C.1j("5Z").J(1X)}L{3s=B.7r("oS")[0];2S=B.7r("oK")[0];1X=C.1j("eM"+1u);1p=1X.7r("eN")[0]};B.Q="oL "+(1u==j.q.1u?"47":"38");1X.Q="oM "+(1u==j.q.1u?"47":"38");1p.1c="0/0"};1E.A.eR=k(1e){l 1u=1e.1I;l p=C.1j("eQ"+1u);if(p){C.1j("5Z").1Z(p)}};1E.A.eU=k(){28(l i=0;i<j.q.pk.1U.1l;i++){l 1e=j.q.pk.1U[i];j.q.pk.7t(1e.1I,0,0)}};1E.A.7t=k(1e){l 1u=1e.1I;l B=C.1j("eM"+1u);if(B){l 1p=B.7r("eN")[0];1p.1c=1e.1X+"/"+1e.4T};if(j.q.pk.1U.1l==2){l 47=j.q.pk.1U[0].1I==j.q.1u?j.q.pk.1U[0]:j.q.pk.1U[1];l 38=j.q.pk.1U[0].1I==j.q.1u?j.q.pk.1U[1]:j.q.pk.1U[0];j.eP(47.1X,47.4T,38.1X,38.4T)}L{j.eP(0,0,0,0)}};1E.A.eP=k(7d,4E,7f,4S){if(4E==0)4E=0.1;if(4S==0)4S=0.1;l oX=(4E*4l/(4E+4S)).91(2)+"%";l mk=(7d*4l/4E).91(2)+"%";l mi=(4S*4l/(4E+4S)).91(2)+"%";l mq=(7f*4l/4S).91(2)+"%";7l("#oN").7q("3I",oX).7M();7l("#oY").7q("3I",mk).7M();7l("#ml").7q("3I",mi).7M();7l("#mj").7q("3I",mq).7M()};1E.A.mr=k(){j.4t();l B=C.1j("ax");if(!B){B=C.K("B");B.id="ax";B.Q="ax";B.1c="AV";C.1o("1w")[0].J(B)};B.1i.2j=""};1E.A.mn=k(){l B=C.1j("ax");if(B)B.2e.1Z(B)};1E.A.mo=k(P){j.aO();j.4t();l o=j;l B=C.K("B");B.id="fw";B.Q="fw";C.1o("1w")[0].J(B);l 1U=C.K("B");1U.Q="AU";B.J(1U);l 1X=C.K("B");1X.Q="AP";B.J(1X);l bo=C.K("B");bo.Q="AO";1X.J(bo);l i;l bn=0;28(i=0;i<P.1y.1l;i++){bn+=P.1y[i].1X};l 38=E;28(i=0;i<P.1y.1l;i++){l 1e=j.q.pk.ef(P.1y[i].1I);l 3A=(1e.1I==j.q.1u);if(!3A)38=1e;l 1N=j.au("AQ"+(3A?"47":"38"),1e);1N.Q+=(3A?" AW":" B2");1U.J(1N);if(1e.1I!=j.q.1u){l 3e=C.K("B");3e.Q="B1";1N.J(3e);(k(1N,1e){1N.V("1Y",k(e){o.q.pk.2H(1e);e.1V()})})(1N,1e)};l 8X=C.K("B");8X.id="B4"+(3A?"47":"38");8X.Q="B3 "+(3A?"47":"38");8X.1c=P.1y[i].1X;1X.J(8X);l 9e=C.K("B");9e.id="AY"+(3A?"47":"38");9e.Q=(3A?"47":"38");bo.J(9e);l 3I=(bn==0?"50%":(P.1y[i].1X*4l/bn)+"%");(k(id,3I){1Q(k(){7l("#"+id).7q("3I",3I).6U("1.2s").7M()},4l)})(9e.id,3I)};l fp=(P.6R==j.q.1u);l 6R=C.K("B");6R.Q="B0 "+(fp?"AZ":"va");6R.1c=(fp?"v9":"vc");B.J(6R);l 2t=C.K("B");2t.Q="vb";1Q(k(){B.J(2t)},4D);l 5I=C.K("a");5I.Q="v6";5I.1c="fy";5I.V("1Y",k(e){o.q.pk.mD();e.1V()});2t.J(5I);l 5M=C.K("a");5M.Q="94";5M.1c="v5";5M.V("1Y",k(e){o.q.pk.mJ();e.1V()});2t.J(5M);l 9f=C.K("a");9f.Q="94";9f.1c="v8";9f.V("1Y",k(e){o.mt();e.1V()});2t.J(9f);l 9b=C.K("a");9b.Q="94";9b.1c="v7";9b.V("1Y",k(e){o.q.pk.2H(38);e.1V()});2t.J(9b);l 96=C.K("a");96.Q="94";96.1c="vi";96.V("1Y",k(e){o.q.I.oV("vh：",k(v){o.q.2H.mH(o.q.1u,38.1I,v,k(){o.q.I.2k("vk")})})});2t.J(96);l 9a=C.K("a");9a.Q="94";9a.1c="gU";9a.V("1Y",k(e){R.19=o.q.1B+"/1h/4I.1L?r="+2z.2J()});2t.J(9a)};1E.A.aO=k(){l B=C.1j("fw");if(B)B.2e.1Z(B)};1E.A.mt=k(){j.8J();l o=j;l 1d=C.K("B");1d.id="f5";1d.Q="f5";1d.V("1Y",k(e){o.8J();e.1V()});C.1o("1w")[0].J(1d);l B=C.K("B");B.id="f3";B.Q="f3";C.1o("1w")[0].J(B);l aQ=C.K("a");aQ.Q="vj";aQ.V("1Y",k(e){o.8J();o.q.pk.fg();e.1V()});B.J(aQ);l aH=C.K("a");aH.Q="ve";aH.V("1Y",k(e){o.8J();R.19=o.q.1B+"/3M.1L?S=pk";e.1V()});B.J(aH)};1E.A.8J=k(){l B=C.1j("f3");if(B)B.2e.1Z(B);l 1d=C.1j("f5");if(1d)1d.2e.1Z(1d)};1n=k(q,ui){j.q=q;j.ui=ui;j.6o;j.b4="vd.vg.vf.v4";j.7C=uS;j.6A=1f;j.dw=1f;j.dq=E;j.2p={9O:10,7D:20,lR:30,2P:50};j.1A={e9:uU,e4:uT,e7:uO,nF:uN,dE:uQ,dH:uP,dD:v0,lX:uZ,mW:v3,dF:v1,mZ:uW,dx:uV,mS:uY,mM:uX,mN:vH,dG:vG,dC:vJ,dy:vI,mK:vD,dz:vC,mL:vF,mQ:vE,mR:vP,mO:vO,dB:vR,ne:vQ,nf:vL,dA:vK,dP:vN,dO:vM,dQ:zx,dS:vq,dR:vp,dN:vs,dJ:vr,dI:vm,n3:vl,dM:vo,nN:vn,n9:vy,dL:vx}};1n.A.dm=k(){2x{l o=j;j.6o=26 vz("ws://"+j.b4+":"+j.7C);j.6o.vt=k(){o.lI()};j.6o.vw=k(e){o.lG(e)};j.6o.vv=k(e){o.m1(e)};j.6o.oI=k(e){o.m4(e)}}2w(e){}};1n.A.u1=k(){j.6o.2n();j.6A=1f;j.q.pk.1S=j.q.pk.2C.aM};1n.A.lI=k(){1r.1x("u0 b4 = "+j.b4+", 7C = "+j.7C);j.6A=1g;u3(j.dq);j.q.pk.1S=j.q.pk.2C.7y;j.lU();if(j.dw){}};1n.A.42=k(){j.q.pk.42()};1n.A.lG=k(e){j.6A=1f;1r.1x("u2.");j.q.pk.1S=j.q.pk.2C.aM};1n.A.m4=k(e){1r.1x("iQ: "+e.v)};1n.A.tX=k(){if(j.6A){N 1g}L{N 1f}};1n.A.tW=k(){l o=j;j.dq=tZ(k(){if(!o.6A){o.dw=1g;o.dm()}},4D)};1n.A.m1=k(e){2x{1r.7X("%c"+e.v,"3j: #tY")}2w(e){1r.1x("←"+e.v)};l P=E;2x{P=2N.51(e.v)}2w(e){1r.1x(e)};if(P!=E){j.a7(P)}};1n.A.2i=k(P){if(j.6A){l 3D=2N.3o(P);2x{j.6o.2i(3D);2x{1r.u9("%c"+3D,"3j: #ub")}2w(e){1r.1x("→"+3D)};N 1g}2w(e){1P(e)}}L{1P("u5")};N 1f};1n.A.lU=k(T){N j.2i({ch:j.2p.9O,2l:j.1A.e9,T:1a.T})};1n.A.lV=k(){l P={ch:j.2p.2P,2l:j.1A.e4,1I:j.q.1u};if(j.q.pk.S)P.S=j.q.pk.S;N j.2i(P)};1n.A.lS=k(){N j.2i({ch:j.2p.2P,2l:j.1A.e7})};1n.A.do=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dE})};1n.A.lY=k(1u,S){N j.2i({ch:j.2p.2P,2l:j.1A.dD,1I:1u,S:S})};1n.A.lZ=k(1u){N j.2i({ch:j.2p.2P,2l:j.1A.dH,1I:1u})};1n.A.lW=k(){l P={ch:j.2p.2P,2l:j.1A.lX,1I:j.q.1u};if(j.q.pk.S)P.S=j.q.pk.S;N j.2i(P)};1n.A.nv=k(1u){N j.2i({ch:j.2p.2P,2l:j.1A.dF,1I:1u})};1n.A.bt=k(){l P={ch:j.2p.2P,2l:j.1A.dG,1I:j.q.1u,3m:j.q.pk.2h};if(j.q.pk.S)P.S=j.q.pk.S;N j.2i(P)};1n.A.nw=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dC,1I:j.q.1u,S:j.q.pk.S,3m:j.q.pk.3m})};1n.A.nt=k(4r){N j.2i({ch:j.2p.2P,2l:j.1A.dy,1I:j.q.1u,mY:4r})};1n.A.nz=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dx})};1n.A.nA=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dz})};1n.A.e3=k(v){N j.2i({ch:j.2p.2P,2l:j.1A.dB,1X:v.1X})};1n.A.ny=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dA})};1n.A.bb=k(S){l P={ch:j.2p.2P,2l:j.1A.dP};if(S)P.S=S;N j.2i(P)};1n.A.nm=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dO})};1n.A.dY=k(3S,S){l P={ch:j.2p.2P,2l:j.1A.dQ,3S:3S};if(S)P.S=S;N j.2i(P)};1n.A.nk=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dS})};1n.A.nl=k(id){N j.2i({ch:j.2p.2P,2l:j.1A.dR,u4:id})};1n.A.nq=k(){l P={ch:j.2p.2P,2l:j.1A.dN,1I:j.q.1u};if(j.q.pk.S)P.S=j.q.pk.S;N j.2i(P)};1n.A.dX=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dJ,3m:j.q.pk.2h})};1n.A.np=k(S){N j.2i({ch:j.2p.2P,2l:j.1A.dM,S:S})};1n.A.nM=k(){N j.2i({ch:j.2p.2P,2l:j.1A.nN})};1n.A.nK=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dI})};1n.A.nL=k(){N j.2i({ch:j.2p.2P,2l:j.1A.dL})};1n.A.a7=k(P){if(P.2X){j.nD(P)}L if(!P.ch||!P.2l){1r.1x("u7")}L{2L(P.ch){18 j.2p.9O:j.nE(P);W;18 j.2p.2P:j.nH(P);W;48:1r.1x("u6：ch = "+P.ch);W}}};1n.A.nD=k(P){1r.1x(P)};1n.A.nE=k(P){2L(P.2l){18 j.1A.e9:j.nB(P);W;48:1r.1x("n7：2l = "+P.2l);W}};1n.A.nB=k(P){if(P.2v){j.42()}L{1P("T tK")}};1n.A.nH=k(P){2L(P.2l){18 j.1A.e4:j.n8(P);W;18 j.1A.e7:j.n4(P);W;18 j.1A.nF:j.n6(P);W;18 j.1A.dE:j.nb(P);W;18 j.1A.dD:j.nj(P);W;18 j.1A.mW:j.nd(P);W;18 j.1A.dF:j.9X(P);W;18 j.1A.dH:j.mP(P);W;18 j.1A.mZ:j.aa(P);W;18 j.1A.dG:j.mX(P);W;18 j.1A.dC:j.mT(P);W;18 j.1A.dy:j.nG(P);W;18 j.1A.dx:j.ak(P);W;18 j.1A.mS:j.am(P);W;18 j.1A.mM:j.ae(P);W;18 j.1A.mN:j.af(P);W;18 j.1A.mK:j.at(P);W;18 j.1A.dz:j.9m(P);W;18 j.1A.mL:j.nC(P);W;18 j.1A.mQ:j.nJ(P);W;18 j.1A.mR:j.nO(P);W;18 j.1A.mO:j.nR(P);W;18 j.1A.dB:j.nQ(P);W;18 j.1A.ne:j.no(P);W;18 j.1A.nf:j.a9(P);W;18 j.1A.dA:j.9Y(P);W;18 j.1A.dP:j.aE(P);W;18 j.1A.dO:j.bk(P);W;18 j.1A.dQ:j.bp(P);W;18 j.1A.dS:j.nn(P);W;18 j.1A.dR:j.ns(P);W;18 j.1A.dN:j.bd(P);W;18 j.1A.dJ:j.nu(P);W;18 j.1A.dI:j.bu(P);W;18 j.1A.n3:j.bx(P);W;18 j.1A.dM:j.lT(P);W;18 j.1A.n9:j.m7(P);W;18 j.1A.dL:j.aS(P);W;48:1r.1x("n7：2l = "+P.2l);W}};1n.A.n8=k(P){1r.1x("tJ：S = "+(P.S||E)+", tM = "+P.3v)};1n.A.n4=k(P){1r.1x("tL")};1n.A.n6=k(P){j.q.pk.n5(P.1e)};1n.A.nb=k(P){if(P.1e){j.q.pk.nh(P.1e,P.U)}L{j.q.pk.ng()}};1n.A.nj=k(P){j.q.pk.ni(P.1I,P.S)};1n.A.nd=k(P){j.q.pk.nc(P.1e)};1n.A.mP=k(P){1r.1x("tG 1I = "+P.1I+" tF")};1n.A.9X=k(P){j.q.pk.9X(P.1I,P.S,P.mY)};1n.A.aa=k(P){j.q.pk.aa(P.1e,P.1S)};1n.A.mX=k(P){if(P.1y==1){j.q.pk.n0(P.1S)}L{j.q.pk.mU(P.1y)}};1n.A.mT=k(P){j.q.pk.mV(P.1S)};1n.A.nG=k(P){j.q.pk.nI(P.3m)};1n.A.ak=k(P){j.q.pk.ak(P.1e)};1n.A.am=k(P){j.q.pk.am(P)};1n.A.ae=k(P){j.q.pk.ae(P.1S)};1n.A.af=k(P){j.q.pk.af(P.1U)};1n.A.at=k(P){j.q.pk.at(P.1S)};1n.A.9m=k(P){j.q.pk.9m(P.1U)};1n.A.nC=k(P){j.q.pk.hd()};1n.A.nJ=k(P){j.q.pk.nP(P.2a)};1n.A.nO=k(P){j.ui.9N(P.2R)};1n.A.nR=k(P){j.ui.9N(P.tI)};1n.A.nQ=k(P){j.q.pk.7t(P.1I,P.1X,P.4T)};1n.A.no=k(P){j.q.pk.nr(P.1S)};1n.A.a9=k(P){j.q.pk.a9(P)};1n.A.9Y=k(P){j.q.pk.9Y(P)};1n.A.aE=k(P){j.q.pk.aE(P.1e,P.U||E)};1n.A.bk=k(P){j.q.pk.bk(P.1e)};1n.A.bp=k(P){j.q.pk.bp(P.1e,P.3S,P.U||E)};1n.A.nn=k(P){j.q.pk.7c=P.2a};1n.A.ns=k(P){j.ui.nx(P.1I,P.7h)};1n.A.bd=k(P){j.q.pk.bd(P.3m,P.4U)};1n.A.nu=k(P){1r.1x("tH："+2N.3o(P))};1n.A.bu=k(P){j.q.pk.bu(P.2a)};1n.A.bx=k(P){j.q.pk.bx(P.2a)};1n.A.lT=k(P){j.q.pk.m8(P.1I,P.U)};1n.A.m7=k(P){j.q.pk.m2(P.S)};1n.A.aS=k(P){j.q.pk.aS(P.2a)};1J=k(q){j.q=q};1J.A.36=k(38,G){if(38==8R||38==E){N G}L{if(G){28(l 2S in G){38[2S]=G[2S]}};N 38}};1J.A.fQ=k(){l gu=26 dj("jn","tR","tU","9z","fR","tT","tO","tN","tQ","tP","jj","uC");l ua=4Y.4W.uB();28(l i=0;i<gu.1l;i++){if(ua.43(gu[i])!=-1){N 1g}};N 1f};1J.A.6t=k(){N/ka|k7|k4|uE/ig.3u(4Y.4W)};1J.A.gx=k(){N/9z|uD/i.3u(4Y.4W)};1J.A.lH=k(){N/uy/ig.3u(4Y.4W)};1J.A.29=k(){l ua=4Y.4W;if(/ux/ig.3u(ua)){N"wx";}L if(/\\lO\\/[\\d\\.]+\\b/ig.3u(ua)){N"qq";}L if(/q/ig.3u(ua)){N"zxs";}L if(/2K/ig.3u(ua)){N"2K";}L if(/jJ/ig.3u(ua)){N"uc";}L if(/hs/ig.3u(ua)){N"7A";}L if(/Z\\.uA\\.gk/ig.3u(ua)){N"gk";}L if(/lQ/ig.3u(ua)){N"aX";}L if(/uz/ig.3u(ua)){N"5R";}L{N"uK";}};1J.A.5f=k(){l 1y=E;l 2g=E;l ua=4Y.4W;2L(j.29()){18"wx":1y=ua.3v(/uJ\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"qq":1y=ua.3v(/\\lO\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"zxs":1y=ua.3v(/1k\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"2K":1y=ua.3v(/uM\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"uc":1y=ua.3v(/uL\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"7A":1y=ua.3v(/hs\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"aX":1y=ua.3v(/lQ\\/([^\\s]+)/i);if(1y)2g=1y[1];W;18"5R":2g=0;W};N 2g};1J.A.8C=k(lL,lK){l r1=lL.3v(/(\\d+)(?!\\d)/ig);l r2=lK.3v(/(\\d+)(?!\\d)/ig);if(r1==E)N 1f;if(r2==E)N 1g;l 1y=1g;28(l i=0;i<99;i++){if(r2.1l<i+1){1y=1g;W};l n1=8W(r1[i]);l n2=8W(r2[i]);if(n1!=n2){1y=(n1>n2);W}};N 1y};1J.A.8t=k(){if(1a.8t)N 1g;if(19.gr=="uH:")N 1g;if(19.2V=="uw"||19.2V=="uk.0.0.1"||19.2V=="U")N 1g;N 1f};1J.A.mx=k(){if(R.19.2y){N R.19.2y}L{N R.19.gr+"//"+R.19.2V+(R.19.7C?":"+R.19.7C:"")}};1J.A.uj=k(){if(19.2y&&19.aG){N 19.2y+19.aG}L{N 19.2I.3v(/[^?#]+/i)[0]}};1J.A.kL=k(){N 19.2I.3v(/[^#;]+/i)[0]};1J.A.8k=k(){if(19.aG){N 19.aG}L{N 19.2I.3v(/(?:Y|cu):\\/\\/[^\\/]+([^?#;]+)/i)[1]}};1J.A.un=k(){N 19.mw};1J.A.34=k(2S){l 67=26 g3("(^|&)"+2S+"=([^&]*)(&|$)","i");l r=R.19.mw.9R(1).3v(67);if(r!=E)N r[2];N E};1J.A.2r=k(F,2S,2E){F=F.2B(/(#.*)/ig,"");l 67=26 g3("([\\?&])"+2S+"=([^&]*)(?=&|$)","i");if(67.3u(F)){N F.2B(67,"$1"+2S+"="+2E)}L{N F+(F.43("?")==-1?"?":"&")+2S+"="+2E}};1J.A.um=k(F,2S){F=F.2B(/(#.*)/ig,"");l 67=26 g3("([\\?&])"+2S+"=([^&]*)(?=&|$)","i");if(67.3u(F)){F=F.2B(67,"");if(F.43("?")==-1)F=F.2B("&","?")};N F};1J.A.19=k(F){R.19=F;l o=j;1Q(k(){C.1K+=".";o.19(F)},4D)};1J.A.mA=k(F){R.19.2B(F);l o=j;1Q(k(){C.1K+=".";o.mA(F)},4D)};1J.A.ms=k(F,D){j.1z("Y://wx.zxs.Z/kK/uf?F="+1v(F),k(v){if(v&&v.2X==0){D&&D.1b(E,v.ue)}})};1J.A.uh=k(3n){if(!3n)N"Y://U.zxs.Z/48.2f";if(3n.43("/0")!=-1){3n=3n.9R(0,3n.1l-2)+"/64"};N 3n};1J.A.5V=k(3n){if(!3n)N"Y://U.zxs.Z/48.2f";if(3n.43("/0")!=-1){3n=3n.9R(0,3n.1l-2)+"/ut"};N 3n};1J.A.8D=k(){l dt=26 4K();dt.us(0);N dt.5t()/3K};1J.A.uv=k(){l dt=26 4K();dt.uu(0,0,0,0);N dt.5t()/3K};1J.A.mf=k(){l 4y=1G[0];l h3=1G[1]||"h2-bm-dd gX:mm:ss";if(2M 4y=="mI"){4y=26 4K(4y*3K)};l 7V=k(6P){6P+="";N 6P.2B(/^(\\d)$/,"0$1")};l gK={h2:4y.mC(),yy:4y.mC().iK().oj(2),M:4y.mF()+1,bm:7V(4y.mF()+1),d:4y.mE(),dd:7V(4y.mE()),gX:7V(4y.ur()),mm:7V(4y.uq()),ss:7V(4y.vS())};N h3.2B(/([a-z])(\\1)*/ig,k(m){N gK[m]})};1J.A.xk=k(){l t=1G[0];l h3=1G[1]||"h2-bm-dd gX:mm:ss";if(t di 4K){t=t.5t()/3K};l 7o="";l 7k=j.8D()-t;if(7k<60){7o="xm"}L if(7k<60*60){7o=2z.mg(7k/60)+"xh"}L if(7k<60*60*24){7o=2z.mg(7k/60/60)+"xg"}L{7o=j.mf(t,"bm-dd")};N 7o};1J.A.71=k(37,G){if(37===8R||37===""||bJ(37))N"";l 3t={6f:1g,ma:1g,3j:E,7a:1f,5o:1f};G=j.36(3t,G);l s=(37/4l).91(2);if(G.3j)s="<3C 1i=\'3j:"+G.3j+"\'>"+s+"</3C>";if(G.7a)s="<b>"+s+"</b>";s=(G.6f?"￥":"")+s+(G.5o?" ":"")+(G.ma?"hl":"");N s};1J.A.cA=k(hj,6d){N 8W((2z.2J()*(6d-hj+1))+hj)};1J.A.6w=k(m9){l 9P="xi";l he="";28(l i=0;i<m9;i++){l n=j.cA(1,9P.1l)-1;he+=9P.9R(n,1)};N he};1J.A.6C=k(){if(!3p.8o){3p.8o=j.6w(40)};N 3p.8o};1J.A.xs=k(X,D){l o=j;j.q.mc(k(v){if(o.q.4m=="mb"){R.19=o.q.1B+"/mb/3r.1L?8S="+o.q.8S;N};if(o.q.4m=="2H"){l 3Z=o.q.v.3Z;o.q.aq(3Z,k(1N){o.5p({X:o.q.1m.1K,5Y:{3f:"xv"+1N.2Q,1O:k(){R.19=o.q.1B+"/1h/2H.1L?3Z="+3Z+"&1C=xu&S="+o.q.S+"&1X="+o.q.1X+"&xp="+1v(o.q.6Y)+"&1K="+o.q.1m.1K}},6b:{3f:"xo"}})});N};if(o.q.4m=="xr"){l 9h=o.q.v.oP;if(9h){o.q.oU(9h,o.q.1m.1K,k(v){o.4O(v)})}}});if(j.q.sp){if(j.q.1m.1K.43(j.q.sp.3R)==-1)j.q.1m.1K+="["+j.q.sp.3R+"]";if(j.q.1m.X.43(j.q.sp.3R)==-1)j.q.1m.X+="["+j.q.sp.3R+"]"};1Q(k(){2L(o.29()){18"wx":o.q.3z();W;18"zxs":18"2K":o.q.3z();W;48:if(o.q.1h){if(gy(X)){D&&D.3Y(E)}};W}},5r);if(j.q.2H){1Q(k(){o.q.2H.an()},3K)}};1J.A.73=k(oQ){if(!oQ)N;if(C.1j("ap"))N;l O=C.K("O");O.id="ap";O.1T="Y://U.zxs.Z/O/xq.oh";O.Q="ap";C.1o("1w")[0].J(O);l 1d=C.K("B");1d.id="gA";1d.Q="gA";C.1o("1w")[0].J(1d);l o=j;1d.V("1Y",k(e){o.gB();e.1V()})};1J.A.gB=k(){l O=C.1j("ap");if(O)O.2e.1Z(O);l 1d=C.1j("gA");if(1d)1d.2e.1Z(1d)};1J.A.5p=k(G){l 3t={1K:E,X:"",4i:[],5Y:E,6b:E};G=j.36(3t,G);j.aj=26 8N(j.q,G);j.aj.45()};1J.A.8O=k(){j.aj&&j.aj.2n()};1J.A.1P=k(X,D){j.5p({X:X,5Y:{3f:"oR",1O:D}})};1J.A.2k=k(X,2R){j.5p({X:X});l o=j;1Q(k(){o.8O()},2R||7j)};1J.A.gy=k(X,ok,2d){j.5p({X:X,5Y:{1O:k(){ok&&ok.1b(E)}},6b:{1O:k(){2d&&2d.1b(E)}}})};1J.A.oV=k(){l 1p=1G[0];l gm="";l D=E;if(2M 1G[1]=="6u")gm=1G[1];if(2M 1G[1]=="k")D=1G[1];if(2M 1G[2]=="k")D=1G[2];l X=1p+"<4g id=\'o8\' 2E=\'"+gm+"\' 69=\'x4\' />";j.5p({X:X,5Y:{1O:k(){l 2E=C.1j("o8").2E;D&&D.1b(E,2E)}},6b:{}})};8N=k(q,G){j.q=q;j.G=G;j.2m()};8N.A.2m=k(){if(j.G.5Y){j.G.4i.3X(j.q.I.36({3f:"ge",3j:"#fK",5u:"#x3",1O:E},j.G.5Y))};if(j.G.6b){j.G.4i.3X(j.q.I.36({3f:"cU",3j:"#fK",5u:"#x6",1O:E},j.G.6b))}};8N.A.45=k(){j.2n();l B=C.K("B");B.id="go";B.Q="go";if(j.G.1K!=E){l 3J=C.K("B");3J.Q="x5";3J.1c=j.G.1K;B.J(3J)};l X=C.K("B");X.Q="x0";X.1c=j.G.X.2B(/\\n/g,"<br/>");B.J(X);if(j.G.4i.1l>0){l 4C=C.K("B");4C.Q="x1";B.J(4C);l o=j;l oa="xc"+j.G.4i.1l;28(l i=0;i<j.G.4i.1l;i++){(k(1M){l a=C.K("a");a.Q=oa;a.1c=1M.3f;if(1M.3j)a.1i.3j=1M.3j;if(1M.5u)a.1i.o3=1M.5u;l 4x=k(e){1M.1O&&1M.1O.3Y(o.q);o.2n();e.1V()};a.V("1Y",4x);a.V("3L",4x);4C.J(a)})(j.G.4i[i])}};C.1o("1w")[0].J(B);l 1d=C.K("B");1d.id="5T";1d.Q="5T";C.1o("1w")[0].J(1d)};8N.A.2n=k(e){l B=C.1j("go");if(B)C.1o("1w")[0].1Z(B);l 1d=C.1j("5T");if(1d)C.1o("1w")[0].1Z(1d);e&&e.1V()};1J.A.5n=k(2R){l 1d=C.K("B");1d.id="5T";1d.Q="5T";C.1o("1w")[0].J(1d);l O=C.K("O");O.id="fP";O.Q="fP";O.1T="Y://U.zxs.Z/O/xb.oh";C.1o("1w")[0].J(O);if(2R){l o=j;1Q(k(){o.od()},2R)}};1J.A.od=k(){l O=C.1j("fP");if(O)C.1o("1w")[0].1Z(O);l 1d=C.1j("5T");if(1d)C.1o("1w")[0].1Z(1d)};1J.A.6W=k(G){j.7Y();l 3t={X:"",2R:10,oe:1f,nV:"cU",9c:E,cD:E,cF:E,6M:E,nS:1f,cG:[]};G=j.36(3t,G);l o=j;l 1p=C.K("B");l 2t=C.K("B");if(G.oe){l 1M=C.K("a");1M.1c=G.nV;1M.V("1Y",k(e){o.7Y();G.9c&&G.9c.1b(E);e.1V()});2t.J(1M)};if(G.cD){j.q.V(G.cD,k(){o.7Y()},1g)};l fY=k(){if(G.cF){1p.1c=G.cF};2t.1c="";if(G.nS){o.7Y()}L{28(l i=0;i<G.cG.1l;i++){l nY={3f:"",3j:"#fK",5u:"#xe",fT:1f,2d:1f,1O:E};l 2G=o.36(nY,G.cG[i]);(k(2G){l 1M=C.K("a");1M.1c=2G.3f;1M.1i.3j=2G.3j;1M.1i.o3=2G.5u;1M.V("1Y",k(e){if(2G.fT){o.6W(G)}L if(2G.2d){o.7Y();G.9c&&G.9c.1b(E)};2G.1O&&2G.1O.1b(E);e.1V()});2t.J(1M)})(2G)}};G.6M&&G.6M.1b(E)};l 1d=C.K("B");1d.id="ho";1d.Q="ho";C.1o("1w")[0].J(1d);l B=C.K("B");B.id="gE";B.Q="gE";C.1o("1w")[0].J(B);l 4J=C.K("B");4J.Q="nZ o4";B.J(4J);l 3a=C.K("B");3a.Q="o1 3a";4J.J(3a);l 4M=C.K("B");4M.Q="nZ o0";B.J(4M);l 2A=C.K("B");2A.Q="o1 2A";4M.J(2A);1p.id="oC";1p.Q="oC";1p.1c=G.X;B.J(1p);3a.1i.7N="7g "+G.2R+"s 4u";3a.1i.7R="4G";3a.1i.7u="7g "+G.2R+"s 4u";3a.1i.7Q="4G";2A.1i.7u="7L "+G.2R+"s 4u";2A.1i.7Q="4G";2A.1i.7N="7L "+G.2R+"s 4u";2A.1i.7R="4G";2A.V("4o",fY);2A.V("4n",fY);2t.id="hm";2t.Q="hm";2t.1i.2D=(j.9l(20)+j.6B(60+3))+"px";C.1o("1w")[0].J(2t)};1J.A.7Y=k(){l B=C.1j("gE");if(B)B.2e.1Z(B);l 2t=C.1j("hm");if(2t)2t.2e.1Z(2t);l 1d=C.1j("ho");if(1d)1d.2e.1Z(1d)};1J.A.ox=k(G){l 3t={2S:j.cA(x8,x7)+"",2R:60,c6:E,6M:E};G=j.36(3t,G);l o=j;l 5D=k(){G.c6&&G.c6.1b(E,G.2R);if(G.2R<=0){G.6M&&G.6M.1b(E)}L{o["oD"+G.2S]=1Q(k(){G.2R--;5D()},3K)}};5D()};1J.A.hg=k(2S){8V(j["oD"+2S])};1J.A.9V=k(2a,D){if(!2a)N;if(2M 2a=="6u")2a=[2a];if(2a.1l==0){D&&D.1b(E);N};l F=2a.lz();l 3s=C.1o("3s")[0]||C.oi;l 7B=C.K("cE");l o=j;7B.xa=k(){o.9V(2a,D)};7B.oI=k(){};7B.g0=1g;7B.1T=F;3s.J(7B)};1J.A.1z=k(){l G={bN:"x9",F:"",v:E,1q:"P",1H:E};2L(1G.1l){18 1:if(2M 1G[0]=="6u")G.F=1G[0];if(2M 1G[0]=="4F")G=j.36(G,1G[0]);W;18 2:G.F=1G[0];G.1H=1G[1];W};G.F=j.2r(G.F,"bG",2z.2J());26 gQ(j.q,G.bN,G.F,G.v,G.1q,G.1H)};1J.A.jo=k(F,v,78,1H){F=j.2r(F,"bG",2z.2J());26 oo(F,v,78,1H).oc()};gQ=k(q,bN,F,v,1q,1H){j.q=q;j.44=E;if(R.oJ){j.44=26 oJ()}L{j.44=26 xS("xR.xU")};j.F=F;j.1q=1q;j.1H=1H;l o=j;j.44.xT=k(){o.D.3Y(o)};if(2M v=="4F"&&v!=E){l a=[];28(l p in v){a.3X(p+"="+or(v[p]))};v=a.oG("&")};2x{j.44.45(bN,F,1g);j.44.2i(v)}2w(e){1r.1x(F);1r.1x(e)}};gQ.A.D=k(){if(j.44.gH==4&&j.44.1S==7w){l v=E;2L(j.1q){18"1p":v=j.44.gT;W;18"P":2x{v=2N.51(j.44.gT)}2w(e){v=j.44.gT};W};j.1H&&j.1H.1b(j.44,v)}L if(j.44.gH==4&&j.44.1S!=7w){8p.3X(["lk","1z","3b","["+j.44.gH+","+j.44.1S+"]"+j.F])}};oo=k(F,v,78,1H,h1){l 39=1f;l 7x=C.1o("3s")[0]||C.oi;l 98=C.K("cE");l 7b="7b"+(2z.2J()+"").oj(2);l os=k(){if(7x!=E){7x.1Z(98);2x{xO R[7b]}2w(ex){};7x=E}};l 2m=k(){98.hK="hL-8";7x.hx(98,7x.xQ);R[7b]=k(ow){39=1g;1H(ow)};78=78||"D";if(F.43("?")>0){F=F+"&"+78+"="+7b}L{F=F+"?"+78+"="+7b};if(2M v=="4F"&&v!=E){28(l p in v){F=F+"&"+p+"="+or(v[p])}}};l 5D=k(){if(2M R[7b]=="k"){os()};if(2M h1=="k"&&39==1f){h1()}};j.oc=k(){2m();98.1T=F;R.1Q(5D,gi)}};1J.A.41=k(){l B=C.K("B");B.id="lA";l 4p=R.3F>R.3h;B.Q="lA"+(4p?" pc":"");if(j.q.h7){B.1c="<O ca=\'y2\' 1T=\'Y://U.zxs.Z/O/il.2f\' /><O ca=\'ik\' 1T=\'Y://U.zxs.Z/"+j.q.S+"/ik.2f\' />"}L{B.1c="<O ca=\'y1\' 1T=\'Y://U.zxs.Z/O/il.2f\' />"};if(j.q.sp&&j.q.sp.5H){B.1c+="<O ca=\'xW\' 1T=\'"+j.q.sp.5H+"\' />"};C.1o("1w")[0].J(B);l 4v=((j.29()=="zxs"||j.29()=="2K")?3K:4D);l o=j;1Q(k(){C.1o("1w")[0].1Z(B);l hc=k(){o.q.5E=1g;o.q.2O("5E")};l a=C.1j("75");if(a){a.Q="75 gJ";l hn=k(){a.Q="75 co";hc();l O=C.1j("gI");if(O){O.Q="gI it";l gR=k(){1Q(k(){O.Q="gI gG"},3K)};O.V("4o",gR);O.V("4n",gR)}};a.V("4o",hn);a.V("4n",hn)}L{hc()}},4v)};1J.A.ha=k(){l O=C.K("O");O.1T=j.q.sp.41;O.1i.i5="xV";O.1i.xY=xX;O.1i.3I="4l%";O.1i.3w=0;O.1i.2D=0;C.1o("1w")[0].J(O);if(j.q.sp.3R){C.1K=j.q.sp.3R};l o=j;1Q(k(){C.1o("1w")[0].1Z(O);if(o.q.sp.3R){C.1K=o.q.sp.3R};o.q.5E=1g;o.q.2O("5E")},4D)};1J.A.hh=k(){l F;l o=j;if(j.q.U.hh){F="Y://pp.zxs.Z/ad/xA?T=&F=xD";j.1z(F,k(v){if(v&&v.1l>0){l hb=v[0].xC;l ad=hb[o.cA(0,hb.1l-1)];l 1d=C.K("B");1d.Q="xx";C.1o("1w")[0].J(1d);l B=C.K("B");B.Q="xw";C.1o("1w")[0].J(B);l O=C.K("O");O.Q="xz";O.1T=ad.O;B.J(O);l fX=k(e){R.19=ad.F;e.1V()};O.V("1Y",fX);O.V("3L",fX);l x=C.K("O");x.Q="xy";x.1T="Y://U.zxs.Z/O/2n.2f";B.J(x);l 2n=k(e){B.2e.1Z(B);1d.2e.1Z(1d);e.1V()};x.V("1Y",2n);x.V("3L",2n);1d.V("1Y",2n);1d.V("3L",2n)}})};if(j.q.H=="uc"&&j.29()!="uc"){F="Y://wx.zxs.Z/pm/i9.5O?H="+j.q.H;j.1z(F,k(v){if(v.ad){l O=C.K("O");O.id="ic";O.Q="ic";O.1T=v.ad.3N;O.V("1Y",k(e){R.19="Y://wx.zxs.Z/pm/1O.5O?id="+v.ad.id;e.1V()});C.1o("1w")[0].J(O)}})};if(j.q.H=="7A"&&j.29()!="7A"){l iC=(j.34("xI")=="1");F="Y://wx.zxs.Z/pm/i9.5O?H="+j.q.H;j.1z(F,k(v){if(v.ad){l O=C.K("O");O.id="ia";O.Q="ia";O.1T=v.ad.3N;O.V("1Y",k(e){if(iC){if(o.29()=="wx"){l 2k=C.K("O");2k.id="iW";2k.Q="iW";2k.1T="Y://U.zxs.Z/O/"+(o.6t()?"xL.2f":"xK.2f");C.1o("1w")[0].J(2k)}L{R.19="xF://"}}L{R.19="Y://wx.zxs.Z/pm/1O.5O?id="+v.ad.id};e.1V()});C.1o("1w")[0].J(O)}})}};1J.A.6B=k(n){N(cg(n)*R.3F/4l)};1J.A.9l=k(n){N(cg(n)*R.3h/4l)};1J.A.3H=k(n){N(cg(n)*R.3F/4l)+\'px\'};1J.A.iT=k(n){N(cg(n)*R.3h/4l)+\'px\'};1J.A.4O=k(52){if(j.q.9A()){1P("[wf]\\n"+j.fS(52))}};1J.A.fS=k(52,5Q){5Q=5Q||"";l X="";if(2M 52=="4F"&&52!=E){28(l 2G in 52){if(2M 52[2G]=="4F"&&52[2G]!=E)X+=5Q+2G+" = \\n"+5Q+"(\\n"+j.fS(52[2G],5Q+"    ")+5Q+")\\n";L X+=5Q+2G+" = "+52[2G]+"\\n"}}L{X+=5Q+52};N X};1J.A.fN=k(g8,j5){l iv=9C.gS.fO.51(g8);l j3=9C.gS.fO.51(j5);l iD=9C.wg.fN(j3,9C.gS.fO.51(g8),{iv:iv,6D:9C.6D.wa});N iD.iK()};1J.A.9F=k(){l 1C=E;l 2E=E;l 5q=E;l D=E;2L(1G.1l){18 1:1C=1G[0];W;18 2:1C=1G[0];if(!bJ(1G[1]))2E=1G[1];if(2M 1G[1]=="k")D=1G[1];W;18 3:1C=1G[0];2E=1G[1];if(2M 1G[2]=="6u")5q=1G[2];if(2M 1G[2]=="k")D=1G[2];W;18 4:1C=1G[0];2E=1G[1];5q=1G[2];D=1G[3];W};if(1C==E){j.4O("9F iQ: wn 1C");N};l F="Y://wx.zxs.Z/45/9F?1C="+1v(1C);if(2E!=E)F=j.2r(F,"2E",2E);if(5q!=E)F=j.2r(F,"5q",1v(5q));if(j.q.S)F=j.2r(F,"S",j.q.S);if(1a.1W)F=j.2r(F,"2v",1a.1W);j.1z(F,k(v){if(v.1H){D&&D.1b(E,v.wm)}L{D&&D.1b(E,0)}})};1J.A.gl=k(){if(j.8t())N;l iR=3p.iL||0;l ga=26 4K().5t();if(ga-iR<4D)N;L 3p.iL=ga;l 8i=j.6w(10);l F="Y://1x.zxs.Z/1h/wp/"+8i+"?F="+1v(19.2I)+"&hZ="+1v(j.8k());if(j.q.S)F+="&S="+j.q.S;if(1a.T)F+="&T="+1a.T;if(1a.2q)F+="&4q="+1a.2q;F+="&8o="+j.6C();if(j.gb)F+="&gg="+j.gb;l o=j;j.1z(F,k(v){if(v&&v.gg){o.gb=v.gg}});1Q(k(){o.gl()},gi)};1J.A.hS=k(){if(j.8t())N;l 8i=j.6w(10);l F="Y://1x.zxs.Z/1x/wo/"+8i+"?F="+1v(19.2I)+"&hZ="+1v(j.8k())+"&6X="+19.2V;if(1a.1W)F=j.2r(F,"id",1a.1W);if(1a.T)F=j.2r(F,"T",1a.T);if(j.q.S)F=j.2r(F,"S",j.q.S);if(j.q.H)F=j.2r(F,"H",j.q.H);F=j.2r(F,"wj",j.29());if(j.5f())F=j.2r(F,"wi",j.5f());if(j.q.2c){l 9w=j.q.2c.9w||"";l 9u=j.q.2c.9u||"";l 4X=j.q.2c.4X||"";if(9w||9u||4X){F=j.2r(F,"9w",1v(9w));F=j.2r(F,"9u",1v(9u));F=j.2r(F,"4X",1v(4X))}};j.1z(F,k(v){})};1J.A.wl=k(G){l 3t={wk:{},g7:{id:w9}};G=j.36(3t,G);2x{l hI=(("cu:"==C.19.gr)?" cu://":" Y://");l 8h=C.K("cE");8h.1q="1p/cc";8h.g0=1g;8h.hK="hL-8";8h.1T=hI+"s5.g7.Z/w0.j9?id="+G.g7.id+"&g0=1";l fW=C.1o("cE")[0];fW.vZ.hx(8h,fW)}2w(e){1r.3d(e)}};2W=k(q){j.q=q;j.2g=E;j.3q=1f;j.3g=E;j.4L=E;j.2m()};2W.A.2m=k(){j.2g=j.q.I.5f();j.hy()};2W.A.74=k(2g){N j.q.I.8C(j.2g,2g)};2W.A.vU=k(){l o=j;C.V("vT",k vW(){cN.on("2Z:2T:vV",k(hV){cN.hT("w6",{"hX":o.q.1m.3N,"23":o.q.1m.23,"8u":o.q.1m.X,"1K":o.q.1m.1K},k(2U){if(2U.hz=="w5:2d"){o.8Z()}L{o.92()}})});cN.on("2Z:2T:fI",k(hV){cN.hT("w2",{"hX":o.q.1m.3N,"w1":"hU","w4":"hU","23":o.q.1m.23,"8u":o.q.1m.X,"1K":o.q.1m.1K},k(2U){if(2U.hz=="w3:2d"){o.8Z()}L{o.92()}})})},1f)};2W.A.hy=k(){l 8r=j.q.I.8D();l cy=j.q.I.6w(16);l 8i=j.q.I.6w(10);l F=j.q.I.kL();l kM=j.q.8F()+"/kK/wQ/"+8i+"?cy="+cy+"&8r="+8r+"&F="+1v(F);l o=j;j.q.I.1z(kM,k(v){if(v.8B){l 8B=v.8B;wx.gK({4O:1f,wP:v.kF,8r:8r,gt:cy,8B:8B,wK:["wJ","kx","kX","wM","wL","g6","wW","wV","wY","wX","gf","gd","wR","gC","gv","gq","gp","wU","d5","wT","d1","wI","ww","wv","wz","wy","wr","jx","wq","79","wu","wF","wE","wH"]});wx.3q(k(){o.3q=1g;o.q.2O("wG");o.42()});wx.3d(k(2U){})}})};2W.A.42=k(){if(j.q.6N){wx.g6({kl:["8Q:2T:wB","8Q:2T:fI","8Q:2T:kv"]})}L{wx.g6({kl:["8Q:2T:fI","8Q:2T:kv"]})};j.3z()};2W.A.3z=k(){l o=j;wx.kx({1K:j.q.1m.1K,23:j.q.1m.23,lp:j.q.1m.3N,1H:k(){l G={S:o.q.S,H:o.q.H,id:1a.1W||E,4m:1,1q:1,6X:(((o.q.1m.23||"").43(o.q.6e)!=-1)?o.q.6e:E)};o.q.gw(G,k(){o.92()})},2d:k(){o.8Z()}});wx.kX({1K:j.q.1m.1K,8u:j.q.1m.X,23:j.q.1m.23,lp:j.q.1m.3N,1q:"",wA:"",1H:k(){l G={S:o.q.S,H:o.q.H,id:1a.1W||E,4m:2,1q:1,6X:(((o.q.1m.23||"").43(o.q.6e)!=-1)?o.q.6e:E)};o.q.gw(G,k(){o.92()})},2d:k(){o.8Z()}})};2W.A.2T=k(){j.3z();if(j.q.S){j.q.fU()}};2W.A.92=k(){8p.3X(["lk","wD","wC"]);j.q.gj();j.3g&&j.3g.3Y(j.q);};2W.A.8Z=k(){j.4L&&j.4L.3Y(j.q);};2W.A.d5=k(D){wx.d5({5i:1,1H:k(2U){l 5j=2U.5j;D&&D.1b(E,5j[0])}})};2W.A.wt=k(D){wx.d5({5i:9,1H:k(2U){l 5j=2U.5j;D&&D.1b(E,5j)}})};2W.A.d1=k(2Y,D){wx.d1({2Y:2Y,l1:1,1H:k(2U){l 6j=2U.6j;D&&D.1b(E,6j)}})};2W.A.wS=k(5j,D){if(!(5j di dj)||5j.1l==0)N;l a=5j.lw();l b=[];l o=j;l gn=k(){if(a.1l>0){l 2Y=a.lz();o.d1(2Y,k(6j){b.3X(6j);gn()})}L{D&&D.1b(E,b)}};gn()};2W.A.wN=k(d3,D){l F="Y://1h.zxs.Z/lv.5O?db="+d3;j.q.I.1z(F,k(v){D&&D.1b(E,v[0])})};2W.A.wO=k(5e,D){if(!(5e di dj)||5e.1l==0)N;l F="Y://1h.zxs.Z/lv.5O";28(l i=0;i<5e.1l;i++){F+=((i==0?"?":"&")+"db="+5e[i])};j.q.I.1z(F,k(v){D&&D.1b(E,v)})};2W.A.w7=k(d3,D){l F="Y://1h.zxs.Z/ls.5O?db="+d3;j.q.I.1z(F,k(v){D&&D.1b(E,v[0])})};2W.A.w8=k(5e,D){if(!(5e di dj)||5e.1l==0)N;l F="Y://1h.zxs.Z/ls.5O";28(l i=0;i<5e.1l;i++){F+=((i==0?"?":"&")+"db="+5e[i])};j.q.I.1z(F,k(v){D&&D.1b(E,v)})};2W.A.gf=k(D){wx.gf();wx.vX({vY:k(2U){l 2Y=2U.2Y;D&&D.1b(E,2Y)}})};2W.A.gd=k(D){wx.gd({1H:k(2U){l 2Y=2U.2Y;D&&D.1b(E,2Y)}})};2W.A.gC=k(2Y,D){wx.gC({2Y:2Y});wx.wc({1H:k(2U){l 2Y=2U.2Y;D&&D.1b(E,2Y)}})};2W.A.gv=k(2Y){wx.gv({2Y:2Y})};2W.A.gq=k(2Y){wx.gq({2Y:2Y})};2W.A.gp=k(2Y,D){wx.gp({2Y:2Y,l1:1,1H:k(2U){l 6j=2U.6j;D&&D.1b(E,6j)}})};2W.A.79=k(v,D){wx.79({8r:v.wd,gt:v.gt,la:v.la,l9:v.l9,9W:v.9W,1H:k(2U){D&&D.1b(E,2U)}})};2W.A.2n=k(){wx.jx()};76=k(q){j.q=q;j.2g=E;j.1q=E;j.3q=1f;j.3g=E;j.4L=E;j.2m()};76.A.2m=k(){j.2g=j.q.I.5f();j.1q=(j.q.I.6t()||/g1\\g4/ig.3u(4Y.4W))?"3P":"5S";l o=j;l fE="Y://wb.wh.Z/we/fE.js?xG=xH";j.q.I.9V(fE,k(){o.3q=1g;o.3z();o.q.2O("jH")})};76.A.74=k(2g){N j.q.I.8C(j.2g,2g)};76.A.42=k(D){if(j.3q){D&&D.1b(j)}L{l o=j;j.q.V("jH",k(){D&&D.1b(o)})}};76.A.3z=k(){l o=j;j.42(k(){l jA={1K:o.q.1m.1K,8u:o.q.1m.X,xE:o.q.1m.3N,xJ:o.q.1m.23};R.xB.v.xM(jA);})};76.A.2T=k(){j.3z();j.q.I.73()};g2=k(q){j.q=q;j.2g=E;j.1q=E;j.3q=1f;j.3g=E;j.4L=E;j.2m()};g2.A.2m=k(){j.2g=j.q.I.5f();j.1q=(j.q.I.6t()||/g1\\g4/ig.3u(4Y.4W))?"3P":"5S"};g2.A.bV=k(v){};4h=k(q){j.q=q;j.2g=E;j.1q=E;j.3g=E;j.4L=E;j.a5=E;j.2m()};4h.A.2m=k(){j.2g=j.q.I.5f();j.1q=(j.q.I.6t()||/g1\\g4/ig.3u(4Y.4W))?"3P":"5S";l o=j;R.xZ=k(){o.3g&&o.3g.3Y(o.q)};R.y0=k(){o.4L&&o.4L.3Y(o.q)};C.V("xP",k(){if(o.a5)C.1K=o.a5;o.3g&&o.3g.3Y(o.q)})};4h.A.74=k(2g){N j.q.I.8C(j.2g,2g)};4h.A.3z=k(){if(j.1q=="3P"){R.19="jp::xN::"+j.q.1m.23+"::"+j.q.1m.1K+"::"+j.q.1m.X+"::"+j.q.1m.3N}L if(j.1q=="5S"){if(j.74("2.0")){R.3i&&3i.3z(2N.3o(j.q.1m))}L{j.a5=C.1K;l 5o="zxs............................................................|";C.1K=5o+j.q.1m.23+"|"+j.q.1m.1K+"|"+j.q.1m.X+"|"+j.q.1m.3N}}};4h.A.2T=k(){if(j.1q=="3P"){j.3z();if(j.q.S){j.q.fU()}}L{R.3i&&3i.2T(2N.3o(j.q.1m))}};4h.A.81=k(){if(j.1q=="3P"){if(j.q.U&&j.q.U.xd==2){19.2I="q://x2"}}L{R.3i&&3i.81(j.q.S)}};4h.A.6T=k(v){v.1X=j.q.1X;v.6Y=j.q.6Y;v.1K=j.q.1m.1K;if(j.1q=="3P"){}L{R.3i&&3i.6T(2N.3o(v))}};4h.A.6V=k(){l T=E;if(j.1q=="5S"){if(R.3i)T=3i.6V()};N T};4h.A.33=k(2y){2x{l T=3i.6V();l F="Y://wx.zxs.Z/1h/33?T="+T;l o=j;j.q.I.1z(F,k(v){if(v&&v.2v){3i.jc(2N.3o(v));l 1F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(2y)+(v.2q?"&4q="+v.2q:"")+(T?"&T="+T:"")+(v.3O?"&1W="+v.3O:"");R.19.2B(1F)}L{o.q.2o.5m();3i.jf()}})}2w(e){R.19=j.q.1B+"/1h/33.1L?r="+2z.2J()}};4h.A.2H=k(1u){R.3i&&3i.2H(1u)};4h.A.bA=k(F){if(!R.3i)N;l 4N=3i.bA(F,R.3F,R.3h);if(4N){l 1i=C.K("1i");1i.jb=4N;C.1o("3s")[0].J(1i)}};4h.A.bl=k(){if(j.1q=="3P"){2D.19="5P://bl"}L{R.3i&&3i.bl()}};4h.A.8P=k(v){if(j.1q=="3P"){2D.19="wZ://"+2N.3o(v)}L{R.3i&&3i.8P(2N.3o(v))}};4h.A.8U=k(v){if(j.1q=="3P"){2D.19="xf://"+1v(v)}L{R.3i&&3i.8U(v)}};4V=k(q){j.q=q;j.2g=E;j.1q=E;j.3g=E;j.4L=E;j.2m()};4V.A.2m=k(){j.2g=j.q.I.5f();j.1q=(/xt\\/2K/ig.3u(4Y.4W)?"3P":"5S")};4V.A.3z=k(){2x{if(j.1q=="3P"){19.2I="2K.3z://"+1v(2N.3o(j.q.1m))}L{2K.3z(2N.3o(j.q.1m))}}2w(e){1r.1x(e)}};4V.A.2T=k(){2x{if(j.1q=="3P"){19.2I="2K.2T://"+1v(2N.3o(j.q.1m))}L{2K.2T(2N.3o(j.q.1m))}}2w(e){1r.1x(e)}};4V.A.81=k(){2x{if(j.1q=="3P"){19.2I="2K.81://"+j.q.S}L{2K.81(j.q.S)}}2w(e){1r.1x(e)}};4V.A.6T=k(v){v.1X=j.q.1X;v.6Y=j.q.6Y;v.1K=j.q.1m.1K;2x{if(j.1q=="3P"){19.2I="2K.6T://"+1v(2N.3o(v))}L{2K.6T(2N.3o(v))}}2w(e){1r.1x(e)}};4V.A.6V=k(){l T=E;2x{if(j.1q=="5S"){T=2K.6V()}}2w(e){1r.1x(e)};N T};4V.A.33=k(2y){2x{l T=2K.6V();l F="Y://wx.zxs.Z/1h/33?T="+T;l o=j;j.q.I.1z(F,k(v){if(v&&v.2v){2K.jc(2N.3o(v));l 1F=o.q.1B+"/2o/3B.1h.1L?2y="+1v(2y)+(v.2q?"&4q="+v.2q:"")+(T?"&T="+T:"")+(v.3O?"&1W="+v.3O:"");R.19.2B(1F)}L{o.q.2o.5m();2K.jf()}})}2w(e){R.19=j.q.1B+"/1h/33.1L?r="+2z.2J()}};4V.A.2H=k(1u){2x{2K.2H(1u)}2w(e){1r.1x(e)}};4V.A.bA=k(F){2x{l 4N=2K.bA(F,R.3F,R.3h);if(4N){l 1i=C.K("1i");1i.jb=4N;C.1o("3s")[0].J(1i)}}2w(e){1r.1x(e)}};8e=k(q){j.q=q;j.2g=E;R.8f={};j.2m()};8e.A.2m=k(){j.2g=j.q.I.5f();l F="Y://xj.uc.cn/xn.j9";l v={8f:"xl"};j.q.I.jo(F,v,E,k(v){R.8f=v})};8e.A.74=k(2g){N j.q.I.8C(j.2g,2g)};8e.A.2T=k(){if(8f.fr===\'9z\'||8f.fr===\'jn\'){if(8f.fr===\'9z\'){2x{jj.uo("up.ug",[j.q.1m.1K,j.q.1m.X,j.q.1m.23,\'\'])}2w(e){1r.3d(e.3D)}}L{if(j.74("9.9.0.0")){j.kb();jJ.k8(j.q.1m.1K,j.q.1m.X,j.q.1m.23,\'\',\'\',\'uI\',\'aK\')}L{19.2I="uF:k8:"}}}L{j.q.I.4O("uG")}};8e.A.kb=k(){l O=C.1j("aK");if(!O){O=C.K("O");O.id="aK";if(j.q.S)O.1T="Y://U.zxs.Z/"+j.q.S+"/3e.2f";L O.1T="Y://U.zxs.Z/3e.2f";O.Q="aK";C.1o("1w")[0].J(O)}};aT=k(q){j.q=q;j.2g=E;j.1q=E;j.3g=E;j.4L=E;j.2m()};aT.A.2m=k(){j.1q=(4Y.4W.3v(/(ka|k7|k4)/ig)?"3P":"5S")};aT.A.2T=k(){l gP={tS:"2T",1K:j.q.1m.1K,F:j.q.1m.23,tV:j.q.1m.3N,ud:j.q.1m.X};if(j.1q=="3P"){19.2I="hs.4x://"+1v(2N.3o(gP))}L if(R.hp&&hp.ki){hp.ki(2N.3o(gP))}};gO=k(q){j.q=q;j.2g=E};gO.A.2T=k(){R.9z.u8(j.q.1m.23,j.q.1m.1K,j.q.1m.3N)};4Z=k(q){j.q=q;j.2g=E;j.1q=E;j.jK=vu;j.3g=E;j.4L=E;j.2m();j.bv()};4Z.A.2m=k(){j.2g=j.q.I.5f();j.1q=(j.q.I.6t())?"3P":"5S";j.q.I.9V(j.q.1B+"/js/kg-vA-vB.js");2x{l o=j;j.42(k(4j){4j.2m(k(3D,kf){1r.1x("kc uR a 3D",3D);l v={"AX AR":"Bh"};1r.1x("kc Bf Bm",v);kf(v)})})}2w(e){1P(e)}};4Z.A.bv=k(){if(!j.q.ui.3l)N;l o=j;j.q.ke([{3e:j.q.1B+"/O/B9.2f",1p:"B6",1O:k(){o.h9()}},{3e:j.q.1B+"/O/Aq.2f",1p:"Ar",1O:k(){o.bO()}},{3e:j.q.1B+"/O/jR.2f",1p:"gU",1O:k(){o.h8()}}])};4Z.A.42=k(D){2x{if(R.h5){D&&D.1b(E,R.h5)}L{C.V("Au",k(){D&&D.1b(E,R.h5)},1f)}}2w(e){1P(e)}};4Z.A.At=k(D){2x{l o=j;j.42(k(4j){4j.7n("C1",{C4:o.jK},k(v){l P=2N.51(v);if(P.3E==1){D&&D.1b(E,P.v)}L{1P(P.22)}})})}2w(e){1P(e)}};4Z.A.C3=k(D){2x{j.42(k(4j){4j.7n("C2",{},k(v){l P=2N.51(v);if(P.3E==1){D&&D.1b(E,P.v)}L{1P(P.22)}})})}2w(e){1P(e)}};4Z.A.bV=k(bL,D){2x{j.42(k(4j){4j.7n("27",bL,k(v){l P=2N.51(v);D&&D.1b(E,P)})})}2w(e){1P(e)}};4Z.A.h9=k(){2x{j.42(k(4j){4j.7n("h9",{},k(v){1r.1x(v)})})}2w(e){1P(e)}};4Z.A.bO=k(){2x{j.42(k(4j){4j.7n("bO",{},k(v){1r.1x(v)})})}2w(e){1P(e)}};4Z.A.h8=k(){2x{j.42(k(4j){4j.7n("h8",{},k(v){1r.1x(v)})})}2w(e){1P(e)}};bP=k(q){j.q=q;j.3g=E;j.4L=E;j.2m()};bP.A.2m=k(){};bP.A.33=k(G){2x{l T=R.jZ.BT();l jW=R.jZ.BW();if(!T){1P("yV，yg。");R.19="Y://m.zxs.Z/2Z.1L?H=5R&y9=4I";N};l o=j;l F="Y://wx.zxs.Z/5R/33?T="+1v(T)+"&zn="+1v(jW);j.q.I.1z(F,k(v){if(v.3d){1P(v.3d);N};o.q.2o.gZ(v);o.q.bi("5R");G.1H&&G.1H.1b(E)})}2w(e){1P(e)}};', 62, 2372, '|||||||||||||||||||this|function|var|||_this||gamezxs|||||data|||||prototype|div|document|callback|null|url|options|spid|utils|appendChild|createElement|else||return|img|json|className|window|gameid|token|game|addEventListener|break|content|http|com|||||||||case|location|localStorage|call|innerHTML|mask|player|false|true|app|style|getElementById|Gamezxs|length|shareData|GamezxsPKIO|getElementsByTagName|text|type|console||wrap|roleid|encodeURIComponent|body|log|result|ajax|Command|baseurl|action|GamezxsPK|GamezxsPKUI|redirect|arguments|success|role_id|GamezxsUtils|title|html|btn|role|click|alert|setTimeout|phone|status|src|players|preventDefault|myuid|score|touchstart|removeChild||GamezxsPay|msg|link|||new|pay|for|getAppType|list|password|user|cancel|parentElement|png|version|roomid|send|display|tip|cmd|init|close|auth|Channel|accessToken|setParameter||btns|createOrder|uid|catch|try|origin|Math|leftcircle|replace|Status|top|value|box|item|chat|href|random|pengpeng|switch|typeof|JSON|dispatchEvent|GAME|nickname|time|name|share|res|hostname|GamezxsWx|errcode|localId|menu||GamezxsAuth||login|getParameter|none|extend|credit|target|finish|rightcircle|fail|money|error|icon|label|shareOK|innerHeight|gamezxsapp|color|start|ball|room_id|headimgurl|stringify|sessionStorage|ready|second|head|defaults|test|match|left|step|check|setShareData|ishero|trans|span|message|code|innerWidth|orderid|vw2pxs|width|header|1000|mousedown|gamecenter|imgurl|wxzxsuid|iOS|GamezxsUI|spname|response|username|net|frame|randPlayers|push|apply|talker||loading|onReady|indexOf|xmlhttp|open|sign|hero|default|attach|movie|GamezxsChat|product|paytype|postMessage|errmsg|input|GamezxsApp|buttons|bridge|showTip|100|source|webkitAnimationEnd|animationend|isPC|access_token|logid|fromid|showMask|linear|interval|realuid|onclick|date|register|img2|captchaBox|footer|3000|herobest|object|forwards|txtPassword|games|rightwrap|Date|shareCancel|leftwrap|css|debug|fromurl|reward|notify|targetbest|best|owner|GamezxsPengPeng|userAgent|city|navigator|GamezxsKuaiwanHuluxia||parse|obj|hide|index|usecredit|qrcode|fullscreen|resetCheckNewMessageTimer|GamezxsUIBallMenu|imgs|bindPhone|isKuaiWan|offsetHeight|mediaIds|getAppVersion|btnNext|txt|count|localIds|gamename|event|clear|wait|space|dialog|memo|500||getTime|bgcolor|padding|GamezxsPayUI|show|wechatid|fadeOut|showRole|height|showBindPhone|timer|loadingComplete|showRoom|type3|logo|btn1|hideMask|size|unionid|btn2|peagame|jsp|gamezxspay|tab|ggzs|Android|gamezxsmask|badge|getHead132|payCallback|tid|buttonOK|gamezxspk||targetIndex|selectedPlayer|txtPhone||pkGameRoomOK|badgeMessage|reg|GamezxsUIBall|placeholder|hidePay|buttonCancel|removeItem|max|shareDomain|symbol|creditshow|Base64|qrmain|serverId|visible|fid|GamezxsChatUI|once|websocket|txtCode|orientation|removeRole|type1|isIOS|string|isAppPay|getRandomString|qrbox|GamezxsPKAni|form|isConnected|vw2px|getSessionId|mode|btnResend|moreurl|quitRoom|room_game|isPrivate|showGame|topic|arrow|onTimeout|isOpenSp|gameurl|num|hideEmojiPanel|winner|playerIndex|onAutoSubmit|duration|getToken|progress|domain|scoreName|encode||formatMoney|resetPassword|shareTip|isVersionOver|gamezxszxsstart|GamezxsQQ|btnBind|jsonparam|chooseWXPay|bold|jsonpcallback|emojis|heroscore|useTrans|targetscore|circleloadright|emoji|hideBindPhone|2000|diff|move|gamezxsballmenu|callHandler|dts|account|set|getElementsByClassName|abid|refreshScore|animation|pklastuser|200|theHead|ONLINE|onClickItem|zhongsou|node|port|CHAT|gamezxsmenu|hideMatchRecommend|GamezxsUIMenu|level|userReady|notice|table|circleloadleft|end|webkitAnimation|maxTop|gamezxspktip|animationFillMode|webkitAnimationFillMode|hideProgress|lnkReset|pkuid|paddNum|maxLeft|info|closeProgress|aiwanh5|verify|onInitGame|game6|postMessageTop|usemoney|postMessageFrame|layabox|sixty|FREE|open_spid|hasAlert|lnkReg|onResize|divqr|GamezxsUC|uc_param_str|txtUsername|cnzz_s_tag|rnd|creditcheck|getPath|isLoading|type2|getSp|sessionid|_czc|isNativeWxpay|timestamp|setPayType|isLocal|desc|btncancel|parent|gamezxspkconfirm|roleReady|hideRoom|getuser|signature|compareVersion|now|events|getWxServer|拒绝|同意|rematch|hidePkOther|btnActive|btnok|sex|GamezxsUtilsDialog|closeDialog|postWxpay|menuItem|undefined|animalid|agree|postAlipay|clearTimeout|parseInt|scoretext|gamezxspkmatcharrow|shareCancelHandler||toFixed|shareOKHandler|showRandPlayers|resultwhite|showCaptcha|btn5|btnLogin|scriptControll||btn6|btn4|onCancel|OK|scorebar|btn3||feedId|gamezxsball|ff6600|showProgress|vh2px|onGameReady|hideTip|qrscan|gotoPublicRoom|btnCancel|clearButton|isNewUser|isSubmitted|province|setProgressText|country|请输入验证码|modehead|android|isTest|modepay|CryptoJS|idods|subscribe|track||credittip|END|sendCodeFreeze|isPk|payAiaiu|pay7k7k|refreshGameTime|PRIVATE|base|gamezxsballmenumask|substr|payLequ|gamezxspkintro|toggle|require|paySign|onGameMatchPlayerOffline|onGameReplay|shachihuyu|showGameIntro|pay1758|pop|removeImg|hideGameIntro|oldTitle|payIqiyi|execute|payLeguyu|onGameResult|onGameJoin|payQQBrowser|payBudejie||onGameRoomOK|onGamePlayers|payQQGame|pay7477|newMessage|_dialog|onGameQuit|checkPhonePassword|onGameRoom|getNewMessage|payPlay68|gamezxsshareevent|getRole|addPlayer|payQQBook|onGameAllReady|renderRole|payFanqie|gamezxspktime|gamezxspktimeout|payQunhei|payKugou|getGameServer|creditlabel|payQidian|payLaya|onGameReplayRequest|hideQrcode|pathname|btnRand|updateOrientation|contentWindow|gamezxsucicon|frameid|OFFLINE|isAppAlipay|hideResult|callback_url|btnWx|scope|onGameRandPlayers|GamezxsZhongsou|touches|isAppWxpay|pageX|kwhlx|getFromUser|isHenKuai|gamezxspaypopup|pageY|checkTask|GamezxsUIStart|server|order|isMove|isPress|balance|60h5|pay5543|doGameReplayRequest|paySina|onGameRoomCreate|payWan669|payCancel||登录失败|initSpid|pay17mao|onGameReplayRequestCancel|onShowPay|MM|totalscore|bar|onGameReplayResponse|payChannel||payTopsgame|doGameJoinPrivate|onGameAllList|initBall|isOK|onGameRandList|payV1game|注册失败|cssHack|_setCustomVar|payLmw|pay360|off|payMeitu|_|gamezxspkmatchscroll|gameReady|isNaN|hideLoginUser|payData|addShortcut|method|refresh|GamezxsGgzs|spReady|countDownTimer|gamezxspkgamelist|orientationChange|isPublic|startPay|lastCheckUnread|delay|createRole|removeEventListener|add|params|isPlayerQuit|gamezxspkrole_target|gamezxsbindphone|isRecommendNotice|onTimer|gameicon|showGameRandList|errMsg|class|showLoginUser|javascript|btnPrev|restart|isRoleReady|parseFloat||hideGameAllList|steps|PLAY|isReady|gamezxsbindphonebtn||pulse|gzurl|totalTime|offline|hideWait|movies|https|gamezxspktiptext|unread|active|noncestr|isTargetJump|getRandomInt|hideGame|gametime|cancelEvent|script|timeoutText|timeoutBtns|busy|pkPlayerQuit|showMyGameRoomWait|gamezxspkmask|gamezxsbindphonetext|showEmojiPanel|WeixinJSBridge|hideChooseGame|READY|showButtons|getTargetPlayer|ani|submit|取消|hideGameRoomCreate|sendCode|gamezxspkemojilist|request_id|和别人玩|hideGameShare|uploadImage|gamezxschatframewrap|mediaId|showRequest|chooseImage|gamezxsuirequest|pkGameAllReady|getCaptchaCode|pkGameReplay|isSelected|media_id|startPk||onces|modetitle|iframe|hideRandPlayers|instanceof|Array|reset|supportOrientation|socketOpen|验证码错误|doGameMatchRecommend|chatTo|keepAliveInterval|code_url|showGameRoomCreate||gamezxscaptchabox|showWaitPeople|isReconnect|GAME_QUIT|GAME_JOIN_OFFLINE|GAME_READY|GAME_REPLAY|GAME_SUBMIT|GAME_JOIN_PUBLIC|GAME_MATCH_RECOMMEND_OK|GAME_MATCH_RECOMMEND|GAME_MATCH_PLAYER_OFFLINE|GAME_JOIN_PRIVATE|GAME_MATCH_IGNORE|GAME_ALL_LIST|GAME_PK_NOTICE|showButton|GAME_RAND_PLAYERS|GAME_ASSIGN|GAME_ROOM_CREATE|GAME_REPLAY_REQUEST_CANCEL|GAME_REPLAY_REQUEST|GAME_REPLAY_RESPONSE|GAME_EMOJI|GAME_EMOJI_LIST|gamezxspkprogresstext|hasMore|gamezxspkwait|支付|doGamePkNotice|doGameReplayResponse|bindPhoneTimer|gamezxspayreward|正在匹配|gamezxspaytype3|doGameSubmit|GAME_MATCH|logout|hideCaptcha|GAME_MATCH_CANCEL|pkHeroQuit|P_TOKEN|gamezxspkbtn|gamezxspkprogress|getCreditReward|gamezxspaytype1|gamezxspaytype2|getPlayer|hideRequest|gamezxsuirequestwrap|fadeOutSlow|gamezxsuirequestmask|jpg|setPayType1|gamezxschatframemask|showContent|scrollPlayer|playerSize|onSpReady|addNextPlayer|setPayType2|while|offsetTop|prepay|sextic||session_spid|flashFast|gamezxspkmatchlist|pm_spid|renderChooseGame|reject|gamezxschoosegame|showGameTime|gamezxssubscribe|hideSubscribe|friendResponse|pkResponse|then|发送|gamezxspkscore_|gamezxspkscoretext|closeReply|refreshPKScore|gamezxspkplayer_|removePlayer|gamezxsuichatpopup|gamezxspkfight|resetAllPlayers|setPayType3|captcha|isPkOffline|onTouchEnd|void|showGameShare|over|gamezxspkroom|gamezxspkother|gamezxspaylabel|gamezxspkothermask|ongz|gamezxspayqrcode|onGameReplayReady|gotoPrivateRoom|showBall|onTouchMove|startMove|onTouchStart|gamezxspkroomcreate|gamezxsmenumask|createRoom|selectedGame|loginUsernamePassword|clearGameStyle|scroll|locate|offsetWidth||wxpay|iswin|selectGameStyle||hideGameRandList|gamezxspkgamesmask|isAssignGame|gamezxspkgames|gamezxspkresult|ALL_READY|再玩一次||gamezxsballmenulist|items|addItems|addItem|qqapi|isChat|gameOrder|rankScore|timeline|isUserReady|FFFFFF|checkUrl|sec|encrypt|Utf8|gamezxswait|isMobile|mobile|describe|again|shareFlow|mokupi|root_s|goad|timeoutCallback|checkVcode|async|uuid|GamezxsQQHall|RegExp|sios|isSpReady|hideMenuItems|cnzz|key|wxOpenOAuth|thisTime|heartbeatDetailId|snsapi_base|stopRecord|确定|startRecord|detail_id|wxGetBase|10000|postShareOK|gfan|heartbeat|defVal|loop|gamezxsdialog|uploadVoice|stopVoice|protocol|connect|nonceStr|mobileAgent|pauseVoice|shareLog|isAndroid|confirm||gamezxssharemask|closeShareTip|playVoice|showBindTip|gamezxsprogress|isGameReady|bounceOutLeft|readyState|gamezxszxsstarttip|bounceInLeft|config|registerWap|customLoading|checkcode|GamezxsGfan|sharedData|GamezxsUtilsAjax|imgfinish|enc|responseText|退出|shareDomains|checkNewMessageNext|HH|homeurl|save|checkMessageTime|timeout|yyyy|format|checkNewMessageTimer|WebViewJavascriptBridge|checkNewMessage|cpid|quit|back|loadingSp|ads|lcomplete|gameStart|str|sms|cancelCountDown|showAd|henkuai|min|addLink|元|gamezxsprogressbtns|afinish|gamezxsprogressmask|JavascriptInterface|gamezxspaycontent|getCssLink|souyue|splice|run|pkShowGame|pkShowRoom|insertBefore|initJsApi|err_msg|getGameInfo|setPlayers|一起玩|public|panel_close|checkSubscribe|pkMatchPlayer|getEventToday|cnzz_protocol|shareBranch|charset|utf|begin|gamezxsbindphoneclose|即将进入游戏|requestAnimationFrame|输入密码|gamezxsbindphonebox|logView|invoke|640|argv|gamezxsbindphonelink|img_url|shareSina|path|shareQQBrowser|发送验证码失败|getRoleList|startTime|重新发送|position|gamezxspkshare|CCC|快点过来找我|get|gamezxsadbottom|QRCode|gamezxsadtop||房号是|||我开好房了|makeCode|showGameAllList|cplogo|slogan||||keyword|resetPasswordCheck|isExist|loginRegister|bounceInRight|male||resetPasswordVerify|sendVcode|female|bindPhoneVerify|registerVerify|showMatchRecommend|isZhousouInstalled|encrypted|showCountDownClock|showCountDown|gamezxspkprogresswrap|gamezxspkwaitwrap|showWait|gamezxspkprogresscircle|toString|heartbeatTime|pkGameResult|pkGameStart|remove|checkCaptchaCode|ERROR|lastTime|hideWaitPeople|vh2pxs|hideMatch|gamezxspkrole_hero|gamezxszhongsoutip|autoScore|lastRankScore|pkGameRandPlayers|bindPhoneCheck|gamezxspkwaittext|gamezxspkwaitcircle|srcs|showMatch|word|fontSize|gameyun901|hkxf|php|douqu126|textContent|onLoginSuccess|youxi135|org|onLoginFail|specDomainUrl|payOpen|错误代码|ucweb|jingyuetuan|pay60h5|7885279|iphone|jsonp|appcall|hongbao|hongbao_id||handleHongbao|payQQHall|initLog|initBindPhone|closeWindow|handlePkRequest|payKuaiwan|shareInfo|initUser|initKuaiwan|快玩游戏|initSns|5000|handleFriendRequest|qqReady|handleMessages|ucbrowser|apkid|getPayServer|isMenu|isOpen|postOpenWxpay|getCredit|showNativeWxpay|game_menu_exit|kdygdsb|gamezxspaycreditcheck|showPay|zxs游戏|deviceInfo|isBall|initGame|loginInfoProviderForH5|支付失败|isPay|initSp|postWapWxpay|iPad|showQrcode|gamezxspayheader|iPod|web_share|zxsapp|iPhone|createIconImage|JS|getDefaultSpid|setBallMenuItems|responseCallback|kuaiwan|wap|onJSClick|pay_wxzf_|showCreditReward|menuList|initTopEvents|请稍候|joinOffline|initFrameEvents|onPlayerQuit|gamezxsbindphonenum|shortcut|isSubscribe|joinPrivate|email||onMenuShareTimeline|创建私人房间|isInvite|gamezxsloginuser|joinPublic|通知朋友|checkVerify|对手没响应|appid|types|sp_block|gameover|玩家|api|getFullUrl|ajaxUrl|pkGameAllList|即将下载|gamezxsbindphonetip|再等等|initCss|registerUsernamePassword|Ready|spCheck|pkAssignGame|checkUsernamePassword|onMenuShareAppMessage|C_GAME_PK_RESPONSE|popup|C_FRIEND_RESPONSE|isShowProgressTips|talkerRole|iqiyi|gamezxsuichatreply|sina|reply|wanyou|pea|signType|package|qing|C_FRIEND_REQUEST|initBindTip|getsp|C_TEXT|sendText|branch||token无效|_trackEvent|isCreateRoom|matchRecommend|matchPlayerOffline|6000|imgUrl|matchOffline|hideChatFrame|cropwx|gotoChat|gamezxschatframe|uploadwx|slice|loadPublicRoom|qqbrowser|shift|gamezxsloading|sum|wxGetUser|gamezxsballimg|real|showStart|onClose|isWindowsWechat|onOpen|flag|version2|version1|checkOkLoad|getUser|bQQ|fromuser|KuaiwanHuluxia|GROUP_CHAT|doGameMatchCancel|onGameAssign|doCheckToken|doGameMatch|doGameMatchOffline|GAME_MATCH_OFFLINE|doGameMatchRecommendOK|doGameMatchIgnore|initOrientationEvent|onMessage|onReAssignGame|getWxAppId|onError|portrait|landscape|onGameReAssign|onAssignGame|len|unit|zoo|autoSubmit|请设置不少于6位的密码|istest|formatDate|floor|showSubscribe|tw1|gamezxspkfightscore_target|hw2|gamezxspkfightbest_target||hideGameOver|showResult||tw2|showGameOver|getShortUrl|showPkOther|checkError|compareAppToken|search|getOrigin||onTap|locationReplace|checkToken|getFullYear|gameReplayRequest|getDate|getMonth|loadItems|sendFriendRequest|number|gamePlayNew|GAME_ALL_READY|GAME_START|GAME_ROOM_OK|GAME_PLAYERS|GAME_TIME|onGameMatchIgnore|GAME_SNAPSHOOT|GAME_COUNT_DOWN|GAME_ROOM|onGameJoinPublic|onJoinPrivateFail|onJoinPublic|GAME_MATCH_RECOMMEND_NOTICE|onGameJoinPrivate|log_id|GAME_JOIN|onJoinPrivate|||GAME_RAND_LIST|onGameMatchCancel|onMatchPlayer|onGameMatchPlayer|未知的命令|onGameMatch|GAME_RE_ASSIGN|loginForm|onGameMatchRecommend|onMatchRecommendNotice|onGameMatchRecommendNotice|GAME_OVER|GAME_RESULT|onMatchRecommendFail|onMatchRecommend|onMatchRecommendOK|onGameMatchRecommendOK|doGameEmojiList|doGameEmoji|doGameReplayRequestCancel|onGameEmojiList|onGameOver|doGameAssign|doGameRoomCreate|gameOver|onGameEmoji|doGameJoinOffline|onGamePkNotice|doGameMatchPlayerOffline|doGameJoinPublic|showEmoji|doGameReplay|doGameQuit|doGameReady|onCheckToken|onGameStart|executeError|executePrivate|GAME_MATCH_PLAYER|onGameJoinOffline|executeGame|onJoinOffline|onGameSnapshoot|doGameAllList|doGameRandPlayers|doGameAssignTimeout|GAME_ASSIGN_TIMEOUT|onGameCountDown|parseSnapshoot|onGameSubmit|onGameTime|closeOnTimeout|showFight|gamezxspkbg|cancelText|gamezxspkfightscore|gamezxspkfightbest|defaultBtn|gamezxsprogresswrap|leftprogress|gamezxsprogresscircle|pkHideRandPlayers|backgroundColor|rightprogress|showChooseGame|distance|scale|gamezxsdialogprompt|loginqq|cls|gethotgames|request|closeWait|showCancel|fly|bindQq|gif|documentElement|substring||borderColor|gamezxspkgame_||GamezxsUtilsJsonp|selectGame|gamezxspkgame|escape|collect|scrollTop|scrollGame|assignGame|responseData|countDown|img3|img1|selectNextPlayer|selectPlayer|gamezxsprogresstext|countDownTimer_|addFeed|gameName|join|currentRoleId|onerror|XMLHttpRequest|gamezxspkplayername|gamezxspkplayer|gamezxspkscore|gamezxspkfightbest_hero|exist|feed_id|force|好|gamezxspkplayerhead|绑定失败|addFeedComment|prompt|checkphone|hw1|gamezxspkfightscore_hero|sns|getGamePlay|mousili|pay_invalid_openid|zxsnative|yinshuya|redirect_uri|linkResult|getgameplay|getcredit|提交失败|2500|checksubscribe||设置要重设的密码|chooseGame|getunreadcount|tianxinjiaolian|ptgcw|秒|lxjre||aikiddy||refreshMessage|gameinfo||saveLink|getrolelist||zuizuiyou|aitiboy|2987992|wxOpenLogin||rtfnw|asc|点击这里立即注册|用户名至少6位|onorientationchange|没有|loginQq|onUserReady|帐号|123|忘记密码|onRoleReady|由字母|onLoadingComplete|loadReady|gameload|您已绑定了手机|resetpass|gamezxscom2014123|connect3|response_type|errCode|resize|orientationchange|bonus|connect2|loginUser|点击这里重设|refreshRankScore|设置密码|getcode|wechat_redirect|state|3500|setbackurl|用户|game_share|gamezxsbindphonecode||gamezxsbindphonemain|gamezxsbindphoneresend|输入验证码|新用户|gamezxsbindphonefooter|登录|激活|gamezxsbindphoneactive|一键注册|addFeedGame|这个手机号还没有注册|老用户|商户名称|游戏中心|gototop||gamezxspaymain|decodeURIComponent|支付金额|商品名称|backurl|gotozxs|100000|addfeed|验证码已经发送到|wxid|getevent|支付平台|getrole|注册成功|getGameDetail|输入zxs帐号|密码重置成功|长按二维码识别或微信扫一扫|pay_info|gamedetail|gameIcon|fzqylp|您现在可以用新的密码登录了|tgysw|icon_01|orderInfo|一键绑定|gamezxsbindphonecancel|addfeedcomment|重设密码|liuzhiping|henkuai_zxs|loginwx|重设密码失败|getEventUrl|完成|||wx_ver|是否立即注册|kw_|app2|wx7c7908cfe6ec84b1|wx6871f420cac35959|qrcode_kuaiwan|wxe0fb670c408a3705|wx093dfa177501b406|进入测试页|wxa96b1e63792ce3ec|wxe05ff32481dad99b|gameentry|验证码|选择一款游戏下载吧|getUserSession|getusersession|gamezxscaptchacode|clientY|clearUserSession|gamezxssubscribeqrmain|getusersp|entry|token参数为空|一键登录|checkOkSave|gamezxsCheckWxpay|gamezxsPayCancel|b1Atb251RGNNZktTeTRCdXp3NDFCMkpoNzR0OA|gamezxsCheckAlipay|gamezxsPayCallback|mousemove|不再提示|offsetLeft|mouseup|稍候提示|登录了|您下次可以使用手机号|qqhall|touchend|clearusersession|换一批|pay_invalid_token|clientX|gamezxssubscribeheader|wxGetOpenid|game_menu_|split|henkuai_|Z2Zhbi1seUN1NWVJRzY5eUlhRTBibmpMQm5iTg|rel|b1Atb251SzlpMHV6eXBZLTlmTkIwUm9VWl9NWQ|stylesheet|关注|tools|恭喜您注册成功|csshacker|Z2Zhbi05V0d3MkJ4QkhhZW9VZU9LTWh5VE5TZA|bindphone|sp_check|b1Atb251T1ZmS0VubEhKSXdxTi1NQ3NuV2xvZw|addBallMenuItem|b1Atb251R0xBLVRldGNjcGxGZmNLWlhsOXZ0bw|这个手机号已经被绑定了||b1Atb251RHpoRmtpa2M2YjhGbF9sUDRzQ28wTQ|hacker|请输入其它的手机号|showShareTip|40007|立即登录|密码错误|40004|compareLocalToken|gamerole|gamezxscaptchabtn|注册新用户|请输入有效的手机号|gamezxsballmenubg|game_menu_add|gameshare|请记住您的用户名和密码|51h5|gamezxssubscribeqrcode||b1Atb251SzVCMHc1S1RIRFBVdUp1MHI2a0tORQ|goto||icon_06|新消息|且必须以字母开头|游戏大厅|wan669|个人中心|gamezxsmenumy|lmw|icon_04|icon_02|随机一起玩|17mao|今日比赛|online|5543|在线玩家|icon_05|decode|weixin|gamezxscaptchatitle|数字和下划线组成|mfxy|oauth2|输入手机号|authorize|getorderreward|create|360|v1game|getuserhandler|meitu|topsgame|pingan|gamezxszxsstartbadge|youku|hww|D2602D|7477|play68|gamezxscaptchaimg|绑定QQ|是否立即绑定|getNotice|刷新验证码|getcreditsum|showmenu||4CAB56|touchmove|恭喜绑定成功|qqgame|绑定手机|1758|gamezxsmenu_credit|budejie|gamezxsgzbanner|gz_banner|7k7k|xcy|lequ||icon_03|leguyu|snsapi_userinfo|aiaiu|qunhei|laya|gamezxscaptchalink|kugou|qidian|qqbook|fanqie|gz_zxs|您的帐号目前尚未绑定任何登录方式|的匹配推荐|忽略来自|发送对战通知|remain|游戏匹配|已失效|取消匹配|人数|incognito|webos|nokia|webmate|ipod|category|blackberry|ipad|image|keepAlive|checkConnect|489348|setInterval|连接成功|socketClose|已断开连接|clearInterval|emoji_id|连接已关闭|未知的通道消息|无效的通道或命令|share2Friends|warn||D34B34||description|short_url|getshorturl|page_share|getHead64||getUrl|127||removeParameter|getQueryString|startRequest|shell|getMinutes|getHours|setMilliseconds|132|setHours|today|localhost|micromessenger|WindowsWechat|GGModel|mappn|toLowerCase|skyfire|linux|Mac|ext|其它分享接口|file|来自zxs游戏|MicroMessenger|other|UCBrowser|PengPeng|5003|5002|5006|5005|got|9502|5001|1001|5012|5011|5014|5013|5008|5007|5010||5009|113|换个游戏|resultred|发送消息|换个对手|你赢了|lose|gamezxspkresultbtn|你输了|182|gamezxspkrandom|139|254|请求加对方为好友|加好友|gamezxspkweixin|好友请求已发送|5071|5070|5073|5072|5051|5050|5065|5061|onopen|1006|onmessage|onclose|5076|5074|WebSocket|huluxia|jsbridge|5021|5020|5023|5022|5016|5015|5018|5017|5030|5029|5032|5031|5025|5024|5028|5026|getSeconds|WeixinJSBridgeReady|initWeixinJSBridge|appmessage|onBridgeReady|onVoiceRecordEnd|complete|parentNode|stat|img_width|shareTimeline|share_timeline|img_height|send_app_msg|sendAppMessage|getSquareImage|getSquareImages|2947366|CBC|pub|onVoicePlayEnd|timeStamp|qqmobile|DEBUG|AES|idqqimg|app_version|app_type|baidu|tongji|track_id|要求|view|heart|scanQRCode|showOptionMenu||chooseImages|openProductSpecificView|openLocation|getNetworkType||hideOptionMenu|getLocation|dataUrl|appMessage|成功|分享|chooseCard|addCard|wxReady|openCard|downloadImage|checkJsApi|jsApiList|onMenuShareWeibo|onMenuShareQQ|getMediaUrl|getMediaUrls|appId|getjsapisignature|onRecordEnd|uploadImages|previewImage|downloadVoice|hideAllNonBaseMenuItem|showMenuItems|translateVoice|showAllNonBaseMenuItem|wxapppay|gamezxsdialogcontent|gamezxsdialogfooter|setfullscreen|FF0000|请在此输入内容|gamezxsdialogheader|888888|20000000|10000000|GET|onload|loading3|gamezxsdialogbutton|gameType|FBC71B|aliapppay|小时前|分钟前|ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789|hao|formatTime|dnfrpfbivecpbtnt|刚刚|getucparam|返回游戏|scorename|sharetip|feed_pk|shareConfirm|ios|game_play_result|分享给|gamezxsadpopup|gamezxsadpopupmask|gamezxsadpopupclose|gamezxsadpopupimg|getad|mqq|ad_json|popupad|image_url|wx360a9785675a8653|_bid|152|isappinstalled|share_url|zhongsou_share_android|zhongsou_share_ios|setShareInfo|setwxshare|delete|gamezxsWxShareOk|firstChild|Microsoft|ActiveXObject|onreadystatechange|XMLHTTP|fixed|splogo|9999|zIndex|gamezxsWxShareOK|gamezxsWxShareCancel|gamezxslogo|gamezxslogo_up|游戏对战请求|feed|未知的聊天格式|游戏对战响应|请求加好友|动态链接|menuid|响应好友请求|正在进入游戏|对方的请求|回复|gamezxsuichatreplymask|sendmessage|请在登录后进游戏|已|发送失败|answer|gamezxsuichatpopupwrap|getnewmessage|请求加你为好友|gamezxsuichatpopupmask|C_GAME_PK_REQUEST_CANCEL|2021|2023|2022|发来一段语音|发来一张照片|图文消息|发来一段视频|接收|给你发了一个红包||想和你一起玩游戏|忽略|重新加入|已是房主|进入私人房间|非房主|8000|create_pk_room|离线匹配指定玩家|重新匹配|房间人已满|请等待|选择游戏|房间已关闭|对方没响应|等待对应响应|已发出通知|催催Ta|gamezxsuiresponsereject|keyCode|keydown|showChatFrame|您尚未登录|gamezxsuichatreplytip|gamezxsuichatreplybtn|bounceOutRight|查看聊天记录|gamezxsuirequesttext|gamezxsuirequesthead|gamezxsuiresponseagree|gamezxsuiresponsebtns|webkitOverflowScrolling|bounceInDown|bounceOutUp|touch|C_GAME_PK_REQUEST|disabled|zxscredit|pay_zfb_|checked|button|gamezxspaytype|||下一步|gamezxspaybtn|请打开微信扫一扫完成支付|长按二维码识别完成支付|gamezxspayqrbox|gamezxspayqrmain|device|您是VIP|本次充值将返还您|target_level_id|gamezxspaymodepay|zxs账户|setAttribute|使用zxs账户支付|gamezxspaycreditshow|gamezxspaycredit|5033|checkbox|gamezxspaycreditaccount|gamezxspaymodehead|gamezxspaymode|支付方式|gamezxspaymodetitle|zxs积分与人民币换算|gamezxspaycredittip|01元|1积分|2013|2012|2014|C_FRIEND_REMOVE|2010|C_UNREAD|2011|C_FEED_LINK|2017|C_BAD_REPORT|2018|C_GAME_LINK|2015|C_ADD_BLACKLIST|2016|C_REMOVE_BLACKLIST|2009|C_VIDEO|2003|C_HTML|2004|C_IMAGE|2001|C_VOICE|2002|C_ARRIVE|2007|C_READ|2008|C_QA|2005|C_QA_ANSWER|2006|gamezxspkroomplayers|玩|roomhero|gamezxspkroomrole_|不要|gamezxspkconfirmbtncancel|的|是否要和|roomtarget|下一页|上一页|game_menu_refresh|刷新|play_count|getAccess|WebViewJavascriptBridgeReady|人正在玩|gamezxspkconfirmbtnok|继续等待|对应无响应|gamezxspkrole_|gamezxsshare|点击马上与|扫描二维码或通知好友立即开战|等待朋友加入|实时对战|matchhero|gamezxspkconfirmicon|gamezxspkrolecity|gamezxspkconfirmbtn|gamezxspkconfirmtext|gamezxspkrole|matchtarget|gamezxspkrolename|gamezxspkrolehead|gamezxspkresultscorebar|gamezxspkresultscore|gamezxspkresultrole_|Responds|得分|gamezxspkscoretitle|gamezxspkresultplayers|时间到|resulthero|Javascript|gamezxspkresultscorebar_|win|gamezxspkresultwinner|gamezxspkresultchaticon|resulttarget|gamezxspkresultscoretext|gamezxspkresultscoretext_|intro|返回|匹配成功|gamezxspkemojiflyright|game_menu_back|FFFD01|FFF|bounce|currentImg|gamezxspkemojiflyleft|responding|ease|测试中文|opacity|gamezxspkemoji|gamezxspkemoji_|18s|with|游戏结束|成绩|gameReplay|游戏下载|状态|waiting|所有人|等待对手准备|取消了再玩一次请求|已同意|拒绝了再玩一次请求|想要再玩一次|对手已经离开房间|正在等待对手回应|想和你一起玩|房间内玩家|玩家加入|玩家退出|房间人齐了|房间信息|ngame|pkGameRoom|离开房间|系统|指定了游戏|各玩家|显示游戏|显示房间|等待对手|进入游戏|rightwait|leftwait|getGGTokenToH5|的房间已经开通|bottom|getDeviceInfoToH5|querySelectorAll|619D96|immediately|onPkGameRandPlayers|access|userinfo|getUserInfo|apk_id|执行步骤|public_|个|callee|下载播放|接收游戏快照|hasPlayer|播放结束|游戏步骤|continue|clearCountDown'.split('|'), 0, {}));
(function (u) {
    function p(b, n, a, c, e, j, k) {
        b = b + (n & a | ~ n & c) + e + k;
        return (b << j | b >>> 32 - j) + n
    }

    function d(b, n, a, c, e, j, k) {
        b = b + (n & c | a & ~ c) + e + k;
        return (b << j | b >>> 32 - j) + n
    }

    function l(b, n, a, c, e, j, k) {
        b = b + (n ^ a ^ c) + e + k;
        return (b << j | b >>> 32 - j) + n
    }

    function s(b, n, a, c, e, j, k) {
        b = b + (a ^ (n | ~ c)) + e + k;
        return (b << j | b >>> 32 - j) + n
    }

    for (var t = CryptoJS, r = t.lib, w = r.WordArray, v = r.Hasher, r = t.algo, b = [], x = 0; 64 > x; x ++)b[x] = 4294967296 * u.abs(u.sin(x + 1)) | 0;
    r = r.MD5 = v.extend({
        _doReset       : function () {
            this._hash = new w.init([1732584193, 4023233417, 2562383102, 271733878])
        },
        _doProcessBlock: function (q, n) {
            for (var a = 0; 16 > a; a ++) {
                var c = n + a, e = q[c];
                q[c]  = (e << 8 | e >>> 24) & 16711935 | (e << 24 | e >>> 8) & 4278255360
            }
            var a                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           = this._hash.words, c                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     = q[n + 0], e = q[n + 1], j = q[n + 2], k = q[n + 3], z = q[n + 4], r                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               = q[n + 5], t = q[n + 6], w = q[n + 7], v = q[n + 8], A = q[n + 9], B = q[n + 10], C = q[n + 11], u = q[n + 12], D = q[n + 13], E = q[n + 14], x = q[n + 15], f = a[0], m = a[1], g = a[2], h = a[3], f = p(f, m, g, h, c, 7, b[0]), h = p(h, f, m, g, e, 12, b[1]), g = p(g, h, f, m, j, 17, b[2]), m = p(m, g, h, f, k, 22, b[3]), f = p(f, m, g, h, z, 7, b[4]), h = p(h, f, m, g, r, 12, b[5]), g = p(g, h, f, m, t, 17, b[6]), m = p(m, g, h, f, w, 22, b[7]),
                f = p(f, m, g, h, v, 7, b[8]), h = p(h, f, m, g, A, 12, b[9]), g = p(g, h, f, m, B, 17, b[10]), m = p(m, g, h, f, C, 22, b[11]), f = p(f, m, g, h, u, 7, b[12]), h = p(h, f, m, g, D, 12, b[13]), g = p(g, h, f, m, E, 17, b[14]), m = p(m, g, h, f, x, 22, b[15]), f = d(f, m, g, h, e, 5, b[16]), h = d(h, f, m, g, t, 9, b[17]), g = d(g, h, f, m, C, 14, b[18]), m = d(m, g, h, f, c, 20, b[19]), f = d(f, m, g, h, r, 5, b[20]), h = d(h, f, m, g, B, 9, b[21]), g = d(g, h, f, m, x, 14, b[22]), m = d(m, g, h, f, z, 20, b[23]), f = d(f, m, g, h, A, 5, b[24]), h = d(h, f, m, g, E, 9, b[25]), g = d(g, h, f, m, k, 14, b[26]), m = d(m, g, h, f, v, 20, b[27]), f = d(f, m, g, h, D, 5, b[28]), h = d(h, f,
                m, g, j, 9, b[29]), g                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       = d(g, h, f, m, w, 14, b[30]), m = d(m, g, h, f, u, 20, b[31]), f = l(f, m, g, h, r, 4, b[32]), h = l(h, f, m, g, v, 11, b[33]), g = l(g, h, f, m, C, 16, b[34]), m = l(m, g, h, f, E, 23, b[35]), f = l(f, m, g, h, e, 4, b[36]), h = l(h, f, m, g, z, 11, b[37]), g = l(g, h, f, m, w, 16, b[38]), m = l(m, g, h, f, B, 23, b[39]), f                                                                                                                                                                                                                                                                                                                                               = l(f, m, g, h, D, 4, b[40]), h = l(h, f, m, g, c, 11, b[41]), g = l(g, h, f, m, k, 16, b[42]), m = l(m, g, h, f, t, 23, b[43]), f = l(f, m, g, h, A, 4, b[44]), h = l(h, f, m, g, u, 11, b[45]), g = l(g, h, f, m, x, 16, b[46]), m = l(m, g, h, f, j, 23, b[47]), f                                                                         = s(f, m, g, h, c, 6, b[48]), h = s(h, f, m, g, w, 10, b[49]), g = s(g, h, f, m,
                E, 15, b[50]), m                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            = s(m, g, h, f, r, 21, b[51]), f = s(f, m, g, h, u, 6, b[52]), h = s(h, f, m, g, k, 10, b[53]), g = s(g, h, f, m, B, 15, b[54]), m                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         = s(m, g, h, f, e, 21, b[55]), f = s(f, m, g, h, v, 6, b[56]), h = s(h, f, m, g, x, 10, b[57]), g = s(g, h, f, m, t, 15, b[58]), m = s(m, g, h, f, D, 21, b[59]), f = s(f, m, g, h, z, 6, b[60]), h = s(h, f, m, g, C, 10, b[61]), g = s(g, h, f, m, j, 15, b[62]), m = s(m, g, h, f, A, 21, b[63]);
            a[0]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            = a[0] + f | 0;
            a[1]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            = a[1] + m | 0;
            a[2]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            = a[2] + g | 0;
            a[3]                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            = a[3] + h | 0
        }, _doFinalize : function () {
            var b                       = this._data, n = b.words, a = 8 * this._nDataBytes, c = 8 * b.sigBytes;
            n[c >>> 5] |= 128 << 24 - c % 32;
            var e                       = u.floor(a /
                4294967296);
            n[(c + 64 >>> 9 << 4) + 15] = (e << 8 | e >>> 24) & 16711935 | (e << 24 | e >>> 8) & 4278255360;
            n[(c + 64 >>> 9 << 4) + 14] = (a << 8 | a >>> 24) & 16711935 | (a << 24 | a >>> 8) & 4278255360;
            b.sigBytes                  = 4 * (n.length + 1);
            this._process();
            b = this._hash;
            n = b.words;
            for (a = 0; 4 > a; a ++)c = n[a], n[a] = (c << 8 | c >>> 24) & 16711935 | (c << 24 | c >>> 8) & 4278255360;
            return b
        }, clone       : function () {
            var b   = v.clone.call(this);
            b._hash = this._hash.clone();
            return b
        }
    });
    t.MD5     = v._createHelper(r);
    t.HmacMD5 = v._createHmacHelper(r)
})(Math);
(function () {
    var u = CryptoJS, p = u.lib, d = p.Base, l = p.WordArray, p = u.algo, s = p.EvpKDF = d.extend({
        cfg    : d.extend({
            keySize   : 4,
            hasher    : p.MD5,
            iterations: 1
        }),
        init   : function (d) {
            this.cfg = this.cfg.extend(d)
        },
        compute: function (d, r) {
            for (var p = this.cfg, s = p.hasher.create(), b = l.create(), u = b.words, q = p.keySize, p = p.iterations; u.length < q;) {
                n && s.update(n);
                var n = s.update(d).finalize(r);
                s.reset();
                for (var a = 1; a < p; a ++)n = s.finalize(n), s.reset();
                b.concat(n)
            }
            b.sigBytes = 4 * q;
            return b
        }
    });
    u.EvpKDF = function (d, l, p) {
        return s.create(p).compute(d,
            l)
    }
})();
CryptoJS.lib.Cipher || function (u) {
    var p = CryptoJS, d = p.lib, l = d.Base, s = d.WordArray, t = d.BufferedBlockAlgorithm, r = p.enc.Base64, w = p.algo.EvpKDF, v = d.Cipher = t.extend({
        cfg               : l.extend(), createEncryptor: function (e, a) {
            return this.create(this._ENC_XFORM_MODE, e, a)
        }, createDecryptor: function (e, a) {
            return this.create(this._DEC_XFORM_MODE, e, a)
        }, init           : function (e, a, b) {
            this.cfg        = this.cfg.extend(b);
            this._xformMode = e;
            this._key       = a;
            this.reset()
        }, reset          : function () {
            t.reset.call(this);
            this._doReset()
        }, process        : function (e) {
            this._append(e);
            return this._process()
        },
        finalize          : function (e) {
            e && this._append(e);
            return this._doFinalize()
        }, keySize        : 4, ivSize: 4, _ENC_XFORM_MODE: 1, _DEC_XFORM_MODE: 2, _createHelper: function (e) {
            return {
                encrypt   : function (b, k, d) {
                    return ("string" == typeof k ? c : a).encrypt(e, b, k, d)
                }, decrypt: function (b, k, d) {
                    return ("string" == typeof k ? c : a).decrypt(e, b, k, d)
                }
            }
        }
    });
    d.StreamCipher = v.extend({
        _doFinalize : function () {
            return this._process(! 0)
        }, blockSize: 1
    });
    var b          = p.mode = {}, x = function (e, a, b) {
        var c = this._iv;
        c ? this._iv = u : c = this._prevBlock;
        for (var d = 0; d < b; d ++)e[a + d] ^=
            c[d]
    }, q                            = (d.BlockCipherMode = l.extend({
        createEncryptor   : function (e, a) {
            return this.Encryptor.create(e, a)
        }, createDecryptor: function (e, a) {
            return this.Decryptor.create(e, a)
        }, init           : function (e, a) {
            this._cipher = e;
            this._iv     = a
        }
    })).extend();
    q.Encryptor = q.extend({
        processBlock: function (e, a) {
            var b = this._cipher, c = b.blockSize;
            x.call(this, e, a, c);
            b.encryptBlock(e, a);
            this._prevBlock = e.slice(a, a + c)
        }
    });
    q.Decryptor = q.extend({
        processBlock: function (e, a) {
            var b = this._cipher, c = b.blockSize, d = e.slice(a, a + c);
            b.decryptBlock(e, a);
            x.call(this,
                e, a, c);
            this._prevBlock = d
        }
    });
    b           = b.CBC = q;
    q = (p.pad = {}).Pkcs7 = {
        pad     : function (a, b) {
            for (var c = 4 * b, c = c - a.sigBytes % c, d = c << 24 | c << 16 | c << 8 | c, l = [], n = 0; n < c; n += 4)l.push(d);
            c = s.create(l, c);
            a.concat(c)
        }, unpad: function (a) {
            a.sigBytes -= a.words[a.sigBytes - 1 >>> 2] & 255
        }
    };
    d.BlockCipher = v.extend({
        cfg               : v.cfg.extend({mode: b, padding: q}), reset: function () {
            v.reset.call(this);
            var a = this.cfg, b = a.iv, a = a.mode;
            if (this._xformMode == this._ENC_XFORM_MODE)var c = a.createEncryptor; else c = a.createDecryptor, this._minBufferSize = 1;
            this._mode = c.call(a,
                this, b && b.words)
        }, _doProcessBlock: function (a, b) {
            this._mode.processBlock(a, b)
        }, _doFinalize    : function () {
            var a = this.cfg.padding;
            if (this._xformMode == this._ENC_XFORM_MODE) {
                a.pad(this._data, this.blockSize);
                var b = this._process(! 0)
            } else b = this._process(! 0), a.unpad(b);
            return b
        }, blockSize      : 4
    });
    var n         = d.CipherParams = l.extend({
        init       : function (a) {
            this.mixIn(a)
        }, toString: function (a) {
            return (a || this.formatter).stringify(this)
        }
    }), b = (p.format = {}).OpenSSL = {
        stringify: function (a) {
            var b = a.ciphertext;
            a     = a.salt;
            return (a ? s.create([1398893684,
                1701076831]).concat(a).concat(b) : b).toString(r)
        }, parse : function (a) {
            a     = r.parse(a);
            var b = a.words;
            if (1398893684 == b[0] && 1701076831 == b[1]) {
                var c = s.create(b.slice(2, 4));
                b.splice(0, 4);
                a.sigBytes -= 16
            }
            return n.create({ciphertext: a, salt: c})
        }
    }, a = d.SerializableCipher = l.extend({
        cfg      : l.extend({format: b}), encrypt: function (a, b, c, d) {
            d     = this.cfg.extend(d);
            var l = a.createEncryptor(c, d);
            b     = l.finalize(b);
            l     = l.cfg;
            return n.create({
                ciphertext: b,
                key       : c,
                iv        : l.iv,
                algorithm : a,
                mode      : l.mode,
                padding   : l.padding,
                blockSize : a.blockSize,
                formatter : d.format
            })
        },
        decrypt  : function (a, b, c, d) {
            d = this.cfg.extend(d);
            b = this._parse(b, d.format);
            return a.createDecryptor(c, d).finalize(b.ciphertext)
        }, _parse: function (a, b) {
            return "string" == typeof a ? b.parse(a, this) : a
        }
    }), p = (p.kdf = {}).OpenSSL = {
        execute: function (a, b, c, d) {
            d || (d = s.random(8));
            a          = w.create({keySize: b + c}).compute(a, d);
            c          = s.create(a.words.slice(b), 4 * c);
            a.sigBytes = 4 * b;
            return n.create({key: a, iv: c, salt: d})
        }
    }, c = d.PasswordBasedCipher = a.extend({
        cfg       : a.cfg.extend({kdf: p}), encrypt: function (b, c, d, l) {
            l    = this.cfg.extend(l);
            d    = l.kdf.execute(d,
                b.keySize, b.ivSize);
            l.iv = d.iv;
            b    = a.encrypt.call(this, b, c, d.key, l);
            b.mixIn(d);
            return b
        }, decrypt: function (b, c, d, l) {
            l    = this.cfg.extend(l);
            c    = this._parse(c, l.format);
            d    = l.kdf.execute(d, b.keySize, b.ivSize, c.salt);
            l.iv = d.iv;
            return a.decrypt.call(this, b, c, d.key, l)
        }
    })
}();
(function () {
    for (var u = CryptoJS, p = u.lib.BlockCipher, d = u.algo, l = [], s = [], t = [], r = [], w = [], v = [], b = [], x = [], q = [], n = [], a = [], c = 0; 256 > c; c ++)a[c] = 128 > c ? c << 1 : c << 1 ^ 283;
    for (var e = 0, j = 0, c = 0; 256 > c; c ++) {
        var k = j ^ j << 1 ^ j << 2 ^ j << 3 ^ j << 4, k = k >>> 8 ^ k & 255 ^ 99;
        l[e]  = k;
        s[k]  = e;
        var z = a[e], F = a[z], G = a[F], y = 257 * a[k] ^ 16843008 * k;
        t[e]  = y << 24 | y >>> 8;
        r[e]  = y << 16 | y >>> 16;
        w[e]  = y << 8 | y >>> 24;
        v[e]  = y;
        y     = 16843009 * G ^ 65537 * F ^ 257 * z ^ 16843008 * e;
        b[k]  = y << 24 | y >>> 8;
        x[k]  = y << 16 | y >>> 16;
        q[k]  = y << 8 | y >>> 24;
        n[k]  = y;
        e ? (e = z ^ a[a[a[G ^ z]]], j ^= a[a[j]]) : e = j = 1
    }
    var H                           = [0, 1, 2, 4, 8,
        16, 32, 64, 128, 27, 54], d = d.AES = p.extend({
        _doReset        : function () {
            for (var a = this._key, c = a.words, d = a.sigBytes / 4, a = 4 * ((this._nRounds = d + 6) + 1), e = this._keySchedule = [], j = 0; j < a; j ++)if (j < d)e[j] = c[j]; else {
                var k = e[j - 1];
                j % d ? 6 < d && 4 == j % d && (k = l[k >>> 24] << 24 | l[k >>> 16 & 255] << 16 | l[k >>> 8 & 255] << 8 | l[k & 255]) : (k = k << 8 | k >>> 24, k = l[k >>> 24] << 24 | l[k >>> 16 & 255] << 16 | l[k >>> 8 & 255] << 8 | l[k & 255], k ^= H[j / d | 0] << 24);
                e[j] = e[j - d] ^ k
            }
            c = this._invKeySchedule = [];
            for (d = 0; d < a; d ++)j = a - d, k = d % 4 ? e[j] : e[j - 4], c[d] = 4 > d || 4 >= j ? k : b[l[k >>> 24]] ^ x[l[k >>> 16 & 255]] ^ q[l[k >>>
            8 & 255]] ^ n[l[k & 255]]
        }, encryptBlock : function (a, b) {
            this._doCryptBlock(a, b, this._keySchedule, t, r, w, v, l)
        }, decryptBlock : function (a, c) {
            var d    = a[c + 1];
            a[c + 1] = a[c + 3];
            a[c + 3] = d;
            this._doCryptBlock(a, c, this._invKeySchedule, b, x, q, n, s);
            d        = a[c + 1];
            a[c + 1] = a[c + 3];
            a[c + 3] = d
        }, _doCryptBlock: function (a, b, c, d, e, j, l, f) {
            for (var m = this._nRounds, g = a[b] ^ c[0], h = a[b + 1] ^ c[1], k = a[b + 2] ^ c[2], n = a[b + 3] ^ c[3], p = 4, r = 1; r < m; r ++)var q = d[g >>> 24] ^ e[h >>> 16 & 255] ^ j[k >>> 8 & 255] ^ l[n & 255] ^ c[p ++], s = d[h >>> 24] ^ e[k >>> 16 & 255] ^ j[n >>> 8 & 255] ^ l[g & 255] ^ c[p ++], t =
                d[k >>> 24] ^ e[n >>> 16 & 255] ^ j[g >>> 8 & 255] ^ l[h & 255] ^ c[p ++], n                                                            = d[n >>> 24] ^ e[g >>> 16 & 255] ^ j[h >>> 8 & 255] ^ l[k & 255] ^ c[p ++], g = q, h = s, k = t;
            q        = (f[g >>> 24] << 24 | f[h >>> 16 & 255] << 16 | f[k >>> 8 & 255] << 8 | f[n & 255]) ^ c[p ++];
            s        = (f[h >>> 24] << 24 | f[k >>> 16 & 255] << 16 | f[n >>> 8 & 255] << 8 | f[g & 255]) ^ c[p ++];
            t        = (f[k >>> 24] << 24 | f[n >>> 16 & 255] << 16 | f[g >>> 8 & 255] << 8 | f[h & 255]) ^ c[p ++];
            n        = (f[n >>> 24] << 24 | f[g >>> 16 & 255] << 16 | f[h >>> 8 & 255] << 8 | f[k & 255]) ^ c[p ++];
            a[b]     = q;
            a[b + 1] = s;
            a[b + 2] = t;
            a[b + 3] = n
        }, keySize      : 8
    });
    u.AES = p._createHelper(d)
})();