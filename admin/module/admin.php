<?php



require_once(APP_PATH . "/include/lib_rights.php") ;

require_once(APP_PATH . "/include/lib_log.php") ;

require_once(APP_PATH . "/include/lib_login.php") ;

require_once(APP_PATH . "/include/lib_common.php") ;




/**

 * 系统权限信息

  +----------------------------------------------------------

 * @author 围剿

 * Time:2011-11-1

  +----------------------------------------------------------

 */

class admin extends cls_base

{



	
	
	
	

	/**

	 * 初始化

	  +----------------------------------------------------------

	 * (non-PHPdoc)

	 * @see _frame/cls_base::init()

	 * Time:2011-11-1

	  +----------------------------------------------------------

	 */

	function init()

	{

		global $_TEMPLATE ;

		$_TEMPLATE->assign( "HOME_URL" , HOME_URL ) ;

		init_session() ;

		parent::init() ;

	}

/*移动端判断*/
function isMobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 


	/**

	 * 我的信息首页

	  +----------------------------------------------------------

	 * Time:2011-11-1

	  +----------------------------------------------------------

	 */

	function myinfoOLD()

	{

		global $_TEMPLATE , $_DB ;

		$id = $this->getUserId() ;

		$sql = "select * from irpt_notice_user where user_id=" . $id . " ORDER BY  `add_time` DESC " ;

		$row = $_DB->get_all( $sql ) ;

		$sql = "UPDATE  `irpt_notice_user`  SET `look` =  '1'  WHERE user_id=" . $id ;

		$_DB->query( $sql ) ;

		$_TEMPLATE->assign( 'row' , $row ) ;

		$_TEMPLATE->display( 'admin/myinfo.html' ) ;

	}


	
	function myinfo(){
		global $_TEMPLATE , $_DB ;
		init_session();
		$hid = isset($_SESSION['hospital_id'])?$_SESSION['hospital_id']:'0';
		$start_start_day = date("Y-m-1");
		$end_start_day = date('y-m-d');
		$this->template->assign( 'start_start_day' , $start_start_day ) ;
		$this->template->assign( 'end_start_day' , $end_start_day ) ;
		$this->template->display( 'admin/data.html' ) ;

	
	}
	
	
	



	function myDetailInfo()

	{

		$nid = get_data( "nid" , "i" ) ;

		$data = $this->db->get_row( "select * from irpt_notice_role where id={$nid}" ) ;

		if( $data )

		{

			$this->db->query( "update irpt_notice_role set `status`=1 where id={$nid}" ) ;

		}

		$this->template->assign( 'data' , $data ) ;

		$this->template->display( 'admin/mydetailinfo.html' ) ;

	}



	function sendMess()

	{

		$dopost = trim( get_data( "dopost" ) ) ;

		if( $dopost == "send" )

		{

			$roleId = get_data( "role_id" , "i" ) ;

			$title = trim( get_data( "title" ) ) ;

			$content = trim( htmlspecialchars( get_data( "content" ) ) ) ;

			if( strlen( $title ) < 1 || strlen( $content ) < 1 )

			{

				redirect( "标题或内容不能为空,正在返回..." , "main.php?m=admin&a=sendMess" , "1" , true ) ;

				exit ;

			}

			$sql = "insert into irpt_notice_role (`title`,`content`,`from_user_id`,`from_user_name`,`to_role_id`,`from_time`) values('{$title}','{$content}','" . intval( $_SESSION[ 'admin_id' ] ) . "','" . trim( $_SESSION[ 'admin_name' ] ) . "','{$roleId}','" . time() . "')" ;

			$this->db->query( $sql ) ;

			$add = $this->db->get_one( "select @@identity" ) ;

			if( $add > 0 )

			{

				redirect( "发送成功,正在跳转..." , "main.php?m=admin&a=myinfo" , "1" , true ) ;

			}

			exit ;

		}

		$roleData = $this->db->get_all( "select id,name from admin_role where available=1" ) ;

		$this->template->assign( 'roleData' , $roleData ) ;

		$this->template->display( 'admin/sendmess.html' ) ;

	}



	function messageList()

