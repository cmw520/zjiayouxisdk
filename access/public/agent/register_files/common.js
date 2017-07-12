/**
 * 身份证验证
 *
 * @example
 * var id = $('#idCard').val();
 * if(id != "" && !chkIdCard(id)){
 *   alert("格式错误");
 * }
 */
// 身份证验证
chkIdCard_DL = function (card) {
    var vcity = {
        11: "北京",
        12: "天津",
        13: "河北",
        14: "山西",
        15: "内蒙古",
        21: "辽宁",
        22: "吉林",
        23: "黑龙江",
        31: "上海",
        32: "江苏",
        33: "浙江",
        34: "安徽",
        35: "福建",
        36: "江西",
        37: "山东",
        41: "河南",
        42: "湖北",
        43: "湖南",
        44: "广东",
        45: "广西",
        46: "海南",
        50: "重庆",
        51: "四川",
        52: "贵州",
        53: "云南",
        54: "西藏",
        61: "陕西",
        62: "甘肃",
        63: "青海",
        64: "宁夏",
        65: "新疆",
        71: "台湾",
        81: "香港",
        82: "澳门",
        91: "国外"
    };
    // 检查号码是否符合规范，包括长度，类型
    isCardNo  = function (card) {
        // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
        var reg = /(^\d{15}$)|(^\d{17}(\d|X)$)/;
        if (reg.test(card) === false) {
            return false;
        }
        return true;
    };

    // 取身份证前两位,校验省份
    checkProvince = function (card) {
        var province = card.substr(0, 2);
        if (vcity[province] == undefined) {
            return false;
        }
        return true;
    };

    // 检查生日是否正确
    checkBirthday = function (card) {
        var len = card.length;
        // 身份证15位时，次序为省（3位）市（3位）年（2位）月（2位）日（2位）校验位（3位），皆为数字
        if (len == '15') {
            var re_fifteen = /^(\d{6})(\d{2})(\d{2})(\d{2})(\d{3})$/;
            var arr_data   = card.match(re_fifteen);
            var year       = arr_data[2];
            var month      = arr_data[3];
            var day        = arr_data[4];
            var birthday   = new Date('19' + year + '/' + month + '/' + day);
            return verifyBirthday('19' + year, month, day, birthday);
        }
        // 身份证18位时，次序为省（3位）市（3位）年（4位）月（2位）日（2位）校验位（4位），校验位末尾可能为X
        if (len == '18') {
            var re_eighteen = /^(\d{6})(\d{4})(\d{2})(\d{2})(\d{3})([0-9]|X)$/;
            var arr_data    = card.match(re_eighteen);
            var year        = arr_data[2];
            var month       = arr_data[3];
            var day         = arr_data[4];
            var birthday    = new Date(year + '/' + month + '/' + day);
            return verifyBirthday(year, month, day, birthday);
        }
        return false;
    };

    // 校验日期
    verifyBirthday = function (year, month, day, birthday) {
        var now      = new Date();
        var now_year = now.getFullYear();
        // 年月日是否合理
        if (birthday.getFullYear() == year
            && (birthday.getMonth() + 1) == month
            && birthday.getDate() == day) {
            // 判断年份的范围（3岁到100岁之间)
            var time = now_year - year;
            if (time >= 3 && time <= 100) {
                return true;
            }
            return false;
        }
        return false;
    };

    // 校验位的检测
    checkParity = function (card) {
        // 15位转18位
        card    = changeFivteenToEighteen(card);
        var len = card.length;
        if (len == '18') {
            var arrInt   = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5,
                8, 4, 2);
            var arrCh    = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4',
                '3', '2');
            var cardTemp = 0, i, valnum;
            for (i = 0; i < 17; i ++) {
                cardTemp += card.substr(i, 1) * arrInt[i];
            }
            valnum = arrCh[cardTemp % 11];
            if (valnum == card.substr(17, 1)) {
                return true;
            }
            return false;
        }
        return false;
    };

    // 15位转18位身份证号
    changeFivteenToEighteen = function (card) {
        if (card.length == '15') {
            var arrInt   = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5,
                8, 4, 2);
            var arrCh    = new Array('1', '0', 'X', '9', '8', '7', '6', '5', '4',
                '3', '2');
            var cardTemp = 0, i;
            card         = card.substr(0, 6) + '19' + card.substr(6, card.length - 6);
            for (i = 0; i < 17; i ++) {
                cardTemp += card.substr(i, 1) * arrInt[i];
            }
            card += arrCh[cardTemp % 11];
            return card;
        }
        return card;
    };

    /***** 开始验证    *****/
    if (card === '' || isCardNo(card) === false
        || checkProvince(card) === false || checkBirthday(card) === false
        || checkParity(card) === false) {
        return false;
    }
    return true;
};
//台湾身份证校验
chkIdCard_TW = function (card) {
    var varea = {
        "A": 10,
        "B": 11,
        "C": 12,
        "D": 13,
        "E": 14,
        "F": 15,
        "G": 16,
        "H": 17,
        "I": 34,
        "J": 18,
        "K": 19,
        "L": 20,
        "M": 21,
        "N": 22,
        "O": 35,
        "P": 23,
        "Q": 24,
        "R": 25,
        "S": 26,
        "T": 27,
        "U": 28,
        "V": 29,
        "W": 32,
        "X": 30,
        "Y": 31,
        "Z": 33
    };
    if (! /^[a-zA-Z][1-2]\d{8}$/.test(card)) {//验证基本格式
        return false;
    }
    card    = varea[card.substr(0, 1).toUpperCase()] + "" + card.substr(1);
    var i   = 9;
    var sum = parseInt(card.substr(0, 1));
    while (i > 0) {
        sum += parseInt(card.substr(10 - i, 1)) * i;
        i --;
    }
    if (parseInt(card.substr(10, 1)) != (10 - sum % 10) % 10) {//校验位验证
        return false;
    }
    return true;
}
//香港身份证校验
chkIdCard_XG = function (card) {
    var varea = {
        "A": 1,
        "B": 2,
        "C": 3,
        "D": 4,
        "E": 5,
        "F": 6,
        "G": 7,
        "H": 8,
        "I": 9,
        "J": 10,
        "K": 11,
        "L": 12,
        "M": 13,
        "N": 14,
        "O": 15,
        "P": 16,
        "Q": 17,
        "R": 18,
        "S": 19,
        "T": 20,
        "U": 21,
        "V": 22,
        "W": 23,
        "X": 24,
        "Y": 25,
        "Z": 26
    };
    card      = card.replace(/\(|\)|\s/g, "");
    if (! /^[a-zA-Z]\d{6}[\d|A]$/.test(card)) {
        return false;
    }
    var sum = varea[card.substr(0, 1).toUpperCase()] * 8;
    var i   = 7;
    while (i > 1) {
        sum += card.substr(8 - i, 1) * i;
        i --;
    }
    var valnum = (11 - sum % 11) % 11 == 10 ? "A" : "" + (11 - sum % 11) % 11;
    if (card.substr(7, 1).toUpperCase() != valnum) {
        return false;
    }
    return true;
}

