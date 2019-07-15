<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");

class public_menu extends cls_base {
	
	var $db;
	var $template;
	
	public function init() {
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		$this->template = $_TEMPLATE;

		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid());		
	}
	
	
	public function index() {
		$hid = get_hid();
		$sql = "select count(*) from irpt_interface where type = 1 and id_hospital = {$hid}";
		$count = $this->db->get_one($sql);
		$p = new Page($count, 20);
		
		$sql = "select * from irpt_interface where type = 1 and id_hospital = {$hid} limit ".$p->firstRow.','.$p->listRows;
		$data = $this->db->get_all($sql);
		
		$this->template->assign('data', $data);
		$this->template->assign('page', $p->show('index'));
		make_json_result($this->template->fetch('public_menu/index.html'), '', []);
	}
	
	
	public function save_menu() {
		$menu = [];
		$menu['button'] = get_data('menu');
		$menu_json = json_encode($menu, JSON_UNESCAPED_UNICODE);
		$id = get_data('id');
		
		$sql = "select * from irpt_interface where id = {$id}";
		$interface = $this->db->get_row($sql);
		
		$token_time = $interface['createTime'];
		
		if(time() - $token_time > 6000){
			$access_token = $this->get_token($interface['appId'], $interface['token']);
		}else{
			$access_token = $interface['access_token'];
		}

		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $menu_json);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		$json = curl_exec($c);
		echo $json;
	}
	
	
	public function show_menu() {
		$id = get_data('id');
		
		$sql = "select * from irpt_interface where id = {$id}";
		$interface = $this->db->get_row($sql);
		$token_time = $interface['createTime'];
		
		if(time() - $token_time > 6000 || $interface['access_token'] == null){
			$access_token = $this->get_token($interface['appId'], $interface['token']);
		}else{
			$access_token = $interface['access_token'];
		}

		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".$access_token;
		
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		
		$this->template->assign('menu_data', $json);
		$this->template->assign('id', $id);
		make_json_result($this->template->fetch('public_menu/menu.html'), '', []);
	}
	
	
	public function get_token($appid, $secret) {
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$arr = json_decode($json, true);
		return $arr['access_token'];
	}
	
}


?>