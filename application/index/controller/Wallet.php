<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;

class Wallet extends HomeBase
{
    public function wallet()
    {
        return $this->fetch();
    }
}