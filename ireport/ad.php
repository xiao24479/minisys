<?php
require_once(APP_PATH."/include/libs.php");
/**
 * 广告管理
 +----------------------------------------------------------
 * @author 围剿
 * Time:2011-11-9
 +----------------------------------------------------------
 */
class ad extends cls_base
{
	function init()
	{
		//init.
		require_once(ROOT_PATH."_ext/auth_admin.php");
	}	
		function ad_select(){							global $_TEMPLATE,$_DB;			$userid= getUserId();				$user_name = $_DB->get_one("select `name` from admin_user where id= $userid");				$hosp = getHospital();				$wheres = " 1=1  and ( 1>1 ";				foreach($hosp as $ahospid)				{					$wheres .=" or id_hospital=".$ahospid["id_hospital"];				}				$wheres .=")";				$_TEMPLATE->assign("hosp",$hosp);				$_TEMPLATE->display("ad/ad_select.html");					}		function ad_select_action(){			global $_TEMPLATE,$_DB;					$userid= getUserId();				$user_name = $_DB->get_one("select `name` from admin_user where id= $userid");				$hosp = getHospital();				$wheres = " 1=1  and ( 1>1 ";				foreach($hosp as $ahospid)				{					$wheres .=" or id_hospital=".$ahospid["id_hospital"];				}				$wheres .=")";			$hospital = get_data('hospital');						$ad_id = get_data('ad_id');						$sql = "SELECT *  FROM  `irpt_cost_ad_data` where  id_hospital = {$hospital} and  ad_id = '{$ad_id}'   ORDER BY  `date_cost` DESC ";						$all = $_DB->get_all($sql);								$_TEMPLATE->assign("all",$all);			$_TEMPLATE->assign("hosp",$hosp);				$_TEMPLATE->display("ad/ad_select.html");	}					
	/**
	 * 添加广告
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function addcost()
	{
		global $_TEMPLATE,$_DB;
		//hospitals
		$userid= getUserId();
		$_TEMPLATE->assign("hosp",$hosp);
		
		$user_name = $_DB->get_one("select `name` from admin_user where id= $userid");
		
		$hosp = getHospital();
		$wheres = " 1=1  and ( 1>1 ";
		foreach($hosp as $ahospid)
		{
			$wheres .=" or id_hospital=".$ahospid["id_hospital"];
		}
		$wheres .=")";
	
		$website =  $_DB->get_all("select * from irpt_hospital_website where $wheres");
		$_TEMPLATE->assign("website",$website);
		
		
		$sql = "select *,from_unixtime(begin_date,'%Y-%m-%d') as begins,from_unixtime(end_date,'%Y-%m-%d') as ends from irpt_cost_ad where $wheres order by id desc limit 10";
		$dbs= $_DB->get_all($sql);
		$_TEMPLATE->assign("dbs",$dbs);
		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->assign("begin_date",date("Y-m-d",strtotime("-1 day")));
		$_TEMPLATE->assign("end_date",date("Y-m-d",strtotime("+30 day")));
		$_TEMPLATE->assign("user_name",$user_name);
		$_TEMPLATE->display("ad/cost_add.html");
	
	}
				/**	 * 添加广告	 +----------------------------------------------------------	 * Time:2011-11-11	 +----------------------------------------------------------	 */	function addcost_data()	{		global $_TEMPLATE,$_DB;		$userid= getUserId();		$_TEMPLATE->assign("hosp",$hosp);				$user_name = $_DB->get_one("select `name` from admin_user where id= $userid");				$hosp = getHospital();				$sql = "select * from irpt_cost_ad_data  order by id desc limit 10";		$dbs= $_DB->get_all($sql);		$_TEMPLATE->assign("dbs",$dbs);		$_TEMPLATE->assign("hosp",$hosp);		$_TEMPLATE->assign("date",date("Y-m-d",strtotime("-1 day")));			$_TEMPLATE->assign("user_name",$user_name);		$_TEMPLATE->display("ad/cost_add_data.html");		}			/**	 * 获取医院的渠道列表	 * Enter description here ...	 */	function get_hospital_qudao(){		global $_DB,$_TEMPLATE;		$hid = get_data('hid');		$sql="select * FROM  `irpt_cost_ad` WHERE id_hospital={$hid}  ORDER BY  `add_time` DESC ";		$all  = $_DB->get_all($sql);		$_TEMPLATE->assign("qudao",$all);				make_json_result($_TEMPLATE->fetch('ad/get_hospital_qudao.html'), '', array());			}			function add_ad_data(){		global $_DB,$_TEMPLATE;			$hospital = get_data('hospital');		$aid	  = get_data('ad_id');		$time = strtotime(get_data('adate'));		$adate1 = date('Y-m-d 00-00-01',strtotime(get_data('adate')));		$adate2  =  date('Y-m-d 23-59-59',strtotime(get_data('adate')));		$fee 	  = get_data('fee');		$fangke   = get_data('fangke');		$duihua_1		= get_data('duihua_1');		$duihua_6 		= get_data('duihua_6');		$duihua_15 		= get_data('duihua_15');		$duihua_bendi 	= get_data('duihua_bendi');		$duihua_youxia  = get_data('duihua_youxia');		$more		 	= get_data('more');		$yuyue	 		= get_data('yuyue');		$daozhen 		= get_data('daozhen');				if($hospital!=''&&$aid!=''){					 	$sql="SELECT id  FROM  `irpt_cost_ad_data` WHERE  ad_id ={$aid} and date_cost_time  between unix_timestamp('{$adate1}') and  unix_timestamp('{$adate2}') ";			$row = $_DB->get_row($sql);			if(!$row['id']){								$sql="select name  from irpt_hospital WHERE id = {$hospital} ";								$hospital_name = $_DB->get_row($sql);								$sql="select ad_name  from irpt_cost_ad  WHERE id = {$aid} ";								$cost_ad = $_DB->get_row($sql);								$sql = "INSERT INTO `irpt_cost_ad_data` (`ad_id` ,`id_hospital` ,`hospital_name` ,`ad_name` ,`date_cost` ,`date_cost_time` ,`fee` ,`fangke` ,`duihua_1` ,`duihua_6` ,`duihua_15` ,`duihua_bendi` ,`duihua_youxia` ,`more` ,`yuyue` ,`daozhen`)VALUES ('{$aid}', '{$hospital}','{$hospital_name['name']}','{$cost_ad['ad_name']}', '{$adate1}',  '{$time}',  '{$fee}',  '{$fangke}',  '{$duihua_1}',  '{$duihua_6}',  '{$duihua_15}',  '{$duihua_bendi}',  '{$duihua_youxia}',  '{$more}',  '{$yuyue}',  '{$daozhen}'); ";								$_DB->query($sql);				$msg = "添加成功";							}else{										$msg = "数据已经添加";							}					}else{						$msg = "添加失败";		}										$userid= getUserId();		$_TEMPLATE->assign("hosp",$hosp);				$user_name = $_DB->get_one("select `name` from admin_user where id= $userid");				$hosp = getHospital();				$sql = "select * from irpt_cost_ad_data  order by id desc limit 10";		$dbs= $_DB->get_all($sql);		$_TEMPLATE->assign("dbs",$dbs);		$_TEMPLATE->assign("hosp",$hosp);		$_TEMPLATE->assign("msg",$msg);		$_TEMPLATE->assign("date",date("Y-m-d",strtotime("-1 day")));			$_TEMPLATE->assign("user_name",$user_name);		$_TEMPLATE->display("ad/cost_add_data.html");					}
			

