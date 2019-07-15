<?php
/*
  后台常用函数
  以及处理操作数据库的全部放在这里
*/

global $db_admin_prefix,$_CFG;
$db_admin_prefix  = $_CFG['prefix'];

//deal role and user role
function insert_role_sql($adminid){
  global $_DB,$db_admin_prefix;
  $_DB->query("insert into `".$db_admin_prefix."admin_role` (`name`, `comment`, `available`) values('超级用户','系统超级用户','1')");
  $id=$_DB->get_one("select @@identity ");//get the insert id
  $_DB->query("insert into `".$db_admin_prefix."admin_user_role` (`user_id`, `role_id`) values('$adminid','$id')");
  return $id;
}
//得到admin  de role rights
function count_role_rights($adminid){
   global $_DB,$db_admin_prefix;
   return $_DB->get_one("select count(*) from ".$db_admin_prefix."admin_role_rights where role_id in(select role_id from  ".$db_admin_prefix."admin_user_role where user_id='$adminid')");
}
function get_admin_id(){
   global $_DB,$db_admin_prefix;
   return $_DB->get_one("select id from ".$db_admin_prefix."admin_user where login_name='admin'");
}
//
function count_father_menu(){
   global $_DB,$db_admin_prefix;
   return $_DB->get_one("select count(*) from ".$db_admin_prefix."admin_rights where code<>0");
}
function get_menu_info($id){
	global $_DB,$db_admin_prefix;
	return $_DB->get_all("select * from ".$db_admin_prefix."admin_rights where father_code='$id' order by sort ");

}
function get_role_rights($id,$rid){
   global $_DB,$db_admin_prefix;

   return $_DB->get_one("select rights_id from ".$db_admin_prefix."admin_role_rights where role_id='$rid' and rights_id='$id' ");
}
//判断权限,对每个角色和用户的权限。
function verify_login(){
		//echo $_SESSION['authnum'];
        global $_DB,$db_admin_prefix;
        //session_start();
		$case=array();
		if(isset($_SESSION['admin_id']) && $_SESSION['admin_id']!=""){

			//登录后判断权限

			//判断是不是admin
			if($_SESSION['admin_id']=="1"){//是admin
			   $case=array("can_add"=>"1","can_view"=>"1","can_edit"=>"1","can_delete"=>"1");
			}else{//不是admin
				//用户查$roleid
				$roleinfo=roleMess($_SESSION['admin_id']);
				$sql="select role_id,rights_id,can_add,can_edit,can_delete,can_view from ".$db_admin_prefix."admin_role_rights  where role_id in (select role_id from ".$db_admin_prefix."admin_user_role as aur right join  ".$db_admin_prefix."admin_role as ar on aur.role_id = ar.id where ar.available='1' and aur.user_id='{$roleinfo['user_id']}' and aur.role_id='".$roleinfo['role_id']."') and rights_id in (select id from ".$db_admin_prefix."admin_rights where available='1')";
				$cases=$_DB->get_all($sql);
				if(!empty($cases)){//有东西
				   $case=$cases;
				}else{//没有东西或没权限
				   $case=array("can_add"=>"0","can_view"=>"1","can_edit"=>"0","can_delete"=>"0");
				}
		   }
	    }

		return $case;

}
// 用户登录验证(name,password)
function checkuser($username, $password,&$errorcode) {
	global $_DB,$userinfo,$db_admin_prefix;
	$username = htmlspecialchars(trim($username));
	$username = trim($username);
	$sql  = "SELECT * FROM ".$db_admin_prefix."admin_user WHERE login_name='".$username."' AND login_passwd='".$password."'";
	//echo $db_admin_prefix;
	//exit;
	$userinfo = $_DB->get_row($sql);
	if (empty($userinfo)) {
		$errorcode=1;
		return false;
	} else {
		if($userinfo['is_delete']=='1'){
			$errorcode=1;
			return false;
		}

		//validate the ip.
		$ip=$userinfo['ip'];
		if($ip=='' || $ip==NULL){
			//cant login.
			$errorcode=2;
			return false;
		}else{
			//get the current ip.
			$currentip=real_ip();
			$configedip = PHP_VERSION>=5.3 ? preg_split('/ /',$ip) : @split(' ',$ip);

			if( $ip=='*' || in_array($currentip,$configedip)){
				//验证成功后获取角色信息，并根据角色信息来判断是否格外处理。
				gm_userprocess($userinfo);
				$errorcode=0;
				return $userinfo;
			}else{
				//cant login.
				$errorcode=3;
				return false;
			}
		}

	}
}


