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

//阻止默认事件，禁止滑动
function stop(name) {
	var $name = $(name)
	$name.bind("touchmove","touchstart",function(e){
		e.preventDefault();
	})
}
