<?php

if(!defined('IN_APP'))
	die('Hacking attempt');

/**
 * admin:userright
 * */
require_once(APP_PATH . '/include/lib_common.php');
require_once(APP_PATH . '/include/lib_userright.php');

class userright extends cls_base {

	// init
	function init() {
		parent::init();
		init_session();
		require_once(ROOT_PATH . "_ext/auth_admin.php");
		require_once(ROOT_PATH."_ext/page.class.php");
	}

	//admin:userright:index，默认进入模块的动作
	function index() {
		//index.
	}

	//admin:userright:show
	function show() {
		// 获取角色
		$roleData = $this->db->get_all("select id,name from admin_role ");
		// 获取医院
		$hospitalData = $this->db->get_all("select id,name from irpt_hospital");
		$this->template->assign("roleData",$roleData);
		$this->template->assign("hospitalData",$hospitalData);
		//$this->template->display("userright/show.htm");
		make_json_result($this->template->fetch("userright/show.htm"),1,array());
	}

	//admin:userright:ajax_add_user
	function ajax_add_user() {
		$this->template->assign('currentip',real_ip());
		make_json_result($this->template->fetch("userright/ajax/add_user.htm"),'',array());
	}
	
	function ajax_add_user_action() {
		$user_name = trim(get_data('user_name'));
		$user_email = trim(get_data('user_email'));
		$login_name = trim(get_data('login_name'));
		$user_ip = get_data('user_ip');
		$pwd = get_data('pwd');				$type = get_data('type');		
		$addTime = time();	

		$user_name = empty($user_name) ? $login_name : $user_name;
		$usercount = 0;
		$usercount = $this->db->get_one("select count(*) from admin_user where login_name='$login_name'");
		if($usercount>0) {
			make_json_result('','',array("result"=>0));
		}
		else {
			$pwds = md5($pwd);
			$sql = "insert into admin_user(`type`,`name`,`login_name`,`login_passwd`,`email`,`ip`,`create_user_id`,`create_user_name`,`add_time`) values('{$type}','{$user_name}','{$login_name}','{$pwds}','{$user_email}','{$user_ip}','{$_SESSION['admin_id']}','{$_SESSION['admin_name']}','{$addTime}')";
			$this->db->query($sql);
			$id = $this->db->get_one('SELECT @@identity');
			make_json_result('','',array("result"=>$id));
		}
	}

	// 优化超级管理员不能被删掉
	function ajax_show_user() {
		// 判断是不是管理员
		$pageSize = 25;
		$count = count(getListUserRight('all',0,0));
		$p = new Page($count,$pageSize);  //分页初始化
		$data = getListUserRight('all',0,0,'',$p->firstRow,$p->listRows);
		$this->template->assign("data",$data);
		$this->template->assign("count",$count);
		$p->setConfig('header','个用户');  //分页样式定制
		$p->setConfig('prev', '上一组');
		$p->setConfig('next','下一组');
		$p->setRoll(0);
		$this->template->assign('page',$p->show('get_user_all')); //输出分页
		$this->template->assign("sys_admin_id",SYS_ADMIN_ID?SYS_ADMIN_ID:0);
		make_json_result($this->template->fetch("userright/ajax/show_user.htm"),'',array());
	}

	// 优化有子级，不能删除。
	function ajax_delete_user() {
		$id = get_data("id","i");
		$user_rs = $this->db->get_row("select * from admin_user where id={$id}");
		if($id<1||!$user_rs) {
			make_json_result('','',array("result"=>0));
		} else {
			// 判断该账户是否有下级
			$count = $this->db->get_one("select count(*) from admin_user where create_user_id={$id}");
			if($count>0) {
				make_json_result('','',array("result"=>-1));
				exit;
			}
			// 删除帐号
			$this->db->query("delete from admin_user where id={$id}");
			// 删除帐号与角色的信息
			$this->db->query("delete from admin_user_role where user_id={$id}");
			// 删除帐号与医院的信息
			$this->db->query("delete from irpt_hospital_admin where id_admin={$id}");

			make_json_result('','',array("result"=>1));
		}
	}