chkIdCard = function (card) {//目前只支持大陆、台湾、香港
    if (chkIdCard_DL(card) || chkIdCard_TW(card) || chkIdCard_XG(card)) {
        return true;
    }
    return false;
}

/**
 * 限制文本区域长度
 * @param {Object} obj
 * @return {TypeName}
 */
function checkLen(obj, len) {
    //document.getElementById("span_orderNo").innerHTML = obj.value.length;
    if (obj.value.length > len) {
        //alert('最多输入200个字！ ');
        //自动截取长度
        obj.value = obj.value.substring(0, len);
        return false;
    }
}

/**
 * 匹配正则表达式
 * @param {Object} filename
 * @param {Object} strType
 * @return {TypeName}
 */
function IsMatch(filename, strType) {
    var regex;
    switch (strType) {
        //验证输入是否为字母和数字的组合
        case "true" :
            regex = /^[a-z_A-Z0-9]+$/;
            break;
        //电子邮件
        case "email" :
            regex = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4})(\]?)$/;
            break;
        //身份证ID
        case "idcard" :
            // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
            //regex = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
            return chkIdCard(filename);
        //break;
        //验证输入的字符是否有空格
        case "empty" :
            regex = /^[^\s]{2,20}$/;
            break;
        //验证手机号码
        case "mobilephone" :
            regex = /^(13|14|15|18|17)\d{9}$/;
            break;
        //验证电话号码
        case "phone" :
            //regex = /^([0-9]\d{10,12})$/;
            regex = /^([0-9]\d{9,11})$/;
            break;
        //验证邮编是否合格
        case "zip" :
            regex = /^([0-9]\d{5})$/;
            break;
        //验证是整数
        case "integer" :
            regex = /^[0-9]\d{0,15}$/;
            break;
        //包含汉字
        case "chinese" :
            //regex = /[^ -~]/;  //包含汉字
            regex = /[一-龥]/; //包含汉字
            //regex = /[^一-龥]/; //检验都是汉字（验证是否有不是汉字的，对，有不是汉字的，而不是:不包含汉字）： /[^一-龥]/， [^\u4e00-\u9fa5]
            //regex = /[^\u4e00-\u9fa5]+[^\u4e00-\u9fa5]+$/;
            //regex = /[^\u4e00-\u9fa5]+[^\u4e00-\u9fa5]+[^\u4e00-\u9fa5]+$/;
            break;

        //默认不能包含特殊符号
        default :
            regex = /^[一-龥_a-zA-Z0-9]+$/;
            break;
    }
    //return regex.IsMatch(filename);
    var result = filename.match(regex);
    if (result == null)
        return false;
    else
        return true;
}

