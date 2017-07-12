<?php
namespace Huosdk\Data;
class Base {
    public function generate_table_header($data) {
        $th_txt = '';
        foreach ($data as $v) {
            $th_txt .= "<th>$v</th>";
        }
        return $th_txt;
    }

    public function generate_table_sum($fields) {
        $txt = '<tr style="color:#0ae;">';
        foreach ($fields as $field) {
            $txt .= '<td>'.$field.'</td>';
        }
        $txt .= '<tr>';
        return $txt;
    }

    public function generate_table_content($data = array(), $fields = array()) {
        $txt = '';
        foreach ($data as $k => $v) {
            $txt .= '<tr>';
            foreach ($fields as $field) {
                $txt .= "<td>".$v[$field]."   </td>";
            }
            $txt .= "</tr>";
        }
        return $txt;
    }
}

