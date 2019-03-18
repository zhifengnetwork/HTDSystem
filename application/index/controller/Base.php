<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Base extends HomeBase
{
    public function ajaxReturn($data = [],$type = 'json'){
        header('Content-Type:application/json; charset=utf-8');
        $data   = !empty($data) ? $data : ['status' => 1, 'msg' => '操作成功'];
        exit(json_encode($data,JSON_UNESCAPED_UNICODE));
    }
}