<?php
namespace Huosdk\UI;
class Pieces {
    public static function export_excel() {
        $txt
            = <<< EOT
        
<input type="submit"  name='submit' style='float:right;'  class="btn btn-success" value="导出数据" />

EOT;
        return $txt;
    }
}

