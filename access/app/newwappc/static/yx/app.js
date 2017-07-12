/**
 *
 * 主线wap应用
 * 严旭
 * 2017-02-07 10:21:15
 *
 */


var yxspa                  = {};
yxspa.page_controller      = {};
yxspa.api_prefix           = "http://120.27.212.31/mobile.php/";
yxspa.image_prefix         = "http://120.27.212.31/upload/logo/";
yxspa.pay_url              = yxspa.api_prefix + "Pay/preorder";	// 支付地址
yxspa.login                = false;
yxspa.user                 = {
    "mem_id"    : "",
    "session_id": ""
};
yxspa.api                  = {};
var yxconfig               = {
    "app_name"               : "z+游戏",
    "show_home_download_area": true,
    "app_down_url"           : "#"
};
var current_search_keyword = "不良人";

yxspa.api.regNormal            = yxspa.api_prefix + "AppApiDev/regNormal";
yxspa.api.getCaptchaImg        = yxspa.api_prefix + "AppApiDev/getCaptchaImg";
yxspa.api.getAppDetailInfo     = yxspa.api_prefix + "AppApiDev/getAppDetailInfo";
yxspa.api.getIosAppPostDetail  = yxspa.api_prefix + "AppApiDev/getIosAppPostDetail";
yxspa.api.getIosAppPosts       = yxspa.api_prefix + "AppApiDev/getIosAppPosts";
yxspa.api.getSlides            = yxspa.api_prefix + "AppApiDev/getSlides";
yxspa.api.getNewApps           = yxspa.api_prefix + "AppApiDev/newApps";
yxspa.api.getHotApps           = yxspa.api_prefix + "AppApiDev/rankHotApps";
yxspa.api.getGYApps            = yxspa.api_prefix + "AppApiDev/GetGYApps";
yxspa.api.getKeyApps           = yxspa.api_prefix + "AppApiDev/keyApps";
yxspa.api.login                = yxspa.api_prefix + "AppApiDev/checkLogin";
yxspa.api.cates                = yxspa.api_prefix + "AppApiDev/cates";
yxspa.api.rankNewApps          = yxspa.api_prefix + "AppApiDev/rankNewApps";
yxspa.api.rankHotApps          = yxspa.api_prefix + "AppApiDev/rankHotApps";
yxspa.api.onlineNewApps        = yxspa.api_prefix + "AppApiDev/onlineNewApps";
yxspa.api.onlineHotApps        = yxspa.api_prefix + "AppApiDev/onlineHotApps";
yxspa.api.cateNewApps          = yxspa.api_prefix + "AppApiDev/cateNewApps";
yxspa.api.cateHotApps          = yxspa.api_prefix + "AppApiDev/cateHotApps";
yxspa.api.search               = yxspa.api_prefix + "AppApiDev/search";
yxspa.api.reg                  = yxspa.api_prefix + "AppApiDev/reg";
yxspa.api.sendPhoneCode        = yxspa.api_prefix + "AppApiDev/sendPhoneCode";
yxspa.api.findpwd              = yxspa.api_prefix + "AppApiDev/findpwd";
yxspa.api.sendPhoneCodeToExist = yxspa.api_prefix + "AppApiDev/sendPhoneCodeToExist";
yxspa.api.gift                 = yxspa.api_prefix + "AppApiDev/gift";
yxspa.api.giftDetail           = yxspa.api_prefix + "AppApiDev/giftDetail";
yxspa.api.getGiftCode          = yxspa.api_prefix + "AppApiDev/getGiftCode";
yxspa.api.myGiftCodes          = yxspa.api_prefix + "AppApiDev/myGiftCodes";
yxspa.api.getCompanyAbout      = yxspa.api_prefix + "AppApiDev/getCompanyAbout";
yxspa.api.getContactInfo       = yxspa.api_prefix + "AppApiDev/getContactInfo";
yxspa.api.loginThird           = yxspa.api_prefix + "AppApiDev/loginThird";
yxspa.api.loginState           = yxspa.api_prefix + "AppApiDev/loginState";
yxspa.api.logout               = yxspa.api_prefix + "AppApiDev/logout";
yxspa.api.getServerList        = yxspa.api_prefix + "AppApiDev/getServerList";
yxspa.api.getAbs               = yxspa.api_prefix + "AppApiDev/get_abs";
yxspa.api.getappinfo           = yxspa.api_prefix + "AppApiDev/getAppInfo";

yxspa.dev_mode = false;

function app_login_callback(type, userid, token) {
//   alert("type="+type+" userid="+userid+" token="+token);
    var type_txt = "";
    if (type === 2) {
        type_txt = "qq";
    } else if (type === 3) {
        type_txt = "wx";
    }
    $.post(yxspa.api.loginThird, {"type": type_txt, "open_id": userid, "access_token": token}, function (res) {
        if (res.error === '0') {
            yxspa.login     = true;
            yxspa.data.user = res.msg;
            location.replace("#/account");
        } else if (res.error === '1') {
            yxspa.login = false;
            yxshare.ui.alert(res.msg);
        }
    });
}

yxspa.data      = {};
yxspa.data.user = {
    "id"      : "",
    "username": "示例玩家",
    "icon"    : "./static/img/icon/2.jpg"
};

yxspa.funcs     = {};
yxspa.funcs.log = function (obj) {
    //console.log(obj);
};

yxspa.values                   = {};
yxspa.values.current_cate_name = '';

yxspa.funcs.showH5Game = function (appid) {
    //alert("打开新的游戏窗口");
//    location.href="http://h5i.6533.com/sdk.php/Game/game/appid/100006/agent/0";
//location.href="http://h5i.6533.com/sdk.php/Game/game/appid/"+appid;

    location.href = "#/appDetail/" + appid;
};

//$scope.showH5Game=function(appid){
//        location.href="http://h5i.6533.com/sdk.php/Game/game/appid/"+appid;
//    };
yxspa.funcs.goto   = function (url) {
    location.href = url;
};
yxspa.funcs.goback = function () {
    history.go(- 1);
};

var yxapp = angular.module('app', ['ngRoute']);
yxapp.config(['$routeProvider', function ($routeProvider) {
    $routeProvider.when('/', {
        redirectTo: '/home'
    }).when('/home', {
        templateUrl: 'pages/home.html'
    }).when('/gift', {
        templateUrl: 'pages/gift.html'
    }).when('/giftDetail/:id', {
        templateUrl: 'pages/giftDetail.html'
    }).when('/rank', {
        templateUrl: 'pages/rank.html',
    }).when('/online', {
        templateUrl: 'pages/online.html',
    }).when('/cate', {
        templateUrl: 'pages/cate.html',
    }).when('/cateDetail/:id', {
        templateUrl: 'pages/cateDetail.html'
    }).when('/search', {
        templateUrl: 'pages/search.html',
    }).when('/account', {
        templateUrl: 'pages/account.html',
    }).when('/login', {
        templateUrl: 'pages/login.html',
    }).when('/register', {
        templateUrl: 'pages/register.html',
    }).when('/reg_normal', {
        templateUrl: 'pages/reg_normal.html',
    }).when('/findpwd', {
        templateUrl: 'pages/findpwd.html',
    }).when('/cardbox', {
        templateUrl: 'pages/cardbox.html',
    }).when('/server', {
        templateUrl: 'pages/server.html',
    }).when('/news', {
        templateUrl: 'pages/news.html',
    }).when('/news_detail/:id', {
        templateUrl: 'pages/newsDetail.html'
    }).when('/cs', {
        templateUrl: 'pages/cs.html',
    }).when('/charge/:id', {
        templateUrl: 'pages/charge.html',
    }).when('/about', {
        templateUrl: 'pages/about.html',
    }).when('/agreement', {
        templateUrl: 'pages/agreement.html',
    }).when('/play', {
        templateUrl: 'pages/play.html',
    }).when('/appDetail/:id', {
        templateUrl: 'pages/appDetail.html',
    }).when('/appDownload', {
        templateUrl: 'pages/appDownload.html',
    }).otherwise({
        redirectTo: '/'
    });
}
]);

yxapp.controller("moduleGiftDetailController", function ($scope, $route, $http) {

    var id = $route.current.params.id;
    console.log('id=' + id);

    $http.get(yxspa.api.giftDetail + "/id/" + id).success(function (res) {
        $scope.data         = res;
        $scope.data.warning = "请尽快使用";
    });

//    $scope.data1={
//        "id":id,
//        "name":"传奇世界圣诞礼包",
//        "icon":"./static/img/icon/1.png",
//        "remain":90,
//        "start_time":"2016-12-27 23:05:15",
//        "end_time":"2016-12-27 23:05:18",
//        "content":"\
//加速卡x5,加速卡x5,<br />仙品元婴丹x10,仙品元婴丹x10,<br />仙品元婴丹x10,<br />\n\
//捉妖卡x20\n\
//<br /><br />\n\
//使用说明：\n\
//点击设置，兑换激活码",
//        "warning":"请尽快使用",
//    };

    $scope.getGiftCode = function () {
        $.post(yxspa.api.getGiftCode + "/gift_id/" + id, {}, function (res) {
            if (res.error === '0') {
                yxshare.ui.alert2(res.msg);
            } else if (res.error === '1') {
                layer.open({
                    content: res.msg,
                    btn    : ['去登陆', '取消'],
                    yes    : function (index, layero) {
                        window.location.href = 'index.html#/login';
                        history.go(0);
                    },
                    btn2   : function (index, layero) {

                    }
                    ,
                    cancel : function () {
                        //右上角关闭回调
                    }
                });
            }
        });
    };

    $scope.goto = function (app_id) {
        location.href = "#/appDetail/" + app_id;
    };
});
yxapp.directive('moduleGiftDetail', function () {
    return {
        templateUrl: 'modules/GiftDetail.html',
        replace    : true
    };
});

