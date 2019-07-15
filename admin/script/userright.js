//admin:userright的JavaScript文件

// 添加新用户
function add_user()
{
	$.ajax({
		url:"main.php",
		type:"get",
		dataType:"json",
		data:{
			m:"userright",
			a:"ajax_add_user",
			j:1,
			tt:Math.random()
		},
		success:function(resp){
			$("#contents").html(resp.content);
		}
	});
}

function add_user_action()
{
	var uname = $("#u_name").val();
	var uemail = $("#u_email").val();
	var ulogin= $("#u_login").val();
	var upwd = $("#u_pwd").val();
	var upwd2 = $("#u_pwd2").val();
	var user_ip = $("#ip").val();

	if(uname.trim()==''){
		alert('用户名称不能为空！');
		$("#u_name").focus();
	} else if(ulogin.trim()==''){
		alert('登录名不能为空！');
		$("#u_login").focus();
	} else if(upwd.trim()==''){
		alert('密码不能为空！');
		$("#u_pwd").focus();
	} else if(upwd.trim()!=upwd2.trim()){
		alert('先后输入的两个密码不一致，请确认！');
		$("#u_pwd2").focus();
	} else {
		$.ajax({
			url:"main.php",
			type:"POST",
			dataType:"json",
			data:{
				m:"userright",
				a:"ajax_add_user_action",
				user_name:uname,
				pwd:upwd,
				login_name:ulogin,
				user_email:uemail,
				user_ip:user_ip,
				j:1,
				tt:Math.random()
			},
			success:function(resp){
				if(resp.result==0){
					alert('该登录帐号已经存在，请重试！');
					return false;
				}else{
					alert('成功保存！');
					show_user();
				}
			}
		});
	}
}

function show_user()
{
	$.ajax({
		url:"main.php",
		type:"get",
		dataType:"json",
		data:{
			m:"userright",
			a:"ajax_show_user",
			j:1,
			tt:Math.random()
		},
		success:function(resp){
			$("#alluser").html(resp.content);
		}
	});
}

function delete_user(id)
{
	if(!window.confirm("确定要删除吗?")) return false;
	if(id<1) return false;
	$.ajax({
		url:"main.php",
		type:"get",
		dataType:"json",
		data:{
			m:"userright",
			a:"ajax_delete_user",
			id:id,
			j:1,
			tt:Math.random()
		},
		success:function(resp){
			if(resp.result==0) {
				alert("该用户不存在，请重试！");
				return false;
			} else if(resp.result==-1) {
				alert("该用户有下级，请先删除其下级后在操作！");
				return false;
			} else if(resp.result==1) {
				alert("删除成功！");
				$("#hid_"+id).hide();
			}
		}
	});
}

function get_user_rs(id) {
	if(id<1) return false;
	$.ajax({
		url:"main.php",
		type:"get",
		dataType:"json",
		data:{
			m:"userright",
			a:"ajax_get_user_rs",
			id:id,
			j:1,
			tt:Math.random()
		},
		success:function(resp){
			$("#contents").html(resp.content);
		}
	});
}

function update_user(id) {
	if(id<1) return false;
	var user_name = $("#user_name").val();
	var email = $("#email").val();
	var ip = $("#ip").val();

	$.ajax({
		url:"main.php",
		type:"get",
		dataType:"json",
		data:{
			m:"userright",
			a:"ajax_update_user_action",
			id:id,
			user_name:user_name,
			email:email,
			ip:ip,
			j:1,
			tt:Math.random()
		},
		success:function(resp){
			if(resp.result==0) {
				alert("该用户不存在，请重试！");
				return false;
			} else {
				alert("更新成功！");
				show_user();
			}
		}
	});
}

function update_user_password(id) {
	if(id<1) return false;
	var newpwd = $("#newpwd").val();
	var newpwd2 = $("#newpwd2").val();
	if(newpwd=="") {
		alert("密码不能为空!");
		return false;
	}
	if(newpwd!=newpwd2) {
		alert("两次密码输入不同，请重试!");
		return false;
	}

	$.ajax({
		url:"main.php",
		type:"get",
		dataType:"json",
		data:{
			m:"userright",
			a:"ajax_update_user_password",
			id:id,
			newpwd:newpwd,
			j:1,
			tt:Math.random()
		},
		success:function(resp){
			if(resp.result==0) {
				alert("该用户不存在，请重试！");
				return false;
			} else {
				alert("更新成功！");
			}
		}
	});
}


