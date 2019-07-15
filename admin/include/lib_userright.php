<?php

if(!defined('IN_APP'))
	die('Hacking attempt');



function getListUserRight($flag,$roleId,$hospitalId,$userName='',$startRow=0,$listRows=0) {
	$createUserId = intval($_SESSION['admin_id']) == SYS_ADMIN_ID ? 0 : intval($_SESSION['admin_id']);
	$userCreate = getUserCreateData($createUserId,$roleId,$hospitalId,$userName,$startRow,$listRows,$flag);
	if($userCreate) {
		foreach($userCreate as &$uc) {
			$uc['node'] = getUserCreateData($uc['id'],$roleId,$hospitalId,$userName,$startRow,$listRows,$flag);
		}
	}

	return $userCreate;
}


function getUserCreateData($createUserId,$roleId,$hospitalId,$userName,$startRow,$listRows,$flag) {
	global $_DB;
	//var_dump($createUserId,$roleId,$hospitalId);
	$userIds = $_DB->get_col("select id from admin_user where create_user_id={$createUserId}");
	$userRole = array();
	$userHospital = array();
	if($roleId!=0) {
		$userRole = $_DB->get_col("select user_id from admin_user_role where role_id={$roleId}");
	}
	if($hospitalId!=0) {
		$userHospital = $_DB->get_col("select id_admin from irpt_hospital_admin where id_hospital={$hospitalId}");
	}
	$result = array();
	if($roleId!=0 && $hospitalId!=0) {
		$result = array_uintersect($userRole,$userHospital,$userIds,"strcasecmp");
	} elseif($roleId!=0 && $hospitalId==0) {
		$result = array_uintersect($userRole,$userIds,"strcasecmp");
	} elseif($roleId==0 && $hospitalId!=0) {
		$result = array_uintersect($userHospital,$userIds,"strcasecmp");
	} else {
		$result = $userIds;
	}

	// 超级管理必须有
	if($createUserId==0) array_unshift($result,1);

	$where = "";
	if(!empty($result)) {
		$or = "";
		foreach($result as $p) {
			$where .= "{$or}id={$p}";
			$or = " or ";
		}
	}
	$where = $where ? " id > 0 and ($where) " : " id < 0 ";
	$startRow = $startRow<0 ? 0 : $startRow;
	$sqlwhere = "";
	if($listRows>0) {
		$sqlwhere = " limit {$startRow},{$listRows} ";
	}
	if(!empty($userName)) $where .= " and `name` like '{$userName}%'";
	$sql = "select id,name,login_name,is_delete from admin_user where {$where} {$sqlwhere}";
	$data = $_DB->get_all($sql);
	// 将状态为正常和禁止分开
	$status = array();
	if($data) {
		foreach($data as $qq) {
			$status[] = $qq['is_delete'];
		}
	}
	array_multisort($status, SORT_ASC, $data);
	if($data) {
		foreach($data as & $q) {
			$q['is_user_hospital_delete'] = $_DB->get_one("select is_delete from irpt_hospital_admin where id_hospital={$hospitalId} and id_admin={$q['id']}");
		}
	}

	return is_array($data) ? $data : array();
}






?>