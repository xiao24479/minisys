<?php
//init the parameters.
/*
$sourcePageURL = 'items/item1/test.html';
//设置原始的页面地址，该地址应该是可以通过smarty display直接返回的。
$newPageFile="templates/pages/test.html";
//生成的页面文件名称，该地址应该包含templages.
$displayPageName="items/item1/test.html";
//最后显示给用户的页面地址，该地址应该包含生成的路径和名称等数据。
*/

$cache_url=isset($sourcePageURL)?$sourcePageURL:'';
$cache_file=isset($newPageFile)?$newPageFile:'';
$cache_show=isset($displayPageName)?$displayPageName:'';

$cache_act=isset($_REQUEST['cache_act'])?$_REQUEST['cache_act']:'';

if($cache_url==''||$cache_file==''||$cache_show==''){
	//lack of the needs parameters.
	die('lack of the needs parameters.'.'<br />'.'must set: \'sourcePageURL\',\'newPageFile\',\'displayPageName\''); 
}

if(!$smarty){ $smarty=new Smarty;}


if($cache_act=='refresh'){
	if(file_exists($cache_file)){
		unlink($cache_file);
	}
}



//$smarty->debug=true;
//check the cache file whether exist?
if(file_exists($cache_file)){
	//just display the cache file.
	$smarty->display($cache_show);	
	exit;
}
//echo 'the cache file not exists.';
?>