<?php
/**
 * the frame core file.
 * Jeanlius 2009-12-20.
 * liujianhe99@gmail.com
 */
include_once(ROOT_PATH."_ext/lib_file.php");
/*
include_once("e:/www/flys/_frame/cls_base.php");

include_once("e:/www/flys/_extends/lib_file.php");
$appeng = new appeng();

$appeng->inits("e:/www/flys/","demo");
$filestrus = $appeng->fileStrus();
echo "the file struts is: ";print_r($filestrus); echo "\n";

$modulesf = $appeng->modules();
echo "the file modulesf is: ";print_r($modulesf); echo "\n";


$actions = $appeng->actions("test");
echo "the file actions is: ";print_r($actions); echo "\n";



if ($appeng->addApps("abc"))
	echo "ok";
else echo "error";

*/
class appeng 
{
	var $appName;
	var $rootPath;
		
	function inits($rootPath,$appName)
	{
		$this->appName=$appName;
		$this->rootPath =$rootPath;
	}
	
	
	//get all of the directorys in the app.
	function dirStrus($paths="")
	{
		$appsArray = dir_read($this->rootPath .$paths);
		return $appsArray;
	}
	
	//get all of the directorys in the app.
	function fileStrus($paths="")
	{
		//$fileContents = file_get_contents($this->rootPath .$paths);
		
		$fileContents = fread(fopen($this->rootPath .$paths,r),filesize($this->rootPath .$paths));
		return $fileContents;
	}
	
	function dirProcess($dirstructs,$paths)
	{
		$dirs=array();
		$files=array();
		
		$fpath = $this->rootPath.$paths;
		
		foreach($dirstructs as $adir)
		{
			if($adir=="." || $adir=="..")
				continue;
			
			if(is_file($fpath."/".$adir))
				$files[]=$adir;
			else
				$dirs[]=$adir;
		
		}
		return array("dirs"=>$dirs,"files"=>$files);
	
	}
	
	
	//get  all of the  module by app.
	function modules()
	{
		$appsArray = dir_read($this->rootPath.$this->appName."/module/");
		$res=array();
		foreach ($appsArray as $afile)
		{
			if($afile=="." || $afile=="..")
				continue;
			
			if(is_file($this->rootPath .$this->appName."/module/".$afile))
				$res[] = $afile;		
		}
		
		return $res;
	}
	
	//get all of the actions by module and app.
	function actions($aModule)
	{
		include_once($this->rootPath.$this->appName."/"."module/".$aModule.".php");
		
		//$module = new $aModule();
		$rc=new ReflectionClass($aModule);
		
		$arrayActions = $rc->getMethods();
	
		
		return $arrayActions;
		
		
	}
	
	//add a module to the exits app.	
	function addModule()
	{
		
	}
	
	//add a action to the exits module.
	function addAction()
	{
		
	}
	
	//add a new application .
	function addApps($appName)
	{
		//must not exit.
		$appsArray = dir_read($this->rootPath);
		if(in_array($appName,$appsArray))
		{//cant.
			return false;
		}else
		{	//can.
			//1.create the directory
			$path = $this->rootPath.$appName;
			
			makedir($path);
			
			//2.copy the dir and files from demo to the new app.
			dir_copy($this->rootPath."demo/",$this->rootPath.$appName."/");
			return true;
		}
	}
	
	

}


?>