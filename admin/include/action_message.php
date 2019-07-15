<?php

if (!defined('IN_APP'))
{
    die('Hacking attempt');
}

require_once(ROOT_PATH . 'framework/lib_common.php');

//信息操作API
class action_message
{
	//数据库
	var $db = null;
	//用户ID
	var $cid = 0;
	var $name = '游戏运营';
	//城市ID
	var $id = 0;
	//城市操作锁
	//TO-DO
	//var $lock = null;
	
	//错误处理
	var $error = 0;
		
	//构造函数
	function __construct(&$db, $cid, $ctid)
	{
		$this->__action_message(&$db, $cid, $ctid);
	}
	
	//初始化
	function __action_message(&$db, $cid, $ctid)
	{
		$this->db = &$db;
		
		$this->cid = $cid;
		$this->id = $ctid;
		
		//TO-DO Jean 
	//	$sql = 'select name from slg_character where id=' . $cid;
	//	$this->name = $this->db->get_one($sql, true);
		
		//TO-DO
		//require_once(ROOT_PATH . 'framework/cls_lock.php');
		//$this->lock = new cls_lock('city_' . $this->id);
	}
	
	function __get_character_name($id)
	{
		$sql = 'select name from slg_character where id=' . $id;
		return $this->db->get_one($sql, true);
	}
	
	function __check_all(&$title, &$content)
	{
		if(str_len($title) > 32) return false;

		//$title = badwords_filter($title);
		if(!$title) return false;
		
		if(str_len($content) > 256) return false;
		
		//$content = badwords_filter($content);
		
		if(!$content) return false;
		
		return true;

	}
	
	//发送系统公告
	function broadcast_system_message($name, &$title, &$content)
	{
		if(empty($name) || empty($title) || empty($content))
		{
			$this->error = 1;	
			return false;
		}
		
		if(!$this->__check_all($title, $content))
		{
			$this->error = 50;
			return false;
		}

		$this->db->auto_execute('slg_message', array('sender_name'=>$name,'title'=>$title,'content'=>$content,'type'=>1,'ctime'=>date('Y-m-d H:i:s')), 'INSERT');

		return true;
	}
	
	//群发信息
	function broadcast_message($ids, &$title, &$content)
	{
		//print_r($ids);
	//	exit;
		$this->id=0;

		if(empty($title) || empty($content))
		{
			$this->error = 1;
			return false;
		}
		
		if(!$this->__check_all($title, $content))
		{
			$this->error = 50;
			return false;
		}
		
		$now = date('Y-m-d H:i:s');
		$sql = 'insert into slg_message(receiver_id,sender_id,sender_name,title,content,type,ctime) values';
		
		$values = '';
		foreach($ids as $id)
		{
		//	echo $id.'<br>';
		
			if($values == '') $values .= '('.$id.','.$this->id.",'".$this->name."','".$title."','".$content."',3,'".$now."')";
			else $values .= ',('.$id.','.$this->id.",'".$this->name."','".$title."','".$content."',3,'".$now."')";
		}
		
		$sql .= $values;
		
	    //echo $sql;
	    //exit;
		
		
		$this->db->query($sql);
		
		return true;
	}
	
	//发送一条系统信息
	function send_system_message($id, $name, &$title, &$content)
	{
		if($id <= 0 || empty($title) || empty($content))
		{
			$this->error = 1;
			return false;
		}
		
		if(!$this->__check_all($title, $content))
		{
			$this->error = 50;
			return false;
		}
		
		$this->db->auto_execute('slg_message', array('receiver_id'=>$id,'sender_name'=>$name,'title'=>$title,'content'=>$content,'type'=>2,'ctime'=>date('Y-m-d H:i:s')), 'INSERT');

		return true;
	}
	
	//发送一条消息
	function send_message($id, &$title, &$content)
	{
		if($id <= 0)
		{
			$this->error = 79;
			return false;
		}
		
		if($id == $this->id)
		{
			$this->error = 77;
			return false;
		}
		
		if(empty($title) || empty($content))
		{
			$this->error = 78;
			return false;
		}
		
		if(!$this->__check_all($title, $content))
		{
			$this->error = 50;
			return false;
		}
		
		$rname = $this->__get_character_name($id);
		
		$this->db->auto_execute('slg_message', array('receiver_id'=>$id,'receiver_name'=>$rname,'sender_name'=>$this->name,'sender_id'=>$this->id,'title'=>$title,'content'=>$content,'type'=>0,'ctime'=>date('Y-m-d H:i:s')), 'INSERT');

		return true;
	}
	
	//读一条信息
	function read_message($id)
	{
		if($id <= 0)
		{
			$this->error = 1;
			return false;
		}
		
		$sql = 'select * from slg_message where id=' . $id;
		
		$msg = $this->db->get_row($sql, true);
		
		if($msg['status'] != 1) $this->db->auto_execute('slg_message', array('status'=>1), 'UPDATE', 'id='.$id);
		
		return $msg;
	}
	
	//取得系统公告
	function get_system_message()
	{
		$sql = 'select id,sender_id,sender_name,title,status,ctime from slg_message where type=1';
		
		return $this->db->get_all($sql);
	}
	
	//取得信息列表
	function get_message_list()
	{
		$sql = 'select id,sender_id,sender_name,title,status,ctime from slg_message where receiver_id=' . $this->cid . ' and type<>1 and status<>2';
		
		return $this->db->get_all($sql);
	}
	
	function get_new_message_count()
	{
		$sql = 'select count(*) from slg_message where type<>1 and status=0 and receiver_id=' . $this->cid;
		
		return $this->db->get_one($sql, true);
	}
	
	function get_new_message()
	{
		$sql = 'select count(*) from slg_message where receiver_id=' . $this->cid . ' and type<>1 and status=0';
		
		return $this->db->get_one($sql, true);
	}
	
	//删除信息
	function delete_message($id, $flag=true)
	{
		if($id <= 0)
		{
			$this->error = 1;
			return false;
		}
		
		$status='';
		if($flag) $status='status';
		else $status='status1';
		
		$this->db->auto_execute('slg_message', array($status=>2), 'UPDATE', 'id='.$id);
		
		return true;
	}
	//add by Jean 20081027.
	function getIdsByPlayerName($names){
		$wherestat = ' 1>1 ';
		foreach($names as $item){
			if(trim($item)!=='')
			$wherestat .=' or name="'.trim($item).'"';
		}
		
		$sql = 'select id,user_id from slg_character where '.$wherestat;
		//echo $sql;break;
		$data = $this->db->get_all($sql);
		
		$ids=array();
		if($data){
		foreach($data as $d){
			$ids[] = $d['user_id'];
		}}
	
		return $ids;
	}
}

?>