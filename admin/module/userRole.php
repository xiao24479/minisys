<?php

class userRole extends cls_base

{

	var $log;



	function init()

	{

		init_session();

		require_once(ROOT_PATH."_ext/auth_admin.php");

		require_once(ROOT_PATH."_ext/page.class.php");

	}

	//主框架的页面显示

	function index(){



	}

	function selectUserName()

	{

		global $_TEMPLATE,$_DB,$_CFG;

		$userName = trim(get_data('userName'));

		if($userName!=''){

			$sql = "SELECT * FROM ".$_CFG['prefix']."`admin_user` WHERE  `name` LIKE '".$userName."%' ";

			$data = $_DB->get_all($sql);

			$count = count($data);

			$p = new Page($count,25);  //分页初始化

			$sql = "SELECT * FROM ".$_CFG['prefix']."`admin_user` WHERE `name` LIKE '".$userName."%' order by id desc LIMIT ".$p->firstRow.",".$p->listRows." ;";

			$data = $_DB->get_all($sql);

			$p->setConfig('header','个用户');  //分页样式定制

			$p->setConfig('prev', '上一组');

			$p->setConfig('next','下一组');

			$p->setRoll(0); //设定不显示页码

			$_TEMPLATE->assign('username',$userName);

			$_TEMPLATE->assign('page',$p->show('selectUserName')); //输出分页

			$_TEMPLATE->assign('data',$data);

			make_json_result($_TEMPLATE->fetch("userrole/user_all.html"), '', array());

		}

		else{

			$this->user_all();

		}



	}

	function showUserRole(){

		global $_TEMPLATE;

	//	$_TEMPLATE->display("userrole/user_role.html");
		make_json_result($_TEMPLATE->fetch("userrole/user_role.html"),1,array());

	}



		function user_detail(){

		global $_TEMPLATE,$_DB,$_CFG;

		$user_id=get_data('user_id');


		$user_rs = $_DB->get_row("select * from admin_user where id={$user_id}");
//		echo $user_id;



		//get the user detail.

		$sql = 'select * from '.$_CFG['prefix'].'admin_user where id='.$user_id;

		$user_info = $_DB->get_row($sql);



		//get the user roles.

		$sql = 'select name,id from '.$_CFG['prefix'].'admin_role where id in(select role_id from '.$_CFG['prefix'].'admin_user_role where user_id='.$user_id.')';

		$user_role = $_DB->get_all($sql);

		
		//get all of the roles.

		$sql = 'select name,id from '.$_CFG['prefix'].'admin_role where id not in(select role_id from '.$_CFG['prefix'].'admin_user_role where user_id='.$user_id.')';

		$roles = $_DB->get_all($sql);
		
		$role_id = $_DB->get_one("select role_id from admin_user_role where user_id=" . intval($_SESSION['admin_id']));
		// 获取该用户医院
		$user_hospital_rs = $_DB->get_all("select id as hospital_id,name from irpt_hospital where id in (select id_hospital from irpt_hospital_admin where id_admin={$user_id}) order by name asc;");
		//if($role_id==1) {
			$hospital_all_rs = $_DB->get_all("select id as hospital_id,name from irpt_hospital where id not in (select id_hospital from irpt_hospital_admin where id_admin={$user_id}) order by name asc;");
		//} else {
			//$hospital_all_rs = $_DB->get_all("select id as hospital_id,name from irpt_hospital where id in (select id_hospital from irpt_hospital_admin where id_admin=" . intval($_SESSION['admin_id']) . " and is_delete!=1 and id_hospital not in (select id_hospital from irpt_hospital_admin where id_admin={$user_id} and is_delete!=1)) order by name asc;");
		//}


		$_TEMPLATE->assign('user_hospital_rs',$user_hospital_rs);
		$_TEMPLATE->assign('hospital_all_rs',$hospital_all_rs);
		$_TEMPLATE->assign('user_rs',$user_rs);
		
		
		$_TEMPLATE->assign('currentip',real_ip());

		$_TEMPLATE->assign('roles',$roles);

		$_TEMPLATE->assign('user_role',$user_role);

		$_TEMPLATE->assign('user_info',$user_info);



		//return the page by json.

		make_json_result($_TEMPLATE->fetch("userrole/user_detail.html"), '', array());

	}



	function user_all(){

		global $_TEMPLATE,$_DB,$_CFG;

		// update by taozi.@2012.08.08

		$count = $_DB->get_one( "select count(*) from {$_CFG[ 'prefix' ]}admin_user " ) ;  //统计表记录

		$p = new Page($count,22);  //分页初始化

		$sql = 'select id,name,login_name,email,cell_phone,is_delete from '.$_CFG['prefix'].'admin_user order by id desc LIMIT '.$p->firstRow.",".$p->listRows." ;";

		$data = $_DB->get_all($sql);

		$p->setConfig('header','个用户');  //分页样式定制

		$p->setConfig('prev', '上一组');

		$p->setConfig('next','下一组');

		$p->setRoll(0);

		$_TEMPLATE->assign('page',$p->show('get_user_all')); //输出分页

		$_TEMPLATE->assign('data',$data);

		make_json_result($_TEMPLATE->fetch("userrole/user_all.html"), '111', array());

	}



