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
	var maxSize = 1048576; //最大1MB
//	console.log(file)
	
	
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
							result1 += '<input uploadFile value="' + ret['f']  + '" name="user_headimg" style="display:none;" webkitRelativePath>';
							$('#result').html(result);
							$('#show').html(result1);
							layer.msg('上传成功');
							console.log(ret['isSuccess'])
					}else{  
							layer.msg('上传失败');
					}  
					
			});  
		//console.log(file);
			// $.ajax({  
			// 		url: "http://www.szpt.com/index/wallet/uppic", 
			// 		type: 'POST',  
			// 		data: {
			// 			img :img,
			// 			size:size,
			// 			type:type
			// 		},  
			// 		dataType: 'JSON',  
			// 		cache: false,  
			// 		processData: false,  
			// 		contentType: false  
			// }).done(function(ret){  
			// 		if(ret['isSuccess']){
			// 				var result = '';
			// 				var result1 = '';
			// 				// $("#show").attr('value',+ ret['f'] +);
			// 				result += '<img src="' + '__ROAD__' + ret['f']  + '" width="100">';
			// 				result1 += '<input value="' + ret['f']  + '" name="user_headimg" style="display:none;">';
			// 				$('#result').html(result);
			// 				$('#show').html(result1);
			// 				layer.msg('上传成功');
			// 		}else{  
			// 				layer.msg('上传失败');
			// 		}  
			// });  
			//return false;
		 }  
	}
	
//判断有内容则显示进度条
	$("#f").on('change', function( e ){
         //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
         //上传图片
        var rd = new FileReader();//创建文件读取对象
        var files = f.files[0];//获取file组件中的文件
        rd.readAsDataURL(files);//文件读取装换为base64类型
        var name = e.currentTarget.files[0].name;
        console.log(e.currentTarget.files.length)
        //添加图片
//      $(".tu").append(`<img src='__public__/${name}'/>`)
        if(e.currentTarget.files.length ==1){
        	$("#wrapper").show()
        }
    });
	

//进度条
var Loader = function () {    
  var loader = document.querySelector('.loader-container'),
      meter = document.querySelector('.meter'),
      k, i = 1,
      counter = function () {
        if (i <= 100) {   
          meter.innerHTML = i.toString();
          i++;
        } else {
          window.clearInterval(k);
        }
      };

	return {
  	init: function (options) {
      options = options || {};
      var time = options.time ? options.time : 0,
	        interval = time/100;
      
    	loader.classList.add('run');
      k = window.setInterval(counter, interval); 
      setTimeout(function () {        
      	loader.classList.add('done');
      }, time);
    },
  }
}();

Loader.init({
  	// If you have changed the @time in LESS, update this number to the corresponding value. Measured in miliseconds.
  	time: 5000
});