<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;
// use app\common\model\Comment as CommentModel;

class CheckOrder extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
	}
		
	/* 
	*	后台点击审核投资订单时，产生直推收益和动态收益待发放记录
	* @param 订单id
	*/
    public function checkOrder()
    {
			if(!is_post()){
				return json(array('code' => 0, 'msg' => '提交类型错误'));
			}
			$order_id = input('post.order_id/d');
			if(!$order_id){
				return json(array('code' => 0, 'msg' => '获取订单id异常'));
			}
			// 根据id获取订单
			$order = Db::name('buy_order')->where(['id'=>$order_id])->find();
			if(!$order){
				return json(array('code' => 0, 'msg' => '当前订单不存在'));
			}
			// 判断订单是否已经审核过
			if($order['is_check']==1){
				return json(array('code' => 0, 'msg' => '当前订单已审核过'));
			}
			$res = Db::name('buy_order')->where(['id'=>$order_id])->update(['is_check'=>1]);
			if(!$res){
				return json(array('code' => 0, 'msg' => '操作异常，请联系管理员'));
			}
			return json(array('code' => 200, 'msg' => '订单审核成功！'));
    }
   
}