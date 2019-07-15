<?php
require_once(APP_PATH."/include/libs.php");
/**
 * 接口管理
 +----------------------------------------------------------
 * @author 围剿
 * Time:2011-11-11
 +----------------------------------------------------------
 */
class interfaces extends cls_base
{
	
	function init()
	{
		require_once(ROOT_PATH."_ext/auth_admin.php");
		require_once(ROOT_PATH."_ext/page.class.php");
	}
	
	function interface_list(){
		global $_DB,$_TEMPLATE;
		
		$hid = get_hid();
		$sql = "select top_id from irpt_hospital where id='{$hid}';";
		$top_id = $_DB->get_one($sql);
		
		if($top_id==0){
			make_json_result("请选择科室！", '', []);
			exit;
		}
		if(empty(get_aid())){
			make_json_result("请选择客服类型！", '', []);
			exit;
		}
		
		make_json_result($_TEMPLATE->fetch("interfaces/interface_list.html"),1,array());
		
	}
	
	
	//接口列表
	function interface_action(){
		global $_DB,$_TEMPLATE;
		$hid = get_hid();
		$aid = get_aid();
		$names = trim(get_data('names'));
		
		$w = "";
		if($names!=''){
			$w .= " and name like '%{$names}%' ";
		}
		$count = $_DB->get_one("select count(*) from irpt_interface where id_hospital='{$hid}' and aid='{$aid}' $w ;");
		$p = new Page($count,20);  //分页初始化
		
		$sql = "select * from irpt_interface where id_hospital='{$hid}' and aid='{$aid}' $w LIMIT ".$p->firstRow.",".$p->listRows.";";
		$all = $_DB->get_all($sql);
		
		$_TEMPLATE->assign('all',$all);
		$_TEMPLATE->assign('page',$p->show('interface_action'));
		
		make_json_result($_TEMPLATE->fetch("interfaces/interface_action.html"),1,array());
	}
	
	
	//添加接口
	function interface_add(){
		global $_TEMPLATE;
		make_json_result($_TEMPLATE->fetch("interfaces/interface_add.html"),1,array());
	}
	
	
	function interface_add_action(){
		global $_TEMPLATE,$_DB;
		
		$hid = get_hid();
		$aid = get_aid();
		$account = trim(get_data('account'));
		$password = trim(get_data('password'));
		$name = trim(get_data('name'));
		$type = get_data('type');
		$appid = trim(get_data('appId'));
		$token = trim(get_data('token'));
		$ToUserName = trim(get_data('ToUserName'));
		
		$sql = "select * from irpt_interface where (account='{$account}' or name='{$name}') and id_hospital='{$hid}';";
		$row = $_DB->get_row($sql);
		
		if($row){
			make_json_result('该接口已经存在！',0,array());
		}else{
			$sql = "insert into irpt_interface(`id_hospital`,`account`,`password`,`name`,`type`,`add_time`,`appId`,`token`,`ToUserName`,`aid`) values('{$hid}','{$account}','{$password}','{$name}','{$type}','".time()."','{$appid}','{$token}','{$ToUserName}','{$aid}');";
			if($_DB->query($sql)){
				make_json_result('添加成功！',1,array());
			}else{
				make_json_result('添加失败！',0,array());
			}
		}
	}
	
	
	//修改接口
	function interface_modify(){
		global $_DB,$_TEMPLATE;
		$id = get_data('id');
		
		$sql = "select * from irpt_interface where id='{$id}';";
		$row = $_DB->get_row($sql);
		
		$_TEMPLATE->assign('row',$row);
		make_json_result($_TEMPLATE->fetch("interfaces/interface_modify.html"),1,array());
	}
	
	function interface_modify_action(){
		global $_DB,$_TEMPLATE;
		
		$id = get_data('id');
		$name = trim(get_data('name'));
		$account = trim(get_data('account'));
		$password = trim(get_data('password'));
		$appid = trim(get_data('appId'));
		$token = trim(get_data('token'));
		$type = get_data('type');
		$ToUserName = trim(get_data('ToUserName'));
		
		$sql = "update irpt_interface set name='{$name}',account='{$account}',password='{$password}',type='{$type}',appId='{$appid}',token='{$token}',ToUserName='{$ToUserName}' where id='{$id}';";
		$_DB->query($sql);
		
		make_json_result('',1,array());
	}
	
	
	//删除接口
	function interface_delete(){
		global $_DB,$_TEMPLATE;
		$id = get_data('id');
		
		$sql = "delete from irpt_interface where id='{$id}';";
		$_DB->query($sql);
		
		make_json_result('',1,array());	
	}
	
	
	
}

?>