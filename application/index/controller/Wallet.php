<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;
use think\Request;
class Wallet extends HomeBase
{

    public $user = '';
    public function _initialize()
    {  
        $this->user = session('home');
        if(!$this->user){
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/";
            header("refresh:1;url=$url");
            exit;
        }
    }
    
    /**
     * 点击首页投资按钮进入获取数据
     * 获取当前用户投资的所有币种订单
    */
    public function wallet()
    {
        if($this->user){
            $id = 1;
        }else{
            $id = 2;
        }
        
        $user_id = session('home.id');
        $users = $this->users($user_id);
        $user_order = $this->user_order($user_id);
        $user_wallet = $this->user_wallet($user_id);
        $htd_currency = $this->htd_currency();
        $userWallet = $this->getUserWallet($user_id);
        // 获取最低投资金额
        // $min_money = Db::name('income_config')->field('name,value')->where(['name'=>'price_min1'])->select();
        // $min_money = arr2name($min_money);
        $this->assign('user_order',$user_order);
        $this->assign('user_wallet',$user_wallet);
        $this->assign('htd_currency',$htd_currency);
        $this->assign('userWallet',$userWallet);
        // $this->assign('min_money',$min_money['price_min1']['value']);
        $this->assign('user',$users);
        $this->assign('id',$id);
        return $this->fetch();
    }

