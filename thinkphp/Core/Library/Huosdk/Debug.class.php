<?php
namespace HuoShu;
class Debug {
    private $file;

    public function __construct($file) {
        $this->file = $file;
    }

    public function log($content) {
        $handle = fopen($this->file, "a");
        fwrite($handle, date("Y-m-d H:i:s")."  ");
        fwrite($handle, $content);
        fwrite($handle, "\r\n");
//        echo SITE_PATH.$this->file.' '.$content;
        fclose($handle);
    }
}
