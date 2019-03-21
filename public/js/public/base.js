/*返回 按钮*/
function returnFun(){
	/*返回上一页*/
	if($('.header-zp .return').attr('data-num') == 1 || $('.header-zp .return').attr('data-num') == undefined ){
		window.history.back();
	}else {
		/*页面跳转*/
		window.location.href = $('.header-zp .return').attr('data-num');
	}
	return false;
}
/*路径-跳转*/
function jumpFun(_url){
	window.location.href = _url;
}

//实现滚动条无法滚动
var mo = function(e) {
	e.preventDefault();
};

/***禁止滑动***/
function stop() {
	document.body.style.overflow = 'hidden';
	document.addEventListener("touchmove", mo, false); //禁止页面滑动
}

/***取消滑动限制***/
function move() {
	document.body.style.overflow = ''; //出现滚动条
	document.removeEventListener("touchmove", mo, false);
}
