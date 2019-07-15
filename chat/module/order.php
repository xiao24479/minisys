<?php

class order extends cls_base{
	
	var $db;
	
	var $template;
	
	// init
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;
		
		require_once(APP_PATH."/include/libs.php");
	
		require_once(ROOT_PATH . "_ext/auth_admin.php");
	
		require_once(ROOT_PATH."_ext/page.class.php");
		
		cuckhid(get_hid());cuckaid(get_hid(),get_aid());
		
	}
	
	/**
	 * 订单列表
	 */
	function order_list(){
		$hid=get_hid();
		$aid = get_aid();
		$date=get_data('date');

		if(empty($date)){
			$add_time=date('Y/m/01',time());
			$end_time=date('Y/m/d',time());
		}else{
			$arr = explode('-', $date);
			$add_time = trim($arr[0]);
			$end_time = trim($arr[1]);
		}

		$w=' hos_id ='.$hid;
		
		if($add_time!='' && $end_time!=''){
			$w .= "  and createTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59');
		}
		
		//查出索引总数
		$sql="SELECT count(id) as a FROM gossip_order where $w";
		
		$count=$this->db->get_one($sql);//总记录数数
		
		$parameter  = '';
		
		if($add_time!=''){
			$parameter .= '&add_time='.$add_time;
		}
		if($end_time!=''){
			$parameter .= '&end_time='.$end_time;
		}
		
		$p = new Page($count,20,$parameter);
		
		$sql="SELECT * FROM gossip_order where $w ORDER BY `id`  DESC LIMIT ".$p->firstRow.",".$p->listRows." ;"; //按更新时间查询
		
		$data=$this->db->get_all($sql);
		
		$this->template->assign('user',getUser());
		
		$this->template->assign('add_time',$add_time);
		
		$this->template->assign('end_time',$end_time);
		
		$this->template->assign('data',$data);
		
		$this->template->assign('page',$p->show('order_list'));

		make_json_result($this->template->fetch('order/order_list.html'), 1, array());
	}
	
	function huifang(){
		global $_DB,$_TEMPLATE;
		$id = get_data('id');
		
		$sql = "select remarks from gossip_order where id='{$id}';";
		$remarks = $_DB->get_one($sql);
		
		
		$_TEMPLATE->assign('id',$id);
		$_TEMPLATE->assign('remarks',$remarks);
		make_json_result($this->template->fetch('order/huifang.html'),1,array());
	}
	
	function huifang_action(){
		global $_DB,$_TEMPLATE;
		$id = get_data('id');
		$remarks = trim(get_data('remarks'));
		
		$sql = "update gossip_order set status=1,remarks='{$remarks}' where id='{$id}';";
		$res = $_DB->query($sql);

		make_json_result('', $res, array());
	}
	
	function hf(){
		global $_DB,$_TEMPLATE;
		$id = get_data('id');
		$aid = get_aid();
		$datename = "gossip_vistors_".$aid;
		$sql = "select go.*,gv.mold,gv.symbol,gv.keyword from gossip_order as go,{$datename} as gv where go.id='{$id}' and go.vistor_id=gv.id;";
		$row = $_DB->get_row($sql);
		if($row['vistor_name']!=''){
			$row['name'] = $row['vistor_name'];
		}else{
			if($row['guest']==''){
					$guest = $row['region'].$row['city'].'访客'.$row['id'];
				}else{
					$guest = $row['guest'];
			}
		}
		$user = getUser();
		$_TEMPLATE->assign('row',$row);
		$_TEMPLATE->assign('user',$user);
		make_json_result($_TEMPLATE->fetch("order/hf_action.html"),1,array());
		
	}
	function hf_action(){
		global $_DB,$_TEMPLATE;
		
		$id = get_data('id');

		$remarks = trim(get_data('remarks'));
		$sql = "select * from gossip_order where id='{$id}';";
		$row = $_DB->get_row($sql);
		$user = getUser();
		if($row['type']==1){
			$sql = "update gossip_order set remarks='{$remarks}' where id='{$id}';";
		}else{
				$time = time();
				$sql = "update gossip_order set uid='{$user[0]}',uname='{$user[1]}',remarks='{$remarks}',hf_time='{$time}',type=1 where vistor_id='{$row['vistor_id']}' and hos_id='{$row['hos_id']}';";
			
		}
		if($_DB->query($sql)){
			make_json_result('',1,array());
		}else{
			make_json_result('',0,array());
		}
		
		
		
	}
	
	
	function del(){
		global $_DB,$_TEMPLATE;
		$id = get_data('id');
		$user = getUser();
		if($user[0]!=1){
			make_json_result('暂无权限！',0,array());
		}else{
			$sql = "delete from gossip_order where id='{$id}';";
			$_DB->query($sql);
			make_json_result('',1,array());
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}