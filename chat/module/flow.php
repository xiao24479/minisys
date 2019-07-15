<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");	

class flow extends cls_base {
	
	var $db;
	var $template;
	
	public function init() {
		
		global $_DB, $_TEMPLATE;
		
		$this->db = $_DB;
		$this->template = $_TEMPLATE;
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid()); 
	}
	

	public function index() {
		$hid = get_hid();
		$date=get_data('date');
		
		if(empty($date)){
			$add_time=date('Y/m/01',time());
			$end_time=date('Y/m/d',time());
		}else{
			$arr = explode('-', $date);
			$add_time = trim($arr[0]);
			$end_time = trim($arr[1]);
		}
		
		
		$w = " hid = {$hid} and access_time > ".strtotime($add_time.'00:00:00'). " and access_time < ".strtotime($end_time.' 23:59:59');
		
		$sql = "select count(*) as count from gossip_flow where {$w} group by name";
		$data = $this->db->get_all($sql);
		$page = new Page(count($data), 20);
			
		$sql = "select *, count(*) as count from gossip_flow where {$w} group by name order by count desc limit ".$page->firstRow.','.$page->listRows;
		$all = $this->db->get_all($sql);
		
		$sql = "select sum(count) from (select count(*) as count from gossip_flow where {$w} group by name) as t";
		$total = $this->db->get_one($sql);
		
		$this->template->assign('page', $page->show('index'));
		$this->template->assign('all', $all);	
		$this->template->assign('total', $total);	
		$this->template->assign('add_time', $add_time);
		$this->template->assign('end_time', $end_time);
		make_json_result($this->template->fetch('flow/index.html'), '', []);
	}


	
}

?>