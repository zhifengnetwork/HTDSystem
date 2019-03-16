<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;

class Login extends HomeBase
{
    public function login()
    {
        return $this->fetch();
    }

    public function register()
    {
        return $this->fetch();
    }

    public function retrieve()
    {
        return $this->fetch();
    }


}