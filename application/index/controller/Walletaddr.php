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

    /**
     * base64图片上传
     * @param $base64_img
     * @return array
     */
    public function uploadImg(){
                
        $base64 = input('post.dataImg');
        $res = uploadImg($base64);
        return $res;
    }
}