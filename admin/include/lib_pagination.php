<?php
/**
 * the frame core file.
 * Jeanlius 2009-12-20.
 * liujianhe99@gmail.com
 */
//////////////////////////////////////////////////////////////////////////
//分页处理
//需要显示的页面数据，必须设置该数据
#$pagenavigate_submiturl='forum_comment.php';
// 必须设置查询语句,包括排序功能。
#$pagenavigate_sql='select * from vu_forum_comment where forum_id='.$forumid.' order by comment_id desc ';
#$pagenavigate_para=''; 该参数为页面上需要传递的参数，如查询某个商品的类别id,该参数会继续带到下面的页面中，或者用户可以把这些参数直接放在pagenavigate_submiturl变量中。

require_once('include/lib_file.php');

$pagenavigate_para  = isset($pagenavigate_para) ? ('&&'.$pagenavigate_para) : '';

//另外分页会直接返回你要查询的数据集给你变量名称为：#pagenavegation_rs
//页面导航会直接设置smarty的变量 pagenavegation ，所以请直接在页面上使用这个参数就可以。
if(!isset($pagenavigate_submiturl)) {
	echo '编译错误：你引用了分页导航文件，当没有设置变量：pagenavigate_submiturl，请设置';
	exit;
}
if(!isset($pagenavigate_sql)){
	echo '编译错误：你引用了分页导航文件，当没有设置变量：pagenavigate_sql，请设置';
	exit;
}

$pagenavigate_count_sql=isset($pagenavigate_count_sql)?($pagenavigate_count_sql) : false;

//设置每页显示的行数，默认为15条数据，可以不设置
#$rowsPerPage=3;
$rowsPerPage  = isset($rowsPerPage) ? $rowsPerPage : 10;
//页面导航上的页码显示数据，假设当前第10页，该数据默认为6
//如该数为2时显示如下：......8  9  10  11  12 ......
//该数为3时显示：...... 7 8 9 10 11 12 13 ......
#$pagenoshow=10;
$pagenoshow  = isset($pagenoshow) ? $pagenoshow : 4;
//下面为分页处理

//generate the counter sql.
$sqlcount="";

if($pagenavigate_count_sql)
	$sqlcount=$pagenavigate_count_sql;
else
	$sqlcount='select count(*) from ('.$pagenavigate_sql.') As T';



//to page no


$toPageNo =isset($toPageNo)?$toPageNo:(isset($_REQUEST['tpg']) ? intval($_REQUEST['tpg']) : 1);

//logfile('pagenavigate','to page no=========>'.$toPageNo.'====');

//echo $sqlcount;
//查询中的页数。
$rownumrow = $GLOBALS['_DB']->get_col($sqlcount);
$numrows=$rownumrow[0];

//echo 'pagecont:'.$numrows.'<br>';


//计算总页数
$pages=intval($numrows/$rowsPerPage);

$pages=($numrows % $rowsPerPage==0)?$pages:($pages+1); 
if($toPageNo>$pages) $toPageNo=$pages;

//echo 'pages:'.$pages.'<br>';


logfile('pagenavigate','pagesize:=========>'.$pagesize.'==end of pagesize==');
//记录游标起点
$offset=$rowsPerPage*($toPageNo - 1);
$offset=($offset<0)?0:$offset;
//echo 'offset:'.$offset.'<br>';
//查询记录
$pagenavegation_rs=$GLOBALS['_DB']->get_all($pagenavigate_sql.' limit '.$offset.','.$rowsPerPage);
logfile('pagenavigate','the sqlis:'.$pagenavigate_sql.' limit '.$offset.','.$rowsPerPage);

//生成导航
//导航样子： 总共100条数据  共20页 当前第10页  第一页  上一页    .....  5  6  7  8  9  10  11 12 13 14 15.....   下一页  最后一页
$totalTag='共<span style=\'color:orange\'>'.$numrows.'</span>条记录';
$pagesTag='分<span style=\'color:orange\'>'.$pages.'</span>页';
$currentpagesTag='当前第'.$toPageNo.'页';
$firstTag='';	
$firstTag= ($toPageNo<=1) ? '第一页' : '<a href=\''.$submiturl.'?tpg=1'.$pagenavigate_para.'\'>第一页</a>';
$preTag= ($toPageNo<=1) ? '上一页' : '<a href=\''.$submiturl.'?tpg='.($toPageNo-1).$pagenavigate_para.'\'>上一页</a>';

$nestTag= ($toPageNo==$pages) ? '下一页' : '<a href=\''.$submiturl.'?tpg='.($toPageNo+1).$pagenavigate_para.'\'>下一页</a>';
$lastTag= ($toPageNo==$pages) ? '最后一页' : '<a href=\''.$submiturl.'?tpg='.$pages.$pagenavigate_para.'\'>最后一页</a>';

//游标上的当前页码，需要把颜色修改一下。
$currentpageTag=$toPageNo;

//设置当前页码前的游标
$prePagenoTag='';
$pagnotemp=0;
for($i=1;$i<$pagenoshow;$i++){
    $pagnotemp=$toPageNo-$i;
	
	//echo '前:'.$pagnotemp.'<br>';
	if($pagnotemp>0){
		$prePagenoTag='&nbsp;&nbsp;'.'<a href=\''.$submiturl.'?tpg='.($toPageNo-$i).$pagenavigate_para.'\'>'.($toPageNo-$i).'</a>'.$prePagenoTag;
		}
	else break;
}
if( $pagnotemp>1) $prePagenoTag='......'.$prePagenoTag;
//设置当前页码后的游标
$nextPagenoTag='';
$pagnotemp=0;
for($i=1;$i<$pagenoshow;$i++){
    $pagnotemp=$toPageNo+$i;
	if($pagnotemp<=$pages)
		$nextPagenoTag=$nextPagenoTag.'&nbsp;&nbsp;'.'<a href=\''.$submiturl.'?tpg='.($toPageNo+$i).$pagenavigate_para.'\'>'.($toPageNo+$i).'</a>';
	else break;
}
if( $pagnotemp<$pages) $nextPagenoTag.='......';

$pagenavegate='<div style="text-align:center; margin:18px 0 28px 0; font-size:12px;" >'.$totalTag.'&nbsp;&nbsp;'.$pagesTag.'&nbsp;&nbsp;'.$currentpagesTag.'&nbsp;&nbsp;'.$firstTag.'&nbsp;&nbsp;'.$preTag.'&nbsp;&nbsp;'.'<span class=\'link-pagination\'>'.$prePagenoTag.'&nbsp;&nbsp;'.$currentpageTag.$nextPagenoTag.'</span>&nbsp;&nbsp;'.$nestTag.'&nbsp;&nbsp;'.$lastTag.'&nbsp;&nbsp;'.'</div>';

#echo $pagenavegate;

	$_TEMPLATE->assign('pagination',$pagenavegate);
	$_TEMPLATE->assign('pagination_rs',$pagenavegation_rs);
	$_TEMPLATE->assign('pageno',$toPageNo);
?>