	{

		global $_TEMPLATE , $_DB ;

		$dopost = trim( get_data( "dopost" ) ) ;

		if( $dopost == "del" )

		{

			$did = get_data( "did" , "i" ) ;

			$this->db->query( "update irpt_notice_role set `status`=2 where id={$did}" ) ;

		}



		$rowsPerPage = '25' ;

		$pagenavigate_sql = "select * from irpt_notice_role order by `status` asc" ;

		//echo $pagenavigate_sql;

		$pagenavigate_submiturl = 'admin.php' ;

		$pagenavigate_para = 'm=admin&a=messageList' ;

		include_once(ROOT_PATH . "_ext/lib_pagination.php") ;

		$this->template->display( 'admin/message_list.html' ) ;

	}



	function getUserId()

	{

		init_session() ;

		$userid = isset( $_SESSION[ 'admin_id' ] ) ? $_SESSION[ 'admin_id' ] : '0' ;



		return $userid ;

	}


	/**

	 *  修改左侧菜单 BY taozy.wu

	 */

	function getMenuinfo()

	{

		$menuetemp = $this->getMenu_() ; //获取左侧栏。,这个为以后的左侧的做铺垫。。

		$_SESSION[ 'menu' ] = $menuetemp ;

		$menueCheckdata = $this->menueValidate( $menuetemp ) ;

		$_SESSION[ 'menu_check' ] = $menueCheckdata ;

		foreach( $_SESSION[ 'menu' ] as & $f1 )

		{

			if( $f1[ 'node' ] != "" ) {

				$f1[ 'nodeState' ] = 1 ;

			} else {

				$f1[ 'nodeState' ] = 0 ;

			}

		}
		
		$catalogs = array(
			array(
				'catalog'=>'系统管理',
				'class'=>'fa-cog'
			),
			array(
				'catalog'=>'机器管理',
				'class'=>'fa-laptop'
			),
			array(
				'catalog'=>'数据分析',
				'class'=>'fa-bar-chart-o'
			),
			array(
				'catalog'=>'客户管理',
				'class'=>'fa-user'
			)
			,
			array(
				'catalog'=>'客服管理',
				'class'=>'fa-phone'
			),
			array(
				'catalog'=>'信息管理',
				'class'=>'fa-list-alt'
			),
			array(
				'catalog'=>'项目管理',
				'class'=>'fa-tasks'
			),
			array(
				'catalog'=>'客户分类',
				'class'=>'fa-sitemap'
			),
			array(
				'catalog'=>'文章管理',
				'class'=>'fa-book'
			),
			array(
				'catalog'=>'医生管理',
				'class'=>'fa-medkit'
			),
			
		);
		
		// 增加project的处理
		$projectData = $this->db->get_all( "SELECT DISTINCT project FROM admin_rights WHERE id IN (SELECT ar.rights_id FROM admin_role_rights AS ar,admin_user_role AS au WHERE ar.role_id=au.role_id AND au.user_id=".$_SESSION['admin_id'].") AND available=1 ORDER BY  `sort` DESC  " ) ;
		
		foreach($projectData as $key=>$val){
			foreach($catalogs as $k=>$v){
				if($val['project']==$v['catalog']){
					$projectData[$key]['class'] = $v['class'];
				}
			}
		}
		return $projectData;
	}



	/**

	 * 后台左侧菜单点击数据 BY taozy.wu

	 */

	function ajaxShowMenuData()

	{

		if( !isset( $_SESSION[ 'admin_id' ] ) )

		{

			make_json_result( '' , 'LOST' , array( ) ) ;

		}

		else

		{

			$data = array( ) ;

			$str = trim( get_data( "str" ) ) ;
			//var_dump($str);
			$arr = isset( $_SESSION[ 'menu' ] ) ? $_SESSION[ 'menu' ] : array( ) ;
			//var_dump();
			if( $arr )

			{

				foreach( $arr as $p )

				{

					if( !empty($str) && $p[ 'project' ] == $str )

					{

						$data[ ] = $p ;

					}

				}

			}
			$this->template->assign( "menuData" , $data ) ;

			make_json_result( $this->template->fetch( "newadmin/ajax_show_menu_data.html" ) , '' , array( ) ) ;

		}

	}

