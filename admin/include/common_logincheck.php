<?php
session_start();
		$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
		if($userid==='0'){
			global $_TEMPLATE;
			$_TEMPLATE->display('reloginmid.html');
		die();
}
?>