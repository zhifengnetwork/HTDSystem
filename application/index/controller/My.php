<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;

class My extends HomeBase
{
      public function my()
    {
        
        
        
        $home = session('home');
        if($home){
            setcookie("as",1,time()+6000);
            $user = db('user')->where(['id'=>$home['id']])->find();
            if($user){
                $this->assign('user',$user);
            }
            $id = 1;
        }else{
            $id = 2;
        }
        $this->assign('id',$id);
        return view();
       
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