	function ajaxShowMenuData2(){
		global $_DB,$_TEMPLATE;
		$code = get_data('k');
		
		if( !isset( $_SESSION[ 'admin_id' ] ) )

		{

			make_json_result( '' , 'LOST' , array( ) ) ;

		}

		else

		{
			
			if($code){
				$uid = $_SESSION['admin_id'];
				$sql = "select role.id from admin_user_role as user_role,admin_role as role where user_role.user_id='{$uid}' and role.id=user_role.role_id;";
				$role_ids = $_DB->get_all($sql);
				$role_ids = array_column($role_ids,'id');
				$role_ids = implode(',',$role_ids);
				
				$sql = "select ar.* from admin_rights as ar,admin_role_rights as arr where ar.father_code='{$code}' and ar.is_show=1 and arr.role_id in($role_ids) and arr.rights_id=ar.id";
				$all = $_DB->get_all($sql);
				$_TEMPLATE->assign('all',$all);
				make_json_result($_TEMPLATE->fetch("newadmin/ajax_show_menu_data2.html"),1,array());
			}else{
				make_json_result( '' , 'LOST' , array( ) ) ;
			}
			
			
			
		}
		
		
	}

	private function menueValidate( $arraymenu_ )

	{

		$menuCheck_ = array( ) ;

		foreach( $arraymenu_ as $amenu )

		{

			$aurl = $amenu[ 'url' ] ;

			if( $aurl )

				array_push( $menuCheck_ , $this->menuParameProc( $aurl ) ) ;

			$nodes = $amenu[ "node" ] ;

			if( $nodes )

				foreach( $nodes as $anode )

					array_push( $menuCheck_ , $this->menuParameProc( $anode[ "url" ] ) ) ;

		}

		return $menuCheck_ ;

	}



	private function menuParameProc( $aurl )

	{

		if( !$aurl || trim( $aurl ) == "" )

			return "" ;

		$url = $aurl ;

		$ps = strrpos( $url , "#" ) ;

		$pq = strrpos( $url , "?" ) ;

		if( $ps > $pq )

			$s = substr( $url , $pq + 1 , $ps - $pq - 1 ) ;

		else

			$s = substr( $url , $pq + 1 ) ;

		$arr = PHP_VERSION >= 5.3 ? preg_split( '/&/' , $s ) : @split( '&' , $s ) ;

		$result = array( ) ;

		foreach( $arr as $item )

		{

			$temp = PHP_VERSION >= 5.3 ? preg_split( '/=/' , $item ) : @split( '=' , $item ) ;

			if( sizeof( $temp ) == 2 )

				$result[ $temp[ 0 ] ] = $temp[ 1 ] ;

			else

				$result[ $temp[ 0 ] ] = $temp[ 0 ] ;

		}

		$m = $result[ "m" ] ;

		$a = $result[ "a" ] ;

		if( !$a )

			$a = "index" ;

		return $m . "_" . $a ;

	}



	private function getMenu_()

	{

		global $_TEMPLATE ;

		//获取父节点

		$fatherMenu = menuInfo_( $_SESSION[ 'admin_id' ] , "0" ) ;



		foreach( $fatherMenu as $rows )

		{

			$fatherId = $rows[ 'code' ] ;

			//子级菜单信息

			$nodeMenu = menuInfo_( $_SESSION[ 'admin_id' ] , $fatherId ) ;





			if( $nodeMenu )

			{

				//处理

				foreach( $nodeMenu as $nodeRow )

				{

					$notesArray[ ] = array( "id" => $nodeRow[ 'id' ] , "name" => $nodeRow[ 'name' ] , 'url' => $nodeRow[ 'url' ] , 'comment' => $nodeRow[ 'comment' ] , 'code' => $nodeRow[ 'code' ] , 'father_code' => $nodeRow[ 'father_code' ] , 'is_show' => $nodeRow[ 'is_show' ] ) ;

				}

			}

			//存放到数组中

			$menuArray[ ] = array( "id" => $fatherId , "name" => $rows[ 'name' ] , 'url' => $rows[ 'url' ] , 'comment' => $rows[ 'comment' ] , 'code' => $rows[ 'code' ] , 'father_code' => $rows[ 'father_code' ] , "node" => $notesArray , 'is_show' => $nodeRow[ 'is_show' ] , 'project' => $rows[ 'project' ] ) ;

			unset( $notesArray ) ;

		}



		return $menuArray ;

	}


	
	
	
	
	
	

	//登录页面

	function login( $errormsg = "" )

