<?php

class personal_xcx extends cls_base{
	

	
	// init
	
	function init() {

		
		require_once(APP_PATH."/include/libs.php");
	
		
	}
	function introduce(){
		$type = get_data('types');//妇科、男科
		$name = get_data('name');
		$file = 'http://'.$_SERVER['SERVER_NAME'].'/data/'.$type.'/'.$name.'.txt';//文件名称
		$cbody = file_get_contents($file);
		return $cbody;
	}
	
	
	
	
	
	
	
	
	
	
	

}