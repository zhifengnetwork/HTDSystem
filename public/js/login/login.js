
	
	var condition={
		phonestate:false,    //用户名手机
		pwdstate:false,      //用户密码
		verifystate:false    //验证码
	}

	// 验证用户
	 $(".username").blur(function(){
		let reg = /^[a-zA-Z0-9_-]{4,16}$/; /*用户名*/
		 let $phone=$(this)
		   if($phone.val()==""){
		   			 $phone.parent().parent().prev().addClass("mistake").html("*用户名不能为空")
		   			 return condition.phonestate=false
		   }else if(!reg.test($phone.val())){
		   			 $phone.parent().parent().prev().addClass("mistake").html("*请输入正确的用户名~")
		   			 return condition.phonestate=false
		   }else{
							$phone.parent().parent().prev().removeClass("mistake").html("")
				
							return condition.phonestate=true
		   }
	 })
	 // 验证用户密码格式
	 $(".pwd").blur(function(){
		 let reg=/^[a-zA-Z0-9]{6,16}$/;; /*用户密码*/
		 let $pwd=$(this)
		 if($pwd.val()==""){
			 $pwd.parent().parent().prev().addClass("mistake").html("*用户密码不能为空")
			 return condition.pwdstate=false
		 }else if(!reg.test($pwd.val())){
			 $pwd.parent().parent().prev().addClass("mistake").html("*用户名密码格式错误 6~16位~")
			 return condition.pwdstate=false
		 }else{
			 $pwd.parent().parent().prev().removeClass("mistake").html("")
			
		return condition.pwdstate=true
		 }
	 })
	 
	//  验证码
	$(".verify").blur(function(){
		let $verify=$(this)
		if($verify.val()==""){
			$verify.parent().parent().prev().addClass("mistake").html("*请输入验证码")
			return condition.verifystate=false
		}else{
			$verify.parent().parent().prev().removeClass("mistake").html("")
	    return condition.verifystate=true
		}
	})
	 // 返回上一页
	     function returnFun(){
	 	/*返回上一页*/
	 	if($('.headWrap_lb .returnBut_lb').attr('data-num') == 1 || $('.headWrap_lb .returnBut_lb').attr('data-num') == undefined ){
	 		window.history.back();
	 		console.log("返回上一页");
	 	}else {
	 		/*页面跳转*/
	 		window.location.href = $('.headWrap_lb .returnBut_lb').attr('data-num');
	 	}
	 	return false;
	 }

// 	 //输入框有值 botton切换样式
// 	 $(".pptt").keyup(function(){
// 		var input = [];
// 		$(".pptt[value]").each(function(i,item){
// 			if(item.value!=""){
// 				input.push(item)
// 			}
// 	})	
// 	  if(input.length==$(".from-icon>input").length){
// 			$(".btn").removeClass("dis").addClass("active").attr("disabled",false)
// 		}
// })

