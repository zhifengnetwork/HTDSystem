

$(function(){
    // 用户名
     $(".username").blur(function(){
         let reg = /^[a-zA-Z0-9_-]{4,16}$/; /*用户名*/
         let name = $(this)
         console.log($(this).parent().siblings(".from-group").children(".pptt"))
        if(name.val()==""){
            name.parent().prev().addClass("mistake").html(`用户名不能为空`)
            return false
        }else if(!reg.test(name.val())){
            name.parent().prev().addClass("mistake").html(`用户名格式错误 长度在4-16之间`)
            return false
        }else{
            name.parent().prev().removeClass("mistake").html("") 
            if($(this).parent().siblings(".from-group").children(".pptt").val().length>0 ){
                $(".btn").removeClass("dis")
            }else{
                $(".btn").addClass("dis")
            }
            return 
        }
     })
    //  手机
     $(".phone").blur(function(){
        let reg = /^1[34578]\d{9}$/;  /*用户手机号*/
        let phone = $(this)
        if(phone.val()==""){
            phone.parent().prev().addClass("mistake").html(`手机不能为空`)
            return false
        }else if(!reg.test(phone.val())){
            phone.parent().prev().addClass("mistake").html(`请输入正确的手机号码~`)
            return false
        }else{
            phone.parent().prev().removeClass("mistake").html("") 
            if($(this).parent().siblings(".from-group").children(".pptt").val().length>0 ){
                $(".btn").removeClass("dis")
            }else{
                $(".btn").addClass("dis")
            }
            return 
        }
     })
     // 密码
     $(".password").blur(function(){
        let reg = /^[\w_-]{6,16}$/;  /*用户密码*/
        let password = $(this)
        if(password.val()==""){
            password.parent().prev().addClass("mistake").html(`密码不能为空`)
            return false
        }else if(!reg.test(password.val())){
            password.parent().prev().addClass("mistake").html(`密码格式错误，长度在6-16之间`)
            return false
        }else{
            password.parent().prev().removeClass("mistake").html("") 
            if($(this).parent().siblings(".from-group").children(".pptt").val().length>0  ){
                console.log($(this).parent().siblings(".from-group").children(".pptt").val().length>0 )
                $(".btn").removeClass("dis")
            }else{
                $(".btn").addClass("dis")
            }
            return 
        }
     })
     //邮箱
     $(".email").blur(function(){
        let reg= /^\w+@\w+(\.[a-zA-Z]{2,3}){1,2}$/; /*用户邮箱*/
        let email = $(this)
        if(email.val()==""){
            email.parent().prev().addClass("mistake").html(`邮箱不能为空`)
            return false
        }else if(!reg.test(email.val())){
            email.parent().prev().addClass("mistake").html(`请输入正确的邮箱`)
            return false
        }else{
            email.parent().prev().removeClass("mistake").html("") 
            if($(this).parent().siblings(".from-group").children(".pptt").val().length>0 ){
                $(".btn").removeClass("dis")
            }else{
                $(".btn").addClass("dis")
            }
            return 
        }
     })
     
    $(".icon").click(function(){
        $(this).toggleClass("active")
        if($(this).hasClass("active")){
            $(".btn").removeClass("dis")
            return 
        }else{
            $(".btn").addClass("dis")
            return false
        }
    })
//   发送验证码
    $(".send-code").click(function(){
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
                $(".phone").attr("disabled",false)
            }
    }
    // 随机验证码
    var code ; 
    function createCode(){ 
       code = "";  
       var codeLength = 4;
       var random = new Array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R',  
       'S','T','U','V','W','X','Y','Z');  
       for(var i = 0; i < codeLength; i++) {  
        var index = Math.floor(Math.random()*36);
        code += random[index];
      }  
    return code;
    } 
  
  //注册
  $(".btn").click(function(){
    let userName=$(".username")
    let userPhone=$(".phone")
    let password = $(".password")
    let userEmail = $(".email")
      if(userName.val()==""){
        userName.parent().prev().addClass("mistake").html(`用户名不能为空`);
        userName.focus()
        return false
      }
      if(userPhone.val()==""){
        userPhone.parent().prev().addClass("mistake").html(`手机不能为空`);
        userPhone.focus()
        return false
      }
      if(password.val()==""){
        password.parent().prev().addClass("mistake").html(`密码不能为空`);
        password.focus()
        return false
      }
      if(userEmail.val()==""){
        userEmail.parent().prev().addClass("mistake").html(`邮箱不能为空`);
        userEmail.focus()
        return false
      }
      
  })
  })