//保留两位小数   
//功能：将浮点数四舍五入，取小数点后2位          

function toDecimal(x) {
    var f = parseFloat(x);

    if (isNaN(f)) {
        return;

    }

    f = Math.round(x * 100) / 100;

    return f;

}
//制保留2位小数，如：2，会在2后面补上00.即2.00          

function toDecimal2(x) {

    var f = parseFloat(x);

    if (isNaN(f)) {
        return false;

    }

    var f = Math.round(x * 100) / 100;

    var s = f.toString();

    var rs = s.indexOf('.');

    if (rs < 0) {
        rs = s.length;

        s += '.';
    }

    while (s.length <= rs + 2) {
        s += '0';

    }

    return s;
}

function fomatFloat(src, pos) {
    return Math.round(src * Math.pow(10, pos)) / Math.pow(10, pos);

}

//返回合并后的对象
function merge(a, b) {
    var aLen = a.length, bLen = b.length;
    var xLen = aLen + aLen, y = 0;
    var c    = new Array()[xLen];
    var i    = 0, j = 0;

    //合并后的对象从索引0到最后1个
    while (y < xLen) {
        //i,j都必须在2个数组长度范围内
        if (i < aLen && j < bLen) {
            if (a[i] <= b[j]) {
                c[y ++] = a[i ++];
            } else {
                c[y ++] = b[j ++];
            }
        }

        //否则，i还在数组a的范围内，但是j已经不再数组b的范围了
        else if (i < aLen) {
            c[y ++] = a[i ++];
        }

        //否则，j还在数组b范围内，但i已经不再a范围内了
        else {
            c[y ++] = b[j ++];
        }
    }
    return c;
}

//////////////////********** 转换日期格式**********//////////////////

//动态显示时间
function showTime(Today) {

    //var Today=new Date()
    var year  = Today.getYear()
    var month = Today.getMonth()
    var date  = Today.getDate()

    var hours   = Today.getHours()
    var minutes = Today.getMinutes()
    var seconds = Today.getSeconds()

    if (hours == 0)
        hours = 12

    if (hours <= 9)
        hours = "0" + hours
    if (minutes <= 9)
        minutes = "0" + minutes
    if (seconds <= 9)
        seconds = "0" + seconds

    myclock = year + "/" + (month + 1) + "/" + date + "/ " + " " + hours + ":"
        + minutes + ":" + seconds;

    return myclock;
}

/////////////////////////////////e///////////////////////
// 取得当前日期,格式yyyy-mm-dd
////////////////////////////////////////////////////////
function GetCurrentDate() {
    var Year        = 0;
    var Month       = 0;
    var Day         = 0;
    var CurrentDate = new Date();

    return ChangeDateToString(CurrentDate);
}

/////////////////////////////////e///////////////////////
// 取得当前日期,格式yyyy-mm-dd hh:mm
////////////////////////////////////////////////////////
function GetCurrentTime() {
    var Year        = 0;
    var Month       = 0;
    var Day         = 0;
    var CurrentDate = new Date();

    return ChangeTimeToString(CurrentDate);
}

////////////////////////////////////////////////////////
// 将日期类型转换成字符串型格式yyyy-MM-dd
////////////////////////////////////////////////////////
function ChangeDateToString(DateIn) {
    var Year  = 0;
    var Month = 0;
    var Day   = 0;

    var CurrentDate = "";

    //初始化时间
    Year  = DateIn.getYear();
    Month = DateIn.getMonth() + 1;
    Day   = DateIn.getDate();

    //IE显示正确年，自动加上1900，而FF显示原来的年？？？
    Year = Year < 1900 ? (Year + 1900) : Year;

    CurrentDate = Year + "-";
    if (Month >= 10) {
        CurrentDate = CurrentDate + Month + "-";
    } else {
        CurrentDate = CurrentDate + "0" + Month + "-";
    }
    if (Day >= 10) {
        CurrentDate = CurrentDate + Day;
    } else {
        CurrentDate = CurrentDate + "0" + Day;
    }

    return CurrentDate;
}

///////////////////////////////////////////////////////
// 将日期类型转换成字符串型格式 hh:mm:ss
////////////////////////////////////////////////////////
function ChangeWhenToString(DateIn) {
    var CurrentDate = "";

    //初始化时间

    var Hour    = DateIn.getHours();
    var Minute  = DateIn.getMinutes();
    var Seconds = DateIn.getSeconds();

    if (Hour < 10) {
        Hour = "0" + Hour;
    }
    if (Minute < 10) {
        Minute = "0" + Minute;
    }
    if (Seconds < 10) {
        Seconds = "0" + Seconds;
    }

    CurrentDate = Hour + ":" + Minute + ":" + Seconds;
    return CurrentDate;
}

