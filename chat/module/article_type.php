<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");

class article_type extends cls_base{
	
	var $db;
	
	var $template;
	
	function init() {
		
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
	}


	function index() {
		$hid = get_hid();
		$aid = get_aid();
		
		$sql = "select count(*) from gossip_article_type where hid={$hid} and top_id = 0";
		$count = $this->db->get_one($sql);
		$page = new Page($count, 5);
		
		$sql = "select count(*) from gossip_article where hid = {$hid} and tag != ''";
		$all = $this->db->get_one($sql);
		
		$this->template->assign('str', $this->get_types());
		$this->template->assign('page', $page->show('index'));
		$this->template->assign('all', $all);
		make_json_result($this->template->fetch("article_type/index.html"), '', array());	
	}
	
	function add() {
		$hid = get_hid();
		
		$sql = "select * from gossip_article_type where hid={$hid} and top_id = 0";
		$types = $this->db->get_all($sql);

		$this->template->assign('types', $types);
		make_json_result($this->template->fetch("article_type/add.html"), '', array());	
	}
	
	
	function add_action() {
		$type_name = trim(get_data('type_name'));
		$top_id = trim(get_data('top_id'));
		$hid = get_hid();
		$base64 = get_data('base64') ? get_data('base64') : '';
		$pic = '';
		
		if($type_name == ''){
			make_json_result('', '分类名不能为空', array());
			exit;
		}
		
		$sql = "select * from gossip_article_type where hid={$hid} and type_name = '{$type_name}'";
		$row = $this->db->get_row($sql);
		
		if($row){
			make_json_result('', '分类已存在, 请勿重复添加', array());
			exit;	
		}
		
		if($base64 != ''){
			$arr = explode(',', $base64);

			$base_dir = 'data/uploads/mini_program';
			if(!file_exists(ROOT_PATH.$base_dir)){
				mkdir(ROOT_PATH.$base_dir);
			}
			
			$hid_dir = $base_dir.'/'.$hid;
			if(!file_exists(ROOT_PATH.$hid_dir)){
				mkdir(ROOT_PATH.$hid_dir);
			}		

			$type_dir = $hid_dir.'/'.'type_img';
			if(!file_exists(ROOT_PATH.$type_dir)){
				mkdir(ROOT_PATH.$type_dir);
			}		
					
			$filename = date('YmdHis', time()).mt_rand(1000000, 9999999).'.jpeg';
			$filepath = $type_dir.'/'.$filename;
			
			$res = file_put_contents(ROOT_PATH.$filepath, base64_decode($arr[1]));
			
			if(!$res){
				make_json_result('', '上传图片时发生错误', array());
				exit;
				
			}else{
				$pic = 'http://'.$_SERVER['HTTP_HOST'].'/'.$filepath;
			}
			
		}
		
		$sql = " insert into gossip_article_type(type_name, top_id, hid, pic) values('{$type_name}', '{$top_id}', '{$hid}', '{$pic}')";
		$res = $this->db->query($sql);
		$id = mysql_insert_id();
		
		if($res){
			
			if($top_id == 0){
				$class = " class='top_0' ";
			}else{
				$class = '';
			}
			
			$str .= "<tr data-id='{$id}' {$class}>
						<td class='tags' onclick=\"get_list(1, {$id}, {$top_id}, '{$type_name}', this);\">
							<span class='del-type' onclick='delType({$id});'>×</span>
							<span class='type_name'>{$type_name}</span> (0)
							<i class='fa fa-edit edit-icon' onclick=\"editType({$id});\"></i>
						</td>
					</tr>";
			
			make_json_result($str, '添加成功', array('id'=>$id, 'top_id'=>$top_id, 'type_name'=>$type_name));
				
		}else{
			make_json_result('', '添加失败', array());	
		}
	}
	
	function edit() {
		$hid = get_hid();
		$id = get_data('id');
		
		$sql = "select * from gossip_article_type where hid={$hid} and id = {$id}";
		$data = $this->db->get_row($sql);

		$sql = "select * from gossip_article_type where hid={$hid} and top_id = 0";
		$types = $this->db->get_all($sql);
		
		$this->template->assign('types', $types);
		$this->template->assign('data', $data);
		make_json_result($this->template->fetch("article_type/edit.html"), '', array());	
		
	}
	
