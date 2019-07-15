//dadaxia:login:index的JavaScript文件

function init()
{

}
//判断登录是不是为空
function checklogin(){
   var name = document.getElementById("login_name").value;
   var password =document.getElementById("login_passwd").value;
   if(name=="" || password==""){
      alert("用户名或密码不能为空");
	  return false;
   }else{
      return true;
   }
}
//判断注册是不是为空
function checkregister(){
	var username=document.getElementById("username").value;
	var password=document.getElementById("password").value;
	var ctrlpassword=document.getElementById("ctlpassword").value;
	var email=document.getElementById("email").value;
  if(username==""){
      alert("用户名不能为空");
	  return false;
  }else if(password==""){
      alert("密码不能为空");
	  return false;
  }else if(password!=ctrlpassword){
      alert("两次输入密码不同");
	  return false;
  }else if(email==""){
      alert("email不能为空");
	  return false;
  }else{
      return true;
  }

}
//处理复选框
function CheckAll(form) {
	for (var i=0;i<form.elements.length;i++) {
	var e = form.elements[i];
	if (e.name == 'com[]'){
	  e.checked = true;
	  if(e.name != 'chkall')
	    e.checked =form.chkall.checked;
	}
	}
	
}
