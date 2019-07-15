<?php
require_once 'soapclientcore.php';

class AdgroupService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('AdgroupService');
	}
}

$service = new AdgroupService();
$output_headers = array();

// Show service definition. 
print('----------service types-----------');
print_r($service->getTypes());
print('----------service functions-----------');
print_r($service->getFunctions());
print("----------service end-----------\n");

// Call getAdgroupByCampaignId function
$arguments = array('getAdgroupByCampaignIdRequest' => array('campaignIds' => array(341395, 341332, 344175)));
$output_response = $service->soapCall('getAdgroupByCampaignId', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);