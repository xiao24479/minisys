<?php
if (!defined('IN_APP')) define( 'IN_APP', true );
if(!defined( 'ROOT_PATH' ) ) define('ROOT_PATH', dirname(str_replace('check_code.php', '', str_replace('\\', '/', __FILE__))).'/');
require_once(ROOT_PATH.'_frame/core.php');
init_session();
$code=get_data('code','s');

putenv('GDFONTPATH='.realpath('.'));
$font=ROOT_PATH.'_frame/font.ttf';


$w=50;
$h=20;

//die(ROOT_PATH);

$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ38564';
$check_code='';
$max=strlen($chars)-1;
for($i=0;$i<4;$i++)
{
	$check_code.=$chars[mt_rand(0, $max)];
	/*$n=rand(0,35);
	if($n<10)
		$check_code.=chr(48+$n);
	else
		$check_code.=chr(87+$n);*/
}
//$check_code='1234';
//echo $check_code;
//die;
$_SESSION['_check_code_'.$code]=$check_code;

$img=imageCreate($w,$h);
ImageColorAllocate($img,rand(160,255),rand(160,255),rand(160,255));

for($i=0;$i<5;$i++)
{
	//bool imageline ( resource image, int x1, int y1, int x2, int y2, int color )
	$lc=ImageColorAllocate($img,rand(100,255),rand(100,255),rand(100,255));
	imageline($img,rand(0,$w),rand(0,$h),rand(0,$w),rand(0,$h),$lc);
}


$gray=ImageColorAllocate($img,180,180,180);
$fc=ImageColorAllocate($img,rand(0,160),rand(0,160),rand(0,160));
//ImageRectangle($img,0,0,$w-1,$h-1,$gray);

//imageString($img,5,5,3,$check_code,$gray);
//imagettftext ( resource image, float size, float angle, int x, int y, int color, string fontfile, string text )
imagettftext($img,11,0,2,15,$fc,$font,$check_code);

Header("Content-type: image/png");
ImagePng($img);
ImageDestroy($img);

?>