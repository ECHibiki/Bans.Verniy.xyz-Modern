var is_ff = false;
var is_chrome = false;

function detectBrowser(){
	if(navigator.userAgent.indexOf("Firefox") != -1){
		is_ff = true;
	}
	else if (navigator.userAgent.indexOf("Chrome") != -1){
		is_chrome = true;
	}
}

function setNodeHref(node, href_c, href_ff, href_def){
	if(is_ff){
		node.href = href_ff;
	}
	else if(is_chrome){
		node.href = href_c;
	}
	else node.href = href_def;
}