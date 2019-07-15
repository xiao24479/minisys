<?php
require_once(APP_PATH."/include/libs.php");
class monitor extends cls_base{
	
	var $db;
	
	var $template;
	
	// init
	
	function init() {
	
		global $_DB,$_TEMPLATE;
	
		$this->db = $_DB;
	
		$this->template = $_TEMPLATE;
	
		require_once(ROOT_PATH . "_ext/auth_admin.php");
	
		require_once(APP_PATH . '/include/lib_common.php');
	
		require_once(ROOT_PATH."_ext/page.class.php");
		cuckhid(get_hid());
		cuckaid(get_hid(),get_aid()); 
	}
	
	
	public function monitor_list(){
		
		$hid=get_hid();
		$aid=get_aid();

		$login_name=get_data('login_name');
		
		$w='ua.hos_id ='.$hid;
		
		$w.= " and ua.access_id='{$aid}'";
		
	    if($login_name!=''){
			$w.= " and u.login_name='{$login_name}'";
		}
		
		//查出索引总数
		$sql="SELECT count(ua.id) as a FROM gossip_user_access as ua 
		     left join admin_user as u on ua.user_id=u.id
		     where $w";
		
		$count=$this->db->get_one($sql);//总记录数数
		
		
		$parameter  = '';
		
		if($login_name!=''){
			$parameter .= '&login_name='.$login_name;
		}

		$p = new Page($count,20,$parameter);
		
		$sql="SELECT u.* FROM gossip_user_access as ua left join admin_user as u on ua.user_id=u.id
		      where $w ORDER BY u.id DESC LIMIT ".$p->firstRow.",".$p->listRows." ;";
		
		$data=$this->db->get_all($sql);

		$this->template->assign('data',$data);
		
		$this->template->assign('login_name',$login_name);
		
		$this->template->assign('page',$p->show2());
		
		$this->template->display("monitor/monitor_list.htm");

	}
	
	
	/**
	 * 修改客服
	 */
	public function edit_monitor(){
	
		$id=get_data('id','i');
		
		$sql="select * from admin_user where id=".$id;
		$data=$this->db->get_row($sql);
		$this->template->assign('data',$data);

		make_json_result($this->template->fetch("monitor/edit_monitor.htm"), '', array());
	}
	
	/**
	 * 更新客服信息
	 */
	
	public function updata_monitor(){
	
		$id=get_data('id','i');
		$autograph=get_data('e_autograph');
		$path=get_data('e_path');
		
		//首先删除原有的图片
	 	$sql="select path from admin_user where id='{$id}'";
		$runpath=$this->db->get_one($sql);
		$pos=strpos($runpath,"data");
		$url=substr($runpath,$pos);
		is_file(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$url) && @unlink(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.$url);

		
		$sql="UPDATE `admin_user` SET `autograph`='".$autograph."',`path`='".$path."' WHERE id=".$id;
		$r=$this->db->query($sql);
		if($r){
			unset($_POST);
			make_json_result('<foot class="msg">修改成功!</foot>', '', array());
		}else{
			make_json_result('<foot class="msg">修改失败,请检查表单!</foot>', '', array());
		} 
	}


}