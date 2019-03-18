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
            setcookie("id",1,time()+60000);
            $this->assign('ss',1);
        
        }else{
            $this->assign('ss',2);
                setcookie("id",2,time()+60000);
                
            }
        
        $user = db('user')->where(['id'=>$home['id']])->find();
        $this->assign('user',$user);
        
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