    /**
     *  用户点击确定投资
     * 1、判断用户是否存在当前币种的订单,如果存在，则累加订单币种（复投）。
    */
    public function confirmInvest()
    {
        if(!is_post()){
            return json(array('code' => 0, 'msg' => '提交方式错误'));
        }
        $uid = session('home.id');
        $param = input('post.');
        // dump($param);
        $cu_id = intval($param['cu_id']);
        $pay_way = intval($param['pay_way']); // 1发票 2复投

        // 判断当前用户是否存在
        $user_one = Db::name('user')->where(['id'=>$uid])->find();
        if(!$user_one){
            return json(array('code' => 0, 'msg' => '当前用户不存在'));
        }
        if(!$cu_id){
            return json(array('code' => 0, 'msg' => '币种id不可为空'));
        }

        if($param['cu_num']<0){
            return json(array('code' => 0, 'msg' => '数量不可小于0'));
        }
        // 判断币种是否开启
        $currency_one = Db::name("currency")->where(['id'=>$cu_id])->find();
        // 判断币种是否存在
        if(!$currency_one){
            return json(array('code' => 0, 'msg' => '当前币种不存在'));
        }
        if($currency_one['status']!=1){
            return json(array('code' => 0, 'msg' => '当前币种暂不开放'));
        }
    
        if(!$param['cu_num']){
            return json(array('code' => 0, 'msg' => '币种数量不可为空'));
        }
        if($pay_way==1){
            if(!$param['imgUrl']){
                return json(array('code' => 0, 'msg' => '请上传发票'));
            }
        }
        
        // if(!$param['cu_price']){
        //     return json(array('code' => 0, 'msg' => '币种价格异常出错'));
        // }
        $total_money = $param['cu_num']*$currency_one['price']; //
        if($pay_way==1){
             // 获取数据库单价
            $cu_num =  $param['money']/$currency_one['price'];
        }else{
            $cu_num = $param['cu_num'];
        }
       
        // 判断当前投资币种是否存在钱包表，如果没有插入一条
        $wallet_is = Db::name('user_wallet')->where(['uid'=>$user_one['id'],'cu_id'=>$currency_one['id']])->find();
        

        Db::startTrans();
        try{
            // 判断用户当前币种是否存在订单，如果存在则累加(复投 2)
            $is_cu_order = Db::name('execute_order')->where(['uid'=>$user_one['id'],'cu_id'=>$cu_id])->find();
        //    echo Db::name('execute_order')->getLastSql();
            if($is_cu_order['cu_id'] && $pay_way==2){
               
                if (!captcha_check($param['verify'])) {
                    return json(array('code' => 0, 'msg' => '验证码错误'));
                }

                // 获取当前用户对应币种的静态(动态)收益120、分红钱包金额121
                $user_wallet = Db::name('user_wallet')->where(['uid'=>$user_one['id'],'cu_id'=>$currency_one['id']])->find();

                $wallet_flag = intval($param['wallet_flag']);
              
                // 判断钱包是否够复投对应币种数量
                if($wallet_flag==120){
                    $wallet_name = '收益';
                    if($user_wallet['bonus_wallet']<$cu_num){
                        return json(array('code' => 0, 'msg' => '收益不足'));
                    }
                    // 扣减对应数量
                    Db::name('user_wallet')->where(['uid'=>$user_one['id'],'cu_id'=>$currency_one['id']])->setDec('bonus_wallet', $cu_num);
                }
                if($wallet_flag==121){
                    $wallet_name = '分红';
                    if($user_wallet['rate_wallet']<$cu_num){
                        return json(array('code' => 0, 'msg' => '分红收益不足'));
                    }
                    Db::name('user_wallet')->where(['uid'=>$user_one['id'],'cu_id'=>$currency_one['id']])->setDec('rate_wallet', $cu_num);
                }
                // 复投累加对应币种数量execute_order和user_wallet对应币种本金钱包
                $inc_res = Db::name('execute_order')->where(['uid'=>$user_one['id'],'cu_id'=>$currency_one['id']])->setInc('num', $cu_num);
                $inc_res2 = Db::name('user_wallet')->where(['uid'=>$user_one['id'],'cu_id'=>$currency_one['id']])->setInc('cu_num', $cu_num);

                // 插入日志
                $this->insertLog($user_one['id'],$cu_id,$wallet_name.'复投'.$cu_num,2); // 复投

                // 提交事务
                Db::commit(); 
                return json(array('code' => 200, 'msg' => '复投成功'));
            }else{

                if(!$wallet_is){
                    $data_in = array(
                        'uid' => $user_one['id'],
                        'cu_id' => $currency_one['id']
                    );
                    Db::name('user_wallet')->insert($data_in);
                }

                $res3 = true;
                $resUp = true;
                // 订单信息入库
                $data = array(
                    'order_no' => byOrderNo(),
                    'uid' => $user_one['id'],
                    'cu_id' => $cu_id,
                    'num'   => $cu_num,
                    'price' => $currency_one['price'],
                    'total_money' => $param['money'],
                    'pay_way' => $pay_way,
                    'voucher' => $param['imgUrl'],
                    'create_time' => time()
                );
                $res = Db::name('buy_order')->insert($data);
                // 判断执行收益订单表是否存在，不存在插入一次
                if(!$is_cu_order){
                    $res3 = Db::name('execute_order')->insert($data);
                }

                // 入单激活会员
                if(!$user_one['activation']){
                    $resUp = Db::name('user')->where(['id'=>$user_one['id']])->update(['activation'=>1]);
                }
            }

            // 提交事务
            Db::commit(); 
            return json(array('code' => 200, 'msg' => '投资成功，请等待审核'));
            
        }catch(\Exception $e){
            // 回滚事务
            Db::rollback();
            return json(array('code' => 0, 'msg' => '网络异常，请稍后再试。'));
        }
    }

    /**
     *  获取币种列表
     */
    private function htd_currency()
    {
        $where['status'] = 1;
        $where['alias_name'] = ['neq','HTD'];
        $htd_currency = Db::name("currency")->where($where)->select();
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
        $user_order = db('buy_order')->field('id,uid,cu_id,num,price,total_money,create_time,is_check')->where(['uid'=>$user_id])->select();
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

    /**
     * 当前用户的资产列表
     */
    public function getUserWallet($uid){

        if($uid){
            $userWallet = Db::name('user_wallet')
            ->alias('a')
            ->field('a.*, b.alias_name')
            ->join('htd_currency b', 'a.cu_id=b.id')
            ->where(['a.uid'=>$uid])->select();
        }
        return $userWallet;
    }


    /**
     * base64图片上传
     * @param $base64_img
     * @return array
     */
    public function getUploadImg(){
        $base64 = input('post.dataImg');
        $res = uploadImg($base64);
        return $res;
    }

    // 插入日志表
	public function insertLog($uid,$cu_id,$note,$type){

		$data = array(
			'uid' => $uid,
			'cu_id' => $cu_id,
			'note' => $note,
			'type' => $type,
			'create_time' => time()
		);
		$res12 = Db::name('user_log')->insert($data);
		return $res12;
	}
}