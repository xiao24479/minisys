<?php


class form_order extends cls_base{

	public function  init(){
		
	}
	
	function form_action(){
		global $_DB;
		
		$hos_id = get_data('hid');
		$vistor_name = trim(get_data('vistor_name'));
		$vistor_id = get_data('vistor_id');
		$phone = trim(get_data('phone'));
		$account = trim(get_data('account'));
		$source = trim(get_data('source'));
		
		$sql = "select id_hospital from irpt_interface where name = '{$source}'";
		$hos_id = $_DB->get_one($sql);
		
		$sql = " select id  from gossip_order where  phone='{$phone}' and vistor_name='{$vistor_name}';";
		$omne = $_DB->get_one($sql);
		
		if($omne){
			make_json_result('',1,array());
		}
		$sql = "insert into gossip_order(`hos_id`,`vistor_id`,`vistor_name`,`phone`,`type`,`account`, `source`, `createTime`) values('{$hos_id}','{$vistor_id}','{$vistor_name}','{$phone}',0,'{$account}', '{$source}','".time()."');";
		
		if($_DB->query($sql)){
			make_json_result('',1,array());
		}else{
			make_json_result('数据错误，请联系客服！',0,array());
		}
		
	}
	

	//仁爱第三版使用的预约接口
	function insert_order(){
		global $_DB;
		
		$vistor_name = trim(get_data('vistor_name'));
		$vistor_id   = get_data('vistor_id');
		$phone       = trim(get_data('phone'));
		$account     = trim(get_data('account'));
		$appid       = trim(get_data('appid'));	
		$today       = strtotime(date('Y-m-d', time()));
		
		if(!preg_match("/^1[3-8]\d{9}$/", $phone)){
			make_json_result('请输入正确的手机号!', 0, array());exit;
		}

		$sql         = " select id from gossip_order where  phone='{$phone}' and vistor_name='{$vistor_name}' and createTime > {$today}";
		$omne        = $_DB->get_all($sql);
		
		//同个患者每天只能提交3次预约信息
		if(count($omne) >= 3){
			make_json_result('您今天的预约次数已用完!', 0, array());exit;
		}
		
		$sql    = "select id_hospital, name from irpt_interface where appId = '{$appid}'";
		$row    = $_DB->get_row($sql);
		
		$hos_id = $row['id_hospital'];
		$source = $row['name'];	

		$sql = "insert into gossip_order(`hos_id`,`vistor_id`,`vistor_name`,`phone`,`type`,`account`, `source`, `createTime`) 
				values('{$hos_id}','{$vistor_id}','{$vistor_name}','{$phone}',0,'{$account}', '{$source}','".time()."');";
		
		if($_DB->query($sql)){
			make_json_result('预约成功!',1,array());
		}else{
			make_json_result('数据错误，请联系客服！',0,array());
		}
	}

	
	/**
		厦门公众号独立接口
	**/
	function form_action2(){
		global $_DB;
		
		$hos_id = get_data('hid');
		$vistor_name = trim(get_data('vistor_name'));
		$vistor_id = get_data('vistor_id');
		$phone = trim(get_data('phone'));
		$account = trim(get_data('account'));
		$source = trim(get_data('source'));
		$date = date('Y-m-d', time());
		
		$sql = " select createTime  from gossip_order where  phone='{$phone}' and vistor_name='{$vistor_name}' order by createTime desc";
		$omne = $_DB->get_one($sql);
		
		//同一个患者可能在不同的日期来预约, 但不能在同一天出现两个同一个患者的预约,虽然有这种可能性, 但是这里暂时忽略这种可能
		if(!empty($omne) && (date('Y-m-d', $omne) == $date)){
			make_json_result('', 1, array());
		}
		
		$sql = "insert into gossip_order(`hos_id`,`vistor_id`,`vistor_name`,`phone`,`type`,`account`, `source`, `createTime`,`channel`) values('{$hos_id}','{$vistor_id}','{$vistor_name}','{$phone}',0,'{$account}', '{$source}','".time()."','1');";
		
		if($_DB->query($sql)){
			make_json_result('',1,array());
		}else{
			make_json_result('数据错误，请联系客服！',0,array());
		}
	}
}
