/**底部nav切换(未-点击)_按循序
 *  *bottomTabArrOne.length来循环
 * **/
var bottomTabArrOne = [
	"img/bottomNav/20181119_11.png",
	"img/bottomNav/20181119_21.png",
	"img/bottomNav/20181119_31.png",
	"img/bottomNav/20181119_41.png",
];
/*底部nav切换(已-点击)_按循序*/
var bottomTabArrTwo = [
	"img/bottomNav/20181119_12.png",
	"img/bottomNav/20181119_22.png",
	"img/bottomNav/20181119_32.png",
	"img/bottomNav/20181119_42.png",
];
/*底部nav-点击url路径_按循序*/
var buttonNavUrl = [
	"'./App底部导航.html'",
	"'./2.html'",
	"''",
	"''",
];
/*底部nav-title_按循序*/
var buttonNavTitle = [
	"首页",
	"积分商城",
	"兑换商城",
	"我的"
];

/*js动态创建-底部导航栏*/
var botNavdStr = '';
for(var g = 0; g < bottomTabArrOne.length; g++) {
	/*<!--项，
		1、点击时 font-color：class="bNavYseFontColor";
		2、（未点击时）字体颜色:class="bNavNoFontColor";
	-->*/
	botNavdStr += '<div class="bottomNavTerm">';
	/*<!--icon box-->*/
	botNavdStr += '<p class="bottomNavIconBox" onclick="window.location.href=' + buttonNavUrl[g] + '">';
		botNavdStr += '<img class="bottomNavIcon" src="' + bottomTabArrOne[g] + '" />';
	botNavdStr += '</p>';
	/*<!--title
		1、（点击时）字体颜色:class="bNavYseFontColor";
	-->*/
	botNavdStr += '<p class="bottomNavTitle bNavNoFontColor">' + buttonNavTitle[g] + '</p>';
	botNavdStr += '</div>';
}

/*生产=> 底部导航栏*/
$('#bottomNavWrap').html(botNavdStr);

/*底部导航栏=> 切换*/
var thisInd = Number($.trim($('.pageTopTitle').attr('page-id')));
/*当前=> 替换*/
/*icon*/
$('#bottomNavWrap .bottomNavTerm').eq(thisInd).find('.bottomNavIcon').attr('src', bottomTabArrTwo[thisInd]);
/*title的color*/
$('#bottomNavWrap .bottomNavTerm').eq(thisInd).find('.bottomNavTitle').addClass('bNavYseFontColor').removeClass('bNavNoFontColor');

$(document).ready(function() {
	
	/**获取焦点
	 * 手机端=>input=>点击隐藏=>底部导航栏
	 * **/
	$('input').focus(function() {
		/*底部导航栏*/
		$('.bottomNavWrap').hide();
	})
	/*失去焦点*/
	$('input').blur(function() {
		/*底部导航栏*/
		$('.bottomNavWrap').show();
	})
})