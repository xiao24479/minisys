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


    	/**
		 * 根据路径创建目录
		 */
		function mkdirs($dir, $mode = 0777){
			if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
			if (!mkdirs(dirname($dir), $mode)) return FALSE;
			return @mkdir($dir, $mode);
		}

		/**
		 * 写日志文件
		 */
		function writelog($path, $msg = ''){
			$dirname = dirname($path);
			if(!is_dir($dirname)){
				mkdirs($dirname);
			}
			$msg = '['.date('Y-m-d H:i:s').']'.$msg.PHP_EOL;
			file_put_contents($path, $msg, FILE_APPEND);
		}



	    //消息处理
	    public function responseMsg(){

	            //接收来自小程序的客户消息JSON
		        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		        if (!empty($postStr) && is_string($postStr)){
		            $postArr = json_decode($postStr,true);
		            if(!empty($postArr['MsgType']) && $postArr['MsgType'] == 'text'){   //文本消息
		                $ToUserName = $postArr['ToUserName'];/**小程序原始id**/
						$FromUserName = $postArr['FromUserName'];/**发送者openid**/
						$CreateTime = $postArr['CreateTime'];/**消息时间**/
						$MsgType = $postArr['MsgType'];/****/
						$Content = $postArr['Content'];/**消息内容**/
						$MsgId = $postArr['MsgId'];/**消息id**/

						//$conn = mysql_connect("127.0.0.1","renai_chat","sziA71Khz");
						$conn = mysql_connect("127.0.0.1","renai_chat","gW34s6C5");
						mysql_select_db("renai_chat",$conn);
						mysql_query("set names utf8");

						$sql = "select * from irpt_interface where ToUserName='{$ToUserName}';";

						$row = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);

						$msgname = "gossip_msg_".$row['aid'];
						$vistorsname = "gossip_vistors_".$row['aid'];
                        $web_id_hospital=$row['id_hospital'];
                        $web_aid=$row['aid'];
						$web_name=$row['name'];

						$sql = "select * from $vistorsname where openId='{$FromUserName}';";
						$user = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
						if(empty($user)){
                            $ip='';
							if($row['type']==0){
								 $mold=1;

							}elseif($row['type']==3){
								 $mold=6;
							}
                            $browser=$web_name;
							$lang='简体中文';
							 $os='小程序';
							$symbol=$web_name;
							$keyword=$web_name;

							$country='';
							$area='';
							$region='';
							$city='';
							$tycoon='';

							$sql = "insert into $vistorsname (`hos_id`,`openId`,`visitTime`,`ip`,`country`,`area`,`region`,`city`,`tycoon`,`os`,`browser`,`lang`,`mold`,`symbol`,`keyword`,`firstTime`,`lastTime`,`visits`) values('{$web_id_hospital}','{$FromUserName}','".time()."','{$ip}','{$country}','{$area}','{$region}','{$city}','{$tycoon}','{$os}','{$browser}','{$lang}','{$mold}','{$symbol}','{$keyword}','".time()."','".time()."',1)";
							$re = mysql_query($sql);

							$sql = "select * from $vistorsname where openId='{$FromUserName}';";
						    $user = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
						}
						if($row){
							//判断是否是当天的第一条对话
							$today_time = strtotime(date("Y-m-d"));
							$sql = "select * from $msgname where vistor_id='".$user['id']."' and createTime>='{$today_time}';";
							$is_first = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);

							$sql = "insert into $msgname(`type`,`hos_id`,`vistor_id`,`content`,`createTime`,`status`) values('1','".$row['id_hospital']."','".$user['id']."','{$Content}','{$CreateTime}','0');";

							$re = mysql_query($sql);
							//修改访客的上次访问时间
							$sql = "update $vistorsname set lastTime='{$CreateTime}' where id=".$user['id'];
							mysql_query($sql);



							if(empty($is_first)){

								$ToUserName = $ToUserName;/**小程序原始id**/
								$FromUsername = $FromUserName;   //发送者openid
								$sql = "select * from irpt_hospital where id = ".$row['id_hospital'];
								$memo = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
								$content1 = $memo['memo'];
								$data=array(
									"touser"=>$FromUsername,
									"msgtype"=>"text",
									"text"=>array("content"=>$content1)
								);
								$json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+

								$sql = "insert into $msgname(`type`,`hos_id`,`vistor_id`,`content`,`createTime`,`status`) values('2','".$row['id_hospital']."','".$user['id']."','{$content1}','{$CreateTime}','1');";
								$re = mysql_query($sql);
								mysql_close($conn);


								$access_token = $this->get_accessToken($ToUserName);
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
									echo 'success';exit;
								}
							}


						}
						echo 'success';exit;
		            }elseif(!empty($postArr['MsgType']) && $postArr['MsgType'] == 'image'){ //图文消息
		            	    $ToUserName = $postArr['ToUserName'];/**小程序原始id**/
			                $FromUsername = $postArr['FromUserName'];   //发送者openid
			                $content = '暂不支持图片消息回复!';
			                $data=array(
			                    "touser"=>$FromUsername,
			                    "msgtype"=>"text",
			                    "text"=>array("content"=>$content)
			                );
			                $json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+

			                $access_token = $this->get_accessToken($ToUserName);
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
			                    echo 'success';exit;
			                }

			         }/**elseif($postArr['MsgType'] == 'event' && $postArr['Event']=='user_enter_tempsession'){ //进入客服动作
			         	    $ToUserName = $postArr['ToUserName'];/**小程序原始id**/
			                /**$FromUsername = $postArr['FromUserName'];   //发送者openid
			                $content = '请问您有什么问题需要咨询吗，如果不方便打字，可以留个电话，稍后我给您回复。';
			                $data=array(
			                    "touser"=>$FromUsername,
			                    "msgtype"=>"text",
			                    "text"=>array("content"=>$content)
			                );
			                $json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+
**/
                             //消息写入数据表
			                /*$conn = mysql_connect("127.0.0.1","root","5R6YaAE1");
							mysql_select_db("renai_chat",$conn);
							mysql_query("set names utf8");

							$sql = "select * from irpt_interface where ToUserName='{$ToUserName}';";

							$row = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);

							$msgname = "gossip_msg_".$row['aid'];
							$vistorsname = "gossip_vistors_".$row['aid'];

							$sql = "select * from $vistorsname where openId='{$FromUserName}';";
							$user = mysql_fetch_array(mysql_query($sql),MYSQL_ASSOC);
							if($row){
								if($user && $MsgType=='text'){
									$sql = "insert into $msgname(`type`,`hos_id`,`vistor_id`,`content`,`createTime`,`status`) values('2','".$row['id_hospital']."','".$user['id']."','{$content}','".time()."','1');";

									$re = mysql_query($sql);

									mysql_close($conn);
								}
							}*/

			               // $access_token = $this->get_accessToken($ToUserName);
			                /*
			                 * POST发送https请求客服接口api
			                 */
			               /** $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
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
			                    echo 'success';exit;
			                }
			          }**/else{
			                exit('aaa');
			          }
		        }else{
		            echo "";
		            exit;
		        }

	    }


        /* 调用微信api，获取access_token*/
	    public function get_accessToken($ToUserName){

                //$conn = mysql_connect("127.0.0.1","renai_chat","sziA71Khz");
                $conn = mysql_connect("127.0.0.1","renai_chat","gW34s6C5");
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
				if(empty($access_token)){//若无数据 那么获取令牌,存入数据库
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

