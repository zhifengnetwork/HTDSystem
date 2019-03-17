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
          $home = session('home');
            // dump($home['id']);die;
            if(!empty($home['id'])){
                
				$url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
			    header("refresh:1;url=$url");
			}else{
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
        if($arr){
        $arr['password'] = md5($arr['password']);
        $resa = DB::name('user')->where($arr['mobile'])->find();
        $resb = DB::name('user')->where($arr['username'])->find();
        $resc = DB::name('user')->where($arr['usermail'])->find();
        $res = DB::name('user')->where(['mobile'=>$arr['mobile'],'password'=>$arr['password']])->find();
        $resv=DB::name('user')->where($arr)->find();
        if($res&&$resv){
            
            Session::set('home',$res);
            setcookie("id",$res['id'],time()+60*10);
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
            header("refresh:1;url=$url");
            
            
        }else if($resa&&$res==false){
            
            $data=array('msg'=>'密码错误');
            $data=json_encode($data);
            echo $data;
            
        }else if($resa){
            
            $data=array('msg'=>'账号已经存在');
            $data=json_encode($data);
            echo $data;
            
            exit;
        }else if($resb){
            
            $data=array('msg'=>'用户名已经存在');
            $data=json_encode($data);
            echo $data;
            
            exit;
        }else if($resc){
            
            $data=array('msg'=>'邮箱已经存在');
            $data=json_encode($data);
            echo $data;
            
            exit;
        }
        }
    }


    public function retrieve()
    {
        return $this->fetch();
    }



}