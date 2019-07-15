<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");

class vistor_type extends cls_base{
	
	var $db;
	
	var $template;
	
	function init() {
		
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
		
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid());
	}


	function index() {
		$hid = get_hid();
		$aid = get_aid();
		
		$sql = "select count(*) from gossip_vistors_type where hid={$hid} and top_id = 0";
		$count = $this->db->get_one($sql);
		$page = new Page($count, 5);
		
		$sql = "select count(*) from gossip_vistors_{$aid} where type_name != ''";
		$all = $this->db->get_one($sql);
		
		$this->template->assign('str', $this->get_types());
		$this->template->assign('page', $page->show('index'));
		$this->template->assign('all', $all);
		make_json_result($this->template->fetch("vistor_type/index.html"), '', array());	
	}
	
	function add() {
		$hid = get_hid();
		$sql = "select * from gossip_vistors_type where hid={$hid} and top_id = 0 order by create_time desc";
		$types = $this->db->get_all($sql);

		$this->template->assign('types', $types);
		make_json_result($this->template->fetch("vistor_type/add.html"), '', array());	
	}
	
	
	function add_action() {
		$type_name = trim(get_data('type_name'));
		$top_id = trim(get_data('top_id'));
		$hid = get_hid();
		$create_time = time();
		
		if($type_name == ''){
			make_json_result('', '分类名不能为空', array());
			exit;
		}
		
		$sql = "select * from gossip_vistors_type where hid={$hid} and type_name = '{$type_name}'";
		$row = $this->db->get_row($sql);
		
		if($row){
			make_json_result('', '分类已存在, 请勿重复添加', array());
			exit;	
		}
		
		$sql = " insert into gossip_vistors_type(type_name, top_id, hid, create_time) values('{$type_name}', '{$top_id}', '{$hid}', '{$create_time}')";
		$res = $this->db->query($sql);
		$id = mysql_insert_id();
		
		if($res){
			
			if($top_id == 0){
				$class = " class='top_0' ";
			}else{
				$class = '';
			}
			
			$str .= "<tr {$class}>
						<td class='tags' onclick=\"get_list(1, {$id}, {$top_id}, '{$type_name}', this);\">{$type_name}</td>
					</tr>";
			
			make_json_result($str, '添加成功', array('top_id'=>$top_id, 'id'=>$id, 'type_name'=>$type_name));
				
		}else{
			make_json_result('', '添加失败', array());	
		}
	}
	
	
	function del() {
		$id = get_data('id');
		$hid = get_hid();
		
		$sql = "delete from gossip_vistors_type where hid = {$hid} and id = {$id}";
		$res = $this->db->query($sql);
		
		if($res){
			make_json_result('', '删除成功', array());	
		}else{
			make_json_result('', '删除失败', array());	
		}
	}
	
	
	function get_types($topid=0) {
		$hid = get_hid();
		$aid = get_aid();
		
		$sql = "select *, (select count(*) from gossip_vistors_{$aid} where type_name = t.type_name) as count from gossip_vistors_type as t where t.hid = {$hid} and t.top_id = {$topid} order by t.create_time desc";
		$all = $this->db->get_all($sql);
		
		foreach($all as $i => $n){
			$all[$i]['create_time'] = date('Y/m/d H:i:s', $n['create_time']);
		}
		
		$str = '';
		foreach($all as $k => $v){
			
				if($topid == 0){
					$class = " class='top_0' ";
					
					$sql = "select type_name from gossip_vistors_type where top_id = ".$v['id'];
					$arr = $this->db->get_all($sql);
					
					if(!empty($arr)){
						$values = '';
						
						foreach($arr as $key => $value){
							$values .= "'".$value['type_name']."',";
						}
						
						$values = trim($values, ',');
						
						$sql = "select count(*) from gossip_vistors_{$aid} where type_name in({$values})";
						$top_count = $this->db->get_one($sql);
						
					}
					
				}else{
					$class = '';
					$top_count = 0;
				}
				
				$count = $v['count'] + $top_count;
				$str .= "<tr $class>
							<td class='tags' onclick=\"get_list(1, '".$v['id']."', '".$v['top_id']."',  '".$v['type_name']."', this);\">".$v['type_name']." (".$count.")</td>
						</tr>";
				$str .= $this->get_types($v['id']);
		}
		return $str;		
	}

	
	function get_list() {
		$type_name = get_data('type_name');
		$top_id = get_data('top_id');
		$type_id = get_data('id');
		$hid = get_hid();
		$aid = get_aid();
		
		$vistors_t = "gossip_vistors_{$aid}";
		$msg_t = "gossip_msg_{$aid}";
		
		$w = "";
		
		if($type_name != ''){
			$w .= " where type_name = '{$type_name}' ";
		}else{
			$w .= " where type_name != '' ";
		}
		
		if($top_id == 0){
			$sql = "select type_name from gossip_vistors_type where top_id = {$type_id}";
			$arr = $this->db->get_all($sql);
			$arr[]['type_name'] = $type_name;
			
			if(!empty($arr)){
				$values = '';
				
				foreach($arr as $k => $v){
					$values .= "'".$v['type_name']."',";
				}
				
				$values = trim($values, ',');
				
				$w = " where type_name in({$values})";
				
			}
		}
		
		$sql = "select count(*) from {$vistors_t} {$w}";
		$count = $this->db->get_one($sql);
		$p = new Page($count, 20);
				
		$sql = "select *, 
					(select count(*) from {$msg_t} where vistor_id = v.id) as msg_count 
				from {$vistors_t} as v {$w} limit ".$p->firstRow.','.$p->listRows;
		$all = $this->db->get_all($sql);

		$this->template->assign('data', $all);		
		$this->template->assign('type_name', $type_name);
		$this->template->assign('page', $p->show('get_list', $type_id, $top_id, $type_name));
		make_json_result($this->template->fetch('vistor_type/list.html'), '', []);
	}
	
	
	function msg_detail() {
		$vid = get_data('id');
		$aid = get_aid();
		$hid = get_hid();
		$msg_t = "gossip_msg_{$aid}";
		$vistor_t = "gossip_vistors_{$aid}";

		$sql = "select *,
					(select name from admin_user where id = m.admin_id) as admin_name,  
					(select guest from {$vistor_t} where id = {$vid}) as vistor_name 
				from {$msg_t} as m where vistor_id = {$vid} ";
		$all = $this->db->get_all($sql);
		
		foreach($all as $k => $v){
			$all[$k]['createTime'] = date('Y/m/d H:i:s', $v['createTime']);
		}
		
		$this->template->assign('data', $all);
		$this->template->assign('vname', $vname);
		make_json_result($this->template->fetch('vistor_type/msg_detail.html'));
		
	}
	
	
	
}


?>