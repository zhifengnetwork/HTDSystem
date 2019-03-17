$(function(){
    //手机框
    $(".phone").blur(function(){
        let pwd1 = $(this)
        let reg = /^1[34578]\d{9}$/;  /*用户手机号*/
        if(pwd1.val()==""){
            pwd1.parent().prev().addClass("mistake").html(`手机不能为空`)
            return false
        }else if(!reg.test(pwd1.val())){
            pwd1.parent().prev().addClass("mistake").html(`请输入正确的手机号~`)
            return false
        }else{
         pwd1.parent().prev().removeClass("mistake").html("")
        }
     })
    // 密码框1
    $(".pwd1").blur(function(){
       let pwd1 = $(this)
       let password = /^[\w_-]{6,16}$/;
       if(pwd1.val()==""){
           pwd1.parent().prev().addClass("mistake").html(`密码不能为空`)
           return false
       }else if(!password.test(pwd1.val())){
           pwd1.parent().prev().addClass("mistake").html(`密码格式错误`)
           return false
       }else{
        pwd1.parent().prev().removeClass("mistake").html("")
       }
    })
// 密码框2
    $(".pwd2").blur(function(){
        let pwd1 = $(this)
        let password = /^[\w_-]{6,16}$/;
        if(pwd1.val()==""){
            pwd1.parent().prev().addClass("mistake").html(`密码不能为空`)
            return false
        }else if(!password.test(pwd1.val())){
            pwd1.parent().prev().addClass("mistake").html(`密码格式错误`)
            return false
        }else{
            pwd1.parent().prev().removeClass("mistake").html("")
        }
     })

   //验证密码是否一致
      $(".pwd2").blur(function(){
          var pwd1 = $(".pwd1").val()
          var pwd2 = $(".pwd2").val()
          if(pwd1!=pwd2){
            $(".pwd1").parent().prev().addClass("mistake").html(`两次输入的密码不一致`)
          return false
          }else{
            $(".pwd1").parent().prev().addClass("mistake").html("")
          }
      })
  
   // 获取验证码
   $(".acquire").click(function(){
        let reg = /^1[34578]\d{9}$/;  /*用户手机号*/
        let phone = $(".phone")
        if(phone.val()==""){
            phone.parent().prev().addClass("mistake").html(`手机不能为空`)
            return false
        }else if(!reg.test(phone.val())){
            phone.parent().prev().addClass("mistake").html(`请输入正确的手机号码~`)
            return false
        }else
            daojishi(60,$(this))
            $(".phone").attr("disabled","disabled") 
            phone.parent().prev().addClass("mistake").html("")
            return   
         
   })
   function daojishi(seconds,obj){
       if (seconds > 1){
               seconds--;
               $(obj).html(seconds+"秒后重新获取 ").attr("disabled", true);
               setTimeout(function(){
                   daojishi(seconds,obj);
               },1000);
           }else{
               $(obj).html("获取验证码").attr("disabled", false);//启用按
           }
   }
 })