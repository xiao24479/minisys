<?php
//generate the static file.

	$pageTag = '<br>The page generate in:'.date('Y-m-d H:i:s');
	
	//echo $pageTag;
	//exit; 
	
	$smarty->assign('pageTag',$pageTag);

	$contents = $smarty->fetch($cache_url);
	MakeHtmlFile($newPageFile,$contents);
	
	//display the static file.		
	$smarty->display($cache_show);
	exit;
	
function MakeHtmlFile($file_name, $content)
{ //目录不存在就创建
	$dirName = dirname($file_name);
	$dirName=$dirName."/";
	if(!file_exists($dirName)){
		if(!mkdir($dirName)){
		die($file_name."目录创建失败！".$dirName);
		}
	}

	if(!$fp = fopen($file_name, "w")){
	echo "文件打开失败！";
	return false;
	}
	
 	

	if(!fwrite($fp, $content)){
	echo "文件写入失败！";
	fclose($fp);
	return false;
	}

	fclose($fp);
	//chmod($file_name,0666);
} 
?>