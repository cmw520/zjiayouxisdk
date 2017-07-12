// 自定义filter
var mHelper = {
	init: function(APP){
		var list = ["atob", "btoa"], name;
		for(var i = 0, max = list.length; i < max; i++){
			name = list[i];
			APP.filter(name, new Function("return mHelper[\"" + name + "\"]"));
		}
	},
	atob: function(val){
		return atob(val);
	},
	btoa: function(val){
		return btoa(val);
	},
	// 获取平台对象
	getPlateFormInstance: function(){
		return {
			"0": "不限",
			"101": "iOS",
			"102": "android"
		};
	},
	// 展示二维码
	qrCode: function(link){
		if(!link){return;}
		var $qrCode = $(".codeTip");
		var $closeBtn = $qrCode.find(".closeBtn");
		if($qrCode.size() <= 0){
			$("body").append('<div class="codeTip"><span class="closeBtn">✖</span><a href=""></a></div>');
			$qrCode = $(".codeTip");
			$closeBtn = $qrCode.find(".closeBtn");
		};
		$qrCode.show().find("a").attr("href", link).qrcode({
			text: link
		});
		$closeBtn.one("click", function(){
			$qrCode.remove();
		});
	},
	location: (function(location){
		var obj = {}, href = location.href, hash = location.hash, search = location.search;

		obj.href = href.replace(hash, "").replace(search, "");
		obj.hash = hash;
		obj.search = search;
		obj.pathname = href.match(/[^#?]*\//)[0];

		// 把搜索，转为对象
		obj.searchObj = {};
		search.replace(/([^?&=]*)=([^&]*)/g, function(str, key, value){
			obj.searchObj[key] = value;
		});


		// 参数处理
		function realParams(cf){
			var params = "";
			if(typeof cf === "object"){
				var search = cf.search || "", hash = cf.hash || "";
				// 修正hash
				if(hash && hash.indexOf("#") != 0){
					hash = "#" + hash;
				}
				// 修正search
				if(search){
					if(typeof search == "string"){
						// 没有问号的，补齐
						search = search.indexOf("?") == 0 ? search : "?" + search;
					}else{
						// 如果是对象，则进行修正
						var list = [];
						for(var i in search){
							list.push(i + "=" + search[i]);
						}
						search = "?" + list.join("&");
					}
				}

				params = search + hash;
			}
			return params;
		}

		// 更新链接
		obj.update = function(cf){
			location.href = this.href + realParams(cf);
		}
		obj.updateWithPathName = function(cf){
			//location.href = this.pathname + realParams(cf);
            location.href = 'http://' + location.host;
		}
		obj.realParams = realParams;

		return obj;
	})(window.location),
// 考虑到各个链接跳转相当的混乱
// 使用统一的接口，进行跳转好了
	enterPage: {
		main: function(token){
			// 首页需要有token
			var search = mHelper.location.searchObj;
			location.href = './childAccount.html' + mHelper.location.realParams({
				search: {
					token: token || search.token || ""
				},
				hash: "#/childAccount"
			});
		},
		mainDetail: function(token){
			// 首页需要有token
			var search = mHelper.location.searchObj;
			location.href = './checkData.html' + mHelper.location.realParams({
				search: {
					token: token || search.token || ""
				},
				hash: "#/main"
			});
		},
		mainBak: function(token){
			// 首页需要有token
			var search = mHelper.location.searchObj;
			location.href = './main_bak.html' + mHelper.location.realParams({
				search: {
					token: token || search.token || ""
				},
				hash: "#/main"
			});
		},
		mainWithAppId: function(token, appID){
			// 首页需要有token和user名字，appID 可选
			var search = mHelper.location.searchObj;
			if(typeof token === "object"){
				search = $.extend({}, search, token);
				token = null;
			};

			location.href = './main.html' + mHelper.location.realParams({
				search: (function(){
					// 有 appID 的带上，用于在 main 中，查找相关数据
					var obj = {
						token: token || search.token || ""
					};
					if(appID || search.appID){
						obj["appID"] = appID || search.appID || "";
					}

					return obj;
				})(search),
				hash: "#/main"
			});
		},
		mainWithChannelId: function(token, channel_id){
			// 首页需要有token和user名字，channel_id 可选
			var search = mHelper.location.searchObj;
			if(typeof token === "object"){
				search = $.extend({}, search, token);
				token = null;
			};

			location.href = './checkData.html' + mHelper.location.realParams({
				search: (function(){
					// 有 channel_id 的带上，用于在 main 中，查找相关数据
					var obj = {
						token: token || search.token || ""
					};
					if(channel_id || search.channel_id){
						obj["channel_id"] = channel_id || search.channel_id || "";
					}

					return obj;
				})(search),
				hash: "#/main"
			});
		},
		login: function(){
			// 首页不需要参数
			mHelper.location.updateWithPathName({});
		},
		// 管理页面
		management: function(search){
			// 搜索参数
			// token=EDDIPD3AIB
			// appID=101100
			// channel_id=1
			// cid=100
			// from=
			// to=
			var searchObj = mHelper.location.searchObj;
			location.href = './management.html' + mHelper.location.realParams({
				search: $.extend({
					token: searchObj.token || ""
				}, search),
				hash: "#/userData"
			});
		},
		userList: function(token){
			// 首页需要有token
			var search = mHelper.location.searchObj;
			location.href = './userList.html' + mHelper.location.realParams({
				search: {
					token: token || search.token || ""
				},
				hash: "#/main"
			});
		},
		// 游戏管理
		GameManage: function(obj){
			var search = mHelper.location.searchObj;
			location.href = "./gameManage.html" + mHelper.location.realParams({
				search: {token: obj && obj.token || search.token || ""},
				hash: "#/gameManage/ios"
			});
		},
		// 登录页进入游戏管理
		ltGameManage: function(obj){
			location.href = "./tpls/gameManage.html" + mHelper.location.realParams({
				search: {token: obj},
				hash: "#/gameManage/ios"
			});
		},
		// 登录页进入子帐号
		ltChildAccount:function(token){
			location.href = "./tpls/childAccount.html" + mHelper.location.realParams({
				search: {token: token},
				hash: "#/childAccount"
			});
		},
		// 子渠道
		childPipe: function(token){
			var search = mHelper.location.searchObj;
			location.href = "./childPipe.html" + mHelper.location.realParams({
				search: {token: token || search.token || ""},
				hash: "#/childPipe/list"
			});
		},
		// 子帐号
		childAccount: function(token){
			var search = mHelper.location.searchObj;
			location.href = "./childAccount.html" + mHelper.location.realParams({
				search: {token: token || search.token || ""},
				hash: "#/childAccount"
			});
		},
		// 充值管理
		recharge: function(token){
			var search = mHelper.location.searchObj;
			location.href = "./recharge.html" + mHelper.location.realParams({
				search: {token: token || search.token || ""},
				hash: "#/recharge"
			});
		},
		// 个人中心
		aboutMe: function(token){
			var search = mHelper.location.searchObj;
			location.href = "./aboutMe.html" + mHelper.location.realParams({
				search: {token: token || search.token || ""},
				hash: "#/aboutMe/safety"
			});
		},
		// 代金券统计
		voucher: function(token){
			var search = mHelper.location.searchObj;
			location.href = "./voucher.html" + mHelper.location.realParams({
				search: {token: token || search.token || ""},
				hash: "#/voucher"
			});
		},
		gpGame: function(token){
			var search = mHelper.location.searchObj;
			location.href = "./gpGame.html" + mHelper.location.realParams({
				search: {token: token || search.token || ""},
				hash: "#/gpGame"
			});
		}
	}
};