yxapp.controller("pageNewsController", function ($scope, $route) {

});

yxapp.filter('to_trusted', ['$sce', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
}]);

yxapp.controller("moduleNaviController", function ($scope, $route) {
    $scope.list     = [
        {"name": "首页", "link": "#/home"},
        {"name": "所有商品", "link": "#/products"},
//        {"name": "我的订单", "link": "#/my_orders"},
//        {"name": "地址管理", "link": "#/my_addr"},

        {"name": "我的账户", "link": "#/account"}
    ];
    $scope.sitename = "";
});
yxapp.directive('modulenavi', function () {
    return {
        templateUrl: 'modules/navi.html',
        replace    : true
    };
});

yxapp.controller("moduleFooterController", function ($scope, $route) {

});
yxapp.directive('modulefooter', function () {
    return {
        templateUrl: 'modules/footer.html',
        replace    : true
    };
});

yxapp.controller("moduleFullscreenExternalController", ['$scope', '$sce', function ($scope, $sce) {
    var url    = "http://h5i.6533.com/sdk.php/Game/game/appid/100006/agent/0";
    $scope.url = $sce.trustAs($sce.RESOURCE_URL, url);
}]);
yxapp.directive('moduleFullscreenExternal', function () {
    return {
        templateUrl: 'modules/FullscreenExternal.html',
        replace    : true
    };
});

yxapp.controller("moduleStarRateController", function ($scope, $route) {
    $scope.list = [{'1': ""}, {'1': ""}, {'1': ""}, {'1': ""}, {'1': ""}];
});
yxapp.directive('moduleStarRate', function () {
    return {
        templateUrl: 'modules/StarRate.html',
        replace    : true
    };
});

yxapp.controller("moduleHeaderController", function ($scope, $route) {
    $scope.app_name = yxconfig.app_name;
});
yxapp.directive('moduleHeader', function () {
    return {
        templateUrl: 'modules/Header.html',
        replace    : true
    };
});

yxapp.controller("moduleCardCodeListController", function ($scope, $route, $http) {
    $scope.list = [];
    $scope.page = 1;
    $scope.load = function () {
        $http.get(yxspa.api.myGiftCodes + "/page/" + $scope.page).success(function (res) {
            if (res.length >= 1) {
                $scope.list = res;
                $scope.list.concat($scope.list);
                $scope.page ++;
            } else {
                $("#loadMoreText").text("没有更多了");
            }
        });
    };
    $scope.load();

    $scope.list1 = [
        {"name": "传奇世界", "code": "12341234", "icon": "./static/img/icon/1.png"},
        {"name": "联盟与部落", "code": "123412341234", "icon": "./static/img/icon/2.jpg"},
        {"name": "决战沙城", "code": "werqwer", "icon": "./static/img/icon/3.png"},
        {"name": "盛世霸业", "code": "qwerasdfasdf", "icon": "./static/img/icon/4.png"},
        {"name": "传奇世界", "code": "qwerqwefqwdf", "icon": "./static/img/icon/1.png"},
        {"name": "联盟与部落", "code": "adfasdfasdf", "icon": "./static/img/icon/2.jpg"},
        {"name": "决战沙城", "code": "dfasdfasfdd", "icon": "./static/img/icon/3.png"},
        {"name": "盛世霸业", "code": "asdfasdasvs", "icon": "./static/img/icon/4.png"},
        {"name": "传奇世界", "code": "fghjfgjh", "icon": "./static/img/icon/1.png"},
        {"name": "联盟与部落", "code": "fgjhfjhfj", "icon": "./static/img/icon/2.jpg"},

    ];

    $scope.loadMore = function () {
        $scope.load();
//            var items=[
//                {"name":"决战沙城","code":"wretwrt","icon":"./static/img/icon/3.png"},
//                {"name":"盛世霸业","code":"sdgf3242","icon":"./static/img/icon/4.png"},
//                {"name":"盛世霸业","code":"asdfasdasvs","icon":"./static/img/icon/4.png"},
//                {"name":"传奇世界","code":"fghjfgjh","icon":"./static/img/icon/1.png"},
//                {"name":"联盟与部落","code":"fgjhfjhfj","icon":"./static/img/icon/2.jpg"},
//                {"name":"决战沙城","code":"wretwrt","icon":"./static/img/icon/3.png"},
//                {"name":"盛世霸业","code":"sdgf3242","icon":"./static/img/icon/4.png"},
//            ];
//            $scope.list=$scope.list.concat(items);
        //console.log($scope.list);
        //$("#loadMoreText").text("没有更多了");
    };
});
yxapp.directive('moduleCardCodeList', function () {
    return {
        templateUrl: 'modules/CardCodeList.html',
        replace    : true
    };
});

yxapp.controller("moduleAccountCenterController", function ($scope, $route, $http) {

    $http.get(yxspa.api.loginState).success(function (res) {
        if (res.state === "true") {
            yxspa.login     = true;
            yxspa.data.user = res.user;
        } else if (res.state === "false") {
            yxspa.login = false;
        }

        $scope.data  = yxspa.data.user;
        $scope.login = yxspa.login;
        if (! $scope.login) {
            location.replace("#/login");
            return;
        }
    });

    $scope.logout = function () {

        yxshare.ui.confirm("真的要退出吗？", function () {
            $http.get(yxspa.api.logout).success(function (res) {
                if (res.error === '0') {
                    yxspa.login   = false;
                    location.href = "#/home";
                }
            });

        });
//        var con=confirm("真的要退出吗？");
//        if(con){
////           alert("退出成功");
////           location.reload();
//            location.href="#/home";
//        }
    };
});
yxapp.directive('moduleAccountCenter', function () {
    return {
        templateUrl: 'modules/AccountCenter.html',
        replace    : true
    };
});

yxapp.controller("moduleMyordersController", function ($scope, $http) {
//    $http.get(yxspa.api.getMyOrders).success(function(res){
//        $scope.orders=res;
//        yxspa.funcs.log(res);
//    });
    $scope.orders = [
        {
            "type"       : "实物商品",
            "name"       : "示例商品1",
            "price"      : "20",
            "num"        : "2",
            "status"     : "交易成功",
            "create_time": "2016-12-09 21:32:46",
            "order_id"   : "1234123412341234",
            "remark"     : ""
        },
        {
            "type"       : "充值卡",
            "name"       : "示例商品1",
            "price"      : "20",
            "num"        : "1",
            "status"     : "正在运输",
            "create_time": "2016-12-09 21:32:46",
            "order_id"   : "1234123412341234",
            "remark"     : ""
        },
        {
            "type"       : "虚拟商品",
            "name"       : "示例商品1",
            "price"      : "20",
            "num"        : "3",
            "status"     : "交易失败",
            "create_time": "2016-12-09 21:32:46",
            "order_id"   : "1234123412341234",
            "remark"     : ""
        },
        {
            "type"       : "游戏币",
            "name"       : "示例商品1",
            "price"      : "20",
            "num"        : "2",
            "status"     : "交易成功",
            "create_time": "2016-12-09 21:32:46",
            "order_id"   : "1234123412341234",
            "remark"     : ""
        },
        {
            "type"       : "实物商品",
            "name"       : "示例商品1",
            "price"      : "20",
            "num"        : "1",
            "status"     : "正在运输",
            "create_time": "2016-12-09 21:32:46",
            "order_id"   : "1234123412341234",
            "remark"     : ""
        },
        {
            "type"       : "实物商品",
            "name"       : "示例商品1",
            "price"      : "20",
            "num"        : "3",
            "status"     : "交易失败",
            "create_time": "2016-12-09 21:32:46",
            "order_id"   : "1234123412341234",
            "remark"     : ""
        }
    ];
});

yxapp.directive('modulemyorders', function () {
    return {
        templateUrl: 'modules/my_orders.html',
        replace    : true
    };
});

yxapp.controller("moduleLoinFullBackgroundController", function ($scope, $route, $location) {
    $scope.login          = yxspa.login;
    $scope.data           = {
        "username": "",
        "password": "",
        "code"    : ""
    };
    $scope.login          = function () {
//        alert("HI");
//        yxspa.funcs.log($scope.data);
        var userName  = $scope.data.username;
        var userPass  = $scope.data.password;
        var checkCode = $scope.data.code;

        if (((userName) === "") || ((userPass) === "")) {
            yxshare.ui.alert("用户名或密码不能为空");
            return;
        }

        if ($.trim(checkCode) === "") {
            yxshare.ui.alert("验证码不能为空");
            return;
        }

        $.post(yxspa.api.login, {"username": userName, "password": userPass, "verify": checkCode},
            function (data) {
                if (data.error === '1') {
                    yxshare.ui.alert(data.msg);
                    $scope.change_captcha();
                    return;
                } else if (data.error === '0') {
                    yxspa.user.mem_id = data.msg;
                    yxspa.funcs.log(yxspa.user.mem_id);
//                        $location.path("#/account");
//                        $route.redirectTo("#/account");
//
                    window.location.href = "#/account";
                }
            });

    };
    $scope.change_captcha = function () {
        document.getElementById('verify_img').src = "/checkcode/index?length=4&font_size=20&width=248&height=42&use_noise=1&use_curve=0";
    };

});
yxapp.directive('moduleloginfullbackground', function () {
    return {
        templateUrl: 'modules/login_full_background.html',
        replace    : true
    };
});

