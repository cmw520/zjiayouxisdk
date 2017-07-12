<?php
// 数据库配置文件引入
if (file_exists(SITE_PATH."conf/db.php")) {
    $db = include SITE_PATH."conf/db.php";
} else {
    $db = array();
}
// 基本配置文件引入
if (file_exists(SITE_PATH."conf/config.php")) {
    $runtime_config = include SITE_PATH."conf/config.php";
} else {
    $runtime_config = array();
}
// 代理配置文件引入
if (file_exists(SITE_PATH."conf/mobile/config.php")) {
    $mobile_config = include SITE_PATH."conf/mobile/config.php";
} else {
    $mobile_config = array();
}
// 公司信息配置文件引入
if (file_exists(SITE_PATH."conf/company.php")) {
    $company_config = include SITE_PATH."conf/company.php";
} else {
    $company_config = array();
}
// 路由配置文件
if (file_exists(SITE_PATH."conf/route.php")) {
    $routes = include SITE_PATH."conf/route.php";
} else {
    $routes = array();
}
if (file_exists(SITE_PATH."conf/setting.inc.php")) {
    $setting_config = include SITE_PATH."conf/setting.inc.php";
} else {
    $setting_config = array();
}
$configs = array(
    "LOAD_EXT_FILE"          => "extend",
    'UPLOADPATH'             => 'upload/',
    'SHOW_ERROR_MSG' => true, 
    'SHOW_PAGE_TRACE'        => false,
    'TMPL_STRIP_SPACE'       => true,  // 是否去除模板文件里面的html空格与换行
    'THIRD_UDER_ACCESS'      => false,  // 第三方用户是否有全部权限，没有则需绑定本地账号
    /* 标签库 */
    'TAGLIB_BUILD_IN'        => THINKCMF_CORE_TAGLIBS,
    'MODULE_ALLOW_LIST'      => array(
        'Mobile'
    ),
    'TMPL_DETECT_THEME'      => false,  // 自动侦测模板主题
    'TMPL_TEMPLATE_SUFFIX'   => '.html',  // 默认模板文件后缀
    'DEFAULT_MODULE'         => 'Mobile',  // 默认模块
    'DEFAULT_CONTROLLER'     => 'Index',  // 默认控制器名称
    'DEFAULT_ACTION'         => 'index',  // 默认操作名称
    'DEFAULT_M_LAYER'        => 'Model',  // 默认的模型层名称
    'DEFAULT_C_LAYER'        => 'Controller',  // 默认的控制器层名称
    'DEFAULT_FILTER'         => 'htmlspecialchars',  // 默认参数过滤方法 用于I函数...htmlspecialchars
    'LANG_SWITCH_ON'         => true,  // 开启语言包功能
    'DEFAULT_LANG'           => 'zh-cn',  // 默认语言
    'LANG_LIST'              => 'zh-cn,en-us,zh-tw',
    'LANG_AUTO_DETECT'       => false,
    'VAR_MODULE'             => 'g',  // 默认模块获取变量
    'VAR_CONTROLLER'         => 'm',  // 默认控制器获取变量
    'VAR_ACTION'             => 'a',  // 默认操作获取变量
    'APP_USE_NAMESPACE'      => true,  // 关闭应用的命名空间定义
    'APP_AUTOLOAD_LAYER'     => 'Controller,Model',  // 模块自动加载的类库后缀
    'AUTOLOAD_NAMESPACE'     => array(
        'plugins' => THINK_PATH.'plugins/'
    ),  // 扩展模块列表
    'SP_TMPL_PATH'           => 'themes/',       // 前台模板文件根目录
    'SP_DEFAULT_THEME'       => 'simplebootx',       // 前台模板文件
    'SP_TMPL_ACTION_ERROR'   => 'error', // 默认错误跳转对应的模板文件,注：相对于前台模板路径
    'SP_TMPL_ACTION_SUCCESS' => 'success', // 默认成功跳转对应的模板文件,注：相对于前台模板路径
    'SP_ADMIN_STYLE'         => 'flat',
    'SP_ADMIN_TMPL_PATH'     => '../mobile/themes/',       // 各个项目后台模板文件根目录
    'SP_ADMIN_DEFAULT_THEME' => 'simplebootx',       // 各个项目后台模板文件
    'SP_ADMIN_TMPL_ACTION_ERROR' 	=> 'error.html', // 默认错误跳转对应的模板文件,注：相对于后台模板路径
    'SP_ADMIN_TMPL_ACTION_SUCCESS' 	=> 'success.html', // 默认成功跳转对应的模板文件,注：相对于后台模板路径
    'TMPL_EXCEPTION_FILE'   => SITE_PATH.'public/exception.html',
    'ERROR_PAGE'             => '',  // 不要设置，否则会让404变302
    'VAR_SESSION_ID'         => 'session_id',
    'SESSION_OPTIONS'        => array(
        'domain' => DOMAIN
    ),  // session配置
    'COOKIE_DOMAIN'          => DOMAIN,  // cookie域名
    "COOKIE_EXPIRE"          => "3600",
    "UCENTER_ENABLED"        => 0,  // UCenter 开启1, 关闭0
    "COMMENT_NEED_CHECK"     => 0,  // 评论是否需审核 审核1，不审核0
    "COMMENT_TIME_INTERVAL"  => 60,  // 评论时间间隔 单位s
    /* URL设置 */
    'URL_CASE_INSENSITIVE'   => true,  // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'              => 1,  // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE 模式); 3 (兼容模式) 默认为PATHINFO 模式，提供最好的用户体验和SEO支持
    'URL_PATHINFO_DEPR'      => '/',  // PATHINFO模式下，各参数之间的分割符号
    'URL_HTML_SUFFIX'        => '',  // URL伪静态后缀设置
    'VAR_PAGE'               => "p",
    'URL_ROUTER_ON'          => true,
    'URL_ROUTE_RULES'        => $routes,
    /*性能优化*/
    'OUTPUT_ENCODE'          => true,  // 页面压缩输出
    'HTML_CACHE_ON'          => false,  // 开启静态缓存
    'HTML_CACHE_TIME'        => 60,  // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'       => '.html',  // 设置静态缓存文件后缀
    'TMPL_PARSE_STRING'      => array(
//        '/Public/upload' => '/upload', 
'__UPLOAD__'  => __ROOT__.'/access/upload/',
'__STATICS__' => __ROOT__.'/access/statics/',
"__PUBLIC__"  => __ROOT__.'/public'
    )
);
return array_merge($configs, $routes, $db, $mobile_config, $runtime_config, $company_config,$setting_config);
