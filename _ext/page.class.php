<?php
class Page{
	// 起始行数
    public $firstRow;
    //分页GET参数
    public $listRows;
    public $type='p';
    // 页数跳转时要带的参数
    public $pageButtonCunt="6";
    public $parameter;
    // 分页总页面数
    protected $totalPages;
    
    // 总行数
    protected $totalRows;
    // 当前页数
    public $nowPage;
    // 分页的栏的总页数
    protected $coolPages;
    // 分页栏每页显示的页数
    protected $rollPage;
	// 分页显示定制
    protected $config =array(
    				'header'=>'条记录',
    				'prev'=>'上一页',
    				'next'=>'下一页',
    				'first'=>'第一页',
    				'last'=>'最后一页',
    				'theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
	
 public function __construct($totalRows,$listRows,$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->rollPage =  $this->pageButtonCunt;
        $this->listRows = !empty($listRows)?$listRows:20;
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET['p'])?$_GET['p']:1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
   }
   /**
    * 设定显示的分页条数
    * Enter description here ...
    * @param $num
    */
   public function setRoll($num){
   	  $this->rollPage=$numm;
   }
   public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
   }
   
   public function show($funName, $param1='', $param2='') {
        if(0 == $this->totalRows) return '';
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a href=\"javascript:".$funName."(".$upRow.",'".$param1."','".$param2."')\">".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }
		
        if ($downRow <= $this->totalPages){
            $downPage="<a href=\"javascript:".$funName."(".$downRow.",'".$param1."','".$param2."')\">".$this->config['next']."</a>";
        }else{
            $downPage="";
        }        // << < > >>
        if(0!=$this->rollPage){
	        if($nowCoolPage == 1){
	            $theFirst = "";
	            $prePage = "";
	        }else{
	            $preRow =  $this->nowPage-$this->rollPage;
	            $prePage = "<a href=\"javascript:".$funName."(".$preRow.",'".$param1."','".$param2."');\">上".$this->rollPage."页</a>";
	            $theFirst = "<a href=\"javascript:".$funName."(1, '".$param1."','".$param2."');\" >".$this->config['first']."</a>";
	        }
	        if($nowCoolPage == $this->coolPages){
	            $nextPage = "";
	            $theEnd="";
	        }else{
	            $nextRow = $this->nowPage+$this->rollPage;
	            $theEndRow = $this->totalPages;
	            $nextPage = "<a href=\"javascript:".$funName."(".$nextRow.",'".$param1."','".$param2."');\" >下".$this->rollPage."页</a>";
	            $theEnd = "<a href=\"javascript:".$funName."(".$theEndRow.",'".$param1."','".$param2."');\">".$this->config['last']."</a>";
	        }
        }
          // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href=\"javascript:".$funName."(".$page.",'".$param1."','".$param2."')\">&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
   /**
    * 分页输出  
   
    */
   
   public function show2($type=1) {
        if(0 == $this->totalRows) return '';
        $p = $this->type;
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        if($type!=1)
        {
        	$_SERVER['REQUEST_URI']=substr(strrchr($_SERVER['REQUEST_URI'],"/"),1,-4);
        }
       	$url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
		

		$parse = parse_url($url);
		
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
      
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";
        }else{
            $upPage="";
        }
		
        if ($downRow <= $this->totalPages){
            $downPage="<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";
        }else{
            $downPage="";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$url."&".$p."=$preRow' >上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a href='".$url."&".$p."=$nextRow' >下".$this->rollPage."页</a>";
            $theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
        return $pageStr;
    }
    
}



?>