	function ajax_get_user_rs() {
		$id = get_data("id","i");
		$user_rs = $this->db->get_row("select * from admin_user where id={$id}");
		if($id<1||!$user_rs) {
			make_json_result('','',array("result"=>0));
		} else {
			// 获取该用户与角色
			$user_role_rs = $this->db->get_all("select id,name from admin_role where id in (select role_id from admin_user_role where user_id={$id})");
			// 判断是不是管理员
			$role_id = $this->db->get_one("select role_id from admin_user_role where user_id=" . intval($_SESSION['admin_id']));
			if($role_id==SYS_ROLE_ID) {
				$role_all_rs = $this->db->get_all("select id,name from admin_role where id not in (select role_id from admin_user_role where user_id={$id})");
			} else {
				$role_all_rs = $this->db->get_all("select id,name from admin_role where id in (select role_id from admin_user_role where user_id=" . intval($_SESSION['admin_id']) . " and role_id not in (select role_id from admin_user_role where user_id={$id}))");
			}
			// 获取该用户医院
			$user_hospital_rs = $this->db->get_all("select id as hospital_id,name from irpt_hospital where id in (select id_hospital from irpt_hospital_admin where id_admin={$id} and is_delete!=1)");
			if($role_id==SYS_ROLE_ID) {
				$hospital_all_rs = $this->db->get_all("select id as hospital_id,name from irpt_hospital where id not in (select id_hospital from irpt_hospital_admin where id_admin={$id} and is_delete!=1)");
			} else {
				$hospital_all_rs = $this->db->get_all("select id as hospital_id,name from irpt_hospital where id in (select id_hospital from irpt_hospital_admin where id_admin=" . intval($_SESSION['admin_id']) . " and is_delete!=1 and id_hospital not in (select id_hospital from irpt_hospital_admin where id_admin={$id} and is_delete!=1))");
			}
		}
		$this->template->assign('user_role_rs',$user_role_rs);
		$this->template->assign('role_all_rs',$role_all_rs);
		$this->template->assign('user_hospital_rs',$user_hospital_rs);
		$this->template->assign('hospital_all_rs',$hospital_all_rs);
		$this->template->assign('currentip',real_ip());
		$this->template->assign("user_rs",$user_rs);
		make_json_result($this->template->fetch("userright/ajax/get_user_rs.htm"),'',array());
	}

	// 修改用户信息
	function ajax_update_user_action() {
		global $_CFG;
		$id = get_data("id","i");
		$user_rs = $this->db->get_row("select * from {$_CFG['prefix']}admin_user where id={$id}");
		if($id<1||!$user_rs) {
			make_json_result('','',array("result"=>0));
		}
		else {
			$user_name = trim(get_data("user_name"));
			$email = trim(get_data("email"));
			$ip = trim(get_data("ip"));
			$this->db->query("update {$_CFG['prefix']}admin_user set name='{$user_name}',email='{$email}',ip='{$ip}' where id={$id}");
			make_json_result('','',array("result"=>1));
		}
	}

	// 修改用户密码
	function ajax_update_user_password() {
		$id = get_data("id","i");
		$user_rs = $this->db->get_row("select * from admin_user where id={$id}");
		if($id<1||!$user_rs) {
			make_json_result('','',array("result"=>0));
		}
		else {
			$password = get_data("newpwd");
			$password = md5($password);
			$this->db->query("update admin_user set login_passwd='{$password}' where id={$id}");
			make_json_result('','',array("result"=>1));
		}
	}

	// 保存用户角色
	function save_user_role_data() {
		$user_id = get_data('id');
		$ids = get_data('ids');
		$idArray = PHP_VERSION>5.3 ? preg_split("/ /",$ids) : @split(' ',$ids);
		// 去掉数组key=0的部分
		array_shift($idArray);
		// 找到上一级的角色数组
		// 此处是为了处理子角色超过父角色的情况
		// 如果是超级管理员的话就不需要下面的情况
		$create_user_id = $this->db->get_one("select create_user_id from admin_user where id={$user_id}");
		$create_role_id = $this->db->get_one("select role_id from admin_user_role where user_id={$create_user_id}");
		if($create_user_id!=0 && SYS_ADMIN_ID != $create_user_id && SYS_ROLE_ID != $create_role_id) {
			$create_role_data = $this->db->get_col("select role_id from admin_user_role where user_id={$create_user_id}");
			$idArray = $create_role_data ? array_intersect($create_role_data,$idArray) : $idArray;
		}
		$sql = "select role_id from admin_user_role where user_id={$user_id}";
		$now_data = $this->db->get_col($sql);
		// 删除已经存在的部分
		$deleteArray = $now_data ? array_diff($now_data,$idArray) : array();
		$roleWhere = "";
		if(!empty($deleteArray)) {
			$or = "";
			foreach($deleteArray as $aid) {
				if(!empty($aid)) {
					$roleWhere .= "{$or} role_id='{$aid}'";
				}
				$or = "or";
			}
			$roleWhere = !empty($roleWhere) ? " and ($roleWhere)" : " and 1>1 ";
			$sql = "delete from admin_user_role where user_id={$user_id}{$roleWhere}";
			$this->db->query($sql);
		}

		// 添加部分
		$addArray = $idArray ? array_diff($idArray,$now_data) : array();
		if(!empty($addArray)) {
			$or = "";
			$sql = "insert into admin_user_role(user_id,role_id) values";
			foreach($addArray as $aid) {
				if(!empty($aid)) {
					$sql .= "$or('{$user_id}','{$aid}')";
					$or = ",";
				}
			}
			$this->db->query($sql);
		}

		make_json_result('','',array("result"=>1));
	}

