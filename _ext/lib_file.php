<?php
function logfile($moduleName,$content){
		$filename = 'log_'.$moduleName.date("Ymd").".txt"; // 以用户名和当前时间做文件名
		// 写文件
		$fp = fopen($filename,"a");
		fwrite($fp,date("YmdHis").'---->> '.$content."\r\n"); // 写入标题和内容，第一行为标题
		fclose($fp);
}

function dir_read($path){
	$datas = array();
	if (is_dir($path)){
	$dir = opendir($path);
	while ($file = readdir($dir)){
		$datas[] = $file;
	}
		closedir($dir);
	}
	else{
		echo "$path 不是一个有效的目录";
	}
	
	return $datas;
}//dir_read 



function makedir( $dir, $mode = "0777" ) {
		 if( ! $dir ) return 0;
		 $dir = str_replace( "\\", "/", $dir );
		 $mdir = "";
		 foreach( explode( "/", $dir ) as $val ) {
		  $mdir .= $val."/";
		  if( $val == ".." || $val == "." ) continue;
		  
			  if( ! file_exists( $mdir ) ) {
				   if(!@mkdir( $mdir, $mode )){
					// "创建目录 [".$mdir."]失败.";
					return false;
					exit;
				   }
				   chmod($mdir,0777);
			  }
		 }
		 return true;
	}
	
 function dir_copy($fdir,$tdir)
{

	if(is_dir($fdir))
	{
	 	if (!is_dir($tdir))
		{
			mkdir($tdir);
		}
		
		$handle =opendir($fdir);
		while(false!==($filename=readdir($handle)))
		{
			if($filename!="."&&$filename!="..")dir_copy($fdir."/".$filename,$tdir."/".$filename);
		}
		closedir($handle);
	   
		return true;
	}
	else
	{
		copy($fdir,$tdir);
		return true;
	}

}
?>