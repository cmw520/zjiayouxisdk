"use strict";
var API = API || {};
API.ajaxDataPreprocessor = function(data){
    switch(data.code){
        case 200:
            return false;
            break;
        case 2000:
            if(confirm(LANG[LANGUAGE]["CLIENT_SESSIONTIMEOUT"])){
                // location.href="./"+"?token="+location.urlParameter("token")+"&appID="+$(this).data("appId")+"#/login"+"!/"+btoa( location.href);
                location.href = "../";
            }
            return false;
            break;
        case 500:
            if(confirm(LANG[LANGUAGE]["CLIENT_SESSIONTIMEOUT"])){
                // location.href="./"+"?token="+location.urlParameter("token")+"&appID="+$(this).data("appId")+"#/login"+"!/"+btoa( location.href);
                location.href = "../";
            }
            return false;
            break;
        case 1000:
            alert(LANG[LANGUAGE]['CLIENT_NAMEORPASSWORNINCORRECT']);
            return true;
        break;
        case 2006:
            alert(LANG[LANGUAGE]['CLIENT_NAMEORPASSWORNINCORRECT']);
            return true;
            break;
        case 2003:
            alert(LANG[LANGUAGE]['CLIENT_NAMEORPASSWORNINCORRECT']);
            return true;
            break;
        case 777:
            alert("您的账号已被禁用，请联系客户");
            return true;
            break;
        default :
            alert(LANG[LANGUAGE]['CLIENT_UNKNOWERR']);
            console.log(data);
            return true;
            break;
    }

};
