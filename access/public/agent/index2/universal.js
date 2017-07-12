// 如果是 ie9 一下的版本，提示不能访问
;~function($){
    if($.browser.msie && $.browser.version <= 8){
        $(function(){
            $("body").html('<p style="padding:20px;">仅支持使用IE9及以上内核的浏览器，换个浏览器试试？</p>');
        });
    }
}($);

// 所有了链接
var API = API || {};
API["SERVER"] = (function(){
    var urls = {
        channelLogin: "/api/channelLogin.php",
        channelSalt: "/api/channelSalt.php",
        channelApps3: "/api/appList3.php",
        paymentDetail:"/api/appTradeDetail.php",
        paidData:"/api/appTradeStatics.php",
        userData:"/api/appData.php",
        newUserData:"/api/channelActiveNewUser.php",
        activeUserData:"/api/channelActiveUser.php",
        retentionData:"/api/retentionData.php",
        arpuData:"/api/arpuData.php",
        payrateData:"/api/payrateData.php",
        // 帐号信息
        currAccInfo: "/api/account/currAccInfo.php",
        // 修改密码
        modifyPwd: "/api/account/modifyPwd.php",	// post形式username和pwd
        // 修改当前直充密码的使用选择
        changeUse: "/api/account/changeUse.php",
        // 子渠道管理:
        childPipeList: "/api/channel/list.php",
        childPipeNew: "/api/channel/add.php",	// name type id[]
        // 渠道的应用列表
        childPipeAppList: "/api/channel/appList.php",
        subChannelAppList: "/api/channel/subChannelInfo.php", // cid=100
        childPipeModify: "/api/channel/update.php",	// cid cname addId[] delId[]
        // 应用打包
        appPackage: "/api/channel/package.php",	// cid appid
        appPackageStateQuery: "/api/channel/package_progress.php",	// { data: [{appid:, cid:}, {appid:, cid:}] }

        getChildChannel: "/api/getChildChannel.php",
        channelUsers: "/api/getChannelUsers.php",

        //发放超级代金券
        grantVoucher: "/api/voucher/grantVoucher.php",
    };

    // 子帐号管理
    urls["account"] = {
        list: '/api/account/list.php',
        add: '/api/account/add.php',	// POST形式username，pwd，remark，ids（数组）
        subAccInfo: '/api/account/subAccInfo.php', // 参数：get形式accId
        update: '/api/account/update.php', // 参数，POST形式accId，pwd，remark，addId（数组），delId（数组）,
        getChildAccount: '/api/account/getChildAccount.php' 
    };

    // 充值
    urls["recharge"] = {
        recharge: '/api/recharge/transMoney.php',	// 参数，POST形式accId, money, pwd
        balance: '/api/recharge/balance.php',	// 当前帐号，有多少果币
        orders: '/api/recharge/orders.php',	// 订单列表
        ordersPaid: '/api/recharge/ordersPaid.php', // 已支付的订单列表，返回与 orders 一致
        channelList: '/api/recharge/channelList.php', // 当前的渠道列表
        directCharge: '/api/recharge/directCharge.php',	// 充值	$.post("/api5/recharge/directCharge.php", {data:{'orderIds'  :["zjzjzjzjzjzjzjzjzjzj", "zjzjzjzjzjzjzjzjzjzj123"]}}, function(data){console.log(data)});
        rechargeRecord: '/api/recharge/rechargeRecord.php',
        rechargeRetract: '/api/recharge/rechargeRetract.php',    //充值记录撤回 add by shaogui
        orderDel: '/api/recharge/orderDel.php',    //订单删除 add by shaogui
        receiveRecord: '/api/recharge/receiveRecord.php',    //当前帐号收币记录
    };

    // 游戏管理
    urls["gameManage"] = {
        notAddedList: '/api/gameManage/notAddedList.php',
        addedAppList: '/api/gameManage/addedAppList.php',
        addToChannelApp: '/api/gameManage/addToChannelApp.php',
        gameIntroduct: '/api/gameManage/gameIntroduct.php',
        delFromChannelApp: '/api/gameManage/delFromChannelApp.php',
    };
    
    // 超级代金券
    urls["voucher"] = {
        //发放超级代金券
        grantVoucher: "/api/voucher/grantVoucher.php",
        voucherStatistics: "/api/voucher/voucherStatistics.php",
        voucherNeedBack: "/api/voucher/voucherNeedBack.php",
    };

    // 消息
    urls["message"] = {
        getMessageList: "/api/message/getMessageList.php",
        messageDetail: "/api/message/messageDetail.php",
    };
    
	// 结算统计
    urls["settlement"] = {
        settleStatistics: "/api/autoSettle/settleStatistics.php",
    };

    
    return urls;
})();

