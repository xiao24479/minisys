//处理ajax
var xmlHttp;
//处理现实该角色的功能
function get_role_rights(str){ 
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
	  alert ("您的浏览器不支持AJAX！");
	  return;
	} 
	var url="admin.php?m=rights&a=function_ajax";
	url=url+"&rid="+str;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}
//处理角色的操作
function  save_role_rights(roleid,rights_id,flag){
    xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){
	  alert ("您的浏览器不支持AJAX！");
	  return;
	} 
	var url="admin.php?m=rights&a=save_role_rights";
	url=url+"&rid="+roleid+"&rights_id="+rights_id+"&flag="+flag;
	url=url+"&sid="+Math.random();
	xmlHttp.onreadystatechange=stateChanged;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}
//处理状态
function stateChanged(){ 
	if (xmlHttp.readyState==4)
	{ 
	   document.getElementById("contentsdata").innerHTML=xmlHttp.responseText;
	}
}
//处理浏览器
function GetXmlHttpObject(){
	var xmlHttp=null;
	try{
	  // Firefox, Opera 8.0+, Safari
	  xmlHttp=new XMLHttpRequest();
	}catch (e){
	  // Internet Explorer
	  try{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	  }catch (e){
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	}
	return xmlHttp;
}