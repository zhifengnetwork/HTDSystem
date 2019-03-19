<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;
use think\Request;
class Wallet extends HomeBase
{

    
    /**
     * 点击首页投资按钮进入获取数据
     * 获取当前用户投资的所有币种订单
    */
    public function wallet()
    {
        $user_id = session('home.id')?session('home.id'):1;
        $users = $this->users($user_id);
        $user_order = $this->user_order($user_id);
        $user_wallet = $this->user_wallet($user_id);
        $htd_currency = $this->htd_currency();
        // 获取最低投资金额
        $min_money = Db::name('income_config')->field('name,value')->where(['name'=>'price_min1'])->select();
		$min_money = arr2name($min_money);
        $this->assign('user_order',$user_order);
        $this->assign('user_wallet',$user_wallet);
        $this->assign('htd_currency',$htd_currency);
        $this->assign('min_money',$min_money['price_min1']['value']);
        $this->assign('user',$users);
        return $this->fetch();
    }

    /**
     *  用户点击确定投资
     * 
    */
    public function confirmInvest()
    {

        if(!is_post()){
            return json(array('code' => 0, 'msg' => '提交方式错误'));
        }
        $param = input('post.');
        $cu_id = intval($param['cu_id']);
        if(!$cu_id){
            return json(array('code' => 0, 'msg' => '币种id不可为空'));
        }
        // 判断币种是否开启
        $currency_one = Db::name("currency")->where(['id'=>$cu_id])->find();
        if($currency_one['status']!=1){
            return json(array('code' => 0, 'msg' => '当前币种暂不开放'));
        }
        if(!$param['num']){
            return json(array('code' => 0, 'msg' => '币种数量不可为空'));
        }
        if(!$param['price']){
            return json(array('code' => 0, 'msg' => '币种价格不可为空'));
        }
        $total_money = $param['num']*$param['price']; // 
        if($total_money!=$param['total_money']){
            return json(array('code' => 0, 'msg' => '投资金额出错'));
        }
        // 订单信息入库
        $data = array(
            'order_no' => byOrderNo(),
            'uid' => session('home.id'),
            'cu_id' => $cu_id,
            'num'   => $param['num'],
            'price' => $param['price'],
            'total_money' => $total_money,
            'pay_way' => 1,
            'voucher' => 'ddddd',
            'create_time' => time()
        );
        $res = Db::name('buy_order')->insert($data);
        if(!$res){
            return json(array('code' => 0, 'msg' => '网络异常，请稍后再试。'));
        }
        return json(array('code' => 200, 'msg' => '投资成功，请等待审核'));
    }

    /**
     *  获取币种列表
     */
    private function htd_currency()
    {
        $htd_currency = Db::name("currency")->where(['status'=>1])->select();
        if($htd_currency){
            return $htd_currency;
        }
    }

    /*
    *   获取用户钱包数据
    */
    private function user_wallet($user_id){

        if(!$user_id){
            return false;
        }
        $user_wallet = db('user_wallet')->where(['uid'=>$user_id])->select();
        return $user_wallet;

    }

    /**
     *  获取用户的投资历史订单
     */
    private function user_order($user_id)
    {
        $user_order = db('buy_order')->field('id,uid,cu_id,num,price,total_money,create_time')->where(['uid'=>$user_id])->select();
        if($user_order){
            $currency_arr = $this->htd_currency();
            foreach($user_order as $k1=>$v2){
                foreach($currency_arr as $k=>$v){
                    if($v2['cu_id'] == $v['id']){
                        $user_order[$k1]['cu_name'] = $v['alias_name'];
                    }
                }
            }
            return $user_order;
        }
    }
    /**
    *获取用户信息
    */
    private function users($user_id){
        $field = "balance,id,pid,username,mobile,promotion,activation";
        $users = db('user')->field($field)->where(['id'=>$user_id])->find();
        return $users;
    }
}