/* get url argument from location or from a given string
 * http://163.com:2344/testpage.php?a=123&b=zzz
 * "http://123.com/?a=sss".toURLParameter("a") == "sss"
 * "http://123.com/?a=sss".toURLParameter().a == "sss"
 * if you want current page URLParameter,use location.toURLParameter
 */
String.prototype.toURLParameter = function (name){
    var match,
    urlParams = {},
    pl     = /\+/g,
    search = /([^&=]+)=?([^&]*)/g,
    decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); };
while (match = search.exec(this)){urlParams[decode(match[1])] = decode(match[2]);}
return typeof(name)==="undefined"?urlParams:urlParams[name];
		}

location.urlParameter=function(name){
	 return location.search.substring(1).toURLParameter(name);
		}
/* repleace all text with given text from a string
 *
 */
String.prototype.replaceAll = function (search, repText){
	return this.split(search).join(repText);
		}
String.prototype.bool=function(){
	return this["0"]+this["1"]+this["2"]+this["3"]==="true";
}
 Array.prototype.last=function(){return this[this.length - 1];}
function localData(key,value){
	var loadData=typeof(value)=="undefined",
		returnData;
	if(loadData){
		//load
		returnData=localStorage.getItem(key);
		if(returnData==null){return returnData;}//return if this key is not storge yet
		returnData=(returnData.substr(0,1)=="{"&&returnData.substr(-1,1)=="}")||(returnData.substr(0,1)=="["&&returnData.substr(-1,1)=="]")?JSON.parse(returnData):returnData;
	}else{
		//save
		returnData=localStorage.setItem(key,typeof(value)=="object"?JSON.stringify(value):value);
	}
	return returnData;
}
function combine(){
	var obj={};
   for(var a= 0;a<arguments.length;a++){
	   for(var b in arguments[a]){
		   obj[b]=arguments[a][b];
	   }
   }
	return obj;
}

// 如果有日期控件，则初始化之
var LANGUAGE = "ZH-CN"; //navigator.language.toUpperCase() || navigator.userLanguage.toUpperCase()|| "ZH-CN";
// 一个操蛋的脚本配置
$(function(){
    if( $ && $.datepicker ){
        $.datepicker.setDefaults($.datepicker.regional[LANGUAGE]);
    }
});

// 公共事件
var gEvent = {
    __: $({}),
    on: function(e, fn){
        this.__.on(e, function(){
            fn.apply(window, [].slice.call(arguments, 1));
        });
    },
    fire: function(){
        this.__.trigger.call(this.__, arguments[0], [].slice.call(arguments, 1));
    },
    _def: {},
    def: function(name){
        if( !this._def[name] ){
            var cb = $.Callbacks("memory");
            function newFn(fn, key){
                return function(code){
                    if(typeof code === "undefined" || code === key){
                        var args = [].slice.call(arguments, 1);
                        fn.apply(this, args);
                    }
                };
            };
            cb.done = function(fn){
                this.add(newFn(fn, true));
                return this;
            };
            cb.fail = function(fn){
                this.add(newFn(fn, false));
                return this;
            };
            cb.always = function(fn){
                this.add(newFn(fn));
                return this;
            };
            cb.resolve = function(){
                this.fire.apply(cb, [true].concat([].slice.call(arguments, 0)));
                return this;
            };
            cb.reject = function(){
                this.fire.apply(cb, [false].concat([].slice.call(arguments, 0)));
                return this;
            };
            this._def[name] = cb;
        }
        return this._def[name];
    }
};

