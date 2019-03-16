$(document).ready(function(e) {
    $(".zjy_ul li").click(function(){
		$(".zjy_ul li").eq($(this).index()).addClass("on").siblings().removeClass("on");
		$(".zjy_box_conten .zjy_box_conten_item").eq($(this).index()).addClass("avtive").siblings().removeClass("avtive");
	})
});