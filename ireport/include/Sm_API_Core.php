<?php
	
function get_sm_Report_id($urer,$date){
		$data['header']['username'] = $urer['username']	;
		$data['header']['password'] = $urer['password']; 
		$data['header']['token'] =    $urer['token'];
	
		$data['body'] = array(
				'performanceData'=>array('cost',  'click', 'impression'),
				'startDate' 		=> $date,
				'endDate' 			=> $date, 
				'idOnly' 			=> false,
				'levelOfDetails'	=> 2,
				'reportType' 		=> 2,
				'format' 			=> 2,
				'unitOfTime'		=> 5,
				'statRange'			=> 2
		);
		$url ="https://e.sm.cn/api/report/getReport";

		$requrt = curl_api($url,json_encode($data));
		//$arr =  json_decode($requrt,true);
		$arr = json_decode($requrt,true,512,JSON_BIGINT_AS_STRING);

		return $arr['body']['taskId'];
}
	

function get_sm_cost($urer,$id){
		$data['header']['username'] = $urer['username'];
		$data['header']['password'] = $urer['password'];
		$data['header']['token'] 	= $urer['token'];
		$data['body'] = array(  
			'fileId' => $id,  
		); 
	   $url = "https://e.sm.cn/api/file/download";
		$requrt = curl_api($url,json_encode($data));

		return explode(",",$requrt);
}


function get_sm_Report_hour_id($urer,$date){
		$data['header']['username'] = $urer['username']	;
		$data['header']['password'] = $urer['password']; 
		$data['header']['token'] =    $urer['token'];
	
		$data['body'] = array(
				'performanceData'=>array('cost',  'click', 'impression'),
				'startDate' 		=> $date,
				'endDate' 			=> $date, 
				'idOnly' 			=> false,
				'levelOfDetails'	=> 2,
				'reportType' 		=> 2,
				'format' 			=> 2,
				'unitOfTime'		=> 7,
				'statRange'			=> 2
		);
		$url ="https://e.sm.cn/api/report/getReport";

		$requrt = curl_api($url,json_encode($data));
		
		$arr =  json_decode($requrt,true,512,JSON_BIGINT_AS_STRING);
		return $arr['body']['taskId'];
}
//计划报表
function get_sm_Report_plan_id($urer,$date){
		$data['header']['username'] = $urer['username']	;
		$data['header']['password'] = $urer['password']; 
		$data['header']['token'] =    $urer['token'];
	
		$data['body'] = array(
				'performanceData'=>array('cost',  'click', 'impression'),
				'startDate' 		=> $date,
				'endDate' 			=> $date, 
				'idOnly' 			=> false,
				'levelOfDetails'	=> 3,
				'reportType' 		=> 10,
				'format' 			=> 2,
				'unitOfTime'		=> 5,
				'statRange'			=> 2
		);
		$url ="https://e.sm.cn/api/report/getReport";

		$requrt = curl_api($url,json_encode($data));
		$arr =  json_decode($requrt,true,512,JSON_BIGINT_AS_STRING);
	
		return $arr['body']['taskId'];
}

//得到计划报表计算值
function get_sm_cost_plan($urer,$id,$plan_biaoshi){
		$data['header']['username'] = $urer['username'];
		$data['header']['password'] = $urer['password'];
		$data['header']['token'] 	= $urer['token'];
		$data['body'] = array(  
			'fileId' => $id,  
		); 
	   $url = "https://e.sm.cn/api/file/download";
		$requrt = curl_api($url,json_encode($data));
		
		$sm =  explode("\n",$requrt);
		$sms = array();
		foreach($sm as $key=>$val){
			$sms[$key] = explode(',',$val);
		}
		$costs = 0;
		$click = 0;
		$zhanxian = 0;
		foreach($sms as $k=>$v){
			if(stristr($v[4],$plan_biaoshi)){
				$costs += $v[7];
				$click += $v[6];
				$zhanxian += $v[5];
			}
		}
		$arr = array();
		$arr['costs'] = $costs;
		$arr['click'] = $click;
		$arr['zhanxian'] = $zhanxian;
		return $arr;
}


//小时-计划报表
function get_sm_Report_hour_plan_id($urer,$date){
		$data['header']['username'] = $urer['username']	;
		$data['header']['password'] = $urer['password']; 
		$data['header']['token'] =    $urer['token'];
	
		$data['body'] = array(
				'performanceData'=>array('cost',  'click', 'impression'),
				'startDate' 		=> $date,
				'endDate' 			=> $date, 
				'idOnly' 			=> false,
				'levelOfDetails'	=> 3,
				'reportType' 		=> 10,
				'format' 			=> 2,
				'unitOfTime'		=> 7,
				'statRange'			=> 2
		);
		$url ="https://e.sm.cn/api/report/getReport";

		$requrt = curl_api($url,json_encode($data));
		$arr =  json_decode($requrt,true,512,JSON_BIGINT_AS_STRING);
	
		return $arr['body']['taskId'];
}

function get_sm_cost_hour_plan($urer,$id){
		$data['header']['username'] = $urer['username'];
		$data['header']['password'] = $urer['password'];
		$data['header']['token'] 	= $urer['token'];
		$data['body'] = array(  
			'fileId' => $id,  
		); 
	   $url = "https://e.sm.cn/api/file/download";
		$requrt = curl_api($url,json_encode($data));
		
		$sm = explode("\n",$requrt);
		$sms = array();
		foreach($sm as $key=>$val){
			$sms[$key] = explode(',',$val);
		}
		return $sms;
}



