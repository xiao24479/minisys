<?php
/**
 * 系统文件上传类
 +----------------------------------------------------------
 * @author 围剿
 * Time:2012-3-10
 +----------------------------------------------------------
 */
class file extends cls_base{

	function init(){
		require_once(ROOT_PATH."_ext/auth_admin.php");
		require_once(ROOT_PATH."admin/include/lib_rights.php");
		require_once ROOT_PATH.'_ext/JSON.php';
		require_once(ROOT_PATH."_ext/Dir.class.php");
	}
	
			
	/**		* ajax上传缩略图		
	+----------------------------------------------------------		
	* Time:2012-4-11		
	+----------------------------------------------------------		
	*/		
	function uploadsImg(){								
		$error = "";			
		$msg = "";					
		$fileElementName = 'fileToUpload';							
		$path_parts  = ROOT_PATH;				
		$save_url  = "/data/uploads/"; //文件保存路径			
		$save_path = $path_parts.$save_url;		
		$site_url  = 'http://'.$_SERVER['SERVER_NAME'].'/';							
		$ext_arr = array(					
		'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),			
		);			
		$max_size=1000000;					
		if(empty($_FILES) === false) {									
		$file_name = $_FILES[$fileElementName]['name'];				
		$tmp_name = $_FILES[$fileElementName]['tmp_name'];				
		$file_size = $_FILES[$fileElementName]['size'];						
	
		if (!$file_name) {					
			$error="请选择要上传的文件!";				
		}						
		if (@is_dir($save_path) === false) {					
			Dir::mk_dir($save_path); //创建目录				
		}						
	
		if (@is_writable($save_path) === false) {					
			$error="上传目录没有写权限!";				
		}				
	
		if (@is_uploaded_file($tmp_name) === false) {					
			$error="临时文件可能不是上传文件!";										
		}				
	
		if ($file_size > $max_size) {					
			$error="上传文件大小超过限制!";										
		}						
	
		$dir_name='image';  //只允许上传图片				
	
		$temp_arr = explode(".", $file_name);				
	
		$file_ext = array_pop($temp_arr);				
	
		$file_ext = trim($file_ext);				
	
		$file_ext = strtolower($file_ext);						
	
		if (in_array($file_ext, $ext_arr[$dir_name]) === false) {					
				$error="上传文件扩展名是不允许的扩展名。只允许" . implode(",", $ext_arr[$dir_name]) . "格式。";						
		}				
	
		if($error=='')				{					
		//创建文件夹					
	
		if ($dir_name !== '') {						
		$save_path .= $dir_name . "/";						
		$save_url .= $dir_name . "/";						
	
			if (!file_exists($save_path)) {							
			mkdir($save_path);						
			}					
		}					
		$ymd = date("Ymd");					
		$save_path .= $ymd . "/";					
		$save_url .= $ymd . "/";					
		if (!file_exists($save_path)) {						
			mkdir($save_path);					
		}					
		$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;					
		$file_path = $save_path . $new_file_name;					
		if (move_uploaded_file($tmp_name, $file_path) === false) {						
		echo "{";						
		echo				"error: '上传文件失败!'\n";						
		echo "}";					
		}					
		@chmod($file_path, 0644);											
		$file_url = $site_url.$save_url . $new_file_name;  //这里设置绝对路径											
		$msg=$file_url;										
		}				
		echo "{";				
		echo				"error: '" . $error . "',\n";				
		echo				"msg: '" . $msg . "'\n";				
		echo "}";								
		}							
		
	}


	//base64图片处理
	function base64_save() {
		$base64 = get_data('image');
		$w      = get_data('w');
		$h      = get_data('h');
		$arr    = explode(',', $base64);
		$data   = [];

		$path      = ROOT_PATH;
		$hid       = $_SESSION['hospital_id'];
		$url       = $_SERVER['HTTP_HOST'];

		if(strpos($url, 'https') !== false){
			$url = "https://" . $url;
		}else{
			$url = "http://" . $url;
		}
		
		$dir_arr = ['data', 'uploads', 'images', 'small_program', $hid];

		foreach($dir_arr as $v){
			$url  .= '/'.$v;
			$path .= '/'.$v;
			if(!file_exists($path)){
				mkdir($path);
			}
		}

		$base_name = date('YmdHis', time()) . uniqid() . '.jpeg';

		$url   .= '/' . $base_name;
		$path  .= '/' . $base_name;

		$res = file_put_contents($path, base64_decode($arr[1]));

		$data['message'] = $res ? 1 : 0;
		$data['content'] = $res ? $url : '上传失败';
		echo json_encode($data);
	}


	function resize_image($img_src, $new_img_path, $new_width, $new_height)  {  

	    $img_info = @getimagesize($img_src);  

	    if (!$img_info || $new_width < 1 || $new_height < 1 || empty($new_img_path)) {  
	        return false;  
	    }  

	    if (strpos($img_info['mime'], 'jpeg') !== false) {  

	        $pic_obj = imagecreatefromjpeg($img_src);  

	    } else if (strpos($img_info['mime'], 'gif') !== false) {  

	        $pic_obj = imagecreatefromgif($img_src);  

	    } else if (strpos($img_info['mime'], 'png') !== false) {  

	        $pic_obj = imagecreatefrompng($img_src);  

	    } else {  

	        return false;  

	    }  

	    $pic_width = imagesx($pic_obj);  

	    $pic_height = imagesy($pic_obj);  

	    if (function_exists("imagecopyresampled")) {  

	        $new_img = imagecreatetruecolor($new_width,$new_height);  

	        imagecopyresampled($new_img, $pic_obj, 0, 0, 0, 0, $new_width, $new_height, $pic_width, $pic_height);  

	    } else {  

	        $new_img = imagecreate($new_width, $new_height);  

	        imagecopyresized($new_img, $pic_obj, 0, 0, 0, 0, $new_width, $new_height, $pic_width, $pic_height);  

	    }  

	    if (preg_match('~.([^.]+)$~', $new_img_path, $match)) {  

	        $new_type = strtolower($match[1]);  

	        switch ($new_type) {  

	            case 'jpg':  
	                imagejpeg($new_img, $new_img_path);  
	                break;  

	            case 'gif':  
	                imagegif($new_img, $new_img_path);  
	                break;  

	            case 'png':  
	                imagepng($new_img, $new_img_path);  
	                break;  

	            default:  
	                imagejpeg($new_img, $new_img_path);  

	        }  

	    } else {  

	        imagejpeg($new_img, $new_img_path);  

	    }  

	    imagedestroy($pic_obj);  

	    imagedestroy($new_img);  	

	    return true;  

	}  
	
}




?>