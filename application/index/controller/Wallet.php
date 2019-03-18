<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;

class Wallet extends HomeBase
{
    public function wallet()
    {
        $user_id = 1;
        $users = $this->users($user_id);
        $user_wallet = $this->user_wallet($user_id);
        $htd_currency = $this->htd_currency();
        // dump($user_wallet);
        $this->assign('htd_currency',$htd_currency);
        $this->assign('user_wallet',$user_wallet);
        $this->assign('user',$users);
        return $this->fetch();
    }

    public function uppic()
    {
        dump($_POST);
    }

    private function htd_currency()
    {
        $htd_currency = db("currency")->select();
        if($htd_currency){
            return $htd_currency;
        }
    }
    //获取用户币种列表
    private function user_wallet($user_id)
    {
        $user_wallet = db('user_wallet')->where(['uid'=>$user_id])->select();
        if($user_wallet){
            return $user_wallet;
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