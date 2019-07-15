<?php
define("TOKEN","xcxToken");// 后台填写的token，在微信公众平台启用

$wechatObj = new wxApi();
if (!isset($_GET['echostr'])) {
   $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wxApi{

	     //验证签名
	    public function valid(){
	        $echoStr = $_GET["echostr"];
	        $signature = $_GET["signature"];
	        $timestamp = $_GET["timestamp"];
	        $nonce = $_GET["nonce"];
	        $token = TOKEN;
			
	        $tmpArr = array($token, $timestamp, $nonce);
	        sort($tmpArr, SORT_STRING);
	        $tmpStr = implode($tmpArr);
	        $tmpStr = sha1($tmpStr);
			
	        if($tmpStr == $signature){
	            echo $echoStr;
	            exit;
	        }
	    }


		public function responseMsg() {
			$data = file_get_contents("php://input");
			file_put_contents('log.txt', $data);
			$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			
			$toUserName = $xml->ToUserName;
			$fromUserName = $xml->FromUserName;
			$createTime = $xml->CreateTime;
			$msgType = $xml->MsgType;
			$content = $xml->Content;
			$msgId = $xml->MsgId;	
			
			file_put_contents('log.txt', $data);
			
			if($msgType != 'text' || $content == ''){
				echo 'success';
				exit;
			}
			
			$conn = mysql_connect("127.0.0.1", "root", "sziA71Khz");
			mysql_select_db("renai_chat",$conn);
			mysql_query("set names utf8");
			
			$sql = "select * from irpt_interface where ToUserName='{$toUserName}';";
			$row = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
					
			$msgname = "gossip_msg_".$row['aid'];
			$vistorsname = "gossip_vistors_".$row['aid'];	
			$hid = $row['id_hospital'];
			
			$sql = "select * from $vistorsname where openId='{$fromUserName}';";
			$res = mysql_query($sql);
			$vistor = mysql_fetch_array($res,MYSQL_ASSOC);
			
			if(empty($vistor)){
				$mold=2;
				$os='公众号';
				$lang='简体中文';
				$symbol = $row['name'];
				$keyword = $row['name'];
				$browser = 	$row['name'];
	
				$sql = "insert into $vistorsname (`hos_id`,`openId`,`visitTime`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) 
						values('{$hid}','{$fromUserName}','".time()."','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
				$res = mysql_query($sql);
				$vid = mysql_insert_id();
				
			}else{
				$vid = $vistor['id'];	
				
				//修改访客的上次访问时间
				$sql = "update $vistorsname set lastTime='{$createTime}' where id = {$vid}";
				mysql_query($sql);				
			}	
			
			//判断是否是当天的第一条对话
			$today_time = strtotime(date("Y-m-d"));
//			$today_time = strtotime("2018-10-01");
			
			$sql = "select * from {$msgname} where vistor_id={$vid} and createTime >= '{$today_time}';";
			$result = mysql_query($sql);
			$is_first = mysql_fetch_assoc($result);
			
			$sql = "insert into $msgname(`type`,`hos_id`,`vistor_id`,`content`,`createTime`,`status`) 
					values('1', {$hid}, {$vid}, '{$content}', '{$createTime}', '0');";
			mysql_query($sql);
			
			if(empty($is_first)){	
				$sql = "select * from irpt_hospital where id = {$hid}";
				$hospital = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
				file_put_contents('log.txt', serialize($hospital));
				$memo = $hospital['memo'];
							
				$sql = "insert into $msgname(`type`,`hos_id`,`vistor_id`,`content`,`createTime`,`status`) 
						values('2', {$hid}, {$vid},'{$memo}', ".time().",'1');";
				mysql_query($sql);
				mysql_close($conn);	
				
				$data=array(
					"touser"=>$fromUserName,
					"msgtype"=>"text",
					"text"=>array("content"=>$memo)
				);
				
				$json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+
				$access_token = $this->get_accessToken($toUserName);
				
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
				//curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
				$output = curl_exec($curl);
				if (curl_errno($curl)) {
					echo 'Errno'.curl_error($curl);//捕抓异常
				}
				curl_close($curl);
				
				if($output == 0){
					echo 'success';
				}
				
			}else{
				echo 'success';	
			}
			
		}

		
        /* 调用微信api，获取access_token*/
	    public function get_accessToken($ToUserName){
            $conn = mysql_connect("127.0.0.1","root","vA5L3BsX");
			mysql_select_db("renai_chat",$conn);
			mysql_query("set names utf8");

			$sql = "select id,appId,token,access_token,createTime from irpt_interface where ToUserName='{$ToUserName}';";
					
			$row = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
            $id=$row['id'];
            $appId=$row['appId'];
            $token=$row['token'];
            $access_token=$row['access_token'];
            $createTime=$row['createTime'];
			$times=strtotime('now');//当前时间

			//数据时间-当前时间  小于800s
			if(empty($access_token)){	//若无数据 那么获取令牌,存入数据库
				$timestamp=strtotime('now')+6000;//100分钟后
				$token= $this->Curl($appId,$token);	

				$sql="update irpt_interface set access_token='{$token}',createTime='{$timestamp}' where id=".$id;
				mysql_query($sql);
				mysql_close($conn);
				return $token;
				
			}else{
				//超过数据的时间，那么重新获取令牌
				if($createTime < $times){
						$timestamp=strtotime('now')+6000;//100分钟后
				        $token= $this->Curl($appId,$token);
				        
						$sql="update irpt_interface set access_token='{$token}',createTime='{$timestamp}' where id=".$id;
				        mysql_query($sql);
				        mysql_close($conn);
				        return $token;
			     }else{//没超过，则从数据库取
			     	    mysql_close($conn);
						return $access_token;
			     }
            }
	     }

         
        //获取 access_token  当然在这之前请连接好自己的数据库
		public function Curl($appid,$appsecret) {
			$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$dataBlock = curl_exec($ch);//这是json数据
			curl_close($ch);
			$res = json_decode($dataBlock, true); //接受一个json格式的字符串并且把它转换为 PHP 变量
		
			return $res['access_token'];
		}

}

