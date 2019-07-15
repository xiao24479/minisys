// JavaScript Document

// trim
String.prototype.trim= function(){
    return this.replace(/(^\s*)|(\s*$)/g, "");  
}

// 缩写 document.getElementById
function D(input_name)
{
    return document.getElementById(input_name);
}

