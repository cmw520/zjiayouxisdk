var yxshare={};

yxshare.funcs={};
yxshare.ui={};

yxshare.ui.alert=function(txt){
    layer.alert(txt,{shift:7});
};

yxshare.ui.alert2=function(txt){
    layer.alert(txt,{shift:7},function(){
        location.reload();
    });
};

yxshare.ui.redirect_delay=function(url,time){
    setTimeout(function(){
        location.href=url;
    },time);
}

yxshare.ui.notice = function (txt){
    layer.msg(txt,{
        shift:7,
        offset: '20px',
        area: '300px'
        }
    );
}
yxshare.ui.confirm=function(txt,func1){
    layer.confirm(
        txt, {
        btn: ['确定','取消'] 
        },
        func1, 
        function(){}
    );
};