function add_select(formselect,toselect) {
	var allr = document.getElementById(formselect);
	var m=allr.options[allr.selectedIndex].value;
	var n=allr.options[allr.selectedIndex].text;
	add_select_action(m,n,toselect);
	allr.removeChild(allr.options[allr.selectedIndex]);
}

function add_select_action(id,name,toselect){
	var userr = document.getElementById(toselect);
	userr.options.add(new Option(name,id));
}

// 保存用户角色
function save_user_role(id)
{
	var temp = document.getElementById('user_role_id');
	ids = '';
	for(i=0;i<temp.options.length;i++ ){
		ids+=' '+temp.options[i].value;
	}

	$.ajax({
	    url:"main.php",
	    type:"get",
	    dataType:"json",
	    data:{m:"userright",a:"save_user_role_data",id:id,ids:ids,j:1,tt:Math.random()},
	    success:function(resp){
			if(resp.result==1){
				alert('保存成功！');
				return false;
			}
	    }
	});
}


function save_user_hospital(id)
{
	var temp = document.getElementById('user_hospital_id');
	ids = '';
	for(i=0;i<temp.options.length;i++ ){
		ids+=' '+temp.options[i].value;
	}

	$.ajax({
	    url:"main.php",
	    type:"get",
	    dataType:"json",
	    data:{m:"userright",a:"save_user_hospital_data",id:id,ids:ids,j:1,tt:Math.random()},
	    success:function(resp){
			if(resp.result==1){
				alert('保存成功！');
				return false;
			}
	    }
	});
}


function show_hidden(flag,id)
{
	if(document.getElementById("div_"+flag+"_"+id).style.display=="none") {
		$("#div_"+flag+"_"+id).show("slow");
		$("#img_"+flag+"_"+id).attr("src","/_ext/img/jian.png");
	} else {
		$("#div_"+flag+"_"+id).hide("slow");
		$("#img_"+flag+"_"+id).attr("src","/_ext/img/jia.png");
	}
}

function update_user_status(flag,id,is_delete) {
	is_delete = is_delete == 0 ? 1 : 0;
	if(is_delete==1) {
		$("#status_"+flag+"_"+id).html("&nbsp;禁止");
	} else {
		$("#status_"+flag+"_"+id).html("&nbsp;正常");
	}
	var page = $("#hidden_page").val();

	$.ajax({
	    url:"main.php",
	    type:"get",
	    dataType:"json",
	    data:{m:"userright",a:"ajax_update_user_status",id:id,is_delete:is_delete,j:1,tt:Math.random()},
	    success:function(resp){
			if(resp.result==1){
				searchListUserRight(page);
			}
	    }
	});

}

function searchListUserRight(page){ 
	var roleId = $("#roleId").val();
	var hospitalId = $("#hospitalId").val();
	var userName = $("#userName").val();
	if(isNaN(page)) page = 0;
	$.ajax({
	    url:"main.php",
	    type:"get",
	    dataType:"json",
	    data:{m:"userright",a:"ajaxSearchListUserRight",p:page,roleId:roleId,hospitalId:hospitalId,userName:userName,j:1,tt:Math.random()},
	    success:function(resp){
			$("#alluser").html(resp.content);
	    }
	});
}


function get_user_all(page){
	$.ajax({
	    url:"main.php",
	    type:"get",
	    dataType:"json",
	    data:{m:"userright",a:"ajax_show_user",p:page,j:1,tt:Math.random()},
	    success:function(resp){
	      $("#alluser").html(resp.content);
	    }
	});
}

//'n',{$n.id},{$hospitalId},{$n.is_user_hospital_delete}
function update_user_hospital_status(flag,user_id,hospital_id,is_delete) { 
	
	if(is_delete==0) { 
		is_delete=1;
	} else if(is_delete==1) { 
		is_delete=2;
	} else if(is_delete==2) { 
		is_delete=0;
	}
	
	if(is_delete==1) {
		$("#user_hospital_status_"+flag+"_"+user_id).html("&nbsp;无效");
	} else if(is_delete==0) {
		$("#hospital_user_status_"+flag+"_"+user_id).html("&nbsp;有效");
	} else if(is_delete==2) {
		$("#hospital_user_status_"+flag+"_"+user_id).html("&nbsp;非咨询");
	}
	
	var page = $("#hidden_page").val();

	$.ajax({
	    url:"main.php",
	    type:"get",
	    dataType:"json",
	    data:{m:"userright",a:"ajax_update_user_hospital_status",user_id:user_id,hospital_id:hospital_id,is_delete:is_delete,j:1,tt:Math.random()},
	    success:function(resp){
			if(resp.result==1){
				searchListUserRight(page);
			}
	    }
	});
}