yxapp.controller("moduleLoinFullController", function ($scope, $route, $location, $http) {
    $scope.login = yxspa.login;
    $scope.data  = {
        "username": "",
        "password": "",
    };

    $scope.login = function () {

        var userName = $scope.data.username;
        var userPass = $scope.data.password;
        if (((userName) === "") || ((userPass) === "")) {
            yxshare.ui.alert("用户名或密码不能为空");
            return;
        }

        $("#index_login_btn").text("登录中...");
        $.post(yxspa.api.login, $scope.data, function (res) {
            if (res.error === '0') {
                $("#index_login_btn").text("登录成功，正在跳转...");
                yxspa.login     = true;
                yxspa.data.user = res.msg;
                location.replace("#/account");
                return;
            } else if (res.error === '1') {
                yxspa.login = false;
                yxshare.ui.alert(res.msg);
                $("#index_login_btn").text("登录");
            }
        });

    };

    $scope.login3rd = function (type) {
        if (type == 'qq') {
            h5app.loginByThird(2, "101375586", "05d1a6488a68b6ec245ccbe9719c5598", null);
        } else if (type == 'wx') {
            h5app.loginByThird(3, "wx27692afc3755a862", "082fd6a03d3f9c0249b374ecd0ca0aea", null);
        }
    };

    $scope.login2 = function () {
        if ($scope.data.username === "yanxu" && $scope.data.password === "123456") {
            yxspa.login   = true;
            location.href = "#/account";
        } else {
            yxshare.ui.alert("用户名或密码错误");
        }

    };

    $scope.login1 = function () {
//        alert("HI");
//        yxspa.funcs.log($scope.data);
        var userName = $scope.data.username;
        var userPass = $scope.data.password;
        if (((userName) === "") || ((userPass) === "")) {
            yxshare.ui.alert("用户名或密码不能为空");
            return;
        }

        $.post(yxspa.api.login, {"username": userName, "password": userPass, "verify": checkCode},
            function (data) {
                if (data.error === '1') {
                    yxshare.ui.alert(data.msg);
                    $scope.change_captcha();
                    return;
                } else if (data.error === '0') {
                    yxspa.user.mem_id = data.msg;
                    yxspa.funcs.log(yxspa.user.mem_id);
//                        $location.path("#/account");
//                        $route.redirectTo("#/account");
//
                    window.location.href = "#/account";
                }
            });
    };
});
yxapp.directive('moduleLoginFull', function () {
    return {
        templateUrl: 'modules/login_full.html',
        replace    : true
    };
});
yxapp.controller("moduleMyaccountController", function ($scope, $route, $http) {
    $scope.login = yxspa.login;
    $scope.data  = {
        "name"   : "严旭",
        "phone"  : "15507501312",
        "addr"   : "北京市东花市北里20号楼6单元501室",
        "icon"   : "./static/img/products/icon_14.jpg",
        "balance": "100"
    };

//    $http.get(yxspa.api.loginState).success(function(res){
////        yxspa.funcs.log(res);
////
////        if(res.msg!=='true'){
////            $scope.login=false;
////            location.href="#/login";
////            return;
////        }
////        $scope.login=true;
//
////        $http.get(yxspa.api.getMemInfo).success(function(res){
////            $scope.data=res;
////        });
//    });

    $scope.logout = function () {
        var con = confirm("确定要退出吗？");
        if (con) {
            $.post(yxspa.api.logout, {}, function () {
                location.href = "#/login";

//                    location.reload();
            });

        }
    };

    $scope.modify_addr  = function () {
        layer.prompt({title: '请输入新的收货地址', formType: 2, shift: 2, maxlength: 100}, function (text, index) {
            layer.close(index);
            $scope.data.addr = text;
            $scope.$apply();
            $.post(yxspa.api.setMemAddr, {"addr": text}).success(function (res) {
                yxspa.funcs.log(res);
            });
        });
    };
    $scope.modify_phone = function () {
        layer.prompt({title: '请输入新的手机号码', formType: 0, shift: 2, maxlength: 11}, function (text, index) {
            layer.close(index);
            $scope.data.phone = text;
            $scope.$apply();

            $.post(yxspa.api.setMemPhone, {"phone": text}).success(function (res) {
                yxspa.funcs.log(res);
            });
        });
    };

    $scope.modify_realname = function () {
        layer.prompt({title: '请输入您的真实姓名', formType: 0, shift: 2, maxlength: 11}, function (text, index) {
            layer.close(index);
            $scope.data.realname = text;
            $scope.$apply();

            $.post(yxspa.api.setMemRealname, {"realname": text}).success(function (res) {
                yxspa.funcs.log(res);
            });
        });
    };

    $scope.viewCodePass = function (ccid) {
        $http.get(yxspa.api.viewCardPass + "?id=" + ccid).success(function (res) {
            yxshare.ui.alert(res.msg);
//                $scope.cardcodes.index.used=1;
//                $scope.$apply();
        });
    };

});
yxapp.directive('modulemyaccount', function () {
    return {
        templateUrl: 'modules/my_account.html',
        replace    : true
    };
});

yxapp.controller("moduleProductItemController", function ($scope, $route, $http) {
    $scope.num     = 1;
    var product_id = $route.current.params.id;
    $http.get(yxspa.api.getProductInfo + "?id=" + product_id).success(function (res) {
        $scope.data = res;
        yxspa.funcs.log(res);
    });

//    $scope.remain=100;
//    $scope.detail_info="<p>商品详细情况<a href=''>链接</a></p>";
//    $scope.price=20;
    $scope.incNum = function () {
        if ($scope.num >= $scope.data.remain) {
            return;
        }
        $scope.num ++;
    };
    $scope.decNum = function () {
        if ($scope.num <= 1) {
            return;
        }
        $scope.num --;
    };
    $scope.buyit  = function () {
//        var con=confirm();
        var show_text = "真的要兑换此商品吗？您将消耗" + $scope.num * $scope.data.price + "积分";
        yxshare.ui.confirm(show_text, function () {
            var product_id = $route.current.params.id;
            $.post(yxspa.api.addOrder, {"product_id": product_id, "num": $scope.num}).success(function (res) {
                console.log(res);
                if (res.error === '0') {
                    yxshare.ui.alert("兑换成功！");
                    location.reload();
                } else if (res.error === '1') {
                    yxshare.ui.alert(res.msg);
                    if (res.code && res.code == '2') {
                        location.href = "#/account";
                    }
                }
            });
        });
//        if(con){
//
//        }

    };
});
yxapp.directive('moduleproductitem', function () {
    return {
        templateUrl: 'modules/jifen_product_item.html',
        replace    : true
    };
});

yxapp.controller("moduleJifenProductsController", function ($scope, $http) {
    $scope.list1 = [
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
//        {"icon":"./static/img/products/24.jpg","name":"积分商品","price":"20"},
    ];
    for (var i = 1; i <= 36; i ++) {
        var item = {"icon": "./static/img/products/icon_" + i + ".jpg", "name": "积分商品" + i, "price": "20"};
        $scope.list1.push(item);
    }

    $http.get(yxspa.api.getNewProducts).success(function (res) {
        $scope.list = res;
        yxspa.funcs.log(res);
    });

    $scope.viewItem = function (id) {
        location.href = '#/product_item/' + id;
    };
});
yxapp.controller("moduleJifenProducts2Controller", function ($scope, $http) {
    $scope.current_page = 1;

    $scope.viewItem = function (id) {
        location.href = '#/product_item/' + id;
    };

    $scope.prevPage   = function () {
        if ($scope.current_page <= 1) {
            return;
        }
        $scope.current_page --;
        $scope.getContent();
    };
    $scope.nextPage   = function () {
        if ($scope.current_page >= $scope.total_pages) {
            return;
        }
        $scope.current_page ++;
        $scope.getContent();
    };
    $scope.getContent = function () {
        $http.get(yxspa.api.getAllProducts + "?page=" + $scope.current_page).success(function (res) {
            $scope.total_pages = res.total_pages;
            $scope.list        = res.data;
            yxspa.funcs.log(res);
        });
    };
    $scope.getContent();

});
yxapp.directive('modulejifenproducts', function () {
    return {
        templateUrl: 'modules/jifen_products.html',
        replace    : true
    };
});
yxapp.directive('modulejifenproductsall', function () {
    return {
        templateUrl: 'modules/jifen_products_all.html',
        replace    : true
    };
});

yxapp.controller("moduleSlidesController", function ($scope, $http) {

    if (yxspa.dev_mode) {
        $scope.list = [
            {'image': './static/img/slides/banner_3.jpg'},
            {'image': './static/img/slides/banner_4.jpg'},
            {'image': './static/img/slides/banner_3.jpg'},
            {'image': './static/img/slides/banner_4.jpg'}
        ];
    } else {
        $http.get(yxspa.api.getSlides).success(function (res) {
            if (res.length >= 1) {
                $scope.list = res;
            } else {
                $scope.list = [
                    {'image': './static/img/slides/banner_3.jpg'},
                    {'image': './static/img/slides/banner_4.jpg'},
                    {'image': './static/img/slides/banner_3.jpg'},
                    {'image': './static/img/slides/banner_4.jpg'}
                ];
            }
        });
    }
    $scope.goto5 = function (appid) {
        location.href = "#/appDetail/" + appid;
    };
    $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
        var swiper = new Swiper('.swiper-container', {
            autoplay           : 3000,
            centeredSlides     : true,
            pagination         : '.swiper-pagination',
            paginationClickable: '.swiper-pagination',
//                nextButton: '.swiper-button-next',
//                prevButton: '.swiper-button-prev',
            spaceBetween       : 30
        });
    });
});
yxapp.directive('moduleSlides', function () {
    return {
        templateUrl: 'modules/slides.html',
        replace    : true

    };
});

