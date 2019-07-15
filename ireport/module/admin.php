<?php
require_once(APP_PATH."/include/libs.php");
/**
 * 医院系统管理
 +----------------------------------------------------------
 * @author 围剿
 * Time:2011-11-11
 +----------------------------------------------------------
 */
class admin extends cls_base
{
	
	function hospital_modify($id){
		global $_TEMPLATE,$_DB;

			$id = get_data('id');
			
			$sql = "SELECT *  FROM  `irpt_hospital` WHERE id='{$id}' ";
			$row = $_DB->get_row($sql);	
			$_TEMPLATE->assign("data",$row);
			
			$groups=$_DB->get_all("select id,name,name_cn from irpt_group");
			$_TEMPLATE->assign("groups",$groups);
			
			$hosts=$_DB->get_all("SELECT *  FROM  `irpt_hospital` where  top_id = 0 ");
			$_TEMPLATE->assign("hosts",$hosts);
			
			make_json_result($_TEMPLATE->fetch("admin/hospital_modify.html"), '', array());
	}
		
	function hospital_modify_action(){
		global $_TEMPLATE,$_DB;
		
		$id = get_data('id');
		$top_id = get_data('top_id');
		$name    = trim(get_data("name"));
		$type = get_data('type');
		$top_id = get_data('top_id');
		$website = trim(get_data("website"));
		$province = trim(get_data("province"));
		$swt_user = trim(get_data('swt_user'));
		$swt_passwd = trim(get_data('swt_passwd'));
		$swt_host = trim(get_data('swt_host'));
		$swt_db = trim(get_data('swt_db'));	
		$city = trim(get_data("city"));
		$memo = trim(get_data("memo"));
		$id_group = trim(get_data("id_group"));	
		$department = trim(get_data("department"));
		$update_time = time();
		
		$sql = "UPDATE `irpt_hospital` SET 					
							`id_group`='{$id_group}',`type`='{$type}',`top_id`='{$top_id}',`name`='{$name}',
							`swt_user`='{$swt_user}',`swt_passwd`='{$swt_passwd}',`swt_host`='{$swt_host}',
							`swt_db`='{$swt_db}',`website`='{$website}',`province`='{$province}',`city`='{$city}',`memo`= '{$memo}',
							`department`='{$department}', `update_time`='{$update_time}'  
				WHERE  `id` = {$id};";
		$res = $_DB->query($sql);
		
		if(!$res){
			make_json_result('', '修改医院失败', []);
			exit;
		}
		
		$sql = "update irpt_hospital_admin set hospital_name = '{$name}' where id_hospital = {$id}";
		$res = $_DB->query($sql);
		
		if($res){
			$this->hospitals();
		}else{
			make_json_result('', '用户医院绑定表更新失败', []);
			exit;
		}
		
	}
	
	
	
