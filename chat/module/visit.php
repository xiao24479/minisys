<?php
/**
 * 模拟对话
 */
class visit extends cls_base{

	public function  init(){
			require_once(APP_PATH."/include/libs.php");
			require_once(ROOT_PATH."_ext/auth_admin.php");
			require_once(ROOT_PATH."_ext/page.class.php");	
			cuckhid(get_hid());
			cuckaid(get_hid(),get_aid());
	}
	
	function dialogue(){

		global $_TEMPLATE;


	
		$_TEMPLATE->assign('url','/chat/main.php?m=visit&a=chatbotAuto&j=1');
		
		$_TEMPLATE->assign('web_url','/chat/main.php?m=visit&a=chatbotAuto&j=1');
		
		$_TEMPLATE->display('visit/dialogue.html');
		
	}
	
	
	/**
	 * 推送机器人
	 */
	function chatbotAuto(){
	
		$msg = get_data('msg');		
		
		
		make_json_result($msg,1, array());
	
	
	}
	
	
	
	
	


}