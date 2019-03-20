<?php
namespace app\index\controller;

use Captcha\Captcha;
use think\Config;
use think\Controller;
use think\Db;
use think\Session;
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
        $res = DB::name('user')->where(['mobile'=>$arr['mobile']])->find();
        // dump($arr);die;
        if($res){
            $password = md5($arr['password'].$res['salt']);
            // dump($res['password']);
            // dump($password);die;
            //这里直接通过返回值给前端，让前端页面实现自己跳转，以下替换
           //前端想要什么类型的传值都在$data添加，前端我做好了传值样式。
            if($res['password'] == $password){
                Session::set('home',$res);
                $url = "http://".$_SERVER ['HTTP_HOST'];
                $data=array('msg'=>'登录成功','flag'=>0,'url'=>$url);
            }
        }else{
            $data=array('msg'=>'账号或密码填写错误!!!','flag'=>2);
        }
        $data = json_encode($data,1);
        echo $data;
       
    }
    //团队
    public function directdrive(){
            
        $home = session('home');

        
    
        $res = DB::name('user')->where(['pid'=>$home['id']])->select();
        if($res){
            // dump($res);die;
            $this->assign('aa', $res);
        }
        
        return view();
    }

    public function register()
    {
        $promotion = input('promotion/d');
        $this->assign('promotion',$promotion);
        return $this->fetch();
    }
    
    
    //
    
  //注册
    public function regis()
    {
        $arr = $this->request->post();
       
        if($arr){
           $usermail= $arr['userEmail']."";
            $reaa = DB::name('user')->where(['username'=>$arr['userName']])->find();
            
            $arr['salt'] = generate_password(18);
            $arr['password'] = md5($arr['password'] . $arr['salt']);
            $reab = DB::name('user')->where(['usermail'=>$usermail])->find();
            $reac = DB::name('user')->where(['mobile'=>$arr['userPhone']])->find();
            $resv=DB::name('user')->where(['username'=>$arr['userName'],'password'=>$arr['password'],'usermail'=>$usermail,'mobile'=>$arr['userPhone']])->find();

            if($resv){
                $data=array('msg'=>'账号已存在，请转往登录界面','flag'=>1);
            }else if($reaa){
                $data=array('msg'=>'用户名已经存在','flag'=>3);
            }else if($reac){
                $data=array('msg'=>'电话号码已经存在,请重新输入!!!','flag'=>2);
            }else if($reab){
                $data=array('msg'=>'邮箱已经存在','flag'=>4);
            }else if(!$reaa&&!$reab&&!$reac){
                $read=DB::name('user')->where(['promotion'=>$arr['rec']])->find();
                
                
                if($read){
                    $data = array(
                        "username"=>$arr['userName'],
                        "password"=>$arr['password'],
                        "usermail"=>$arr['userEmail'],
                        "mobile"=>$arr['userPhone'],
                        "regtime"=>time(),
                        "pid"=>$read['id'],
                        "promotion"=>byTgNo(),
                        "salt"=>$arr['salt']
                    );
                    
                    $res = DB::name('user')->insert($data);
                    // 生成钱包
                    if($res){
                        createWallet($res);
                    }
                    $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/index/";
                    $data=array('msg'=>"注册成功",'flag'=>5,'url'=>$url);
                }else{
                    $data=array('msg'=>"推广码不存在,不能进行注册!!!",'flag'=>6);
                }
            }   
            $data = json_encode($data);
            echo $data;
        }
    }

    public function retrieve()
    {
        return $this->fetch();
    }



}