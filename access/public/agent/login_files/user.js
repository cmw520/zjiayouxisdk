$(function(){
	popstyle('pop');
	/*顶部通用广告栏*/
	jQuery(".page-advertising").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"left",autoPlay:true,vis:1});
	jQuery(".swiperBanner").slide({effect:"left",mainCell:".bd ul",autoPlay:true});
	/*jQuery(".hotGame").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",autoPlay:false,vis:8,pnLoop:false});
	jQuery(".hotGameContent").slide({titCell:".hd ul",mainCell:".bd ul",effect:"left",autoPlay:false,vis:8,pnLoop:false});*/
	
	/*优化ie7.0和ie8.0下显示效果*/
	
	if($.browser.version==7.0||$.browser.version==8.0){
		$(".page-header").css({borderBottom:"1px solid #e4e4e4"});
		$(".page-left").css({border:"1px solid #e4e4e4"});
	}
	$(".pack-time div input").focus(function(){
			$(this).css({boxShadow:"none",border:"none",color:"#333333",backgroundColor:"#ffffff",borderRight:"1px solid #e4e4e4"});
		});
		$(".pack-time div input").blur(function(){
			$(this).css({boxShadow:"none",border:"none",color:"#333333",backgroundColor:"#ffffff"});
	});
	
	/*51508首页*/
	$(".tJ").each(function(){
		var tj = $(this).find(".title_2").text();
		if(tj.length>52){
			var encrypt = tj.substring(0,52)+"...";
			$(this).find(".title_2").text(encrypt);
		}
	});
	$(".tJ").hover(function(){
		$(this).find(".layer").animate({
			height:'76px'
		},200);
		$(this).find('.title_1').stop().animate({
			marginBottom:'0px'
		},10);
	},function(){
		$(this).find('.title_1').stop().animate({
			marginBottom:'7px'
		},10);
		$(this).find(".layer").stop().animate({
			height:'40px'
		},200);
	});
	/*热门游戏*/
	$(".newHotHeader p").click(function(){
		var index = $(this).index();
		if(index==0){
			$('.hotGame').show();
			$('.hotGameContent').hide();
		}else{
			$('.hotGameContent').show();
			$('.hotGame').hide();
		}
		$(this).addClass('on').siblings().removeClass('on');
	});
	$(".warpWhiteC_2 ul li").eq(0).css({'width':'400px'});
	$(".warpWhiteC_2 ul li").eq(0).find('img').css({'opacity':'1','filter':'alpha(opacity=100)'})
	$(".warpWhiteC_2 ul li").eq(0).find(".oneName").css('margin-left','27px');
	$(".warpWhiteC_2 ul li").eq(0).find(".twoName").css('margin-left','24px');
	$(".warpWhiteC_2 ul li").eq(0).find(".gN").css('height','78px');
	$(".warpWhiteC_5 .listul li").eq(0).css('margin-top','0px');
	$(".warpWhiteC_2 ul li").hover(
		function(){
			$(this).stop().animate({
				width:400
			},400).
			siblings().stop().animate({
				width:198
			},400);
			
			$(this).find('img').stop().animate({
				opacity:1,
				filter:'alpha(opacity=100)'
			},400);
			$(this).siblings().find('img').stop().animate({
				opacity:0.6,
				filter:'alpha(opacity=60)'
			},400);
			
			$(this).addClass('on').siblings().removeClass('on');
			$(this).find(".oneName").stop().animate({
				marginLeft:27
			},400);
			$(this).find(".twoName").stop().animate({
				marginLeft:24
			},400);
			$(this).find('.gN').stop().animate({
				height:78
			},400);
			$(this).siblings().find('.gN').stop().animate({
				height:44
			},200);
		});
	$(".warpWhiteC_2 ul li").each(function(){
		var name = $(this).find('.twoName').text();
		if(name.length>54){
			var encrypt = name.substring(0,54)+"...";
			 $(this).find('.twoName').text(encrypt);
		}
	});
	var arr = new Array();
	$(".glist .div").each(function(){
		var index = $(this).index();
		$(this).css('left',index*159+'px');
		arr.push(index*159);
		if(index>4){
			$(this).css({'left':(index-5)*159+'px','top':'236px'});
		}
	});
	$(".glist .div").hover(function(){
		$(this).addClass('hover');
		var l = $(this).css('left');
		$(this).css('left',parseInt(l)-16+"px");
	},function(){
		var index = $(this).index();
		$(this).removeClass('hover');
		if(index>4){
			$(this).css('left',arr[index-5]+"px");
		}else{
			$(this).css('left',arr[index]+"px");
		}
		
	});
	$(".viewmore").hover(
		function(){
			$(this).addClass('on');
		},function(){
			$(this).removeClass('on');
		}
	);
	$(".listul li").eq(0).css('margin-top','0px');
	$(".listul li").eq(0).css('margin-top','0px');
	/*$(".listul li").eq(0).css('height','99px');*/
});
/*弹出层 begin 父级div调用*/
function popstyle(popclass){
	$("."+popclass).css({position:"absolute",display:"none",zIndex:"9999"});
}
/*父级div调用*/
function popupbox(popid){
	var pop = "#"+popid;
	var w = $(pop).outerWidth();
	var h = $(pop).outerHeight();
	var _dl = document.documentElement.scrollTop||document.body.scrollTop;
	var l = Math.round((document.documentElement.clientWidth - w) / 2 + document.documentElement.scrollLeft);
	var t = Math.round((document.documentElement.clientHeight - h) / 2 + _dl);
	$(pop).css({"top":t,"left":l});
	$(pop).find(".pop-box-bg").css({"width":w,"height":h});
	var ch = document.documentElement.scrollHeight;
	var cw = document.documentElement.scrollLeft+document.documentElement.clientWidth;
	var cover = document.createElement("div");
	cover.id = "cover";
	cover.style.position = "absolute";
	cover.style.top = "0px";
	cover.style.left = "0px";
	cover.style.width = cw+"px";
	cover.style.height = ch + "px";
	cover.style.zIndex = "9999";
	cover.style.filter = "alpha(opacity=0)";
	cover.style.opacity = "0";
	cover.style.display = "block";
	cover.style.background = "#252130";
	cover.innerHTML = '<iframe id="if" name="if" style="position:absolute;top:-5px;left:0;border:none;width:100%;height:100%;filter:alpha(opacity=0);" ></iframe>';
	document.body.appendChild(cover);
	$("#cover").css({zIndex:"8888"});
	$("#cover").animate({opacity:0.8}, 100, function() {
		$(pop).fadeIn(300);
	});
	$(window).resize(function(){
		var ncw = document.documentElement.scrollLeft+document.documentElement.clientWidth;
		$("#cover").width(ncw);
	});
};
/*关闭按钮调用*/
function closebox(popup){
	$("#cover").remove();
	$(popup).fadeOut(100);
}
/*弹出层 end*/
///////弹出层layer
(function($){
  $.fn.mylayer = function(){
    var isIE = (document.all) ? true : false; 
    var isIE6 = isIE && !window.XMLHttpRequest; 
    var position = !isIE6 ? "fixed" : "absolute"; 
    var containerBox = $(this); 
    containerBox.css({"z-index": "9999","display": "block","position": position ,"top": "50%","left": "50%","margin-top": -(containerBox.height() / 2) + "px","margin-left": - (containerBox.width() /2 ) + "px"}); 
    var mylayer=$("<div></div>"); 
    mylayer.css({"width": "100%","height": "100%","position": position,"top": "0px","left": "0px","background-color": "#000","z-index": "9998","opacity": "0.45"}); 
    $("body").append(mylayer); 
    function mylayer_iestyle(){
      var maxWidth = Math.max(document.documentElement.scrollWidth, document.documentElement.clientWidth) + "px"; 
      var maxHeight = Math.max(document.documentElement.scrollHeight, document.documentElement.clientHeight) + "px"; 
      mylayer.css({"width": maxWidth , "height": maxHeight }); 
    }
    function containerBox_iestyle(){ 
      var marginTop = $(document).scrollTop - containerBox.height() / 2 + "px"; 
      var marginLeft = $(document).scrollLeft - containerBox.width() / 2 + "px"; 
      containerBox.css({"margin-top": marginTop , "margin-left": marginLeft }); 
    }
    if(isIE){ 
      mylayer.css("filter", "alpha(opacity=45)"); 
    } 
    if(isIE6){ 
      mylayer_iestyle(); 
      containerBox_iestyle(); 
    } 
    $("window").resize(function(){ 
      mylayer_iestyle(); 
    }); 
    $(".closepop", containerBox).click(function(){
      containerBox.hide(0); 
      $(mylayer).remove();
    }); 
    $(".closeBtn", containerBox).click(function(){
      containerBox.hide(0); 
      $(mylayer).remove();
    });
  }; 
})(jQuery);
///////弹出层layer end