	function update() {
		$id = get_data('id');
		$hid = get_hid();
		$type_name = trim(get_data('type_name'));
		$top_id = trim(get_data('top_id'));
		$pic = get_data('pic') ? get_data('pic') : '';
		
		if($type_name == ''){
			make_json_result('', '分类名不能为空', array());
			exit;
		}
		
		$sql = "select top_id from gossip_article_type where hid = {$hid} and id = {$id}";
		$old_top_id = $this->db->get_one($sql);
		
		if($top_id != $old_top_id){		//如果改变了该分类的层级关系
			$sql = "select id from gossip_article_type where hid = {$hid} and top_id = {$id}";
			$all = $this->db->get_all($sql);
			
			if($old_top_id == 0 && !empty($all)){
				make_json_result('', '不能改变该分类的层级, 因为该分类下有子分类', array());
				exit;
			}
		}
		
		if(!strpos($pic, 'http://') && $pic != ''){
			$arr = explode(',', $pic);

			$base_dir = 'data/uploads/mini_program';
			if(!file_exists(ROOT_PATH.$base_dir)){
				mkdir(ROOT_PATH.$base_dir);
			}
			
			$hid_dir = $base_dir.'/'.$hid;
			if(!file_exists(ROOT_PATH.$hid_dir)){
				mkdir(ROOT_PATH.$hid_dir);
			}		

			$type_dir = $hid_dir.'/'.'type_img';
			if(!file_exists(ROOT_PATH.$type_dir)){
				mkdir(ROOT_PATH.$type_dir);
			}		
					
			$filename = date('YmdHis', time()).mt_rand(1000000, 9999999).'.jpeg';
			$filepath = $type_dir.'/'.$filename;
			
			$res = file_put_contents(ROOT_PATH.$filepath, base64_decode($arr[1]));
			
			if(!$res){
				make_json_result('', '上传图片时发生错误', array());
				exit;
				
			}else{
				$pic = 'http://'.$_SERVER['HTTP_HOST'].'/'.$filepath;
			}
			
		}
		
		$sql = "update gossip_article_type set type_name='{$type_name}', top_id='{$top_id}', pic='{$pic}' where hid = {$hid} and id = {$id}";
		$res = $this->db->query($sql);
		
		if($res){
			make_json_result($res, '修改成功', array('top_id'=>$top_id, 'type_name'=>$type_name));
				
		}else{
			make_json_result($res, '修改失败', array());	
		}
		
	}
	
	function del() {
		$id = get_data('id');
		$hid = get_hid();
		
		$sql = "select * from gossip_article_type where hid = {$hid} and top_id = {$id}";
		$all = $this->db->get_all($sql);
		
		if(!empty($all)){
			make_json_result('', '不能删除, 因为此分类下有子分类', array());
			exit;
		}
		
		$sql = "select id from gossip_article where hid = {$hid} and tag = (select type_name from gossip_article_type where hid = {$hid} and id = {$id})";
		$data = $this->db->get_all($sql);
		
		if(!empty($data)){
			make_json_result('', '不能删除, 因为此分类下有文章', array());
			exit;
		}
		
		$sql = "delete from gossip_article_type where hid = {$hid} and id = {$id}";
		$res = $this->db->query($sql);
		
		if($res){
			make_json_result($res, '删除成功', array());	
			
		}else{
			make_json_result($res, '删除失败', array());	
		}
	}
	
	
	//递归查询分类, 并统计每一个分类的文章数量
	function get_types($topid=0) {	
		$hid = get_hid();
		$aid = get_aid();
		
		$sql = "select *, (select count(*) from gossip_article where hid = {$hid} and tag = t.type_name) as count from gossip_article_type as t where t.hid = {$hid} and t.top_id = {$topid} order by count desc";
		$all = $this->db->get_all($sql);
		
		$str = '';
		foreach($all as $k => $v){
				
				if($topid == 0){	//如果是顶级分类, 就要把其子类的所有文章数量也加在一起
					$class = " class='top_0' ";
					
					$sql = "select type_name from gossip_article_type where hid = {$hid} and top_id = ".$v['id'];
					$arr = $this->db->get_all($sql);

					if(!empty($arr)){
						$values = '';
						
						foreach($arr as $key => $value){
							$values .= "'".$value['type_name']."',";
						}
						
						$values = trim($values, ',');

						$sql = "select count(*) from gossip_article where hid = {$hid} and tag in({$values})";
						$top_count = $this->db->get_one($sql);
						
					}else{
						$top_count = 0;
					}
					
				}else{
					$class = '';
					$top_count = 0;
				}
				
				$count = $v['count'] + $top_count;	//得到这个分类下的所有文章数

				$str .= "<tr data-id='".$v['id']."' {$class}>
							<td class='tags' onclick=\"get_list(1, '".$v['id']."', '".$v['top_id']."',  '".$v['type_name']."', this);\">
								<span class='del-type' onclick='delType(".$v['id'].", this);'>×</span>
								<span class='type_name'>".$v['type_name']."</span> ({$count})
								<i class='fa fa-edit edit-icon' onclick=\"editType(".$v['id'].");\"></i>
							</td>
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
		
		$w = " where hid = {$hid} ";
		
		if($type_name != ''){
			if($top_id == 0){
				$sql = "select type_name from gossip_article_type where hid = {$hid} and top_id = {$type_id}";
				$arr = $this->db->get_all($sql);
				$arr[]['type_name'] = $type_name;		
	
				if(!empty($arr)){
					$values = '';
					
					foreach($arr as $k => $v){
						$values .= "'".$v['type_name']."',";
					}
					
					$values = trim($values, ',');
					
					$w .= " and tag in({$values})";		//查询此顶级分类下的文章
					
				}
			}else{
				$w .= " and tag = '{$type_name}' ";		//查询非顶级分类的文章
			}
			
		}else{
			$w .= " and tag != '' ";	//查询全部文章
		}

		$sql = "select count(*) from gossip_article {$w} ";
		$count = $this->db->get_one($sql);
		$p = new Page($count, 20);
				
		$sql = "select * from gossip_article {$w} order by add_time desc limit ".$p->firstRow.','.$p->listRows;
		$all = $this->db->get_all($sql);

		foreach($all as $k => $v){
			$all[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
		}

		$this->template->assign('data', $all);		
		$this->template->assign('type_name', $type_name);
		$this->template->assign('page', $p->show('get_list', $type_id, $top_id, $type_name));
		make_json_result($this->template->fetch('article_type/list.html'), '', []);
	}
	
	
	
}


?>