var yxspa={};
yxspa.page_controller={};
yxspa.api_prefix="http://localhost/projects/kechuang_custom_site/api/index.php";

yxspa.changeUrl = function () {
    var url = location.hash.replace('#', '');
    if (url === '/') {
        url = "/home";
    }
    if(location.hash==''){
        url = "/home";
    }
    
    console.log(window.location);

    var page_url = "pages" + url + ".html";

    $.get(page_url, function (response) {
        
        if (url == "/home") {
            $("#tpl_page_home").html(response);
            yxspa.page_controller.home();
        }else if(url == "/news"){
            $("#tpl_page_news").html(response);
            yxspa.page_controller.news();
        }else if(url == "/contact"){
            $("#yxframe_content").html(response);
        }else if(url == "/view_news"){
            $("#tpl_page_tmp").html(response);
            yxspa.page_controller.view_news();
        }
    });

};
yxspa.page_controller.home=function(){
    var all_content = '';
    
    var slides_data = {
        list: ['./static/img/slides/1.png', './static/img/slides/2.jpg']
    };
    all_content += template('slides', slides_data);

    var navi_data = {
        list: ['首页', '动态', '产品介绍', '加入我们', '联系我们'],
        products: [
            {'img': "./static/img/products/1.jpg", "name": "联运平台"},
            {'img': "./static/img/products/1.jpg", "name": "推广平台"},
            {'img': "./static/img/products/1.jpg", "name": "管理平台"},
            {'img': "./static/img/products/1.jpg", "name": "多级分销"}
        ]
    };
    all_content += template('brefintro', navi_data);

    var history_data = {
        list: ['首页', '动态', '产品介绍', '加入我们', '联系我们']
    };
    all_content += template('history', history_data);

    var culture_data = {
        list: ['首页', '动态', '产品介绍', '加入我们', '联系我们']
    };
    all_content += template('culture', culture_data);

    document.getElementById('yxframe_content').innerHTML = all_content;

    var swiper = new Swiper('.swiper-container', {
            autoplay: 3000,
            pagination: '.swiper-pagination',
            paginationClickable: '.swiper-pagination',
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
            spaceBetween: 30
        }
    );
};
yxspa.page_controller.news = function(){
    var all_content = '';
    
    $.post(yxspa.api_prefix+"?action=get_news_data",{},function(res){
        var list=res;
        all_content += template('module_news', list);    
        document.getElementById('yxframe_content').innerHTML = all_content;        
    });
    
//    var slides_data = {
//        list: ['./static/img/slides/1.png', './static/img/slides/2.jpg']
//    };
    
};

yxspa.page_controller.view_news = function(){
    var all_content = '';
    var news_id=yxspa._get("id");
    console.log(news_id);
    $.post(yxspa.api_prefix+"?action=get_news_item_data&news_id="+news_id,{},function(res){
        var list=res;
        all_content += template('tpl_page_view_news', list);    
        document.getElementById('yxframe_content').innerHTML = all_content;        
    });
    
};

yxspa._get= function GetQueryString(name){
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!==null)return  unescape(r[2]); return null;
};