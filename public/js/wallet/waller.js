/**投资按钮-状态 */
// var stateJ = true;
$(document).ready(function(){
	/**获取焦点
	 * 手机端=>input=>点击隐藏=>底部导航栏
	 * **/
	$('#throw_inp').focus(function() {
		/*底部导航栏*/
		$('.bottomNavWrap').hide();
	})
	/*失去焦点*/
	$('#throw_inp').blur(function() {
		/*底部导航栏*/
		$('.bottomNavWrap').show();
	})
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
		// if(!money){
		// 	layer.msg('请先输入投资额');
		// 	return false;
		// }
		
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

	//出现弹框背景禁止滚动
	$(".qb_bg").bind("touchmove","touchstart",function(e){
			e.preventDefault();
	})
	$(".shadow-wrap-up").bind("touchmove","touchstart",function(e){
			e.preventDefault();
	})
	$(".shadow-wrap").bind("touchmove","touchstart",function(e){
		e.preventDefault();
})
	
	/*投资-按钮*/
	$('.sum').on('click',function(){
		var obj=$(this);
		// 获取投资金额
		var cu_num = $('.inp').val();
		// 获取最小投资金额
		// var min_money = $('.min_money').html();
		
		var cu_id = $('#cu_name_input').attr('data-name');
		// var cu_price = $('.cu_price').html();
		// var cu_num = $('.p3').html();
		if(!cu_id){
			layer.msg('请选择币种');
			return false;
		}
		// if(!cu_price){
		// 	layer.msg('币种单价获取出错！');
		// 	return false;
		// }
		if(!cu_num){
			layer.msg('请输入币种数量');
			return false;
		}

		// if(!cu_num){
		// 	layer.msg('请先输入币种数量');
		// 	return false;
		// }
		if(cu_num<0){
			layer.msg('投资币种数量不可为0');
			return false;
		}

		// 获取支付方式
		var pay_way = $('#pay_way_id').val();
		if(pay_way==1){
			// 判断是否上传发票
			var imgUrl = $("#imgUrl_id").val();
			if(!imgUrl){
				layer.msg('请上传发票');
				return false;
			}
			var dataJson = {cu_id:cu_id,cu_num:cu_num,pay_way:pay_way,imgUrl:imgUrl};
		}else{
			var dataJson = {cu_id:cu_id,cu_price:cu_price,cu_num:cu_num,pay_way:pay_way};
		}
		//防止快速双击 
		var has_click=obj.attr('has-click');
		if(has_click=='1'){
			return false;
		}else{
			obj.attr('has-click','1');
		}
		$.ajax({
			url: '/index/wallet/confirmInvest',
			type: 'post',
			dataType: 'json',
			data: dataJson,
			success:function(msg){
				if(msg.code==200){
					obj.attr('has-click','0');
					layer.open({
						content: msg.msg,
						icon: 1,
						skin:'my-layer-cancelcheck-btn',
						closeBtn: 0,
						shade:0.3,
						yes: function(index){
						  layer.close(index); //如果设定了yes回调，需进行手工关闭
						  location.reload();
						}
					});

				}else{
					obj.attr('has-click','0');
					layer.msg(msg.msg);
					// stateJ = true;
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
	// if(!money){
	// 	layer.msg('请先输入投资额');
	// 	return false;
	// }
	// if(money<1){
	// 	layer.msg('投资额额度不可为0');
	// 	return false;
	// }
	var domain = document.domain;
	var path_url = '/'+wallet_qrcode;
	// 对应币种二维码放置
	$('.payment_wenma_Two').attr('src',path_url);
	// 获取填充对应币种钱包地址
	$('.p_text').html(walletAddr);
	// getQrcode(walletAddr);
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

	/*ajax*/
}

// 生成对应二维码地址
// function getQrcode(walletAddr){
// 	$('#tg_qrcode').html("");
// }


// 上传发票
/*对应 回显图片的下标*/
// var ind = null;
/**存储丢给后台图片(发票)*/
var dataImg = null;

/*上传图片*/
function UpLoad(e) {
	//显示提示信息（上传中..）
	$(".uploadWrap .passData").css("display","block");
	$(".uploadWrap .resultData").css("display","none");
	
	var that = $(e);
	if(e.files[0]) {
		var f = e.files[0];
		fileType = f.type;
		if(/image\/\w+/.test(fileType)) {
			var fileReader = new FileReader();
			fileReader.readAsDataURL(f);
			fileReader.onload = function(event) {
				var result = event.target.result; //返回的dataURL   
				var image = new Image();
				image.src = result;
				//若图片大小大于1M，压缩后再上传，否则直接上传  
				if(f.size > 1024 * 1024) {
					image.onload = function() {
						//创建一个image对象，给canvas绘制使用
						var canvas = document.getElementById("canvas");
						canvas.width = image.width;
						canvas.height = image.height; //计算等比缩小后图片宽高   
						var ctx = canvas.getContext('2d');
						ctx.drawImage(this, 0, 0, canvas.width, canvas.height);
						var newImageData = canvas.toDataURL(fileType, 0.8); //重新生成图片
						/* 图片 回显*/
						// that.siblings(".preViewImg").eq(0).attr("src", newImageData);
						console.log(newImageData);
						dataImg	= newImageData;
						$("#canvas").hide();
						/*根据点击的下标 =>显示 '回显图片'*/
						//console.log('压缩:dataImg');
						$.ajax({
							url: '/index/wallet/getUploadImg',
							type: 'post',
							dataType: 'json',
							data: {dataImg:image.src},
							success:function(msg){
								if(msg.code==200){
									$('#imgUrl_id').val(msg.imgUrl);
									/*隐藏'上传中...'*/
									$(".uploadWrap .passData").css("display","none");
									$(".uploadWrap .resultData").css("display","block");
									$('.uploadWrap .resultData .text').html('成功'); 
									// console.log(msg);
									layer.msg(msg.msg)
								}else{
									/*隐藏'上传中...'*/
									$(".upload-tips .passData").css("display","none");
									$(".uploadWrap .resultData").css("display","block");
									$('.uploadWrap .resultData .text').html('失败');
									layer.msg(msg.msg)
									return false;
								}
							}
						});
					}
				}else {
					//创建一个image对象，给canvas绘制使用 
					image.onload = function() {
						$.ajax({
							url: '/index/wallet/getUploadImg',
							type: 'post',
							dataType: 'json',
							data: {dataImg:image.src},
							success:function(msg){
								if(msg.code==200){
									$('#imgUrl_id').val(msg.imgUrl);
									/*隐藏'上传中...'*/
									$(".uploadWrap .passData").css("display","none");
									$(".uploadWrap .resultData").css("display","block");
									$('.resultData .text').html('成功');
									// console.log(msg);
									layer.msg(msg.msg)
								}else{
									/*隐藏'上传中...'*/
									$(".uploadWrap .passData").css("display","none");
									$(".uploadWrap .resultData").css("display","block");
									$('.resultData .text').html('失败');
									layer.msg(msg.msg)
									return false;
								}
							}
						});
						/*图片 回显 */
						dataImg = result;
					}
				}
			}
		} else {
			layer.msg("请选择图片");
		}

	} else {
		console.log('取消选择图片！')
	}
}


  //  var form = layui.form,
  //           upload = layui.upload,
  //           element = layui.element,
  //           jq = layui.jquery;


  //       upload.render({
  //           elem: '#image',
  //           url: '{:url("index/upload/upimage")}',
  //           done: function(res) {
  //               //如果上传失败
  //               if (res.code == '200') {
  //                   jq('input[name=userhead]').val(res.path);
  //                   headedit.src = res.headpath;
  //                   return layer.msg('上传成功');
  //               } else {
  //                   //上传成功
  //                   return layer.msg(res.msg);
  //               }

  //           }
  //       });
	

