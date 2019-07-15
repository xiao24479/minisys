<?php
require_once(APP_PATH."/include/libs.php");

class dialog extends cls_base{

	var $db;

	var $template;

	// init

	function init() {
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
		
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid());
	}
	
	
	/**
	 * 聊天后台界面
	 */
	public function message(){
		
		//获得客服信息
		$hid=get_hid();
		$aid=get_aid();
		$only=$_SESSION['only'];
		$vistors_table_name="gossip_vistors_".$aid;
	
		$user_data=getUser();

		$add_time=date('Y-m-d',time());
		$end_time=date('Y-m-d',time());
		
		$sql="SELECT * FROM $vistors_table_name where hos_id=$hid and (mold=3 or mold=4 or mold=5) and lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59')." order by lastTime desc"; //按更新时间查询
		
		$data=$this->db->get_all($sql);
		
		$data_arr=array();
		
		foreach($data as $val){
			$array=array();
			$array['room_id']=1;
			if($val['guest']){
				$array['client_name']=$val['guest'];
			}else{
				$array['client_name']=$val['region'].$val['city'].'访客'.$val['id'];
			}
			if($val['mold']==1){
				$array['channel']='小程序';
			}elseif($val['mold']==2){
				$array['channel']='公众号';
			}elseif($val['mold']==3){
				$array['channel']='PC网站';
			}elseif($val['mold']==4){
				$array['channel']='APP';
			}elseif($val['mold']==5){
				$array['channel']='移动网站';
			}
			$array['keyword']=$val['keyword'];
			$array['ip']=$val['ip'];
			$array['vistor_id']=$val['id'];
			$array['only']=$val['only'];
			$array['hos_id']=$val['hos_id'];
			$array['collect']=$val['collect'];
			$data_arr[]=$array;
		}
		
		$this->template->assign('data',$data_arr);

		$this->template->assign('hid',$hid);
		
		$this->template->assign('aid',$aid);
		
		$this->template->assign('only',$only);
		
		//查出用户头像
		$sql="select autograph,path from admin_user where id=".$user_data[0];
		$user=$this->db->get_row($sql);
		
		$this->template->assign('autograph',$user['autograph']);
		
		$this->template->assign('path',$user['path']);

		$this->template->assign('user_data',$user_data);

		$this->template->display("dialog/message.htm");
		
	}
	
	
	/**
	 * 查看对话信息
	 +----------------------------------------------------------
	 * Time:2017-12-21
	 +----------------------------------------------------------
	 */
	public function check_vistor(){
		$id=get_data('id','i');
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
		$msg_table_name="gossip_msg_".$aid;
		$sql="select m.type,m.content,m.createTime,u.name,u.path,v.id,v.region,v.city,v.guest from $msg_table_name as m 
		left join admin_user as u on m.admin_id=u.id
		left join $vistors_table_name as v on m.vistor_id=v.id where m.vistor_id=".$id." order by m.createTime asc";
		$data=$this->db->get_all($sql);
		
		$this->template->assign('data',$data);
		make_json_result($this->template->fetch("dialog/check_vistor.htm"), '', array());
	}
	
	/**
	 * 客户端消息推送接口
	 */
	public function push(){
		$aid=get_aid();
		$hos_id=get_data('hospid');
		$vistor_id=get_data('vistor_id');
		$msg = get_data('msg','s');
		$msg_table_name="gossip_msg_".$aid;
		$vistors_table_name="gossip_vistors_".$aid;
		$user_data=getUser();
		$admin_id=$user_data[0];
		$admin_name=$user_data[1];
		
		//首次开始推送消息给访客时，修改对应的访客客服信息
		$sql="select admin_id from $vistors_table_name where id='{$vistor_id}'";
		$admin_ids=$this->db->get_one($sql);
		if($admin_ids==0){//不存在客服时添加客服数据
			$sql="UPDATE $vistors_table_name SET `admin_id`='{$admin_id}',`admin_name`='{$admin_name}'  WHERE id=".$vistor_id;
			$res=$this->db->query($sql);
		}

		$sql="INSERT INTO $msg_table_name (type, hos_id,vistor_id,content,admin_id,createTime) VALUES (3,'{$hos_id}', '{$vistor_id}', '{$msg}', '{$admin_id}','".time()."')";
		$this->db->query($sql);
	
		make_json_result('200','推送成功',array());
	}

	/**
	 * 更新访客信息
	 */
	
	public function updata_vistor(){
	
		$vistor_id=get_data('vistor_id','i');
		$guest=get_data('guest');
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
	
		$sql="UPDATE $vistors_table_name SET `guest`='".$guest."' WHERE id=".$vistor_id;
		$res=$this->db->query($sql);
		if($res){
			make_json_result('200','修改成功',array());
		}else{
			make_json_result('300','修改失败',array());
		}
	}
	
	/**
	 * 收藏访客
	 */
	
	public function collect_vistor(){
	
		$vistor_id=get_data('vistor_id','i');
		$collect=get_data('collect');
		$aid=get_aid();
		$vistors_table_name="gossip_vistors_".$aid;
	
		$sql="UPDATE $vistors_table_name SET `collect`=$collect WHERE id=".$vistor_id;
		$res=$this->db->query($sql);
		if($res){
			make_json_result('200','收藏成功',array());
		}else{
			make_json_result('300','收藏失败',array());
		}
	}

}
