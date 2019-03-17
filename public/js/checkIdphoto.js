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
    jq('.checkIdphoto').click(function(){

        var id= jq(this).data('id');
        var url= jq(this).data('url');
        var page= jq('.pagination li.active span').html();
        var length= jq('.admin-table #content tr').length;

        // layer.confirm('要通过审核?', {icon: 3, title:'安全提示'}, function(index){
        //     loading = layer.load(2, {
        //         shade: [0.2,'#000']
        //     });
        //
        //
        //     jq.getJSON(url,function(data){
        //
        //         if(data.code == 200){
        //             layer.close(loading);
        //             layer.msg(data.msg, {icon: 1, time: 1000}, function(){
        //
        //             });
        //         }else{
        //             layer.close(loading);
        //             layer.msg(data.msg, {icon: 2, anim: 6, time: 1000});
        //         }
        //     });
        //
        // });
        layer.confirm('通过审核?', {icon: 3, title:'安全提示'}, function(index){

            loading = layer.load(2, {
                shade: [0.2,'#000']
            });
            console.log(index);

            // jq.getJSON(url,function(data){
            //
            //     if(data.code == 200){
            //         layer.close(loading);
            //         layer.msg(data.msg, {icon: 1, time: 1000}, function(){
            //
            //         });
            //     }else{
            //         layer.close(loading);
            //         layer.msg(data.msg, {icon: 2, anim: 6, time: 1000});
            //     }
            // });

        });



    });



});