	{

		global $_TEMPLATE, $_DB;
		$isMobile = $this->isMobile();
		
		$error_msg = "" ; //初始化变量

		$from = "" ; //初始化变量

		if( $errormsg )

			$_TEMPLATE->assign( "errormsg" , $errormsg ) ;



		if( isset( $_SESSION[ 'admin_id' ] ) && $_SESSION[ 'admin_id' ] != "" )

		{//判断是不是登录过？
			
			$hosp		 = $this->getHospital(); //取得用户分配的医院
			$projectData = $this->getMenuinfo();
			$hid = $_SESSION['hospital_id'];
			
			$sql = "select name from irpt_hospital where  id ='{$hid}' ";
			$hpl = $_DB->get_one($sql);
			
			$_TEMPLATE->assign('hpl', $hpl);
			$_TEMPLATE->assign('projectData',$projectData);
			$_TEMPLATE->assign('hosp',$hosp);
			$_TEMPLATE->assign('hid', $hid);
			$_TEMPLATE->assign("adminname", $_SESSION[ 'admin_name' ] ) ;
			 
			if($isMobile){
				$_TEMPLATE->display( "newadmin/frame_mobile.html" ) ;
			}else{
				$_TEMPLATE->display( "newadmin/frame.html" ) ;
			}

		}

		else

		{		//没登录
			
			$login_name=isset($_COOKIE['login_name'])?$_COOKIE['login_name'] : '' ;
			$login_passwd=isset($_COOKIE['login_passwd'])?$_COOKIE['login_passwd'] : '' ;
			$re_status=isset($_COOKIE['re_status'])?$_COOKIE['re_status']:'';
			$_TEMPLATE->assign( "login_name" , $login_name ) ;
			$_TEMPLATE->assign( "login_passwd" , $login_passwd ) ;
			$_TEMPLATE->assign( "re_status" , $re_status ) ;

			$_TEMPLATE->assign( "from" , $from ) ;

			$_TEMPLATE->assign( "SYS_NAME" , SYS_NAME ) ;

			$_TEMPLATE->display( "admin/login.html" ) ;

		}

	}



	//处理登录

