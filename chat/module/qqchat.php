<?php
require_once(ROOT_PATH."chat/include/Phpanalysis.php");
require_once(ROOT_PATH."admin/include/lib_common.php");
require_once(ROOT_PATH."chat/include/lib_share.php");

/**
 * 对微信小程序用户加密数据的解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */


class ErrorCode
{
	public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
}


class WXBizDataCrypt
{
	private $appid;
	private $sessionKey;

	/**
	 * 构造函数
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 * @param $appid string 小程序的appid
	 */
	public function WXBizDataCrypt( $appid, $sessionKey)
	{
		$this->sessionKey = $sessionKey;
		$this->appid = $appid;
	}


	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData( $encryptedData, $iv, &$data )
	{
		if (strlen($this->sessionKey) != 24) {
			return ErrorCode::$IllegalAesKey;
		}
		$aesKey=base64_decode($this->sessionKey);


		if (strlen($iv) != 24) {
			return ErrorCode::$IllegalIv;
		}
		$aesIV=base64_decode($iv);

		$aesCipher=base64_decode($encryptedData);

		$result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$dataObj=json_decode( $result );
		if( $dataObj  == NULL )
		{
			return ErrorCode::$IllegalBuffer;
		}
		if( $dataObj->watermark->appid != $this->appid )
		{
			return ErrorCode::$IllegalBuffer;
		}
		$data = $result;
		return ErrorCode::$OK;
	}

}


class qqchat extends cls_base{
	
	
	/**
	 * 更新用户匹配用户接口
	 */
	public function renew(){
		
		global $_CFG,$_DB;
		
		$userid=get_data('userid');

		$sql="select id from admin_user limit 0,1";

		$data=$_DB->get_row($sql);
		
		$_DB->select_database($_CFG['link_db_base']); //切换数据库
		
		$sql="select api from user where type=1 and id=$userid";
		
		$api=$_DB->get_one($sql);

		make_json_result('200',$api,'');

	}
	
	
	/**
	 * 微信小程序授权登录接口
	 */
	