yxapp.directive('onFinishRenderFilters', function ($timeout) {
    return {
        restrict: 'A',
        link    : function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {
                    scope.$emit('ngRepeatFinished');
                });
            }
        }
    };
});

yxapp.controller("moduleBrefintroController", function ($scope, $http) {
    var url = yxspa.api_prefix + "?action=get_products_data";
    $http.get(url).success(function (res) {
        $scope.products = res;
    });
    $scope.detail = function () {

    };

});
yxapp.directive('modulebrefintro', function () {
    return {
        templateUrl: 'modules/brefintro.html',
        replace    : true

    };
});

yxapp.controller("moduleProductlistController", function ($scope, $http) {
    var url = yxspa.api_prefix + "?action=get_products_data";
    $http.get(url).success(function (res) {
        $scope.products = res;
    });
    $scope.showinfo = function (index) {
        var text = $scope.products[index]['info'];
        $("#product_info").html(text);
    }
});
yxapp.directive('moduleproductlist', function () {
    return {
        templateUrl: 'modules/productlist.html',
        replace    : true

    };
});

yxapp.controller("moduleCateListGroupController", function ($scope, $http) {

    $http.get(yxspa.api.cates).success(function (res) {
        $scope.list = res;
    });

    $scope.list1 = [
        {"id": 1, "name": "休闲类", "icon": "./static/img/asset/cate.png", "num": "1000002"},
        {"id": 2, "name": "脑力类", "icon": "./static/img/asset/cate.png", "num": "101202"},
        {"id": 3, "name": "敏捷类", "icon": "./static/img/asset/cate.png", "num": "1021302"},
        {"id": 4, "name": "益智类", "icon": "./static/img/asset/cate.png", "num": "1002"},
        {"id": 5, "name": "闯关类", "icon": "./static/img/asset/cate.png", "num": "111002"},
        {"id": 6, "name": "消除类", "icon": "./static/img/asset/cate.png", "num": "1002"},
        {"id": 7, "name": "装扮类", "icon": "./static/img/asset/cate.png", "num": "1002"},
        {"id": 8, "name": "动作类", "icon": "./static/img/asset/cate.png", "num": "123002"},
        {"id": 9, "name": "策略类", "icon": "./static/img/asset/cate.png", "num": "104202"},
        {"id": 10, "name": "赛车类", "icon": "./static/img/asset/cate.png", "num": "1002"},
        {"id": 11, "name": "射击类", "icon": "./static/img/asset/cate.png", "num": "10102"},
    ];

    $scope.goto = function (id, name) {
        location.href                  = "#/cateDetail/" + id;
        yxspa.values.current_cate_name = name;
    };

});
yxapp.directive('moduleCateListGroup', function () {
    return {
        templateUrl: 'modules/CateListGroup.html',
        replace    : true

    };
});

yxapp.controller("moduleAppRankController", function ($scope, $http) {
    $scope.tab = 1;

    $scope.newlist  = [];
    $scope.page_new = 1;
    $scope.loadNew  = function () {
        $http.get(yxspa.api.rankNewApps + "/page/" + $scope.page_new).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText_new").text("没有更多了");
                return;
            }
            $scope.newlist = $scope.newlist.concat(res);
        });
        $scope.page_new ++;
    };

    $scope.hotlist  = [];
    $scope.page_hot = 1;
    $scope.loadHot  = function () {
        $http.get(yxspa.api.rankHotApps + "/page/" + $scope.page_hot).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText_hot").text("没有更多了");
                return;
            }
            $scope.hotlist = $scope.hotlist.concat(res);
        });
        $scope.page_hot ++;
    };

    $scope.loadNew();
    $scope.loadHot();

    $scope.loadMoreHot = function () {
        $scope.loadHot();
    };
    $scope.loadMoreNew = function () {
        $scope.loadNew();
    };

    $scope.showH5Game = yxspa.funcs.showH5Game;

//       $scope.newlist1=[
//            {"name":"传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业","tag":"精品","icon":"./static/img/icon/4.png"},
//            {"name":"传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业","tag":"精品","icon":"./static/img/icon/4.png"},
//            {"name":"传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业","tag":"精品","icon":"./static/img/icon/4.png"},
//       ];
//       $scope.hotlist1=[
//            {"name":"传奇世界hot","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落hot","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城hot","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业hot","tag":"精品","icon":"./static/img/icon/4.png"},
//            {"name":"传奇世界hot","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落hot","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城hot","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业hot","tag":"精品","icon":"./static/img/icon/4.png"},
//            {"name":"传奇世界hot","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落hot","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"hot决战沙城","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"hot盛世霸业","tag":"精品","icon":"./static/img/icon/4.png"},
//       ];

    $scope.show = function (s) {
        $scope.tab = s;
        $("#new_tab").removeClass("AppRank_tab_active");
        $("#hot_tab").removeClass("AppRank_tab_active");
        if (s == 1) {
            $("#new_tab").addClass("AppRank_tab_active");
        } else if (s == 2) {
            $("#hot_tab").addClass("AppRank_tab_active");
        }
    };
});
yxapp.directive('moduleAppRank', function () {
    return {
        templateUrl: 'modules/AppRank.html',
        replace    : true

    };
});
yxapp.controller("moduleAppOnlineController", function ($scope, $http) {
    $scope.tab = 1;

    $scope.newlist  = [];
    $scope.page_new = 1;
    $scope.loadNew  = function () {
        $http.get(yxspa.api.onlineNewApps + "/page/" + $scope.page_new).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText_new").text("没有更多了");
                return;
            }
            $scope.newlist = $scope.newlist.concat(res);
        });
        $scope.page_new ++;
    };

    $scope.hotlist  = [];
    $scope.page_hot = 1;
    $scope.loadHot  = function () {
        $http.get(yxspa.api.onlineHotApps + "/page/" + $scope.page_hot).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText_hot").text("没有更多了");
                return;
            }
            $scope.hotlist = $scope.hotlist.concat(res);
        });
        $scope.page_hot ++;
    };

    $scope.loadNew();
    $scope.loadHot();

    $scope.loadMoreHot = function () {
        $scope.loadHot();
    };
    $scope.loadMoreNew = function () {
        $scope.loadNew();
    };

    $scope.showH5Game = yxspa.funcs.showH5Game;

    $scope.show = function (s) {
        $scope.tab = s;
        $("#new_tab").removeClass("AppRank_tab_active");
        $("#hot_tab").removeClass("AppRank_tab_active");
        if (s == 1) {
            $("#new_tab").addClass("AppRank_tab_active");
        } else if (s == 2) {
            $("#hot_tab").addClass("AppRank_tab_active");
        }
    };
});

yxapp.controller("moduleRecruitlistController", function ($scope, $http) {
    var url = yxspa.api_prefix + "?action=get_recruit_data";
    $http.get(url).success(function (res) {
        $scope.list = res;
    });
});
yxapp.directive('modulerecruitlist', function () {
    return {
        templateUrl: 'modules/recruit.html',
        replace    : true

    };
});

yxapp.controller("moduleGiftCodesController", function ($scope, $http) {
    $scope.page = 1;
    $scope.list = [];
    $scope.load = function () {
        $http.get(yxspa.api.gift + "/page/" + $scope.page).success(function (res) {
            if (res.length >= 1) {
                $scope.list = $scope.list.concat(res);
                $scope.page ++;
            } else {
                $("#loadMoreText").text("没有更多了");
            }

        });
    };
    $scope.load();
//    $scope.list1=[
//        {"id":100,"name":"传奇世界圣诞礼包","icon":"./static/img/icon/1.png","remain":"100"},
//        {"id":101,"name":"联盟与部落双11礼包","icon":"./static/img/icon/2.jpg","remain":"90"},
//        {"id":102,"name":"决战沙城双12礼包","icon":"./static/img/icon/3.png","remain":"80"},
//        {"id":100,"name":"盛世霸业国庆礼包","icon":"./static/img/icon/4.png","remain":"20"},
//        {"id":10,"name":"传奇世界","icon":"./static/img/icon/1.png","remain":"100"},
//        {"id":1000,"name":"联盟与部落","icon":"./static/img/icon/2.jpg","remain":"90"},
//        {"id":100,"name":"决战沙城","icon":"./static/img/icon/3.png","remain":"80"},
//        {"id":100,"name":"盛世霸业","icon":"./static/img/icon/4.png","remain":"20"},
//        {"id":100,"name":"决战沙城","icon":"./static/img/icon/3.png","remain":"80"},
//        {"id":100,"name":"盛世霸业","icon":"./static/img/icon/4.png","remain":"20"},
//    ];

    $scope.loadMore = function () {
        $scope.load();
//            var items=[
//                {"id":100,"name":"决战沙城","icon":"./static/img/icon/3.png","remain":"80"},
//                {"id":100,"name":"盛世霸业","icon":"./static/img/icon/4.png","remain":"20"},
//                {"id":100,"name":"决战沙城","icon":"./static/img/icon/3.png","remain":"80"},
//                {"id":100,"name":"盛世霸业","icon":"./static/img/icon/4.png","remain":"20"},
//            ];
//            $scope.list=$scope.list.concat(items);
        //console.log($scope.list);
        //$("#loadMoreText").text("没有更多了");
    };

    $scope.goto = function (id) {
        location.href = '#/giftDetail/' + id;
    };
});
yxapp.directive('moduleGiftCodes', function () {
    return {
        templateUrl: 'modules/GiftCodes.html',
        replace    : true
    };
});

