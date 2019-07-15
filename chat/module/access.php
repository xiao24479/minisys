<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");
class access extends cls_base{
	
	
	var $db;
	
	var $template;
	
	// init
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;

		cuckhid(get_hid());
	}

	/**
	 * 添加接口
	 +----------------------------------------------------------
	 * Time:2017-12-22
	 +----------------------------------------------------------
	 */
	public function add_access(){
		$hid=get_hid();
		
		$sql="select id,fame from gossip_access where hos_id=$hid and style=1";
		$robot_list=$this->db->get_all($sql);
		
		$this->template->assign('robot_list',$robot_list);
		make_json_result($this->template->fetch("access/add_access.html"), '', array());
	}
	
	
	/**
	 * 查询对应的机器人
	 */
	public function select_robot(){
		$hid=get_hid();
		$style=get_data('style');
		
		$sql="SELECT id,fame FROM gossip_access where hos_id='{$hid}' and style='{$style}'";
		$data=$this->db->get_all($sql);
		
		$this->template->assign('data',$data);
		make_json_result($this->template->fetch("access/select_robot.html"), '', array());
	}
	

	/**
	 * 字典创建数据表方法
	 */
	
	public function createTable($aid,$robot){
		
		$data=array();
		//创建字典管理表
		$answer_table_name="wisdom_answer_".$aid;
		$sql="CREATE TABLE `".$answer_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `noun` varchar(100) DEFAULT NULL COMMENT '名词',
			  `verb` varchar(100) DEFAULT NULL COMMENT '动词',
			  `doubt` varchar(100) DEFAULT NULL COMMENT '疑问词',
			  `genre` int(2) NOT NULL COMMENT '分类： 1问候词类 2中文词类 3\r\n\r\n关联词类',
			  `consult` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '咨询量',
			  `score` int(11) NOT NULL DEFAULT '0' COMMENT '分值',
			  `answer` varchar(200) NOT NULL COMMENT '问题答案',
			  `addTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
			  `editTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
			  PRIMARY KEY (`id`),
			  KEY `noun` (`noun`) USING BTREE,
			  KEY `verb` (`verb`) USING BTREE,
			  KEY `doubt` (`doubt`) USING BTREE,
			  KEY `genre` (`genre`) USING BTREE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['indicate']=$answer_table_name;

		//创建访客表
		$vistors_table_name="gossip_vistors_".$aid;
		$sql="CREATE TABLE `".$vistors_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `hos_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医院ID',
			  `client_id` varchar(200) DEFAULT NULL COMMENT '用户Id',
			  `doctor_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医生id',
			  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '初次客服id 0是机器人 大于0是客服',
			  `admin_name` varchar(30) DEFAULT NULL COMMENT '客服名称',
			  `collect` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否收藏 0否 1是',
			  `openId` varchar(255) DEFAULT NULL,
			  `only` varchar(32) DEFAULT NULL COMMENT '微信小程序中用户生成的唯一码',
			  `visitTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始访问时间',
			  `ip` varchar(30) DEFAULT NULL COMMENT '本次访问IP地址',
			  `country` varchar(30) DEFAULT NULL COMMENT '国家',
			  `area` varchar(30) DEFAULT NULL COMMENT '区域',
			  `region` varchar(30) DEFAULT NULL COMMENT '省份',
			  `city` varchar(30) DEFAULT NULL COMMENT '地市',
			  `tycoon` varchar(30) DEFAULT NULL COMMENT '网络服务商',
			  `os` varchar(100) DEFAULT NULL COMMENT '操作系统',
			  `browser` varchar(100) DEFAULT NULL COMMENT '浏览器类型',
			  `lang` varchar(30) DEFAULT NULL COMMENT '浏览器语言',
			  `guest` varchar(30) DEFAULT NULL COMMENT '访客名称',
			  `mold` int(2) unsigned DEFAULT '0' COMMENT '服务类型 1小程序 2公众号 3web 4app 5移动端',
			  `symbol` varchar(200) DEFAULT NULL COMMENT '来源',
			  `keyword` varchar(60) NOT NULL COMMENT '关键词',
			  `firstTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最初访问时间',
			  `lastTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次访问时间',
			  `visits` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '累计访问次数',
			  `is_delete` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '删除状态 0使用 1删除',
			  `type_name` varchar(20) COMMENT '分类名字',
			  PRIMARY KEY (`id`),
			  KEY `lastTime` (`lastTime`),
			  KEY `id` (`id`),
			  KEY `hos_id` (`hos_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			
		$this->db->query($sql);
		$data['caller']=$vistors_table_name;

		//创建消息表
		$msg_table_name="gossip_msg_".$aid;
		$sql="CREATE TABLE `".$msg_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `type` int(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1访客 2机器人 3客服',
			  `hos_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医院ID',
			  `vistor_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访客Id',
			  `obtain_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问题ID',
			  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '客服id',
			  `content` text NOT NULL COMMENT '聊天内容',
			  `createTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
			   `status` int(11) NOT NULL COMMENT '是否阅读',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['capacity']=$msg_table_name;
		
		//创建问题表
		$ask_table_name="wisdom_ask_".$aid;
		$sql="CREATE TABLE `".$ask_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `question` varchar(200) DEFAULT NULL COMMENT '问题',
			  `answer` varchar(200) DEFAULT NULL COMMENT '答案',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['corpus']=$ask_table_name;
		
		/**
                        创建完数据表返回接口
		 */
		if($robot>0){
				$old_table_name='wisdom_answer_'.$robot;
				$sql="insert into $answer_table_name(noun,verb,doubt,genre,answer,addTime) select noun,verb,doubt,genre,answer,addTime from $old_table_name";
				$this->db->query($sql);
		}
		$data['interface']='/chat/main.php?m=qqchat&a=chatbot&accessid='.$aid;
		return $data;
	}
	
	/**
	 * 问诊创建数据表方法
	 */
	
	public function foundTable($aid,$robot){
	
		$data=array();
		//创建问诊管理表
		$answer_table_name="wisdom_answer_".$aid;
		$sql="CREATE TABLE `".$answer_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '问诊ID',
			  `groupid` int(11) unsigned NOT NULL DEFAULT '0',
			  `question` varchar(200) DEFAULT NULL COMMENT '问题语句',
			  `express` varchar(200) DEFAULT NULL COMMENT '校验1',
			  `proof` varchar(200) DEFAULT NULL COMMENT '校验2',
			  `nicety` varchar(200) DEFAULT NULL COMMENT '校验3',
			  `keyword` varchar(100) DEFAULT NULL COMMENT '关键词',
			  `answer` varchar(200) DEFAULT NULL COMMENT '肯定索引',
			  `reference` varchar(200) DEFAULT NULL COMMENT '否定索引',
			  `yesid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '肯定节点id',
			  `notid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '否定节点id',
			  `consult` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '咨询量',
			  `score` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分值',
			  `addTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
			  `editTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['indicate']=$answer_table_name;
		
		//创建访客表
		$vistors_table_name="gossip_vistors_".$aid;
		$sql="CREATE TABLE `".$vistors_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `hos_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医院ID',
			  `client_id` varchar(200) DEFAULT NULL COMMENT '用户Id',
			  `doctor_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医生id',
			  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '初次客服id 0是机器人 大于0是客服',
			  `admin_name` varchar(30) DEFAULT NULL COMMENT '客服名称',
			  `collect` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否收藏 0否 1是',
			  `openId` varchar(255) DEFAULT NULL,
			  `only` varchar(32) DEFAULT NULL COMMENT '微信小程序中用户生成的唯一码',
			  `visitTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '开始访问时间',
			  `ip` varchar(30) DEFAULT NULL COMMENT '本次访问IP地址',
			  `country` varchar(30) DEFAULT NULL COMMENT '国家',
			  `area` varchar(30) DEFAULT NULL COMMENT '区域',
			  `region` varchar(30) DEFAULT NULL COMMENT '省份',
			  `city` varchar(30) DEFAULT NULL COMMENT '地市',
			  `tycoon` varchar(30) DEFAULT NULL COMMENT '网络服务商',
			  `os` varchar(100) DEFAULT NULL COMMENT '操作系统',
			  `browser` varchar(100) DEFAULT NULL COMMENT '浏览器类型',
			  `lang` varchar(30) DEFAULT NULL COMMENT '浏览器语言',
			  `guest` varchar(30) DEFAULT NULL COMMENT '访客名称',
			  `mold` int(2) unsigned DEFAULT '0' COMMENT '服务类型 1小程序 2公众号 3web 4app 5移动端',
			  `symbol` varchar(200) DEFAULT NULL COMMENT '来源',
			  `keyword` varchar(60) NOT NULL COMMENT '关键词',
			  `firstTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最初访问时间',
			  `lastTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次访问时间',
			  `visits` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '累计访问次数',
			  `is_delete` int(2) unsigned NOT NULL DEFAULT '0' COMMENT '删除状态 0使用 1删除',
			  PRIMARY KEY (`id`),
			  KEY `lastTime` (`lastTime`),
			  KEY `id` (`id`),
			  KEY `hos_id` (`hos_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['caller']=$vistors_table_name;
		
		//创建消息表
		$msg_table_name="gossip_msg_".$aid;
		$sql="CREATE TABLE `".$msg_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `type` int(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1访客 2机器人 3客服',
			  `hos_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医院ID',
			  `vistor_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '访客Id',
			  `obtain_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '问题ID',
			  `admin_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '客服id',
			  `content` text NOT NULL COMMENT '聊天内容',
			  `createTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
			   `status` int(11) NOT NULL COMMENT '是否阅读',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['capacity']=$msg_table_name;
		
		//创建问题表
		$ask_table_name="wisdom_ask_".$aid;
		$sql="CREATE TABLE `".$ask_table_name."` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `question` varchar(200) DEFAULT NULL COMMENT '问题',
			  `answer` varchar(200) DEFAULT NULL COMMENT '答案',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		$this->db->query($sql);
		$data['corpus']=$ask_table_name;

		/**
		 创建完数据表返回接口
		 */
		if($robot>0){
			$old_table_name='wisdom_answer_'.$robot;
			$sql="insert into $answer_table_name(question,answer,express,proof,nicety,keyword,reference,yesid,notid,groupid,addTime) select question,answer,express,proof,nicety,keyword,reference,yesid,notid,groupid,addTime from $old_table_name";
			$this->db->query($sql);
		}
		$data['interface']='/chat/main.php?m=qqchat&a=chitchat&accessid='.$aid;
		return $data;
     }

     
	/**
	 * 添加机器人
	 +----------------------------------------------------------
	 * Time:2017-12-22
	 +----------------------------------------------------------
	 */
	function add_action(){
		$hid=get_hid();
		$fame=trim(get_data('fame'));
		$style=get_data('style');
		$sex=get_data('sex');
		$agetype=get_data('agetype');
		$robot=get_data('robot');
		$createTime=strtotime('now');
		
		if($fame==''){
			make_json_result('', '名称必填，请检查表单!', []);
			exit;
			
		}else{ 
			/**
			 * 查询是否存在重复机器名称
			 */
			$sql="select id from gossip_access where hos_id='{$hid}' and fame='{$fame}'";
			$id=$this->db->get_one($sql);
			
			if($id>0){
				make_json_result('', '机器名称相同，请机检查器人是否已经存在!', []);
				exit;
				
			}else{
				$sql="INSERT INTO `gossip_access`(`hos_id`,`fame`, `style`,`sex`, `agetype`, `createTime`) VALUES('".$hid."','".$fame."','".$style."','".$sex."','".$agetype."','".$createTime."')";
				$r=$this->db->query($sql);
				
				if($r){
					$id = mysql_insert_id();
					if(!$id){
						make_json_result('', '没有获取到最近插入的数据的ID!', ['result'=>0]);
						exit;
					}
					
					//创建数据表
					if($style==1){
						$block=$this->createTable($id,$robot);
					}elseif($style==2){
						$block=$this->foundTable($id,$robot);
					}
					
					$indicate=$block['indicate'];
					$caller=$block['caller'];
					$capacity=$block['capacity'];
					$corpus=$block['corpus'];
					$interface=$block['interface'];
					
					$sql = "update gossip_access set `indicate`='".$indicate."', `caller`='".$caller."', `capacity`='".$capacity."', `corpus`='".$corpus."', `interface`='{$interface}' where id = {$id}";
					$res = $this->db->query($sql);

					make_json_result(1, '添加成功!', []);
					exit;
					
				}else{
					make_json_result('', '添加失败!', []);
					exit;
				}

			}	
		} 
	}

	
	/**
	 * 接口列表
	 */
	public function access_list(){
		
		$hid=get_hid();
		
		$fame = get_data('fame') ? trim(get_data('fame')) : '';
		$sex = get_data('sex') ? get_data('sex') : '';
		$agetype = get_data('agetype') ? get_data('agetype') : '';
		$style = get_data('style') ? get_data('style') : '';
		
		$w=' hos_id ='.$hid;
		
		if($fame!=''){
			$w.= " and fame='{$fame}'";
		}
		
		if($sex!=''){
			$w.= " and sex='{$sex}'";
		}
		
		if($agetype!=''){
			$w.= " and agetype='{$agetype}'";
		}
		
		if($style!=''){
			$w.= " and style='{$style}'";
		}
		
		//查出索引总数
		$sql="SELECT count(id) as a FROM gossip_access where $w";
		
		$count=$this->db->get_one($sql);//总记录数数
		
		$parameter  = '';
		
		if($fame!=''){
			$parameter .= '&fame='.$fame;
		}
		
		if($sex!=''){
			$parameter .= '&sex='.$sex;
		}
		
		if($agetype!=''){
			$parameter .= '&agetype='.$agetype;
		}
		
		if($style!=''){
			$parameter .= '&style='.$style;
		}
		
		if($add_time!=''){
			$parameter .= '&add_time='.$add_time;
		}
		if($end_time!=''){
			$parameter .= '&end_time='.$end_time;
		}
		
		$p = new Page($count,20,$parameter);
		
		$sql="SELECT * FROM gossip_access where $w ORDER BY id desc LIMIT ".$p->firstRow.",".$p->listRows." ;"; //按更新时间查询
		
		$data=$this->db->get_all($sql);
		
		$this->template->assign('fame',$fame);
		
		$this->template->assign('sex',$sex);
		
		$this->template->assign('agetype',$agetype);
		
		$this->template->assign('style',$style);
		
		$this->template->assign('data',$data);	
		
		$this->template->assign('page',$p->show('access_list'));
		
		make_json_result($this->template->fetch("access/access_list.html"), '', []);
		
	}
	
	
	/**
	 * 接口分配
	 */
	public function access_allot(){
		make_json_result($this->template->fetch("access/access_allot.html"), '', []);
	}
	
	
	
	/**
	
	* 用户列表
	
	+----------------------------------------------------------
	
	* Time:2012-3-7
	
	+----------------------------------------------------------
	
	*/
	
	function user_all(){
	
	
		//统计表记录
		$hid=get_hid();
		
		//查出索引总数
		$sql="SELECT count(ha.id) as a FROM irpt_hospital_admin as ha left join admin_user as u on ha.id_admin=u.id where u.is_delete!=1 and ha.id_hospital='{$hid}'";
		
		$count=$this->db->get_one($sql);//总记录数数
	
		$p = new Page($count,25);  //分页初始化
	
		$sql = "select u.id,u.name,u.login_name,u.email,u.cell_phone,u.is_delete from  irpt_hospital_admin as ha left join admin_user as u on ha.id_admin=u.id  where u.is_delete!=1 and ha.id_hospital='{$hid}' order by u.id desc LIMIT ".$p->firstRow.",".$p->listRows." ;";
	
		$data = $this->db->get_all($sql);
	
		$p->setConfig('header','个用户');  //分页样式定制
	
		$p->setConfig('prev', '上一组');
	
		$p->setConfig('next','下一组');
	
		$p->setRoll(0);
	
		$this->template->assign('page',$p->show('get_user_all')); //输出分页
	
		$this->template->assign('data',$data);
	
		make_json_result($this->template->fetch("access/user_all.html"), '', array());
	
	}
	
	/**
	 * 用户分配详情
	 */
	
	function user_detail(){
		
		$hid=get_hid();
	
		$user_id=get_data('user_id');
		$user_name = get_data('user_name');

		$sql = "select id,fame from gossip_access where hos_id='{$hid}' and id in(select access_id from gossip_user_access where user_id='{$user_id}' and hos_id='{$hid}')";
	
		$user_access = $this->db->get_all($sql);
		
		$sql = "select id,fame from gossip_access where hos_id='{$hid}' and id not in(select access_id from gossip_user_access where user_id='{$user_id}' and hos_id='{$hid}')";
	
		$access = $this->db->get_all($sql);
	
//		$this->template->assign('currentip',real_ip());
	
		$this->template->assign('access',$access);
	
		$this->template->assign('user_access',$user_access);
		
		$this->template->assign('hosp_id',$hid);
	
		$this->template->assign('user_id',$user_id);
		
		$this->template->assign('user_name',$user_name);
	
		make_json_result($this->template->fetch("access/user_detail.html"), '', array());
	
	}
	
	
	/**
	 * 保存分配的用户机器人
	 */
	function access_save(){
		
		$hosp_id=get_data('hid');
	
		$user_id=get_data('user_id');
	
		$ids=get_data('ids');
	
		$idarrary=split(',',$ids);

		$this->adduseraccess($hosp_id,$user_id,$idarrary);
	
		make_json_result('', 'ok', array());
	
	}
	
	private function adduseraccess($hosp_id,$user_id,$idArray){
	
		$sql = "select access_id from gossip_user_access where user_id='{$user_id}' and hos_id='{$hosp_id}'";
	
		$now_data = $this->db->get_col($sql);
	
		$deleteArray = array_diff($now_data, $idArray);
	
		$roleids='1>1';
	
		foreach($deleteArray as $aid){
	
			$roleids .=' or access_id='.$aid;
	
		}
	
		$sql  = 'delete from gossip_user_access where user_id='.$user_id.' and hos_id='.$hosp_id.' and ('.$roleids.')';
	
		$this->db->query($sql);
	
		$addArray = array_diff($idArray,$now_data);
	
		foreach($addArray as $aid){
	
			if(trim($aid)!=''){
	
				$sql  = "insert into gossip_user_access(user_id,hos_id,access_id) values('$user_id','$hosp_id','$aid')";
	
				$this->db->query($sql);
	
			}
	
		}
	
	}


	/**
	
	* 查询用户
	
	+----------------------------------------------------------
	
	* Time:2017-12-23
	
	+----------------------------------------------------------
	
	*/
	
	function selectUserName(){
	
		$userName = trim(get_data('userName'));
	
		if($userName!=''){
	
			$sql = "SELECT * FROM `admin_user` WHERE  is_delete!=1 and `name` LIKE '".$userName."%' ";
	
			$data = $this->db->get_all($sql);
	
			$count = count($data);
	
			$p = new Page($count,25);  //分页初始化
	
			$sql = "SELECT * FROM `admin_user` WHERE is_delete!=1 and `name` LIKE '".$userName."%' order by id desc LIMIT ".$p->firstRow.",".$p->listRows." ;";
	
			$data = $this->db->get_all($sql);
	
			$p->setConfig('header','个用户');  //分页样式定制
	
			$p->setConfig('prev', '上一组');
	
			$p->setConfig('next','下一组');
	
			$p->setRoll(0); //设定不显示页码
	
			$this->template->assign('username',$userName);
	
			$this->template->assign('page',$p->show('selectUserName')); //输出分页
	
			$this->template->assign('data',$data);
	
			make_json_result($this->template->fetch("access/user_all.htm"), '', array());
	
		}else{
	
			$this->access_allot();
	
		}

	}
	
	
	function del_robot() {
		$id = get_data('id');
		$arr = [];
		$arr[] = $answer_table_name="wisdom_answer_".$id;
		$arr[] = $vistors_table_name="gossip_vistors_".$id;
		$arr[] = $msg_table_name="gossip_msg_".$id;
		$arr[] = $ask_table_name="wisdom_ask_".$id;
		
		foreach($arr as $k => $v){
			$sql = "select * from {$v} limit 0,1";
			$data = $this->db->get_all($sql);
			
			if(!empty($data)){
				make_json_result($data, "{$v}表中有数据, 不能删除!", ['result'=>0]);
				exit;
			}
		}
		
		$sql = "delete from gossip_access where id = {$id}";
		$res = $this->db->query($sql);
		
		if(!$res){
			make_json_result('', "删除失败!", ['result'=>0]);
			exit;
		}
		
		$sql = "DROP TABLE {$answer_table_name}, {$vistors_table_name}, {$msg_table_name}, {$ask_table_name}";
		$res = $this->db->query($sql);
		
		if($res){
			make_json_result('', "删除完成!", ['result'=>1]);
		}else{
			make_json_result('', "删除失败!", ['result'=>0]);
		}
		
	}



}




