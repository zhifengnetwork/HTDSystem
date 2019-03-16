<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;

class My extends HomeBase
{
    public function my()
    {
        return $this->fetch();
    }

    public function editUser()
    {
        return $this->fetch();
    }

    public function setPass()
    {
        return $this->fetch();
    }

    public function upIDCardNew()
    {
        return $this->fetch();
    }

}