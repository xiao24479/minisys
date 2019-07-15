<?php
 function image_center_crop($source, $width, $height, $target)
{
	
	
	switch (exif_imagetype($source)) {
		
		case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($source);
			break;
		case IMAGETYPE_PNG:
			$image = imagecreatefrompng($source);
			break;
		case IMAGETYPE_GIF:
			$image = imagecreatefromgif($source);
			break;
	}
	

	if (!isset($image)) return false;
	/* 获取图像尺寸信息 */
	$target_w = $width;
	$target_h = $height;
	$source_w = imagesx($image);
	$source_h = imagesy($image);
	/* 计算裁剪宽度和高度 */
	$judge = (($source_w / $source_h) > ($target_w / $target_h));
	$resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
	$resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
	$start_x = $judge ? ($resize_w - $target_w) / 2 : 0;
	$start_y = !$judge ? ($resize_h - $target_h) / 2 : 0;
	/* 绘制居中缩放图像 */
	$resize_img = imagecreatetruecolor($resize_w, $resize_h);
	imagecopyresampled($resize_img, $image, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
	$target_img = imagecreatetruecolor($target_w, $target_h);
	imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);
	/* 将图片保存至文件 */
	if (!file_exists(dirname($target))) mkdir(dirname($target), 0777, true);
	switch (exif_imagetype($source)) {
		case IMAGETYPE_JPEG:
			imagejpeg($target_img, $target);
			break;
		case IMAGETYPE_PNG:
			imagepng($target_img, $target);
			break;
		case IMAGETYPE_GIF:
			imagegif($target_img, $target);
			break;
	}
	
}


class infos_file extends cls_base{
	
	function init(){
		include_once(ROOT_PATH."_ext/auth_admin.php");
		include_once(ROOT_PATH."admin/include/lib_rights.php");
		include_once ROOT_PATH.'_ext/JSON.php';
		include_once(ROOT_PATH."_ext/Dir.class.php");
	}
	
	
	function fengmian_upload(){
					
			$uptypes=array(
				'image/jpg',
				'image/jpeg',
				'image/png'
				);
			//上传文件大小限制, 单位BYTE
			$max_file_size=2000000;     

			//上传文件路径'../uppict/'
			$destination_folder="../uppict/"; 

			//请求上传图片操作
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && 'up'==$_GET['mothed'])
			{
				//是否存在文件
				if (!is_uploaded_file($_FILES["upfile"]["tmp_name"]))
				{
				   echo "图片不存在!";
				   exit;
			   }

			   $file = $_FILES["upfile"];

				//检查文件大小
			   if($max_file_size < $file["size"])
			   {
				echo "文件太大!";
				exit;
			}

				//检查文件类型
			if(!in_array($file["type"], $uptypes))
			{
				echo "文件类型不符!".$file["type"];
				exit;
			}

			if(!file_exists($destination_folder))
			{
				mkdir($destination_folder);
			}

				//获取信息
			$filename=$file["tmp_name"];
			$image_size = getimagesize($filename);
			$pinfo=pathinfo($file["name"]);
			$ftype=$pinfo['extension'];

				//可以在这修改上传后图片的名字，这里以time()为命名
			$destination = $destination_folder.time().".".$ftype;

				//检查是否已经存在同名文件
			if (file_exists($destination) && $overwrite != true)
			{
				echo "同名文件已经存在了";
				exit;
			}

				//上传图片操作
			if(!move_uploaded_file ($filename, $destination))
			{
				echo "移动文件出错";
				exit;
			}

				//获取信息
			$pinfo=pathinfo($destination);
			$fname=$pinfo['basename'];

				//重定向浏览器 
			header('Location: http://'.$_SERVER['HTTP_HOST'].'/uppic/views/croppic.php?name='.$fname); 

				//确保重定向后，后续代码不会被执行 
			exit;
			}

			//请求截图保存操作
			else if ($_SERVER['REQUEST_METHOD'] == 'POST' && 'crop'==$_GET['mothed'])
			{
				//获取图片名
				$name=$_GET['name'];

				//高宽
				$targ_w = $targ_h = 150;
				/**
				 *范围从 0（最差质量，文件更小）
				 *到 100（最佳质量，文件最大）。
				 *默认为 IJG 默认的质量值（大约 75）
				 */
				$jpeg_quality = 90;

				//图片暂放地址'../uppict/'
				$src = "../uppict/".$_GET['name'];

				//分开图片名和图片后缀
				$arr_name = explode ( ".", $name );

				//判断图片后缀选择新建图片方式
				$img_r ='';
				if ('png' == $arr_name [1])
				{
					$img_r = imagecreatefrompng ( $src );
				} else
				{
					$img_r = imagecreatefromjpeg ( $src );
				}

				$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

				//截取图片
				imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);

				//判断图片后缀选择生成图片
				//保存位置'../userpic/'// 生成图片
				if ('png' == $arr_name [1])
				{
					imagepng ( $dst_r, '../userpic/' . $name );
				} else
				{
					imagejpeg ( $dst_r, '../userpic/' . $name, $jpeg_quality );
				}

