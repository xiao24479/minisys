<?php
//小程序欺骗页面显示控制, 1为开启欺骗页面, 0为关闭, 函数名为对应的小程序APPID
require_once(APP_PATH."/include/libs.php");

class controlApi extends cls_base {
	
	var $db;
	
	public function init() {
		global $_DB;
	
		$this->db = $_DB;		
	}	
	
	public function wxc41d7049662337fb() {
		make_json_result('1', 'ok', []);
	}	
	
}
?>