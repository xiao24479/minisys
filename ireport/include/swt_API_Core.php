<?php

/**
 * 获取总访客
 */
function get_sum_liulan($host,$start_time,$end_time){
		$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
		 $sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' ";
		 return $_SWT->get_one($sql);
}

/**
 * 获取总访客
 */
function get_sum_fangke($host,$start_time,$end_time){
		$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
		 $sql = "SELECT  count(distinct ip)  FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' ";
		 return $_SWT->get_one($sql);
}
/**
 * 获取某个账户总访客
 */
function get_hosp_fangke($host,$start_time,$end_time,$biaoji){
		$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
		$sql = "SELECT count(distinct ip) FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' and firstURl LIKE '%{$biaoji}%' ";
		return $_SWT->get_one($sql);
}

/**
 * 获取某个账户总浏览
 */
function get_hosp_liulan($host,$start_time,$end_time,$biaoji){
		$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
		$sql = "SELECT count(id)  FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' and firstURl LIKE '%{$biaoji}%' ";
		return $_SWT->get_one($sql);
}

/**
 * 获取某个账户的对话类型（1,仅访问；2，客服未接受；3，客服无讯息；4，客人无讯息；5，普通对话；6 较好对话；7，极佳对话；8，内部对话）
 */
function get_chatkind_duihua($host,$start_time,$end_time,$biaoji,$chatkind){
		$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
		$sql = "SELECT  count(distinct cid)  FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' and chatkind ='{$chatkind}'  and firstURl  LIKE '%{$biaoji}%' ";
		return $_SWT->get_one($sql);
}
//获取账户对话
function get_chatkind_duihua2($host,$start_time,$end_time,$biaoji){
	$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
	$sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' and wordscount>'0'  and firstURl  LIKE '%{$biaoji}%' ";
	return $_SWT->get_one($sql);
}
//获取账户无效对话
function get_chatkind_duihua3($host,$start_time,$end_time,$biaoji){
	$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
	$sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '$start_time' and '$end_time' and wordscount>'0' and colors='-1' and firstURl  LIKE '%{$biaoji}%' ";
	return $_SWT->get_one($sql);
}
//获取账户留联
function get_chatkind_liulian($host,$start_time,$end_time,$biaoji){
	$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
	$sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '{$start_time}' and '{$end_time}' and wordscount>0  and colors='28'  and firstURl  LIKE '%{$biaoji}%'; ";
		return $_SWT->get_one($sql);
	}

	
function get_chat_15($host,$month,$name){
	$start_time = strtotime($month.' 00:00:00');
	$end_time = strtotime(date("Y-m-d 00:00:00",strtotime("$month +1 day")));
	$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
	$sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '{$start_time}' and '{$end_time}' and wordscount>14    and  Operator  LIKE '%{$name}%'; ";

	return $_SWT->get_one($sql);
	
}	
function get_chat_6($host,$month,$name){
	$start_time = strtotime($month.' 00:00:00');
	$end_time = strtotime(date("Y-m-d 00:00:00",strtotime("$month +1 day")));
	$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
	$sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '{$start_time}' and '{$end_time}' and wordscount<14  and  wordscount>6   and  Operator  LIKE '%{$name}%'; ";
	return $_SWT->get_one($sql);
	
}	
function get_chat_1($host,$month,$name){
	$start_time = strtotime($month.' 00:00:00');
	$end_time = strtotime(date("Y-m-d 00:00:00",strtotime("$month +1 day")));
	$_SWT = new cls_mysql($host['host'], $host['user'],$host['pwd'], $host['dbname']);
	$sql = "SELECT  count(id)  FROM `visitors` WHERE  vtime  BETWEEN '{$start_time}' and '{$end_time}' and wordscount<6  and  wordscount>0   and  Operator  LIKE '%{$name}%'; ";
	return $_SWT->get_one($sql);
	
}		
/**
*预约
**/
function get_chatkind_yuyue($hid,$biaoshi,$ttime,$btime){
	global  $_DB ;
	$sql = "SELECT count(id) as c FROM  irpt_patient  WHERE   id_hospital ='{$hid}' and add_time between '$ttime' and  '$btime'  and gonghai_status = 0 and source_url LIKE '%{$biaoshi}%' ;"; 
	
	$daozhen =  $_DB->get_one($sql);
	return $daozhen;
}
/**
*获取某个账户总到诊
**/	
function get_chatkind_daozhen($hid,$biaoshi,$ttime,$btime){
	global  $_DB ;
	$sql = "SELECT count(id) as c FROM  irpt_patient  WHERE   id_hospital ='{$hid}'  and  visite_date  between '$ttime' and '$btime' and gonghai_status = 0 and source_url LIKE '%{$biaoshi}%' "; 
	$daozhen =  $_DB->get_one($sql);
	
	return $daozhen;
}


