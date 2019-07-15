<?php
require_once(APP_PATH."/include/libs.php");

class interfacesApi extends cls_base
{
	protected $db;
	
	public function init()
	{
		global $_DB;
		$this->db = $_DB;
	}

	public function getHid(){
		$appid = get_data('appid');
		$hid = $this->db->get_one("select id_hospital from irpt_interface where appId = '{$appid}'");
		make_json_result($hid, 'ok', []);
	}

}

?>