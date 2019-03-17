$(function(){
	
	 $('#tit span').click(function() {
            var i = $(this).index();//下标第一种写法
            //var i = $('tit').index(this);//下标第二种写法
            $(this).addClass('select').siblings().removeClass('select');
            $(this).addClass('dianji').siblings().removeClass('dianji');
            $('#con li').eq(i).show().siblings().hide();
        });
		
	
})