yxapp.controller("moduleNaviTabsController", function ($scope, $route) {
    $scope.list      = [

        {"icon": "./static/img/asset/new_game2.png", "name": "新游", "url": "#/rank"},
        {"icon": "./static/img/asset/cate.png", "name": "分类", "url": "#/cate"},
        {"icon": "./static/img/asset/gift.png", "name": "礼包", "url": "#/gift"},
//        {"icon":"./static/img/asset/onlinegame.png","name":"资讯","url":"#/news"},
        {"icon": "./static/img/asset/server.png", "name": "开服", "url": "#/server"},
    ];
    $scope.goto      = function (url) {
//        console.log(url);
        location.href = url;
    };
    $scope.keyword   = current_search_keyword;
    $scope.go_search = function () {
        current_search_keyword = $scope.keyword;
        location.href          = "#/search/";

    };
});
yxapp.directive('moduleNaviTabs', function () {
    return {
        templateUrl: 'modules/NaviTabs.html',
        replace    : true
    };
});

yxapp.controller("moduleHeaderSearchController", function ($scope, $route, $http) {
    $scope.data           = {"key": current_search_keyword};
    $scope.show_load_more = 0;
    $scope.list           = [];

    $scope.page = 1;

    $scope.load = function () {
        $http.get(yxspa.api.search + "/keyword/" + $scope.data.key + "/page/" + $scope.page).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText").text("没有更多了");
                return;
            }
            $scope.list = $scope.list.concat(res);
            $scope.page ++;
        });
    };

    $scope.search = function () {
        $scope.show_load_more = 1;
        $scope.page           = 1;
        $scope.list           = [];
        $("#loadMoreText").text("加载更多");
        $scope.load();

//        $scope.list1=[
//            {"name":"传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"联盟与部落","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业","tag":"精品","icon":"./static/img/icon/4.png"},
//            {"name":"联盟与部落","tag":"推荐","icon":"./static/img/icon/2.jpg"},
//            {"name":"决战沙城","tag":"热门","icon":"./static/img/icon/3.png"},
//            {"name":"盛世霸业","tag":"精品","icon":"./static/img/icon/4.png"},
//        ];

    };

    $scope.loadMore = function () {
        $scope.load();
//        var items=[
//            {"name":"1传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"2传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"4传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//            {"name":"5传奇世界","tag":"精品","icon":"./static/img/icon/1.png"},
//        ];
//        $scope.list=$scope.list.concat(items);
        //console.log($scope.list);
        //$("#loadMoreText").text("没有更多了");
    };

    $scope.showH5Game = yxspa.funcs.showH5Game;
    $scope.search();
});
yxapp.directive('moduleHeaderSearch', function () {
    return {
        templateUrl: 'modules/HeaderSearch.html',
        replace    : true

    };
});

yxapp.controller("moduleCateDetailHeaderBackController", function ($scope, $route) {
    var id = $route.current.params.id;
    if (yxspa.values.current_cate_name === '') {
        yxspa.values.current_cate_name = "分类标题";
    }
    $scope.title = yxspa.values.current_cate_name;
});
yxapp.controller("moduleCateDetailController", function ($scope, $route, $http) {
    var id = $route.current.params.id;
//    console.log("cate detail apps "+id);

    $scope.tab = 1;

    $scope.newlist  = [];
    $scope.page_new = 1;
    $scope.loadNew  = function () {
        $http.get(yxspa.api.cateNewApps + "/cateid/" + id + "/page/" + $scope.page_new).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText_new").text("没有更多了");
                return;
            }
            $scope.newlist = $scope.newlist.concat(res);
        });
        $scope.page_new ++;
    };

    $scope.hotlist  = [];
    $scope.page_hot = 1;
    $scope.loadHot  = function () {
        $http.get(yxspa.api.cateHotApps + "/cateid/" + id + "/page/" + $scope.page_hot).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText_hot").text("没有更多了");
                return;
            }
            $scope.hotlist = $scope.hotlist.concat(res);
        });
        $scope.page_hot ++;
    };

    $scope.loadNew();
    $scope.loadHot();

    $scope.loadMoreHot = function () {
        $scope.loadHot();
    };
    $scope.loadMoreNew = function () {
        $scope.loadNew();
    };

    $scope.showH5Game = yxspa.funcs.showH5Game;

    $scope.show = function (s) {
        $scope.tab = s;
        $("#new_tab").removeClass("AppRank_tab_active");
        $("#hot_tab").removeClass("AppRank_tab_active");
        if (s == 1) {
            $("#new_tab").addClass("AppRank_tab_active");
        } else if (s == 2) {
            $("#hot_tab").addClass("AppRank_tab_active");
        }
    };
});

yxapp.controller("moduleHeaderBackController", function ($scope, $route) {

});
yxapp.directive('moduleHeaderBack', function () {
    return {
        templateUrl: 'modules/HeaderBack.html',
        replace    : true

    };
});
yxapp.controller("moduleKeyRecommendAppController", function ($scope, $http) {
    if (yxspa.dev_mode) {
        $scope.list = [
            {"id": "1", "name": "传奇世界", "cates": "精品", "icon": "./static/img/icon/1.png", "clicknum": "123412"},
            {"id": "1", "name": "联盟与部落", "cates": "推荐", "icon": "./static/img/icon/2.jpg", "clicknum": "123412"},
            {"id": "1", "name": "决战沙城", "cates": "热门", "icon": "./static/img/icon/3.png", "clicknum": "123412"},
            {"id": "1", "name": "盛世霸业", "cates": "精品", "icon": "./static/img/icon/4.png", "clicknum": "123412"},
        ];
    } else {
        $scope.image_prefix = yxspa.image_prefix;

        $scope.load = function () {
            $http.get(yxspa.api.getKeyApps).success(function (res) {
                $scope.list = res;
            });
        };
        $scope.load();
    }

    $scope.showH5Game = yxspa.funcs.showH5Game;
});
yxapp.directive('moduleKeyRecommendApp', function () {
    return {
        templateUrl: 'modules/KeyRecommendApp.html',
        replace    : true
    };
});

yxapp.controller("moduleNewRecommendAppController", function ($scope, $http) {
    $scope.image_prefix = yxspa.image_prefix;
    if (yxspa.dev_mode) {
        $scope.list = [
            {
                "id"      : "1",
                "name"    : "传奇世界",
                "cates"   : "精品",
                "icon"    : "./static/img/icon/1.png",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                "size"    : "100MB"
            },
            {
                "id"      : "1",
                "name"    : "联盟与部落",
                "cates"   : "推荐",
                "icon"    : "./static/img/icon/2.jpg",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                "size"    : "100MB"
            },
            {
                "id"      : "1",
                "name"    : "决战沙城",
                "cates"   : "热门",
                "icon"    : "./static/img/icon/3.png",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                "size"    : "100MB"
            },
            {
                "id"      : "1",
                "name"    : "盛世霸业",
                "cates"   : "精品",
                "icon"    : "./static/img/icon/4.png",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                "size"    : "100MB"
            },
        ];

        $scope.loadMore = function () {
            var items   = [
                {
                    "id"      : "1",
                    "name"    : "1传奇世界",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/1.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "2联盟与部落",
                    "cates"   : "推荐",
                    "icon"    : "./static/img/icon/2.jpg",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "3决战沙城",
                    "cates"   : "热门",
                    "icon"    : "./static/img/icon/3.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "4盛世霸业",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/4.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "5传奇世界",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/1.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "6联盟与部落",
                    "cates"   : "推荐",
                    "icon"    : "./static/img/icon/2.jpg",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "7决战沙城",
                    "cates"   : "热门",
                    "icon"    : "./static/img/icon/3.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
                {
                    "id"      : "1",
                    "name"    : "8盛世霸业",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/4.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}],
                    "size"    : "100MB"
                },
            ];
            $scope.list = $scope.list.concat(items);
            $("#loadMoreText").text("没有更多了");
        };

    } else {
        $scope.page = 1;
        $scope.list = [];

        $scope.goto_more = function () {
            location.href = "#/rank";
        };

        $scope.load = function () {
            $http.get(yxspa.api.getNewApps + "/page/" + $scope.page).success(function (res) {
                if (res.length === 0) {
                    $("#loadMoreText").text("没有更多了");
                } else {
                    $scope.list = $scope.list.concat(res);
                    $scope.page ++;
                }
            });
        };

        $scope.loadMore = function () {
            $scope.load();
        };
        $scope.load();
    }

    $scope.showH5Game = yxspa.funcs.showH5Game;
});
yxapp.directive('moduleNewRecommendApp', function () {
    return {
        templateUrl: 'modules/NewRecommendApp.html',
        replace    : true

    };
});

yxapp.controller("moduleHotAppController", function ($scope, $http) {
    $scope.image_prefix = yxspa.image_prefix;
    $scope.page         = 1;
    $scope.list         = [];
    $scope.goto_more    = function () {
        location.href = "#/rank";
    };

    $scope.load = function () {
        $http.get(yxspa.api.getHotApps + "/page/" + $scope.page).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText").text("没有更多了");
            } else {
                $scope.list = $scope.list.concat(res);
                $scope.page ++;
            }
        });
    };

    $scope.loadMore = function () {
        $scope.load();
    };
    $scope.load();
    $scope.showH5Game = yxspa.funcs.showH5Game;
});

