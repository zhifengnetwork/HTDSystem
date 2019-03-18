

$(function(){
    // 状态
        var condition={
            username:false,       //用户名
            phonestate:false,     //用户名手机
            pwdstate:false,       //用户密码
            inconsistent:false,   //验证密码
            emailstate:false,     //用户邮箱
            iconstate:false,      //用户是否勾选
            securitycode:false,   //验证码
            referrer:false        //推荐人
        } 
        
        $(".username").blur(statusname)       //用户名
        $(".phone").blur(statusphone)         //手机
        $(".password").blur(statuspwd)        //密码
        $(".affirmpwd").blur(affirmpwd)       //确认密码
        $(".email").blur(statusemail)         //邮箱
        $(".verify").blur(statusverify)       //验证码
        $(".icon").click(statusicon)          //勾选
        $(".recommend").blur(statusreferrer)  //推荐人 
    
        //用户名
         function  statusname(){
             var reg = /^[a-zA-Z0-9_-]{4,16}$/; /*用户名*/
             var name = $(this)
            if(name.val()==""){  //用户名输入为空
                name.parent().parent().prev().addClass("mistake").html(`用户名不能为空`)
                return condition.username=false
            }else if(!reg.test(name.val())){ //用户名验证失败
                name.parent().parent().prev().addClass("mistake").html(`用户名格式错误 长度在4-16之间`)
                return condition.username=false
            }else{
                name.parent().parent().prev().removeClass("mistake").html("") 
                return condition.username=true;
                // if(condition.username&&condition.phonestate&&condition.pwdstate&&condition.emailstate&&condition.iconstate&&condition.securitycode){
                //     $(".btn").removeClass("dis").addClass("active").attr("disabled",false)
                //    }else{
                //     $(".btn").addClass("dis").removeClass("active").attr("disabled",true)  
                // }   
            }
         }
         
        //  手机
         function statusphone(){
            var reg = /^1[34578]\d{9}$/;  /*用户手机号*/
            var phone = $(this)
            if(phone.val()==""){
                phone.parent().parent().prev().addClass("mistake").html(`手机不能为空`)
                return condition.phonestate=false
            }else if(!reg.test(phone.val())){
                phone.parent().parent().prev().addClass("mistake").html(`请输入正确的手机号码~`)
                return condition.phonestate=false
            }else{
                phone.parent().parent().prev().removeClass("mistake").html("") 
                return condition.phonestate=true
            }
         }
    
        // 密码
        function statuspwd(){
            var reg = /^[\w_-]{6,16}$/;  /*用户密码*/
            var password = $(this)
            var pwd2 = $(".affirmpwd")
            if(password.val()==""){
                password.parent().parent().prev().addClass("mistake").html(`密码不能为空`)
                return condition.pwdstate=false
            }else if(!reg.test(password.val())){
                password.parent().parent().prev().addClass("mistake").html(`密码格式错误，长度在6-16之间`)
                return condition.pwdstate=false
            }else{
                password.parent().parent().prev().removeClass("mistake").html("") 
                if(pwd2.val() != ""){
                    $(".affirmpwd").blur()  
                }
                return condition.pwdstate=true
            }
         }
    
        //确认密码
        function affirmpwd(){
             var $pwd =  $(".password").val() 
             var $pwd2 = $(this)
             if($pwd2.val() == ""){
                $pwd2.parent().parent().prev().addClass("mistake").html(`*两次输入的密码不一致`)
                return condition.inconsistent=false
             }else if($pwd2.val()!=$pwd){
                $pwd2.parent().parent().prev().addClass("mistake").html(`*输入密码不一致`)
                return condition.inconsistent=false
             }else{
                $pwd2.parent().parent().prev().removeClass("mistake").html("") 
                return condition.inconsistent=true
             }
        }
    
        //邮箱
        function statusemail(){
            var reg= /^\w+@\w+(\.[a-zA-Z]{2,3}){1,2}$/; /*用户邮箱*/
            var email = $(this)
            if(email.val()==""){
                email.parent().parent().prev().addClass("mistake").html(`邮箱不能为空`)
                return condition.emailstate=false
            }else if(!reg.test(email.val())){
                email.parent().parent().prev().addClass("mistake").html(`请输入正确的邮箱`)
                return condition.emailstate=false
            }else{
                email.parent().parent().prev().removeClass("mistake").html("")
                return condition.emailstate=true
            }
         }
    
        //用户是否同意政策
        function statusicon(){
            $(this).toggleClass("active")
            if($(this).hasClass("active")){
                condition.iconstate=true
                $(".btn").addClass("active").removeClass("dis").attr("disabled",false)
            }else{
                $(".btn").addClass("dis").removeClass("active").attr("disabled",true)  
                return condition.iconstate=false
            }
        }
    
        //验证码
        function statusverify(){
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
        }
    
        //推荐人
        function statusreferrer(){
            var $referrer = $(this)
            if($referrer.val()==""){
                $referrer.parent().parent().prev().addClass("mistake").html(`推荐码不能为空`)
                return condition.referrer=false
            }else{
                $referrer.parent().parent().prev().removeClass("mistake").html("")
                return condition.referrer=true
            }
        }
        
        //发送验证码
        $(".send-code").click(function(){
            var reg = /^1[34578]\d{9}$/;  /*用户手机号*/
            var phone = $(".phone")
            if(phone.val()==""){
                phone.parent().parent().prev().addClass("mistake").html(`手机不能为空`)
                return condition.phonestate=false
            }else if(!reg.test(phone.val())){
                phone.parent().parent().prev().addClass("mistake").html(`请输入正确的手机号码~`)
                return condition.phonestate=false
            }else
               createCode()
               $(".verify").focus();
               console.log(code)
                daojishi(60,$(this))
                $(".phone").attr("disabled","disabled") 
                return condition.phonestate=true
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
    
        //输入框有值 botton切换样式
        $(".pptt").keyup(function(){
            var input = [];
            $(".pptt[value]").each(function(i,item){
                if(item.value!=""){
                    input.push(item)
                }
        })	
        if(input.length==$(".from-icon>input").length ){
                $(".btn").removeClass("dis").addClass("active").attr("disabled",false)
            }
            // else{
            //     $(".btn").addClass("dis").removeClass("active").attr("disabled",true)  
            // }
        })
    
        // 随机验证码
        var code ; 
        function createCode(){ 
           code = "";  
           var codeLength = 4;
           var random = new Array
        (0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R', 'S','T','U','V','W','X','Y','Z');  
           for(var i = 0; i < codeLength; i++) {  
            var index = Math.floor(Math.random()*36);
            code += random[index];
          }  
           code = code.replace(/[^a-z\d]/ig,"")
        return code;
        } 
     
      //注册
      $(".btn").click(function(){
        let userName = $(".username")  //用户名
        let userPhone = $(".phone")    //手机
        let password = $(".password")  //密码
        let affirmpwd = $(".affirmpwd")   //确认密码
        let userEmail = $(".email")    //邮箱
        let rec = $(".recommend")      //推荐人
        let verify = $(".verify")      //验证码
        let body = $("body")
        
        
        if(condition.username&&condition.phonestate&&condition.pwdstate&&condition.inconsistent&&condition.emailstate&&condition.iconstate&&condition.securitycode&&condition.referrer){
                
                suredAlert(body,"注册成功~");
                return
        }else{
        //注册用户名
            if (userName.val() == ""){       //用户名为空
                suredAlert(body,"用户名不能为空~");
                userName.focus()
                return 
        }else if (!condition.username){  //用户名格式不正确
                suredAlert(body,"请输入正确的用户名~");
                userName.focus()
                return 
        }
        //注册用户名密码
        if (password.val() == ""){       //密码为空
                suredAlert(body,"密码不能为空~");
                password.focus()
                return
        }else if (!condition.pwdstate){  //用户名密码
                suredAlert(body,"请输入正确的密码~");
                password.focus()
                return 
        } 
    
        //确认密码
        if(password.val() != affirmpwd.val()){  //密码不一致
            console.log(password.val(),affirmpwd.val())
                suredAlert(body,"输入密码不一致 请重新输入~");
                affirmpwd.focus()
                return
        }
        
        //用户邮箱
        if(userEmail.val() == ""){        //邮箱为空
                suredAlert(body,"邮箱不能为空~");
                userEmail.focus()
                return
        }else if (!condition.emailstate){ //用户邮箱为false
                suredAlert(body,"请输入正确的邮箱~");
                userEmail.focus()
                return 
        }
    
        //用户名手机
        if(userPhone.val() == ""){    
                suredAlert(body,"手机号码不能为空~");
                userPhone.focus()
                return
        }else if (!condition.phonestate){ //用户手机号
                suredAlert(body,"请输入正确的手机号~");
                userPhone.focus()
                return 
        }
       
        //推荐人
        if (!condition.referrer){    //推荐人
            suredAlert(body,"请填写推荐人~");
            return
        }
    
        //验证码
        if(verify.val() == ""){
            suredAlert(body,"验证码不能为空~");
            verify.focus()
            return
        }else if (!condition.securitycode){  //验证码
            suredAlert(body,"请输入正确的验证码~");
            verify.focus()
            return
        }
        
        //勾选协议
        if (!condition.iconstate){      //勾选协议
            suredAlert(body,"请勾选用户协议~");
            return
        }
        }
       
        
    
       
      })
      })