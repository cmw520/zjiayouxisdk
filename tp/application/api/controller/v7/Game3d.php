<?php
/*
 * 游戏3D效果参数
 * 
 */
namespace app\api\controller\v7;

use app\common\controller\Basehuo;

class Game3d extends Basehuo {
	
	function _initialize(){
		parent::_initialize();
	}
	
	public function get() {
		$_key_arr = array(
			'game_id'	
		);
		$this->getParams($_key_arr);
		$_game3d_class = new \huosdk\game\Game3d();
		$_rdata = $_game3d_class->get($this->rq_data['game_id']);
		
		if(empty($_rdata)){
			return hs_huosdk_responce(400,"获取3D参数失败");
		}
		return hs_huosdk_responce(200, '获取3D参数成功', $_rdata, $this->auth_key);
	}
	
	public function save(){
		/*
		$orgkey = "280_".time()."_qwertyuiopasdfgh";
		
		$_pri_path = CONF_PATH.'extra/key/rsa_private_key.pem';
		$_pub_path = CONF_PATH.'extra/key/rsa_public_key.pem';
		$_rsa_class = new \huosdk\common\Rsa($_pub_path, $_pri_path);
		$key = $_rsa_class->encrypt($orgkey);
		$key = urlencode($key);
		echo "key=".$key.'<br>';
		
		$authkey = "8a58bef9b80e35bcc09537084ddacae4qwertyuiopasdfgh";
		$arr['game_id']='33';
		$arr['game_name']='rrrr';
		$arr['game_note']='rfss';
		
		$_auth_class = new \huosdk\common\Authcode();
		$_auth_jsondata = json_encode($arr);
		$data = $_auth_class->discuzAuthcode($_auth_jsondata, 'ENCODE', $authkey, 0);
		$data = urlencode($data);
		echo "data=".$data.'<br>';
		exit();
		*/
		
		$_key_arr = array(
			'device_id',
			'user_id',
			'game_id',
			'parameter'
		);
		$this->getParams($_key_arr);
		$_game3d_class = new \huosdk\game\Game3d();
		$_rdata = $_game3d_class->save($this->rq_data);
		
		if(200 == $_rdata){
			hs_huosdk_responce(200,'调节参数成功');
		}
		hs_huosdk_responce(400,'调节参数失败');
	}
}