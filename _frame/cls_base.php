<?php

//所有模块的父类
class cls_base
{
	//初始化方法
	function init()
	{
		global $_DB,$_TEMPLATE;
		$this->db = $_DB;
		$this->template = $_TEMPLATE;
	}

	function index()
	{
		echo 'Not define index page';
	}

	//销毁方法
	function destroy()
	{}

	//处理错误的方法
	function error($msg)
	{
		die($msg);
	}
}

?>