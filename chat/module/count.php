<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/auth_admin.php");
require_once(ROOT_PATH."_ext/page.class.php");	
class count extends cls_base{
	
	var $db;

	var $template;

	// init

	function init() {
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
		
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid());
	}
	
	
	function area(){
		$this->template->assign('add_time',date('Y/m/01',time()));	
		$this->template->assign('end_time',date('Y/m/d', time()));
		make_json_result($this->template->fetch('count/area.html'), '', array());
	}
	
	
	function area_action(){
		$date = get_data('date');
		$hid=get_hid();
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
		
		$arr = explode('-', $date);
		$add_time = trim($arr[0]);
		$end_time = trim($arr[1]);
		
		$w = " hos_id=$hid and lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');

		$sql = "select id from $vistors_table_name where {$w} GROUP BY area";
		$all = $this->db->get_all($sql);
		
		if(!empty($all)){
			$count = count($all);
		}else{
			$count = 0;
		}
		
		$page = new Page($count, 20);
		
		$sql="SELECT region as area, COUNT( id ) as num  FROM $vistors_table_name where {$w} GROUP BY area ORDER BY `num` DESC "; //按更新时间查询
		$all = $this->db->get_all($sql);
		
		$this->template->assign('all',$all);	
		$this->template->assign('page', $page->show('query'));
		make_json_result($this->template->fetch('count/area_action.html'), '', array());
	}


	function qudao(){
		$this->template->assign('add_time',date('Y/m/01',time()));	
		$this->template->assign('end_time',date('Y/m/d', time()));
		make_json_result($this->template->fetch('count/qudao.html'), '', array());
	}
	
	
	function qudao_action(){
		$date = get_data('date');
		$hid=get_hid();
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
		
		$arr = explode('-', $date);
		$add_time = trim($arr[0]);
		$end_time = trim($arr[1]);

		$w = " hos_id=$hid and lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');

		$sql = "select id from $vistors_table_name where {$w} GROUP BY mold";
		$all = $this->db->get_all($sql);
		
		if(!empty($all)){
			$count = count($all);
		}else{
			$count = 0;
		}
		
		$page = new Page($count, 20);
		
		$sql="SELECT mold, COUNT( id ) as num  FROM $vistors_table_name where {$w} GROUP BY mold ORDER BY  `num` DESC limit ".$page->firstRow.','.$page->listRows; //按更新时间查询
		$all = $this->db->get_all($sql);
		
		$this->template->assign('all',$all);	
		$this->template->assign('page', $page->show('query'));
		make_json_result($this->template->fetch('count/qudao_action.html'), '', array());
	}
	
	
	function keys(){
		$this->template->assign('add_time',date('Y/m/01', time()));	
		$this->template->assign('end_time',date('Y/m/d', time()));	
		make_json_result($this->template->fetch('count/keys.html'), '', array());
	}
	
	
	function keys_action(){
		$date = get_data('date');
		$hid=get_hid();
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
		
		$arr = explode('-', $date);
		$add_time = trim($arr[0]);
		$end_time = trim($arr[1]);
		
		$w = " hos_id=$hid and lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');
		
		$sql = "select id from $vistors_table_name where {$w} GROUP BY symbol";
		$all = $this->db->get_all($sql);
		
		if(!empty($all)){
			$count = count($all);
		}else{
			$count = 0;
		}
		
		$page = new Page($count, 20);
		
		$sql="SELECT symbol, COUNT( id ) as num  FROM $vistors_table_name where {$w}  GROUP BY symbol ORDER BY  `num` DESC limit ".$page->firstRow.','.$page->listRows; //按更新时间查询
		$all = $this->db->get_all($sql);
		
		$this->template->assign('all',$all);	
		$this->template->assign('page', $page->show('query'));
		make_json_result($this->template->fetch('count/keys_action.html'), '', array());
		
		
	}
}