<?php
require_once 'soapclientcore.php';

class CampaignService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('CampaignService');
	}
}

$service = new CampaignService();
$output_headers = array();

// Show service definition. 
print('----------service types-----------');
print_r($service->getTypes());
print('----------service functions-----------');
print_r($service->getFunctions());
print("----------service end-----------\n");

// Call getAllCampaignId function
$arguments = array('getAllCampaignIdRequest' => array());
$output_response = $service->soapCall('getAllCampaignId', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);

// Call getCampaignByCampaignId function
$arguments = array('getCampaignByCampaignIdRequest' => array('campaignIds' => array(341395, 341332)));
$output_response = $service->soapCall('getCampaignByCampaignId', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);