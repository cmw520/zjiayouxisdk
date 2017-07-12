 var clipboard = new Clipboard('.link_copy_btn');
clipboard.on('success', function (e) {
    yxalert("复制链接成功！");
});

clipboard.on('error', function (e) {
    yxalert("复制链接失败，您的浏览器不支持此功能，请右键单击选择复制！");
});

function copy(e) {
    var url = $(e).attr("data-link");

    var clipboardData = window.clipboardData; //for IE  
    if (! clipboardData) { // for chrome
        clipboardData = e.originalEvent.clipboardData;
    }
    //e.clipboardData.getData('text');//可以获取用户选中复制的数据  
    clipboardData.setData('Text', url);
    yxalert("复制链接成功！");
}
//                                    $(".link_copy_btn").click(function(){
//
//                                        var url = $(this).attr("data-link");
//                                        
//                                        var clipboardData = window.clipboardData; //for IE  
//                                        if (!clipboardData) { // for chrome  
//                                            clipboardData = this.originalEvent.clipboardData;  
//                                        }  
//                                        //e.clipboardData.getData('text');//可以获取用户选中复制的数据  
//                                        clipboardData.setData('Text', url);  
//                                        alert("复制链接成功！");
//
//                                    });

