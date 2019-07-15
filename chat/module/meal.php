<?php
require_once(APP_PATH."/include/libs.php");

class meal extends cls_base
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
	
	function meal_list(){
		make_json_result($this->template->fetch("meal/meal_list.html"), 1, array());
	}
	

	function meal_action(){
		$title = trim(get_data('title'));
		
		$w = "";

		if($title!=''){
			$w .= " and title like '%{$title}%' ";
		}

		$count = $this->db->get_one("select count(*) from gossip_meal where hid={$this->hid} $w");
		$p = new Page($count,20); 
		
		$sql = "select * from gossip_meal where hid={$this->hid} $w order by is_discounts desc, add_time desc LIMIT ".$p->firstRow.",".$p->listRows.";";
		$all = $this->db->get_all($sql);

		foreach($all as $k => $v){
			$all[$k]['add_time']  = date('Y/m/d H:i:s', $v['add_time']);
			$count = $this->db->get_one("select count(*) from gossip_meal_detail where mid = ".$v['id']);
			$all[$k]['count'] = $count ?: 0;
		}
		
		$this->template->assign('all',$all);
		$this->template->assign('page',$p->show('meal_action'));
		make_json_result($this->template->fetch("meal/meal_action.html"),1,array());
	}
	

	function meal_add(){
		make_json_result($this->template->fetch("meal/meal_add.html"), 1, array());
	}
	
	
	function meal_add_action(){
		$data = get_data('json_data');
		$detail = $data['detail'];
		unset($data['detail']);

		$error = [];
		$error[] = empty($data['pic']) ? '套餐照片不能为空!' : '';
		$error[] = strpos($data['pic'], 'http') === false ? '套餐照片不能为空!' : '';
		$error[] = empty($data['title']) ? '套餐标题不能为空!' : '';
		$error[] = $data['sell_price'] >= 0 ? '' : '套餐售价不能为空!';
		$error[] = $data['original_price'] >= 0 ?  '' : '套餐原价不能为空!';
		$error[] = empty($data['stock']) ? '套餐库存不能为空!' : '';

		foreach($error as $i => $n){
			if($n != ''){
				make_json_result(0, $n, array()); exit;
			}
		}

		$fields = 'hid';
		$values = $this->hid;
		foreach($data as $k => $v){
			$fields .= ",{$k}";
			$values .= ",'{$v}'";
		}
		$fields .= ', add_time';
		$values .= ', '.time();		

		$res = $this->db->query("insert into gossip_meal({$fields}) values({$values})");
		$id = mysql_insert_id();

		if($id && !empty($detail)){
			$values = "";
			foreach($detail as $v){
				$values .= "({$id}, '{$v}'),";
			}
			$values = trim($values, ',');
			$this->db->query("insert into gossip_meal_detail(mid, picurl) values {$values}");
		}

		$msg = $res ? '添加成功' : '添加失败';
		make_json_result($res, $msg, array());
	}
	

	//判断标题是否重复
	function check_title() {
		$title = get_data('title');
		$id = get_data('id');

		$one = $this->db->get_one("select id from gossip_meal where hid={$this->hid} and title = '{$title}'");

		//有id时为修改模式,无id时为添加模式
		if($id){
			//有id时如果找到该标题, 而该标题所在的行, 是正在修改的行, 则为不重复
			$res = $one ? ($id==$one ? 1 : 0) : 1;
		}else{
			//无id时只要没有找到该标题, 则为不重复
			$res = $one ? 0 : 1;
		}

		make_json_result($res, 'ok', array());
	}


	function meal_modify(){
		$id = get_data('id');

		$row = $this->db->get_row("select * from gossip_meal where hid={$this->hid} and id = {$id}");
		$details = $this->db->get_all("select * from gossip_meal_detail where mid = {$id}");

		$this->template->assign('details', $details);
		$this->template->assign('row', $row);
		make_json_result($this->template->fetch("meal/meal_modify.html"),1,array());
	}
	

	function meal_modify_action(){
		$id     = get_data('id');
		$data   = get_data('json_data');
		$detail = $data['detail'];
		unset($data['detail']);

		$row_id = $this->db->get_one("select id from gossip_meal where hid = {$this->hid} and title = '{$data['title']}'");

		$error = [];
		$error[] = empty($data['pic']) ? '套餐照片不能为空!' : '';
		$error[] = strpos($data['pic'], 'http') === false ? '套餐照片不能为空!' : '';
		$error[] = empty($data['title']) ? '套餐标题不能为空!' : '';
		$error[] = $row_id && $row_id!=$id ? '套餐标题已存在!' : '';
		$error[] = $data['sell_price'] >= 0 ? '' : '套餐售价不能为空!';
		$error[] = $data['original_price'] >= 0 ?  '' : '套餐原价不能为空!';
		$error[] = empty($data['stock']) ? '套餐库存不能为空!' : '';

		foreach($error as $i => $n){
			if($n != ''){
				make_json_result(0, $n, array()); exit;
			}
		}

		$set = '';
		foreach($data as $k => $v){
			$set .= "{$k}='{$v}',";
		}
		$set = trim($set, ',');

		$this->db->query("BEGIN");
		$res1 = $this->db->query("update gossip_meal set {$set} where hid = {$this->hid} and id = {$id}");
		$res2 = $this->db->query("delete from gossip_meal_detail where mid = {$id}");
		$res3 = 1;
		
		if(!empty($detail)){
			$values = "";
			foreach($detail as $v){
				$values .= "({$id}, '{$v}'),";
			}
			$values = trim($values, ',');
			$res3 = $this->db->query("insert into gossip_meal_detail(mid, picurl) values {$values}");
		}

		if($res1 && $res2 && $res3){
			$this->db->query("COMMIT");
			make_json_result(1, '修改成功!', array());

		}else{
			$this->db->query("ROLLBACK");
			make_json_result(0, '修改失败!', array());
		}
	}


	function meal_delete(){
		$id = get_data('id');

		$picurls = $this->db->get_all("select picurl from gossip_meal_detail where mid={$id}");
		$pic = $this->db->get_all("select pic as picurl from gossip_meal where id = {$id}");
		$pic_arr = array_merge($picurls, $pic);

		foreach($pic_arr as $v){
			$host = $_SERVER['HTTP_HOST'];
			$filename = str_replace($host, ROOT_PATH, strstr($v['picurl'], $host));
			unlink($filename);
		}

		$this->db->query("BEGIN");
		$res1 = $this->db->query("delete from gossip_meal where hid={$this->hid} and id = {$id}");
		$res2 = $this->db->query("delete from gossip_meal_detail where mid = {$id}");

		if($res1 && $res2){
			$this->db->query("COMMIT");
			make_json_result(1, '删除成功!', array());

		}else{
			$this->db->query("ROLLBACK");
			make_json_result(0, '删除失败!', array());
		}
	}
	
	function change_dis() {
		$id = get_data('id');
		$num = get_data('is_dis');
		if(!is_numeric($num) || $num>1 || $num<0){
			make_json_result(0, '非法数值!', array());exit;
		}
		$is_discounts = (int)!$num;
		$res = $this->db->query("update gossip_meal set is_discounts = {$is_discounts} where hid={$this->hid} and id = {$id}");
		$msg = $res ? '修改成功' : '修改失败';
		make_json_result($res, $msg, array());
	}
	
}

?>