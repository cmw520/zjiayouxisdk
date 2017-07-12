function xhr() {
    if (window.XMLHttpRequest) {
        //针对FireFox，Mozillar，Opera，Safari，IE7，IE8
        xmlHttpRequest = new XMLHttpRequest();
        //针对某些特定版本的mozillar浏览器的BUG进行修正
        if (xmlHttpRequest.overrideMimeType) {
            xmlHttpRequest.overrideMimeType("text/xml");
        }
    } else if (window.ActiveXObject) {
        //针对IE6，IE5.5，IE5
        //两个可以用于创建XMLHTTPRequest对象的控件名称，保存在一个js的数组中
        //排在前面的版本较新
        var activexName = ["MSXML2.XMLHTTP", "Microsoft.XMLHTTP"];
        for (var i = 0; i < activexName.length; i ++) {
            try {
                //取出一个控件名进行创建，如果创建成功就终止循环
                //如果创建失败，回抛出异常，然后可以继续循环，继续尝试创建
                xmlHttpRequest = new ActiveXObject(activexName[i]);
                if (xmlHttpRequest) {
                    break;
                }
            } catch (e) {
            }
        }
    }
    return xmlHttpRequest;
}
function get(xhrName, url, succ, err) {
    var xhrName = new xhr();
    if (xhrName) {
        xhrName.open("GET", url, true);
        xhrName.onreadystatechange = function (data) {
            if (xhrName.readyState == 4) {
                if (xhrName.status == 200) {
                    var headerText = xhrName.responseText;
                    succ(headerText)
                } else {
                    err(msg);
                }
            }
        }
        xhrName.send(null);
    }
}