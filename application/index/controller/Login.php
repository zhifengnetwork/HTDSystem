<?php
namespace app\index\controller;

use Captcha\Captcha;
use think\Config;
use think\Controller;
use think\Db;
use think\Session;
use app\index\controller\createWallet;
class Login extends Controller
<<<<<<< HEAD
{
=======
{//登录成功通过session值判断，如果已经登录自动跳转主页
>>>>>>> b9e2a5f11a8d88fb917fcacb5d0ad6cc062cfdab
      public function index(){
          $home = session('home');
            // dump($home['id']);die;
            if(!empty($home['id'])){
                
				$url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
			    header("refresh:1;url=$url");
			}else{
				return $this->fetch();
<<<<<<< HEAD
            }
=======
			}
>>>>>>> b9e2a5f11a8d88fb917fcacb5d0ad6cc062cfdab
    
    }
    //登录
    public function login()
    {
        $arr = $this->request->post();
        
       $arr['password'] = md5($arr['password']);
       $res = DB::name('user')->where($arr)->find();
       if ($res){
           Session::set('home',$res);
<<<<<<< HEAD

=======
>>>>>>> b9e2a5f11a8d88fb917fcacb5d0ad6cc062cfdab
           setcookie("id",$res['id'],time()+60*10);
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
    //注册
    public function regis()
    {
        $arr = $this->request->post();
        $users=[
            'username' => $arr['username'],
            'usermail'=>$arr['usermail'],
            'mobile'=>$arr['mobile']
        ];

        $user = Db::name('user')->whereOr($users)->find();
        if ($user){
            echo "<script>history.go(-1);</script>";exit;
        }

        $users = Db::name('user')->where('promotion',$arr['promotion'])->find();
        if(!$users){
             echo "<script>history.go(-1);</script>";exit;
        }
        $arr['pid'] = $users['id'];
        $arr['userip'] = $_SERVER['REMOTE_ADDR'];
        $arr['password'] = md5($arr['password']);
        $arr['regtime'] = date('Y-m-d H:i:s',time());
        unset($arr['verify']);

        $res = DB::name('user')->insert($arr);
        if ($res){
//          createWallet($uid);
            return $this->fetch('index');
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