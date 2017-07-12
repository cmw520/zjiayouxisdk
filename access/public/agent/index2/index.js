var Index = {};

// banner切换
Index["banner"] = function(){
    var $root = $("#background"), $dots = $(".dots", $root);
    var $images = $(".images", $root);
    var max = $images.size(), current = 0;
    var activeClass = "active";

    // 插入指示器
    $dots.html( (new Array(max + 1)).join('<span class="dot"></span>'));
    $dots = $(".dot", $root);
    $dots.eq(0).addClass(activeClass);

    // 设置当前索引
    function setIndex(index){
        var old = current;  // 用于修正图片，因为最后一张的存在，SO，一定要在修正索引前，先记录下来

        if( index >= max ){
            index = current = 0;
        }
        current = index;
        var $now = $dots.filter("." + activeClass).removeClass(activeClass).end().eq(index).addClass(activeClass);

        fixImage(old, current);
    };
    // 修复图片、背景颜色
    // 实际上，这里如果要变为插件，把回调还给外部即可
    function fixImage(old, now){
        $images.eq(old).fadeOut().end().eq(now).fadeIn();
    }

    // 计时器
    var timer;
    function bsetInterval(){
        bcloseInterval();
        timer = window.setInterval(function(){
            setIndex(current + 1);
        }, 3000);
    }
    function bcloseInterval(){
        window.clearInterval(timer);
    }

    // 启动计时器
    bsetInterval();

    // 一些事件的响应
    $root.on("mouseenter", ".dots", bcloseInterval)
        .on("mouseleave", ".dots", bsetInterval)
        .on("click", ".dot", function(){
            setIndex($(this).index());
        });
};

// tab 切换
Index["gameTab"] = function(){
    var $root = $("#games"), $tab = $(".header", $root);
    var $more = $(".more", $tab);   // 更多游戏
    var $move = $(".lists", $root), $indicator = $(".line", $tab);  // 两个需要移动的玩意
    // 指示器每次移动的距离
    var indicatorML = $tab.find(".item").eq(0).outerWidth();
    // 选中的class
    var activeClass = "active";


    // 设置索引
    function setIndex(index){
        $move.css("margin-left", "-" + (index * 100) + "%");
        $indicator.css("left", index * indicatorML);
    }

    $tab.on("click", ".item", function(){
        var $that = $(this);
        $that.addClass(activeClass).siblings("." + activeClass).removeClass(activeClass);
        $more.attr("href", $that.data("href"));
        setIndex($that.index());
    });

};

// 平台优势的tips
Index["advantageTips"] = function(){
    var $root = $("#advantage"), $tip = $(".tips", $root);
    // 信息需要重新设置
    var $text = $(".ttext", $tip), $head = $(".thead", $tip);
    // 从第1个li获取需要的信息
    var $firstLi = $("li[data-text]", $root);
    // 基本的 Item 的宽度，margin距离
    var width = $firstLi.outerWidth(), margin = 2 * $firstLi.prop("offsetLeft");
    var total = width + margin, half = total / 2;
    // tip 的长度
    var tipWidth = $tip.outerWidth();

    // 移动距离计算公式: half + index * total - tipWidth / 2
    function getOut(){
        $tip.animate({
            opacity: 0
        }, 600, function(){
            $tip.css("display", "none");
        });
    };
    function comeIn(index){
        $tip.css("display", "block").stop(1, 0).animate({
            opacity: 1,
            left: half + index * total - tipWidth / 2
        }, 400);
    };
    // 初始状态
    getOut();

    // 事件绑定
    // 觉得如果随意移动，都tip出来，挺浪费的，加一个计时器，hover一定时间，才飘提示
    var timer;
    $root.on("mouseenter", "li[data-text]", function(){
        clearTimeout(timer);
        var $that = $(this);
        timer = setTimeout(function(){
            comeIn($that.index());
            $text.html($that.data("text"));
            $head.html($that.find(".text").html());
        }, 250);
    }).on("mouseleave", "li[data-text]", function(){
        clearTimeout(timer);
        timer = setTimeout(function(){
            getOut();
        }, 250);
    });

    // 重复hover
    $tip.on("mouseenter", function(){
        $tip.stop(1, 1).css({
            display: "block", opacity: 1
        });
    }).on("mouseleave", function(){
        $tip.stop(1, 1).css("display", "none");
    });

};

// cookie 操作，帐号还是保存cookie吧
Index["cookie"] = {
    set: function(key, value, day){
        var date = new Date();
        date.setDate(date.getDate() + day);
        document.cookie = [key + "=" + value, "expires=" + date.toGMTString(), "path=/"].join(";") + ";";
    },
    get: function(key){
        var arr = new RegExp( key + "=([^;]*)" ).exec(document.cookie);
        return arr ? arr[1] : null;
    }
};

