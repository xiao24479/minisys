<?php

class check extends cls_base{
	public function  init(){
			require_once(ROOT_PATH."_ext/auth_admin.php");
			require_once(ROOT_PATH."_ext/page.class.php");	
	}
	/*效验索引日记*/
	public  function check_log(){
		global $_TEMPLATE ;
		$_TEMPLATE->assign('day',date('Y-m-d',strtotime("-1 day")));	
		$_TEMPLATE->assign('day2',date('Y-m-d'));	
		$_TEMPLATE->display('check/check_log.html');
	
	}
	function check_log_action(){
		global $_TEMPLATE,$_DB;
		$dateday1 = strtotime(get_data('dateday1')." 00:00:00");
		$dateday2 = strtotime(get_data('dateday2')." 23:59:59");
		$sql = "SELECT count(id) as id  FROM  `gossip_check`  WHERE add_time BETWEEN $dateday1 and $dateday2 ";
		$count=$_DB->get_one($sql);
		$p = new Page($count,20);
		
		$sql = "SELECT * FROM  `gossip_check`  WHERE add_time BETWEEN $dateday1 and $dateday2  LIMIT ".$p->firstRow.",".$p->listRows." ;";
		
		$all = $_DB->get_all($sql);
		$_TEMPLATE->assign('all',$all);	 
		$_TEMPLATE->assign('page',$p->show('query'));	
		make_json_result($_TEMPLATE->fetch("check/check_log_action.html"), '', array());
		
	}
	
}