<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Session;
use think\Db;
use think\Cache;
use think\Controller;
use think\Request;

class My extends HomeBase
{
      public function my()
    {
        $home = session('home');
        if($home){
            setcookie("as",1,time()+6000);
            $user = db('user')->where(['mobile'=>$home['mobile']])->find();
            $id = 1;
        }else{
            $id = 2;
        }
        $this->assign('user',$user);
        $this->assign('id',$id);
        return view();
       
    }

    //区块浏览器
    public function browser()
    {
        $home = session('home');
        if($home){
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
    
    
    public function edituser()
    {
        return $this->fetch();
    }

    public function set_pass()
    {
        return $this->fetch();
    }

    public function up_id_card_new()
    {
        return $this->fetch();
    }
    //修改密码
    public function password()
    {
        $ses = Session::get('home');
        $arr = $this->request->post();
        if ($ses['password'] != md5($arr['pwd'].$ses['salt'])) {
            return json_encode(array('msg'=>'原始密码不对请重新输入','flag'=>0));
        }
        unset($arr['userpassword'],$arr['pwd']);
        $arr['password'] = md5($arr['password'].$ses['salt']);
        $id = $ses['id'];

        $info = DB::name('user')->where('id',$id)->update($arr);
        if ($info) {
            Session('home',null);
//            $url = $_SERVER['HTTP_HOST']."/index/my/index";

            return json_encode(array('msg'=>'修改成功','flag'=>1));
        } else {

            return json_encode(array('msg'=>'修改失败','flag'=>0));
        }
    }

}