// 登录
Index["login"] = function(){
    var $root = $("#loginForm");
    var $username = $(".username", $root),
        $password = $(".password", $root),
        $remember = $(".remember", $root),
        $error = $(".error", $root),
		$code = $(".capcha", $root);

    // 登录操作
    var isLoging = false;
    function login(){
        if(isLoging){return;}


        var username = $.trim($username.val()),
            password = $password.val();
        if( username == "" ){
            return error("用户名不能为空");
        }else if( password == "" ){
            return error("密码不能为空");
        }

        error("登录中...");

        var firstDef = $.Deferred();
        // 按照以前逻辑改写
//        $.get(API.SERVER.channelSalt, {
//                username: username,
//                timestamp: Date.now()
//            }
//        ).done(function(message){
//            if(message.code!=200){
//                error(LANG[LANGUAGE]['CLIENT_NAMEORPASSWORNINCORRECT']);
//                firstDef.reject();
//                return;
//            }
//            // 可以往下执行
//            firstDef.resolve(message);
//        }).fail(function(message){
//            error(LANG[LANGUAGE]['CLIENT_UNKNOWERR']);
//            firstDef.reject();
//        });

        // 第1次请求成功之后，继续执行后续操作
        firstDef.done(function(message){
            var postData = {
                username: username,
                password: CryptoJS.MD5(message.data + password).toString(),
                verifyCode : $(".code", $root).val(),
				timestamp: Date.now()
            };
            $.post(API.SERVER.channelLogin, postData).done(function(message){
                /*
				if(message.code == 2016){
                    alert("您好，当前帐号不属于新版本内测帐号，暂时无法登录，敬请期待！");
                    return;
                }
				*/
                if(message.code == 1234){
                    error("验证码错误");
                    return;
                }
                if(message.code == 403){
                    error("错误次数过多，请10分钟后再尝试！");
					return;
                }
                if(message.code != 200){
                    error(LANG[LANGUAGE]['CLIENT_NAMEORPASSWORNINCORRECT']);
                    return;
                }
                if( $remember.is(":checked") ){
                    Index.cookie.set("username", username, 30);
                }else{
                    Index.cookie.set("username", "", -30);
                }
                // var href = mHelper.location.searchObj.href;
                // if( href ){
                //     // #/login!/某个链接吧?	负责这种规律的，走这里
                //     location.href = atob(href).replace(/token=[^&]*/g, "token=" + message.token3);
                // }else{
                error();
                gObject.init();
                gObject.userMsgDef.done(function(){
                    if( gObject.hasPower('accountM') && gObject.hasPower('pipeM')){
                        mHelper.enterPage.ltChildAccount(message.token3);
                    }else{
                        mHelper.enterPage.ltGameManage(message.token3);
                    }
                });

                // }
            }).fail(function(message){
                error(LANG[LANGUAGE]['CLIENT_UNKNOWERR']);
            }).always(function(){
                isLoging = false;
            });
        }).fail(function(){
            isLoging = false;
        });

    }
    // 错误信息
    function error(txt){
        if(txt){
            $error.text(txt).fadeIn();
        }else{
            $error.fadeOut();
        }
    }
    // 如果有cookie，则敲入
    var username = Index.cookie.get("username");
    if(username){
        $username.val(username);
        $remember.prop("checked", true);
    }


    $root.on("focus", ".input", function(){
        error();
    }).on("click", ".login", function(){
        //当已经存在验证码时不生成新的验证码
    	var display = $(".codeWrap").css("display");
		if(display == 'none'){    
			getCodeImg();
		}
        login();
    });
    // 敲入回车，则登录
    $password.on("keypress",function(e){
        if(e.keyCode==13){
        	//当已经存在验证码时不生成新的验证码
    		var display = $(".codeWrap").css("display");
			if(display == 'none'){    
				getCodeImg();
			}
            login();
        }
    });
    // 密码框获取焦点
    $password.on("focus",function(e){
        //当已经存在验证码时不生成新的验证码
    	var display = $(".codeWrap").css("display");
		if(display == 'none'){    
			getCodeImg();
		}
    });
    // 换一张验证码
    
	$code.on("click",function(){
        getCodeImg();
    });

	$(".code", $root).on("keypress",function(e){
        if(e.keyCode==13){
            login();
        }
	});	
	
    function getCodeImg(){
		var username = $username.val();
       	var timestamp = new Date().getTime();
		//var url = "https://tt.guopan.cn/api/verificationCode.php?username=" + username + "&tm=" + timestamp; 
		var url = "./api/verificationCode.php?username=" + username + "&tm=" + timestamp; 
		$.get(url).done(function(data){
            if(data){
				// 设置图片路径
            	$(".capcha").attr("src", url);
				$(".codeWrap").show();
			}
        });
    }
}


$(function(){
    // banner 切换
    Index.banner();
    // game tab 切换
    Index.gameTab();
    // 平台优势tips
    Index.advantageTips();
    // 登录
    Index.login();
});