	/**
	 * 初始化
	 +----------------------------------------------------------
	 * (non-PHPdoc)
	 * @see _frame/cls_base::init()
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function init()
	{
		require_once(ROOT_PATH."_ext/auth_admin.php");
		require_once(ROOT_PATH."_ext/page.class.php");
	}	
	/**
	 * 集团管理
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function groups(){
		global $_TEMPLATE,$_DB;
		$data = $_DB->get_all("select * from irpt_group order by id desc");
		$_TEMPLATE->assign("data",$data);
		$_TEMPLATE->assign("datac",count($data));
		make_json_result($_TEMPLATE->fetch("admin/groups.html"), '1', array());
	}
	/**
	 * 添加集团
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function group_add(){
		global $_TEMPLATE;
		make_json_result($_TEMPLATE->fetch("admin/group_add.html"), '', array());
	}
	
	/**
	 * 删除集团
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function group_delete(){
		global $_TEMPLATE,$_DB;
		$id= trim(get_data("id"));
		
		if(!$id) die("非法操作");
		$hosc = $_DB->get_one("select count(*) from irpt_hospital where id_group=$id");
		if($hosc>0) die("不能操作，该机构下已经有医院信息！");
		else
		$_DB->query("delete from irpt_group where id=$id");
		
		$this->groups();
	}
	/**
	 * 集团添加方法
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function group_add_action()
	{
		global $_DB,$_TEMPLATE;
		$name    = trim(get_data("name"));
		$name_cn = trim(get_data("name_cn"));
		$self_phone = trim(get_data("self_phone"));
		$website = trim(get_data("website"));
		$phone = trim(get_data("phone"));
		$contact = trim(get_data("contact"));
		$memo = trim(get_data("memo"));
		
		$sql = "insert into irpt_group(`name`,website,phone,contact,self_phone,memo,name_cn) values('$name','$website','$phone','$contact','$self_phone','$memo','$name_cn')";
		$_DB->query($sql);
		
		$this->groups();
	
	}
	/**
	 * 用户查找绑定
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function bands_query()
	{
		global $_TEMPLATE,$_DB;
		

		
		$_TEMPLATE->display("admin/bands_query.html");
	}	
	/**
	 *  用户查找绑定查询
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function bands_select()
	{
		global $_TEMPLATE,$_DB;
		$admin_name = get_data('adminname');
		$admin_id = get_data('adminid');
		$sql = "SELECT count(*) FROM `irpt_hospital_admin` WHERE `admin_name` LIKE '%".$admin_name."%' ";
		$count=$_DB->get_one($sql);
		
		if($count!=''){
			$p = new Page($count,20);  //分页初始化
			$sql = "SELECT * FROM `irpt_hospital_admin` WHERE `admin_name` LIKE '%".$admin_name."%' ORDER BY  `irpt_hospital_admin`.`is_delete` ASC  LIMIT ".$p->firstRow.",".$p->listRows.";";
			$data = $_DB->get_all($sql);
			//列出所有查找到的用户名ID和登录名
			
			
			$sql = "select distinct  `admin_user`.`id`,`admin_user`.`name`,`admin_user`.`login_name` from `admin_user`,`irpt_hospital_admin` where `irpt_hospital_admin`.`id_admin` = `admin_user`.`id` and `irpt_hospital_admin`.`admin_name`  LIKE '%".$admin_name."%' ORDER BY  `admin_user`.`name` ASC ";
			$admin_all=$_DB->get_all($sql);
			$_TEMPLATE->assign("admin_all",$admin_all );
			$_TEMPLATE->assign("adminid",$admin_id );
			$_TEMPLATE->assign("adminname",$admin_name );
			$_TEMPLATE->assign("data",$data);
			$_TEMPLATE->assign('page',$p->show('bands_select')); //输出分页 
		}
		else 
			$_TEMPLATE->assign('page','没有记录'); //输出分页 
		make_json_result($_TEMPLATE->fetch("admin/admin_list.html"),'',array());
		
		
	}
	/**
	 * 用户查找绑定功能
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function bandsadd_action2()
	{
		global $_TEMPLATE,$_DB;
		$users = trim(get_data('userid'));
		$hospital_id=trim(get_data('hospitalid'));
		if($hospital_id==0 ||$users ==0)  
			die("医院和账号必须得选择！");
		/**
		 * 做插入
		 */
		$theHospital = $_DB->get_row("select hosp.*,groups.name_cn as group_cn,groups.name as group_name from irpt_hospital as hosp,irpt_group as groups where hosp.id_group=groups.id and hosp.id='$hospital_id'");
		$group_name = $theHospital['group_name'];
		$hostpital_name = $theHospital['name'];
		$group_id = $theHospital['id_group'];
		$theUserName = $_DB->get_one("select name from admin_user where id='$users'");
		$sql = "insert into irpt_hospital_admin(id_group,id_hospital,id_admin,hospital_name,group_name,admin_name) values('$group_id','$hospital_id','$users','$hostpital_name','$group_name','$theUserName')";
		$_DB->query($sql);	 
		//结束插入 
	}
	function bandsadd_action()
	{
		global $_DB,$_TEMPLATE;
		//get the parameter
		$hospital_id    = trim(get_data("hospital_id"));
		$users = trim(get_data("users"));
		
		if($hospital_id==0 ||$users ==0)  die("医院和账号必须得选择！");
		
		
		$theHospital = $_DB->get_row("select hosp.*,groups.name_cn as group_cn,groups.name as group_name from irpt_hospital as hosp,irpt_group as groups where hosp.id_group=groups.id and hosp.id='$hospital_id'");
		$group_name = $theHospital['group_name'];
		$hostpital_name = $theHospital['name'];
		$group_id = $theHospital['id_group'];
		$theUserName = $_DB->get_one("select name from admin_user where id='$users'");
		$sql = "insert into irpt_hospital_admin(id_group,id_hospital,id_admin,hospital_name,group_name,admin_name) values('$group_id','$hospital_id','$users','$hostpital_name','$group_name','$theUserName')";
		$_DB->query($sql);
		
		//插入父級
		$top_id = $theHospital['top_id'];
		$check = $_DB->get_row("select id_admin from irpt_hospital_admin where id_admin='$users' and id_hospital = '$top_id'");
		if($check==false){
			$theHospital = $_DB->get_row("select name from irpt_hospital where id='$top_id'"); 
			$hostpital_name = $theHospital['name']; 
			$sql = "insert into irpt_hospital_admin(id_group,id_hospital,id_admin,hospital_name,group_name,admin_name,is_delete) values('$group_id','$top_id','$users','$hostpital_name','$group_name','$theUserName','2')";
			$_DB->query($sql);	
		}
		
		$this->bands();
	
	}
	/**
	 * 医院列表
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function hospital_list()
	{
		global $_TEMPLATE,$_DB;
		$adminid = get_data('adminid');
		$sql = "SELECT hospital_name  FROM `irpt_hospital_admin` WHERE `id_admin` ='".$adminid."';";
		$data = $_DB->get_all($sql);
		
		$and='';
		//只列出未添加的医院
		foreach($data as $key =>$value)
		{
			$and .=" AND `irpt_hospital`.`name` <> '{$value['hospital_name']}' ";
		} 
		$sql = "SELECT  distinct   irpt_hospital.id,irpt_group.name as name2,irpt_group.name_cn,irpt_hospital.name  FROM `irpt_hospital`,`irpt_group` WHERE `irpt_group`.`id`=`irpt_hospital`.`id_group` ".$and;
		$data = $_DB->get_all($sql);
		$_TEMPLATE->assign('hospitals',$data);
		
		make_json_result($_TEMPLATE->fetch("admin/hospital_list2.html"),'',array());
	}
	
	function hospitals(){
		global $_TEMPLATE,$_DB;
		$data = $_DB->get_all("select hosp.*,groups.name_cn as group_cn,groups.name as group_name from irpt_hospital as hosp,irpt_group as groups where hosp.id_group=groups.id");
		
		$_TEMPLATE->assign("data",$data);
		$_TEMPLATE->assign("datac",count($data));
		make_json_result($_TEMPLATE->fetch("admin/hospitals.html"), '', array());
	}
	/**
	 * 添加医院
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function hospital_add(){
		global $_TEMPLATE,$_DB;
		$groups=$_DB->get_all("select id,name,name_cn from irpt_group");
		$_TEMPLATE->assign("groups",$groups);
		
		$hosts=$_DB->get_all("SELECT *  FROM  `irpt_hospital` where  top_id = 0 ");
		$_TEMPLATE->assign("hosts",$hosts);
		
		make_json_result($_TEMPLATE->fetch("admin/hospital_add.html"), '', array());
	}
	/**
	 * 删除医院
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function hospital_delete(){
		global $_TEMPLATE,$_DB;
		$id= trim(get_data("id"));
		
		if(!$id) die("非法操作");
		
		$hosc = $_DB->get_one("select count(*) from irpt_hospital_admin where id_hospital=$id");
		if($hosc>0){
			make_json_result('', "不能操作，该机构下已经有账号绑定！", []);
			exit;
		}
		
		$child = $_DB->get_all("select id from irpt_hospital where top_id=$id");
		if(!empty($child)){
			make_json_result('', "不能操作，该机构下有子医院！", []);
			exit;
		}
		
		$_DB->query("delete from irpt_hospital where id=$id");
		$this->hospitals();
	}
	
	
	/**
	 * 添加医院方法
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function hospital_add_action()
	{
		global $_DB,$_TEMPLATE;
		
		$top_id = get_data('top_id');
		$name    = trim(get_data("name"));
		$type = get_data('type');
		$top_id = get_data('top_id');
		$website = trim(get_data("website"));
		$province = trim(get_data("province"));
		$swt_user = trim(get_data('swt_user'));
		$swt_passwd = trim(get_data('swt_passwd'));
		$swt_host = trim(get_data('swt_host'));
		$swt_db = trim(get_data('swt_db'));	
		$city = trim(get_data("city"));
		$memo = trim(get_data("memo"));
		$id_group = trim(get_data("id_group"));	
		$department = trim(get_data("department"));
		$add_time = time();
					
		$sql = "insert into irpt_hospital(`top_id`,`type`,id_group,`name`,`swt_user`,`swt_passwd`,`swt_host`,`swt_db`,province,city,website,memo, department, add_time)
				values('{$top_id}','{$type}','{$id_group}','$name','{$swt_user}','{$swt_passwd}','{$swt_host}','{$swt_db}','{$province}','$city','$website','$memo','{$department}', '{$add_time}')";
		$res = $_DB->query($sql);
		
		$this->hospitals();
	}
	
	
	/**
	 * 人员医院绑定
	 */	
	function bands(){
		global $_TEMPLATE;
		$_TEMPLATE->display("admin/bands.html");
	}
	/**
	 * 人员医院列表
	 */
	
	function bands_all(){
		
		global $_TEMPLATE,$_DB;
		$group_list = array_unique($_DB->get_col("select  name    from  irpt_group ")); 
		$_TEMPLATE->assign('group_list',$group_list);  //只显示集团名称
		$group_name = $group_list[0];  //取得默认医院下的网站
		$where=" and `irpt_group`.`name` = '{$group_name}'";
		$hospital_all=array_unique($_DB->get_col('select  irpt_hospital.name   from `irpt_group`,`irpt_hospital` where  `irpt_group`.`id` = `irpt_hospital`.`id_group`  '.$where));
		$_TEMPLATE->assign("hospital_list",$hospital_all);
		make_json_result($_TEMPLATE->fetch("admin/bands_All.html"),'',array()); 
		
		
	}

	
	/**
	 * 取得集团下面的医院
	 */
	function hospital_all()
	{
		global $_TEMPLATE,$_DB;
		$group_name = get_data('gn');
		
		$where = '';
		if(!empty($group_name)){
			$where=" and `irpt_group`.`name` = '{$group_name}'";
			$hospital_all=array_unique($_DB->get_col('select  irpt_hospital.name   from `irpt_group`,`irpt_hospital` where  `irpt_group`.`id` = `irpt_hospital`.`id_group`  '.$where));
			$_TEMPLATE->assign("hospital_list",$hospital_all);
			make_json_result($_TEMPLATE->fetch("admin/hospital_list.html"),'',array()); 
		}
		else{
			make_json_result($_TEMPLATE->fetch("admin/hospital_list.html"),'',array()); 
		}
	}
	/**
	 * 取得医院下面的人员
	 */
	
	function admin_all()
	{
		global $_TEMPLATE,$_DB;
		$hospital_name = get_data('hn');
		$where = '';
		if(!empty($hospital_name)){
			$where =" and `irpt_hospital`.`name` ='{$hospital_name}'; ";
			$sql = "SELECT `irpt_hospital_admin`.`admin_name` FROM `irpt_hospital`,`irpt_hospital_admin` WHERE `irpt_hospital`.`id` = `irpt_hospital_admin`.`id_hospital` ".$where;
			$admin_all = array_unique($_DB->get_col($sql));
			$_TEMPLATE->assign("admin_all",$admin_all);
			make_json_result($_TEMPLATE->fetch("admin/admin_all.html"),'',array()); 
		}
		else{
			make_json_result($_TEMPLATE->fetch("admin/admin_all.html"),'',array()); 
		}
		
	}
	/**
	 * 人员医院查询列表
	 */
	function select_bands()
	{
		global $_TEMPLATE,$_DB;
		
		$group_name = get_data('gn');
		$hospital_name = get_data('ha');

		$where=$and='';	
		if($group_name!='')
		{
			$and = $where=" and  `irpt_group`.`name` = '{$group_name}'";	
			$_TEMPLATE ->assign('gn',$group_name); //做模版突出显示
			//查询出此集团下面的医院  
			$hospital_all=array_unique($_DB->get_col('select  irpt_hospital.name   from `irpt_group`,`irpt_hospital` where  `irpt_group`.`id` = `irpt_hospital`.`id_group`  '.$and));
			$_TEMPLATE->assign("hospital_list",$hospital_all);
		}
		
		if($hospital_name!=''){
			$where .=$and=" and `irpt_hospital_admin`.`hospital_name` = '{$hospital_name}' ";
			
			$_TEMPLATE ->assign('ha',$hospital_name); //做模版突出显示
			//查询出此医院下面的人员  
			$admin_list =array_unique($_DB->get_col("SELECT `irpt_hospital_admin`.`admin_name` FROM `irpt_hospital`,`irpt_hospital_admin` WHERE `irpt_hospital`.`id` = `irpt_hospital_admin`.`id_hospital` ".$and)); 
			$_TEMPLATE->assign("admin_all",$admin_list);
		}

		$sql = "SELECT count(*) FROM `irpt_hospital_admin`, irpt_group WHERE `irpt_hospital_admin`.`id_group` = irpt_group.id ".$where."";
		$count=$_DB->get_one($sql);
		$p = new Page($count,20);  //分页初始化
		$data = $_DB->get_all("select  irpt_hospital_admin.*,irpt_group.name_cn  as groupname,admin_user.id as iid,admin_user.is_delete as isdelete from irpt_hospital_admin,irpt_group,admin_user   where irpt_hospital_admin.id_group=irpt_group .id  and admin_user.id=irpt_hospital_admin.id_admin   ".$where." ORDER BY  `irpt_hospital_admin`.`is_delete` ASC  LIMIT ".$p->firstRow.",".$p->listRows."  ;");
		$hospital_list =$_DB->get_col("select `irpt_hospital`.`name`  from  `irpt_group`,`irpt_hospital` where `irpt_group`.`id` = `irpt_hospital`.`id_group` ");
		$group_list = array_unique($_DB->get_col("select  name    from  irpt_group "));  //将所有的集团名字列出
		$_TEMPLATE->assign('group_list',$group_list);
		$_TEMPLATE->assign("data",$data);
		$_TEMPLATE->assign('page',$p->show('select_page')); //输出分页 
	 	make_json_result($_TEMPLATE->fetch("admin/bands_All.html"),'',array());  
	}
	/**
	 * 添加绑定
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function bands_add(){
		global $_TEMPLATE,$_DB;
		
		$users=$_DB->get_all("select id,`name`,login_name from admin_user order by id desc");
		
		$_TEMPLATE->assign("users",$users);
		
		$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
		$sql ="select ha.id_hospital,ha.hospital_name as name,ha.group_name as group_name,h.top_id from irpt_hospital_admin as ha,irpt_hospital as h where ha.id_admin='$userid' and ha.is_delete<>1 and ha.id_hospital = h.id  ORDER BY  ha.`hospital_name` ASC ";
		$hospital = $_DB->get_all($sql);
		 
		//医院
		$hospital_data = array();
		foreach($hospital as $hospital_temp){
			if($hospital_temp['top_id'] ==0 ){
				$check =0 ;
				foreach($hospital_data as $hospital_data_temp){
					if(strcmp($hospital_temp['name'],$hospital_data_temp['name']) == 0){
						$check =1;break;
					} 
				}
				if(empty($check)){
					$hospital_data[] = $hospital_temp;
				}
			}
		}   
		//科室
		foreach($hospital_data as $hospital_key => $hospital_temp){
			foreach($hospital as $hospital_temp_two){
				if($hospital_temp_two['top_id'] == $hospital_temp['id_hospital']){
					$hospital_data[$hospital_key]['chird'][] =$hospital_temp_two;
				}
			}
		}  
		$_TEMPLATE->assign("hospitals",$hospital_data);
		 
		
		$_TEMPLATE->display("admin/bands_add.html");
	}
	/**
	 * 删除绑定
	 +----------------------------------------------------------
	 * Time:2011-11-11
	 +----------------------------------------------------------
	 */
	function bands_delete(){
		global $_TEMPLATE,$_DB;
		$id= trim(get_data("id"));
		if(!$id) die("非法操作");
	
		$detail = $_DB->get_row("select * from irpt_hospital_admin where id=$id");
		
		$id_hospital=$detail["id_hospital"];
		$id_admin=$detail["id_admin"];
		
		$hosc = $_DB->get_one("select count(*) from irpt_patient where  channel_type=0 and id_hospital=$id_hospital and id_consult=$id_admin");
		if($hosc>0) 
			make_json_result("不能操作，该医院该咨询人员下已经有预约患者！",'',array());
		else{
			$_DB->query("delete from irpt_hospital_admin where id=$id");
			make_json_result("false",'',array());
		}
		 
	}
	/**
	 * 设定有效
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-29  Update: 2011-8-29
	 * ----------------------------------------------------------------------
	 */
	function yes_delete()
	{
		global $_DB;
		
		$sql = "UPDATE `irpt_hospital_admin` SET `is_delete` = '1' WHERE `irpt_hospital_admin`.`id` = ".get_data('id');
		$_DB->query($sql);
	}
	/**
	 * 设定失效
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-29  Update: 2011-8-29
	 * ----------------------------------------------------------------------
	 */
	function no_delete(){
		global $_DB;
		$sql = "UPDATE `irpt_hospital_admin` SET `is_delete` = '0' WHERE `irpt_hospital_admin`.`id` = ".get_data('id');
		$_DB->query($sql);
	}
	/**
	 * 设定非咨询
	 * ----------------------------------------------------------------------
	 * AddTime:2011-8-29  Update: 2011-8-29
	 * ----------------------------------------------------------------------
	 */
	function no_consult(){
		global $_DB;
		$sql = "UPDATE `irpt_hospital_admin` SET `is_delete` = '2' WHERE `irpt_hospital_admin`.`id` = ".get_data('id');
		$_DB->query($sql);
	}
	
	function no_c_delete(){
		global $_DB;
		$sql = "UPDATE `admin_user` SET `is_delete` = '1' WHERE `id` = ".get_data('id');
		$_DB->query($sql);
	}
	function yes_c_delete(){
		global $_DB;
		$sql = "UPDATE `admin_user` SET `is_delete` = '0' WHERE `id` = ".get_data('id');
		$_DB->query($sql);	
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
}

?>