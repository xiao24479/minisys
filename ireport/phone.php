<?php
require_once(APP_PATH."/include/libs.php");
/**
 * 电话
 * ----------------------------------------------------------------------
 * @author 张嘉鹏
 * AddTime:2011-8-12  Update: 2011-8-12
 * ----------------------------------------------------------------------
 */
class phone extends cls_base
{
	/**
	 * 媒体类型
	 * ----------------------------------------------------------------------
	 * @var unknown_type
	 * AddTime:2011-8-12  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */	
	public $channel =array(		
			0=>array("info"=>"选择渠道"),	
			1=>array("info"=>"市场义诊"),			
			2=>array("info"=>"市场转诊"),
			3=>array("info"=>"报纸看到的"),
			4=>array("info"=>"朋友介绍的"),
			5=>array("info"=>"广告上看到"),						
			6=>array("info"=>"网上看到的"),
	);
	/**
	 * 初始化
	 * ----------------------------------------------------------------------
	 * @see _frame/cls_base::init()
	 * AddTime:2011-7-29  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function init()
	{
		require_once(ROOT_PATH."_ext/auth_admin.php");
		require_once(ROOT_PATH."_ext/page.class.php");
	}
	/**
	 * 我的电话查询
	 +----------------------------------------------------------
	 * Time:2012-2-11
	 +----------------------------------------------------------
	 */
	function myselect()
	{
		global $_TEMPLATE,$_DB;
		$hosp = getHospital();
		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->assign("start_day",date("Y-m-d",strtotime("-2 day")));
		$_TEMPLATE->assign("dateday",date("Y-m-d",strtotime("-1 day")));
		$_TEMPLATE->display("phone/myselect.html");
	}
	/**
	 * 我的电话查询功能
	 +----------------------------------------------------------
	 * Time:2012-2-11
	 +----------------------------------------------------------
	 */
	function myselect_action()
	{
		global $_TEMPLATE;
		$hosp = getHospital();
		$start_day=get_data('start_day','s');	
		$dateday=get_data('dateday','s');
		$query_type=get_data('query_type');
		$_TEMPLATE->assign("query_type",$query_type);
		$_TEMPLATE->assign("dateday",$dateday);
		$_TEMPLATE->assign("start_day",$start_day);
		
		
		$wheres = "1=1";
		$conditins = "";
		$conditins .= "&start_day=".$start_day;
		$conditins .= "&dateday=".$dateday; 
		$conditins .= "&query_type=".$query_type;
		if($query_type=="0")
		{
			$wheres .= " and (add_time between unix_timestamp('$start_day 00:00:00')  and unix_timestamp('$dateday 23:59:59')) ";
		}
		else if($query_type=="1") 
		{
			$wheres .= " and (date_day between unix_timestamp('$start_day 00:00:00')  and unix_timestamp('$dateday 23:59:59')) ";
			
		}
		else if($query_type=="2")
		{
			$wheres .= " and  visited=1 and (visite_date between unix_timestamp('$start_day 00:00:00')  and unix_timestamp('$dateday 23:59:59')) ";
			
		}
		
		$hospid=get_data('hospital','i');	
		
		$userid= getUserId();
		
		$pagenavigate_countsql = "select count(*) from irpt_patient where id_consult='$userid' and id_hospital ='$hospid'  and $wheres   and channel_type=1";
		
		 $pagenavigate_sql = "select *,from_unixtime(date_day,'%Y-%m-%d') as date_day1,from_unixtime(track_next_time,'%Y-%m-%d') as track_next_time1,from_unixtime(visite_date,'%Y-%m-%d') as visite_date1,from_unixtime(add_time,'%Y-%m-%d') as add_time1 ,from_unixtime(visite_datetime,'%Y-%m-%d') as visite_datetime1 from irpt_patient where id_consult='$userid' and channel_type=1 and id_hospital ='$hospid'  and $wheres order by id desc";
		
		$pagenavigate_submiturl= '/ireport/main.php';
		$rowsPerPage= 20;
		$pagenavigate_para     = "m=phone&a=myselect_action&".$conditins."&hospital=".$hospid;
		include_once(ROOT_PATH.'/_ext/lib_pagination.php');

		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->assign("hospid",$hospid);
		$_TEMPLATE->display("phone/myselect.html");
	}
	
