<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;
use think\Request;
class Walletaddr extends HomeBase
{
    /**
     *  显示钱包地址二维码
     */
    public function showWalletAddr(){
        $walletAddr = input('walletAddr/s');
        if($walletAddr){
            $walletAddr = $walletAddr;
        }else{
            $walletAddr = 'is null';
        }
        $this->assign('walletAddr',$walletAddr);
        return $this->fetch('wallet/addr');
    }

    // public function gets(){

    //     // $arr = ['0'=>123,'1'=>456];
    //     $number = 12312.000;
    //     $ary = explode('.', (string)$number);
    //     $a = count($ary);
    //     if($a==2){
    //         p($ary);

    //     }else{
    //         return $number;
    //     }
    //     // if(!$ary[1]==1){
    //     //     return $number;
    //     // }
    //     // $as = array_key_exists($arr['1'],$arr);
    //     p($ary);
    // }

}