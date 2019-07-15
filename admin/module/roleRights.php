<?php
require_once(APP_PATH."/include/lib_rights.php");

class roleRights extends cls_base
{
	var $log;

	function init()
	{
         init_session();
		require_once(ROOT_PATH."_ext/auth_admin.php");
	}
	//主框架的页面显示
	function index()
	{
	  $this->show();
	}
	function show()
	{
		global $_TEMPLATE,$_DB,$_CFG;
		$role_sql="select id,name,comment,available from ".$_CFG['prefix']."admin_role order by id desc";
		$all_role=$_DB->get_all($role_sql);
		$_TEMPLATE->assign('all_role',$all_role);
		//$_TEMPLATE->display("rolerights/show.html");
		make_json_result($_TEMPLATE->fetch("rolerights/show.html"),1,array());
	}
	//show rights.
	function show_rights($menu="")
	{
	   global $_TEMPLATE,$_DB,$_CFG;
       $sql="select id,name,url,comment,available,sort,code,father_code,project from ".$_CFG['prefix']."admin_rights order by sort ";
	   $all_rights=$_DB->get_all($sql);

	   //deal 
	   if($menu=="")
	   {
	     $menuArray=$this->get_rights_tree();
	   }
	   else
	   {
	     $menuArray=$menu;
	   }
	   $_TEMPLATE->assign('menuArray',$menuArray);
	   make_json_result($_TEMPLATE->fetch("rolerights/show_rights.html"), '', array());
	}
	//get role right tree
	private function get_rights_tree()
	{
	   $fatherMenu = get_menu_info("0");
	   if($fatherMenu)
	   {
			foreach($fatherMenu as $rows)
			{
					$fatherId=$rows['code'];
					$nodeMenu= get_menu_info($fatherId);
					if($nodeMenu)
					{
						foreach($nodeMenu as $nodeRow)
						{
							$notesArray[]=array("id"=>$nodeRow['id'],"name"=>$nodeRow['name'],'url'=>$nodeRow['url'],'comment'=>$nodeRow['comment'],'code'=>$nodeRow['code'],'father_code'=>$nodeRow['father_code']);
						}
					}
					$menuArray[]=array("id"=>$rows['id'],"name"=>$rows['name'],'url'=>$rows['url'],'comment'=>$rows['comment'],'code'=>$nodeRow['code'],'father_code'=>$nodeRow['father_code'],"node"=>$notesArray);
					unset($notesArray);
			}
	   }
	   else   
	   {
		    $menuArray=array();
	   }
	        return $menuArray;
	}
	//save role right
	function save_role_rights()
    {
	    global $_TEMPLATE,$_DB,$_CFG;

		//get the parameter.
		$roleid     = get_data("roleid","i");//角色id
		$id         = get_data("id","i");//id
		$flag       = get_data("flag");//状态
      
		$right_rs   = $_DB->get_row("select code,father_code from ".$_CFG['prefix']."admin_rights where id='$id'");
        
		
		//get the father code.
		if($right_rs['code']!="" && $right_rs['father_code']=="0")
		{
		   $ff_code= $right_rs['code'];
		}else if($right_rs['father_code']!=""&& $right_rs['code']=="0")
		{
		   $ff_code= $right_rs['father_code'];
		}
		//get the father id
		$father_id =$_DB->get_one("select id from ".$_CFG['prefix']."admin_rights where code='$ff_code'");
        
		if($right_rs['code']!="0" && $right_rs['father_code']=="0")
		{//父。
		   //父下面的所有功能
		   $father_rights = $_DB->get_all("select * from ".$_CFG['prefix']."admin_rights where father_code='$ff_code'");
		   //父下面的count
		   $father_rights_count = $_DB->get_one("select count(*) from ".$_CFG['prefix']."admin_rights where father_code='$ff_code'");
		   if($flag=="checked")
		   {//全部选中操作。而且是父
				$wherestat = "('".$roleid."','".$id."')";
				if($father_rights_count>1)
			    {
					foreach($father_rights as $rrid)
					{
					   $wherestat .= ",('".$roleid."','".$rrid['id']."')";
					}
				}
				else	
				{
					$wherestat .= ",('".$roleid."','".$father_rights[0]['id']."')";
				}
				$wherestat=str_replace(",,",",",$wherestat);// ,,
				$wherestat=str_replace("), )","))",$wherestat);//))
				$wherestat=str_replace("(, (","((",$wherestat);//((
				$_DB->query("insert into ".$_CFG['prefix']."admin_role_rights (`role_id`,`rights_id`) values ".$wherestat);
				make_json_result('', 'insertok', array());
		   }
		   else if($flag=="cansel")
		   {//全部取消操作。而且是父
		       
			   $where =" role_id='$roleid' and (rights_id='$id' ";
			   if($father_rights_count>1)
			   {
					foreach($father_rights as $rrid)
					{
					   $where .=" or rights_id='".$rrid['id']."'";
					}
					   $where .=")";
				}
				else
				{
					   $where .=" or rights_id='".$rrid['id']."')";
				}
				$_DB->query("delete from ".$_CFG['prefix']."admin_role_rights where ".$where);
				make_json_result('', 'delok', array());
		   }
		}else
		{//非父
		   if($flag=="checked")
		   {//选中
			   //判断有没有父
			   $has_role_father=$_DB->get_one("select id from ".$_CFG['prefix']."admin_role_rights where role_id='$roleid' and rights_id='$father_id'");

               //insert the father 
			   if(!$has_role_father)
			   {
			        $_DB->query("insert into ".$_CFG['prefix']."admin_role_rights (`rights_id`,`role_id`) values('$father_id','$roleid')");
			   }
			   //insert the son 
			   $_DB->query("insert into ".$_CFG['prefix']."admin_role_rights (`rights_id`,`role_id`) values('$id','$roleid')");
			   make_json_result('', 'insertok', array());
		   }
		   else if($flag=="cansel")
		   {//取消
			   $_DB->query("delete from ".$_CFG['prefix']."admin_role_rights where role_id='$roleid' and rights_id='$id'");
			   //判断还有没有子，没有也把父给删掉
			   $has_father_rights=$_DB->get_all("select rights_id from  ".$_CFG['prefix']."admin_role_rights where role_id='$roleid' and rights_id in (select id from ".$_CFG['prefix']."admin_rights where father_code='$ff_code') ");			   
			   if(!$has_father_rights){
			       $_DB->query("delete from ".$_CFG['prefix']."admin_role_rights where role_id='$roleid' and rights_id='$father_id'");
			   }
               make_json_result('', 'delok', array());
		   }
		}
	}
	//get role rights
	function get_role_rights()
	{
	   global $_TEMPLATE,$_DB,$_CFG;

	   //get the parameter.
	   $rid     = get_data("roleid","i");
       $get_tree= $this->get_rights_tree();
	   //deal 
	   foreach($get_tree as &$m)
	   {
		   $father_role_rights=get_role_rights($m['id'],$rid);
		   if($father_role_rights!="")
		   {
		       $m['father_flag']="1";
		   }
		   foreach($m['node'] as &$n)
		   {
		       $son_role_rights=get_role_rights($n['id'],$rid);
			   if($son_role_rights!="")
			   {
		        $n['son_flag']="1";
		       }
		   }
	   }
	   $this->show_rights($get_tree);
	   exit;
	}
	/**
     *changede by 2009-03-12
	 *rights insert,update,delete. 
	 *
	**/
	//随机抽取1-99999不重复的随机数
	private function getrandomnum()
	{
		global $_DB,$_CFG;
		$a=0;
		while(true)
		{
			$a = rand (1,99999);
			$count = $_DB->get_one("select count(*) from  ".$_CFG['prefix']."admin_rights where `code`=$a");
			if($count>0)
				continue;
			else
				break;
		}
		return $a;
	}
	//insert rights
    function rights_add()
	{
	   global $_TEMPLATE,$_DB,$_CFG;
	   //取上级目录中有效
	   $father=$_DB->get_all("select `code`,`name`,`project` from ".$_CFG['prefix']."admin_rights where `code` <> 0");
	   $_TEMPLATE->assign('father',$father);
	   make_json_result($_TEMPLATE->fetch("rolerights/rights_add.html"), '', array());
	}
	//insert rights action
	function rights_add_action()
	{
		global $_DB,$_CFG;
		//get the parameter
		$name        = trim(get_data("name"));//name
		$url         = trim(get_data("url"));//url
		$comment     = trim(get_data("comment"));//comment
		$order       = get_data("order","i");//order
		$father_id   = get_data("father_id","i");//father
		$project     = trim(get_data("project"));//project
		$flag        = get_data("flag","i");//是不是有效
		$is_show     = get_data("is_show","i");//是不是有效
		
		//判断是不是根目录
		$father_code = '0';
		$code        = '0';
		//if father id is root
		if($father_id=='0')
		{
		  $code=$this->getrandomnum();
		}
		else
		{
		  $father_code = $father_id;
		}		
		//if data is not null
		$has=$_DB->get_one("select id from ".$_CFG['prefix']."admin_rights where name='$name' and project='$project' ");
		if($has)
		{
		   make_json_result('', '0', array());
		   exit;
		}
		//insert into sql
		$slq = "insert into ".$_CFG['prefix']."admin_rights (`name`,`url`,`comment`,`sort`,`project`,`available`,`father_code`,`code`,`is_show`) values('$name','$url','$comment','$order','$project','$flag','$father_code','$code','$is_show')";
		$_DB->query($slq);
	    make_json_result('', 'ok', array());
	}
	//get right info
	function get_right_info()
	{
	    global $_TEMPLATE,$_DB,$_CFG;
		//get the parameter.
		$id            = get_data("id","i");
		$is_select     = "yes";

		$sql           = "select `id`,`name`,`url`,`comment`,`available`,`sort`,`code`,`father_code`,`project`,`is_show` from 
		                  ".$_CFG['prefix']."admin_rights 
						  where `id`=$id ";
		$right         = $_DB->get_row($sql);
		//判断是不是根目录，是的话，判断它下面有没有子，有的话，就不能选在，否则可以选
		if($right['father_code']=="0")
		{
		   //是
		   $this_son_rs= $_DB->get_all("select * from ".$_CFG['prefix']."admin_rights where `father_code`=".$right['code']);
		   if($this_son_rs)
		   {
		     $is_select= "no";
		   }
		}
		$father_sql    = "select `id`,`name`,`code`,`father_code`,`project` from ".$_CFG['prefix']."admin_rights where `code`<>0  ";
		$father_rs     = $_DB->get_all($father_sql);

		$_TEMPLATE->assign('father_rs',$father_rs);
		$_TEMPLATE->assign('is_select',$is_select);
		$_TEMPLATE->assign('right',$right);
		
	    make_json_result($_TEMPLATE->fetch("rolerights/get_right.html"), '', array());
	}
	//the right save
    function right_detail_save()
	{
	    global $_DB,$_CFG;

        $name            = trim(get_data("name"));
		$url             = trim(get_data("url"));
		$comment         = trim(get_data("comment"));
		$order           = get_data("order","i");
		$father_id       = get_data("father_id","i");
		$project         = trim(get_data("project"));
		$flag            = get_data("flag","i");
		$id              = get_data("id","i");
		$is_show              = get_data("is_show","i");
		
		$hidden_name     = trim(get_data("hidden_name"));
		$hidden_project  = trim(get_data("hidden_project"));
		$hidden_father   = get_data("hidden_father","i");

		if($hidden_name!=$name || $hidden_project !=$project)
		{
		   $has_name=$_DB->get_one("select name from ".$_CFG['prefix']."admin_rights where name='$name' and project='$project' ");
		   if($has_name){
		      make_json_result('', 'no', array());
			  exit;
		   }
		}
		$sql             = "update ".$_CFG['prefix']."admin_rights  set `name`='$name',`url`='$url',`comment`='$comment',`sort`=$order,`project`='$project',`available`=$flag,`is_show`=$is_show";
		//if father id is not root
		if($hidden_father!=$father_id)
		{
		  if($father_id=="0"){
			  $code=$this->getrandomnum();
			  $sql      .= ",`father_code`=0,`code`=".$code;
		  }else{
		      $sql      .= ",`father_code`=$father_id,`code`=0"; 
		  } 
		}
		$sql            .=" where `id`=".$id;
		$_DB->query($sql);
        make_json_result('', 'ok', array());
	}
    /****
	*****
	****/
	//delete the right
	function del_right()
	{
	   global $_DB,$_CFG;
       
	   //get the parameter.
	   $id =  get_data("id","i");

	   $has_user=$_DB->get_all("select * from ".$_CFG['prefix']."admin_role_rights where rights_id='$id'");
	   if($has_user)
	   {//has the role rights
		   make_json_result('', 'has', array());
		   exit;
	   }
	   $is_father=$_DB->get_one("select father_code from ".$_CFG['prefix']."admin_rights where id='$id'");
	   if($is_father=="0")
	   {//父
		  //有没有子
		  $has_father_rights=$_DB->get_all("select * from ".$_CFG['prefix']."admin_rights where father_code='$id'");
		  if($has_father_rights)
		  {
			  //删除父及它下面所有子功能
			  $_DB->query("delete from ".$_CFG['prefix']."admin_rights where id in(select id from ".$_CFG['prefix']."admin_rights where father_code='$id'),'$id')");
		  }
		  else
		  {
		      //只有父
			  $_DB->query("delete from ".$_CFG['prefix']."admin_rights where id='$id'");
		  }
		  make_json_result('', 'ok', array());
		  exit;
	   }
	   else
	   {//非父,删除，并不删除父
          $_DB->query("delete from ".$_CFG['prefix']."admin_rights where id ='$id'");
		  make_json_result('', 'ok', array());
		  exit;
	   }
	}
}

?>