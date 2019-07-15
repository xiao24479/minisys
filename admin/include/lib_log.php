<?php
   global $_CFG;
   //添加登录日志记录
   function insert_login_log($login_array){
     global $_DB,$_CFG;
	 $_DB->query("insert into ".$_CFG['prefix']."admin_login_log(`user_id`,`login_name`,`login_datetime`,`ip`,`session_id`) values ('".$login_array['user_id']."','".$login_array['login_name']."','".$login_array['login_time']."','".$login_array['ip']."','".$login_array['sessionid']."')");
	 
   }
   //更新记录
   function update_login_log($time,$sessionid){
     global $_DB,$_CFG;
	// die( "update ".$_CFG['prefix']."admin_login_log set logout_datetime='$time' where session_id='$sessionid'");
	 $_DB->query("update ".$_CFG['prefix']."admin_login_log set logout_datetime='$time' where session_id='$sessionid'");
   }
   //判断是不是存在
   function is_login_log($sessionid){
     global $_DB,$_CFG;
	 return $_DB->get_row("select * from ".$_CFG['prefix']."admin_login_log where session_id='$sessionid'");
   }
   //get the admin name
   function get_admin_name(){
     global $_DB,$_CFG;
     return $_DB->get_all("select * from ".$_CFG['prefix']."admin_user ");
   }
?>