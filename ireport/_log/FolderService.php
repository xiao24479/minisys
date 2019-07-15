<?php
require_once 'soapclientcore.php';

class FolderService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('FolderService');
	}
}

$service = new FolderService();
$output_headers = array();

// Show service definition. 
print('----------service types-----------');
print_r($service->getTypes());
print('----------service functions-----------');
print_r($service->getFunctions());
print("----------service end-----------\n");

// Call getFolder function
$arguments = array('getFolderRequest' => array());
$output_response = $service->soapCall('getFolder', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);

// Call getMonitorWordByFolderId function
$arguments = array('getMonitorWordByFolderIdRequest' => array('folderIds' => array(924, 903, 830, 827)));
$output_response = $service->soapCall('getMonitorWordByFolderId', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);