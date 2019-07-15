<?php
require_once 'soapclientcore.php';

class KeywordService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('KeywordService');
	}
}

$service = new KeywordService();
$output_headers = array();

// Show service definition. 
print('----------service types-----------');
print_r($service->getTypes());
print('----------service functions-----------');
print_r($service->getFunctions());
print("----------service end-----------\n");

// Call getKeywordByAdgroupId function
$arguments = array('getKeywordByAdgroupIdRequest' => array('getTemp' => 1, 'adgroupIds' => 
		array (54204009)));
$output_response = $service->soapCall('getKeywordByAdgroupId', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);