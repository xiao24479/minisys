<?php
require_once 'soapclientcore.php';

class KRService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('KRService');
	}
}

$service = new KRService();
$output_headers = array();

// Show service definition. 
print('----------service types-----------');
print_r($service->getTypes());
print('----------service functions-----------');
print_r($service->getFunctions());
print("----------service end-----------\n");

// Call getKRbySeedWord function
$arguments = array('getKRbySeedWordRequest' => array('seedWord' => '中国节', 'seedFilter' => 
		array ('matchType' => 3, 'maxNum' => 20)));
$output_response = $service->soapCall('getKRbySeedWord', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);