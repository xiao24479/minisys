<?php
if (!defined('IN_APP'))
{
    die('Hacking attempt');
}
define('_APPNAME', 'common');
define('_VERSION', 'V1.0.0');
define('_RELEASE', '20091203');

define('SYS_NAME', '仁爱产品渠道客服系统');
define('SYS_DESC', 'just for running!');
define('HOME_URL', '');

//database config
$_CFG=array();
//$_CFG['db_host']='192.168.1.100:3306';
$_CFG['db_host']='localhost';
$_CFG['db_name']='renai_chat';
$_CFG['db_user']='renai_chat';
$_CFG['db_pass']='gW34s6C5';
$_CFG['prefix']='';//the gm is slg_gm_  slg_gm_

//dont change!
$_CFG['db_cache']='_cache/caches/'.APP_NAME.'/';
$_CFG['db_logfile']='_data/db';

$_CFG['mail_service']=1;

$_CFG['lang']='cn';
$_CFG['cache_time']=30;

//the theme of the template.
$_CFG['template_theme']=''; //such as "/abc"

$_CFG['template_dir']=APP_PATH.'template'.$_CFG['template_theme'];
$_CFG['script_dir']=APP_PATH.'script';
$_CFG['style_dir']=APP_PATH.'style';
$_CFG['cache_dir']=ROOT_PATH.'_cache/caches/'.APP_NAME.'/';
$_CFG['compile_dir']=ROOT_PATH.'_cache/compiled/'.APP_NAME.'/';

$_CFG['static_svr']='';

$_CFG['enable_gzip']=0;

// 系统管理员角色
define('SYS_ROLE_ID',1);
// 系统管理员ID
define('SYS_ADMIN_ID',1);
?>

        