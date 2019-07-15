<?php //插件技术demo演示

	function admin_plugin_demo($_TEMPLATE){
		global $_DB;
		$p1 = get_data("p1");
		$_TEMPLATE->assign("p1",$p1);
		
		//$user = $_DB->get_row("select * from admin_user where `name`='admin' ");
		//print_r($user);
	}

?>