	/**
	 * 超级电话查询
	 +----------------------------------------------------------
	 * Time:2012-2-11
	 +----------------------------------------------------------
	 */
	function my_query()
	{
		global $_TEMPLATE;
		$_TEMPLATE->display("phone/my_query.html");
	}
	
	/**
	 * 超级查询统计方法
	 +----------------------------------------------------------
	 * Time:2012-2-11
	 +----------------------------------------------------------
	 */
	function my_query_select(){
		global $_TEMPLATE,$_DB;
		$hid = get_data('hid');
		$name = get_data('name');
		$phone = get_data('phone');
		$order_no = get_data('order_no');
		$uid= getUserId();
		if($name!=''||$phone!=''||$order_no!='')
		{
			
			if($hid!=0){

				$where="";
				if($name!='')
				{
					$where.=" and `patient_name`  like '%".$name."%'  ";
				}
				if($phone!='') 
				{  
					$where.=" and `phone` = '".$phone."'  "; 
				}
				if($order_no!='')
				{
					$where.=" and `order_no` = '".$order_no."'  ";
				}
				$sql="select * from  `irpt_patient` where  `channel_type` ='1'   and `id_hospital` = '".$hid."' ".$where;
				$all_1=$_DB->get_all($sql);  
				if($all_1)   
				{ 
						$p = new Page(count($all_1),20); 
						$sql="select * from `irpt_patient`  where   `channel_type`='1'  and  `id_hospital` = '".$hid."' ".$where." LIMIT ".$p->firstRow.",".$p->listRows." ;";
						$all=$_DB->get_all($sql); 
						$_TEMPLATE->assign('page',$p->show('query')); 
						$_TEMPLATE->assign('data',$all);
						make_json_result($_TEMPLATE->fetch('phone/my_query_select.html'),'', array());	
				}
				else{  
					make_json_result($_TEMPLATE->fetch('phone/my_query_select.html'),'', array());
				}
				
			
			}else{
				$hosp = getHospital();  
				$hw = " and  ( 1>1  "; 
				foreach ($hosp as $key => $val){
					$hw .= " or `id_hospital` = '".$val['id_hospital']."'  ";
				}
				$hw .=" ) ";
				$where="";
				if($name!='')
				{
					$where.=" and `patient_name`  like '%".$name."%'  ";
				}
				if($phone!='') 
				{  
					$where.=" and `phone` = '".$phone."'  "; 
				}
				if($order_no!='')
				{
					$where.=" and `order_no` = '".$order_no."'  ";
				}
				
				
				
				 $sql="select * from  `irpt_patient` where  `channel_type`='1'    ".$hw.$where; 
				$all_1=$_DB->get_all($sql);  
				if($all_1)   
				{ 
						$p = new Page(count($all_1),20); 
						$sql="select * from `irpt_patient`  where   `channel_type`='1'  ".$hw.$where." LIMIT ".$p->firstRow.",".$p->listRows." ;";
						$all=$_DB->get_all($sql); 
						$_TEMPLATE->assign('page',$p->show('query')); 
						$_TEMPLATE->assign('data',$all);
						make_json_result($_TEMPLATE->fetch('phone/my_query_select.html'),'', array());	
				}
				else{  
					make_json_result($_TEMPLATE->fetch('phone/my_query_select.html'),'', array());
				}
				
				
				
			}
			
		}
		else{
			make_json_result($_TEMPLATE->fetch('phone/my_query_select.html'),'', array());
		}
		
	} 
	/** 
	 * 超级查询统计今天回访
	 +----------------------------------------------------------
	 * Time:2012-2-11
	 +----------------------------------------------------------
	 */
	function my_query_vb()
	{
		global $_TEMPLATE,$_DB;
		$hid = get_data('hid');
		$t_top = $this->top_day();  //取得昨天时间 
		$t_btm = $this->btm_day();  //取得明天时间

		
		$uid = getUserId();
		$sql="SELECT  count(*) as count  FROM   `irpt_patient`  where  `track_next_time` between '".$t_top."' and  '".$t_btm."' ;  ";
		$temp=$_DB->get_one($sql);
		$p = new Page($temp,20);
		$sql = "SELECT  *  FROM   `irpt_patient` where   `track_next_time` between '".$t_top."' and  '".$t_btm."'  ORDER BY  `track_next_time` DESC  LIMIT ".$p->firstRow.",".$p->listRows." ;";
		$data=$_DB->get_all($sql);
		$_TEMPLATE->assign('vb',$temp);
		$_TEMPLATE->assign('page',$p->show('query_vb')); //输出分页 
		$_TEMPLATE->assign('data',$data);	
		make_json_result($_TEMPLATE->fetch('patients/query.html'),'', array());	
	}
	
	
	
	
	/**
	 * 所有电话患者
	 +----------------------------------------------------------
	 * Time:2012-2-10
	 +----------------------------------------------------------
	 */
	function select()
	{
		global $_TEMPLATE,$_DB;
		$hosp = getHospital();
		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->assign("start_day",date("Y-m-d",strtotime("-2 day")));
		$_TEMPLATE->assign("dateday",date("Y-m-d",strtotime("-1 day")));
		$_TEMPLATE->display("phone/phone.html");
	}
	function select_action()
	{
		global $_TEMPLATE;
		$hosp = getHospital();
		$start_day=get_data('start_day','s');	
		$dateday=get_data('dateday','s');
		$query_type=get_data('query_type');
		$_TEMPLATE->assign("query_type",$query_type);
		$_TEMPLATE->assign("dateday",$dateday);
		$_TEMPLATE->assign("start_day",$start_day);
		
		
		$wheres = "1=1";
		$conditins = "";
		$conditins .= "&start_day=".$start_day;
		$conditins .= "&dateday=".$dateday; 
		$conditins .= "&query_type=".$query_type;
		if($query_type=="0")
		{
			$wheres .= " and (add_time between unix_timestamp('$start_day 00:00:00')  and unix_timestamp('$dateday 23:59:59')) ";
		}
		else if($query_type=="1") 
		{
			$wheres .= " and (date_day between unix_timestamp('$start_day 00:00:00')  and unix_timestamp('$dateday 23:59:59')) ";
			
		}
		else if($query_type=="2")
		{
			$wheres .= " and  visited=1 and (visite_date between unix_timestamp('$start_day 00:00:00')  and unix_timestamp('$dateday 23:59:59')) ";
			
		}
		
		$hospid=get_data('hospital','i');	
		
		$userid= getUserId();
	
		$pagenavigate_countsql = "select count(*) from irpt_patient where id_hospital ='$hospid'  and $wheres   and channel_type=1";
		
		$pagenavigate_sql = "select *,from_unixtime(date_day,'%Y-%m-%d') as date_day1,from_unixtime(track_next_time,'%Y-%m-%d') as track_next_time1,from_unixtime(visite_date,'%Y-%m-%d') as visite_date1,from_unixtime(add_time,'%Y-%m-%d %H:%y:%s') as add_time1 ,from_unixtime(visite_datetime,'%Y-%m-%d') as visite_datetime1 from irpt_patient where channel_type=1 and id_hospital ='$hospid'  and $wheres order by id desc";

		$pagenavigate_submiturl= '/ireport/main.php';
		$rowsPerPage= 20;
		$pagenavigate_para     = "m=phone&a=select_action&".$conditins."&hospital=".$hospid;
		include_once(ROOT_PATH.'/_ext/lib_pagination.php');

		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->assign("hospid",$hospid);
		$_TEMPLATE->display("phone/phone.html");
	}
	
	
	/**
	 * 电话预约录入
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-1  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function add()
	{
		
		
		
		global $_TEMPLATE,$_DB;
		$userid= getUserId();
		$consult_name = $_DB->get_one("select `name` from admin_user where id= $userid");
		$_TEMPLATE->assign("consult_name",$consult_name);
		$_TEMPLATE->assign('day',date("Y-m-d"));
		$_TEMPLATE->assign('channel',$this->channel); 
		$sql="SELECT * FROM  `irpt_patient` WHERE  `channel_type` = 1 AND id_consult ='".$userid."'  ORDER BY  `add_time` DESC LIMIT 0 , 10";
		$data= $_DB->get_all($sql);
		$i=date('i');
		$ii=$i-1; //设定1小时
		$time=strtotime(date('Y-m-d H:'.$i.':s'));
		$time2=strtotime(date('Y-m-d H:'.$ii.':s'));
		foreach ($data as $key =>$value){
			if($value['add_time']>=$time2||$value['add_time']<=$time){
				$data[$key]['is_delete']='1';
			}
		}
		
		$_TEMPLATE->assign('dbs',$data);
		
	
		$_TEMPLATE->display("phone/add.html");
			
		
		
		
	}
	
	
	
	/**
	 * 电话预约录入
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-1  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function madd()
	{
		
		
		
		global $_TEMPLATE,$_DB;
		$userid= getUserId();
		$consult_name = $_DB->get_one("select `name` from admin_user where id= $userid");
		$_TEMPLATE->assign("consult_name",$consult_name);
		$_TEMPLATE->assign('day',date("Y-m-d"));
		$_TEMPLATE->assign('channel',$this->channel); 
		$sql="SELECT * FROM  `irpt_patient` WHERE  `channel_type` = 1 AND id_consult ='".$userid."'  ORDER BY  `add_time` DESC LIMIT 0 , 10";
		$data= $_DB->get_all($sql);
		$i=date('i');
		$ii=$i-1; //设定1小时
		$time=strtotime(date('Y-m-d H:'.$i.':s'));
		$time2=strtotime(date('Y-m-d H:'.$ii.':s'));
		foreach ($data as $key =>$value){
			if($value['add_time']>=$time2||$value['add_time']<=$time){
				$data[$key]['is_delete']='1';
			}
		}
		
		$_TEMPLATE->assign('dbs',$data);
		
		
			$_TEMPLATE->display("phone/add_m.html");
			
		
		
		
		
	}
	
	function isMobile()
	{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 
	
	
	/**
	 * 添加患者
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-12  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function add_patients()
	{
		global $_TEMPLATE,$_DB;
		$hid = get_data('hospital');
		$name = get_data('name');
		$channel = get_data('channel');
		$phone = get_data('phone');
		$age = get_data('age');
		$sex = get_data('sex');
		$zuoji = get_data('zuoji');
		$area = get_data('area');
		$month = strtotime(get_data('month'));
		$btm_month=get_data('btm_month');
		if($btm_month!='')
			$btm_month=strtotime($btm_month);
		$disease =  $_POST['disease'];
		$contents = get_data('contents');
		$consult_name = get_data('consult_name');
		$userid= getUserId();
		$fee = get_data('fee');
		$form_site = get_data('site');
		$time3=strtotime("-3 month"); //取得前三个月的时间
		$order_no = get_data('order_no');
			if($_POST['disease'][0]!='')
			$disease_no1 =$_POST['disease'][0];
		$msg = '';
		
		if(empty($channel)) 
		{
			$msg = "具体预约情况不能为空!";
		
		}
		if(empty($name))
		{	
			$msg = "姓名不能为空!";
			
		}
		if($phone==''&&$zuoji=='')
		{ $msg = "电话和座机必须填一个!";
		}
		
		if(!is_numeric($phone)&&str_len($phone)!=11)
		
		{
			 $msg = "电话格式有问题!";
			
		}
		
		
		if(empty($area))
		{
			 $msg = "地区不能为空!";
		}	
		if(empty($disease))
		{ 
			 $msg = "病种不能为空!";
	
		}
		$sql="select id from irpt_patient where channel_type=1 and   phone='".$phone."' and  patient_name='".$name."' and `add_time` > '".$time3."';";  
		$bool=$_DB->get_one($sql);
		if($bool){
			
			$msg = "添加失败,该患者已经存在!";
			
		}
		
		
		if($msg==''){
			$sql="select id from irpt_patient where    phone='".$phone."' or  zuoji='".$zuoji."' ;";  
			$bool=$_DB->get_one($sql);
			if($bool){
				
				$msg = "添加失败,电话和座机存在重复!";
				
			}
		}
		
		
		if($msg==''){
			
	
			
			
			$iiiid=$_DB->get_one("SELECT max(id) FROM `irpt_patient`");
			$iiiid++;	
			
			$sql = "INSERT INTO `irpt_patient` (id, `channel_type`,`channel_detail`,`id_hospital`, `id_consult`, `consult_name`, `add_time`, `visited`, `visite_type`, `patient_name`, `age`, `area`,`from_site` ,`phone`,`zuoji`, `memo`,`date_day`,  `sex`,  `disease`,  `fee`,  `add_day`, `track_times`,`order_no`,`track_next_time`)  VALUES ('{$iiiid}','1','".$channel."' ,'".$hid."', '".$userid."', '".$consult_name."', '".time()."', '0', '0', '".$name."', '".$age."', '".$area."','".$form_site."', '".$phone."','".$zuoji."', '".$contents."', '".$month."' ,'".$sex."', '".$disease_no1."',  '".$fee."', '".$month."','0','".$order_no."','$btm_month');";
			 $_DB->query($sql);
			$time = time();
			
			foreach ($disease as $key=>$val){
				$disease_id=$_DB->get_one("SELECT id FROM `irpt_disease` WHERE real_name = '{$val}' ");
				$sql = "INSERT INTO  `irpt_patient_disease` ( `patient_id`,`disease_id`, `disease_name`, `add_time`) VALUES ( '{$iiiid}','{$disease_id}', '{$val}', '{$time}');";
				 $_DB->query($sql);
			}
			$_TEMPLATE->assign("msg","添加成功！");
			
			$this->add();
		}else{
			
			
			$_TEMPLATE->assign("channel",$channel);

			$_TEMPLATE->assign("name",$name);

			$_TEMPLATE->assign("phone",$phone);

			$_TEMPLATE->assign("age",$age);

			$_TEMPLATE->assign("area",$area);
			$_TEMPLATE->assign("disease",$disease);
			$_TEMPLATE->assign("contents",$contents);
			
			$_TEMPLATE->assign("msg",$msg);
			$this->add();
		}
		
		
		
		
		
	}
	
	
/**
	 * 添加患者
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-12  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function add_m_patients()
	{
		global $_TEMPLATE,$_DB;
		$hid = get_data('hospital');
		$name = get_data('name');
		$channel = get_data('channel');
		$phone = get_data('phone');
		$age = get_data('age');
		$sex = get_data('sex');
		$area = get_data('area');
		$month = strtotime(get_data('month'));
		$btm_month=get_data('btm_month');
		if($btm_month!='')
			$btm_month=strtotime($btm_month);
		$disease =  $_POST['disease'];
		$contents = get_data('contents');
		$consult_name = get_data('consult_name');
		$userid= getUserId();
		$fee = get_data('fee');
		$form_site = get_data('site');
		$time3=strtotime("-3 month"); //取得前三个月的时间
		$order_no = get_data('order_no');
			if($_POST['disease'][0]!='')
			$disease_no1 =$_POST['disease'][0];
		$msg = '';
		
		if(empty($channel)) 
		{
			$msg = "具体预约情况不能为空!";
		
		}
		if(empty($name))
		{	
			$msg = "姓名不能为空!";
			
		}
		if(empty($phone))
		{ $msg = "电话不能为空!";
		}
		if(empty($area))
		{
			 $msg = "地区不能为空!";
		}	
		if(empty($disease))
		{ 
			 $msg = "病种不能为空!";
	
		}
		$sql="select id from irpt_patient where channel_type=1 and   phone='".$phone."' and  patient_name='".$name."' and `add_time` > '".$time3."';";  
		$bool=$_DB->get_one($sql);
		if($bool){
			
			$msg = "添加失败,该患者已经存在!";
			
		}
		
		
		if($msg==''){
			
			$iiiid=$_DB->get_one("SELECT max(id) FROM `irpt_patient`");
			$iiiid++;	
			
			$sql = "INSERT INTO `irpt_patient` (id, `channel_type`,`channel_detail`,`id_hospital`, `id_consult`, `consult_name`, `add_time`, `visited`, `visite_type`, `patient_name`, `age`, `area`,`from_site` ,`phone`, `memo`,`date_day`,  `sex`,  `disease`,  `fee`,  `add_day`, `track_times`,`order_no`,`track_next_time`)  VALUES ('{$iiiid}','1','".$channel."' ,'".$hid."', '".$userid."', '".$consult_name."', '".time()."', '0', '0', '".$name."', '".$age."', '".$area."','".$form_site."', '".$phone."', '".$contents."', '".$month."' ,'".$sex."', '".$disease_no1."',  '".$fee."', '".$month."','0','".$order_no."','$btm_month');";
			 $_DB->query($sql);
			$time = time();
			
			foreach ($disease as $key=>$val){
				$disease_id=$_DB->get_one("SELECT id FROM `irpt_disease` WHERE real_name = '{$val}' ");
				$sql = "INSERT INTO  `irpt_patient_disease` ( `patient_id`,`disease_id`, `disease_name`, `add_time`) VALUES ( '{$iiiid}','{$disease_id}', '{$val}', '{$time}');";
				 $_DB->query($sql);
			}
			$_TEMPLATE->assign("msg","添加成功！");
			
			$this->madd();
		}else{
			
			
			$_TEMPLATE->assign("channel",$channel);

			$_TEMPLATE->assign("name",$name);

			$_TEMPLATE->assign("phone",$phone);

			$_TEMPLATE->assign("age",$age);

			$_TEMPLATE->assign("area",$area);
			$_TEMPLATE->assign("disease",$disease);
			$_TEMPLATE->assign("contents",$contents);
			
			$_TEMPLATE->assign("msg",$msg);
			$this->madd();
		}
		
		
		
		
		
	}
	
	/**
	 * 电话预约查询
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-12  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function query()
	{
		global $_TEMPLATE,$_DB;
		$_TEMPLATE->display("phone/query.html");
	}
	/**
	 * 电话查询具体方法
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-12  Update: 2011-8-12
	 * ----------------------------------------------------------------------
	 */
	function query_action()
	{
		
		global $_TEMPLATE,$_DB;
		$hid = get_data('hid');
		$name = get_data('name');
		$phone = get_data('phone');
		$order_no = get_data('order_no');
		$uid= getUserId();
		if($name!=''||$phone!=''||$order_no!='')
		{
			$where="";
			if($name!='')
			{
				$where.=" and `patient_name`  like '%".$name."%'  ";
			}
			if($phone!='') 
			{  
				$where.=" and `phone` = '".$phone."'  ";
			}
			if($order_no!='')
			{
				$where.=" and `order_no` = '".$order_no."'  ";
			}
			$sql="select * from  `irpt_patient` where  `channel_type` ='1'  and `id_consult` = '".$uid."' and `id_hospital` = '".$hid."' ".$where;
			$all_1=$_DB->get_all($sql);  
			if($all_1)  
			{ 
					$p = new Page(count($all_1),20);  //分页初始化
					$sql="select * from `irpt_patient`  where   `channel_type`='1'  and  `id_consult` = '".$uid."' and  `id_hospital` = '".$hid."' ".$where." LIMIT ".$p->firstRow.",".$p->listRows." ;";
					$all=$_DB->get_all($sql); 
					$_TEMPLATE->assign('page',$p->show('query')); //输出分页 
					$_TEMPLATE->assign('data',$all);
					make_json_result($_TEMPLATE->fetch('phone/query_data.html'),'', array());	
			}
			else{ 
				
				make_json_result($_TEMPLATE->fetch('phone/query_data.html'),'', array());
			}
			
		}
		else{
			make_json_result($_TEMPLATE->fetch('phone/query_data.html'),'', array());
		}
	}
	/**
	 * 个人统计
	 * ----------------------------------------------------------------------
	 * AddTime:2011-9-3  Update: 2011-9-3
	 * ----------------------------------------------------------------------
	 */
	function myquery()
	{
		global $_TEMPLATE;
		$_TEMPLATE->assign('day1',date('Y-m-1'));		
		$_TEMPLATE->assign('day2',date('Y-m-d'));
		$_TEMPLATE->display('phone/myquery.html');
	}
	function my_query_action()
	{
		global $_TEMPLATE,$_DB;
		$hid=get_data('hid');
		$day1=strtotime(get_data('day1'));
		$day2=strtotime(get_data('day2').' '.date('h:i:s'));
		$uid= getUserId();
		
		$sql="SELECT  count(*) as count  FROM irpt_patient WHERE  id_consult=".$uid."  and  id_hospital='".$hid."' and  add_time  between '".$day1."' and '".$day2."'; ";
		$count=$_DB->get_one($sql);
	
		$p = new Page($count, 20);
		$sql="SELECT  *  FROM  irpt_patient  WHERE  id_consult=".$uid." and  id_hospital='".$hid."' and add_time between '".$day1."' and '".$day2."' ORDER BY  add_time  DESC  LIMIT ".$p->firstRow.",".$p->listRows." ;";
		$all = $_DB->get_all($sql);
		$_TEMPLATE->assign('page',$p->show('query')); //输出分页 
		$_TEMPLATE->assign('data',$all);
		
		make_json_result($_TEMPLATE->fetch('phone/my_query_data.html'),'', array());
	}
	
	
	
}
?>