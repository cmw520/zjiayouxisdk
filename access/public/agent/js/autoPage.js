function jumpPage_queryForm(x){
    sendDate("1.php",{page:x},function(data){
        console.log(data);
    })
}
/*****************Ìø×ªÒ³°´Å¥****************/
function locatePage_queryForm(count){
    $(".paging ul li").each(function(){
        if($(this).html()==count){
            $(this).attr("class","now-num").siblings().removeClass("now-num");
        }
    });
    sendDate("1.php",{value:count},function(data){
        console.log(data);
    })
}