yxapp.controller("moduleGYAppController", function ($scope, $http) {
    $scope.image_prefix = yxspa.image_prefix;
    $scope.page         = 1;
    $scope.list         = [];

    $scope.goto_more = function () {
        location.href = "#/rank";
    };

    $scope.load = function () {
        $http.get(yxspa.api.getGYApps + "/page/" + $scope.page).success(function (res) {
            if (res.length === 0) {
                $("#loadMoreText").text("没有更多了");
            } else {
                $scope.list = $scope.list.concat(res);
                $scope.page ++;
            }
        });
    };

    $scope.loadMore = function () {
        $scope.load();
    };
    $scope.load();
    $scope.showH5Game = yxspa.funcs.showH5Game;
});

yxapp.controller("modulePartnersController", function ($scope, $route) {
    $scope.list = [
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"},
        {"icon": "./static/img/partners/1.png"}
    ];
});
yxapp.directive('modulepartners', function () {
    return {
        templateUrl: 'modules/partners.html',
        replace    : true

    };
});

yxapp.controller("moduleNewslistController", function ($scope, $http) {
//    $scope.list=[];
//    $.post(yxspa.api_prefix+"?action=get_news_data",{},function(res){
//       $scope.list=res;
//       yxspa.funcs.log(res);
//    });
    var url = yxspa.api_prefix + "?action=get_news_data&page=1";
    $http.get(url).success(function (res) {
        $scope.list = res.list;
    });
});
yxapp.directive('modulenewslist', function () {
    return {
        templateUrl: 'modules/newslist.html',
        replace    : true,
//        scope:{},
//        link : function($scope) {
//            $.post(yxspa.api_prefix+"?action=get_news_data",{},function(res){
//                $scope.list=res;
////                yxspa.funcs.log(res);
//             });
//        }

    };
});

yxapp.directive('moduletabs', function () {
    return {
        restrict  : 'E',
        transclude: true,
        scope     : {},
        controller: ["$scope", function ($scope) {
            var panes = $scope.panes = [];

            $scope.select = function (pane) {
                angular.forEach(panes, function (pane) {
                    pane.selected = false;
                });
                pane.selected = true;
            };

            this.addPane = function (pane) {
                if (panes.length === 0) $scope.select(pane);
                panes.push(pane);
            };
        }],
        template  : '<div class="tabbable">' +
        '<ul class="nav nav-tabs">' +
        '<li ng-repeat="pane in panes" ng-class="{active:pane.selected}">' +
        '<a href="" ng-click="select(pane)">{{pane.title}}</a>' +
        '</li>' +
        '</ul>' +
        '<div class="tab-content" ng-transclude></div>' +
        '</div>',
        replace   : true
    };
});
yxapp.directive('modulepane', function () {
    return {
        require   : '^moduletabs',
        restrict  : 'E',
        transclude: true,
        scope     : {title: '@'},
        link      : function (scope, element, attrs, tabsCtrl) {
            tabsCtrl.addPane(scope);
        },
        template  : '<div class="tab-pane" ng-class="{active: selected}" ng-transclude>' +
        '</div>',
        replace   : true
    };
});

yxapp.controller("moduleTopFuncBarController", function ($scope, $route) {
    $scope.list         = [
        {"name": "我的订单", "link": "#/my_orders"},
        {"name": "地址管理", "link": "#/my_addr"}

    ];
    $scope.login        = true;
    $scope.setHighlight = function ($event) {
//        yxspa.funcs.log($event.target);
        var e = $event.target;
        $(e).siblings("a").removeClass("top_func_bar_active");
        $(e).addClass("top_func_bar_active");
    };
});
yxapp.directive('moduletopfuncbar', function () {
    return {
        templateUrl: 'modules/top_func_bar.html',
        replace    : true

    };
});

//yxapp.directive('ngsButterbar', ['$rootScope', function ($rootScope) {
//        return {
//            link: function (scope, element) { //attrs
//                element.hide();
//                $rootScope.$on('$routeChangeStart', function () {
//                    element.show();
//                    $('div[ng-view]').css('display', 'none');
//                });
//                $rootScope.$on('$routeChangeSuccess', function () {
//                    element.hide();
//                    $('div[ng-view]').css('display', 'none');
//                });
//            }
//        };
//    }]
//);

yxapp.controller("moduleContactTextBlockController", function ($scope, $route, $http) {
    $http.get(yxspa.api.getContactInfo).success(function (res) {
        if (res) {
            $scope.content = res;
        } else {
            $scope.content = '\
QQ客服：123412341234\n\
<br /><br />\n\
玩家交流群1：123412341234\n\
<br /><br />\n\
玩家交流群2：123412341234\n\
<br /><br />\n\
微信公众号：asdfasdff\n\
\n\
';
        }

    });
});
yxapp.controller("moduleAboutTextBlockController", function ($scope, $route, $http) {
    $http.get(yxspa.api.getCompanyAbout).success(function (res) {
        if (res) {
            $scope.content = res;
        } else {
            $scope.content = '暂无';
        }

    });
});
yxapp.controller("moduleTextBlockController", function ($scope, $route) {

});
yxapp.directive('moduleTextBlock', function () {
    return {
        templateUrl: 'modules/TextBlock.html',
        replace    : true

    };
});

yxapp.controller("moduleRegisterController", function ($scope, $route, $location, $http) {
    $scope.login = yxspa.login;
    $scope.data  = {
        "phone"     : "",
        "phone_code": "",
        "password"  : "",
        "agree"     : true,
        "sex"       : "male"
    };

    $scope.sendCode = function () {
        if ($scope.data.phone === '') {
            yxshare.ui.alert("请输入手机号");
            return;
        }

        if (! yxshare.funcs.validatePhone($scope.data.phone)) {
            yxshare.ui.alert("手机号格式不正确");
            return;
        }

//        yxshare.ui.alert("正在发送");

        $.post(yxspa.api.sendPhoneCode, {"mobile": $scope.data.phone}, function (res) {
            if (res.error === '0') {
                $(".send_phonecode_btn").text("已发送");
                yxshare.ui.notice2("发送成功，请尽快输入");
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
            }
        });

    };

    $scope.reg = function (sex) {
        $scope.data.sex = sex;
        console.log($scope.data);

        if (! yxshare.funcs.validatePassword($scope.data.password)) {
            yxshare.ui.alert("密码必须由6到20位字符组成");
            return;
        }

        $.post(yxspa.api.reg, $scope.data, function (res) {
            if (res.error === '0') {
                yxspa.user.mem_id = res.msg;
                // yxshare.ui.alert3("注册成功","#/login");
                yxshare.ui.alert3("注册成功", "#/home");
//                location.replace("#/login");
                return;
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
            }
        });
    };
});
yxapp.directive('moduleRegister', function () {
    return {
        templateUrl: 'modules/Register.html',
        replace    : true
    };
});

yxapp.controller("moduleFindpwdController", function ($scope, $route, $location, $http) {
    $scope.data = {
        "phone"     : "",
        "phone_code": "",
        "password"  : ""
    };

    $scope.sendCode = function () {
        if ($scope.data.phone === '') {
            yxshare.ui.alert("请输入手机号");
            return;
        }

        if (! yxshare.funcs.validatePhone($scope.data.phone)) {
            yxshare.ui.alert("手机号格式不正确");
            return;
        }

        $.post(yxspa.api.sendPhoneCodeToExist, {"mobile": $scope.data.phone}, function (res) {
            if (res.error === '0') {
                $(".send_phonecode_btn").text("已发送");
                yxshare.ui.notice2("发送成功，请尽快输入");
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
            }
        });

    };

    $scope.find = function () {
        console.log($scope.data);

        if (! yxshare.funcs.validatePassword($scope.data.password)) {
            yxshare.ui.alert("密码必须由6到20位字符组成");
            return;
        }

        $.post(yxspa.api.findpwd, $scope.data, function (res) {
            if (res.error === '0') {
                yxspa.user.mem_id = res.msg;
//                location.replace("#/login");
                yxshare.ui.alert3("密码找回成功", "#/login");
                return;
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
            }
        });
    };
});
yxapp.directive('moduleFindpwd', function () {
    return {
        templateUrl: 'modules/Findpwd.html',
        replace    : true
    };
});

