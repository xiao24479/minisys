<?php

if( !defined( 'IN_APP' ) )
	die( 'Hacking attempt' ) ;
		function get_hid(){	init_session();	$hid = isset($_SESSION['hospital_id'])?$_SESSION['hospital_id']:'0';	return $hid;}
		function get_aid(){	init_session();	$aid = isset($_SESSION['access_id'])?$_SESSION['access_id']:'0';	return $aid;}	
//admin应用中的公用方法

if( !function_exists( 'redirect' ) )
{

	function redirect( $msg , $url , $min = "0" )
	{
		echo $msg . "\n" ;
		echo "<meta http-equiv=\"refresh\" content=\"" . $min . ";URL=" . $url . "\">\n" ;
		exit ;
	}

}

/**
 * 将字符串转成数组  tao.wu
 * @param string $str
 * @param string $charset
 */
function mbStringToArray( $str , $charset = 'UTF-8' )
{
	$strlen = mb_strlen( $str ) ;
	while( $strlen )
	{
		$arr[ ] = mb_substr( $str , 0 , 1 , $charset ) ;
		$str = mb_substr( $str , 1 , $strlen , $charset ) ;
		$strlen = mb_strlen( $str ) ;
	}

	return $arr ;
}

/**
 * 截取 通过先组合成数组，然后在截取。 tao.wu
 * @param string $str
 * @param int $len
 * @return mixed
 */
function msubStr( $str , $len )
{
	$arr = mbStringToArray( $str ) ;
	$arr = array_slice( $arr , 0 , $len ) ;

	return implode( "" , $arr ) ;
}

?>