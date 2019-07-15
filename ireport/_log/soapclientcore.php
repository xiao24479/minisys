<?php
/**
 * Baidu Api PHP client core service. Please change the define of USERNAME, PASSWORD, TOKEN before usage.
 * 
 * @author Baidu Api Team
 *
 */

// Sandbox URL
//define('URL', 'https://sfapitest.baidu.com');
// Online URL
define('URL', 'https://api.baidu.com');
//USERNAME
define('USERNAME', '东方泌尿专科3');
//PASSWORD
define('PASSWORD', 'Sbr2Hi');
//TOKEN
define('TOKEN', '6cf5c7b3b2249d4f7fbfa20e66fed4d8');

class Baidu_Api_Client_Core {
	private $soapClient;
	
	/**
	 * construcor of Baidu_Api_Client_Core, only need the service name.
	 * @param String $serviceName
	 */
	public function __construct($serviceName) {
		$this->soapClient = new SoapClient ( URL . '/sem/sms/v2/' . $serviceName . '?wsdl', array ('trace' => TRUE, 'connection_timeout' => 30 ) );
		
		// Prepare SoapHeader parameters 
		$sh_param = array ('username' => USERNAME, 'password' => PASSWORD, 'token' => TOKEN );
		$headers = new SoapHeader ( 'http://api.baidu.com/sem/common/v2', 'AuthHeader', $sh_param );
		
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