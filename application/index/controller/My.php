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
    //退出登录
   public function index()
    {
        $sess = session('home');
        unset($sess);
        session_destroy();
        $url = "http://".$_SERVER ['HTTP_HOST']."/index";
        
        header("refresh:1;url=$url");
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