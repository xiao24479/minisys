<?php
require_once(APP_PATH."/include/libs.php");

class doctor extends cls_base
{
	protected $db;
	protected $template;
	protected $hid;
	
	function init()
	{
		global $_DB, $_TEMPLATE;

		$this->db       = $_DB;
		$this->template = $_TEMPLATE;
		$this->hid      = get_hid();

		// require_once(ROOT_PATH."_ext/auth_admin.php");
		require_once(ROOT_PATH."_ext/page.class.php");
	}
	
	function doctor_list(){
		make_json_result($this->template->fetch("doctor/doctor_list.html"), 1, array());
	}
	

	function doctor_action(){
		$name = trim(get_data('name'));
		
		$w = "";

		if($name!=''){
			$w .= " and name like '%{$name}%' ";
		}

		$count = $this->db->get_one("select count(*) from gossip_doctor where hid={$this->hid} $w");
		$p = new Page($count,20); 
		
		$sql = "select * from gossip_doctor where hid={$this->hid} $w order by add_time desc LIMIT ".$p->firstRow.",".$p->listRows.";";
		$all = $this->db->get_all($sql);

		foreach($all as $k => $v){
			$deps          = $this->db->get_all("select dep_name from gossip_doc_dep where hid={$this->hid} and doc_id={$v['id']}");
			$dep_name      = join(array_column($deps, 'dep_name'), '<br/>');

			$v['dep_name'] = $dep_name;
			$v['add_time'] = date('Y/m/d H:i:s', $v['add_time']);
			$all[$k]       = $v;
		}
		
		$this->template->assign('all',$all);
		$this->template->assign('page',$p->show('doctor_action'));
		
		make_json_result($this->template->fetch("doctor/doctor_action.html"),1,array());
	}
	
	

	function doctor_add(){
		$deps = $this->db->get_all("select * from gossip_department where hid={$this->hid} and top_id > 0");
		$this->template->assign('deps', $deps);
		make_json_result($this->template->fetch("doctor/doctor_add.html"),1,array());
	}
	
	
	function doctor_add_action(){
		$data = get_data('json_data');
		$error = [];
		$error[] = empty($data['pic']) ? '医生照片不能为空!' : '';
		$error[] = empty($data['name']) ? '医生名字不能为空!' : '';
		$error[] = empty($data['dep_id']) ? '医生科室不能为空!' : '';

		foreach($error as $i => $n){
			if($n != ''){
				make_json_result(0, $n, array()); exit;
			}
		}

		$dep_id = $data['dep_id'];
		$dep_name = $data['dep_name'];
		unset($data['dep_id']);
		unset($data['dep_name']);

		$fields = 'hid';
		$values = $this->hid;
		foreach($data as $k => $v){
			$fields .= ",{$k}";
			$values .= ",'{$v}'";
		}
		$fields .= ', add_time';
		$values .= ', '.time();		

		$this->db->query("insert into gossip_doctor({$fields}) values({$values})");
		$doc_id = mysql_insert_id();

		if(empty($doc_id)){
			make_json_result(0, '添加失败!', array()); exit;
		}

		foreach($dep_id as $key => $value){
			$this->db->query("insert into gossip_doc_dep(hid, doc_id, dep_id, dep_name) value({$this->hid}, '{$doc_id}', '{$value}', '{$dep_name[$key]}')");
		}

		make_json_result(1, "添加成功!", array());
	}
	
	

	function doctor_modify(){
		$id = get_data('id');
		$row = $this->db->get_row("select * from gossip_doctor where hid={$this->hid} and id = {$id}");
		$dep_id = $this->db->get_all("select * from gossip_doc_dep where hid={$this->hid} and doc_id = {$id}");
		$ids = array_column($dep_id, 'dep_id');
		$deps = $this->db->get_all("select * from gossip_department where hid={$this->hid} and top_id > 0");
		foreach($deps as $k => $v){
			if(in_array($v['id'], $ids)){
				$v['selected'] = 1;
				$deps[$k] = $v;
			}
		}
		$this->template->assign('row', $row);
		$this->template->assign('deps', $deps);
		make_json_result($this->template->fetch("doctor/doctor_modify.html"),1,array());
	}
	

	function doctor_modify_action(){
		$data    = get_data('json_data');
		$id      = get_data('id');
		$error   = [];

		$error[] = empty($data['pic']) ? '医生照片不能为空!' : '';
		$error[] = empty($data['name']) ? '医生名字不能为空!' : '';
		$error[] = empty($data['dep_id']) ? '医生科室不能为空!' : '';

		foreach($error as $i => $n){
			if($n != ''){
				make_json_result(0, $n, array()); exit;
			}
		}

		$dep_id = $data['dep_id'];
		$dep_name = $data['dep_name'];
		unset($data['dep_id']);
		unset($data['dep_name']);

		$set = "";
		foreach($data as $k => $v){
			$set .= "{$k}='{$v}',";
		}	
		$set = trim($set, ',');

		$this->db->query("BEGIN");

		$res1 = $this->db->query("update gossip_doctor set {$set} where hid={$this->hid} and id = {$id}");
		$res2 = $this->db->query("delete from gossip_doc_dep where hid={$this->hid} and doc_id = {$id}");

		if(!$res1 || !$res2){
			$this->db->query("ROLLBACK");
			make_json_result(0, "修改失败!", array()); exit;
		}

		foreach($dep_id as $key => $value){
			$res = $this->db->query("insert into gossip_doc_dep(hid, doc_id, dep_id, dep_name) value({$this->hid}, '{$id}', '{$value}', '{$dep_name[$key]}')");
			if(!$res){
				$this->db->query("ROLLBACK");
			make_json_result(0, "修改失败!", array()); exit;
			}
		}

		$this->db->query("COMMIT");
		make_json_result(1, "修改成功!", array());
	}


	function doctor_delete(){
		$id = get_data('id');
		
		$this->db->query("BEGIN");
		$res1 = $this->db->query("delete from gossip_doc_dep where hid={$this->hid} and doc_id = {$id}");
		$res2 = $this->db->query("delete from gossip_doctor where hid={$this->hid} and id = {$id}");

		if($res1 && $res2){
			$this->db->query("COMMIT");
			make_json_result(1, "删除成功!", array());
		}else{
			$this->db->query("ROLLBACK");
			make_json_result(0, "删除失败!", array());
		}
	}
	
	
	
}

?>