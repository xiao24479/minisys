// JavaScript Document
var showmenu=1;
TableChangeColor = function(table, trColor){
	this.table = document.getElementById(table);
	if(this.table){
		this.trColor = trColor;
		this.init();
	}
}
TableChangeColor.prototype.init = function(){
	this.trs = 	this.table.getElementsByTagName('tr');
	var _self = this;
	for(var i=0; i<this.trs.length; i++){
		this.trs[i].onmouseover = function(){
			this.style.backgroundColor = _self.trColor;
		}
		this.trs[i].onmouseout = function(){
			this.style.backgroundColor = "";
		}
	}
}
function mySelectAll(toggle,checkboxes){
	if(toggle.selectOk == null)
		toggle.selectOk = false;
	if(checkboxes.length && checkboxes.length > 0){
	  for(var i=0;i<checkboxes.length;i++){
	  	if(checkboxes[i].checked == toggle.selectOk)
	      checkboxes[i].click();
	  }
	  toggle.selectOk = checkboxes[0].checked;
	}
}
	

function menustylechange(node){
	var body = document.getElementById('menucontents');
	//alert(body.childNodes.length);
//	body.childNodes[0].class=".contents_menu_selected";
	//alert(body.childNodes[0]);
	alert(document.getElementById('Messagemenu').class);//=".contents_menu_selected";
}
function menuclick(){
	var t = document.getElementById("contents_menu");
	
	if(window.showmenu==1){
		t.style.display = 'none';
		window.showmenu=0;
	}else{
		t.style.display = 'block';
		window.showmenu=1;
	}
	
	//t.className='';
	
	//alert(t.id);
	
}
