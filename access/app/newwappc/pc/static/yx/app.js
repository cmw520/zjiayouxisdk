/**
 *
 * 主线pc应用
 * 严旭
 * 2017-02-07 10:21:15
 */


var yxspa             = {};
yxspa.page_controller = {};
yxspa.api_prefix      = "http://h5i.6533.com/";
yxspa.image_prefix    = "http://h5i.6533.com/upload/logo/";
yxspa.login           = false;
yxspa.user            = {
    "mem_id"    : "",
    "session_id": ""
};
yxspa.api             = {};
var yxconfig          = {
    "app_name": "主线WAP"
};

yxspa.api.getSlides            = yxspa.api_prefix + "AppApi/getSlides";
yxspa.api.getNewApps           = yxspa.api_prefix + "AppApi/newApps";
yxspa.api.getKeyApps           = yxspa.api_prefix + "AppApi/keyApps";
yxspa.api.login                = yxspa.api_prefix + "AppApi/checkLogin";
yxspa.api.cates                = yxspa.api_prefix + "AppApi/cates";
yxspa.api.rankNewApps          = yxspa.api_prefix + "AppApi/rankNewApps";
yxspa.api.rankHotApps          = yxspa.api_prefix + "AppApi/rankHotApps";
yxspa.api.onlineNewApps        = yxspa.api_prefix + "AppApi/onlineNewApps";
yxspa.api.onlineHotApps        = yxspa.api_prefix + "AppApi/onlineHotApps";
yxspa.api.cateNewApps          = yxspa.api_prefix + "AppApi/cateNewApps";
yxspa.api.cateHotApps          = yxspa.api_prefix + "AppApi/cateHotApps";
yxspa.api.search               = yxspa.api_prefix + "AppApi/search";
yxspa.api.reg                  = yxspa.api_prefix + "AppApi/reg";
yxspa.api.sendPhoneCode        = yxspa.api_prefix + "AppApi/sendPhoneCode";
yxspa.api.findpwd              = yxspa.api_prefix + "AppApi/findpwd";
yxspa.api.sendPhoneCodeToExist = yxspa.api_prefix + "AppApi/sendPhoneCodeToExist";
yxspa.api.gift                 = yxspa.api_prefix + "AppApi/gift";
yxspa.api.giftDetail           = yxspa.api_prefix + "AppApi/giftDetail";
yxspa.api.getGiftCode          = yxspa.api_prefix + "AppApi/getGiftCode";
yxspa.api.myGiftCodes          = yxspa.api_prefix + "AppApi/myGiftCodes";
yxspa.api.getCompanyAbout      = yxspa.api_prefix + "AppApi/getCompanyAbout";
yxspa.api.getContactInfo       = yxspa.api_prefix + "AppApi/getContactInfo";
yxspa.api.loginThird           = yxspa.api_prefix + "AppApi/loginThird";
yxspa.api.loginState           = yxspa.api_prefix + "AppApi/loginState";
yxspa.api.logout               = yxspa.api_prefix + "AppApi/logout";

yxspa.dev_mode = true;

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
    }).when('/findpwd', {
        templateUrl: 'pages/findpwd.html',
    }).when('/cardbox', {
        templateUrl: 'pages/cardbox.html',
    }).when('/server', {
        templateUrl: 'pages/server.html',
    }).when('/cs', {
        templateUrl: 'pages/cs.html',
    }).when('/about', {
        templateUrl: 'pages/about.html',
    }).when('/play', {
        templateUrl: 'pages/play.html',
    }).when('/appDetail/:id', {
        templateUrl: 'pages/appDetail.html',
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
                yxshare.ui.alert(res.msg);
            }
        });
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
    $scope.list = [
        {"icon": "./static/img/asset/gift.png", "name": "礼包", "url": "#/gift"},
        {"icon": "./static/img/asset/rank.png", "name": "排行", "url": "#/rank"},
        {"icon": "./static/img/asset/cate.png", "name": "分类", "url": "#/cate"},
//        {"icon":"./static/img/asset/onlinegame.png","name":"资讯","url":"#/news"},
        {"icon": "./static/img/asset/server.png", "name": "开服", "url": "#/server"},
    ];
    $scope.goto = function (url) {
//        console.log(url);
        location.href = url;
    };
});
yxapp.directive('moduleNaviTabs', function () {
    return {
        templateUrl: 'modules/NaviTabs.html',
        replace    : true
    };
});