///////////////////////////////////////////////////////
// 将日期类型转换成字符串型格式yyyy-MM-dd hh:mm:ss
////////////////////////////////////////////////////////
function ChangeTimeToString(DateIn) {
    var CurrentDate = "";

    //初始化时间
    var Year    = DateIn.getYear(); //IE显示正确年，自动加上1900，而FF显示原来的年？？？
    var Month   = DateIn.getMonth() + 1;
    var Day     = DateIn.getDate();
    var Hour    = DateIn.getHours();
    var Minute  = DateIn.getMinutes();
    var Seconds = DateIn.getSeconds();

    //IE显示正确年，自动加上1900，而FF显示原来的年？？？
    Year = Year < 1900 ? (Year + 1900) : Year;

    if (Month < 10) {
        Month = "0" + Month;
    }
    if (Day < 10) {
        Day = "0" + Day;
    }

    if (Hour < 10) {
        Hour = "0" + Hour;
    }
    if (Minute < 10) {
        Minute = "0" + Minute;
    }
    if (Seconds < 10) {
        Seconds = "0" + Seconds;
    }

    CurrentDate = Year + "/" + Month + "/" + Day + " " + Hour + ":" + Minute
        + ":" + Seconds;
    return CurrentDate;
}

/**
 * DIV中嵌套DIV，点击里面的按钮事件后，不触发上一次DIV的按钮事件
 * @param {Object} e
 */
function stopBubble(e) {
    //一般用在鼠标或键盘事件上
    if (e && e.stopPropagation) {
        //W3C取消冒泡事件
        e.stopPropagation();
    } else {
        //IE取消冒泡事件
        window.event.cancelBubble = true;
    }
};

//根据当前复选框是否选中，决定全部选中全部取消
//function check_All(obj){
function select_All(obj, name) {
    //debugger;

    var isCheck = $(obj).is(":checked");
    //alert(isCheck)
    $("input[type=checkbox][name=" + name + "]").attr("checked", isCheck);

}

/**
 * JQuery边框闪烁
 * @param {Object} ele
 * @param {Object} cls
 * @param {Object} times
 * @return {TypeName}
 */
function shake(ele, cls, times) {
    //debugger;
    var i = 0, t = false, o = ele.attr("class") + " ", c = "", times = times
        || 2;
    if (t)
        return;
    t = setInterval(function () {
        i ++;
        c = i % 2 ? o + cls : o;
        ele.attr("class", c);
        if (i == 2 * times) {
            clearInterval(t);
            ele.removeClass(cls);
        }
    }, 200);
};

/**
 * 中间消息弹窗口后消失
 * @param {Object} divid
 * @param {Object} msg
 * @memberOf {TypeName}
 */
function alertMsg(divid, msg, time) {
    if (! $('#' + divid).is(':visible')) {
        $("#" + divid).html(msg);
        $('#' + divid).css({
            display: 'block',
            top    : '-100px'
        }).animate({
            top: '+100'
        }, 500, function () {

            //延迟1秒后执行
            setTimeout(function () {
                $('#' + divid).animate({
                    top: '0'
                }, 500, function () {
                    $(this).css({
                        display: 'none',
                        top    : '-100px'
                    });
                });
            }, time == null ? 1000 : time);
        });
    }
}

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r   = window.location.search.substr(1).match(reg);
    if (r != null)
        return unescape(r[2]);
    return null;
}

function copy_clip(copy) {
    if (window.clipboardData) {
        window.clipboardData.setData("Text", copy);
    } else if (window.netscape) {
        try {
            netscape.security.PrivilegeManager
                .enablePrivilege('UniversalXPConnect');
        } catch (e) {
            alert("您已经取消操作！\n或者此操作被浏览器拒绝！解决方法如下：\n在浏览器地址栏输入'about:config'后回车\n将signed.applets.codebase_principal_support的值设置为'true',双击即可。");
        }
        var clip = Components.classes['@mozilla.org/widget/clipboard;1']
            .createInstance(Components.interfaces.nsIClipboard);
        if (! clip)
            return;
        var trans = Components.classes['@mozilla.org/widget/transferable;1']
            .createInstance(Components.interfaces.nsITransferable);
        if (! trans)
            return;
        trans.addDataFlavor('text/unicode');
        var str      = new Object();
        var len      = new Object();
        var str      = Components.classes["@mozilla.org/supports-string;1"]
            .createInstance(Components.interfaces.nsISupportsString);
        var copytext = copy;
        str.data     = copytext;
        trans.setTransferData("text/unicode", str, copytext.length * 2);
        var clipid = Components.interfaces.nsIClipboard;
        if (! clip)
            return false;
        clip.setData(trans, null, clipid.kGlobalClipboard);
    }
    alert("复制成功");
    return false;
}