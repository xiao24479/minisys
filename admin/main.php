<?php
error_reporting(E_ALL);
//权限模块
define('IN_APP',true);
if (__FILE__ == '')  die('Fatal error code: 0');

$path_parts = pathinfo(__FILE__);

define('APP_PATH', $path_parts["dirname"].'/');
define('APP_NAME',substr(APP_PATH,strripos(APP_PATH,"\\")+1));

define('_DEBUG',false);  //the debug model
define('URI_STATIC',false);//useing url statics,such as  admin.php/admin/login.html.
define('_I18N',false);//国际化处理。

$timezone="Asia/Shanghai"; //the time zone, such  'Asia/Shanghai'.
define('_LOG_LEVEL',5);  //the level of the log. 0 is close.
define('ROOT_PATH', dirname(str_replace('main.php', '', str_replace('\\', '/', __FILE__))).'/');


define('_LOG_FILE', APP_PATH . "php.log");  //

@ini_set('display_errors',_DEBUG);  //dont display the error message after the application published.

//echo APP_PATH;

include(APP_PATH.'/config/config.php');
include(ROOT_PATH.'_frame/core.php');


if(!$_MODULE)  die("404 no module");//$_MODULE="admin";


if(is_file(APP_PATH."module/".$_MODULE.".php"))
{
	include(APP_PATH."module/".$_MODULE.".php");
	execute_action();
}
else die("404");

if(_DEBUG ) //if
	dump_all();

?>