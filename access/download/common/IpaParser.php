<?php
/**
 * 解析Ipa plist文件
 *
 * @author zhoushen extrembravo@gmail.com
 * @since  2014/2/14
 */
require dirname(__FILE__).'/CFPropertyList/CFPropertyList.php';

class IpaParser {
    const INFO_PLIST = 'Info.plist';

    public function parse($ipaFile, $infoFile = self::INFO_PLIST) {
        $zipObj = new ZipArchive();
        if ($zipObj->open($ipaFile) !== true) {
            throw new PListException("unable to open {$ipaFile} file!");
        }
        // scan plist file
        $plistFile = null;
        for ($i = 0; $i < $zipObj->numFiles; $i++) {
            $name = $zipObj->getNameIndex($i);
            if (preg_match('/Payload\/(.+)?\.app\/'.preg_quote($infoFile).'$/i', $name)) {
                $plistFile = $name;
                break;
            }
        }
        // parse plist file
        if (!$plistFile) {
            throw new PListException("unable to parse plist file！");
        }
        // deal in memory
        $plistHandle = fopen('php://memory', 'wb');
        fwrite($plistHandle, $zipObj->getFromName($plistFile));
        rewind($plistHandle);
        $zipObj->close();
        $plist = new CFPropertyList($plistHandle, CFPropertyList::FORMAT_AUTO);
        $this->plistContent = $plist->toArray();
        return true;
    }

    // 获取包名
    public function getPackage() {
        return $this->plistContent['CFBundleIdentifier'];
    }

    // 获取版本
    public function getVersion() {
        return $this->plistContent['CFBundleVersion'];
    }

    // 获取应用名称
    public function getAppName() {
        return $this->plistContent['CFBundleDisplayName'];
    }

    // 获取解析后的plist文件
    public function getPlist() {
        return $this->plistContent;
    }

    // 生成下载plist文件
    /*
     * @param $path plist文件路径
     */
    public function createPlist($path, $downurl, $data) {
        $plist = new CFPropertyList();
        /*
         * 手动创建sample.xml.plist
         */
        $packname = $data['pakagename'];
        $title = $data['appname'].'(安装完成之后，请到设置->通用->描述文件与设备管理，信任企业级应用)';
        $version = $data['vername'];
        $plist->add($dict = new CFDictionary());
        $dict->add('items', $items_array = new CFArray());
        $items_array->add($items_array_dict = new CFDictionary());
        $items_array_dict->add('assets', $assets_array = new CFArray());
        $assets_array->add($assets_dict1 = new CFDictionary());
        $assets_dict1->add('kind', new CFString('software-package'));
        $assets_dict1->add('url', new CFString($downurl));
        $items_array_dict->add('metadata', $metadata_dict = new CFDictionary());
        $metadata_dict->add('bundle-identifier', new CFString($packname));
        $metadata_dict->add('bundle-version', new CFString($version));
        $metadata_dict->add('kind', new CFString('software'));
        $metadata_dict->add('title', new CFString($title));
        $plist->saveXML($path);
    }

    /**
     * 创建Webclip
     *
     * @method : createMobileCnf
     * @param   : $path weblip文件路径(绝对路径)
     * @param   : $iconpath weblip中icon路径
     * @param   : $url weblip跳转的web路径
     * @param   : $data array 包名 应用名 版本
     *
     * @return  :
     * @author  : wuyonghong <wyh@huosdk.com>
     * @date    : 2016年12月10日下午3:40:11
     * @since   7.0
     * @modified:
     */
    public function createWebclip($path, $iconpath, $url, array $data) {
        $packname = $data['pakagename'];
        $toolname = $data['appname'];
        $uuid = (string)$this->gen_uuid();
        $identifier = strtoupper(gethostname()).(string)$uuid;
        $plist = new CFPropertyList();
        $plist->add($dict = new CFDictionary());
        /* 添加mobileconfig基本信息 */
        $dict->add('PayloadContent', $dict_arr = new CFArray());
        $dict->add(
            'PayloadDescription',
            new CFString("修复".$toolname."程序.当".$toolname."闪退时使用\"修复".$toolname."\"进行修复.")
        );
        $dict->add('PayloadDisplayName', new CFString("修复".$toolname));
        $dict->add('PayloadIdentifier', new CFString($packname.$identifier));
        $dict->add('PayloadOrganization', new CFString(''));
        $dict->add('PayloadRemovalDisallowed', new CFBoolean(false));
        $dict->add('PayloadType', new CFString('Configuration'));
        $dict->add('PayloadUUID', new CFString($uuid));
        $dict->add('PayloadVersion', new CFNumber(1));
        $icondata = base64_encode(file_get_contents($iconpath));
        $icon_arr = str_split($icondata, 52);
        $icondata = implode('   ', $icon_arr);
        /* 添加存储信息 */
        $dict_arr->add($arr_dict = new CFDictionary());
        $arr_dict->add('FullScreen', new CFBoolean(true));
        $arr_dict->add('Icon', new CFData($icondata, true));
        $arr_dict->add('IsRemovable', new CFBoolean(false));
        $arr_dict->add('Label', new CFString('修复'.$toolname));
        $arr_dict->add('PayloadDescription', new CFString('Adds a Web Clip.'));
        $arr_dict->add('PayloadDisplayName', new CFString('Web Clip (修复'.$toolname.')'));
        $arr_dict->add('PayloadIdentifier', new CFString($packname.$identifier.'webclip'));
        $arr_dict->add('PayloadOrganization', new CFString(''));
        $arr_dict->add('PayloadType', new CFString('com.apple.webClip.managed'));
        $arr_dict->add('PayloadUUID', new CFString($identifier));
        $arr_dict->add('PayloadVersion', new CFNumber(1));
        $arr_dict->add('Precomposed', new CFBoolean(false));
        $arr_dict->add('URL', new CFString($url));
        $plist->saveXML($path);
    }

    protected function gen_uuid() {
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        } else {
            mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
            $charid = strtoupper(md5(uniqid(rand(), true))); // 根据当前时间（微秒计）生成唯一id.
            $hyphen = chr(45); // "-"
            $uuid = ''.substr($charid, 0, 8).$hyphen.substr($charid, 8, 4).$hyphen.substr($charid, 12, 4).$hyphen
                    .substr(
                        $charid,
                        16,
                        4
                    ).$hyphen.substr($charid, 20, 12);
            return $uuid;
        }
        return rtrim(shell_exec("uuidgen"));
    }
}
