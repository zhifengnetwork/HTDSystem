layui.use(['layer','jquery','form'],function(){
		var layer = layui.layer
		, form = layui.form
		,jq = layui.jquery;

		//点击事件
		jq('.elementcheck').click(function(index){
			var id= jq(this).data('id');
			var url= jq(this).data('url');

			var page= jq('.pagination li.active span').html();
			var length= jq('.admin-table #content tr').length;

			layer.open({
				title:'安全提示',
				type:1,
				content:'<div style="padding:15px 0px 10px 20px;font-size:14px">通过审核?</div>',
				time:5000,
				closeBtn:1,
				btn:['通过','不通过','取消'],

				//通过
				yes:function(index,layero){
					jq.getJSON(url,{'id':id,'status':1},function(data){
						layer.close(index);

						if(data.code == 200){
							layer.msg(data.msg, {icon: 1, time: 3000}, function(){

								if(length-1>0){
									location.reload();
								}else{
									if(page>1){
										page=page-1;
									}

						        	location.href = window.location.href+'?page='+page;// '{:url("admin_user/index")}'+page;
						        }
						    });
						}else{
							layer.msg(data.msg, {icon: 2, anim: 6, time: 3000});
						}
					});
				},
				//不通过
				btn2:function(index,layero){
					jq.getJSON(url,{'id':id,'status':2},function(data){
						layer.close(index);
						if(data.code == 200){
							layer.msg(data.msg, {icon: 1, time: 3000}, function(){

								if(length-1>0){
									location.reload();
								}else{
									if(page>1){
										page=page-1;
									}

						        	location.href = window.location.href+'?page='+page;// '{:url("admin_user/index")}'+page;
						        }
						    });
						}else{
							layer.msg(data.msg, {icon: 2, anim: 6, time: 3000});
						}
					});
				},
				//取消
				btn3:function(index,layero){
					layer.close(index);
				},
				cancel:function(){
					layer.close();
				}
			})
		});	
  	});