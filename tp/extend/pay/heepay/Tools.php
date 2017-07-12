<?php

class Tools {
    static function getXMLValue($srcXML, $element) {
        $ret = "";
        try {
            $begElement = "<".$element.">";
            $endElement = "</".$element.">";
            $begPos = strripos($srcXML, $begElement);
            $endPos = strripos($srcXML, $endElement);
            if (!$begPos) {
                $ret = "";
                return $ret;
            }
            if ($begPos != -1 && $endPos != -1 && $begPos <= $endPos) {
                $begPos += strlen($begElement);
                $ret = substr($srcXML, $begPos, ($endPos - $begPos));
            } else {
                $ret = "";
            }
        } catch (Exception $ex) {
            $ret = "";
        }
        return $ret;
    }
}

?>