<?php
function logfile1($moduleName,$content){
	
		$filename = 'log_'.$moduleName.date("Ymd").".txt"; // 以用户名和当前时间做文件名
		// 写文件
		$fp = fopen($filename,"a");
		fwrite($fp,date("YmdHis").'---->> '.$content."\r\n"); // 写入标题和内容，第一行为标题
		fclose($fp);
}
function logfile($moduleName,$content){}
?>