	/**
	 * 添加广告方法
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */	
	function addcost_action()
	{
		global $_DB,$_TEMPLATE;
		
		//get the parameter
		$hospital = trim(get_data("hospital"));
		$begin_date    = trim(get_data("begin_date"));
		$end_date    = trim(get_data("end_date"));
		$ad_site = trim(get_data("ad_site"));
		$ad_site = trim(get_data("ad_site"));
		$ad_name = trim(get_data("ad_name"));
		$cost = trim(get_data("cost"));
		$memo = trim(get_data("memo"));				$bingzhong = trim(get_data("bingzhong"));		$diyu = trim(get_data("diyu"));		$tongji = trim(get_data("tongji"));
		$ad_service=trim(get_data("ad_service"));
		$userid= getUserId();
		$exitsd ='0';
	    if($exitsd>0){//the data exits
			
			$_TEMPLATE->assign("msg","数据已经存在，某个网站的某个渠道一条只能存在一条消费数据！");
			$_TEMPLATE->assign("hospital",$hospital);
			$_TEMPLATE->assign("date_cost",$date_cost);
			$_TEMPLATE->assign("website",$website);
			$_TEMPLATE->assign("media",$media);
			$_TEMPLATE->assign("cost",$cost);
			$_TEMPLATE->assign("count_click",$count_click);
			$_TEMPLATE->assign("memo",$memo);			$_TEMPLATE->assign("tongji",$tongji);
			$_TEMPLATE->assign("diyu",$diyu);			$_TEMPLATE->assign("bingzhong",$bingzhong);
			$this->addcost();
		}else{
			$add_user=$_DB->get_one("select `name` from admin_user where id=$userid");
			$hospital_name=$_DB->get_one("select `name` from irpt_hospital where id=$hospital");
		
			$sql = "insert into irpt_cost_ad(id_hospital,hospital_name,ad_name,fee,begin_date,end_date,memo,add_time,ad_site,add_username,ad_service,bingzhong,diyu,tongji) values('$hospital','$hospital_name','$ad_name','$cost',unix_timestamp('$begin_date 00:00:00'),unix_timestamp('$end_date 23:59:59'),'$memo',unix_timestamp(),'$ad_site','$add_user','$ad_service','$bingzhong','$diyu','$tongji')";
			
			$_DB->query($sql);
			
			$_TEMPLATE->assign("msg","添加成功！");
			$this->addcost();
		}
		
		
	
	}
	/**
	 * 删除广告
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function costdelete_action()
	{
		global $_TEMPLATE,$_DB;
		$id=get_data('id','i');			
		
		$hosp = getHospital();
		$wheres = " 1=1  and ( 1>1 ";
		foreach($hosp as $ahospid)
		{
			$wheres .=" or id_hospital=".$ahospid["id_hospital"];
		}
		$wheres .=")";
		$sql = "delete from irpt_cost_ad where id=$id and ($wheres)";
		$_DB->query($sql);
		$_TEMPLATE->assign("msg","数据成功删除！");
		$this->addcost();
	
	}		/**	 * 删除广告	 +----------------------------------------------------------	 * Time:2011-11-11	 +----------------------------------------------------------	 */	function cost_data_delete_action()	{		global $_TEMPLATE,$_DB;		$id=get_data('id','i');							$hosp = getHospital();		$wheres = " 1=1  and ( 1>1 ";		foreach($hosp as $ahospid)		{			$wheres .=" or id_hospital=".$ahospid["id_hospital"];		}		$wheres .=")";		$sql = "delete from irpt_cost_ad_data where id=$id and ($wheres)";		$_DB->query($sql);		$_TEMPLATE->assign("msg","数据成功删除！");		$this->addcost_data();		}
	/**
	 * 合作信息
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function ads()
	{
		global $_TEMPLATE,$_DB;
		$userid= getUserId();
		$user_name = $_DB->get_one("select `name` from admin_user where id= $userid");
		$hosp = getHospital();
		$wheres = " 1=1  and ( 1>1 ";
		foreach($hosp as $ahospid)
		{
			$wheres .=" or id_hospital=".$ahospid["id_hospital"];
		}
		$wheres .=")";
		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->display("ad/ads.html");
	
	}
	/**
	 * 合作信息查询方法
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function ads_query()
	{
		global $_TEMPLATE,$_DB;
		$hosp = getHospital();
		$hospid=get_data('hospital','i');	
		$conditins = "";
		$conditins .= "&id_hospital=".$hospid;
		$_TEMPLATE->assign("id_hospital",$hospid);
		$userid= getUserId();
		$pagenavigate_sql = "select *,from_unixtime(add_time,'%Y-%m-%d') as addtime,from_unixtime(begin_date,'%Y-%m-%d') as begins,from_unixtime(end_date,'%Y-%m-%d') as ends from irpt_cost_ad where  id_hospital ='$hospid'  order by id desc limit 30";
		$data = $_DB->get_all($pagenavigate_sql);
		$_TEMPLATE->assign("hosp",$hosp);
		$_TEMPLATE->assign("pagination_rs",$data);
		$_TEMPLATE->assign("hospid",$hospid);
		$_TEMPLATE->assign("datac",count($data));
		$_TEMPLATE->display("ad/ads.html");
	
	}
}

?>