yxapp.controller("moduleHeaderSearchController", function ($scope, $route, $http) {
    $scope.data           = {"key": "三国"};
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
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
            },
            {
                "id"      : "1",
                "name"    : "联盟与部落",
                "cates"   : "推荐",
                "icon"    : "./static/img/icon/2.jpg",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
            },
            {
                "id"      : "1",
                "name"    : "决战沙城",
                "cates"   : "热门",
                "icon"    : "./static/img/icon/3.png",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
            },
            {
                "id"      : "1",
                "name"    : "盛世霸业",
                "cates"   : "精品",
                "icon"    : "./static/img/icon/4.png",
                "clicknum": "123412",
                "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
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
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "2联盟与部落",
                    "cates"   : "推荐",
                    "icon"    : "./static/img/icon/2.jpg",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "3决战沙城",
                    "cates"   : "热门",
                    "icon"    : "./static/img/icon/3.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "4盛世霸业",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/4.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "5传奇世界",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/1.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "6联盟与部落",
                    "cates"   : "推荐",
                    "icon"    : "./static/img/icon/2.jpg",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "7决战沙城",
                    "cates"   : "热门",
                    "icon"    : "./static/img/icon/3.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
                {
                    "id"      : "1",
                    "name"    : "8盛世霸业",
                    "cates"   : "精品",
                    "icon"    : "./static/img/icon/4.png",
                    "clicknum": "123412",
                    "catesArr": [{"name": "标签1"}, {"name": "标签2"}]
                },
            ];
            $scope.list = $scope.list.concat(items);
            $("#loadMoreText").text("没有更多了");
        };

    } else {
        $scope.page = 1;
        $scope.list = [];

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
            $scope.content = '\
自由网络科技有限公司是一家游戏研发公司，1988年4月27日成立至今，\n\
从资本额新台币100万元、员工3人发展起，现已成为上柜公司，资本4.7亿，两岸员工达600余人，集团内7家公司同时掌握两岸三地的产品研发及营销市场。\n\
<br /><br />\n\
\n\
多年来，一直坚持以掌握自有品牌及开发自创产品为主轴，并以玩家的需求、喜好为依归，研发出一套套如轩辕剑、仙剑奇侠传、大富翁、明星志愿、\n\
天使帝国及正宗台湾16张麻将等脍炙人口的系列大作，产品的触角也随著科技时代的进步从PC Game拓展至Console Game、Online Game、Mobile Game及\n\
Casual Game等范围。\n\
\n\
<br /><br />\n\
\n\
面对快速兴起的市场及技术，始终秉持著不断创新、时时用心的精神在经营，为的就是要让玩家能玩到更好玩、更耐玩的游戏。';
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
                yxshare.ui.alert3("注册成功", "#/login");
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
        $scope.data1 = {};

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
            "shots"       : [
                {"image": "./static/img/app_shots/shot (1).jpg"},
                {"image": "./static/img/app_shots/shot (2).jpg"},
                {"image": "./static/img/app_shots/shot (3).jpg"},
                {"image": "./static/img/app_shots/shot (4).jpg"},
            ]
        };

        $scope.data.gift = {
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

        console.log($scope.data);
    } else {

    }
});
yxapp.directive('moduleAppDetail', function () {
    return {
        templateUrl: 'modules/AppDetail.html',
        replace    : true
    };
});

yxapp.controller("moduleServerListController", function ($scope, $route) {
    $scope.today_list   = [
        {"name": "传奇世界", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/1.png"},
        {"name": "联盟与部落", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/2.jpg"},
        {"name": "决战沙城", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/3.png"},
        {"name": "盛世霸业", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/4.png"},
    ];
    $scope.about_list   = [
        {"name": "传奇世界", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/1.png"},
        {"name": "联盟与部落", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/2.jpg"},
        {"name": "决战沙城", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/3.png"},
        {"name": "盛世霸业", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/4.png"},
        {"name": "传奇世界", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/1.png"},
    ];
    $scope.already_list = [
        {"name": "传奇世界", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/1.png"},
        {"name": "联盟与部落", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/2.jpg"},
        {"name": "决战沙城", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/3.png"},
        {"name": "盛世霸业", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/4.png"},
        {"name": "传奇世界", "start_time": "2017-01-13 10:43:34", "icon": "./static/img/icon/1.png"},
    ];
});
yxapp.directive('moduleServerList', function () {
    return {
        templateUrl: 'modules/ServerList.html',
        replace    : true

    };
});