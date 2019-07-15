<?php



require_once(APP_PATH."/include/lib_rights.php");

class rights extends cls_base

{

	var $log;







	function init()

	{

		init_session();

	}

	//主框架的页面显示

	function index(){

	     global $_TEMPLATE;

         $_TEMPLATE->display('admin/index.html');

	}

	//修改密码

	function password_change(){

	    global $_TEMPLATE;

        $_TEMPLATE->assign('admin_id',$_SESSION['admin_id']);

		$_TEMPLATE->assign('admin_name',$_SESSION['admin_name']);
		
		make_json_result($_TEMPLATE->fetch("password/show.html"),1,array());
		

	}

	/**

	 * 将密码规则修改，限制了只能有数字和英文组成的密码

	 +----------------------------------------------------------

	 * Time:2012-1-9 

	 +----------------------------------------------------------

	 */

	function pwd_change_action(){


	  global $_DB,$db_admin_prefix;

	  $id=get_data('id','i'); 

	  $new_pwd =get_data('new_pwd');	  

	  if(strlen($new_pwd)<=5){

		make_json_result('', 'no', array());  

	  }  


	  if(preg_match("/^[A-Za-z]+$/",$new_pwd)){  

	 	 make_json_result('', 'no', array());  

	  } 

	  if(preg_match("/^[0-9]+$/",$new_pwd)){  

	 	 make_json_result('', 'no', array());  

	  } 

	  if(preg_match("/^[a-zA-Z0-9]+$/",$new_pwd)){  

	     $_DB->query("update ".$db_admin_prefix."admin_user set login_passwd='".md5($new_pwd)."' where id='$id'");
	 	 make_json_result('', 'ok', array());  

	  } 

	  
	


     

   

	}

	function export(){

		global $_DB,$_TEMPLATE,$db_admin_prefix;

		

		//get all of the project.

		$sql = "select project from ".$db_admin_prefix."admin_rights where project is not null and project<>'' group by project;";

		$data = $_DB->get_all($sql);

		$_TEMPLATE->assign('projects',$data);
		
		make_json_result($_TEMPLATE->fetch("export/export.html"), 'ok', array());  		
	}

	//export the menu.

	function export_action(){

		 global $_DB,$_TEMPLATE,$db_admin_prefix;

		

		//get the par.

		$project = get_data('project');

		$prex = get_data('prex');

		$_TEMPLATE->assign('project',$project);

		$_TEMPLATE->assign('prex',$prex);

		

		

		//get all of the project.

		$sql = "select project from ".$db_admin_prefix."admin_rights where project is not null and project<>'' group by project;";

		$data = $_DB->get_all($sql);

		$_TEMPLATE->assign('projects',$data);

		

		

		$sql = 'select name,url,comment,available,sort,project,code,father_code from '.$db_admin_prefix.'admin_rights';

		if($project!='0'){

			$sql .= " where project='$project' order by  father_code";

		}

		

		//echo $sql;

		$data = $_DB->get_all($sql);

		

	//	print_r($data);

		$_TEMPLATE->assign('data',$data);

		$_TEMPLATE->assign('action',true);

		$_TEMPLATE->display("export/export.html");

	}

	//execute sql

	function execute(){

		 global $_TEMPLATE;

		$_TEMPLATE->display("export/execute.html");

	}

	function execute_action(){

	   global $_DB;

	   $textarea=stripslashes(stripslashes(get_data("sqlscript")));

	   $rus = get_data('rus');

	   $r='';

	   

	   if($rus)

	   	$r=$_DB->get_all($textarea);

	   else

	   	$r = $_DB->query($textarea);

	   if($r)

	   	print_r($r);

	   else

	   echo "脚本运行完成！";



	}
	
	function admin_info(){
		global $_TEMPLATE,$_DB;
		$uid = $_SESSION['admin_id'];
		$sql = "select name,login_name from admin_user where id='{$uid}';";
		$user = $_DB->get_row($sql);
		$_TEMPLATE->assign('user',$user);
		make_json_result($_TEMPLATE->fetch("password/user_info.html"),1,array());
		
	}


}



?>
