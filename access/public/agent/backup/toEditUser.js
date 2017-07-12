function succHeader(headerText){
    $("body").prepend(headerText);
    $("nav .main_nav li").eq(1).addClass("active").siblings().removeClass("active");
}
get("xhr1","user_center_header.html",succHeader);
get("xhr2","aside.html",succAside);
get("xhr2","user_center_footer.html",succFoot);
function succAside(data){
    $(".page-content").html(data+$(".page-content").html());
    $(".page-left .left-content ul li").each(function(){
        $(this).removeClass("on")
    });
    $(".page-left .left-content ul>li.user-management>ul>li").eq(2).addClass("on").siblings().removeClass("on");
}
function succFoot(data){
    $("body>.main").html($("body>.main").html()+data)
}
window.addEventListener("load",function(){
    imgTabs($(".input-tab>ul>li"),"on",$(".information"),$(".personal-information-management>div"),0);
    imgTabs($(".menu>ul>li"),"on",$(".menu-content"),$(".settlement-information>div"),1);
    function imgTabs(list,ClassName,targetEl,targetBox,x){
        for(var i=0;i<list.length;i++){
            list[i].index=i;
            $(list[i]).click(function(){
                $(this).addClass(ClassName).siblings().removeClass(ClassName);
                targetEl.eq(this.index).css("display","block").siblings().css("display","none");
                if(targetBox){
                    targetBox.eq(x).css("display","block");
                }
            })
        }
    }
    $(".fill-phoone-code .get-code").each(function(){
        $(this).click(function code(){
            $(this).addClass("");
            if($(this).hasClass("get-code-one")){
                _getCode($(".fill-phoone-code .get-code-one"),30,code,1000,$("#phoone-code-two"));
                sendData("{:U('user/ucenter/do_post')}",{"way":"bank"},function(){
                    alert("银行方式....");
                })
            }else{
                _getCode($(".fill-phoone-code .get-code-two"),30,code,1000,$("#phoone-code-two"));
                sendData("{:U('user/ucenter/do_post')}",{"way":"zfb"},function(){
                    alert("支付宝方式....");
                })
            }

        });
    })
/************基本信息保存****************/
    
    /************开户信息****************/
    $(".menu-content>.display>div").each(function(){
        $(this).children("input").click(function(){
            $(this).siblings("ul").css("display","block");
            $(this).siblings("ul").children("li").click(function(){
                $(this).parent().css("display","none");
                $(this).parent().siblings("input").val($(this).html());
            });
        });
    });
    $(".one-btn").click(function(){
        var  province=$(".target-province>input").val();
        var  city=$(".target-city>input").val();
        var  bank=$(".opening-bank>input").val();
        var  bankId=$(".bank-account-name>input").val();
        var  code=$(".phoone-code-one>input").val();
        sendData("{:U('user/ucenter/do_post')}",{"province":province,"city":city,"bank":bank,"bankId":bankId,"code":code},function(){
            alert("信息已提交..");
        })
    });
    $(".two-btn").click(function(){
        if(($("#confirm-account1").val()===$("#account1").val())&&($("#account1").val().trim()!=="")){
            if($("#zfbPhonecode").val().trim()!==""){
                var zfb=$("#account1").val();
                var code=$("#zfbPhonecode").val();
                var phone=$(".verified-phone>i").html()

                sendData("{:U('user/ucenter/do_post')}",{"id":zfb,"code":code,"phone":phone},function(){
                    alert("支付宝帐号已提交...");
                })
            }else{
                showMsg("#phoone-code-two","验证码错误...","red")
            }
        }else{
            showMsg("#phoone-code-two","两次帐号输入不一致...","red")
        }

    });
    $(".settlement-information  .menu-content-1>.display>div>ul").each(function(){
        $(this).live("click",function(event){
            var val=event.target.innerHTML;
            $(this).prev().val(val);
            if($(this).parent().hasClass("target-province")){//省份
                sendData("{:U('user/ucenter/do_post')}",{"name":"sheng"},function(data){alert("要显示的省份已提交...");})
            }
        });
    });
},false)
