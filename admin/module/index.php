<?php //默认文件，可跳转
require_once(APP_PATH . "/include/libs.php");
require_once(ROOT_PATH . "/_ext/page.class.php");

function get_aid(){	init_session();	$aid = isset($_SESSION['access_id'])?$_SESSION['access_id']:'0';	return $aid;}	
	class index extends cls_base{
		var $db;

		var $template;
		var $hid;
		function init(){

			global $_DB,$_TEMPLATE;
			
			$this->db = $_DB;
			
			$this->template = $_TEMPLATE;
			
			$this->hid = get_hid();
			if($this->hid==0){
				make_json_result('请选择医院',0,array());
			}

		}
		
		function home_index(){ 
		
			$hid = get_hid(); 
			$month_start = strtotime(date('Y-m-01'));
			$month_end = strtotime(date('Y-m-t'));
			
			 $aid = get_aid();
			$month_data = array();
			
			$sql ="select count(distinct vistor_id) as duihua FROM gossip_msg_{$aid}  where hos_id='{$hid}' and  type=2  and  createTime BETWEEN '{$month_start}' and '{$month_end}'  ";
			$month_data['duihua'] = $this->db->get_one($sql);
			
			$sql = "select count(id) as liuliang FROM gossip_flow where hid='{$hid}' and access_time BETWEEN '{$month_start}' and '{$month_end}' ;";
			$month_data['liuliang'] = $this->db->get_one($sql);
			
			$sql = "select count(id) as yuyue FROM gossip_order where hos_id='{$hid}' and createTime BETWEEN '{$month_start}' and '{$month_end}' ;";
			$month_data['yuyue'] = $this->db->get_one($sql);
			
			$d = date("t");
			$dayt_data = array();
			for($i=1;$i<=$d;$i++){
				$arr = array();
				
				$date = strtotime(date('Y-m-'.$i)." 00:00:01"); 
				$date2 = strtotime(date('Y-m-'.$i)." 23:59:59"); 
				$sql ="select count(distinct vistor_id) as duihua FROM gossip_msg_{$aid}  where hos_id='{$hid}'  and  type=2 and  createTime BETWEEN '{$date}' and '{$date2}'  ";
				$arr = $this->db->get_row($sql);
				$sql = "select count(id) as liuliang FROM gossip_flow where hid='{$hid}' and access_time BETWEEN '{$date}' and '{$date2}' ;";
				$arr['liuliang'] = $this->db->get_one($sql);
				$sql = "select count(id) as yuyue FROM gossip_order where hos_id='{$hid}' and createTime BETWEEN '{$date}' and '{$date2}' ;";
				$arr['yuyue'] = $this->db->get_one($sql);
				
				
				$arr['date'] = date('Y-m-d',$date);
				
				$dayt_data[] = $arr; 
				
			}  
			$strs = "";
			foreach ($dayt_data as $key=>$val){
					$date_str = date('d',strtotime($val['date']));
					
				if($val['liuliang']!=''){
						$strs .= "{country:'本月流量', year: '".$date_str."',value:".$val['liuliang'];	
						$strs .= "},";
					}else{
						$strs .= "{country:'本月流量', year: '".$date_str."',value:0";	
						$strs .= "},";
					}
					
					if($val['duihua']!=''){
						$strs .= "{country:'本月对话', year: '".$date_str."',value:".$val['duihua'];	
						$strs .= "},";
					}else{
						$strs .= "{country:'本月对话', year: '".$date_str."',value:0";	
						$strs .= "},";
					}
					
					if($val['yuyue']!=''){
						$strs .= "{country:'留言预约', year: '".$date_str."',value:".$val['yuyue'];	
						$strs .= "},";
					}else{
						$strs .= "{country:'留言预约', year: '".$date_str."',value:0";	
						$strs .= "},";
					}
			}
			
			$this->template->assign('dayt_data2',$strs); 
			$this->template->assign('month_data',$month_data);
			make_json_result($this->template->fetch("index/index.html"),1,array());
		}
		
		
		function wechat_action() {
			$hid = get_hid();
			$aid = get_aid();
			$start_time = strtotime(date('Y-m', time()));
			$style = get_data('style') ? get_data('style') : "asc";
			$order_column = get_data("column") ? get_data("column") : "msg_count";
			
			if($style == "desc"){
				$order_style = "asc";
			}else if($style == 'asc'){
				$order_style = "desc";
			}
			
			$sql = "select count(id) from irpt_interface where id_hospital = {$hid}";
			$count = $this->db->get_one($sql);
			$p = new Page($count, 20);
			
			$table = "gossip_vistors_{$aid}";
			$sql = "select name, 
						(select count(*) from gossip_flow where name = i.name and id > 0 and access_time > {$start_time}) as flow_count,
						(select count(*) from gossip_order where source = i.name and id > 0 and createTime > {$start_time} and vistor_name not like '%测试%' and account not like '%测试%') as order_count,
						(select count(distinct openId) from {$table} where symbol = i.name and id > 0 and firstTime > {$start_time} and guest not like '%测试%' ) as msg_count 
					from irpt_interface as i where id_hospital = {$hid} order by {$order_column} {$order_style} limit ".$p->firstRow.",".$p->listRows;
			$wechat_stats = $this->db->get_all($sql);
					
			$this->template->assign('wechat_stats', $wechat_stats);
			$this->template->assign('order_style', $order_style);
			$this->template->assign('page', $p->show('wechat_action', $order_column, $style));
			make_json_result($this->template->fetch('index/wechat_action.html'), '', []);
		}		
		
	}


