<?php
require_once(APP_PATH."/include/libs.php");

class department extends cls_base
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
	
	function dep_list(){
		make_json_result($this->template->fetch("department/dep_list.html"), 1, array());
	}
	

	function dep_action(){
		$name = trim(get_data('name'));
		
		$w = "";

		if($name!=''){
			$w .= " and name like '%{$name}%' ";
		}

		$count = $this->db->get_one("select count(*) from gossip_department where hid={$this->hid} and top_id = 0 $w");
		$p = new Page($count,20); 
		
		$sql = "select * from gossip_department where hid={$this->hid} and top_id = 0 $w order by add_time desc LIMIT ".$p->firstRow.",".$p->listRows.";";
		$all = $this->db->get_all($sql);

		foreach ($all as $key => $value) {
			$all[$key]['add_time'] = date('Y/m/d H:i:s', $value['add_time']);
			$childs = $this->db->get_all("select * from gossip_department where hid={$this->hid} and top_id = ".$value['id']);
			if(!empty($childs)){
				$all[$key]['childs'] = $childs;
			}
		}
		
		$this->template->assign('all',$all);
		$this->template->assign('page',$p->show('dep_action'));
		make_json_result($this->template->fetch("department/dep_action.html"),1,array());
	}
	
	
	function dep_add(){
		$top_dep = $this->db->get_all("select * from gossip_department where hid={$this->hid} and top_id = 0");
		$this->template->assign('top_dep',$top_dep);
		make_json_result($this->template->fetch("department/dep_add.html"),1,array());
	}
	
	
	function dep_add_action(){
		$data     = get_data('json_data');
		$add_time = time();
		$dep_name = $this->db->get_one("select name from gossip_department where hid={$this->hid} and name = '{$data['name']}'");

		$error = [];
		$error[] = empty($data['name']) ? '科室名字不能为空!' : '';
		$error[] = !empty($dep_name) ? '科室名字已存在!' : '';
		$error[] = !is_numeric($data['dep_order']) ? '预约量必须为数字!' : '';


		foreach($error as $i => $n){
			if($n != ''){
				make_json_result(0, $n, array()); exit;
			}
		}	

		foreach($data as $k => $v){
			$data[$k] = trim($v);
		}

		$res = $this->db->query("insert into gossip_department(hid, name, top_id, add_time, dep_order) 
								value({$this->hid}, '{$data['name']}', {$data['top_id']}, {$add_time}, {$data['dep_order']})");
		$msg = $res ? '添加成功' : '添加失败';
		make_json_result($res, $msg, array());
	}
	
	

	function dep_modify(){
		$id = get_data('id');
		$row = $this->db->get_row("select * from gossip_department where hid={$this->hid} and id = {$id}");
		$top_dep = $this->db->get_all("select * from gossip_department where hid={$this->hid} and top_id = 0");

		$this->template->assign('top_dep',$top_dep);
		$this->template->assign('row', $row);
		make_json_result($this->template->fetch("department/dep_modify.html"),1,array());
	}
	

	function dep_modify_action(){
		$data    = get_data('json_data');
		$id      = get_data('id');

		if($data['top_id'] > 0){
			$one = $this->db->get_one("select id from gossip_department where hid={$this->hid} and top_id = {$id}");
			if(!empty($one)){
				make_json_result(0, '该科室下有子科室,不能改变层级!', array()); exit;
			}
		}

		$dep_name = $this->db->get_one("select name, top_id from gossip_department where hid={$this->hid} and name = '{$data['name']}' and id != {$id}");

		$error   = [];
		$error[] = empty($data['name']) ? '科室名字不能为空!' : '';
		$error[] = !empty($dep_name) ? '科室名字已存在!' : '';
		$error[] = !is_numeric($data['dep_order']) ? '预约量必须为数字!' : '';

		foreach($error as $i => $n){
			if($n != ''){
				make_json_result(0, $n, array()); exit;
			}
		}
		foreach($data as $k => $v){
			$data[$k] = trim($v);
		}

		$this->db->query("BEGIN");
		$res1 = $this->db->query("update gossip_department set name='{$data['name']}', top_id={$data['top_id']}, dep_order={$data['dep_order']} 
								where hid={$this->hid} and id = {$id}");

		$res2 = $this->db->query("update gossip_doc_dep set dep_name='{$data['name']}' 
								where hid={$this->hid} and dep_id = {$id}");

		if($res1 && $res2){
			$msg = '修改成功';
			$this->db->query("COMMIT");
		}else{
			$msg = '修改失败';
			$this->db->query("ROLLBACK");
		}

		make_json_result($res1, $msg, array());
	}


	function dep_delete(){
		$id = get_data('id');

		$one = $this->db->get_one("select id from gossip_department where hid={$this->hid} and top_id = {$id}");
		if(!empty($one)){
			make_json_result(0, '该科室下有子科室, 不能删除!', array());exit;
		}

		$this->db->query("BEGIN");
		$res1 = $this->db->query("delete from gossip_doc_dep where hid={$this->hid} and dep_id = {$id}");
		$res2 = $this->db->query("delete from gossip_department where hid={$this->hid} and id = {$id}");

		if($res1 && $res2){
			$msg = '删除成功';
			$this->db->query("COMMIT");
		}else{
			$msg = '删除失败';
			$this->db->query("ROLLBACK");
		}
		make_json_result($res1, $msg, array());
	}
	
	function change_hot() {
		$id = get_data('id');
		$num = get_data('hot');
		if(!is_numeric($num) || $num>1 || $num<0){
			make_json_result(0, '非法数值!', array());exit;
		}
		$is_hot = (int)!$num;
		$res = $this->db->query("update gossip_department set is_hot = {$is_hot} where hid={$this->hid} and id = {$id}");
		$msg = $res ? '修改成功' : '修改失败';
		make_json_result($res, $msg, array());
	}	
	
}

?>