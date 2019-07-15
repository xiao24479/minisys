<?php
require_once(APP_PATH."/include/libs.php");

class flowApi extends cls_base {
	
	var $db;
	
	function init() {
		
		global $_DB;
		
		$this->db = $_DB;
		
	}
	
	function access() {
		$name = trim(get_data('name'));
		$event = trim(get_data('event'));
		$access_time = time();
		
		$sql = "select id_hospital from irpt_interface where name = '{$name}'";
		$hid = $this->db->get_one($sql);
		
		$sql = "insert into gossip_flow(name, hid, event, access_time) values('{$name}', '{$hid}', '{$event}', '{$access_time}')";
		$res = $this->db->query($sql);
		
		if($res){
			echo json_encode(['result'=>$res, 'msg'=>'访问已记录']);
		}else{
			echo json_encode(['result'=>$res, 'msg'=>'访问记录失败']);
		}
	}
	
	
	function open_count() {
		$appid = get_data('appid');
		$event = trim(get_data('event'));
		$access_time = time();

		$row = $this->db->get_row("select name, id_hospital from irpt_interface where appId = '{$appid}'");
		
		$hid = $row['id_hospital'];
		$name = $row['name'];

		$sql = "insert into gossip_flow(name, hid, event, access_time) values('{$name}', '{$hid}', '{$event}', '{$access_time}')";
		$res = $this->db->query($sql);
		
		if($res){
			echo json_encode(['result'=>$res, 'msg'=>'访问已记录']);
		}else{
			echo json_encode(['result'=>$res, 'msg'=>'访问记录失败']);
		}

	}
	
}

?>