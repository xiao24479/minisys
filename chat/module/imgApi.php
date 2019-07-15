<?php
require_once(APP_PATH."/include/libs.php");

class imgApi extends cls_base{

	var $db;

	function init() {
		global $_DB;
		
		$this->db = $_DB;
		
	}
	
	public function get_imgs() {
		$appid = get_data('appid');
		
		$sql = "select id_hospital from irpt_interface where appId = '{$appid}'";
		$hid = $this->db->get_one($sql);
		
		$arr = ['jpg', 'jpeg', 'png'];
		$imgs = [];
		
		$hid_dir = "/data/uploads/mini_program/{$hid}";
		if(!file_exists(ROOT_PATH.$hid_dir)){
			mkdir(ROOT_PATH.$hid_dir);
		}
		
		$filepath = $hid_dir."/lunbo";
		if(!file_exists(ROOT_PATH.$filepath)){
			mkdir(ROOT_PATH.$filepath);
		}
		
		$dir = opendir(ROOT_PATH.$filepath);
		
		while($item = readdir($dir)){
			$ext = pathinfo($item, PATHINFO_EXTENSION);
			if(in_array($ext, $arr)){
				$url = "http://".$_SERVER['HTTP_HOST'].$filepath."/".$item;
				$imgs[] = $url;
			}
		}
		
		make_json_result($imgs, 'ok', []);
	}
	
	function dg_imgs() {
		$dir = "http://".$_SERVER['HTTP_HOST']."/data/nk/dg_nk/";
		$path = ROOT_PATH."data/nk/dg_nk";
		$urls = [];
		
		$handle = opendir($path);
		while($item = readdir($handle)){
			if($item != '.' && $item != '..'){
				$name = pathinfo($item, PATHINFO_FILENAME);
				$urls[$name] = $dir.$item;	
			}
		}
		make_json_result($urls, '', []);
	}
}

?>