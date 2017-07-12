<?php
/*
 * 游戏3D参数
 */
namespace huosdk\game;

use think\Db;

class Game3d {
	
	public function get($game_id){
		if(empty($game_id)){
			return "";
		}
		
		$_map['game_id'] = $game_id;
		$_rdata = Db::name('game_3deffect')->where($_map)->find();
		
		return $_rdata;
	}
	
	public function save($data){
		$_data['device_id'] = get_val($data, 'device_id', 0);
		$_data['user_id'] = get_val($data, 'user_id', 0);
		$_data['game_id'] = get_val($data, 'game_id', 0);
		$_data['parameter'] = get_val($data, 'parameter', 0);
		
		$_rs = Db::name('game3d_data')->insert($_data);
		if (false === $_rs) {
			return 400;
		}
		return 200;
	}
}
