<?php
require_once 'soapclientcore.php';

class ReportService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('ReportService');
	}
}

$service = new ReportService();
$output_headers = array();

// Show service definition. 
print('----------service types-----------');
print_r($service->getTypes());
print('----------service functions-----------');
print_r($service->getFunctions());
print("----------service end-----------\n");

// Call getProfessionalReportId function
$arguments = array('getProfessionalReportIdRequest' => array('reportRequestType' => 
		array ('performanceData' => array('cost', 'cpc', 'click', 'impression', 'ctr', 'cpm'), 'levelOfDetails' => 3, 
		'reportType' => 3, 'startDate' => '2011-01-01T00:00:00', 'endDate' => '2011-01-16T00:00:00')));
$output_response = $service->soapCall('getProfessionalReportId', $arguments, $output_headers);
print('----------output body-----------');
print_r($output_response);
print('----------output header-----------');
print_r($output_headers);