	// 保存用户与医院信息
	function save_user_hospital_data() {
		$user_id = get_data('id');
		$ids = get_data('ids');
		$idArray = PHP_VERSION>5.3 ? preg_split("/ /",$ids) : @split(' ',$ids);
		// 去掉key=0的部分
		array_shift($idArray);
		// 找到上一级的角色数组
		// 此处是为了处理子角色超过父角色的情况
		// 如果是超级管理员的话就不需要下面的情况
		$create_user_id = $this->db->get_one("select create_user_id from admin_user where id={$user_id}");
		$create_role_id = $this->db->get_one("select role_id from admin_user_role where user_id={$create_user_id}");
		if($create_user_id!=0 && SYS_ADMIN_ID!=$create_user_id && SYS_ROLE_ID!=$create_role_id) {
			$create_hospital_data = $this->db->get_col("select id_hospital from irpt_hospital_admin where id_admin={$create_user_id} and is_delete!=1");
			$idArray = $create_hospital_data ? array_intersect($create_hospital_data,$idArray) : array();
		}
		$sql = "select id_hospital from irpt_hospital_admin where id_admin={$user_id}";
		$now_data = $this->db->get_col($sql);
		// 删除已经存在的部分
		$deleteArray = $now_data ? array_diff($now_data,$idArray) : array();
		$hospitalWhere = "";
		if(!empty($deleteArray)) {
			$or = "";
			foreach($deleteArray as $aid) {
				if(!empty($aid)) {
					$hospitalWhere .= "{$or} id_hospital='{$aid}'";
				}
				$or = "or";
			}
			$hospitalWhere = !empty($hospitalWhere) ? " and ($hospitalWhere)" : " and 1>1 ";
			$sql = "delete from irpt_hospital_admin where id_admin={$user_id}{$hospitalWhere}";
			$this->db->query($sql);
		}

		// 添加
		$addArray = $idArray ? array_diff($idArray,$now_data) : array();
		if(!empty($addArray)) {
			foreach($addArray as $aid) {
				if(!empty($aid)) {
					$sql = "insert into irpt_hospital_admin (id_admin,id_hospital) values('{$user_id}','{$aid}')";
					$this->db->query($sql);
					$add = $this->db->get_one("select @@identity");
					if($add>0) {
						$hospital_rs = $this->db->get_row("select id,name from irpt_hospital where id='{$aid}'");
						$user_rs = $this->db->get_row("select * from admin_user where id='{$user_id}'");
						$this->db->query("update irpt_hospital_admin set hospital_name='{$hospital_rs['name']}',admin_name='{$user_rs['name']}' where id={$add}");
					}
				}
			}
		}

		make_json_result('','',array("result"=>1));
	}

	function ajax_update_user_status() {
		$id = get_data("id","i");
		$is_delete = get_data("is_delete","i");
		$sql = "update admin_user set is_delete={$is_delete} where id={$id}";
		$this->db->query($sql);
		make_json_result('','',array("result"=>1));
	}




	// 增加在账户管理权限通过角色查用户，通过医院查用户
	function ajaxSearchListUserRight()
	{
		$roleId = get_data("roleId","i");
		$hospitalId = get_data("hospitalId","i");
		$userName = trim(get_data("userName","s"));
		$pageSize = 25;
		$count = count(getListUserRight('search',$roleId,$hospitalId,$userName,0,0));
		$count = (int) $count;
		$p = new Page($count,$pageSize);  //分页初始化
		$data = getListUserRight('search',$roleId,$hospitalId,$userName,$p->firstRow,$p->listRows);
//		print_r($data);
		$this->template->assign("data",$data);
		$this->template->assign("count",$count);
		$p->setConfig('header','个用户');  //分页样式定制
		$p->setConfig('prev', '上一组');
		$p->setConfig('next','下一组');
		$p->setRoll(0);
		$this->template->assign('hospitalId',$hospitalId);
		$this->template->assign('page',$p->show('searchListUserRight')); //输出分页
		$this->template->assign("data",$data);
		$this->template->assign("pg",!empty($_GET['p'])?$_GET['p']:1);
		$this->template->assign("sys_admin_id",SYS_ADMIN_ID?SYS_ADMIN_ID:0);
		make_json_result($this->template->fetch("userright/ajax/show_user.htm"),'',array());
	}

	function ajax_update_user_hospital_status() {
		$userId = get_data("user_id","i");
		$hospitalId = get_data("hospital_id","i");
		$isDelete = get_data("is_delete","i");
		$this->db->query("update `irpt_hospital_admin` set is_delete={$isDelete} where `id_admin`={$userId} and `id_hospital`={$hospitalId}");

		make_json_result('','',array("result"=>1));
	}
										
}

?>