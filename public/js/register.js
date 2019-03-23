

// $(function(){
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
            // if(email.val()==""){
            //     email.parent().parent().prev().addClass("mistake").html(`邮箱不能为空`)
            //     return condition.emailstate=false
            // }else 
            if(email.val()){
                if(!reg.test(email.val())){
                    email.parent().parent().prev().addClass("mistake").html(`请输入正确的邮箱`)
                    return condition.emailstate=false
                }else{
                    email.parent().parent().prev().removeClass("mistake").html("")
                    return condition.emailstate=true
                }
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
            var code1 = $('.verify').val();
            // var code=123456;
            if(code1 ==""){
                var code1=$(this)
                code1.parent().parent().prev().addClass("mistake").html(`验证码不能为空`)
                return condition.securitycode=false
            }
            // else if(code1.val()!=code){
            //     code1.parent().parent().prev().addClass("mistake").html(`请输入正确的验证码`)
            //     return condition.securitycode=false
            // }else{
            //     code1.parent().parent().prev().removeClass("mistake").html("")
            //     return condition.securitycode=true
            // }
        }
    
        //推荐人
        function statusreferrer(){
            var $referrer = $(this)
            if(!$referrer.val()){
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
            var phone = $(".phone");
            var phoneS = $(".phone").val();
            if(phone.val()==""){
                phone.parent().parent().prev().addClass("mistake").html('手机不能为空')
                return condition.phonestate=false
            }
            if(!reg.test(phone.val())){
                phone.parent().parent().prev().addClass("mistake").html('请输入正确的手机号码~')
                return condition.phonestate=false
            }

            var sms_type = $('#sms_type_id').val();
            // layer.msg(111); return false;
            // 获取验证码
            var flag = true;
            $.ajax({
                url: '/index/login/getPhoneVerify',
                type: 'post',
                dataType: 'json',
                data: {phone:phoneS, sms_type:sms_type},
                success:function(msg){
                    if(msg.code==0){
                        layer.msg(msg.msg);
                        flag = false;
                        return false;
                    }
                }
            });
            if(flag){
                layer.msg('已发送');
            }
            $(".verify").focus();
            daojishi(60,$(this))
            $(".phone").attr("disabled","disabled") 
            return condition.phonestate=true
        })
        function daojishi(seconds,obj){
            if (seconds > 1){
                    seconds--;
                    $(obj).html(seconds+"重新获取 ").attr("disabled", true);
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
    