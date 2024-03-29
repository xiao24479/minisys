<?php
require_once(APP_PATH."/include/libs.php");
class surface extends cls_base{

	var $db;

	var $template;

	// init

	function init() {
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
	}
	
    /**
	 * 聊天访问页面，获得访客信息
	 */
	public function consult(){
		$hos_id=get_data('hospid');//医院id
		$access_id=get_data('accessid');//数据表id
		$mold=get_data('mold');//来源类型必传参数
		$camp=get_data('camp');//关键词
		
		$this->template->assign('accessid',$access_id);
		$this->template->assign('mold',$mold);
		$this->template->assign('hospid',$hos_id);
		$this->template->assign('camp',$camp);
		
		$this->template->display("surface/consult.htm");

	}

	
}
