function initZeroClipboard() {
    if (typeof(ZeroClipboard) == "undefined") {
        $('.zeroclipboard').each(function (event) {
            $(this).click(function () {
                copy_clip($(this).attr("data-clipboard-text"), "复制成功");
            });
        });
    } else {
        var client = new ZeroClipboard($('.zeroclipboard'));
        var zclip  = new ZeroClipboard($(".zeroclipboard"));
        zclip.on("ready", function (readyEvent) {
            zclip.on("aftercopy", function (event) {
                alert("复制成功");
            });
        });
        zclip.on('error', function (event) {
            ZeroClipboard.destroy();
        });
    }
}

function copy_clip(copy, tips) {
    if (window.clipboardData) {
        window.clipboardData.setData("Text", copy);
    } else if (window.netscape) {
        try {
            netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
        } catch (e) {
            alert("您已经取消操作！\n或者此操作被浏览器拒绝！解决方法如下：\n在浏览器地址栏输入'about:config'后回车\n将signed.applets.codebase_principal_support的值设置为'true',双击即可。");
        }
        var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
        if (! clip)
            return;
        var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
        if (! trans)
            return;
        trans.addDataFlavor('text/unicode');
        var str      = new Object();
        var len      = new Object();
        var str      = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        var copytext = copy;
        str.data     = copytext;
        trans.setTransferData("text/unicode", str, copytext.length * 2);
        var clipid = Components.interfaces.nsIClipboard;
        if (! clip)
            return false;
        clip.setData(trans, null, clipid.kGlobalClipboard);
    }
    alert(tips);
    return false;
}
