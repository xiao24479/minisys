<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");

class article extends cls_base {
	
	var $db;
	
	var $template;
	
	public function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;
	}	
	
	public function create() {
		$id = get_data('id') ? get_data('id') : '';
		$hid = get_hid();
		$article = [];
		
		if($id != ''){
			$sql = "select * from gossip_article where hid = {$hid} and id = {$id}";
			$article = $this->db->get_row($sql);
			$article['content'] = stripslashes($article['content']);
		}
		
		$this->template->assign('article', $article);
		$this->template->assign('types', $this->get_types($article['tag']));
		make_json_result($this->template->fetch('article/create.html'), '', []);
	}
	
	public function get_types($type_name, $top_id = 0) {
		$hid = get_hid();
		
		$sql = "select * from gossip_article_type where hid = {$hid} and top_id = {$top_id}";
		$all = $this->db->get_all($sql);
		
		$str = '';
		$class = '';
		
		if($top_id == 0){
			$class = " class='top_0' ";
		}
		
		foreach($all as $k => $v){
			if($type_name && $type_name == $v['type_name']){
				$selected = " selected='selected' ";
			}else{
				$selected = '';
			}
			$str .= "<option {$class} {$selected} value='".$v['id']."'>".$v['type_name']."</option>";
			$str .= $this->get_types($type_name, $v['id']);
		}
		return $str;
	}
	
	public function save() {
		$hid = get_hid();
		$title = trim(get_data('title'));
		$from = trim(get_data('from'));
		$tag = trim(get_data('tag'));
		$content = trim(get_data('content'));
		$add_time = time();
		
		if($title == ''){
			make_json_result(0, '标题不能为空', []);
			exit;
		}
				
		if($from == ''){
			make_json_result(0, '来源不能为空', []);
			exit;
		}
		
		if($tag == ''){
			make_json_result(0, '请选择一个标签', []);
			exit;
		}	
		
		if($content == ''){
			make_json_result(0, '请输入文章内容', []);
			exit;
		}	
			
		$sql = "insert into gossip_article(`hid`, `title`, `from`, `tag`, `content`, `add_time`) value({$hid}, '{$title}', '{$from}', '{$tag}', '{$content}', '{$add_time}')";
		$res = $this->db->query($sql);
		
		if($res){
			$msg = '保存成功!';
		}else{
			$msg = '保存失败!';
		}
		
		make_json_result($res, $msg, []);
	}
	
	public function update() {
		$id = get_data('id');
		$hid = get_hid();
		$title = trim(get_data('title'));
		$from = trim(get_data('from'));
		$tag = trim(get_data('tag'));
		$content = trim(get_data('content'));
		
		if($title == ''){
			make_json_result(0, '标题不能为空', []);
			exit;
		}
				
		if($from == ''){
			make_json_result(0, '来源不能为空', []);
			exit;
		}
		
		if($tag == ''){
			make_json_result(0, '请选择一个标签', []);
			exit;
		}	
		
		if($content == ''){
			make_json_result(0, '请输入文章内容', []);
			exit;
		}	
			
		$sql = "update gossip_article set `title`='{$title}', `from`='{$from}', `tag`='{$tag}', `content`='{$content}' where hid = {$hid} and id = {$id}";
		$res = $this->db->query($sql);
		
		if($res){
			$msg = '修改成功!';
		}else{
			$msg = '修改失败!';
		}
		
		make_json_result($res, $msg, []);
	}	
	
	public function del() {
		$id = get_data('id');
		$hid = get_hid();
		
		$sql = "delete from gossip_article where hid = {$hid} and id = {$id}";
		$res = $this->db->query($sql);
		
		if($res){
			make_json_result($res, '删除成功', array());	
			
		}else{
			make_json_result($res, '删除失败', array());	
		}
	}	
}
?>