yxapp.controller("moduleAppDetailController", function ($scope, $route, $http) {
    var id = $route.current.params.id;
    if (yxspa.dev_mode) {
        $scope.data = {
            "id"          : id,
            "name"        : "传奇世界",
            "icon"        : "./static/img/icon/1.png",
            "description" : "新资料新玩法，“决战之王”花落谁家！游戏融合新技术，演绎出魔幻的蛮荒世界。游戏中的场景优美格调，细腻真实，一草一木都相当秀丽逼真。\n\
提供刀、剑、扇、杖等多种职业装备琳琅满目，华丽且炫酷，技能效果绽放绚丽，暴爽打击感与写实精美的画风的完美结合，让各位有身临其境之感，领略号令天下的代入感。\n\
<br />游戏特色\n\
<br />1.十年征途，千秋霸业经典三职业，独特新玩法，让你欲罢不能\n\
<br />2.一战到底，江湖情义再战沙城精彩活动，丰富副本，畅爽游戏体验\n\
<br />3.热血攻城，胜者为尊傲视群雄独特系统，实时团战，沙城霸主舍我其谁\n\
<br />4.帮派激战，真实PK刀刀烈火实时多人PK，再战经典万人攻沙，帮你成就真男人团战梦想！\n\
<br />5.怒刷BOSS，极品装备超高爆率酷炫技能、霸气称号，让你战力再度飙升！",
            "bref_intro"  : "首款男人最爱玩的能赚RMB，可以自由交易，超高爆率，刀刀砍怪爽翻天的传奇游戏《热血世界》带你进入新的传奇世界。",
            "download_url": "#/rank",
            "clicknum"    : "1234",
            "shots"       : [
                {"image": "./static/img/app_shots/shot (1).jpg"},
                {"image": "./static/img/app_shots/shot (2).jpg"},
                {"image": "./static/img/app_shots/shot (3).jpg"},
                {"image": "./static/img/app_shots/shot (4).jpg"},
            ]
        };

        $scope.data.gift    = {
            "id"        : id,
            "name"      : "传奇世界圣诞礼包",
            "icon"      : "./static/img/icon/1.png",
            "remain"    : 90,
            "start_time": "2016-12-27 23:05:15",
            "end_time"  : "2016-12-27 23:05:18",
            "content"   : "\
                    加速卡x5,加速卡x5,<br />仙品元婴丹x10,仙品元婴丹x10,<br />仙品元婴丹x10,<br />\n\
                    捉妖卡x20\n\
                    <br /><br />\n\
                    使用说明：\n\
                    点击设置，兑换激活码",
            "warning"   : "请尽快使用",
        };
        var full_desc       = $scope.data.description;
//        var short_desc=$scope.data.description.substr(0,100)+" <span style='color:#333;'>【点击查看更多】</span>";
        var short_desc      = $scope.data.description.substr(0, 100);
        var is_short        = false;
        $scope.arrow_up     = false;
        $scope.toggle_intro = function () {
            if (is_short) {
                $scope.data.description = full_desc;
            } else {
                $scope.data.description = short_desc;
            }
            is_short        = ! is_short;
            $scope.arrow_up = ! $scope.arrow_up;
        };
        $scope.toggle_intro();

        console.log($scope.data);
    } else {
        var full_desc   = {};
        var short_desc  = {};
        var is_short    = false;
        $scope.arrow_up = false;

        $.get(yxspa.api.getAppDetailInfo + "/id/" + id).then(function (res) {
//            console.log(JSON.stringify(res));
            console.log(res);
            $scope.data = res.game_info;
            $scope.gift = res.gift_info;
            $scope.news = res.news_info;
//            $scope.data.gift={
//                "id":id,
//                "name":"传奇世界圣诞礼包",
//                "icon":"./static/img/icon/1.png",
//                "remain":90,
//                "start_time":"2016-12-27 23:05:15",
//                "end_time":"2016-12-27 23:05:18",
//                "content":"\
//                        加速卡x5,加速卡x5,<br />仙品元婴丹x10,仙品元婴丹x10,<br />仙品元婴丹x10,<br />\n\
//                        捉妖卡x20\n\
//                        <br /><br />\n\
//                        使用说明：\n\
//                        点击设置，兑换激活码",
//                "warning":"请尽快使用",
//            };

            full_desc  = $scope.data.description;
            short_desc = $scope.data.description.substr(0, 100);
            $scope.toggle_intro();
            $scope.$apply();
        });

        $scope.showDetail = function (id) {
            location.href = "#/news_detail/" + id;
        };

        $scope.toggle_intro = function () {
            if (is_short) {
                $scope.data.description = full_desc;
            } else {
                $scope.data.description = short_desc;
            }
            is_short        = ! is_short;
            $scope.arrow_up = ! $scope.arrow_up;
        };
        //

        $scope.goto = function (id) {
            location.href = '#/giftDetail/' + id;
        };

        $scope.charge = function (id) {

            $http.get(yxspa.api.loginState).success(function (res) {
                if (res.state === "true") {
                    yxspa.login     = true;
                    yxspa.data.user = res.user;
                    location.href   = '#/charge/' + id;
                    $.cookie("current_charge_app_name", $scope.data.name);
                    $.cookie("current_charge_user_name", res.user.username);

                } else if (res.state === "false") {
                    yxspa.login = false;
                }

                $scope.data  = yxspa.data.user;
                $scope.login = yxspa.login;
                if (! $scope.login) {
                    location.replace("#/login");
                    return;
                }
            });

        };

        $scope.images_full_show = false;
        $scope.images_full      = function () {
            $scope.images_full_show = ! $scope.images_full_show;
        };

        console.log(JSON.stringify($scope.data));
    }
});
yxapp.directive('moduleAppDetail', function () {
    return {
        templateUrl: 'modules/AppDetail.html',
        replace    : true
    };
});

yxapp.controller("moduleServerListController", function ($scope, $route) {
    $scope.tab  = 1;
    $scope.show = function (s) {
        $scope.tab = s;

        $("#new_tab").removeClass("AppRank_tab_active");
        $("#hot_tab").removeClass("AppRank_tab_active");
        if (s == 1) {
            $("#new_tab").addClass("AppRank_tab_active");
        } else if (s == 2) {
            $("#hot_tab").addClass("AppRank_tab_active");
        }

    };
    $scope.load = function () {
        $.post(yxspa.api.getServerList, {}, function (res) {
            if (res.error === '0') {
                $scope.open = res.msg[0];
                $scope.test = res.msg[1];
                $scope.$apply();
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
            }
        });
    };

    $scope.loadMore = function () {
        $scope.load();
    };

    $scope.load();

});
yxapp.directive('moduleServerList', function () {
    return {
        templateUrl: 'modules/ServerList.html',
        replace    : true

    };
});

yxapp.controller("moduleBottomTabsController", function ($scope, $route) {
    $scope.data = [
        {"name": "首页", "icon": "./static/img/bottom_icon/home.png"},
        {"name": "游戏", "icon": "./static/img/bottom_icon/game_gray.png"},
        {"name": "礼包", "icon": "./static/img/bottom_icon/gift_gray.png"},
        {"name": "个人中心", "icon": "./static/img/bottom_icon/user_gray.png"},
    ];

    var dataall    = [
        {"name": "首页", "icon": "./static/img/bottom_icon/home_gray.png"},
        {"name": "游戏", "icon": "./static/img/bottom_icon/game_gray.png"},
        {"name": "礼包", "icon": "./static/img/bottom_icon/gift_gray.png"},
        {"name": "个人中心", "icon": "./static/img/bottom_icon/user_gray.png"},
    ];
    var list       = ["home", "game", "gift", "user"];
    var url        = ["home", "rank", "gift", "account"];
    $scope.current = 0;
    $scope.change  = function (i) {

        $scope.current  = i;
        var datanow     = deepCopy(dataall);
        datanow[i].icon = "./static/img/bottom_icon/" + list[i] + ".png";
        $scope.data     = datanow;
        location.href   = "#/" + url[i];
    };

    var deepCopy = function (source) {
        var result;
        (source instanceof Array) ? (result = []) : (result = {});

        for (var key in source) {
            result[key] = (typeof source[key] === 'object') ? deepCopy(source[key]) : source[key];
        }
        return result;
    }
});
yxapp.directive('moduleBottomTabs', function () {
    return {
        templateUrl: 'modules/BottomTabs.html',
        replace    : true

    };
});

yxapp.controller("moduleTodayNewsController", function ($scope, $route) {
    $scope.list = [];

    var page          = 1;
    $scope.load       = function () {
        $.get(yxspa.api.getIosAppPosts + "?page=" + page).then(function (res) {
            if (res.length > 0) {
                var top     = res.slice(0, 1);
                $scope.top  = top[0];
                $scope.list = $scope.list.concat(res.slice(1, 5));
                $scope.$apply();
                page ++;
                console.log($scope.list);
            } else {
                $("#LoadMoreText").text("没有更多了");
            }
        });
    };
    $scope.showDetail = function (id) {
        location.href = "#/news_detail/" + id;
    };
    $scope.load();
});

yxapp.directive('moduleTodayNews', function () {
    return {
        templateUrl: 'modules/TodayNews.html',
        replace    : true

    };
});

/**
 *
 * 广告图模块
 * 严旭
 * 2017-02-07 16:27:28
 *
 */
yxapp.controller("moduleImageBlock1Controller", function ($scope, $route, $http) {
    $http.get(yxspa.api.getAbs + "/type/1").success(function (res) {
        console.log(res);
        $scope.img = res.pic_url;
        $scope.target_id = res.target_id;
    });
    $scope.goto2 = function (appid) {
        location.href = "#/appDetail/" + appid;
    };
    // $scope.img = "./static/img/ads/1.jpg";
});
yxapp.controller("moduleImageBlock2Controller", function ($scope, $route, $http) {
    $http.get(yxspa.api.getAbs + "/type/2").success(function (res) {
        $scope.img = res.pic_url;
        $scope.target_id = res.target_id;
    });
    $scope.goto2 = function (appid) {
        location.href = "#/appDetail/" + appid;
    };
    // $scope.img = "./static/img/ads/2.jpg";
});
yxapp.directive('moduleImageBlock', function () {
    return {
        templateUrl: 'modules/ImageBlock.html',
        replace    : true
    };
});

yxapp.controller("moduleFloatDownloadController", function ($scope, $route, $http) {
    $scope.show         = yxconfig.show_home_download_area;
    $scope.img          = "./static/img/ads/2.jpg";
    $http.get(yxspa.api.getappinfo).success(function (res) {
        $scope.logo = res.logo;
        $scope.download_url = res.packageurl;
    });
    // $scope.download_url = yxconfig.app_down_url;
    $scope.hide         = function () {
        yxconfig.show_home_download_area = false;
        $scope.show                      = false;
    };
});
yxapp.directive('moduleFloatDownload', function () {
    return {
        templateUrl: 'modules/FloatDownload.html',
        replace    : true
    };
});

