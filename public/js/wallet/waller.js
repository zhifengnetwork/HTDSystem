$(document).ready(function(){
	//点击总投资-弹出框
	$(".asset").on("click",function(){
		$(".pop").show();
		$(".shadow-wrap-up").show();
	})
	//点击关闭总投资-弹出框
	$(".hideTz").on("click",function(){
		$(".pop").hide();
		$(".shadow-wrap-up").hide();
	})
	
	/*点击出现-币种详情*/
	$(".wtf_xiala").on('click',function(){
		
		
		// 获取投资金额
		var money = $('.inp').val();
		if(!money){
			layer.msg('请先输入投资额');
			return false;
		}
		
		$(".showZ").show();
		/*蒙版*/
		$(".shadow-wrap").show();
	})
	
	/*关闭-币值详情*/
	$(".hideIcon").on('click',function(){
		$(".assetPopup").hide();
		/*蒙版*/
		$(".shadow-wrap").hide();
	})

	//弹出二维码显示隐藏
	$("#sm_click").on("click",function(){
		$(".payment_wenma").show()
	})
	$(".qb_bg").on("click",function(){
		$(".payment_wenma").hide()

	})
	$(".payment_wenma_sc").on("click",function(){
		$(".payment_wenma").hide()
	})


	/*投资-按钮*/
	$('.sum').on('click',function(){
		
		// 获取投资金额
		var money = $('.inp').val();
		// 获取最小投资金额
		// var min_money = $('.min_money').html();
		if(!money){
			layer.msg('请先输入投资额');
			return false;
		}
		if(money<1){
			layer.msg('投资额额度不可为0');
			return false;
		}
		var cu_id = $('#cu_name_input').attr('data-name');
		var cu_price = $('.cu_price').html();
		var cu_num = $('.p3').html();
		if(!cu_id){
			layer.msg('请选择币种');
			return false;
		}
		if(!cu_price){
			layer.msg('币种单价获取出错！');
			return false;
		}
		if(!cu_num){
			layer.msg('币种数量获取出错');
			return false;
		}

		// 获取支付方式
		var pay_way = $('#pay_way_id').val();
		if(pay_way==1){
			// 判断是否上传发票
			// todo
			var dataJson = {money:money,cu_id:cu_id,cu_price:cu_price,cu_num:cu_num,pay_way:pay_way};
		}else{
			var dataJson = {money:money,cu_id:cu_id,cu_price:cu_price,cu_num:cu_num,pay_way:pay_way};
		}

		$.ajax({
			url: '/index/wallet/confirmInvest',
			type: 'post',
      dataType: 'json',
			data: dataJson,
			success:function(msg){
				if(msg.code==200){
					// layer.msg(msg.msg);
					layer.msg(msg.msg, function(){
						location.reload();
					});
				}else{
					layer.msg(msg.msg);
					return false;
				}
			}
		});

		// $(".hideEvm").show();
		// /*蒙版*/
		// $(".shadow").show();
	})
	/*二维码-弹框-关闭*/
//	$('.shut').on('click',function(){
//		$(".hideEvm").hide();
//		/*蒙版*/
//		$(".shadow").hide();
//	})
	
})

/*获取对应的币值*/
function obtainFun(id,name,price,walletAddr,wallet_qrcode){
	// 获取投资金额
	var money = $('.inp').val();
	// 获取最小投资金额
	// var min_money = $('.min_money').html();
	if(!money){
		layer.msg('请先输入投资额');
		return false;
	}
	if(money<1){
		layer.msg('投资额额度不可为0');
		return false;
	}


	// 对应币种二维码放置
	$('.payment_wenma_sc').attr('src',wallet_qrcode);
	
	// getQrcode(walletAddr);
	// 获取填充对应币种钱包地址
	// $('.p_text').html(walletAddr);
	// 钱包地址二维码
	// var qrCodeUrl = '';
	// var domain = document.domain;
	// // // 组织url
	// // qrCodeUrl = 'http://'+domain+'/index/walletaddr/showWalletAddr/?walletAddr='+walletAddr;
	// // console.log(qrCodeUrl);
	// // new QRCode('tg_qrcode', {
	// // 	text: qrCodeUrl, 
	// // 	width: 220, 
	// // 	height: 220, 
	// // 	colorDark : '#000000', 
	// // 	colorLight : '#ffffff', 
	// // 	correctLevel : QRCode.CorrectLevel.H 
	// // });

	/*关闭弹窗*/
	$(".assetPopup").hide();
	$(".shadow-wrap").hide();
	// 点击获取name值到input框
	$('#cu_name_input').html(name);	
	$('#cu_name_input').attr('data-name',id);
	// 根据投资金额和当前币种价计算
	if(money>0){
		// 当前投资额度
		$('.in_money').html(money);
		// 币种单价
		$('.cu_price').html(price);
		// 币种数量
		var cu_num= money/price;
		cu_num = cu_num.toFixed(8);
		$('.p3').html(cu_num);
	}
	console.log(money);

	/*ajax*/
}

// 生成对应二维码地址
// function getQrcode(walletAddr){
// 	$('#tg_qrcode').html("");
	
// }

/* function sc(){

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
	} */
	
//判断有内容则显示进度条
	$("#f").on('change', function( e ){
         //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
         //上传图片
        var rd = new FileReader();//创建文件读取对象
        var files = f.files[0];//获取file组件中的文件
        rd.readAsDataURL(files);//文件读取装换为base64类型
        var name = e.currentTarget.files[0].name;
        //添加图片
//      $(".tu").append(`<img src='__public__/${name}'/>`)
        if(e.currentTarget.files.length ==1){
        	$("#wrapper").show()
        	console.log(e.currentTarget.files)
        	//进度条
			var Loader = function () {    
			  var loader = document.querySelector('.loader-container'),
			      meter = document.querySelector('.meter'),
			      k, i = 1,
			      counter = function () {
			        if (i <= 100) {   
			          meter.innerHTML = i.toString();
			          i++;
			        }else if(i>=100){
				       $("#wrapper").hide(5000)
			        }else {
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
			  	time: 2000
			});
        	
        }
    });
	

