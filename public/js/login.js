$(function() {

	// 验证用户手机格式
	 $(".phone").blur(function(){
		 let reg= /^1[34578]\d{9}$/;  /*用户手机号*/
		 let $phone=$(this)
		 
		   if($phone.val()==""){
		   			 $phone.parent().prev().addClass("mistake").html("手机号不能为空")
		   			 return _state_.push(false);
		   }else if(!reg.test($phone.val())){
		   			 $phone.parent().prev().addClass("mistake").html("请输入正确的手机号~")
		   			 return _state_.push(false);
		   }else{
							$phone.parent().prev().removeClass("mistake").html("")
							if($(this).parent().siblings(".from-group").children(".pptt").val().length>0){
                  $(".btn").addClass("active").removeClass("dis").attr("disabled",false)
							}else{
								  $(".btn").addClass("dis").removeClass("active").attr("disabled","disabled")
							}
		   			 return true;
		   }
	 })
	 // 验证用户密码格式
	 $(".pwd").blur(function(){
		 let reg=/^[a-zA-Z0-9]{6,16}$/;; /*用户密码*/
		 let $pwd=$(this)
		 if($pwd.val()==""){
			 $pwd.parent().prev().addClass("mistake").html("用户密码不能为空")
			 return false;
		 }else if(!reg.test($pwd.val())){
			 $pwd.parent().prev().addClass("mistake").html("用户名密码格式错误 6~16位~")
			 return false;
		 }else{
			 $pwd.parent().prev().removeClass("mistake").html("")
			   if($(this).parent().siblings(".from-group").children(".pptt").val().length>0){
			  	$(".btn").addClass("active").removeClass("dis").attr("disabled",false)
		}else{
			  	$(".btn").addClass("dis").removeClass("active").attr("disabled","disabled")
		}
			 return true;
		 }
	 })
	 
	
	 

})
//  登录
function loginFun(){
	console.log(666)
	window.location.href = '../home/index.html';
}
