<?php
require_once(APP_PATH."/include/libs.php");
class interfaceApi extends cls_base {
	var $db;
	
	var $template;
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;
	}
	
	function get_secret() {
		$appid = get_data('appid');
		$sql = "select token from irpt_interface where appId = '{$appid}'";
		$secret = $this->db->get_one($sql);
		echo json_encode(['secret' => $secret]);
	}
	
}
?>