	public function weixin_auth_login(){
	
		global $_DB;
		//$rid = get_data('rid');//对应以后的授权接口id
		$access_id=get_data('accessid');//接口默认必传参数
		$hos_id=get_data('hospid');
		$mold=get_data('mold');
		$camp=get_data('camp');
		$vistors_table_name='gossip_vistors_'.$access_id;//拼接表名
	
		$sql="select  appId,token from irpt_interface where id_hospital='{$hos_id}' and name='{$camp}'";
		$interface=$_DB->get_row($sql);
	
		$code = get_data('code');//用户code
		$appid = $interface['appId'];
		$secret = $interface['token'];
		$URL = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
		$apiData=file_get_contents($URL);
	
		$encryptedData  =  get_data('encryptedData');
		$iv  =  get_data('iv');
	
		$userNews=array();
		if(!isset($apiData['errcode'])){
			$openid = json_decode($apiData)->openid;
			$sessionKey = json_decode($apiData)->session_key;
			$userinfo = new \WXBizDataCrypt($appid, $sessionKey);
			$data='';
			$errCode = $userinfo->decryptData($encryptedData, $iv, $data);
			if ($errCode == 0) {
				$userNews = (array) json_decode($data,true);
	
				//查出用户是否原有登过
				$sql="SELECT  `id`  FROM  $vistors_table_name WHERE  `openId` ='{$openid}';";
				$id=$_DB->get_one($sql);
	
				$openId=$userNews['openId'];
				$nickName=preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $userNews['nickName']);
				$language=$userNews['language'];
				$gender=$userNews['gender'];
				$city=$userNews['city'];
				$province=$userNews['province'];
				$country=$userNews['country'];
				$avatarUrl=$userNews['avatarUrl'];
				$appid=$userNews['watermark']['appid'];
				$add_time=$userNews['watermark']['timestamp'];
				$edit_time=$userNews['watermark']['timestamp'];
	
				if(!$id){//原有用户登录过，则更新用户信息
	
					//记录访客数据
					$vistor_id=$this->petty($vistors_table_name,$hos_id,$openId,$nickName,$gender,$avatarUrl,$mold,$camp);
				}
	
				$arr=array();
				$arr['openId']=$openId;
				$arr['nickName']=$nickName;
				$arr['avatarUrl']=$avatarUrl;
				$arr['city']=$city;
				$arr['province']=$province;
				$arr['country']=$country;
				$arr['language']=$language;
				make_json_result($arr, '用户登录成功', array());
	
			} else {
				make_json_result(300, '用户获取失败，状态码为'.$errCode, array());
			}
		}else{
			make_json_result(300, '用户认证失败！', array());
		}
	}
	
	
	/**
	 * 保存测试小程序访客接口
	 */
	public function addPettyVistorData($table_name,$hospid,$openId,$nickName,$gender,$avatarUrl,$mold,$ip='',$country='',$area='',$region='',$city='',$tycoon='',$os='',$browser='',$lang='',$symbol='',$keyword='',$vistorid=0) {
		global $_CFG,$_DB;
		if($vistorid>0){
			$sql="UPDATE $table_name SET visits=visits+1,lastTime='".time()."' where id=".$vistorid;
			$res=$_DB->query($sql);
			return $vistorid;
		}else{
			$sql = "insert into $table_name (`hos_id`,`openId`,`nickName`,`gender`,`avatarUrl`,`visitTime`,`ip`,`country`,`area`,`region`,`city`,`tycoon`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) values('{$hospid}','{$openId}','{$nickName}','{$gender}','{$avatarUrl}','".time()."','{$ip}','{$country}','{$area}','{$region}','{$city}','{$tycoon}','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
			$_DB->query($sql);
			$add_id = $_DB->get_one("select @@identity");
			return $add_id;
		}
	}
	
	
	/**
	 * 小程序获得访客信息，记录访客信息
	 */
	
	public function petty($table_name,$hospid,$openId,$nickName='',$gender=1,$avatarUrl='',$mold,$camp){
	
		global $_DB;
		$ip=real_ip();
	
		/*查找用户是否存在*/
		$sql="SELECT id FROM  $table_name where openId='{$openId}'";
		$vistor_id = $_DB->get_one($sql);
	
		if($vistor_id>0){
			$this->addPettyVistorData($table_name,$hospid,$openId,$nickName,$gender,$avatarUrl,$mold,'','','','','','','','','','','',$vistor_id);
			return $vistor_id;
		}else{
	
			$browser=$camp;
			$lang='简体中文';
			$os='小程序';
			$symbol=$camp;
			$keyword=$camp;
	
			$client_ip_info=get_onlineip($ip);
			if($client_ip_info){
				$country=$client_ip_info['country'];
				$area=$client_ip_info['area'];
				$region=$client_ip_info['region'];
				$city=$client_ip_info['city'];
				$tycoon=$client_ip_info['isp'];
			}
	
			$vistor_id=$this->addPettyVistorData($table_name,$hospid,$openId,$nickName,$gender,$avatarUrl,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
			return $vistor_id;
		}
	
	}
	
	

	/**
	 * 访客记录获取
	 */
	public function weixin_login(){
		global $_DB;
		$access_id=get_data('accessid');//数据表id
		
		if(empty($access_id)){
			make_json_result('300','参数错误，接口无法解析！',array());
		}
		
		$vistors_table_name='gossip_vistors_'.$access_id;//拼接表名
		
		$mold=get_data('mold');//来源类型必传参数
		
		if(empty($mold)){
			make_json_result('300','参数错误，系统无法识别！',array());
		}
		
		$hos_id=get_data('hospid');
		
		if(empty($hos_id)){
			make_json_result('300','参数错误，系统识别医院错误！',array());
		}
		
		$camp=get_data('camp');
		$only=get_data('only');
		$vistor_id=$this->small($vistors_table_name,$hos_id,$only,$mold,$camp);
		
		$append=array();
		//消息中加入访客id
		$append['vistorid']=$vistor_id;
		//得到访客名称
		$sql="select id,hos_id,guest,region,city,mold,keyword,ip,collect,only from $vistors_table_name where hos_id=$hos_id and id=$vistor_id";
		$vistors=$_DB->get_row($sql);
		$append['hosid']=$vistors['hos_id'];
		if($vistors['guest']){//后台访客别名
			$append['monicker']=$vistors['guest'];
		}else{
			$append['monicker']=$vistors['region'].$vistors['city'].'访客'.$vistors['id'];
		}
		//访客名称
		$append['vistorname']=$vistors['region'].$vistors['city'].'访客'.$vistors['id'];
		if($vistors['mold']==1){
			$append['channel']='小程序';
		}elseif($vistors['mold']==2){
			$append['channel']='公众号';
		}elseif($vistors['mold']==3){
			$append['channel']='PC网站';
		}elseif($vistors['mold']==4){
			$append['channel']='APP';
		}elseif($vistors['mold']==5){
			$append['channel']='移动网站';
		}
		$append['keyword']=$vistors['keyword'];
		$append['ip']=$vistors['ip'];
		$append['collect']=$vistors['collect'];
		$append['only']=$vistors['only'];
		$append['time']=date('Y-m-d H:i:s',time());
		make_json_result('200','授权成功',$append);
	}

	/**
	 * 保存小程序访客接口
	 */
	public function addSmallVistorData($table_name,$hospid,$mold,$ip='',$country='',$area='',$region='',$city='',$tycoon='',$os='',$browser='',$lang='',$symbol='',$keyword='',$vistorid=0) {
	    global $_CFG,$_DB;
		if($vistorid>0){
			$sql="UPDATE $table_name SET visits=visits+1,lastTime='".time()."' where id=".$vistorid;
			$res=$_DB->query($sql);
			return $vistorid;
		}else{
			$sql = "insert into $table_name (`hos_id`,`visitTime`,`ip`,`country`,`area`,`region`,`city`,`tycoon`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) values('{$hospid}','".time()."','{$ip}','{$country}','{$area}','{$region}','{$city}','{$tycoon}','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
			$_DB->query($sql);
			$add_id = $_DB->get_one("select @@identity");
			
			$user_only=md5($add_id.$ip.time());
			
			//更新用户唯一码
			$sql="update $table_name set only='{$user_only}' where id=".$add_id;
		    $_DB->query($sql);
		    
			return $add_id;
		}
	}
	

	/**
	 * 小程序获得访客信息，记录访客信息
	 */
	
	public function small($table_name,$hospid,$only,$mold,$camp){
		
		global $_DB;
		$ip=real_ip();

		if(empty($only)){
			$browser=$camp;
			$lang='简体中文';
			$os='小程序';
			$symbol=$camp;
			$keyword=$camp;
			
			$client_ip_info=get_onlineip($ip);
			if($client_ip_info){
				$country=$client_ip_info['country'];
				$area=$client_ip_info['area'];
				$region=$client_ip_info['region'];
				$city=$client_ip_info['city'];
				$tycoon=$client_ip_info['isp'];
			}
			
			$vistor_id=$this->addSmallVistorData($table_name,$hospid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
			return $vistor_id;
		}else{
			
			/*查找用户是否存在*/
			$sql="SELECT id FROM  $table_name where only='{$only}'";
			$vistor_id = $_DB->get_one($sql);
			
			if($vistor_id>0){
				$this->addSmallVistorData($table_name,$hospid,$mold,'','','','','','','','','','','',$vistor_id);
				return $vistor_id;
			}else{
				$browser=$camp;
				$lang='简体中文';
				$os='小程序';
				$symbol=$camp;
				$keyword=$camp;
					
				$client_ip_info=get_onlineip($ip);
				if($client_ip_info){
					$country=$client_ip_info['country'];
					$area=$client_ip_info['area'];
					$region=$client_ip_info['region'];
					$city=$client_ip_info['city'];
					$tycoon=$client_ip_info['isp'];
				}
					
				$vistor_id=$this->addSmallVistorData($table_name,$hospid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
				return $vistor_id;
			}
		}
	}

	/**
	 * 公众号获得访客信息，记录访客信息
	 */
	public function people(){

	}
	
	
	/**
	 * 保存web端访客接口
	 */
	public function addWebsiteVistorData($table_name,$hospid,$mold,$ip='',$country='',$area='',$region='',$city='',$tycoon='',$os='',$browser='',$lang='',$symbol='',$keyword='',$vistorid=0) {
		global $_CFG,$_DB;
		if($vistorid>0){
			$sql="UPDATE $table_name SET visits=visits+1,lastTime='".time()."' where id=".$vistorid;
			$res=$_DB->query($sql);
			return $vistorid;
		}else{
			$sql = "insert into $table_name (`hos_id`,`visitTime`,`ip`,`country`,`area`,`region`,`city`,`tycoon`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) values('{$hospid}','".time()."','{$ip}','{$country}','{$area}','{$region}','{$city}','{$tycoon}','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
			$_DB->query($sql);
			$add_id = $_DB->get_one("select @@identity");
	
			$user_only=md5($add_id.$ip.time());
	
			//更新用户唯一码
			$sql="update $table_name set only='{$user_only}' where id=".$add_id;
			$_DB->query($sql);
	
			return $add_id;
		}
	}
	
	
	/**
	 * web端获得访客信息，记录访客信息
	 */
	public function website($table_name,$hospid,$only,$mold,$camp){

		global $_DB;
		$ip=real_ip();
		
		if(empty($only)){
			$browser=$camp;
			$lang='简体中文';
			$os='PC网站';
			$symbol=$camp;
			$keyword=$camp;
		
			$client_ip_info=get_onlineip($ip);
			if($client_ip_info){
				$country=$client_ip_info['country'];
				$area=$client_ip_info['area'];
				$region=$client_ip_info['region'];
				$city=$client_ip_info['city'];
				$tycoon=$client_ip_info['isp'];
			}
		
			$vistor_id=$this->addWebsiteVistorData($table_name,$hospid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
			return $vistor_id;
		}else{
		
			/*查找用户是否存在*/
			$sql="SELECT id FROM  $table_name where only='{$only}'";
			$vistor_id = $_DB->get_one($sql);
		
			if($vistor_id>0){
				$this->addSmallVistorData($table_name,$hospid,$mold,'','','','','','','','','','','',$vistor_id);
				return $vistor_id;
			}else{
				$browser=$camp;
				$lang='简体中文';
				$os='PC网站';
				$symbol=$camp;
				$keyword=$camp;
					
				$client_ip_info=get_onlineip($ip);
				if($client_ip_info){
					$country=$client_ip_info['country'];
					$area=$client_ip_info['area'];
					$region=$client_ip_info['region'];
					$city=$client_ip_info['city'];
					$tycoon=$client_ip_info['isp'];
				}
					
				$vistor_id=$this->addWebsiteVistorData($table_name,$hospid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
				return $vistor_id;
			}
		}
	}

	/**
	 * 保存app访客接口
	 */
	public function addApplianceVistorData($table_name,$hospid,$clientid,$doctorid,$mold,$ip='',$country='',$area='',$region='',$city='',$tycoon='',$os='',$browser='',$lang='',$symbol='',$keyword='',$vistorid=0) {
		global $_CFG,$_DB;
		if($vistorid>0){
			$sql="UPDATE $table_name SET visits=visits+1,lastTime='".time()."' where id=".$vistorid;
			$res=$_DB->query($sql);
			return $vistorid;
		}else{
			$sql = "insert into $table_name (`hos_id`,`client_id`,`doctor_id`,`visitTime`,`ip`,`country`,`area`,`region`,`city`,`tycoon`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) values('{$hospid}','{$clientid}','{$doctorid}','".time()."','{$ip}','{$country}','{$area}','{$region}','{$city}','{$tycoon}','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
			$_DB->query($sql);
			$add_id = $_DB->get_one("select @@identity");
			
			$user_only=md5($add_id.$ip.time());
				
			//更新用户唯一码
			$sql="update $table_name set only='{$user_only}' where id=".$add_id;
			$_DB->query($sql);
			
			return $add_id;
		}
	}

	
	/**
	 * app获得访客信息，记录访客信息
	 */
	public function appliance($table_name,$hospid,$clientid,$doctorid,$mold,$camp){
		global $_DB;
		$ip=real_ip();
		
		/*查找用户是否存在*/
		$sql="SELECT id FROM  $table_name where hos_id='{$hospid}' and client_id='{$clientid}' and doctor_id='{$doctorid}'";
		$vistor_id = $_DB->get_one($sql);

		if($vistor_id>0){
				
			/*
			 * 更新访客关联的医生状态
			*/
			$sql="update $table_name set is_delete=0 where id=".$vistor_id." and doctor_id=".$doctorid;
			$_DB->query($sql);
				
			$this->addApplianceVistorData($table_name,$hospid,$clientid,$doctorid,$mold,'','','','','','','','','','','',$vistor_id);
			return $vistor_id;
		}else{

			/* $browser=GetBrowser();
			$lang=GetLang();
			$os=GetOs(); */
				
			$browser=$camp;
			$lang='简体中文';
			$os='安卓';
			$symbol=$camp;
			$keyword=$camp;

			$client_ip_info=get_onlineip($ip);
			if($client_ip_info){
				$country=$client_ip_info['country'];
				$area=$client_ip_info['area'];
				$region=$client_ip_info['region'];
				$city=$client_ip_info['city'];
				$tycoon=$client_ip_info['isp'];
			}

			$vistor_id=$this->addApplianceVistorData($table_name,$hospid,$clientid,$doctorid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
			return $vistor_id;
		}
	}
	
	
	/**
	 * 保存移动端访客接口
	 */
	public function addMobileVistorData($table_name,$hospid,$mold,$ip='',$country='',$area='',$region='',$city='',$tycoon='',$os='',$browser='',$lang='',$symbol='',$keyword='',$vistorid=0) {
		global $_CFG,$_DB;
		if($vistorid>0){
			$sql="UPDATE $table_name SET visits=visits+1,lastTime='".time()."' where id=".$vistorid;
			$res=$_DB->query($sql);
			return $vistorid;
		}else{
			$sql = "insert into $table_name (`hos_id`,`visitTime`,`ip`,`country`,`area`,`region`,`city`,`tycoon`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) values('{$hospid}','".time()."','{$ip}','{$country}','{$area}','{$region}','{$city}','{$tycoon}','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
			$_DB->query($sql);
			$add_id = $_DB->get_one("select @@identity");
				
			$user_only=md5($add_id.$ip.time());
				
			//更新用户唯一码
			$sql="update $table_name set only='{$user_only}' where id=".$add_id;
			$_DB->query($sql);
	
			return $add_id;
		}
	}

	
	/**
	 * 移动端获得访客信息，记录访客信息
	 */
	public function mobile($table_name,$hospid,$only,$mold,$camp){
		global $_DB;
		$ip=real_ip();
		
		if(empty($only)){
			$browser=$camp;
			$lang='简体中文';
			$os='移动端';
			$symbol=$camp;
			$keyword=$camp;
				
			$client_ip_info=get_onlineip($ip);
			if($client_ip_info){
				$country=$client_ip_info['country'];
				$area=$client_ip_info['area'];
				$region=$client_ip_info['region'];
				$city=$client_ip_info['city'];
				$tycoon=$client_ip_info['isp'];
			}
				
			$vistor_id=$this->addMobileVistorData($table_name,$hospid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
			return $vistor_id;
		}else{
				
			/*查找用户是否存在*/
			$sql="SELECT id FROM  $table_name where only='{$only}'";
			$vistor_id = $_DB->get_one($sql);
				
			if($vistor_id>0){
				$this->addSmallVistorData($table_name,$hospid,$mold,'','','','','','','','','','','',$vistor_id);
				return $vistor_id;
			}else{
				$browser=$camp;
				$lang='简体中文';
				$os='移动端';
				$symbol=$camp;
				$keyword=$camp;
					
				$client_ip_info=get_onlineip($ip);
				if($client_ip_info){
					$country=$client_ip_info['country'];
					$area=$client_ip_info['area'];
					$region=$client_ip_info['region'];
					$city=$client_ip_info['city'];
					$tycoon=$client_ip_info['isp'];
				}
					
				$vistor_id=$this->addMobileVistorData($table_name,$hospid,$mold,$ip,$country,$area,$region,$city,$tycoon,$os,$browser,$lang,$symbol,$keyword);
				return $vistor_id;
			}
		}
	}
	

	
	/**
	 * 保存消息方法
	 */
	public function keep($table_name,$type,$hos_id,$vistor_id,$msg){
		global $_DB;
		/**
		 * 记录客户消息
		 */
		$sql = "insert into $table_name (`type`,`hos_id`,`vistor_id`,`content`,`createTime`) values('{$type}','{$hos_id}','{$vistor_id}','{$msg}','".time()."')";
		$_DB->query($sql);
	}
	
	
	/**
	 * 过滤
	 * 匹配用户消息，生成订单信息
	 */
	public function build($hos_id,$vistor_id,$msg){
		global $_DB;
		
		/**
		 * 抓取手机号码和QQ号码
		 */
		
		preg_match_all("/\d+/",$msg,$arr);
		if(count($arr[0])>0){
			foreach($arr[0] as $val){
				/**
				 * 匹配手机号码
				 */
				if(preg_match("/^1[34578]\d{9}$/", $val)){
					
					$sql = "SELECT id FROM  `gossip_order` where  hos_id='{$hos_id}' and vistor_id='{$vistor_id}' and phone='{$val}'";
					$id = $_DB->get_one($sql);
					if(empty($id)){
						$sql = "insert into gossip_order (`hos_id`,`vistor_id`,`phone`,`createTime`) values('{$hos_id}','{$vistor_id}','{$val}','".time()."')";
						$_DB->query($sql);
					}
				}
				/**
				 * 匹配QQ号码
				 */
				if(preg_match("/^[1-9][0-9]{5,9}$/", $val)){
					$sql = "SELECT id FROM  `gossip_order` where  hos_id='{$hos_id}' and vistor_id='{$vistor_id}' and qq='{$val}'";
					$id = $_DB->get_one($sql);
					if(empty($id)){
						$sql = "insert into gossip_order (`hos_id`,`vistor_id`,`qq`,`createTime`) values('{$hos_id}','{$vistor_id}','{$val}','".time()."')";
						$_DB->query($sql);
					}
				}
			}
		}
		
		
		/**
		 * 抓取微信号
		 */
		preg_match_all("/[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}/",$msg,$arr);
		if(count($arr[0])>0){
			foreach($arr[0] as $val){
				$sql = "SELECT id FROM  `gossip_order` where  hos_id='{$hos_id}' and vistor_id='{$vistor_id}' and wechat='{$val}'";
				$id = $_DB->get_one($sql);
				if(empty($id)){
					$sql = "insert into gossip_order (`hos_id`,`vistor_id`,`wechat`,`createTime`) values('{$hos_id}','{$vistor_id}','{$val}','".time()."')";
					$_DB->query($sql);
				}
			}
		}
		
		/**
		 * 抓取固定电话
		 */
		preg_match_all("/\d{3}-\d{8}|\d{4}-\d{7,8}/",$msg,$arr);
		if(count($arr[0])>0){
			foreach($arr[0] as $val){
				$sql = "SELECT id FROM  `gossip_order` where  hos_id='{$hos_id}' and vistor_id='{$vistor_id}' and telphone='{$val}'";
				$id = $_DB->get_one($sql);
				if(empty($id)){
					$sql = "insert into gossip_order (`hos_id`,`vistor_id`,`telphone`,`createTime`) values('{$hos_id}','{$vistor_id}','{$val}','".time()."')";
					$_DB->query($sql);
				}
			}
		}
	}
	
	
	/*中文分词*/
	function chineseParticiple($strong){
		$analysis = new Services_Phpanalysis_Phpanalysis();
		$analysis->SetSource(strtolower($strong));
		$analysis->StartAnalysis();
		$result = explode(',', $analysis->GetFinallyResult(','));unset($result[0]);
		return $result;
	}
	
	
	/*中文分词搜索*/
	function chineseParticipleSearch($table_name,$hid,$yuyi){
		$search = array();
		/*匹配名词*/
		foreach ($yuyi as $key =>$val){
			$sql = "SELECT * FROM  $table_name where hid = '{$hid}' and   noun = '{$val}' and genre<>3 ORDER BY  `genre` DESC,`score` DESC ";
			$mc = $this->db->get_all($sql);
			if($mc){
				foreach($mc as $v){
					$search['mingci'][] = $v;
				}
				unset($yuyi[$key]);
			}
		}
	
	
		/*匹配动词*/
	
		foreach ($yuyi as $key =>$val){
			foreach ($search['mingci'] as $k=>$v){
				if($search['mingci'][$k]['verb']==$val){
					$search['dongci'][] = $search['mingci'][$k];
					unset($yuyi[$key]);
				}
			}
		}
		/*匹配疑问词*/
	
		foreach ($yuyi as $key =>$val){
			foreach ($search['dongci'] as $k=>$v){	/*匹配动词*/
				if($search['dongci'][$k]['doubt']==$val){
					$search['yiwenci'][] = $search['dongci'][$k];
					unset($yuyi[$key]);
				}
			}
				
			foreach ($search['mingci'] as $k=>$v){	/*匹配名词*/
				if($search['mingci'][$k]['doubt']==$val){
					$search['yiwenci'][] = $search['mingci'][$k];
					unset($yuyi[$key]);
				}
			}
				
				
		}
	
		return $search;
	}


   /**
	 * 安卓机器人对话接口
	 */
	function chatbot(){
		
		global $_DB;
		$access_id=get_data('accessid');//接口默认必传参数
		
		if(empty($access_id)){
			make_json_result('300','参数错误，接口无法解析！',array());
		}

		$answer_table_name='wisdom_answer_'.$access_id;//拼接表名
		$vistors_table_name='gossip_vistors_'.$access_id;//拼接表名
		$msg_table_name='gossip_msg_'.$access_id;//拼接表名

		$mold=get_data('mold');//来源类型必传参数
		
		if(empty($mold)){
			make_json_result('300','参数错误，系统无法识别！',array());
		}
		
		$hos_id=get_data('hospid');
		
		if(empty($hos_id)){
			make_json_result('300','参数错误，系统识别医院错误！',array());
		}
		
		$camp=get_data('camp');
		
		if($mold==1){//是小程序
			$only=get_data('only');
			$vistor_id=$this->small($vistors_table_name,$hos_id,$only,$mold,$camp);
		}elseif($mold==2){//是微信公众号

		}elseif($mold==3){//是web端
			$only=get_data('only');
			$vistor_id=$this->website($vistors_table_name,$hos_id,$only,$mold,$camp);
		}elseif($mold==4){//是app
			$client_id=get_data('userid');
			$doctor_id=get_data('doctorid');
			$vistor_id=$this->appliance($vistors_table_name,$hos_id,$client_id,$doctor_id,$mold,$camp);/*返回访客信息记录*/
		}elseif($mold==5){//是移动端
			$only=get_data('only');
			$vistor_id=$this->mobile($vistors_table_name,$hos_id,$only,$mold,$camp);
		}

		$time = time();
		$msg = get_data('msg','s');
		$this->build($hos_id,$vistor_id,$msg);/*记录用户的基本信息*/
		
		//消息中加入访客id
		$append['vistorid']=$vistor_id;
		//得到访客名称
		$sql="select id,hos_id,guest,region,city,mold,keyword,ip,collect,only from $vistors_table_name where hos_id=$hos_id and id=$vistor_id";
		$vistors=$_DB->get_row($sql);
		$append['hosid']=$vistors['hos_id'];
		if($vistors['guest']){//后台访客别名
			$append['monicker']=$vistors['guest'];
		}else{
			$append['monicker']=$vistors['region'].$vistors['city'].'访客'.$vistors['id'];
		}
		//访客名称
		$append['vistorname']=$vistors['region'].$vistors['city'].'访客'.$vistors['id'];
		
		if($vistors['mold']==1){
			$append['channel']='小程序';
		}elseif($vistors['mold']==2){
			$append['channel']='公众号';
		}elseif($vistors['mold']==3){
			$append['channel']='PC网站';
		}elseif($vistors['mold']==4){
			$append['channel']='APP';
		}elseif($vistors['mold']==5){
			$append['channel']='移动网站';
		}
		$append['keyword']=$vistors['keyword'];
		$append['ip']=$vistors['ip'];
		$append['collect']=$vistors['collect'];
		$append['only']=$vistors['only'];
		$append['time']=date('Y-m-d H:i:s',time());
		$append['record']=array();

		if(empty($msg)){
			
			//查出是否存在原有记录
			$sql="select m.*,u.autograph,u.path,v.region,v.city,v.id as visid from $msg_table_name as m 
			left join admin_user as u on m.admin_id=u.id 
			left join $vistors_table_name as v on m.vistor_id=v.id
			where m.hos_id='{$hos_id}' and m.vistor_id='{$vistor_id}' order by m.createTime asc";
			$msg_list=$_DB->get_all($sql);
			if(empty($msg_list)){
				$append['str']='';
				$this->keep($msg_table_name,2,$hos_id,$vistor_id,'您好，请问您需要什么帮助？');
				make_json_result('200','您好，请问您需要什么帮助？',$append);
			}else{
				$append['str']='';
				$append['record']=$msg_list;
				make_json_result('200','您好，请问您需要什么帮助？',$append);
			}

		}else{
			sleep(mt_rand(1,3));
			/*
			 * 访客统计
			*/
			init_session();
			$real_ip = real_ip();
			$lastmsg = 	$_SESSION[$real_ip]['lastmsg'];/*取得上次对话*/
			
			$_SESSION[$real_ip]['lastmsg'] = $msg;  /*记录本次对话*/
			
			$this->keep($msg_table_name,1,$hos_id,$vistor_id,$msg);
			
			/*短语模糊搜索*/
			$sql = "SELECT * FROM  $answer_table_name where hid='{$hos_id}' and  noun = '{$msg}' ORDER BY  `genre` DESC,`score` DESC ";
			$msgm = $_DB->get_row($sql);
			if($msgm){/*先检索短语*/
				/*判断是否是关联词，结合上次问题再次分析*/
				if($msgm['genre']==3){
					/*上次问题去寻找名词*/
					$yuyi  = $this->chineseParticiple(strFilter($lastmsg.$msg));
				}else{
					/*本次问题*/
					$yuyi  = $this->chineseParticiple($msg);
				}
			
				$str = '';
				foreach ($yuyi as $key =>$val){
					$str .= $val.",";
				}
				$append['str'] = $str;
				$search = $this->chineseParticipleSearch($answer_table_name,$hos_id,$yuyi);
					
			
				if(count($search['yiwenci'])){
					$this->keep($msg_table_name,2,$hos_id,$vistor_id,$search['yiwenci'][0]['answer']);
					make_json_result('200',$search['yiwenci'][0]['answer'],$append);
				}
				if(count($search['dongci'])){
					$this->keep($msg_table_name,2,$hos_id,$vistor_id,$search['dongci'][0]['answer']);
					make_json_result('200',$search['dongci'][0]['answer'],$append);
				}
				if(count($search['mingci'])){
					$this->keep($msg_table_name,2,$hos_id,$vistor_id,$search['mingci'][0]['answer']);
					make_json_result('200',$search['mingci'][0]['answer'],$append);
				}
				$this->keep($msg_table_name,2,$hos_id,$vistor_id,$str);
				make_json_result('200',$msgm['answer'],$append);
			}
			
			/*非短语*/
			/*本次问题*/
			$yuyi  = $this->chineseParticiple($msg);
			$str = '';
			foreach ($yuyi as $key =>$val){
				$str .= $val.",";
			}
			$append['str'] = $str;
			$search = $this->chineseParticipleSearch($answer_table_name,$hos_id,$yuyi);
				
				
			if(count($search['yiwenci'])){
				$this->keep($msg_table_name,2,$hos_id,$vistor_id,$search['yiwenci'][0]['answer']);
				make_json_result('200',$search['yiwenci'][0]['answer'],$append);
			}
			if(count($search['dongci'])){
				$this->keep($msg_table_name,2,$hos_id,$vistor_id,$search['dongci'][0]['answer']);
				make_json_result('200',$search['dongci'][0]['answer'],$append);
			}
			if(count($search['mingci'])){
				$this->keep($msg_table_name,2,$hos_id,$vistor_id,$search['mingci'][0]['answer']);
				make_json_result('200',$search['mingci'][0]['answer'],$append);
			}
			
			
			/*图灵接口*/
			
			
			$tuling = tuling($msg,$lastmsg,$real_ip);
			
			if($tuling['text']!='' && $msg!=''){
				$sql = "INSERT INTO $answer_table_name (`hid`,`noun`,`answer`,`genre` ,`addTime`) VALUES ('{$hos_id}','{$msg}','{$tuling['text']}','1','{$time}')";
			
				$this->db->query($sql);
				$sql = "INSERT INTO `gossip_check` (`ip`,`add_time` ,`problem`) VALUES ('{$real_ip}','{$time}',  '{$str}')";
				$this->db->query($sql);
				$this->keep($msg_table_name,2,$hos_id,$vistor_id,$tuling['text']);
				make_json_result('200',$tuling['text'],$append);
			}
			
			/*以下是图灵没有的*/
			
			/*错误机制*/
			
			$sql = "INSERT INTO `gossip_check` (`ip`,`add_time` ,`problem`) VALUES ('{$real_ip}','{$time}',  '{$str}')";
			$this->db->query($sql);
			$this->keep($msg_table_name,2,$hos_id,$vistor_id,'很抱歉，我还没学会这个问题!您可以请求人工客服进行服务。');
			make_json_result('200','很抱歉，我还没学会这个问题!您可以请求人工客服进行服务。',$append);

		}

	}
	
	/**
	 * 套路保存消息方法
	 */
	public function persist($table_name,$type,$hos_id,$vistor_id,$obtain_id=0,$msg){
		global $_DB;
		/**
		 * 记录客户消息
		 */
		$sql = "insert into $table_name (`type`,`hos_id`,`vistor_id`,`obtain_id`,`content`,`createTime`) values('{$type}','{$hos_id}','{$vistor_id}','{$obtain_id}','{$msg}','".time()."')";
		$_DB->query($sql);
	}
	
	
	/**
	 * 套路聊天接口
	 */
	public function chitchat(){

		global $_DB;
		$access_id=get_data('accessid');//数据表id
		
		if(empty($access_id)){
			make_json_result('300','参数错误，接口无法解析！',array());
		}
		
		$answer_table_name='wisdom_answer_'.$access_id;//拼接表名
		$vistors_table_name='gossip_vistors_'.$access_id;//拼接表名
		$msg_table_name='gossip_msg_'.$access_id;//拼接表名
		
		$mold=get_data('mold');//来源类型必传参数
		
		if(empty($mold)){
			make_json_result('300','参数错误，系统无法识别！',array());
		}
		
		$hos_id=get_data('hospid');
		
		if(empty($hos_id)){
			make_json_result('300','参数错误，系统识别医院错误！',array());
		}
		
		$camp=get_data('camp');
		
		if($mold==1){//是小程序
			if($hos_id==109){
				$openId=get_data('openId');
				$vistor_id=$this->petty($vistors_table_name,$hos_id,$openId,'',1,'',$mold,$camp);
			}else{ 
				$only=get_data('only');
				$vistor_id=$this->small($vistors_table_name,$hos_id,$only,$mold,$camp);
			}
		}elseif($mold==2){//是微信公众号
		
		}elseif($mold==3){//是web端
			$only=get_data('only');
			$vistor_id=$this->website($vistors_table_name,$hos_id,$only,$mold,$camp);
		}elseif($mold==4){//是app
			$client_id=get_data('userid');
			$doctor_id=get_data('doctorid');
			$vistor_id=$this->appliance($vistors_table_name,$hos_id,$client_id,$doctor_id,$mold,$camp);/*返回访客信息记录*/
		}elseif($mold==5){//是移动端
			$only=get_data('only');
			$vistor_id=$this->mobile($vistors_table_name,$hos_id,$only,$mold,$camp);
		}

		$relax=array('1'=>'express','2'=>'proof','3'=>'nicety');
		$time = time();
		$msg = get_data('msg','s');
		$this->build($hos_id,$vistor_id,$msg);/*记录用户的基本信息*/

		//消息中加入访客id
		$append['vistorid']=$vistor_id;
		//得到访客名称
		$sql="select id,hos_id,guest,region,city,mold,keyword,ip,collect,only from $vistors_table_name where hos_id=$hos_id and id=$vistor_id";
		$vistors=$_DB->get_row($sql);
		$append['hosid']=$vistors['hos_id'];
		if($vistors['guest']){//后台访客别名
			$append['monicker']=$vistors['guest'];
		}else{
			$append['monicker']=$vistors['region'].$vistors['city'].'访客'.$vistors['id'];
		}
		//访客名称
		$append['vistorname']=$vistors['region'].$vistors['city'].'访客'.$vistors['id'];
		if($vistors['mold']==1){
			$append['channel']='小程序';
		}elseif($vistors['mold']==2){
			$append['channel']='公众号';
		}elseif($vistors['mold']==3){
			$append['channel']='PC网站';
		}elseif($vistors['mold']==4){
			$append['channel']='APP';
		}elseif($vistors['mold']==5){
			$append['channel']='移动网站';
		}
		$append['keyword']=$vistors['keyword'];
		$append['ip']=$vistors['ip'];
		$append['collect']=$vistors['collect'];
		$append['only']=$vistors['only'];
		$append['time']=date('Y-m-d H:i:s',time());
		$append['record']=array();

		if(empty($msg)){
			
			//查出是否存在原有记录
			$sql="select m.*,u.autograph,u.path,v.region,v.city,v.id as visid from $msg_table_name as m 
			left join admin_user as u on m.admin_id=u.id 
			left join $vistors_table_name as v on m.vistor_id=v.id
			where m.hos_id='{$hos_id}' and m.vistor_id='{$vistor_id}' order by m.createTime asc";
			$msg_list=$_DB->get_all($sql);
			if(empty($msg_list)){
				$sql="select id,question from $answer_table_name where hid=$hos_id  order by groupid asc limit 0,1";
				$answer=$_DB->get_row($sql);
				$this->persist($msg_table_name,2,$hos_id,$vistor_id,$answer['id'],$answer['question']);
				make_json_result('200',$answer['question'],$append);
			}else{
				//首先判断是否推送问候消息
				/* $sql="select obtain_id from $msg_table_name where hos_id=$hos_id and vistor_id=$vistor_id and type=2 and obtain_id>0 order by id desc limit 0,1";
				$obtain_id=$_DB->get_one($sql);
				
				//查出
				$sql="select keyword from $answer_table_name where hid=$hos_id && id=$obtain_id";
				$keyword=$_DB->get_one($sql);
					
				//查出是否是最后一句
				$sql="select id from $answer_table_name where hid=$hos_id and keyword='{$keyword}' order by groupid desc limit 0,1";
				$answer_id=$_DB->get_one($sql);
					
				if($obtain_id==$answer_id){
					$this->persist($msg_table_name,2,$hos_id,$vistor_id,0,'感谢您的提问，我们客服稍后会联系您！');
					make_json_result('200','感谢您的提问，我们客服稍后会联系您！',$append);
				}else{
					//获得当前提问内容回复消息
					$sql="select question from $answer_table_name where hid=$hos_id && id=$obtain_id";
					$question=$_DB->get_one($sql);
					make_json_result('200',$question,$append);
				} */
				$append['record']=$msg_list;
				make_json_result('200','您好，请问您需要什么帮助？',$append);
			}

		}else{
			
			//保存用户消息
			$this->persist($msg_table_name,1,$hos_id,$vistor_id,0,$msg);
			sleep(mt_rand(1,3));
			
			
			//查出问的是否是回答第一句的内容
			$sql="select count(id) as count,id from $msg_table_name where hos_id=$hos_id and vistor_id=$vistor_id and type=2 and obtain_id>0";
			$msg_arr=$_DB->get_row($sql);
			
			if($msg_arr['count']==1){
				/*查出组id，然后从这组里面去查记录*/
				$sql="select groupid from $answer_table_name where hid=$hos_id group by groupid asc limit 0,1";
				$groupid=$_DB->get_one($sql);
				
				/*非短语*/
				$answer_id=0;
				$yuyi  = $this->chineseParticiple($msg);
				
				foreach($yuyi as $val){
					$sql = "SELECT id FROM  $answer_table_name where hid = '{$hos_id}' and  answer like '%".$val."%' and groupid='{$groupid}' limit 0,1";
					$mc_id = $this->db->get_one($sql);
					if($mc_id>0){
						$answer_id=$mc_id;break;
					}
				}
				
				if($answer_id>0){
					//更新消息中的提问id
					$sql="update $msg_table_name set obtain_id='{$answer_id}' where hos_id='{$hos_id}' and id='{$msg_arr['id']}'";
					$_DB->query($sql);
					
					//得到上一句的索引内容
					$sql="select groupid,answer,keyword from  $answer_table_name where id='{$answer_id}'";
					$answer_data=$_DB->get_row($sql);
					$groupid=$answer_data['groupid'];
					$answer=$answer_data['answer'];
					$keyword=$answer_data['keyword'];
					
					//查出下一句的问题
					$sql="select id,question from $answer_table_name where hid=$hos_id and keyword='{$keyword}' and groupid>$groupid order by groupid asc limit 0,1";
					$answer=$_DB->get_row($sql);
					$this->persist($msg_table_name,2,$hos_id,$vistor_id,$answer['id'],$answer['question']);
					make_json_result('200',$answer['question'],$append);
				}else{
					
					$sql="select id,express,proof,nicety from $answer_table_name where hid=$hos_id order by groupid asc limit 0,1";
					$answer=$_DB->get_row($sql);
					$random=mt_rand(1,3);
					$this->persist($msg_table_name,2,$hos_id,$vistor_id,0,$answer[$relax[$random]]);
					make_json_result('200',$answer[$relax[$random]],$append);
				}

			}else{
				//查出上一句的内容
				$sql="select obtain_id from $msg_table_name where hos_id=$hos_id and vistor_id=$vistor_id and type=2 and obtain_id>0 order by id desc limit 0,1";
				$obtain_id=$_DB->get_one($sql);
					
				//得到上一句的索引内容
				$sql="select groupid,answer,keyword from  $answer_table_name where id=$obtain_id";
				$answer_data=$_DB->get_row($sql);
				$groupid=$answer_data['groupid'];
				$answer=$answer_data['answer'];
				$keyword=$answer_data['keyword'];
					
				//查出是否是最后一句
				$sql="select id from $answer_table_name where hid=$hos_id and keyword='{$keyword}' order by groupid desc limit 0,1";
				$answer_id=$_DB->get_one($sql);
					
				if($obtain_id==$answer_id){
					$this->persist($msg_table_name,2,$hos_id,$vistor_id,0,'感谢您的提问，我们客服稍后会联系您！');
					make_json_result('200','感谢您的提问，我们客服稍后会联系您！',$append);
				}
				
				//拆词
				$answer_arr=explode(',', $answer);
				
				/*非短语*/
				$yuyi  = $this->chineseParticiple($msg);
				$judge=false;
					
				foreach($answer_arr as $val){
				
					if(in_array($val,$yuyi)){
						$judge=true;break;
					}
					//校验年龄
					if($val=='{num}'){
						$num=false;
						foreach($yuyi as $vl){
							if(preg_match("/\d/",$vl)){
								$num=true;break;
							}
						}
						if($num){
							$judge=true;break;
						}
					}
					if($val=='{phone}'){
						$phone=false;
						foreach($yuyi as $vl){
							if(preg_match("/13[123569]{1}\d{8}|15[1235689]\d{8}|188\d{8}/",$vl)){
								$phone=true;break;
							}
						}
						if($phone){
							$judge=true;break;
						}
					}
					if($val=='{qq}'){
						$phone=false;
						foreach($yuyi as $vl){
							if(preg_match("/[1-9][0-9]{5,9}/",$vl)){
								$phone=true;break;
							}
						}
						if($phone){
							$judge=true;break;
						}
					}
					if($val=='{weixin}'){
						$phone=false;
						foreach($yuyi as $vl){
							if(preg_match("/[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}/",$vl)){
								$phone=true;break;
							}
						}
						if($phone){
							$judge=true;break;
						}
					}
				}
					
				//回答成功执行下一句
				if($judge){
				
					//查出下一句的问题
					$sql="select id,question from $answer_table_name where hid=$hos_id and keyword='{$keyword}' and groupid>$groupid order by groupid asc limit 0,1";
					$answer=$_DB->get_row($sql);
					$this->persist($msg_table_name,2,$hos_id,$vistor_id,$answer['id'],$answer['question']);
					make_json_result('200',$answer['question'],$append);
				
				}else{
					
					//没有答对重复校验问题
					$sql="select id,express,proof,nicety from $answer_table_name where hid=$hos_id and keyword='{$keyword}' and id=$obtain_id";
					$answer=$_DB->get_row($sql);
					$random=mt_rand(1,3);
					$this->persist($msg_table_name,2,$hos_id,$vistor_id,$answer['id'],$answer[$relax[$random]]);
					make_json_result('200',$answer[$relax[$random]],$append);

				}
			}

		}

	}
	
	/**
	 * 用户回复消息接口
	 */
	public function reply(){
		global $_DB;
		$access_id=get_data('accessid');
		$msg_table_name='gossip_msg_'.$access_id;//拼接表名
		$hos_id=get_data('hospid');
		$vistor_id=get_data('vistor_id');

		$msg = get_data('msg','s');
		
		$sql="INSERT INTO $msg_table_name (type, hos_id,vistor_id,content,createTime) VALUES (1,'{$hos_id}', '{$vistor_id}', '{$msg}','".time()."')";
		$_DB->query($sql);
		
		make_json_result('200','回复成功',array());
	}


	/**
	 * 获得用户列表数据
	 */
	public function userlist(){
		
		
		global $_DB, $_CFG;
		
	   /**
	    * 查出消息
	    */

		$client_id=get_data('userid');
		
		if(empty($client_id)){
			make_json_result('300','参数错误，系统识别用户错误！',array());
		}
		
		//只查出东莞东方医院男科,深圳仁爱医院产科,深圳仁爱医院妇科数据
		$pages=get_data('pages');
		if(empty($pages)){
			$pages=1;
		}
		$pageSize=get_data('pageSize');
		if(empty($pageSize)){
			$pageSize=20;
		}
		/**
		 * 获得数据总数
		 */
		$sql="select count(v.id) as count from (select id,client_id,is_delete from gossip_vistors_4 UNION select id,client_id,is_delete from gossip_vistors_6 UNION  select id,client_id,is_delete from gossip_vistors_2) as v where v.client_id=".$client_id." and v.is_delete=0";
		$count=$_DB->get_one($sql);

		$sql="select m.content,m.createTime,v.id as deleteid,v.hos_id,v.doctor_id from (select * from gossip_vistors_4 UNION select * from gossip_vistors_6 UNION select * from gossip_vistors_2) as v 
				left join (select * from (select * from gossip_msg_4 UNION select * from gossip_msg_6 UNION select * from gossip_msg_2) as msg order by id desc) as m on v.id=m.vistor_id  where v.client_id=".$client_id." and v.is_delete=0 group by m.vistor_id limit  ".intval($pages*$pageSize-$pageSize).",".$pageSize;
		
		$list=$_DB->get_all($sql);
		
		$_DB->select_database($_CFG['link_db_base']); //切换数据库
		
		$list_info=array();

		foreach($list as $val){
			$info=array();
			$sql="select id,name,nickname,pic,sex,is_famous,type,api,access_id from user where id=".$val['doctor_id'];
			$arr=$_DB->get_row($sql);
			$info['id']=$arr['id'];
			$info['hos_id']=$val['hos_id'];
			$info['access_id']=$arr['access_id'];
			$info['deleteid']=$val['deleteid'];
			$info['name']=$arr['name'];
			$info['nickname']=$arr['nickname'];
			$info['pic']=$arr['pic'];
			$info['sex']=$arr['sex'];
			$info['is_famous']=$arr['is_famous'];
			$info['type']=$arr['type'];
			$info['api']=$arr['api'];
			$info['msg']=$val['content'];
			$info['createTime']=$val['createTime'];
			$list_info[]=$info;
		}
		
		$data['data']['totalRow']	= $count;
		$data['data']['totalPage']	= ceil($data['totalRow'] / $pageSize);
		$data['data']['list']=$list_info;
		make_json_result('200','成功',$data);
	}
	
	/*
	 * 删除记录
	 */
	public function delete(){
		global $_DB;
		
		$access_id=get_data('accessid');//数据表id
		
		if(empty($access_id)){
			make_json_result('300','参数错误，接口无法解析！',array());
		}

		$delete_id=get_data('deleteid');
		
		if(empty($delete_id)){
			make_json_result('300','参数错误，不能删除对应信息！',array());
		}
		
		$vistors_table_name='gossip_vistors_'.$access_id;//拼接表名
		
		$sql="update $vistors_table_name set is_delete=1 where id=".$delete_id;
		
		$_DB->query($sql);
		
		make_json_result('200','成功','');
	}
	
	/**
	 * 清空数据表方法
	 */
	public function clean(){
		
		global $_DB;
		$access_id=get_data('accessid');//数据表id
		$vistors_table_name='gossip_vistors_'.$access_id;//拼接表名
		$msg_table_name='gossip_msg_'.$access_id;//拼接表名
		$hos_id=get_data('hospid');
		$client_id=get_data('userid');
		$doctor_id=get_data('doctorid');
		
		/*查找用户是否存在*/
		$sql="SELECT id FROM  $vistors_table_name where hos_id='{$hos_id}' and client_id='{$client_id}' and doctor_id='{$doctor_id}'";
		$vistor_id = $_DB->get_one($sql);
	
		$sql="delete from $msg_table_name where vistor_id=$vistor_id";
		$_DB->query($sql);
		
		make_json_result('200','成功','');
	}
	

	
}