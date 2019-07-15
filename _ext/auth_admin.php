<?php

init_session() ;

//添加每次操作都记录日记
if( !function_exists( 'update_login_log' ) )
{

	function update_login_log( $time , $sessionid )
	{
		global $_DB ;
		$_DB->query( "update admin_login_log set logout_datetime='$time' where session_id='$sessionid'" ) ;
	}

}

$is_login = false;
if( isset( $_SESSION[ 'admin_id' ] ) && $_SESSION[ 'admin_id' ] != 0 )
{
	global $_DB;
	$is_admin = $_DB->get_one("select count(*) from admin_user where id=" . intval($_SESSION['admin_id']) . " and is_delete=0");
	$is_login = $is_admin<1 ? false : true;
}

if( !$is_login ) {
	header( "Location: ../admin/main.php?m=admin&a=timeout" ) ;
	exit;
} else {
	// 更新log
	update_login_log( date( 'Y-m-d H:i:s' ) , session_id() ) ;
}

//是否有权限判断。
global $_MODULE,$_ACTION,$_AJAX;
$menucheck = $_SESSION["menu_check"];

if(!$_AJAX && !in_array($_MODULE."_".$_ACTION,$menucheck)) die("对不起，你不具备此权限，请与系统管理员联系！");

?>