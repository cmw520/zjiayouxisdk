<?php
/**
 * HuoImage.php UTF-8
 * 火速图片处理
 *
 * @date    : 2017/2/7 20:12
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : wuyonghong <wyh@huosdk.com>
 * @version : HUOSDK 7.0
 */
namespace huosdk\common;

use think\Image;

class HuoImage {
    public function savePortrait($file) {
        $_image = Image::open($file);
        $_savename = uniqid().'.'.$_image->type();
        $_path = DS.'upload'.DS.date('Ymd');
        $_savepath = ROOT_SITE_PATH.'access'.$_path;

        if (!is_dir($_savepath)) {
            mkdir($_savepath, 0777, true);
        }
        $_image->save($_savepath.DS.$_savename);

        return $_path.DS.$_savename;
    }
}