// 公共对象
var gObject = ({
    userMsgDef: gEvent.def("userMsgDef"),   // 用户信息存储　def 对象
    init: function(){
        // 进行初始化操作

        // 获取用户信息
        var userMsgDef = this.userMsgDef;
        $.get(API.SERVER.currAccInfo).done(function(data){
            if(data && data.code == 200 && data.data){
                // 初始化用户权限
                var limits = data.data.rights || null;
                if( limits ){
                    var map = gObject.userPower, keyMap = gObject._indexToUserPower
                    for(var i = 0, max = limits.length; i < max; i++){
                        var key = keyMap[limits[i]];
                        map[key] = true;
                    }
                }

                // 后端返回的角色列表是：[{roleId, roleName}, {roleId, roleName}]
                // 不过前端好像用不到，纠结着要不要进行转换呢？
                // 算了，不转！！！！

                // 用户信息加载完毕
                userMsgDef.resolve(data.data.accInfo || {}, data);
            }else{
                userMsgDef.reject(data);
            }
        }).fail(function(){
            userMsgDef.reject();
        });

        return this;
    },
    // 用户权限
    userPower: {
        // 在请求完成之后，初始化
    },
    _indexToUserPower: {
        // 权限对应列表
        // 后缀: M管理, D数据, RC充值数, UC用户数, C数量, P百分率, MN 金额
        // 前缀: DPD:明细-渠道概况, DRD:明细-交易明细, DUD:明细-用户数据, DND: 明细-新增明细, DAD: 明细-活跃明细
        "1": "myapp",   // 我的应用
        "2": "pipeM",   // 子渠道管理
        "3": "accountM",// 子帐号管理
        "4": "rechargeM", // 游戏直充
        "5": "baseD", // 基础信息
        "6": "tradeD", // 交易数据
        "7": "rechargeMN", // 直充金额
        "8": "nrechargeMN", // 非直充金额
        "9": "arpuD", // ARPU值
        "10": "tradeUC", // 交易用户数
        "11": "tradeC", // 交易数
        "12": "nextDateRetentionP", // 次日留存率
        "13": "newUser", // 新增用户
        "14": "activeUser", // 活跃用户
        "15": "createDownloadLink", // 生成下载链接
        "16": "checkDownloadLink", // 查看下载链接
        "17": "DPD-newUserD", // 明细-渠道概况-新增用户
        "18": "DPD-activeUserD", // 明细-渠道概况-活跃用户
        "19": "DPD-retentionP", // 明细-渠道概况-留存率
        "20": "DPD-tradeUC", // 明细-渠道概况-交易用户数
        "21": "DPD-tradeC", // 明细-渠道概况-交易笔数
        "22": "DPD-arpuD",  // 明细-渠道概况-arpu值
        "23": "DPD-datePayP", // 明细-渠道概况-日付费率
        // 24、25是后端处理的权限，前端无视之
        //"24": "DRD-rechargeD", // 明细-交易明细-游戏直充
        //"25": "DRD-nrechargeD", // 明细-交易明细-非直充
        "26": "DUD-new", // 明细-用户数据-新增
        "27": "DUD-active", // 明细-用户数据-活跃
        // 28、29是菜单权限
        "28": "D-newDetailM" // 明细-新增明细
        ,"29": "D-activeDetailM" // 明细-活跃明细
    },
    indexToUserRole: {
        "4": "游戏管理",
        "5": "子渠道管理",
        "6": "子帐号管理",
        "7": "游戏直充",
        "8": "直充交易数据",
        "9": "非直充交易数据",
        "10": "交易用户数",
        "11": "新增/活跃用户数",
        "12": "生成下载链接",
        "13": "交易数据"
    },
    // 判断是否拥有权限
    hasPower: function(key){
        return this.userPower[key];
    },
    // 根据json获取 菜单
    // 形式1：{title:"", list[{id:, text:}]}
    // 形式2: {title:"", list:[ {title:, list: []}, {title:"", list:[]}  ]};
    // @param isLabel 是否以label形式生成
    // @param breakLine label时，是否需要断行
    getMenuHtml: function(obj, isLabel, breakLine){
        var title = obj.title || "全选";
        var list = obj.list;

        var html = '<div class="title"><span class="all checkboxWrap"><i class="checkbox "></i>全选</span></div>';
        html += '<div class="manageList clearfix">';
        
        if(list){
            for(var i = 0, max = list.length; i < max; i++){
                var item = list[i];

                if( !$.isPlainObject(item) ){
                    item = {id: item, text: item};
                }
                
                if(isLabel){
                    html += '<div class="col">'
                    html += this.getMenuItem(item);
                    html += "</div>";
                }else{
                    if(item.list){
                        html += '<div class="col">'
                        html += this.getMenuItem(item);
                        for(var j=0, l=item.list.length; j<l; j++){
                            html += this.getMenuItem(item.list[j]);
                        }
                        html += "</div>";
                    }else{
                        html += '<div class="col">'
                        html += this.getMenuItem(item);
                        html += "</div>";
                    }
                }
            }

        }
        
        return html += '</div>';
    },
    getMenuItem: function(obj, title){
        if(!obj.disabled){
            return '<p class="checkboxWrap '+(obj.list ? 'fatherBox' : 'sonBox')+'"><i class="checkbox " '+ (obj.id ? "data-value='" + obj.id + "'" : "") +'></i>'+ (title || obj.text || obj.title) +'</p>'
        }
    },
    // 把menu绑定到元素上
    bindMenuToElem: function(elem, obj, cf){
        var $elem = $(elem);
        $elem.html(gObject.getMenuHtml(obj, cf && cf.isLabel, cf && cf.breakLine));
        // 绑定相关UI，如果没有绑定过的，则绑定
        if( !$elem.data("isInitCheckbox") ){
            $elem.data("isInitCheckbox", true);
            
            $elem.on("click", '.checkboxWrap' ,function(){
                var $this = $(this);

                $this.find('.checkbox').toggleClass('selected');

                var $v;
                var $u = $this.parents('.manage');
                var $col = $this.parents('.col');

                if($this.hasClass('fatherBox')){
                    if($this.find('.checkbox.selected').length == 1){
                        $col.find('.sonBox .checkbox').addClass("selected");
                    }else{
                        $col.find('.sonBox .checkbox').removeClass("selected");
                    }
                }else{
                    if($col.find('.sonBox .checkbox.selected').length != $col.find('.sonBox').length){
                        $col.find('.fatherBox .checkbox').removeClass("selected");
                    }else{
                        $col.find('.fatherBox .checkbox').addClass("selected");
                    }
                }

                if($this.hasClass('all')){
                    $v = $this.parent().next();
                    if($this.find('.checkbox.selected').length != 1){
                        $u.find(".checkbox").removeClass("selected");
                    }else{
                        $u.find(".checkbox").addClass("selected");
                    }
                }else{
                    $v = $this.parents('.manageList');
                    if($v.find('.checkbox.selected').length == $v.find('.checkbox').length) {
                        // 全选中
                        $u.find(".all .checkbox").addClass("selected");
                    } else {
                        $u.find(".all .checkbox").removeClass("selected");
                    }
                }

            });

        }
    },
    // 获取全部选中的值
    getMenuCheckList: function(elem){
        var list = [];
        // $(elem).find("[type=checkbox]").each(function(i, e){
        //     var $e = $(e), val = $e.data("value");
        //     val && $e.is(":checked") && list.push(val);
        // });
        $(elem).find(".col .checkbox").each(function(i, e){
            var $e = $(e);
            if($e.hasClass("selected")){
                var val = $e.data("value");
                list.push(val);
            }
        });
        return list;
    }
});


