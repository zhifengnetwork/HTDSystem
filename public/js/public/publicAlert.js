/*动态创建对话框*/
/**第一参数:把动态标签放在那里。
 * 第二参数:提示文本。
 * 第三参数:是否跳转，跳转的路径(可选)
 * **/
function suredAlert(_butShow,_text,_url) {
	/*如果未传，默认空*/
	var pageUrl = _url || "";
	/*创建标签*/
	var str = '';
	str += '<div class="maskWrap">';
		str += '<div class="signOutBox">';
			str += '<div class="signOutBox-text paddingOneRem">';
				str += '<p class="alertBoxTipsText fontSizieThree">' + _text + '</p>';
			//	str +='<p class="numericalValue">'+_number+'</p>';
			str += '</div>';
			str += '<div class="signOutBox-button">';
				str += '<p class="signOut-confirm sure-middle" onclick="thisButD(this)" data-url="'+ pageUrl +'">确认</p>';
			str += '</div>';
		str += '</div>';
	str += '</div>';
	
	/*放在最外边框的第一位*/
	_butShow.prepend(str);
	/*获取(手机)遮罩层的height*/
	$('.maskWrap').css({
		'height': $(window).height(),
	});
	
	return false;
}
/**
 * 弹框=>确认按钮
 * **/
function thisButD(_this,_url) {
	console.log('点击确认!');
	/*保存this*/
	var _that = $(_this);
	console.log('跳转的路径:',_that.attr('data-url'));
	if(_that.attr('data-url') != ""){
		console.log(66666666)
		window.location.href = _that.attr('data-url');
	}
	/*删除 => 弹框*/
	$('.maskWrap').remove();
	
	return false;
}
