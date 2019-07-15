<?php
require_once(APP_PATH."/include/libs.php");
require_once(ROOT_PATH."_ext/page.class.php");	

class infos extends cls_base {
	
	var $db;
	
	var $template;
	
//	var $domain = "https://ai.ra120.com/";
	
	var $domain = "http://www.chat.com/";
	
	// init
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;
		
	}
	
	
	function infos_list() {
		echo ROOT_PATH;
		$name = trim(get_data('name')) ? trim(get_data('name')) : '';
		$w = '';
		if($name != '') {
			$w .= " and weixin_name like '%{$name}%'"; 
		}
		
//		$hid = get_hid();
		$hid = 113;
		$sql = "select count(*) from gossip_infos where hospital_id='{$hid}' {$w} ";
		$count = $this->db->get_one($sql);
		$page = new Page($count, 20);
		
		$sql = "select * from gossip_infos where  hospital_id='{$hid}' {$w}  ORDER BY  status desc, add_time desc  limit ".$page->firstRow.",".$page->listRows;
		$infos = $this->db->get_all($sql);
		
		foreach($infos as $key => $value) {
			$infos[$key]['add_time'] = date('Y-m-d H:i:s', $value['add_time']);
		}
		
		$this->template->assign('weixin_name', $name);
		$this->template->assign('infos', $infos);
		$this->template->assign('page', $page->show2());
		$this->template->display("infos/infos_list.html");
	}
	
	
	function infos_add() {

		make_json_result($this->template->fetch("infos/infos_add.html"), '', array());		
	}
	
	function infos_add_action() {
		$arr = [];
		$arr['weixin_name'] = get_data('weixin_name');
		$arr['weixin_pic']  = get_data('weixin_pic');
		$arr['hospital_name'] = get_data('hospital_name');
		$arr['hospital_id'] = get_data('hospital_id');
		$arr['type']		= get_data('type');
		$arr['phone']		= get_data('phone');
		$arr['words']		= get_data('words');
		$arr['add_time']	= time();
		$pic			    = get_data('pic');
		
		if(!empty($pic)) {
			for($i=1; $i<=count($pic); $i++){
				$arr['pic'.$i] = $pic[$i-1];
			}
		}
		
		$column = '';
		$values = '';
		foreach($arr as $k => $v){
			if($v){
				$column .= "{$k},";
				$values .= "'{$v}',";				
			}
		}
		$column = trim($column, ',');
		$values = trim($values, ',');
		
		$sql = "insert into gossip_infos({$column}) values({$values})";
		$res = $this->db->query($sql);
		if($res){
			make_json_result('', '添加成功！', array('result'=>1));
		}else{
			make_json_result('', '添加失败！', array('result'=>0));
		}
	}
	
	function update_info() {
		header('Content-type: application/json');  
		$id = get_data('id');
		
		$sql = "select * from gossip_infos where id = {$id}";
		$info = $this->db->get_row($sql);
		$diff = array_diff_assoc($_POST, $info);
		
		$images = $_FILES['images'];
		$allow_type = ['image/gif', 'image/png', 'image/jpeg'];
		$img_arr = [];
		
		if($images['name'][0] != null){
			
			foreach($images['type'] as $value){
				if(!in_array($value, $allow_type)){
					make_json_result('', '不能上传非图片类型文件!', array('result'=>0));
					exit;
				}
			}
			
			$count = count($images['name']);
			
			for($i=1; $i<=9; $i++){
				if($info['pic'.$i] == null){
					$num = $i;
					$max = 9-$i+1;
					break;
				}
			}
			
			if($count > $max){
				make_json_result('', '最多只能保存9张图片, 你还能上传'.$max.'张图片!', array('result'=>0));
				exit;
			}
			
			foreach($images as $key => $value){
				
				foreach($value as $k => $v){
					if($key == 'name'){
						$tmp_name = $images['tmp_name'][$k];
						$name 	  = $images['name'][$k];
						
						$filename = date("YmdHis") . '_' . rand(10000, 99999);
						
						if($info['pic1']){
							$img_path = ROOT_PATH.strstr($info['pic1'], 'data/infos');
							$save_path = pathinfo($img_path, PATHINFO_DIRNAME) . '/' . $filename . '.' . pathinfo($name, PATHINFO_EXTENSION);
		
						}else{
							$date_dir = ROOT_PATH . 'data/infos/' . date('Ymd', time()); 
							
							if(!file_exists($date_dir)){
								mkdir($date_dir);
							}	
							
							$openid_dir = $date_dir.'/'.$info['openid'];
							
							if(!file_exists($openid_dir)){
								mkdir($openid_dir);
							}
							
							$save_path = $openid_dir . '/' .  $filename . '.' . pathinfo($name, PATHINFO_EXTENSION);
						}
						
						$res = move_uploaded_file($tmp_name, $save_path);
						if($res){
							$img_arr['pic'.$num] = $this->domain.strstr($save_path, 'data/infos');
							$num++;								
						}else{
							make_json_result('', '上传文件失败!', array('result'=>0));
							exit;
						}
					
					}
				}
			}						
		}
		
		$data = array_merge($diff, $img_arr);
		if(empty($data)){
			make_json_result('', '数据未发生变化!', array('result'=>0));
			exit;
		}
		
		$str = '';
		foreach($data as $key => $value) {
			$str .= "{$key} = '{$value}',";
		}
		$str = trim($str, ',');
		
		$sql = "update gossip_infos set {$str} where id={$id}";
		$res = $this->db->query($sql);
		if($res){
			make_json_result($img_arr, '更新数据成功!', array('result'=>1, 'id'=>$id));
		}else{
			make_json_result('', '更新数据失败!', array('result'=>0));
		}
		
	}
	
	
	function infos_edit() {
		$id = get_data('id');
		
		$sql = "select * from gossip_infos where id = {$id}";
		$info = $this->db->get_row($sql);
		$arr = [];
		$count = 0;
		for($i=1; $i<=9; $i++){
			if($info['pic'.$i]){
				$arr['pic'.$i] = $info['pic'.$i];
			}else{
				$count += 1;
			}
		}
		$info['pic'] = $arr;
		$info['add_time'] = date('Y-m-d H:i:s');
		$this->template->assign('info', $info);
		$this->template->assign('count', $count);
		make_json_result($this->template->fetch("infos/infos_edit.html"), '', array());
	}
	
	
	function del_info() {		
		$id = get_data('id');
		$sql = "select * from gossip_infos where id = {$id}";
		$info = $this->db->get_row($sql);
		$imgs = [];
		
		for($i=1; $i<=9; $i++){
			if($info['pic'.$i]){
				$imgs[] = $info['pic'.$i];
			}
		}
		foreach($imgs as $value) {
			if(!strpos($value, 'forbid')){
				$str = strstr($value, 'data/infos');
				$filename = ROOT_PATH.$str;
				echo unlink($filename);				
			}

		}
		
		$sql = "delete from gossip_infos where id = {$id}";
		$res = $this->db->query($sql);
		make_json_result($res, '', array());		
	}
	
	
	function img_forbid() {
		$id = get_data('id');
		$field = get_data('field');
		$img_path = $this->domain.'data/infos/forbid_img/forbid.jpg';
		
		$sql = "update gossip_infos set {$field} = '{$img_path}' where id={$id}";
		$res = $this->db->query($sql);
		if($res){
			make_json_result($img_path, '', array('result'=>$res));
		}else{
			make_json_result('', '', array('result'=>$res));
		}
	}


	function change_status() {
		$id = get_data('id');
		$status = get_data('status');
		
		if($status){
			$change = 0;
		}else{
			$change = 1;
		}
		
		$sql = "update gossip_infos set status = {$change} where id = {$id}";
		$res = $this->db->query($sql);
		if($res){
			make_json_result('', '', array("result"=>1, "status"=>$change));			
		}else{
			make_json_result('', '', array("result"=>0));			
		}

	}
		
}

	




?>