	function loginAction()
	{  
		global $_TEMPLATE,$_DB;
		
		$isMobile = $this->isMobile();
		$userinfo = "" ;
		
		$_TEMPLATE->assign('hid',$_SESSION['hospital_id']);
		
		//判断是不是登录过
		if( isset( $_SESSION[ 'admin_id' ] ) && $_SESSION[ 'admin_id' ] != "" ){
			$hosp = $this->getHospital(); //取得用户分配的医院
			$projectData = $this->getMenuinfo();
			
			$hid = $_SESSION['hospital_id'];
			$sql = "select name from irpt_hospital where  id ='{$hid}'; ";
			$hpl = $_DB->get_one($sql);
			
			$_TEMPLATE->assign('hpl',$hpl);
			$_TEMPLATE->assign('projectData',$projectData);
			$_TEMPLATE->assign('hosp',$hosp);
			$_TEMPLATE->assign('hid',$hid);
			$_TEMPLATE->assign("adminname", $_SESSION[ 'admin_name' ] ) ;
			if($isMobile){
				$_TEMPLATE->display( "newadmin/frame_mobile.html" ) ;
			}else{
				$_TEMPLATE->display( "newadmin/frame.html" ) ;
			}
			exit ;

		}

		$username = addslashes( trim( get_data( "login_name" ) ) ) ; //用户名

		$password = addslashes( trim( get_data( "login_passwd" ) ) ) ; //密码
		
		$re_status = get_data("re_status");//是否记录

		$secode = addslashes( strtolower( trim( get_data( "yanzheng" ) ) ) ) ; //验证码
		
		$cookie_timeout=time()+36000*24*365;
		if(!empty($re_status) && $re_status){//echo "记住我";
			setcookie("login_name", $username,$cookie_timeout );
			setcookie("login_passwd", $password,$cookie_timeout );
			setcookie("re_status", true, $cookie_timeout);
		}else{//echo "不记住";
			setcookie("login_name", null, $cookie_timeout);
			setcookie("login_passwd", null, $cookie_timeout);
			setcookie("re_status", null, $cookie_timeout);
		}

		$code = get_data( 'code' ) ;

		//$from = $_SERVER['REQUEST_URI'] ; 



		$errorcode = 0 ;

		if( $userinfo = checkuser( $username , md5( $password ) , $errorcode ) )

		{
			$lifeTime = 24 * 3600; 
			session_set_cookie_params($lifeTime); 

			$_SESSION[ 'admin_id' ] = $userinfo[ 'id' ] ;

			$_SESSION[ 'admin_name' ] = $username ;

			$_SESSION[ 'is_login' ] = md5( time() ) ;  

			$_SESSION[ 'login_session_time' ] = time() ;



			if( isset( $userinfo[ 'sp_id' ] ) )

			{

				$_SESSION[ 'sp_id' ] = $userinfo[ 'sp_id' ] ;

				$_SESSION[ 'sp_name' ] = $userinfo[ 'sp_name' ] ;

				$_SESSION[ 'log_level' ] = $userinfo[ 'log_level' ] ;

			}

			//登录成功，要记录日志

			$array = array( ) ; //初始化

			$array = array( 'user_id' => $_SESSION[ 'admin_id' ] , 'login_name' => $_SESSION[ 'admin_name' ] , 'login_time' => date( 'Y-m-d H:i:s' ) , 'ip' => getip() , 'sessionid' => session_id() ) ;

			//判断是不是存在

			if( is_login_log( session_id() ) )

			{

				update_login_log( '' , session_id() ) ;

			}

			else

			{

				insert_login_log( $array ) ;

			}

			//登录成功，应该有个记录

			$login = array(

				'id' => $_SESSION[ 'admin_id' ] ,

				'is_login' => $_SESSION[ 'is_login' ] ,

				) ;



			update_login_session( $login ) ;



 			

			if( $from != "" )

			{

				redirect( "" , "$from" ) ; //跳转$from

			}

			else

			{//跳转
				
				
				
				$projectData = $this->getMenuinfo();
				$hosp = $this->getHospital(); //取得用户分配的医院
			
			  //默认选择一个项目
				$sql = "select irpt_hospital.* from irpt_hospital,irpt_hospital_admin where  irpt_hospital_admin.id_hospital =irpt_hospital.id and irpt_hospital.top_id > 0  and irpt_hospital_admin.id_admin='".$_SESSION[ 'admin_id' ]."'; ";
				$hpl = $_DB->get_row($sql);
				
				$sql = "select id from gossip_access where hos_id = " . $hpl['id'];
				$aid = $_DB->get_one($sql);
				
				$_SESSION['hospital_id'] = $hpl['id'];
				$_SESSION['access_id'] = $aid;
				
				$_TEMPLATE->assign('hpl',$hpl['name']);
				$_TEMPLATE->assign('hid',$hpl['id']);
				$_TEMPLATE->assign('hosp',$hosp);
				$_TEMPLATE->assign('projectData',$projectData);
				$_TEMPLATE->assign("adminname", $_SESSION[ 'admin_name' ] ) ;
				
				if($isMobile){
					$_TEMPLATE->display( "newadmin/frame_mobile.html" );
				}else{
					$_TEMPLATE->display( "newadmin/frame.html" ) ;
				}

			}

		}

		else

		{

			if( $errorcode == 1 )

				$this->login( "用户名或密码错误" ) ;

			else

				$this->login( "服务器拒绝访问" ) ;

		}

	}



	//处理注销

	function logout()

	{

		update_login_log( date( 'Y-m-d H:i:s' ) , session_id() ) ;

		unset( $_SESSION[ 'admin_id' ] ) ; //unset id

		unset( $_SESSION[ 'admin_name' ] ) ; //unset name

		unset( $_SESSION[ 'password' ] ) ; //unset password

		unset( $_SESSION[ 'menu' ] ) ; //unset menu

		unset( $_SESSION[ 'sp_id' ] ) ; //unset menu

		unset( $_SESSION[ 'log_level' ] ) ; //unset menu

		unset( $_SESSION[ "menu_check" ] ) ;

		session_destroy() ;

		$_SESSION = array( ) ;

		redirect( "注销成功,正在返回..." , "main.php?m=admin&a=login" , "1" , true ) ;

	}


	function register_close()

	{

		global $_TEMPLATE ; 

		$_TEMPLATE->display( "admin/register.html" ) ;

	}

	function registerAction_close()

