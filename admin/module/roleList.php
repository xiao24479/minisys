<?php
class roleList extends cls_base
{
	var $log;

	function init()
	{
		require_once(ROOT_PATH."_ext/auth_admin.php");
         
	}
	//主框架的页面显示
	function index(){
	}
	
	function show_role(){
		global $_TEMPLATE,$_DB;
		
		$sql = 'select id,name,comment,available from '.$_CFG['prefix'].'admin_role order by id desc';
		$data = $_DB->get_all($sql);
		//deal 
		foreach($data as & $r){
		  if($r['available']!="1"){
		     $r['mess']="(<font color=red>不可用</font>)";
		  }
		}
		$_TEMPLATE->assign('data',$data);
		make_json_result($_TEMPLATE->fetch("rolelist/roleList.html"),1,array());
		//$_TEMPLATE->display("rolelist/roleList.html");
	}
	//具体
	function role_detail(){
		global $_TEMPLATE,$_DB,$_CFG;
		$role_id = get_data('role_id','i');	
				
		//get the role detail.
		$sql = 'select * from '.$_CFG['prefix'].'admin_role where id='.$role_id;
		$role_info = $_DB->get_row($sql);
		$_TEMPLATE->assign('role_info',$role_info);
		
		//return the page by json.
		make_json_result($_TEMPLATE->fetch("rolelist/role_detail.html"), '', array());
	}
	//所有角色
	function role_all(){
		global $_TEMPLATE,$_DB,$_CFG;
		//GET all role.
		$sql = 'select id,name,comment,available from '.$_CFG['prefix'].'admin_role order by id desc';
		$data = $_DB->get_all($sql);
		//deal 
		foreach($data as & $r){
		  if($r['available']!="1"){
		     $r['mess']="(<font color=red>不可用</font>)";
		  }
		}
		$_TEMPLATE->assign('data',$data);
		make_json_result($_TEMPLATE->fetch("rolelist/role_all.html"), '', array());
	}
	//
	function role_delete(){
		global $_DB,$_CFG;
	//	echo "del";
	//	exit;
		$role_id = get_data('role_id','i');	
		$sql = 'select * from '.$_CFG['prefix'].'admin_user_role where role_id='.$role_id;
		$userrole=$_DB->get_all($sql);
		//
		if($userrole){
		    make_json_result('', '0', array());
		}else{//delete 
		    //delete the role.
			$sql ='delete from '.$_CFG['prefix'].'admin_role where id ='.$role_id;
		    $_DB->query($sql);
		    //delete the rights of role
		    $sql = 'delete from '.$_CFG['prefix'].'admin_role_rights where role_id='.$role_id;
		    $_DB->query($sql);
			// delete the user of role
		    $sql = 'delete from '.$_CFG['prefix'].'admin_user_role where role_id='.$role_id;
		    $_DB->query($sql);
			make_json_result('', 'ok', array());
		}
		
	}
	// update the detail role
	function role_detail_save(){
		global $_DB,$_CFG;
		//get the parameter
		$role_id  = get_data('role_id','i');
		$role_name= get_data('rname');
		$desc     = get_data('desc');
		$project  = get_data('project');
		$state    = get_data("state","i");
		//hidden
		$role_    = get_data('rolename');
		$pro_     = get_data('pro');
		//role exits
		if($role_name != $role_ || $project != $pro_){
		    $has =$_DB->get_row("select * from ".$_CFG['prefix']."admin_role where name ='$role_name' and project='$project'");
			if($has){
			   make_json_result('', 'has', array());
			}
		}
		
		$sql = "update ".$_CFG['prefix']."admin_role set name='$role_name',comment='$desc',available='$state',project='$project' where id=$role_id";
		$_DB->query($sql);
		
		make_json_result('', 'ok', array());
	}
	//添加
	function role_add(){
		global $_TEMPLATE;
		make_json_result($_TEMPLATE->fetch("rolelist/role_add.html"), '', array());
	}
	//添加action
	function role_add_action(){
		global $_DB,$_CFG;
        //get the parameter 
		$role_name   = get_data('role_name');
		$desc        = get_data('desc');
		$state       = get_data('state');
		$project     = get_data('project');
		
		$rolecount=0;
		$rolecount = $_DB->get_one("select count(*) from ".$_CFG['prefix']."admin_role where name ='$role_name' and project ='$project'");
		if($rolecount>0){
			make_json_result('', '0', array());//返回
		}else{ //add the data.
			$sql = "insert into `".$_CFG['prefix']."admin_role`(`name`,`comment`,`available`,`project`)  values('$role_name','$desc','$state','$project')";
			$_DB->query($sql);
			$id = $_DB->get_one('SELECT @@session.identity');//取该id
			make_json_result('', "$id", array());
		}
		
		
		
		
	
	
	}

}

?>