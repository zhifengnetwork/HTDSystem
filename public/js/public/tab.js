$(document).ready(function(e) {
    $(".zjy_ul li").click(function(){
		$(".zjy_ul li").eq($(this).index()).addClass("on").siblings().removeClass("on");
		$(".zjy_box_conten .zjy_box_conten_item").eq($(this).index()).addClass("avtive").siblings().removeClass("avtive");
	})
    $(".payment_ul li").click(function(){
    	console.log(111)
		$(".payment_ul li").eq($(this).index()).find("span").addClass("payment_active").parent().parent().siblings().find("span").removeClass("payment_active");
		$(".payment_cont .payment_cont_item").eq($(this).index()).addClass("avtive").siblings().removeClass("avtive");
	})
});