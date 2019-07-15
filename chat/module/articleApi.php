<?php
require_once(APP_PATH."/include/libs.php");

class articleApi extends cls_base {
	
	var $db;
	
	public function init() {
	
		global $_DB;
	
		$this->db = $_DB;
		
	}	

	public function news_detail() {
		$id = get_data('id');
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=news_detail";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['id'=>$id]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	public function get_news() {
		$p = get_data('p') ? get_data('p') : 0;
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=get_news";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['p'=>$p]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}
	
	public function get_list() {
		$id = get_data('id');
		$hid = get_data('hid');
		
		$sql = "select type_name from gossip_article_type where hid = {$hid} and (top_id = {$id} or id = {$id})";
		$type_arr = $this->db->get_all($sql);
		
		$str = '';
		foreach($type_arr as $k => $v){
			$str .= "'".$v['type_name']."',";
		}
		$str = trim($str, ',');
		
		$sql = "select * from gossip_article where hid = {$hid} and tag in($str)";
		$all = $this->db->get_all($sql);
		
		foreach($all as $k => $v) {
			$all[$k]['content'] = mb_substr(strip_tags($v['content']), 0, 70, 'utf8');
		}
		
		make_json_result($all, '', []);
	}
	
	public function get_detail() {
		$type_id = get_data('type_id');
		$article_id = get_data('article_id');
		$hid = get_data('hid');
		
		if($type_id){		
			$sql = "select type_name from gossip_article_type where hid={$hid} and id={$type_id}";
			$type_name = $this->db->get_one($sql);
			
			$sql = "select * from gossip_article where hid={$hid} and tag = '{$type_name}'";
			$row = $this->db->get_row($sql);
		}else{	
			$sql = "select * from gossip_article where hid={$hid} and id = {$article_id}";
			$row = $this->db->get_row($sql);
		}
		
		preg_match("|<img src=.*\"(.*)\\\\\"/>|U", $row['content'], $url);
		$img = $url[1];
		
		 $data= preg_replace_callback("|<p.*>(.*)</p>|U", function($match) {
			return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<p>".$match[1]."</p>\n";
		}, $row['content']);
		
		$data = preg_replace_callback("|<div.*>(.*)</div>|U", function($match) {
			return "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div>".$match[1]."</div>\n";
		}, $data);		
		
		$row['pic'] = $img;
		$row['content'] = strip_tags($data);
		
		make_json_result($row, '', []);
	}
	
	public function get_types() {
		$appid = get_data('appid');
		
		$sql = "select id_hospital from irpt_interface where appId = '{$appid}'";
		$hid = $this->db->get_one($sql);
		
		$sql = "select * from gossip_article_type where hid = {$hid} and top_id = 0 ";
		$all = $this->db->get_all($sql);
		
		make_json_result($this->types($all, $hid), '', ['hid'=>$hid]);
	}
	
	public function types($arr, $hid) {
		foreach($arr as $k => $v){
			$sql = "select * from gossip_article_type where hid = {$hid} and top_id = ".$v['id'].' limit 0,6';
			$child = $this->db->get_all($sql);
			
			$arr[$k]['child'] = $this->types($child, $hid);
		}
		return $arr;
	}


	//=============================================================================

    public function lists()
    {
        $appid = trim(get_data('appid'));
        $catid = get_data('catid');
        $page = get_data('page','i')?get_data('page','i'):0;
        $limit = get_data('limit','i')?get_data('limit','i'):3;

        $sql    = "select id_hospital,name from irpt_interface where appId = '{$appid}'";
        $row    = $this->db->get_row($sql);

        if (!$row){
            $return = array(
                "errno" => 40009,
                "msg" => "invalid request",
                "data" => [],
            );
            echo json_encode($return,JSON_UNESCAPED_UNICODE);
            exit();
        }

        $hid = $row['id_hospital'];

        $sql = "select type_name from gossip_article_type where hid = {$hid} and (top_id = {$catid} or id = {$catid})";
        $type_arr = $this->db->get_all($sql);

        $str = '';
        foreach($type_arr as $k => $v){
            $str .= "'".$v['type_name']."',";
        }
        $str = trim($str, ',');

        $sql = "select * from gossip_article where hid = {$hid} and tag in($str) limit {$page},{$limit}";
        $all = $this->db->get_all($sql);

        foreach($all as $k => $v) {
            $all[$k]['views'] = rand(100,999);
            $all[$k]['add_time'] = date("m-d H:i",$v['add_time']);
            $all[$k]['content'] = mb_substr(strip_tags($v['content']), 0, 70, 'utf8');
        }

        $return = array(
            "errno" => 0,
            "msg" => "success",
            "data" => $all,
        );

        echo json_encode($return,JSON_UNESCAPED_UNICODE);
    }

    public function show() {

        $aid = get_data('aid','i');

        $sql = "select * from gossip_article where id = {$aid}";
        $row = $this->db->get_row($sql);

        if (!$row){
            $return = array(
                "errno" => 40009,
                "msg" => "invalid request",
                "data" => [],
            );
            echo json_encode($return,JSON_UNESCAPED_UNICODE);
            exit();
        }

        $data= preg_replace_callback("|<p.*>(.*)</p>|U", function($match) {
            return $match[1]."\n";
        }, $row['content']);

        $data = preg_replace_callback("|<div.*>(.*)</div>|U", function($match) {
            return $match[1]."\n";
        }, $data);

        $row['content'] = strip_tags($data);

        $return = array(
            "errno" => 0,
            "msg" => "success",
            "data" => $row,
        );

        echo json_encode($return,JSON_UNESCAPED_UNICODE);
        exit();
    }








}

?>