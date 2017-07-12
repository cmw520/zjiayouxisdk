//新手任务
$(".djycdiv").click(function(){
  $(".rwts").hide();
});

//手机福利社
$('.giftApp .a-02').hover(function() {
	$('.codeBox').removeClass('hide');
}, function() {
	$('.codeBox').addClass('hide');
});

// 首页页签切换
$('.cTabTop span').click(function(event) {
	var idx = $(this).index();
	$(this).addClass('on').siblings('span').removeClass('on');
	$('.tabBox .tabGiftBox').eq(idx).removeClass('hide').siblings().addClass('hide');
});
// 排行热榜悬停效果
$('.rankItemBox1').hover(function() {
	$(this).parent('.rankLine').find('.rankItemBox2').removeClass('hide');
	$(this).addClass('hide');
	$(this).parent('.rankLine').siblings().find('.rankItemBox2').addClass('hide');
	$(this).parent('.rankLine').siblings().find('.rankItemBox1').removeClass('hide');
}, function() {
	return false;
});
// 排行热榜页签
$('.rankFilter span').click(function(event) {
	var idx = $(this).index();
	$(this).addClass('on').siblings('span').removeClass('on');
	$('.cRankBox').eq(idx).removeClass('hide').siblings('.cRankBox').addClass('hide');
});
// 无缝滚动
(function(a){a.fn.scroll=function(k){function e(b,c){b.find("ul").animate({marginTop:"-=1"},0,function(){Math.abs(parseInt(a(this).css("margin-top")))>=c&&(a(this).find("li").slice(0,1).appendTo(a(this)),a(this).css("margin-top",0))})}var g=a.extend({},{speed:40,rowHeight:24},k),c=[];this.each(function(b){var f=g.rowHeight,h=g.speed,d=a(this);c[b]=setInterval(function(){d.find("ul").height()<=d.height()?clearInterval(c[b]):e(d,f)},h);d.hover(function(){clearInterval(c[b])},function(){c[b]=setInterval(function(){d.find("ul").height()<=d.height()?clearInterval(c[b]):e(d,f)},h)})})}})(jQuery);

$('.hotGiftInner li').hover(function() {
	$(this).addClass('on');
}, function() {
	$(this).removeClass('on');
});
$('.cAside a').hover(function() {
	$('.codeBox').removeClass('hide');
}, function() {
	$('.codeBox').addClass('hide');
});
//手机福利社漂浮
$('.asideClose').click(function(event) {
	$('.cAside').hide();
	return false;
});
$('.cAside a').hover(function() {
	$('.codeBox').removeClass('hide');
}, function() {
	$('.codeBox').addClass('hide');
});


/*复制*/
$(".copy-btn").click(function(){
	$(".copy-success").show();
	var i = 2;
	var count_down  = setInterval(function(){
		i--;
		if(i==0){
			clearInterval(count_down);
			$(".copy-success").hide();
		}
	},1000);
});

with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion='+~(-new Date()/36e5)];

//个人任务中心切换滚动
jQuery(".picScroll-left").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"leftLoop",autoPlay:false,vis:4,trigger:"click"});

function setTab(name, cursel, n) {
    for (i = 1; i <= n; i++) {
        var menu = document.getElementById(name + i);
		var con = document.getElementById("con_" + name + "_" + i);
        menu.className = i == cursel ? "on" : ""; 
		con.style.display = i == cursel ? "block" : "none";
    } 
}

/*$(function(){
	$(".picScroll-left").find("li").click(function(){
		var flag = $(this).index();
		$(this).addClass("on").siblings("li").removeClass("on");
		$(".all_tab_bot").find(".task-tab-box").eq(flag).show().siblings(".task-tab-box").hide();
	});
});*/

$(function(){
	$(".gift-tab-top").find("li").click(function(){
		var flag = $(this).index();
		$(this).addClass("on").siblings("li").removeClass("on");
		$(".gift-tab-bot").find(".gift-tab-item").eq(flag).show().siblings(".gift-tab-item").hide();
	});
});

$(function(){
	$(".task-tab-top").find("li").click(function(){
		var flag = $(this).index();
		$(this).addClass("on").siblings("li").removeClass("on");
		$(".task-tab-bot").find(".task-tab-item").eq(flag).show().siblings(".task-tab-item").hide();
	});
});
