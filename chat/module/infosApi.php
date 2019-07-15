<?php
require_once(APP_PATH."/include/libs.php");
class infosApi extends cls_base {
	var $db;
	
	var $template;
	
	var $start = 0;
	
	var $offset = 8;
	
//	var $domain = "https://ai.ra120.com/";

	var $domain = "http://www.chat.com/";
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;

		
	}
	
	function get_more() {
		$num = get_data('num') + 1;
		$type = get_data('type');
		$hid = get_data('hid');
		$start = $num * $this->offset;
		$hid = 113;
		
		$sql = "select * from gossip_infos where hospital_id = '{$hid}' and type = {$type} order by status desc, add_time desc limit ".$start.','.$this->offset;
		$all = $this->db->get_all($sql);
		foreach($all as $key => $value) {
			$all[$key]['add_time'] = date('Y年m月', $value['add_time']);
				if($all[$key]['pic2']==''){
					$all[$key]['num']  = 2;
				}elseif($all[$key]['pic7']!=''){
					
					$all[$key]['num']  = 1;
				}else{
					
					$all[$key]['num']  = 3;
				}
		}
		$count = count($all);
		if($count < $this->offset){
			echo json_encode(['msg'=>'没有更多了', 'content'=>$all]);			
		}else{
			echo json_encode(['msg'=>'更多', 'content'=>$all]);			
		}

	}

	function get_detail() {
		$id = get_data('id');
		$sql = "select * from gossip_infos where id = {$id}";
		$info = $this->db->get_row($sql);
		$info['add_time'] = date('Y年m月', $info['add_time']);
		echo json_encode($info);
	}
	
	
	function get_infos() {
		$arr = [1=>'新房', 2=>'二手房', 3=>'租房'];
		
		$hid = get_data('hid');
		$hid = 113;		
		$sql = "select type as title from gossip_infos where hospital_id = '{$hid}' group by type";
		$types = $this->db->get_all($sql);
		$num = 0;
		foreach($types as $key => $value) {
			$sql = "select * from gossip_infos where hospital_id = '{$hid}' and type = ".$value['title']." order by status desc, add_time desc limit ".$this->start.",".$this->offset;
			$all = $this->db->get_all($sql);
			foreach($all as $k => $v) {
				$all[$k]['add_time'] = date('Y年m月', $v['add_time']);
				if($all[$k]['pic2']==''){
					$all[$k]['num']  = 2;
				}elseif($all[$k]['pic7']!=''){
					
					$all[$k]['num']  = 1;
				}else{
					
					$all[$k]['num']  = 3;
				}
				 
				
			}
			$types[$key]['id'] = $num;
			$types[$key]['isSelect'] = $num == 0 ? true : false;
			$types[$key]['infos'] = $all;
			$types[$key]['title'] = $arr[$value['title']];
			$count = count($all);
			if($count < $this->offset){
				$types[$key]['msg'] = "没有更多了";
			}else{
				$types[$key]['msg'] = "更多";
			}
			$num++;
		}
		echo json_encode($types);
	}
	

	function add_info() {
		$openid = trim(get_data('openid'));
		$weixin_name = trim(get_data('weixin_name'));
		$hospital_id = trim(get_data('hid'));
		$hospital_name = trim(get_data('hname'));
		$weixin_pic = trim(get_data('weixin_pic'));
		$type = get_data('type');
		$words = trim(get_data('words'));
		$phone = trim(get_data('phone'));
		$add_time = time();
		
		$words = $this->vaildTel($words);
		
		
		$sql ="select id from gossip_infos  where words='{$words}';";
		
		$one = $this->db->get_one($sql);
		if($one){
			echo json_encode(['msg'=>"提交失败!", 'result'=>0, 'id'=>'']); exit;
			
		}
		
		$sql = "insert into gossip_infos(openid, weixin_name, hospital_id,hospital_name, weixin_pic, type, words, phone, add_time)
				value('{$openid}', '{$weixin_name}', '{$hospital_id}','{$hospital_name}', '{$weixin_pic}', '{$type}', '{$words}', '{$phone}', '{$add_time}')";
		$res = $this->db->query($sql);
		$id = mysql_insert_id();
		
		if($res){
			echo json_encode(['msg'=>"提交成功!", 'result'=>1, 'id'=>$id]);
		}else{
			echo json_encode(['msg'=>"提交失败!", 'result'=>0, 'id'=>'']);
		}
	}
	//替换字符串中的手机号和固话
	 public function vaildTel($s){
        $n = preg_match_all("/15[0-9]\d{8}|17[0-9]\d{8}|14[0-9]\d{8}|13[0-9]\d{8}|18[0-9]\d{8}/",$s,$arr);
		
        foreach ($arr[0] as $tel) {
            //$new = substr($tel,0,3).'****'.substr($tel,7,strlen($tel));
            $s = str_replace($tel,'',$s);
        }
		$m = preg_match_all("/0\d{2,3}-\d{7,8}/",$s,$arr2);
		 foreach ($arr2[0] as $tel) {
            //$new = substr($tel,0,3).'****'.substr($tel,7,strlen($tel));
            $s = str_replace($tel,'',$s);
        }
        return $s;
    }
	
	
	function upload() {
		$id = get_data('id');
		$openid = get_data('openid');	
		$num = get_data('num') + 1;
		
		$img = $_FILES['image'];
		$tmp = $img['tmp_name'];
		$name = $img['name'];
		
		$date_dir = ROOT_PATH.'data/infos/'.date('Ymd', time());
		
		if(!file_exists($date_dir)){
			mkdir($date_dir);
		}
		
		$openid_dir = $date_dir.'/'.$openid;
		
		if(!file_exists($openid_dir)){
			mkdir($openid_dir);
		}	
							
		$ext = pathinfo($name, PATHINFO_EXTENSION);
		$basename = date("YmdHis") . '_' . rand(10000, 99999);
		$filename = $basename.'.'.$ext;
		
		$save_path = $openid_dir.'/'.$filename;
		$res = move_uploaded_file($tmp, $save_path);
		
		if($res){
			$field = 'pic'.$num;
			$img_url = $this->domain.strstr($save_path, "data/infos"); 
			$sql = "update gossip_infos set {$field} = '{$img_url}' where id = {$id}";	
			$result = $this->db->query($sql);	
			echo json_encode(['msg'=>"上传成功", 'result'=>1]);
		}
		
	}

	function get_openid() {
		$code = get_data('code');
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid=wxd287cc8e5790b30c&secret=57ac6069298523439587eb2d9f678c49&js_code=".$code."&grant_type=authorization_code";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);	
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($curl);
		curl_close($curl);
		echo $json;
	}
	
}
?>