// 公共控制器: user 信息部分，用户名、权限、是否大渠道、id等
var userMsgCtrl = {
    _map: {},
    init: function(app, name){
        // 防止重复初始化
        var name = name || "userMsgCtrl";
        if(!this["_map"][name]){
            this["_map"][name] = true;
            app.controller(name, ["$scope", "$timeout", this.ctrl]);
        }
    },
    ctrl: function($scope, $timeout){
        gObject.userMsgDef.done(function(data){
            // 因为涉及到异步，暂时不知道要不要timeout
            // $timeout(function(){
                $scope.user = {
                    name: data.currUserName,
                    id: data.currAccId,
                    isBigChannel: data.currLevel == 1,
                    level: data.currLevel   // 是第 n 级角色
                };
            // });
        });
        // 权限控制，是否有某个权限
        $scope.hasPower = function(key){
            return gObject.hasPower(key);
        }
    }
};



// 公共控制器: header 部分
var headerCtrl = {
	/**
	 * 初始化header的控制器
	 * @param APP {angular.module}
	 * @param ctrlName {String} 控制器名字
	 * @param config {Object} 控制器的相关配置
	 */
	init: function(APP, config){
		APP.controller("header", ["$scope", "$http", "$sce", this.getHeaderCtrlFunc(config)]);
        // 用户信息控制
        userMsgCtrl.init(APP, "userCtrl");
	},
	/**
	 * 获取header controller的处理函数
	 * @param config {Object} 控制器的配置
	 *		{isIndex: 是否首页？, isChildPipe: 是否子渠道？}
	 */
	getHeaderCtrlFunc: function(config){

		config = $.extend({
			isIndex: false,
			isChildPipe: false
		}, config || {});

		return function($scope, $http, $sce){
			// 顶部控制部分
			$scope.userName = "";// mHelper.location.searchObj["user"] || "";

			$scope.isIndex = config.isIndex;
            $scope.isMainDetail = config.isMainDetail;
            $scope.isMainBak = config.isMainBak;
			$scope.isChildPipe = config.isChildPipe;
            $scope.isChildAccount = config.isChildAccount;
            $scope.isRecharge = config.isRecharge;
            $scope.isGameManage = config.isGameManage;
            $scope.isUserList = config.isUserList;
            $scope.isAboutMe = config.isAboutMe
            $scope.isVoucher = config.isVoucher;
            $scope.isGpGame = config.isGpGame;
            $scope.isSettlement = config.isSettlement;

            $scope.isBigAcc = true;
            $scope.$on("headerCurrAccInfo", function(e, data){
                // 大渠道隐藏游戏管理
                $scope.isBigAcc = data.isBigAcc;
            });

            $scope.enterVoucher = function(){
                if( !$scope.isVoucher ){
                    mHelper.enterPage.voucher();
                }
            }
            $scope.enterGameManage = function(){
                //if( !$scope.isGameManage ){
                    mHelper.enterPage.GameManage({isBig: $scope.isBigAcc});
                //}
            }
			$scope.exit = function(){
				mHelper.enterPage.login();
			}
			$scope.enterChilePipe = function(){
				// if( !$scope.isChildPipe ){
					mHelper.enterPage.childPipe();
				// }
			}
			$scope.myApps = function(){
				if( !$scope.isIndex ){
					mHelper.enterPage.main();
				}
			}
            $scope.myAppsDetail = function(){
                if( !$scope.isMainDetail ){
                    mHelper.enterPage.mainDetail();
                }
            };
            $scope.myAppsBak = function(){
                if( !$scope.isMainBak ){
                    mHelper.enterPage.mainBak();
                }
            };
            $scope.enterChileAccount = function(){
                // if( !$scope.isChildAccount ){
					mHelper.enterPage.childAccount();
				// }
            }
            $scope.enterRecharge = function(){
                if( !$scope.isRecharge ){
					mHelper.enterPage.recharge();
				}
            }
            $scope.userList = function(){
                if( !$scope.isUserList ){
                    mHelper.enterPage.userList();
                }
            }
            $scope.enterAboutMe = function(){
                if( !$scope.isAboutMe ){
                    mHelper.enterPage.aboutMe();
                }
            }
            $scope.enterGpGame = function(){
                if( !$scope.isGpGame ){
                    mHelper.enterPage.gpGame();
                }
            }
            $scope.enterSettlement = function(){
                if( !$scope.isSettlement ){
                    mHelper.enterPage.settlement();
                }
            }

            gObject.userMsgDef.done(function(data){
                $scope.accId = data.currAccId;
            });
            // 获取列表数据
            $scope.dataList = [];
            $scope.listError = "";
            $scope.getData = function(){
                var params = {
                    accId : $scope.accId,
                    page_idx: $scope.pager.index + 1,
                    page_size: $scope.pager.select
                };
                $http({
                    url: API.SERVER.message.getMessageList,
                    method: "GET",
                    params: params
                }).success(function(data){
                    if( data && data.code == 200 ){
                        $scope.dataList = data.data.list;
                    }else{
                        $scope.dataList = [];
                        $scope.listError = data.data;
                    }
                    // 分页信息
                    $scope.pager.total = data.data.pager && data.data.pager.totalCount || 0;
                }).error(function(){
                    $scope.dataList = [];
                    $scope.listError = "请求错误，请稍后重试";
                });
            }

            // 分页信息
            $scope.isMsg = true;
            $scope.pager = {
                index: 0,
                total: 0,
                display: 5,
                select: 5,
                callback: function(index, count){
                    // index: 页码，0开始， count: 每页多少条数据
                    $scope.getData();
                }
            };

            // 显示消息列表弹窗
            $scope.showMessageList = function(){
                $scope.getData();

                $scope.showList = true;
        
                gObject.userMsgDef.done(function(data){
                    $scope.accId = data.currAccId;
                });
                 
            
            }

            // 显示消息详情弹窗
            $scope.showMessageDetail = function(item){
                var readed = item.read;

                $scope.showList = false;
                $scope.showDetail = true;

                item.read = 1;

                var params  = {
                    accId : $scope.accId,
                    msgId : item.id
                };
                
                $http({
                    url: API.SERVER.message.messageDetail,
                    method: "GET",
                    params: params
                }).success(function(data){
                    if( data && data.code == 200 ){
                        $scope.message = data.data;
                    }else{
                        $scope.message = "";
                        console.log("获取不到数据!");
                    }
                }).error(function(){
                    $scope.listError = "请求错误，请稍后重试";
                });

                if($scope.unread > 0 && readed != 1){
                    $scope.unread = $scope.unread - 1;    
                }
            
            }

            // 返回消息列表
            $scope.backToList = function(){
                $scope.showDetail = false;
                $scope.showList = true;
            }
			
            // 从 global 信息获取用户
            gObject.userMsgDef.done(function(accInfo, data){
                $scope.userName = accInfo.currUserName;
                $scope.unread = accInfo.unread;
                $scope.$emit("headerCurrAccInfo", {
                    isBigAcc: accInfo.currLevel == 1,    // 是否大渠道
                    currAccId: accInfo.currAccId,
                    reid: accInfo.reid   // 大渠道id
                });
            });

            $scope.turnToHtml = function(p){
                return $sce.trustAsHtml(p);
            };

		}
	}
}