$(".Pcode input").focus(function(){
	$(".Pcode p").css({'box-shadow':'0 0 3px #53aee7','border':"1px solid #53aee7"});
});
$(".Pcode input").blur(function(){
	$(".Pcode p").css({'box-shadow':'none','border':"1px solid #cccccc"});
});
/*51508首页*/
;(function($){
	$.home = $.home||{};
	$.home={
		inits:function(){
			var _this = this;
			 $(".listul li").jieQu();
			 $("ul.listul li .libtn,ul.listul li .nowdl").lisHover();
			 $("#loginPop .inputDiv input,.pwdinput").inputfocus();
			 $("#loginPop .account input").keyD();
			 $(".inputDiv input").inputblur();
			 $(".pwd .inputNoteIcon").inputNoteIcon();
			 $(".pwd .inputNoteIcon").inputNoteIconC();
			 //$(".pwd .inputNoteIcon i").viewPwd();
			 $(".account .inputNoteIcon").qk();
			 //$("#loginPop .popBtn").loginC();
			 //$(".codeBtn").countDown();
			 //$("div.phone span.inputIcon").choose();
			 //$("div.phone ul li").chooseNum();
			 //$(".popContent_1 .popBtn").checkPhone();
			 //$(".popContent_2 .popBtn").qr();
			 $(".xiugaiBtn").xiugai();
			 $(".querenBtn").queren();
			 $(".dl div a").openweb();
			 $(".textinput").fillin();
			 
		},
		jieQu:function(){
			$(this).hover(function(){
				$(this).addClass('on').siblings().removeClass('on');
				/*$(this).stop().animate({
					height:99
				},200);
				$(this).siblings().stop().animate({
					height:52
				},200);*/
			});
		},
		lisHover:function(){
			$(this).hover(function(){
				$(this).addClass('on');
			},function(){
				$(this).removeClass('on');
			});
		},
		inputfocus:function(){
			$(this).focus(function(){
				var hasclass = $(this).parent().parent().hasClass('error');
				if(!hasclass){
					$(this).parent().parent().addClass('focus');
					$(".notemsg").css('border-shadow','none');
				}
			});
		},
		inputblur:function(){
			$(this).blur(function(){
				$(this).parent().parent().removeClass('focus');
			});
		},
		inputNoteIcon:function(){
			$(this).hover(function(){
				var hasclass = $(this).find('i').hasClass('green');
				if(!hasclass){
					$(this).find('i').addClass('on');
				}
			},function(){
				var hasclass = $(this).find('i').hasClass('green');
				if(!hasclass){
					$(this).find('i').removeClass('on');
				}
			});
		},
		inputNoteIconC:function(){
			$(this).click(function(){
				var hasclass = $(this).find('i').hasClass('green');
				if(hasclass){
					$(this).parent().append("<input type='text'class='textinput'>");
					$(this).parent().find(".pwdinput").val($(this).parent().parent().find(".textinput").val());
					$(this).parent().find(".textinput").remove();
					$(this).parent().find(".pwdinput").show();
					$(this).find('i').removeClass('green');
				}else{
					$(this).parent().append("<input type='text'class='textinput'value="+$(this).parent().parent().find(".pwdinput").val()+">");
					$(this).parent().find(".pwdinput").hide();
					$(this).find('i').addClass('green');
				}
			});
		},
		viewPwd:function(){
			$(this).click(function(){
				var hasclass = $(this).hasClass('green');
				if(hasclass){
					$(this).parent().parent().append("<input type='text'class='textinput'>");
					$(this).parent().parent().find(".pwdinput").val($(this).parent().parent().find(".textinput").val());
					$(this).parent().parent().find(".textinput").remove();
					$(this).parent().parent().find(".pwdinput").show();
					
					
				}else{
					$(this).parent().parent().append("<input type='text'class='textinput'value="+$(this).parent().parent().find(".pwdinput").val()+">");
					$(this).parent().parent().find(".pwdinput").hide();
				}
			});
		},
		loginC:function(){
			$(this).click(function(){
				var account = $("#loginPop .account input").val();
				var pwd = $("#loginPop .pwd input").val();
				if(account==""){
					$("#loginPop .account").parent().addClass('error');
				}else if(pwd==""){
					$("#loginPop .account").parent().removeClass('error');
					$("#loginPop .pwd").parent().addClass('error');
				}else{
					$(this).text('正在登陆...');
					$("#loginPop .pwd").parent().removeClass('error');
					$(this).removeClass('tijiao');
					return false;
				}
			});
		},
		keyD:function(){
			$(this).keyup(function(){
				var account = $(this).val();
				if(account.trim(account)!=""){
					$(".account .inputNoteIcon").show();
				}else{
					$(".account .inputNoteIcon").hide();
				}
			});
		},
		qk:function(){
			$(this).click(function(){
				var account = $('.account input').val();
				$(".account input").val('');
			});
		},
		countDown:function(){
			$(this).click(function(){
				if($(".codeBtn").text()=="获取短信验证码"){
					var i=60;
					$(".codeBtn").css('color','#AAAAAA');
					$(".codeBtn").text(i+"秒后重新发送");
					var down = setInterval(function(){
						i--;
						$(".codeBtn").text(i+"秒后重新发送");
						$(".codeBtn").css('color','#AAAAAA')
						if(i==0){
							clearInterval(down);
							$(".codeBtn").text("获取短信验证码");
							$(".codeBtn").css('color','#666666')
						}
					},1000);
				}
				
			});
			
		},
		choose:function(){
			$(this).click(function(){
				var hasclass = $(this).hasClass('down');
				if(!hasclass){
					$(this).addClass('down');
					$(this).parent().find('ul').show();
				}else{
					$(this).removeClass('down');
					$(this).parent().find('ul').hide();
				}
				
			});
			
		},
		chooseNum:function(){
			$(this).click(function(){
				var address = $(this).find('.address').text();
				var addressNumber = $(this).find('.addressNumber').text();
				$(this).parent().parent().find('.inputIcon .address').text(address);
				$(this).parent().parent().find('.inputIcon .addressNumber').text(addressNumber);
			});
		},
		checkPhone:function(){
			$(this).click(function(){
				var phone = $("div.phone input").val();
				if(phone==""){
					$("div.phone input,.popContent_1 .firstDiv").addClass('error');
					$(".popContent_1 .firstDiv  .notemsg").hide();
					$(".popContent_1 .firstDiv .errorNoteMsg").show();
				}else{
					$(".popContent_1").hide();
					$(".popContent_2").show();
					$("#zhucePop").addClass('zhucePop_2')
				}
			});
		},
		qr:function(){
			$(this).click(function(){
				var code = $(".Pcode input").val();
				var newpwd = $(".newpwd input").val();
				var qrpwd = $(".qrpwd input").val();
				var pattern = /^(?=.*\d.*)(?=.*[a-zA-Z].*).{6,20}$/;
				if(code==""){
					$(".Pcode").parent().addClass('error');
					$(".Pcode").addClass('error');
					$(".Pcode").parent().find('.notemsg').hide();
				}else{
					$(".Pcode").parent().removeClass('error');
					$(".Pcode").removeClass('error');
					$(".Pcode").parent().find('.notemsg').show();
					if(newpwd=="" || pattern.test(newpwd) !== true){
						$(".newpwd").parent().addClass('error');
						$(".newpwd").parent().find('.notemsg').hide();
					}else{
						$(".newpwd").parent().removeClass('error');
						$(".newpwd").parent().find('.notemsg').show();
						
						if(qrpwd != newpwd){
							$(".qrpwd").parent().addClass('error');
							$(".qrpwd").parent().find('.notemsg').hide();
						}else{
							$(".qrpwd").parent().removeClass('error');
							$(".qrpwd").parent().find('.notemsg').show();
							closebox('#zhucePop');
						}
					}
					
				}
			});
			
		},
		xiugai:function(){
			$(this).click(function(){
				$(".editcontentC input").attr('disabled',false);
				$(".editcontentC input").removeClass('disable');
				$(this).hide();
				$(".querenBtn").show();
			});
		},
		queren:function(){
			$(this).click(function(){
				$(".editcontentC input").attr('disabled',true);
				$(".editcontentC input").addClass('disable');
				$(this).hide();
				$(".xiugaiBtn").show();
			});
		},
		openweb:function(){
			var daddress = $(".daddress a").text();
			$(this).attr('href',daddress);
		},
		fillin:function(){
			$(this).focus(function(){
				alert(1)
			});
			$(this).bind('input propertychange', function(){
				alert(1);
				var num = $(this).val();
				console.log(num);
			});
		}
	};
    $.extend($.fn,$.home);
    $(function(){$.home.inits();})
})(jQuery);
$(document).bind("click",function(e){ 
var target = $(e.target); 
if(target.closest("div.phone span.inputIcon").length == 0){ 
	$("div.phone span.inputIcon").parent().find('ul').hide();
	$("div.phone span.inputIcon").removeClass('down');
} 
});