	{

		global $_TEMPLATE ;



		$realname = addslashes( trim( get_data( "realname" ) ) ) ; 

		$username = addslashes( trim( get_data( "username" ) ) ) ; 

		$password = addslashes( trim( get_data( "password" ) ) ) ; 

		$ctlpassword = addslashes( trim( get_data( "ctlpassword" ) ) ) ; 

		$email = addslashes( trim( get_data( "email" ) ) ) ; 

		$address = addslashes( trim( get_data( "address" ) ) ) ; 



		if( strlen( $username ) < 6 || strlen( $password ) < 6 )

		{

			sa_exit( "用户名或密码长度不能短于6位" , "main.php?m=admin&a=login" ) ;

		}

		if( eregi( "[<>{}(),%#|^&!`$]" , $username ) )

		{

			sa_exit( "用户名只能用a-z,0-9和'_'线组成" , "main.php?m=admin&a=login" ) ;

		}

		if( $username == $password )

		{

			sa_exit( "密码不能等于用户名" , "main.php?m=admin&a=login" ) ;

		}

		if( $password != $ctlpassword )

		{

			sa_exit( "两次输入密码不同" , "main.php?m=admin&a=login" ) ;

		}

		if( !eregi( "^[_.0-9a-z-]+@([0-9a-z][0-9a-z-]+.)+[a-z]{2,3}$" , $email ) )

		{

			sa_exit( "email格式不对" , "main.php?m=admin&a=login" ) ;

		}

		$isok = registerok( $realname , $username , $password , $email , $address ) ; //注册

		if( $isok == 1 )

		{

			redirect( "注册成功.正在返回..." , "main.php?m=admin&a=login" ) ;

		}

		else

		{

			sa_exit( "注册失败" , "main.php?m=admin&a=login" ) ;

		}

		unset( $isok ) ;

	}



	function getHeaderOLD()

	{

		global $_TEMPLATE , $_DB ;



		$verify = verify_login() ; 

		if( $verify == false )

		{

			$_TEMPLATE->display( "admin/reloginmid.html" ) ;

		}

		else

		{



			$sql = "SELECT count(*) as a FROM `irpt_notice_user`  WHERE look=0 and `user_id` = " . $_SESSION[ 'admin_id' ] ;

			$c = $_DB->get_one( $sql ) ;

			$_TEMPLATE->assign( "count" , $c ) ;

			$_TEMPLATE->assign( "adminname" , $_SESSION[ 'admin_name' ] ) ;

			$_TEMPLATE->assign( "SYS_NAME" , SYS_NAME ) ;
	
			$_TEMPLATE->display( "admin/header.html" ) ;

		}

	}
	

	function getHeader()

	{

		$verify = verify_login() ; 

		if( $verify == false )

		{

			$this->template->display( "admin/reloginmid.html" ) ;

		}

		else

		{


			$count = 0 ;

			$roleData = $this->db->get_all( "select role_id from admin_user_role where user_id=" . intval( $_SESSION[ 'admin_id' ] ) ) ;

			if( $roleData )

			{

				foreach( $roleData as $v )

				{

					$count += $this->db->get_one( "select count(*) from irpt_notice_role where `status`=0 and to_role_id={$v[ 'role_id' ]}" ) ;

				}

			}

			$hosp = $this->getHospital(); //取得用户分配的医院

			$this->template->assign('hospital_list',$hosp);

			$this->template->assign( "count" , $count ) ;
			$this->template->assign( "hid" , get_hid() ) ; 
			
			$this->template->assign( "access_list" , $this->getAccess($_SESSION[ 'admin_id' ],get_hid())) ;
			$this->template->assign( "aid" , get_aid() ) ;
			
			$this->template->assign( "adminname" , $_SESSION[ 'admin_name' ] ) ;

			$this->template->assign( "SYS_NAME" , SYS_NAME ) ;

			$this->template->display( "admin/header.html" ) ;

		}

	}
	