	function user_delete(){

		global $_DB,$_CFG;

		$user_id=get_data('user_id');



		//delete the user.

		$sql ='delete from '.$_CFG['prefix'].'admin_user where id ='.$user_id;

		$_DB->query($sql);

		//delete the roles of the user.

		$sql = 'delete from '.$_CFG['prefix'].'admin_user_role where user_id='.$user_id;

		$_DB->query($sql);



		make_json_result('', 'ok', array());

	}



	function user_stop(){

		$user_id=get_data('user_id');

		//stop the user.



		make_json_result('', 'ok', array());

	}



	function roles_save(){

		$user_id=get_data('user_id');

		$ids=get_data('ids');

		$idarrary= PHP_VERSION>5.3 ? preg_split("/ /",$ids) : @split(' ',$ids);

		$this->adduserrole($user_id,$idarrary);

		make_json_result('', 'ok', array());

	}



	private function adduserrole($user_id,$idArray){

		global $_DB,$_CFG;

		//query all of the id.

		$sql = 'select role_id from '.$_CFG['prefix'].'admin_user_role where user_id='.$user_id;

		//echo sql;

		$now_data = $_DB->get_col($sql);





		//print_r($now_data);

	//	echo '=========';

		//print_r($idArray);

		$deleteArray = array_diff($now_data,$idArray);

	//

	//	echo '=========';

		//print_r($deleteArray);

		//delete the data.

		$roleids='1>1';

		foreach($deleteArray as $aid){

			$roleids .=' or role_id='.$aid;

		}



		$sql  = 'delete from '.$_CFG['prefix'].'admin_user_role where user_id='.$user_id.' and ('.$roleids.')';

			//echo $sql;

		$_DB->query($sql);



		$addArray = array_diff($idArray,$now_data);

		//echo '=========';

		//print_r($addArray);

		//add the data.

		foreach($addArray as $aid){

			if(trim($aid)!=''){

				$sql  = 'insert into '.$_CFG['prefix'].'admin_user_role(user_id,role_id) values('.$user_id.','.$aid.')';

			//	echo $sql;

				$_DB->query($sql);

			}

		}

	}

	function user_detail_save(){

		global $_DB,$_CFG;

		$user_id=get_data('user_id');

		$user_name=get_data('user_name');

		$user_email=get_data('user_email');

		$user_ip=get_data('user_ip');

		$sql = "update ".$_CFG['prefix']."admin_user set name='$user_name',email='$user_email',ip='$user_ip' where id=$user_id";

		$_DB->query($sql);



		make_json_result('', 'ok', array());

	}



	//chang

	function user_password_save(){

		global $_DB,$_CFG;

		$pwd=get_data('pwd');

		$user_id=get_data('user_id');

		if(trim($pwd)=='') die();

		$newpwd = md5($pwd);

		$sql = "update ".$_CFG['prefix']."admin_user set login_passwd='$newpwd' where id=$user_id";

		$_DB->query($sql);

		make_json_result('', 'ok', array());

	}

	function user_add(){

		global $_TEMPLATE;

		$_TEMPLATE->assign('currentip',real_ip());

		make_json_result($_TEMPLATE->fetch("userrole/user_add.html"), '', array());

	}

	function user_add_action(){

		global $_DB,$_CFG;

		//$user_id=get_data('user_id');

		$user_name=get_data('user_name');

		$user_email=get_data('user_email');

		$login_name=get_data('login_name');

		$user_ip=get_data('user_ip');

		$pwd=get_data('pwd');



		//change by 2009-02-20

		if($user_name==""){

		    $user_name=$login_name;

		}

		//check user login exits.

		$usercount=0;



		$usercount = $_DB->get_one("select count(*) from ".$_CFG['prefix']."admin_user where login_name='$login_name'");

		if($usercount>0){

			make_json_result('', '0', array());

		}else{ //add the data.

			//save the data.

			$pwds = md5($pwd);
			$addTime = time();

			$sql = "insert into ".$_CFG['prefix']."admin_user(`name`,login_name,login_passwd,email,ip,`create_user_id`,`create_user_name`,`add_time`)  values('$user_name','$login_name','$pwds','$user_email','$user_ip','{$_SESSION['admin_id']}','{$_SESSION['admin_name']}','{$addTime}')";



			$_DB->query($sql);

			$id = $_DB->get_one('SELECT @@session.identity');

			make_json_result('', "$id", array());

		}













	}



}



?>