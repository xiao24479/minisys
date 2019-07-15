<?php
define('URL', 'http://api.agent.sogou.com:8080'); 

class Sogou_Api_Client_Core {
	private $soapClient;
	

	/**
	 * construcor of Sogou_Api_Client_Core, only need the service name.
	 * @param String $serviceName
	 */
	public function __construct($serviceName,$user) {
	

		$this->soapClient = new SoapClient ( URL . '/sem/sms/v1/' . $serviceName . '?wsdl', array ('trace' => TRUE, 'connection_timeout' => 30 ) );
		// set user's soapheader
		$sh_param = array ('username' =>  $user['username'], 'password' =>  $user['password'], 'token' =>  $user['token'] );
		// set agent's soapheader 
		//$sh_param = array ('agentusername' => AGENTUSERNAME, 'agentpassword' => AGENTPASSWORD, 'username' =>  USERNAME, 'password' =>  PASSWORD, 'token' =>  TOKEN );

		$headers = new SoapHeader ( 'http://api.sogou.com/sem/common/v1', 'AuthHeader', $sh_param );

		// Prepare Soap Client 
		$this->soapClient->__setSoapHeaders ( array ( $headers ) );
	}
	

	public function getFunctions() {
		return $this->soapClient->__getFunctions();
	}
	
	public function getTypes() {
		return $this->soapClient->__getTypes();
	}
	
	public function soapCall($function_name, array $arguments, array &$output_headers) {
		return $this->soapClient->__soapCall($function_name, $arguments, null, null, $output_headers);
	}
}


function get_sougou_hour($user,$hour){
	$service = new Sogou_Api_Client_Core('RealTimeReportService',$user);

	$output_headers = array();
	$arguments = array('GetAccountReportRequest' => array('realTimeReportRequest' => array('hour' => $hour)));
	
	$output_response = $service->soapCall('getAccountReport', $arguments, $output_headers);
	
	return $output_response->realTimeReportResponse;
	
}
function get_sogou_request_id($user,$date){
	$service = new Sogou_Api_Client_Core('ReportService',$user);

	$output_headers = array();
	$startDate= $date.'T00:00:00';
	$endDate =  $date.'T00:00:00';
	$arguments = array('getReportIdRequest' => array('reportRequestType' => 
			array ('performanceData' => array('cost',  'click', 'impression'), 'levelOfDetails' => 1, 
			'reportType' =>1, 'startDate' => $startDate, 'endDate' => $endDate)));
		
	$output_response = $service->soapCall('getReportId', $arguments, $output_headers);
	return $output_response->reportId;
}

function get_report_state($id,$user){
	$service = new Sogou_Api_Client_Core('ReportService',$user);
	$output_headers = array();
	$arguments = array('getReportStateRequest'=>array('reportId' => $id));
	$output_response = $service->soapCall('getReportState', $arguments, $output_headers);  //获取报表状态
	return  $output_response->isGenerated;
}


function get_sougou_cost($id,$user){
	$service = new Sogou_Api_Client_Core('ReportService',$user);
	$output_headers = array();
	$arguments = array('getReportPathRequest'=>array('reportId' => $id));
	$output_response = $service->soapCall('getReportPath', $arguments, $output_headers);  //获取下载地址
	$filename =	$id.'.csv.zip';
	$filename2 =	$id.'.csv';
	exec('wget -O '.$filename.'  "'.$output_response->reportFilePath.'"');  //下载
	exec(' 7za x  '.$filename);   //解压，注意服务器必须安装7za
	$file = fopen($filename2,'r');   
	while ($data = fgetcsv($file)) {
		$goods_list[] = $data;
		
	}
	 fclose($file);
	 exec("rm ".$filename." -rf");
	 exec("rm ".$filename2." -rf");
	 return $goods_list[2];

}
function get_sogou_request_plan_id($user,$date){
	$service = new Sogou_Api_Client_Core('ReportService',$user);

	$output_headers = array();
	$startDate= $date.'T00:00:00';
	$endDate =  $date.'T00:00:00';
	$arguments = array('getReportIdRequest' => array('reportRequestType' => 
			array ('performanceData' => array('cost',  'click', 'impression'), 'levelOfDetails' => 1, 
			'reportType' =>2, 'startDate' => $startDate, 'endDate' => $endDate)));
		
	$output_response = $service->soapCall('getReportId', $arguments, $output_headers);
	return $output_response->reportId;
}
function get_sougou_plan_cost($id,$user){
	$service = new Sogou_Api_Client_Core('ReportService',$user);
	$output_headers = array();
	$arguments = array('getReportPathRequest'=>array('reportId' => $id));
	$output_response = $service->soapCall('getReportPath', $arguments, $output_headers);  //获取下载地址
	$filename =	$id.'.csv.zip';
	$filename2 =	$id.'.csv';
	exec('wget -O '.$filename.'  "'.$output_response->reportFilePath.'"');  //下载
	exec(' 7za x  '.$filename);   //解压，注意服务器必须安装7za
	$file = fopen($filename2,'r');   
	while ($data = fgetcsv($file)) {
		$goods_list[] = $data;
		
	}
	 fclose($file);
	 exec("rm ".$filename." -rf");
	 exec("rm ".$filename2." -rf");
	 return $goods_list;

}



