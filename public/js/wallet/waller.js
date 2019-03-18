$(document).ready(function(){
	$(".asset").on("click",function(){
		$(".showZ").show();
		$(".shadow").show();
	})
	$(".hideIcon").on("click",function(){
		$(".showZ").hide();
		$(".shadow").hide();
	})
	$(".sum").on("click",function(){
		$(".pop").show();
		$(".shadow").show();
	})
	$(".hideTz").on("click",function(){
		$(".pop").hide();
		$(".shadow").hide();
	})
	$(".ur").on("click",function(){
		$(".code-up").show();
		$(".pop").hide();
	})
	$(".shut").on('click',function(){
		$(".code-up").hide();
		$(".shadow").hide();
	})
})


function wallet(aaa){
	$("#span").html(aaa);
	console.log(aaa);
}

function sc(){
	var animateimg = $("#f").val(); //获取上传的图片名 带//
	
	var imgarr=animateimg.split('\\'); //分割
	var myimg=imgarr[imgarr.length-1]; //去掉 // 获取图片名
	var houzui = myimg.lastIndexOf('.'); //获取 . 出现的位置
	
	var ext = myimg.substring(houzui, myimg.length).toUpperCase();  //切割 . 获取文件后缀
	
	var file = $('#f').get(0).files[0]; //获取上传的文件
	var fileSize = file.size;           //获取上传的文件大小
	var maxSize = 1048576;              //最大1MB
	if(ext !='.PNG' && ext !='.GIF' && ext !='.JPG' && ext !='.JPEG' && ext !='.BMP'){
			parent.layer.msg('文件类型错误,请上传图片类型');
			return false;
	}else if(parseInt(fileSize) >= parseInt(maxSize)){
			parent.layer.msg('上传的文件不能超过1MB');
			return false;
	}else{  
		// var data = new FormData($('#form1')); 
		var img = file.name;
		var size = file.size;
		var type = file.type;
		// console.log(name);
		// console.log(name);
		// console.log(name);
		console.log(file);
			$.ajax({  
					url: "http://www.szpt.com/index/wallet/uppic", 
					type: 'POST',  
					data: {
						img :img,
						size:size,
						type:type
					},  
					dataType: 'JSON',  
					cache: false,  
					processData: false,  
					contentType: false  
			}).done(function(ret){  
					if(ret['isSuccess']){
							var result = '';
							var result1 = '';
							// $("#show").attr('value',+ ret['f'] +);
							result += '<img src="' + '__ROAD__' + ret['f']  + '" width="100">';
							result1 += '<input value="' + ret['f']  + '" name="user_headimg" style="display:none;">';
							$('#result').html(result);
							$('#show').html(result1);
							layer.msg('上传成功');
					}else{  
							layer.msg('上传失败');
					}  
			});  
			return false;
		 }  
	}