				//显示保存后的图片
				echo '<img src="../userpic/'.$name.'" />';

				exit;
			}

					
		
	}

	
	
	/**		* ajax上传缩略图		
	+----------------------------------------------------------		
	* Time:2012-4-11		
	+----------------------------------------------------------		
	*/		
	function uploads(){								
		$error = "";			
		$msg = "";					
		$fileElementName = 'imgFile';							
		$path_parts  = ROOT_PATH;				
		$save_url  = "data/infos/"; //文件保存路径			
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
		
	
		//image_center_crop($msg,350,200,$path_parts.$save_url. $new_file_name);
		
		
		
		
		header('Content-type: text/html; charset=UTF-8');
				$json = new Services_JSON();
				echo $json->encode(array('error' => 0, 'url' => $file_url));		
				exit;
			}											
		}							
		
	}
	
	
	function upload_video(){
		$error = "";			
		$msg = "";					
		$fileElementName = 'addVideo-file';							
		$path_parts  = ROOT_PATH;				
		$save_url  = "data/infos/"; //文件保存路径			
		$save_path = $path_parts.$save_url;		
		$site_url  = 'http://'.$_SERVER['SERVER_NAME'].'/';							
		$ext_arr = array(					
		'image' => array('mp4'),			
		);			
		$max_size=10000000000000000;					
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
	
		$dir_name='video';  //只允许上传图片				

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
		
	
		//image_center_crop($msg,350,200,$path_parts.$save_url. $new_file_name);
		
		
		
		
		header('Content-type: text/html; charset=UTF-8');
				$json = new Services_JSON();
				echo $json->encode(array('error' => 0, 'url' => $file_url));		
				exit;
			}											
		}							
		
	}
	
	function uploadsImg(){								
		$error = "";			
		$msg = "";					
		$fileElementName = 'fileToUpload';							
		$path_parts  = ROOT_PATH;				
		$save_url  = "/data/infos/"; //文件保存路径			
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
	
	/**
	*上传base64数据流图片，并且剪裁规定大小
	*
	**/
	function uploadpic(){
		$image = get_data('image');
		
		$w = get_data('w');//裁剪的图片宽度
		$h = get_data('h');//裁剪的图片高度
		
		
		//匹配出图片的格式
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $image, $result)){
				$type = $result[2];
				
				$path_parts  = ROOT_PATH;				
				$save_url  = "data/infos/"; //文件保存路径			
				$save_path = $path_parts.$save_url;		
				$site_url  = 'http://'.$_SERVER['SERVER_NAME'].'/';	
				
				$dir_name=''; 
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
				$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $type;					
				$file_path = $save_path . $new_file_name;	//文件保存路径					
				
				$file_url = $site_url.$save_url . $new_file_name;  //这里设置绝对路径											
				$msg=$file_url;	
				
				
				
			if (file_put_contents($file_path, base64_decode(str_replace($result[1], '', $image)))){
				
				
				
				$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.png';					
				$file_pathn = $save_path . $new_file_name;	//新的文件保存路径	
				
				
				$re = $this->resize_image($msg,$file_pathn,$w,$h);
				if($re){
					//删除旧的图片
					unlink($file_path);
					
					$file_url = $site_url.$save_url . $new_file_name;  //这里设置绝对路径											
					$msg=$file_url;	
					
					make_json_result($msg,1,array());
				}else{
					make_json_result('图像调整尺寸失败',0,array());
				}
				
			}else{
				make_json_result('失败',0,array());
			}
		}
	}
	
	
	
	function uploadpic2(){
		$image = get_data('image');
	
	
		
		//匹配出图片的格式
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $image, $result)){
				$type = $result[2];
				
				$path_parts  = ROOT_PATH;				
				$save_url  = "data/infos/"; //文件保存路径			
				$save_path = $path_parts.$save_url;		
				$site_url  = 'http://'.$_SERVER['SERVER_NAME'].'/';	
				
				$dir_name='image'; 
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
				$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $type;					
				$file_path = $save_path . $new_file_name;	//文件保存路径					
				
				$file_url = $site_url.$save_url . $new_file_name;  //这里设置绝对路径											
				$msg=$file_url;	
				
				
				
			if (file_put_contents($file_path, base64_decode(str_replace($result[1], '', $image)))){
				
				
				
				$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.png';					
				$file_pathn = $save_path . $new_file_name;	//新的文件保存路径	
				
					
				make_json_result($msg,1,array());
			
				
			}else{
				make_json_result('失败',0,array());
			}
		}
	}

/** 
 * 改变图片的宽高 
 *  
 * @author flynetcn (2009-12-16) 
 *  
 * @param string $img_src 原图片的存放地址或url  
 * @param string $new_img_path  新图片的存放地址  
 * @param int $new_width  新图片的宽度  
 * @param int $new_height 新图片的高度 
 * @return bool  成功true, 失败false 
 */  
function resize_image($img_src, $new_img_path, $new_width, $new_height)  
{  
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