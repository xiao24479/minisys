<?php

function baidu_cost($user,$passwd,$token,$date){  
		$data['header']['username'] = $user	;
		$data['header']['password'] = $passwd; 
		$data['header']['token'] =  $token;
		$data['header']['action'] = '';
		$data['body']['realTimeRequestTypes'] = array(
				'performanceData'=>array('cost',  'click', 'impression'),
				'startDate' 		=> $date."T00:00:00.00",
				'endDate' 			=> $date."T00:00:00.00", 
				'levelOfDetails' 	=> 2,
				'statRange' 		=> 2,
				'unitOfTime' 		=> 5,
				'reportType' 		=> 2,
				'Device' 			=> 2,
				'platform'			=> 0
		);
	
		$url = 'https://api.baidu.com/json/sms/v3/ReportService/getRealTimeData';
		$requrt = curl_api($url,json_encode($data));
		$arr =  json_decode($requrt,true);
		return $arr['body']['realTimeResultTypes'][0];
}

function baidu_cost_hour($user,$passwd,$token,$date){
	$data['header']['username'] = $user	;
		$data['header']['password'] = $passwd; 
		$data['header']['token'] =  $token;
		$data['header']['action'] = '';
		$data['body']['realTimeRequestTypes'] = array(
		
				'performanceData'=>array('cost', 'click', 'impression'),
				'startDate' 		=> $date."T00:00:00.00",
				'endDate' 			=> $date."T00:00:00.00", 
				'levelOfDetails' 	=> 2,
				'statRange' 		=> 2,
				'unitOfTime' 		=> 7,
				'reportType' 		=> 2,
				'Device' 			=> 0,
				'platform'			=> 0,
		);
	
		$url = 'https://api.baidu.com/json/sms/v3/ReportService/getRealTimeData';
		
		

		$requrt = curl_api($url,json_encode($data));
		$arr =  json_decode($requrt,true);
		return $arr['body']['realTimeResultTypes'];
}
//获取一天的计划报表
function baidu_cost_plan($user,$passwd,$token,$date){
	$data['header']['username'] = $user	;
		$data['header']['password'] = $passwd; 
		$data['header']['token'] =  $token;
		$data['header']['action'] = '';
		$data['body']['realTimeRequestTypes'] = array(
		
				'performanceData'=>array('cost', 'click', 'impression'),
				'startDate' 		=> $date."T00:00:00.00",
				'endDate' 			=> $date."T00:00:00.00", 
				'levelOfDetails' 	=> 3,
				'statRange' 		=> 3,
				'unitOfTime' 		=> 5,
				'reportType' 		=> 10,
				'Device' 			=> 0,
				'platform'			=> 0,
				'order'				=> null
		);
	
		$url = 'https://api.baidu.com/json/sms/v3/ReportService/getRealTimeData';
		
		
		
		$requrt = curl_api($url,json_encode($data));
		$arr =  json_decode($requrt,true);
		return $arr['body']['realTimeResultTypes'];
}

//获取小时段的计划报表
function baidu_cost_plan_hour($user,$passwd,$token,$date){
	$data['header']['username'] = $user	;
		$data['header']['password'] = $passwd; 
		$data['header']['token'] =  $token;
		$data['header']['action'] = '';
		$data['body']['realTimeRequestTypes'] = array(
		
				'performanceData'=>array('cost', 'click', 'impression'),
				'startDate' 		=> $date."T00:00:00.00",
				'endDate' 			=> $date."T00:00:00.00", 
				'levelOfDetails' 	=> 3,
				'statRange' 		=> 3,
				'unitOfTime' 		=> 7,
				'reportType' 		=> 10,
				'Device' 			=> 0,
				'platform'			=> 0,
				'order'				=> null
		);
	
		$url = 'https://api.baidu.com/json/sms/v3/ReportService/getRealTimeData';
		
		
		
		$requrt = curl_api($url,json_encode($data));
		$arr =  json_decode($requrt,true);
		return $arr['body']['realTimeResultTypes'];
}
