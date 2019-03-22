<?php
namespace app\index\validate;

use think\Validate;

class Index extends Validate
{
    protected $rule = [
       'number'         => 'require|gt:0|checkNums',
    //    'number'        => ['require','gt:0',^[0-9]{1,11}([.][0-9]{1,8})?$],
       'qrcode_addr'   => 'require',
       'wallet_addr'   => 'require'
    ];
    protected $message = [
        'number.require'  => '请输入提币数量',
        'number.gt'     => '提币数量最少1个',
        // 'number.number'  => '请输入正确数字',
        // 'number.float'  => '请输入正确数字1',
        'qrcode_addr.require'  => '请上传二维码',
        'wallet_addr.require'  => '请输入钱包地址'
    ];
    protected function checkNums($value){
        //匹配正则表达式函数       
        if(preg_match('/^[0-9]{1,11}([.][0-9]{1,8})?$/',$value)){
                    return true;
               }else{
                    return  '输入有误';   
               }      
    }       
        
    
    protected $scene = [
    		// 'passwordedit'  =>  ['password','confirm_password'],
    ];
    
}