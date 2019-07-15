<?php
require_once(APP_PATH."/include/libs.php");

class relayApi extends cls_base {
	
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

	public function get_menu() {
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=get_menu";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	//充值记录
	public function recharge_log() {
		$uid = get_data('uid');
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=recharge_log";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['uid'=>$uid]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	//获取我的积分
	public function my_integral() {
		$uid = get_data('uid');
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=my_integral";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['uid'=>$uid]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	//获取套餐内容
	public function get_meal() {
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=get_meal";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	//播放次数+1
	public function play() {
		$id = get_data('id');
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=play";
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

	//我已买的课程
	public function my_bought() {
		$uid = get_data('uid');
		
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=my_bought";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['uid'=>$uid]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	//获取单曲留言
	public function audio_comment() {
		$id = get_data("id");
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=audio_comment";
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

	//添加留言
	public function add_comment() {
		$uid = get_data('uid');
		$cid = get_data('cid');
		$ctitle = get_data('ctitle');
		$content = trim(get_data('content'));
		
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=add_comment";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['uid'=>$uid, 'cid'=>$cid, 'ctitle'=>$ctitle, 'content'=>$content]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);

	}

	//合辑详细
	public function agg_detail() {
		$agg_id = get_data('agg_id');
		
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=agg_detail";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['agg_id'=>$agg_id]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}

	//合辑列表
	public function album_list() {
		$num = get_data('page');
		
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a=album_list";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, ['page'=>$num]);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}
	
	public function curl($funcname, $array=[]) {
		$url = "http://admin.liangxingweike.com/wiki/main.php?m=mini_program_api&a={$funcname}";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_HEADER, 0);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $array);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$json = curl_exec($c);
		$data = json_decode($json, true);
		make_json_result($data['content'], $data['message'], []);
	}
	
	//单曲详细
	public function get_detail() {
		$id = get_data('id');
		$this->curl('get_detail', ['id'=>$id]);
	}
	
	//首面轮播图
	public function slide_imgs() {
		$this->curl('slide_imgs');
	}
	
	//单曲列表
	public function curri_list() {
		$num = get_data('page');
		$this->curl('curri_list', ['page'=>$num]);
	}


	//用户进入小程序时保存用户信息
	public function user_auth() {		
		$code = get_data('code');
		$channel_name = get_data('channel_name');
		$appid = get_data('appid');

		$this->curl('user_auth', ['code'=>$code, 'channel_name'=>$channel_name, 'appid'=>$appid]);
	}

	//用户授权登录时更新用户信息
	public function user_update() {		
		$uid = get_data('uid');
		$name = get_data('name');
		$sex = get_data('sex');
		$userpic = get_data('userpic');
		
		$this->curl('user_update', ['uid'=>$uid, 'name'=>$name, 'sex'=>$sex, 'userpic'=>$userpic]);
	}
	
	//登录的用户是否有购买过此产品
	public function is_buy() {		
		$uid = get_data('uid');
		$id = get_data('id');
		$type = get_data('type');	// 1视频, 2合辑
		
		$this->curl('is_buy', ['uid'=>$uid, 'id'=>$id, 'type'=>$type]);
	}

	//充值积分
	public function recharge() {
		$uid = get_data('uid');
		$price = get_data('price');
		$code = get_data('code');
		$appid = get_data('appid');
		$total_fee = get_data('total_fee');		//金额, 单位分	

		$this->curl('recharge', ['uid'=>$uid, 'price'=>$price, 'code'=>$code, 'appid'=>$appid, 'total_fee'=>$total_fee]);
	}

	public function buy() {
		$uid = get_data('uid');
		$cid = get_data('cid');
		$price = (int)(get_data('price'));
		
		$this->curl('buy', ['uid'=>$uid, 'cid'=>$cid, 'price'=>$price]);
	}

		
}
?>