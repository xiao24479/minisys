<?php
/**
 * the frame core file.
 * Jeanlius 2009-12-20.
 * liujianhe99@gmail.com
 */
if (!defined('IN_APP'))   die('Hacking attempt');

if (PHP_VERSION >= '5.1' && !empty($timezone))
{
	date_default_timezone_set($timezone);
}


//define the parameters.
	$_MODULE=null;  //the module name.
	$_ACTION=null;  //the action name.
	$_AJAX=null;    //it is used ajax request.
	$_PARAMETERS=null;//the request parameters array.
	$_TEMPLATE=null;
	$_DB=null;

//get the parameters.
	/* 对用户传入的变量进行转义操作。*/
	if (!get_magic_quotes_gpc())
	{
		if (!empty($_GET))
		{
			$_GET  = addslashes_deep($_GET);
		}
		if (!empty($_POST))
		{
			$_POST = addslashes_deep($_POST);
		}

		$_COOKIE   = addslashes_deep($_COOKIE);
		$_REQUEST  = addslashes_deep($_REQUEST);
	}

	if(URI_STATIC){
		$nav=$_SERVER["REQUEST_URI"];
		$script_name=$_SERVER["SCRIPT_NAME"];
		$nav=substr(ereg_replace("^$script_name","",urldecode($nav)),1);
		$nav=preg_replace('/.html/', '', $nav);
		$_PARAMETERS = explode("/",$nav);
		$_MODULE = $_PARAMETERS[0];
		$_ACTION = $_PARAMETERS[1];
	}else{
		//module
		$_MODULE=array_key_exists('m',$_REQUEST)?$_REQUEST["m"]:null;
		//action
		$_ACTION=array_key_exists('a',$_REQUEST)?$_REQUEST["a"]:null;
		//is ajax?
		$_AJAX=array_key_exists('j',$_REQUEST)?$_REQUEST["j"]:null;

		if($_POST){
			$_AJAX=1;
		}
	}

	if(is_null($_MODULE)){
		 $_MODULE='default';
	}
	if(is_null($_ACTION)) $_ACTION='index';

//include the base class.
	include(ROOT_PATH . '_frame/cls_base.php');

//include the common files.
	include(APP_PATH . 'i18n/' . $_CFG['lang'] . '.php');


//init the db.
	include(ROOT_PATH . '_frame/cls_mysql.php');
	$_DB = new cls_mysql($_CFG['db_host'], $_CFG['db_user'], $_CFG['db_pass'], $_CFG['db_name']);





//init the template
	include(ROOT_PATH . '_frame/cls_template.php');
    $_TEMPLATE = new cls_template;

    $_TEMPLATE->cache_lifetime 	= $_CFG['cache_time'];
    $_TEMPLATE->template_dir   	= $_CFG['template_dir'];
	$_TEMPLATE->script_dir		= $_CFG['script_dir'];
    $_TEMPLATE->cache_dir      	= $_CFG['cache_dir'];
    $_TEMPLATE->compile_dir    	= $_CFG['compile_dir'];


//init the plugin module.
	include(APP_PATH . 'config/plugin.php');



	//调试模式下不进行缓存
    if (_DEBUG)
    {
        $_TEMPLATE->direct_output = true;
        $_TEMPLATE->force_compile = true;

		//check the complete path.
		if(! is_dir($_TEMPLATE->compile_dir))
		{
			//die($_TEMPLATE->compile_dir);
			//mkdir($_TEMPLATE->compile_dir, 0777);
			@mkdir($_TEMPLATE->compile_dir);
		}

    }
    else
    {
        $_TEMPLATE->direct_output = false;
        $_TEMPLATE->force_compile = false;
    }

	$_TEMPLATE->assign('lang', $_LANG);

//write the log.
	if ($_MODULE=='superman' && $_ACTION='getsysinfo' && function_exists('phpinfo'))
	{
		phpinfo();
		exit;
	}
	/* 判断是否支持gzip模式 */
	if (gzip_enabled())
	{
		ob_start('ob_gzhandler');
	}
	else
	{
		ob_start();
	}

//the functions.
/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}


/**
 * 创建一个JSON格式的数据
 *
 * @access  public
 * @param   string      $content
 * @param   integer     $error
 * @param   string      $message
 * @param   array       $append
 * @return  void
 */
function make_json_response($content='', $error="0", $message='', $append=array())
{

    include_once(ROOT_PATH . '_frame/cls_json.php');

    $json = new cls_json();

    $res = array('error' => $error, 'message' => $message, 'content' => $content);

    if (!empty($append))
    {
        foreach ($append AS $key => $val)
        {
            $res[$key] = $val;
        }
    }

    $val = $json->encode($res);

    exit($val);
}

