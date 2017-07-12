<?php
/*
 * 设备验证
 */
 
namespace huosdk\model;

use think\Db;

class Model {
	
	public function check($series){
		if(empty($series)){
			return "";
		}
		
		$_map['series'] = $series;
		$_map['status'] = 1;
		$_data = Db::name('device')->where($_map)->find();
		
		if(empty($_data)){
			echo "";
		}
		
		return $_data;
	}
}
