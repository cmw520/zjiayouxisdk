function sendData(url,data,succ,err,type,dataType,conentType){
    if(!type){type="POST"}
    if(!err){err=""}
    if(!url){throw new Error("url is not find...");}
    if(!dataType){dataType="JSON"}
    if(!conentType){conentType="application/x-www-form-urlencoded"}
    $.ajax({
        type:type,
        dataType:dataType,
        url: url, //目标地址
        data:data,
        success:succ,
        error:err
    });
}


//获取短信验证码
function _getCode(el,time,back,interval,msgBox){
    var code=$(el);
    if(!time){time=30}
    if(!interval){interval=1000}
    var time1=time;
    var codeback=code.css("background-color");
    var codeColor=code.css("color");
    code.css("background-color","#aaa");
    code.css("color","#fff");
    code.unbind("click",back);
    time1--;
    code.html(time1+"秒");
    code.addClass("msgs1");
    var t=setInterval(function  () {
        time1--;
        code.html(time1+"秒");
        if (time1==0) {
            clearInterval(t);
            code.html("重新获取");
            code.removeClass("msgs1");
            code.css("background-color",codeback);
            code.css("color",codeColor);
            code.bind("click",back);
            showMsg(msgBox,"请尽快完成验证码，五分钟内有效","red");
        }
    },interval)
}
function showMsg(el,text,color){
    $(el).css("display","block");
    $(el).html(text);
    $(el).css("color",color);
}

function succAside(i){
    $(".aside_nav ul li").each(function(){
        $(this).removeClass("active");
    });
    $(".aside_nav ul li").eq(i).addClass("active");
}

function succAsideID(current_id){
    $(".aside_nav ul li").each(function(){
        $(this).removeClass("active");
    });
    $("#"+current_id).addClass("active");
}

