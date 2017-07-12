var yxspa             = {};
yxspa.page_controller = {};
yxspa.api_prefix      = "http://192.168.1.218/projects/kechuang_custom_site/api/index.php";

var yxapp = angular.module('app', ['ngRoute']);
yxapp.config(
    ['$routeProvider',
        function ($routeProvider) {
            $routeProvider.when('/', {
                redirectTo: '/home'
            }).when('/home', {
                templateUrl: 'pages/home.html',
                controller : 'moduleSlidesController'
            }).when('/news', {
                templateUrl: 'pages/news.html',
            }).when('/apps', {
                templateUrl: 'pages/apps.html',
            }).when('/aboutus', {
                templateUrl: 'pages/aboutus.html',
            }).otherwise({
                redirectTo: '/'
            });
        }
    ]
);

yxapp.controller("pageNewsController", function ($scope, $route) {

});

yxapp.filter('to_trusted', ['$sce', function ($sce) {
    return function (text) {
        return $sce.trustAsHtml(text);
    };
}]);

yxapp.controller("moduleNaviController", function ($scope, $route) {
    $scope.list = [
        {"name": "首页", "link": "#/home"},
//        {"name": "全部应用", "link": "#/apps"},
        {"name": "游戏公告", "link": "/agent.php/Agent/news/getlist/type/4"}
//        {"name": "关于我们", "link": "#/aboutus"}
    ];
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

yxapp.controller("moduleAllAppsController", function ($scope, $route) {
    $scope.apps = [
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
    ];
});
yxapp.directive('moduleallapps', function () {
    return {
        templateUrl: 'modules/all_apps.html',
        replace    : true
    };
});

yxapp.controller("moduleLoinFullBackgroundController", function ($scope, $route) {
    $scope.data           = {
        "username": "",
        "password": "",
        "code"    : ""
    };
    $scope.login          = function () {
//        alert("HI");
//        console.log($scope.data);
        var userName  = $scope.data.username;
        var userPass  = $scope.data.password;
        var checkCode = $scope.data.code;

        if (((userName) === "") || ((userPass) === "")) {
            alert("用户名或密码不能为空");
            return;
        }

        if ($.trim(checkCode) === "") {
            alert("验证码不能为空");
            return;
        }

        $.post("/agent.php/Front/account/do_login", {
                "userName": userName,
                "userPass": userPass,
                "checkCode": checkCode
            },
            function (data) {
                if (data.error === '1') {
                    alert(data.msg);
                    $scope.change_captcha();
                    return;
                } else if (data.error === '0') {
                    window.location.href = "/agent.php/Front/account/index";
                }
            });

    };
    $scope.change_captcha = function () {
        var myimg = document.getElementById("verify_img");
        myimg.src = "/agent.php/Front/checkcode/index?length=4&font_size=20&width=250&height=40&use_noise=1&use_curve=0&random="+Math.random();
    };

});
yxapp.directive('moduleloginfullbackground', function () {
    return {
        templateUrl: 'modules/login_full_background.html',
        replace    : true
    };
});

yxapp.controller("moduleAppAndNewsController", function ($scope, $http) {
    $scope.data1 = {
        "rec" : [
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        ],
        "hot" : [
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
            {"icon": "./static/img/icon/1.png", "name": "示例游戏"},
        ],
        "news": [
            {"title": "示例动态1"},
            {"title": "示例动态1"},
            {"title": "示例动态1"},
            {"title": "示例动态1"},
            {"title": "示例动态1"}
        ]
    };

    var url = "/agent.php/front/apiGame/getList";
    $http.get(url).success(function (res) {

        $scope.data = res;
    });
});
yxapp.directive('moduleappandnews', function () {
    return {
        templateUrl: 'modules/app_and_news.html',
        replace    : true
    };
});

yxapp.controller("moduleAdvantageController", function ($scope, $route) {

});
yxapp.directive('moduleadvantage', function () {
    return {
        templateUrl: 'modules/advantage.html',
        replace    : true
    };
});

yxapp.controller("moduleHowtojoinusController", function ($scope, $route) {

});
yxapp.directive('modulehowtojoinus', function () {
    return {
        templateUrl: 'modules/how_to_join_us.html',
        replace    : true
    };
});

yxapp.controller("moduleSlidesController", function ($scope, $route) {
    $scope.list = [
        './static/img/slides/1.png', './static/img/slides/2.jpg'
    ];
    $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
        var swiper = new Swiper('.swiper-container', {
            autoplay           : 3000,
            pagination         : '.swiper-pagination',
            paginationClickable: '.swiper-pagination',
            nextButton         : '.swiper-button-next',
            prevButton         : '.swiper-button-prev',
            spaceBetween       : 30
        });
    });

});
yxapp.directive('moduleslides', function () {
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

yxapp.controller("moduleCompanyhistoryController", function ($scope, $route) {

});
yxapp.directive('modulecompanyhistory', function () {
    return {
        templateUrl: 'modules/companyhistory.html',
        replace    : true

    };
});

yxapp.controller("moduleCompanycultureController", function ($scope, $route) {

});
yxapp.directive('modulecompanyculture', function () {
    return {
        templateUrl: 'modules/companyculture.html',
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
//       console.log(res);
//    });
//    var url=yxspa.api_prefix+"?action=get_news_data&page=1";
//    $http.get(url).success(function(res){
//       $scope.list=res.list;             
//    });
    $scope.list = [
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },
        {"title"     : "示例新闻标题1",
            "time"   : "2016-12-13 17:02:36",
            "content": "测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1测试新闻内容1"
        },

    ];
});
yxapp.directive('modulenewslist', function () {
    return {
        templateUrl: 'modules/newslist.html',
        replace    : true,
//        scope:{},
//        link : function($scope) {
//            $.post(yxspa.api_prefix+"?action=get_news_data",{},function(res){
//                $scope.list=res;             
////                console.log(res);
//             });
//        }

    };
});