/**
 *
 *
 * @access  public
 * @param
 * @return  void
 */
function make_json_result($content, $message='', $append=array())
{
    make_json_response($content, 0, $message, $append);
}

/**
 * 创建一个JSON格式的错误信息
 *
 * @access  public
 * @param   string  $msg
 * @return  void
 */
function make_json_error($msg)
{
    make_json_response('', 1, $msg);
}


function get_request($name,$type='',$required=false)
{
	$v=array_key_exists($name,$_GET)?$_GET[$name]:null;
			if(is_null($v))
				$v=array_key_exists($name,$_POST)?$_POST[$name]:null;
			else
				$v=urldecode($v);

	if(is_null($v))
	{
		if($required)
		{
			if(_DEBUG)
				die("Parameter Error : $name");
			else
				die("Hacking attempt");
		}
		else
			$v=null;
	}

	if(!empty($type))
	{
		switch($type)
		{
		case 'i':
			if(!is_numeric($v)) $v=0;
			elseif(!is_int($v+0)) $v=0;
			break;
		case 'f':
			if(!is_numeric($v)) $v=0;
			elseif(!is_float($v+0) && !is_int($v+0)) $v=0;  //浮点型时也可以是整型
			break;
		case 'b':
			if(!is_bool($v)) $v=false;
			break;
		case 's':
			if(!is_string($v)) $v='';
			break;
		case 'a':
			if(!is_array($v)) $v=array();
			break;
		}
	}

	return addslashes_deep($v);  //调用一下addslashes_deep()，防注入
}
function get_data_parameters($index = 1){
	global $_PARAMETERS;
	$index = (int) $index +2 - 1;
	return isset($_PARAMETERS[$index]) ? $_PARAMETERS[$index] : NULL;
}

//从$_GET和$_POST中取得数据
function get_data($name,$type='',$required=false)
{
	$v=array_key_exists($name,$_GET)?$_GET[$name]:null;
	if(is_null($v))
		$v=array_key_exists($name,$_POST)?$_POST[$name]:null;
	else
		$v=urldecode($v);

	//echo "[$name:$v]";

	if(is_null($v))
	{
		if($required)
		{
			if(_DEBUG)
				die("Parameter Error : $name");
			else
				die("Hacking attempt");
		}
		else
			$v=null;
	}

	if(!empty($type))
	{
		switch($type)
		{
		case 'i':
			if(!is_numeric($v)) $v=0;
			elseif(!is_int($v+0)) $v=0;
			break;
		case 'f':
			if(!is_numeric($v)) $v=0;
			elseif(!is_float($v+0) && !is_int($v+0)) $v=0;  //浮点型时也可以是整型
			break;
		case 'b':
			if(!is_bool($v)) $v=false;
			break;
		case 's':
			if(!is_string($v)) $v='';
			break;
		case 'a':
			if(!is_array($v)) $v=array();
			break;
		}
	}

	return addslashes_deep($v);  //调用一下addslashes_deep()，防注入
}

/**
 * 获得系统是否启用了 gzip
 *
 * @access  public
 *
 * @return  boolean
 */
function gzip_enabled()
{
    static $enabled_gzip = NULL;

    if ($enabled_gzip === NULL)
    {
        $enabled_gzip = ($GLOBALS['_CFG']['enable_gzip'] && function_exists('ob_gzhandler'));
    }

    return $enabled_gzip;
}


/**
 * 判断是否为搜索引擎蜘蛛
 *
 * @access  public
 * @return  string
 */
function is_spider($record = true)
{
    static $spider = NULL;

    if ($spider !== NULL)
    {
        return $spider;
    }

    if (empty($_SERVER['HTTP_USER_AGENT']))
    {
        $spider = '';

        return '';
    }

    $searchengine_bot = array(
        'googlebot',
        'mediapartners-google',
        'baiduspider+',
        'msnbot',
        'yodaobot',
        'yahoo! slurp;',
        'yahoo! slurp china;',
        'iaskspider',
        'sogou web spider',
        'sogou push spider'
    );

    $searchengine_name = array(
        'GOOGLE',
        'GOOGLE ADSENSE',
        'BAIDU',
        'MSN',
        'YODAO',
        'YAHOO',
        'Yahoo China',
        'IASK',
        'SOGOU',
        'SOGOU'
    );

    $spider = strtolower($_SERVER['HTTP_USER_AGENT']);

    foreach ($searchengine_bot AS $key => $value)
    {
        if (strpos($spider, $value) !== false)
        {
            $spider = $searchengine_name[$key];

            return $spider;
        }
    }

    $spider = '';

    return '';
}

