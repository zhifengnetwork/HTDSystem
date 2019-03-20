
$(function(){
    //状态
    var condition={
        phonestate:false,    //用户名手机
        pwdstate:false,      //用户密码
        securitycode:false,  //验证码
        inconsistent:false   //验证密码
    }
    //手机框
    $(".phone").blur(function(){
        let pwd1 = $(this)
        let reg = /^1[34578]\d{9}$/;  /*用户手机号*/
        if(pwd1.val()==""){
            pwd1.parent().parent().prev().addClass("mistake").html(`手机不能为空`)
            return condition.phonestate=false
        }else if(!reg.test(pwd1.val())){
            pwd1.parent().parent().prev().addClass("mistake").html(`请输入正确的手机号~`)
            return condition.phonestate=false
        }else{
           pwd1.parent().parent().prev().removeClass("mistake").html("")
           return condition.phonestate=true
        }
     })
     //验证码
   $(".verify").blur(function(){
        var code1=$(this)
        if(code1.val()==""){
            code1.parent().parent().prev().addClass("mistake").html(`验证码不能为空`)
            return condition.securitycode=false
        }else if(code1.val()!=code){
            code1.parent().parent().prev().addClass("mistake").html(`请输入正确的验证码`)
            return condition.securitycode=false
        }else{
            code1.parent().parent().prev().removeClass("mistake").html("")
      return condition.securitycode=true
        }
    })
    // 密码框1
    $(".pwd1").blur(function(){
       let pwd1 = $(this)
       var pwd2 = $(".pwd2")
       let password = /^[\w_-]{6,16}$/;
       if(pwd1.val()==""){
           pwd1.parent().parent().prev().addClass("mistake").html(`密码不能为空`)
           return condition.pwdstate=false
       }else if(!password.test(pwd1.val())){
           pwd1.parent().parent().prev().addClass("mistake").html(`密码格式错误`)
           return condition.pwdstate=false
       }else {
        pwd1.parent().parent().prev().removeClass("mistake").html("")
        if(pwd1.val() == pwd2.val()){
           pwd2.parent().parent().prev().addClass("mistake").html(``)  
        }else{
           pwd2.parent().parent().prev().addClass("mistake").html(`两次输入的密码不一致`)  
           $(".pwd2").blur()
        }
           return condition.pwdstate=true
    } 
    })


   //验证密码是否一致
      $(".pwd2").blur(function(){
          var pwd1 = $(".pwd1").val()
          var pwd2 = $(".pwd2").val()
          if(pwd1!=pwd2){
            $(".pwd2").parent().parent().prev().addClass("mistake").html(`两次输入的密码不一致`)
             return condition.inconsistent=false
          }else{
            $(".pwd2").parent().parent().prev().addClass("mistake").html("")
             return condition.inconsistent=true
          }
      })

  
   // 获取验证码
   $(".acquire").click(function(){
        let reg = /^1[34578]\d{9}$/;  /*用户手机号*/
        let phone = $(".phone")
        if(phone.val()==""){
            phone.parent().parent().prev().addClass("mistake").html(`手机不能为空`)
            return condition.securitycode=false
        }else if(!reg.test(phone.val())){
            phone.parent().parent().prev().addClass("mistake").html(`请输入正确的手机号码~`)
            return condition.securitycode=false
        }else
            createCode()
           $(".verify").focus();
            console.log(code)
            daojishi(60,$(this))
            $(".phone").attr("disabled","disabled") 
            phone.parent().parent().prev().addClass("mistake").html("")
            return condition.securitycode=true   
   })
   var code 
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
      code = code.replace(/[^a-z\d]/ig,"")
    return code;
     } 

 //输入框有值 botton切换样式
 $(".pptt").keyup(function(){
    var input = [];
    $(".pptt[value]").each(function(i,item){
        if(item.value!=""){
            input.push(item)
        }
})	
  if(input.length==$(".from-icon>input").length){
        $(".btn").removeClass("dis").addClass("active").attr("disabled",false)
    }else{
        $(".btn").addClass("dis").removeClass("active").attr("disabled",true)  
    }
})
    //找回密码
  $(".btn").click(function(){
    let userPhone = $(".phone") //手机
    let pwd1 = $(".pwd1")       //密码1
    let pwd2 = $(".pwd2")       //密码2
    let acquire = $(".acquire") //验证码
    let body = $("body")     
    if(condition.phonestate&&condition.pwdstate&&condition.securitycode&&condition.inconsistent){
        suredAlert(body,"修改成功~");
            return
    }else{
        
       if(!condition.phonestate){  //手机号码
        suredAlert(body,"请输入正确的手机号~");
        userPhone.focus()
        return
      } 
      if(!condition.securitycode){  //验证码
        suredAlert(body,"请输入正确的验证码~");
        acquire.focus()
        return  
      }
      if(!condition.pwdstate){     //密码
        suredAlert(body,"请输入正确的密码~");
        pwd1.focus()
        return
      }
      if(pwd1.val() != pwd2.val()){ //二次密码
        suredAlert(body,"输入的密码不一致~");
        pwd2.focus()
        return  
      }
    }
  })
  
 })