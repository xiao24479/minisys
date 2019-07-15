<?php
class statics {
	
	//静态化页面，数据可以在调用该方法前assign.
	//$static_path 静态化路径，绝对路径，不存在会创建。
	//$static_file 静态化文件名称。
	//$template_file 静态化模板。
	//$smarty  静态化smarty对象。
	function staticsAction($static_path_,$static_file_,$template_file_,$smarty_){
		//die( $static_path_.$static_file_.$template_file_);
		//exit;
		$static_path=isset($static_path_)?$static_path_:'';
		$static_file=isset($static_file_)?$static_file_:'';
		$template_file=isset($template_file_)?$template_file_:'';
		//die( $static_path.$static_file.$template_file);
		
		if($static_path==''||$static_file==''||$template_file==''){
			//lack of the needs parameters.
			print_r('lack of the needs parameters.'.'<br />'.'must set: \'static_path\',\'static_file\',\'template_file\'<br>'); 
			return false;
		}
		if(!$smarty_){
			print_r('<br>You must set the smarty object.<br>'); 
			return false;
		}
		
		$pageTag = '<br>The page generate in:'.date('Y-m-d H:i:s');
		$smarty_->assign('pageTag',$pageTag);
		$contents = $smarty_->fetch($template_file);
		$this->MakeHtmlFile($static_path.$static_file,$contents);
		return true;
	}
	
	private function MakeHtmlFile($file_name, $content)
	{ //目录不存在就创建
		$dirName = dirname($file_name);
		$dirName=$dirName."/";
		
		if(!file_exists($dirName)){
			if(! $this->makedir($dirName)){
				print_r($file_name."目录创建失败！".$dirName);
				return false;
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
	} 
	
	
	private function makedir( $dir, $mode = "0777" ) {
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


}

?>