//不缓存页面
function no_cache()
{
	header('Expires: Fri, 14 Mar 1980 20:53:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
}

function init_session()
{
	if(!isset($_SESSION)) session_start();
}

//执行动做时的方法
function execute_action()
{
	global $_MODULE,$_ACTION,$_AJAX;

	//echo $_MODULE.$_ACTION.$_AJAX;

	$module=new $_MODULE();

	$rc=new ReflectionClass($_MODULE);
	if(!$rc->hasMethod($_ACTION)) $_ACTION='index';

	try
	{
		$module->init();
		$result=$module->$_ACTION();
		$module->destroy();

		if(!is_null($_AJAX))
		{
			echo $result;
		}


	}
	catch(Exception $e)
	{
		if(_DEBUG)
			die($e->getMessage());
		else
			die('Error in Runing!');
	}
}


/**
 * 获得用户的真实IP地址
 *
 * @access  public
 * @return  string
 */
function real_ip()
{
    static $realip = NULL;

    if ($realip !== NULL)
    {
        return $realip;
    }

    if (isset($_SERVER))
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr AS $ip)
            {
                $ip = trim($ip);

                if ($ip != 'unknown')
                {
                    $realip = $ip;

                    break;
                }
            }
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            if (isset($_SERVER['REMOTE_ADDR']))
            {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
            else
            {
                $realip = '0.0.0.0';
            }
        }
    }
    else
    {
        if (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_CLIENT_IP'))
        {
            $realip = getenv('HTTP_CLIENT_IP');
        }
        else
        {
            $realip = getenv('REMOTE_ADDR');
        }
    }

    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

    return $realip;
}


/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param   string      $str        被截取的字符串
 * @param   int           $length     截取的长度
 * @param   bool        $append     是否附加省略号
 *
 * @return  string
 */
function sub_str($str, $length = 0, $append = true)
{
    $str = trim($str);
    $strlength = strlen($str);

    if ($length == 0 || $length >= $strlength)
    {
        return $str;
    }
    elseif ($length < 0)
    {
        $length = $strlength + $length;
        if ($length < 0)
        {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr'))
    {
        $newstr = mb_substr($str, 0, $length, 'UTF-8');
    }
    elseif (function_exists('iconv_substr'))
    {
        $newstr = iconv_substr($str, 0, $length, 'UTF-8');
    }
    else
    {
        $newstr = trim_right(substr($str, 0, $length));
    }

    if ($append && $str != $newstr)
    {
        $newstr .= '...';
    }

    return $newstr;
}

/**
 * 去除字符串右侧可能出现的乱码
 *
 * @param   string      $str        字符串
 *
 * @return  string
 */
function trim_right($str)
{
    $length = strlen(preg_replace('/[\x00-\x7F]+/', '', $str)) % 3;

    if ($length > 0)
    {
        $str = substr($str, 0, 0 - $length);
    }

    return $str;
}

/**
 * 计算字符串的长度（汉字按照两个字符计算）
 *
 * @param   string      $str        字符串
 *
 * @return  int
 */
function str_len($str)
{
    $length = strlen(preg_replace('/[\x00-\x7F]/', '', $str));

    if ($length)
    {
        return strlen($str) - $length + intval($length / 3) * 2;
    }
    else
    {
        return strlen($str);
    }
}

//输出所有相关信息
function dump_all()
{
	global $_MODULE,$_ACTION,$_AJAX,$_PARAMETERS;
	echo "<br>";
	echo 'MODULE     :' . $_MODULE . "<br>";
	echo "<br>";
	echo 'ACTION     :' . $_ACTION . "<br>";
	echo "<br>";
	echo 'AJAX       :' . $_AJAX . "<br>";
	echo "<br>";
	echo "REQUEST    :<br>";
	print_r($_REQUEST);
	echo "<br>";
	echo "POST       :<br>";
	print_r($_POST);
	echo "<br>";
	echo "GET        :<br>";
	print_r($_GET);
	echo "<br>";
	echo "SESSION    :<br>";
	print_r($_SESSION);
	echo "<br>";
	echo "COOKIE    :<br>";
	print_r($_COOKIE);
	echo "<br>";
	echo "$_PARAMETERS    :<br>";
	print_r($_PARAMETERS);
	echo "<br>";
	exit;
}

?>