layui.use(['layer','jquery','form'],function(){
  var layer = layui.layer
  , form = layui.form
  ,jq = layui.jquery;
  
  form.on('checkbox(checkAll)', function(data){
    if(data.elem.checked){
      jq("input[lay-filter='checkOne']").prop('checked',true);
    }else{
      jq("input[lay-filter='checkOne']").prop('checked',false);
    }
    form.render('checkbox');
  });  

  form.on('checkbox(checkOne)', function(data){
    var is_check = true;
    if(data.elem.checked){
      jq("input[lay-filter='checkOne']").each(function(){
        if(!jq(this).prop('checked')){ is_check = false; }
      });
      if(is_check){
        jq("input[lay-filter='checkAll']").prop('checked',true);
      }
    }else{
      jq("input[lay-filter='checkAll']").prop('checked',false);
    } 
    form.render('checkbox');
  });

  jq('.elementcheck').click(function(){
	  
	  var order_id= jq(this).data('id');
    var url= '/admin/Check_Order/checkOrder';
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
        $.ajax({
          url: url,
          type: 'post',
          dataType: 'json',
          data: {'order_id':order_id,'type':1},
          success:function(data){
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
            }
        });
			},
			//不通过
			btn2:function(index,layero){
        $.ajax({
          url: url,
          type: 'post',
          dataType: 'json',
          data: {'order_id':order_id,'type':2},
          success:function(data){
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
		});
    // layer.confirm('通过审核?', {icon: 3, title:'安全提示'}, function(index){
    //     loading = layer.load(2, {
    //         shade: [0.2,'#000']
    //     });
    //     jq.getJSON(url,function(data){

    //         if(data.code == 200){
    //             layer.close(loading);
    //             layer.msg(data.msg, {icon: 1, time: 1000}, function(){
    //                 //
    //                 if(length-1>0){
    //                     location.reload();
    //                 }else{
    //                     if(page>1){
    //                       page=page-1;
    //                     }
    //                       window.location.reload();
    //                 }

    //             });
    //         }else{
    //             layer.close(loading);
    //             layer.msg(data.msg, {icon: 2, anim: 6, time: 1000});
    //         }
    //     });
      
    //   }); 
    });
  });








  