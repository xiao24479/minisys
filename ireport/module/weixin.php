<?php

/**
 * 接口管理
 +----------------------------------------------------------
 * @author 围剿
 * Time:2011-11-11
 +----------------------------------------------------------
 */

class weixin extends cls_base{
	
	function init()
	{	
		
		
	}
	function test(){
		$this->writelog('../ceshi2','456123');
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
			if(!empty($postStr)){
				$this->writelog('../ceshi2','不为空1');
				$this->writelog('../ceshi2',$postStr);
				
			}else{
				$this->writelog('../ceshi2','空的1');
			}
			
			
		
	}
	
		
			/**
 * 根据路径创建目录
 */
function mkdirs($dir, $mode = 0777)
{
	if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
	if (!mkdirs(dirname($dir), $mode)) return FALSE;
	return @mkdir($dir, $mode);
}

/**
 * 写日志文件
 */
function writelog($path, $msg = ''){
	$dirname = dirname($path);
	if(!is_dir($dirname)){
		mkdirs($dirname);
	}
	$msg = '['.date('Y-m-d H:i:s').']'.$msg.PHP_EOL;
	file_put_contents($path, $msg, FILE_APPEND);
}

	

}

?>