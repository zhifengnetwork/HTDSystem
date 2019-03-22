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
	*	后台点击审核投资订单时，把对应币种数量累加到buy_execute_order和对应用户的币种钱包本金数量
	*   
	* @param 订单id
	*/
    public function checkOrder()
    {
			// if(!is_post()){
			// 	return json(array('code' => 0, 'msg' => '提交类型错误'));
			// }
			$order_id = input('order_id/d');
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

			// 开启事务
			Db::startTrans();
			try{

				// 如果审核订单和当前execute_order订单号相同则不累加，该状态即可
				// 修改execute_order订单状态
				$execute_order_check = Db::name('execute_order')->where(['uid'=>$order['uid'], 'cu_id'=>$order['cu_id']])->find();
				if($execute_order_check['order_no'] != $order['order_no']){
					// 数量累加到execute_order
					$res1 = Db::name('execute_order')->where(['uid'=>$order['uid'], 'cu_id'=>$order['cu_id']])->setInc('num', $order['num']);
					// 累加total_money
					Db::name('execute_order')->where(['uid'=>$order['uid'], 'cu_id'=>$order['cu_id']])->setInc('total_money', $order['total_money']);
				}
				// 数量累加到钱包对应币种数量
				$res2 = Db::name('user_wallet')->where(['uid'=>$order['uid'], 'cu_id'=>$order['cu_id']])->setInc('cu_num', $order['num']);

				// 修改订单状态buy_order
				$res3 = Db::name('buy_order')->where(['id'=>$order_id])->update(['is_check'=>1,'check_time'=>time()]);
				
				// execute_order审核过一次不需要审核
				if($execute_order_check['is_check']==0){
					$res4 = Db::name('execute_order')->where(['order_no'=> $order['order_no']])->update(['is_check'=>1]);
				}

				// 把当前订单的总金额*3累加到股权钱包中
				$stock_rights_money = $order['total_money']*3;  // 1:3
				$res5 = Db::name('user')->where(['id'=>$order['uid']])->setInc('stock_rights', $stock_rights_money);


				// 提交事务
				Db::commit();   
				return json(array('code' => 200, 'msg' => '订单审核成功！'));
				
			}catch(\Exception $e){

				// 回滚事务
				Db::rollback();
				return json(array('code' => 0, 'msg' => '操作异常，请联系管理员'));
			}
	}
	
	// 用户提取对应币种本金
   
}