	function set_hospital(){
		global $_TEMPLATE,$_DB;
		init_session();
		$hid = get_data('hid');
		$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
		$hospital = $_DB->get_row("select id_hospital,hospital_name from irpt_hospital_admin where id_admin='$userid' and is_delete<>1   and id_hospital = $hid  ");
		if($userid==0) die;
		$_SESSION[ 'hospital_id' ] = $hospital[ 'id_hospital' ] ;
		
		if(isset($_SESSION['access_id']))unset($_SESSION['access_id']);
		
		$all=$this->getAccess($userid,$_SESSION[ 'hospital_id' ]);
		
		$_TEMPLATE->assign('all',$all);
		
		make_json_result($_TEMPLATE->fetch("admin/get_access.html"), '', array());

	}
	
	
	function set_access(){
		global $_DB;
		init_session();
		$hid = get_data('hid');
		$aid = get_data('aid');
		$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
		$access = $_DB->get_row("select access_id from gossip_user_access where user_id=$userid and hos_id = $hid and access_id=$aid");
		if($userid==0) die;
		$_SESSION[ 'access_id' ] = $access[ 'access_id' ] ;
		make_json_result(1, 1, array());
	
	}
	
	
	function getHospital()
	{
		global $_DB;
		init_session();
		$userid = isset($_SESSION['admin_id'])?$_SESSION['admin_id']:'0';
		if($userid==0) die;
		
		$hospital = $_DB->get_all("select ha.id_hospital, h.name as hospital_name, h.top_id from irpt_hospital_admin as ha, irpt_hospital as h where ha.id_admin='$userid' and ha.id_hospital = h.id group by h.id  ORDER BY  ha.`hospital_name` ASC ");
		 //医院
		$hospital_data = array();
		foreach($hospital as $hospital_temp){
			if($hospital_temp['top_id'] ==0 ){
				$check =0 ;
				foreach($hospital_data as $hospital_data_temp){
					if(strcmp($hospital_temp['hospital_name'],$hospital_data_temp['hospital_name']) == 0){
						$check =1;break;
					} 
				}
				if(empty($check)){
					$hospital_data[] = $hospital_temp;
				}
			}
		}   
		
		//科室
		foreach($hospital_data as $hospital_key => $hospital_temp){
			
			foreach($hospital as $hospital_temp_key => $hospital_temp_two){
				if($hospital_temp_two['top_id'] == $hospital_temp['id_hospital']){
					$hospital_data[$hospital_key]['chird'][] =$hospital_temp_two;
				}
			}
		}
		return $hospital_data;
	}
	
	
	
	function getAccess($userid,$hospid){
		
		global $_DB;
		
		if(empty($userid)){
			return array();
		}
		
		if(empty($hospid)){
			return array();
		}

		$sql="SELECT access_id FROM `gossip_user_access` WHERE user_id='{$userid}' and hos_id ='{$hospid}'";
		
		$all=$_DB->get_col($sql);
		
		$w=" 1=1 and ( 1>1 ";
		
		foreach($all as $val){
		
			$w.=' or id='.$val;
				
		}
		
		$w.=')';
		
		$sql="SELECT id,fame,style FROM gossip_access WHERE ".$w;
		
		$all=$_DB->get_all($sql);
		
		foreach($all as &$val){
			if($val['style']==1){
				$val['fame']='字典-'.$val['fame'];
			}elseif($val['style']==2){
				$val['fame']='问诊-'.$val['fame'];
			}
		}

		return $all;
		
	}
		

	function getMsg()

	{

		global $_DB ;

		$sql = "SELECT count(*) as a FROM `irpt_notice_user`  WHERE look=0 and `user_id` = " . $_SESSION[ 'admin_id' ] ;

		$c = $_DB->get_one( $sql ) ;

		make_json_result( $c , '' , array( ) ) ;

	}



	function timeout()

	{

		global $_TEMPLATE ;

		$_TEMPLATE->display( "admin/reloginmid.html" ) ;

	}



	function geratCheckCode()

	{

		require_once(ROOT_PATH . "_ext/check_code.php") ;

	}




	function yes_delete()

	{

		global $_DB ;

		$sql = "UPDATE `admin_user` SET `is_delete` = '1' WHERE `id` = " . get_data( 'id' ) ;

		$_DB->query( $sql ) ;

	}




	function no_delete()

	{

		global $_DB ;

		$sql = "UPDATE `admin_user` SET `is_delete` = '0' WHERE  `id` = " . get_data( 'id' ) ;

		$_DB->query( $sql ) ;

	}
	
	
	function radio(){
		global $_DB;
		$hid = get_data('hid');
		$url = $_SESSION['request_url'];
		
		$sql = "select id from gossip_access where hos_id = " . $hid;
		$aid = $_DB->get_one($sql);
		
		$sql = "select name from irpt_hospital where  id ='{$hid}'; ";
		$hpl = $_DB->get_one($sql);
		
		$_SESSION['access_id'] = $aid;
		$_SESSION['hospital_id'] = $hid;
		
		make_json_result($url,$hpl,array());
	}
	
	
	function url_session(){
		$url = trim(get_data('url'));
		
		$_SESSION['request_url'] = $url;
		
		make_json_result('',1,array());
	}



}



?>