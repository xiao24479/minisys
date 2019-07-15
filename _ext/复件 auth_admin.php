<?php
   
	//添加每次操作都记录日记
   if(!function_exists('update_login_log')){
	   function update_login_log($time,$sessionid){
	     global $_DB,$_CFG;
		 $_DB->query("update ".$_CFG['prefix']."admin_login_log set logout_datetime='$time' where session_id='$sessionid'");
	   }
   }

	/* 把该文件引用意味着你的功能必须是登陆后才能使用，目前仅限于后台管理功能，至于类似会员之类的功能那得另外扩张，可以参照改方法*/
	
		 init_session();
		/*
		$timeout=6000;    //10分钟
		
		$now=time(); 
		if(($now-$_SESSION['login_session_time'])>$timeout){
			session_destroy();
			echo '<script type="text/javascript"> window.parent.location.href="/admin/main.php?m=admin&a=logout";</script>';
			die();
		}else{
			  $_SESSION[ 'login_session_time']=time(); 
		}
		*/
		$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
		if($userid==='0'){ //是否登陆判断！
			header("Location: admin.php?m=admin&a=timeout");
			die(); 
		}else{ 
			update_login_log(date('Y-m-d H:i:s'),session_id());
		}
		
		/* 
		if(!function_exists('get_login_session')){
		   function get_login_session($userid){
		     global $_DB,$_CFG;
			 return $_DB->get_row("SELECT  `is_login`  FROM  ".$_CFG['prefix']."admin_user  WHERE  id=".$userid);
		   }
	    }
		
	    $u= get_login_session($userid); 
		if($u['is_login']!=$_SESSION['is_login'])
		{
			session_destroy();
			
			echo '<script type="text/javascript"> alert("您的账户在别处登录了，请重新登录！"); window.parent.location.href="/admin/main.php?m=admin&a=logout";</script>';
		
			die();
		}
		*/
		
		//是否有权限判断。
		global $_MODULE,$_ACTION,$_AJAX;
		$menucheck = $_SESSION["menu_check"];

		if(!$_AJAX && !in_array($_MODULE."_".$_ACTION,$menucheck)) die("对不起，你不具备此权限，请与系统管理员联系！");
		
	
		
?>