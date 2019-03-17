<?php
namespace app\index\controller;

use Captcha\Captcha;
use think\Config;
use think\Controller;
use think\Db;
use think\Session;
use app\index\controller\createWallet;
class Login extends Controller
{//登录成功通过session值判断，如果已经登录自动跳转主页
      public function index(){
            
            if(!empty($_COOKIE['id'])){
        
       $url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
			    header("refresh:1;url=$url");
        
        
        
     else{
            
           return $this->fetch();
    }
    
    }
    //登录
    public function login()
    {
       $arr = $this->request->post();
       $arr['password'] = md5($arr['password']);
       $res = DB::name('user')->where($arr)->find();
       if ($res){
           Session::set('home',$res);
           setcookie("id",$res['id'],time()+60*10);
           $this->success('登录成功!');
		         $url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
			    header("refresh:1;url=$url");
       } else {
           echo "<script>history.go(-1);</script>";
           exit;
       }
    }
//团队
	public function directDrive(){
  
      
        $res = DB::name('user')->where("id",Session::get('home')['id'])->find();
        if($res){
            $ress = DB::name('user')->where("pid",$res['id'])->select();
			$aas=json_encode($ress);
            $this->assign('aa', $aas);
        }else{
            
            $this->assign('aa',0);
            
        }
        return view();
    }

    public function register()
    {
        return $this->fetch();
    }

    public function regis()
    {

        $arr = $this->request->post();
        $users=[
            'promotion'=>['neq',$arr['promotion']],
            'username' => ['eq',$arr['username']],
            'usermail'=>['eq',$arr['usermail']],
            'mobile'=>['eq',$arr['mobile']]
        ];

        $user = Db::name('user')->whereOr($users)->find();
//        var_dump(Db::name('user')->whereOr($users)->getLastSql());
//        exit;
        if ($user){
            echo "<script>history.go(-1);</script>";exit;
        }

        $arr['pid'] = $user['id'];
        var_dump($arr['promotion']);
        $arr['userip'] = $_SERVER['REMOTE_ADDR'];
        $arr['password'] = md5($arr['password']);
        $arr['regtime'] = date('Y-m-d H:i:s',time());
        unset($arr['verify']);

        $res = DB::name('user')->insert($arr);
        if ($res){
//            createWallet($uid);
            echo '成功';
        } else {
            echo "<script>history.go(-1);</script>";
            exit;
        }
    }


    public function retrieve()
    {
        return $this->fetch();
    }


}