<?php
require_once(APP_PATH."/include/libs.php");

class smallprogramApi extends cls_base{

	var $db;

	var $template;

	// init

	function init() {
		
		global $_DB,$_TEMPLATE;
		
		$this->db = $_DB;
		
		$this->template = $_TEMPLATE;
	}
	
	
	public function login() {
		$username = trim(get_data('username'));
		$passwd = trim(get_data('passwd'));
		
		if($username == '' || $passwd == ''){
			make_json_result('', '用户名或密码不能为空!', array('result'=>0));
			exit;
		}
		
		$passwd = md5($passwd);
		$sql = "select * from admin_user where login_name = '{$username}' and login_passwd = '{$passwd}'";
		$user = $this->db->get_row($sql);
		
		if(!empty($user)){
			
			if($user['is_delete']){
				make_json_result('', '拒绝登录!请联系管理员!', array('result'=>0));
			}else{
				make_json_result($user, 0, array('result'=>1));				
			}

		}else{
			make_json_result('', '用户名或密码错误!', array('result'=>0));
		}
	}
	
	
	public function get_hospital() {
		$uid = get_data('uid');
		
		$sql = "select ha.id, ha.id_hospital, ha.id_admin, ha.hospital_name, ha.admin_name, h.id as hid, h.top_id, h.type, h.name, h.province, h.city, h.memo 
				from irpt_hospital_admin as ha, irpt_hospital as h where ha.id_hospital = h.id and ha.is_delete <> 1 and ha.id_admin = {$uid}";
		$hospitals = $this->db->get_all($sql);
		
		foreach($hospitals as $key => $value) {
			if($value['top_id'] == 0) {
				foreach($hospitals as $k => $v){
					if($v['top_id'] == $value['hid']){
						$hospitals[$key]['child'][] = $v;
						unset($hospitals[$k]);
					}
				}
			}
		}
		make_json_result($hospitals, '', array('result'=>1));
	}
	

	
	/**每15s请求一次该方法**/
	function get_data(){
		$hid = get_data('hid');
		$uid = get_data('uid');
		
		$sql = "select access_id from gossip_user_access where user_id = {$uid} and hos_id = {$hid}";
		$aid = $this->db->get_one($sql);
		
		$vistors_table_name="gossip_vistors_".$aid;
		$msg_table_name = "gossip_msg_".$aid;
		
		$add_time=date('Y-m-d',strtotime("-150 day"));
		$end_time=date('Y-m-d',time());
		
		$sql = "SELECT vt.*,(select count(mt.id) from {$msg_table_name} as mt where mt.vistor_id = vt.id and mt.status=0) as c, (select count(mt.id) from {$msg_table_name} as mt where mt.vistor_id = vt.id ) as asd from {$vistors_table_name} as vt where vt.hos_id = {$hid} and vt.lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59')." and vt.mold in(1,2) order by vt.lastTime desc"; //按更新时间查询
		
		$data=$this->db->get_all($sql);
		
		$data_arr=array();
		
		foreach($data as $val){
			$array=array();
			$array['room_id']=1;
			
			if($val['guest']){
				$array['client_name']=$val['guest'];
			}else{
				$array['client_name']='访客'.$val['id'];
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
			}elseif($val['mold']==6){
				$array['channel']='两性微课';
			}
			
			if($val['asd']>0 && $val['openId']!=''){
				$array['mold']  =$val['mold'];
				$array['keyword']=$val['keyword'];
				$array['ip']=$val['ip'];
				$array['num']=$val['c'];
				$array['vistor_id']=$val['id'];
				$array['hos_id']=$val['hos_id'];
				$array['collect']=$val['collect'];
				$data_arr[]=$array;
			}
			
		}
		
		$sql = "select mt.* from {$msg_table_name} as mt,{$vistors_table_name} as vt where vt.hos_id = {$hid} and vt.lastTime between ".strtotime($add_time.' 00:00:00')." and ".strtotime($end_time.' 23:59:59')." and vt.mold in(1,2) and vt.id=mt.vistor_id and mt.status=0 and vt.openId<>'';";
		$row = $this->db->get_all($sql);
		
		if($row){
			make_json_result($data_arr, 1,array("result"=>1));
			
		}else{
			make_json_result($data_arr, 0,array("result"=>1));
		}
	}
	
	
	/**
	 * 查看对话信息
	 +----------------------------------------------------------
	 * Time:2017-12-21
	 +----------------------------------------------------------
	 */
	 public function check_vistor(){
		$vid = get_data('vid');
		$aid = get_data('aid');
		
//		$vistors_table_name="gossip_vistors_".$aid;
		$msg_table_name="gossip_msg_".$aid;
		
		$sql = "select count(*) from $msg_table_name where vistor_id='{$vid}';";
		$c = $this->db->get_one($sql);
		$c10 = $c-10;
		if($c10<0){
			$c10=0;
		}
		$sql="select * from {$msg_table_name} where vistor_id = ".$vid." order by createTime asc limit {$c10}, {$c} ";
		$data=$this->db->get_all($sql);
		
		//查询是否有新的对话
		$sql = "select * from {$msg_table_name} where vistor_id='{$vid}' and status=0;";
		$row = $this->db->get_row($sql);
		
		if($row){
			//修改阅读状态
			$sql = "update {$msg_table_name} set status=1 where vistor_id='{$vid}';";
			$this->db->query($sql);
		}
		
		make_json_result($data, 1, array('result'=>1));
	}

	
	/**
	 * 客户端消息推送接口
	 */
	public function push(){
			global $_DB,$_TEMPLATE;
			$hid = get_data('hid');
			$aid = get_data('aid');
			$content = get_data('content');
			$vistor_id = get_data('vistor_id');
			
			$msg_table_name="gossip_msg_".$aid;
			$vistors_table_name="gossip_vistors_".$aid;
			
			$sql = "SELECT * FROM $vistors_table_name where id='{$vistor_id}'";
			$vistor = $_DB->get_row($sql);
			$fromUsername = $vistor['openId'];
			
			/**获取回复小程序的信息**/
			$sql = "select * from irpt_interface where name='{$vistor['symbol']}' and id_hospital='{$hid}' and aid='{$aid}';";
			$interface = $_DB->get_row($sql);
			
			$data=array(
               "touser"=>$fromUsername,
               "msgtype"=>"text",
               "text"=>array("content"=>$content)
            );
		   
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+
			
			if((time()-$interface['createTime']) < 6000 && $interface['access_token'] != ''){
				$access_token = $interface['access_token'];
			}else{
				$access_token = $this->get_accessToken($interface['appId'],$interface['token']);
				$sql = "update irpt_interface set access_token='{$access_token}',createTime='".time()."' where id=".$interface['id'];
				$_DB->query($sql);
			}

           /* 
            * POST发送https请求客服接口api
            */
            $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
            //以'json'格式发送post的https请求
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			
            if (!empty($json)){
                curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
            }
			
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
			$re = json_decode($output);
			
            curl_close($curl);//关闭会话
			//{"errcode":0,"errmsg":"ok"}" {"error":0,"message":1,"content":"success"}
		
            if($re->errcode == 0){//成功，进行数据库插入操作
				$user = getUser();
				$time = time();
				$sql = "insert into $msg_table_name(`type`,`hos_id`,`vistor_id`,`admin_id`,`content`,`createTime`,`status`) 						values('3','{$hid}','{$vistor_id}','{$user[0]}','{$content}','{$time}','1');";
				$_DB->query($sql);
				$data = array();
				$data['time'] = date('Y-m-d H:i:s');
				$data['admin_name'] = $user[1];
				$data['content'] = $content;
				//判断初次客服
				$w = '';
				if($vistor['admin_id']==0){
					$w .= " ,admin_id='{$user[0]}',admin_name='{$user[1]}' ";
				}
				
				//修改最近的动态时间
				$sql = "update $vistors_table_name set lastTime='{$time}' $w where id='{$vistor_id}';";
				$_DB->query($sql);
                make_json_result($data,1,array());
            }elseif($re->errcode == -1){
				make_json_result('系统繁忙',0,array());
			}elseif($re->errcode == 40001){
				make_json_result('获取 access_token 时 AppSecret 错误',0,array());
			}elseif($re->errcode == 40002){
				make_json_result('不合法的凭证类型',0,array());
			}elseif($re->errcode == 40003){
				make_json_result('不合法的 OpenID',0,array());
			}elseif($re->errcode == 45015){
				make_json_result('回复时间超过限制',0,array());
			}elseif($re->errcode == 45047){
				make_json_result('客服接口下行条数超过上限',0,array());
			}elseif($re->errcode == 48001){
				make_json_result('api功能未授权',0,array());
			}elseif($re->errcode == 48002){
				make_json_result('访客已拒收消息',0,array());
			}else{//其他错误，清空数据表account_token
				$sql = "update irpt_interface set access_token='',createTime=0 where id=".$interface['id'];
				$_DB->query($sql);
				make_json_result('推送失败，请重新推送',0,array());
			}
	}
	
	
	
	/**
	获取access_token
	**/
	function get_accessToken($appid,$secret){
		
			/* 不在有效期，重新发送请求，获取access_token */
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
	
            $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);
			$res = json_decode($output, true);
	
            if($res){
                return $res['access_token'];
            }else{
                return 'api return error';
            }
        
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
	
	function dialogue(){
		$id = get_data('id');
		$hid = get_hid();
		$aid = get_aid();
		$vistors_name = "gossip_vistors_".$aid;
		$sql = "select * from $vistors_name where id='{$id}';";
		$row = $this->db->get_row($sql);
		if($row['guest']==null){
			$row['guest']='访客'.$row['id'];
		}
		
		$user = getUser();
		$sql = "select * from admin_user where id='{$user[0]}';";
		$user = $this->db->get_row($sql);
		$this->template->assign('row',$row);
		$this->template->assign('user',$user);
		make_json_result($this->template->fetch("smallprogram/dialogue.htm"),1,array());
	}

}
