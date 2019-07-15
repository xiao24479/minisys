<?php
if (!defined('IN_APP'))    die('Hacking attempt');

$_CFG=array();

$_CFG['domain']=''; //the website domain.
//数据库配置
$_CFG['db_host']='localhost'; //192.168.1.100  localhost
$_CFG['db_name']='renai_chat';
$_CFG['db_user']='renai_chat'; //root 
$_CFG['db_pass']='gW34s6C5'; //gm
$_CFG['prefix']='';//$_CFG['db_name'] . '_'; //the gm is slg_gm_
$_CFG['db_cache']='_cache/caches/'.APP_NAME.'/';
$_CFG['db_logfile']='_data/log';

//模板配置
$_CFG['lang']='cn';
$_CFG['theme']=''; //such as '/blue' or /theme2
$_CFG['cache_time']=30;
$_CFG['template_dir']=APP_PATH.'template'.$_CFG['theme'];
$_CFG['script_dir']=APP_PATH.'script';
$_CFG['style_dir']=APP_PATH.'style';
$_CFG['cache_dir']=ROOT_PATH.'_cache/caches/'.APP_NAME.'/';
$_CFG['compile_dir']=ROOT_PATH.'_cache/compiled/'.APP_NAME.'/';

//服务器选项
$_CFG['enable_gzip']=0;

define('DOMAIN',$_CFG['db_user']);
?>

        