<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");
class vistor extends cls_base{
	
	var $db;
	
	var $template;
	
	// init
	
	function init() {
		
		
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
		
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid());
	}
	
	public function index(){
		
		$hid=get_hid();
		
		$aid=get_aid();
		
		$level=get_data('level');

		$region=get_data('region');
		
		$city=get_data('city');
		
		$date = get_data('date');
		
		$vistors_table_name="gossip_vistors_".$aid;
		
		$msg_table_name="gossip_msg_".$aid;
		
		if(empty($date)){
			$add_time=date('Y/m/01',strtotime("-1 day"));
			$end_time=date('Y/m/d',time());
		}else{
			$arr = explode('-', $date);
			$add_time = trim($arr[0]);
			$end_time = trim($arr[1]);
		}
		
		$w='v.hos_id ='.$hid;
		
		if($level!=''){
			if($level==1){
				$w.= " and m.num<=5";
			}elseif($level==2){
				$w.= " and 6<=m.num and m.num<=14";
			}elseif($level==3){
				$w.= " and 15<=m.num";
			}
		}
		
	    if($region!=''){
			$w.= " and v.region='{$region}'";
		}
		
		if($city!=''){
			$w.= " and v.city='{$city}'";
		}
		
		if($add_time!='' && $end_time!=''){
			$w.="  and v.lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');
		}
		
		//获得所有省份
		$sql="SELECT distinct region FROM $vistors_table_name where hos_id='{$hid}'";
		$region_list=$this->db->get_all($sql);
		
		//获得地市
		if($region!=''){
			$sql="SELECT distinct city FROM $vistors_table_name where region='{$region}' and hos_id='{$hid}'";
			$city_list=$this->db->get_all($sql);
			
		}else{
			$city_list=array();
		}

		//查出索引总数
		$sql="SELECT count(v.id) as a FROM $vistors_table_name as v left join (SELECT count(id) as num,vistor_id FROM $msg_table_name group by vistor_id) as m on v.id=m.vistor_id where $w";
		$count=$this->db->get_one($sql);//总记录数数
		
		$p = new Page($count,20);
		
		$sql="SELECT v.*,m.num FROM $vistors_table_name as v left join (SELECT count(id) as num,vistor_id FROM $msg_table_name group by vistor_id) as m on v.id=m.vistor_id where $w ORDER BY v.lastTime DESC LIMIT ".$p->firstRow.",".$p->listRows." ;"; //按更新时间查询
		
		$data=$this->db->get_all($sql);
		
		foreach($data as $k => $v){
			$sql1 = "select count(*) from $msg_table_name where vistor_id = ".$v['id']." and type = 1";
			$count = $this->db->get_one($sql1);
			
			$sql2 = "select admin_id from $msg_table_name where vistor_id = ".$v['id']." and admin_id > 0 group by admin_id";
			$admin_id = $this->db->get_one($sql2);

			if($admin_id != null){
				$sql3 = "select name from admin_user where id = ".$admin_id;
				$name = $this->db->get_one($sql3);				
			}else{
				$name = '';
			} 
			
			$data[$k]['v_count'] = $count;
			$data[$k]['admin_name'] = $name;
		}

		$this->template->assign('level',$level);
		
		$this->template->assign('region',$region);
		
		$this->template->assign('city',$city);

		$this->template->assign('add_time',$add_time);
		
		$this->template->assign('end_time',$end_time);
		
		$this->template->assign('region_list',$region_list);
		
		$this->template->assign('city_list',$city_list);
		
		$this->template->assign('data',$data);
		
		$this->template->assign('page',$p->show('index'));

		make_json_result($this->template->fetch("vistor/index.html"), '', array());
		
	}
	
	
	/**
	 * 查看对话信息
	 +----------------------------------------------------------
	 * Time:2017-12-21
	 +----------------------------------------------------------
	 */
	function check_vistor(){
		$id=get_data('id','i');
		$aid=get_aid();
		
		$vistors_table_name="gossip_vistors_".$aid;
		$msg_table_name="gossip_msg_".$aid;
		
		$sql="select m.type,m.content,m.createTime,u.name,v.id,v.region,v.city,v.guest from $msg_table_name as m 
		left join admin_user as u on m.admin_id=u.id
		left join $vistors_table_name as v on m.vistor_id=v.id where m.vistor_id=".$id." order by createTime ASC;";
		$data=$this->db->get_all($sql);
		
		$this->template->assign('data',$data);
		make_json_result($this->template->fetch("vistor/check_vistor.html"), '', array());
	}
	
	
	/**
	 * 查询省市
	 */
	public function select_city(){
		$hid=get_hid();
		$aid=get_aid();
		
		$vistors_table_name="gossip_vistors_".$aid;
		$region=get_data('region');
		
		$sql="SELECT distinct city FROM $vistors_table_name where region='{$region}' and hos_id='{$hid}'";
		$data=$this->db->get_all($sql);
		
		$this->template->assign('data',$data);
		make_json_result($this->template->fetch("vistor/select_city.html"), '', array());
	}
	
	
	/**
	 * 修改访客
	 */
	public function edit_vistor(){
		$id=get_data('id','i');
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
		$hid = get_hid();
		
		$sql="select * from $vistors_table_name where id=".$id;
		$data=$this->db->get_row($sql);

		$sql = "select * from gossip_vistors_type where hid = {$hid}";
		$types = $this->db->get_all($sql);

		$this->template->assign('data',$data);
		$this->template->assign('types',$types);
		make_json_result($this->template->fetch('vistor/edit_vistor.html'), '', array());
	}
	
	
	/**
	 * 更新访客信息
	 */
	
	public function updata_vistor(){

		$id=get_data('id','i');
		$type_name = get_data('type_name');
		$guest=get_data('e_guest');
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;

		$sql="UPDATE $vistors_table_name SET type_name = '{$type_name}', `guest`='".$guest."' WHERE id=".$id;
		$r=$this->db->query($sql);
		
		if($r){
			unset($_POST);
			make_json_result('<foot class="msg">修改成功!</foot>', '', array());
		}else{
			make_json_result('<foot class="msg">修改失败,请检查表单!</foot>', '', array());
		}
	}
	
	public function add_type() {
		$type_name = trim(get_data('type_name'));
		$hid = get_hid();
		$create_time = time();
		
		if($type_name == ''){
			make_json_result('', '分类名不能为空', array());
			exit;
		}
		
		$sql = "select * from gossip_vistors_type where type_name = '{$type_name}'";
		$row = $this->db->get_row($sql);
		
		if($row){
			make_json_result('', '分类已存在, 请勿重复添加', array());
			exit;	
		}
		
		$sql = " insert into gossip_vistors_type(type_name, hid, create_time) values('{$type_name}', '{$hid}', '{$create_time}')";
		$res = $this->db->query($sql);
		
		if($res){
			make_json_result("<option value='{$type_name}'>{$type_name}</option>", '添加成功', array());	
		}else{
			make_json_result('', '添加失败', array());	
		}
	}
	

}