yxapp.controller("moduleNewsDetailController", function ($scope, $route) {
    var id = $route.current.params.id;

    $.get(yxspa.api.getIosAppPostDetail + "?id=" + id).then(function (res) {
        $scope.content = res;
        $scope.$apply();
    });

});

yxapp.controller("moduleTodayNewsAllController", function ($scope, $route) {
    $scope.list = [];

    var page    = 1;
    $scope.load = function () {
        $.get(yxspa.api.getIosAppPosts + "?page=" + page).then(function (res) {
            if (res.length > 0) {
                $scope.list = $scope.list.concat(res);
                $scope.$apply();
                page ++;
            } else {
                $("#LoadMoreText").text("没有更多了");
            }
        });
    };

    $scope.showDetail = function (id) {
        location.href = "#/news_detail/" + id;
    };
    $scope.showMore   = function () {
        $scope.load();
    };
    $scope.load();
});
yxapp.directive('moduleTodayNewsAll', function () {
    return {
        templateUrl: 'modules/TodayNewsAll.html',
        replace    : true
    };
});

yxapp.controller("moduleTestController", function ($scope, $route) {

});
yxapp.directive('moduleTest', function () {
    return {
        templateUrl: 'modules/Test.html',
        replace    : true
    };
});

yxapp.controller("moduleAppDownloadController", function ($scope, $route) {

});
yxapp.directive('moduleAppDownload', function () {
    return {
        templateUrl: 'modules/AppDownload.html',
        replace    : true,
        link       : function () {
            var h = $(window).height() - 100;
            $(".module_AppDownload").css("height", h + "px");
        }
    };
});

yxapp.controller("moduleAgreementController", function ($scope, $route) {
    $scope.content = "\n\
    <h4 style='width:100%;text-align:center;margin-bottom:20px;'>用户服务协议</h4>\n\
    <p>当您申请用户时，表示您已经同意并遵守本站规章。</p>\n\
    <p>欢迎您加入平台参与交流和讨论，本站点为公共交流平台，为维护网上公共秩序和社会稳定，请您自觉遵守以条款：</p>\n\
    <p>一、不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用本站制作、复制和传播下列信息：</p>\n\
    <p >（一）煽动抗拒、破坏宪法和法律、行政法规实施的；</p>\n\
    <p >（二）煽动颠覆国家政权，推翻社会主义制度的；</p>\n\
    <p >（三）煽动分裂国家、破坏国家统一的；</p>\n\
    <p >（四）煽动民族仇恨、民族歧视，破坏民族团结的；</p>\n\
    <p>（五）捏造或者歪曲事实，散布谣言，扰乱社会秩序的；</p>\n\
    <p>（六）宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；</p>\n\
    <p>（七）公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；</p>\n\
    <p>（八）损害国家机关信誉的；</p>\n\
    <p>（九）其他违反宪法和法律行政法规的；</p>\n\
    <p>（十）进行商业广告行为的。</p>\n\
    <p>二、互相尊重，对自己的言论和行为负责。</p>\n\
    <p>三、禁止在申请用户时使用相关本站的词汇，或是带有侮辱、毁谤、造谣类的或是有其含义的各种语言进行注册用户，否则我们会将其删除。</p>\n\
    <p>四、禁止以任何方式对本站进行各种破坏行为。</p>\n\
    <p>五、如果您有违反国家相关法律法规的行为，本站概不负责，您的登录论坛信息均被记录无疑，必要时，我们会向相关的国家管理部门提供此类信息。</p>\n\
    <p>六、用户上传资源作为网上学习交流使用，视为资源版权归原创作者和网站共同所有，如用户上传非原创资源，应自行解决相关版权问题，否则，由此引起版权纠纷与本网站无关，网站不负任何法律责任。</p>\n\
    <p>七、网站与论坛文章仅代表作者本人观点，与本站立场无关。如有转载的文章请注明出处或原作者，否则引起的版权纠纷与本网站无关。</p>\n\
    <p>八、凡以任何方式访问本站或直接、间接使用网站信息者，视为自愿接受网站免责声明的约束。</p>\n\
    <p>九、用户注册账号后如果长期不使用，有权收回帐号，以免造成资源浪费，由此带来的包括并不限于用户资料、邮件和游戏虚拟道具丢失等损失由用户自行承担。</p>\n\
    <p>十、本免责条款以及其修改权、更新权及最终解释权均属小游戏所有。</p>\n\
";
});

yxapp.controller("moduleRegNormalController", function ($scope, $route) {
    $scope.login    = yxspa.login;
    var w           = $(window).width() - 60;
    var captcha_url = yxspa.api.getCaptchaImg + "?width=" + w + "&height=50";
    $scope.data     = {
        "phone"     : "",
        "phone_code": "",
        "captcha"   : captcha_url,
        "password"  : "",
        "agree"     : true,
        "sex"       : "male"
    };

    $scope.re_captcha = function () {
        var url = captcha_url;
        $(".captcha_img_wrapper img").attr("src", url);
        //$scope.$apply();
    };

    $scope.sendCode = function () {
        if ($scope.data.phone === '') {
            yxshare.ui.alert("请输入用户名");
            return;
        }

        if (! yxshare.funcs.validatePhone($scope.data.phone)) {
            yxshare.ui.alert("用户名格式不正确");
            return;
        }

//        yxshare.ui.alert("正在发送");

        $.post(yxspa.api.sendPhoneCode, {"mobile": $scope.data.phone}, function (res) {
            if (res.error === '0') {
                $(".send_phonecode_btn").text("已发送");
                yxshare.ui.notice2("发送成功，请尽快输入");
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
            }
        });

    };

    $scope.reg = function (sex) {
        $scope.data.sex = sex;
        console.log($scope.data);

        if (! yxshare.funcs.validateUsername($scope.data.phone)) {
            yxshare.ui.alert("用户名必须由字母或数字组成 <br />长度在6到30位<br />以字母开头");
            return;
        }

        if (! yxshare.funcs.validatePassword($scope.data.password)) {
            yxshare.ui.alert("密码必须由6到20位字符组成");
            return;
        }

        $.post(yxspa.api.regNormal, $scope.data, function (res) {
            if (res.error === '0') {
                yxspa.user.mem_id = res.msg;
                yxshare.ui.alert3("注册成功", "#/home");
//                location.replace("#/login");
                return;
            } else if (res.error === '1') {
                yxshare.ui.alert(res.msg);
                $scope.re_captcha();
            }
        });
    };
});
yxapp.directive('moduleRegNormal', function () {
    return {
        templateUrl: 'modules/RegNormal.html',
        replace    : true
    };
});

yxapp.controller("moduleChargeCenterController", function ($scope, $route, $route) {
    /*var assign_data={
     "username":"yanxu1",
     "productname":"平台币",
     "amount":"29"
     };*/

    var id = $route.current.params.id;

    $scope.current_paynum_index = 0;
    $scope.paynum_list          = [10, 20, 50, 100, 200, 500];
    $scope.change_num           = function (i) {
        $scope.current_paynum_index = i;
        $scope.data.amount          = $scope.paynum_list[i];
    };

    $scope.current_payway_index     = - 1;
    $scope.current_payway_shortname = "";

    $scope.data    = {
        "product" : $.cookie("current_charge_app_name"),
        //"username":	assign_data.username,
        "username": $.cookie("current_charge_user_name"),
        "amount"  : $scope.paynum_list[0]
    };
    $scope.payways = [
        {"shortname": "alipay", "name": "支付宝", "image": "./static/img/payway/alipay.png"},
        {"shortname": "wxpay", "name": "微信", "image": "./static/img/payway/wxpay.png"},
//        {"shortname":"unpay","name":"银联","image":"./static/img/payway/unionpay2.png"},
//        {"shortname":"ptbpay","name":"平台币","image":"./static/img/payway/ptbpay.png"},
    ];

    $scope.setPayway = function ($event, name) {
        $scope.current_payway_shortname = name;
        var e                           = $event.target;

        var i = 0;
        if (name === "alipay") {
            i = 0;
        } else if (name === "wxpay") {
            i = 1;
        } else if (name === "unpay") {
            i = 2;
        } else if (name === "ptbpay") {
            i = 3;
        }
        $scope.current_payway_index = i;
        $(".payway_item .row").removeClass("payway_item_active");
        $(".payway_item").eq(i).children(".row").addClass("payway_item_active");
    };
    $scope.doPay     = function () {
        if ($scope.current_payway_index === - 1) {
            yxshare.ui.alert("请选择支付方式");
            return;
        }

        var type = 0;
        if ($scope.current_payway_shortname == "alipay") {
            type = 2;
        } else if ($scope.current_payway_shortname == "wxpay") {
            type = 9;
        } else if ($scope.current_payway_shortname == "unpay") {
//			type=9;
            yxshare.ui.alert("银联暂未开放，敬请期待");
            return;
        } else if ($scope.current_payway_shortname == "ptbpay") {
            type = 1;
        }
        $("#type").val(type);
        $("#frmPay").attr('action', yxspa.pay_url);
        $("#username").val($scope.data.username);
        $("#amount").val($scope.data.amount);
        $("#gamename").val($scope.data.product);
        $("#frmPay").submit();
        //yxshare.ui.alert("将吊起"+$scope.current_payway_shortname+"支付"+$scope.data.amount+"元");
    };
});
yxapp.directive('moduleChargeCenter', function () {
    return {
        templateUrl: 'modules/ChargeCenter.html',
        replace    : true
    };
});