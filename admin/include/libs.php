<?php //默认文件，可跳转

function curl_api($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_POST,           true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $data ); //$data是每个接口的json字符串
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //不加会报证书问题
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  //不加会报证书问题
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json; charset=utf-8'));
		return curl_exec ($ch);
}

function  cuckhid($hid){
		global $_DB;
		$sql = "select top_id from irpt_hospital where  id = {$hid}";
		$id = $_DB->get_one($sql);
		if($id==0){
			exit("请选择科室");
		}
}



/**
 * 计算两日期之间间隔
 * ----------------------------------------------------------------------
 * @param unknown_type $interval
 * @param unknown_type $date1
 * @param unknown_type $date2
 * AddTime:2011-8-30  Update: 2011-8-30
 * ----------------------------------------------------------------------
 */
function DateDiff ($interval = "d", $date1,$date2) {
	
	$timedifference = strtotime($date2) - strtotime($date1);
	switch ($interval) {
		case "w": $retval = bcdiv($timedifference ,604800); break;//星期
		case "d": $retval = bcdiv( $timedifference,86400); break;//天
		case "h": $retval = bcdiv ($timedifference,3600); break;//小时
		case "n": $retval = bcdiv( $timedifference,60); break;//分钟
		case "s": $retval = $timedifference; break;//秒
	}
	return $retval;
}


/**
 * 取得月末时间
 * ----------------------------------------------------------------------
 * AddTime:2011-8-27  Update: 2011-8-27
 * ----------------------------------------------------------------------
 */
function end_month($time)
{
	$end_month=date('d',strtotime('-1 day',strtotime(date('Y-m-1',strtotime('+1 month',strtotime($time.'-1'))))));
	return $end_month;
}



/**
* 百分比函数
* ----------------------------------------------------------------------
* @param unknown_type $value1	整数
* @param unknown_type $value2	整数
* AddTime:2011-7-14  Update: 2011-7-14
* ----------------------------------------------------------------------
*/
function percentage($value1,$value2)
{
	return round($value1/$value2*100,2); 
}
/**
* 昨天
* ----------------------------------------------------------------------
* @param $time
* AddTime:2011-7-25  Update: 2011-7-25
* ----------------------------------------------------------------------
*/
function yesterday($time)
{
	return strtotime("-1 day",strtotime($time.' 23:59:59'));
}
/**
* 明天
* ----------------------------------------------------------------------
* @param $time
* AddTime:2011-7-25  Update: 2011-7-25
* ----------------------------------------------------------------------
*/
function tomorrow($time)
{
	return strtotime("+1 day",strtotime($time .'00:00:01'));
}



/**
 * 取得医院列表
 * ----------------------------------------------------------------------
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function getHospital()
{
	global $_DB;
	init_session();
	$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
	if($userid==0) die;
	$hospital = $_DB->get_all("select id_hospital,hospital_name from irpt_hospital_admin where id_admin='$userid' and is_delete<>1 ORDER BY `hospital_name` ASC ");
	return $hospital;
}

/**
 * 判断某个IP是否在一个IP数组中
 +----------------------------------------------------------
 * @param $ip	IP
 * @param $ips  IP数组
 * Time:2011-11-30
 +----------------------------------------------------------
 */
function inIPArray($ip,$ips){
	if(in_array($ip,$ips)){ //在的话直接返回
		return  true;	
	}
	$ipe=explode('.',$ip); //切开IP
	$ip1=$ipe[0].'.'.$ipe[1].'.'.$ipe[2].'.*'; //先查找第一个IP段
	if(in_array($ip1,$ips)){ //在的话直接返回
		return  true;	
	}
	$ip2=$ipe[0].'.'.$ipe[1].'.*.*'; //再查找第二个IP段
	if(in_array($ip2,$ips)){ //在的话直接返回
		return  true;	
	}
	$ip3=$ipe[0].'.*.*.*'; //再查找第三个IP段
	if(in_array($ip3,$ips)){ //在的话直接返回
		return  true;	
	}
	return FALSE;
}

/**
 * 验证用户是否对这个医院有权限（针对统计表恶意修改表单做处理）
 * ----------------------------------------------------------------------
 * @param $id
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function checkHospId($id)
{
	$ids=getHospital();
	foreach ($ids as $key => $value)
	{
		if($value['id_hospital']==$id)
			return true;
	}
	return false;	
}
/**
 * 当有恶意修改表单，记下日记。
 * ----------------------------------------------------------------------
 * @param $id
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function safetyHosp($id)
{
	if(false==checkHospId($id))
	{
		exit('非法操作，系统已经记录下你的记录！');  //先不数据表处理，待扩展
	}
} 


/**
 * 取得医院分配的用户
 * ----------------------------------------------------------------------
 * @param $hospitalid
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function getConsult($hospitalid)
{
	global $_DB;
	$consults = $_DB->get_all("select bands.* from irpt_hospital_admin as bands,admin_user_role as roles where bands.id_hospital='$hospitalid' and bands.id_admin = roles.user_id and roles.role_id=2 and is_delete=0");	
	return $consults;
}


/**
 * 取得用户
 * ----------------------------------------------------------------------
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function getUser()
{
	init_session();
	$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
	$userName = isset($_SESSION['admin_name'])?$_SESSION['admin_name']:'0';
	return array($userid,$userName);
}
/**
 * 取得用户ID
 * ----------------------------------------------------------------------
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function getUserId()
{
	init_session();
	$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
	
	return $userid;
}

function get_hid()
{
	init_session();
	$hid = isset($_SESSION['hospital_id'])?$_SESSION['hospital_id']:'0';

		return $hid;
	
}



/**
 * 取得用户名称
 * ----------------------------------------------------------------------
 * AddTime:2011-8-16  Update: 2011-8-16
 * ----------------------------------------------------------------------
 */
function getUserName()
{
	init_session();
	$userid = isset($_SESSION['admin_name'])?$_SESSION['admin_name']:'0';
	
	return $userid;
}

function getALL($sql)
{
	global $_DB;
	return $_DB->get_all($sql);
}

/**
 * 验证时间  让查前三个月的数据
 */
function checkingTime($time){
	global $_DB;
	init_session();
	$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
	$time2=$_DB->get_one('select add_time from admin_user  where id='.$userid);
	//if($time<$time2){
		//die();
	//}
	
}







?>