function gm_userprocess(&$userinfo){
	global $_DB,$db_admin_prefix;
	$userid = $userinfo['id'];
	$sql = "select  distinct(project) as project from ".$db_admin_prefix."admin_role where id in(select role_id from ".$db_admin_prefix."admin_user_role where user_id=$userid)";

	$projects = $_DB->get_col($sql);
	//print_r($projects);
	if(in_array('gm',$projects)){
		//get the sp user config.
		$sqls = "select * from ".$db_admin_prefix."admin_user_config where user_id = $userid limit 1 ";
		$data = $_DB->get_row($sqls);
		if($data){
			$userinfo['sp_id']=$data['sp_id'];
			$userinfo['sp_name']=$data['sp_name'];
			$userinfo['log_level']=$data['log_level'];
		}
	}
}

//菜单信息,通过用户id和菜单id
function menuInfo_($userid,$classid){
	 global $_DB,$db_admin_prefix;
	 return $_DB->get_all("select * from ".$db_admin_prefix."admin_rights where id in (select distinct(rights_id) from ".$db_admin_prefix."admin_role_rights  where role_id in (select r.role_id from ".$db_admin_prefix."admin_user_role as r right join ".$db_admin_prefix."admin_role as a on r.role_id=a.id where a.available='1' and r.user_id='$userid' )) and available='1' and father_code='$classid' order by sort ");

}

//菜单信息,通过用户id和菜单id
function menuInfo($userid,$classid){
	 global $_DB,$db_admin_prefix;
	 return $_DB->get_all("select * from ".$db_admin_prefix."admin_rights where id in (select distinct(rights_id) from ".$db_admin_prefix."admin_role_rights  where role_id in (select r.role_id from ".$db_admin_prefix."admin_user_role as r right join ".$db_admin_prefix."admin_role as a on r.role_id=a.id where a.available='1' and r.user_id='$userid' )) and available='1' and father_code='$classid' order by sort ");

}
//获取一个角色的用户
function getUsersByRoleName($roleName){
	global $_DB,$db_admin_prefix;
	$sql = "select u.name,u.id from ".$db_admin_prefix."admin_user as u,".$db_admin_prefix."admin_user_role as ur,".$db_admin_prefix."admin_role as r where ur.user_id = u.id and ur.role_id=r.id and r.name='".$roleName."'";
//	echo $sql;

   	return $_DB->get_all($sql);
}
//获取一个角色的用户
function getUsersByRoleName_($table_pre,$roleName){
	global $_DB,$db_admin_prefix;
	$sql = "select u.login_name as name,u.id from ".$table_pre."admin_user as u,".$table_pre."admin_user_role as ur,".$table_pre."admin_role as r where ur.user_id = u.id and ur.role_id=r.id and r.name='".$roleName."' group by id";
//	echo $sql;
   	return $_DB->get_all($sql);
}
// 获取客户端IP
function getip() {
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$realip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if (getenv("HTTP_X_FORWARDED_FOR")) {
			$realip = getenv( "HTTP_X_FORWARDED_FOR");
		} elseif (getenv("HTTP_CLIENT_IP")) {
			$realip = getenv("HTTP_CLIENT_IP");
		} else {
			$realip = getenv("REMOTE_ADDR");
		}
	}
	return $realip;
}

function redirect($msg, $url, $min="0") {
	echo $msg."\n";
	echo "<meta http-equiv=\"refresh\" content=\"".$min.";URL=".$url."\">\n";
	exit;
}
// 错误提示信息
function sa_exit($msg, $url, $target='') {
	if (!empty($target)) {
		$target = " target=\"".$target."\"";
	}
    echo $msg."\n";
	echo "<a href=\"".$url."\"".$target.">点击这里返回...</a>";
    exit;
}
//注册信息(realname,username,password,email,address)
function registerok_close($realname,$username,$password,$email,$address){
     global $_DB,$db_admin_prefix;
	 $ok=$_DB->query("insert into `".$db_admin_prefix."admin_user` (`name`,`login_name`,`login_passwd`,`email`,`cell_phone`,`comment`) values('$realname','$username','".md5($password)."','$email','','')");
	 if($ok){
	    return 1;
	 }else{
	    return 0;
	 }
}
//获取角色，通过用户(userid)
function roleMess($userid){
     global $_DB,$db_admin_prefix;
	 return $_DB->get_row("select * from ".$db_admin_prefix."admin_user_role where user_id='$userid'");
}

?>