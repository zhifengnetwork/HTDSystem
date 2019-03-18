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
        // dump($arr);die;
        $res = DB::name('user')->where(['mobile'=>$arr['phone']])->find();
        $password = md5($arr['pwd'].$res['salt']);
        //这里直接通过返回值给前端，让前端页面实现自己跳转，以下替换
       //前端想要什么类型的传值都在$data添加，前端我做好了传值样式。
        if($res['password'] == $password){
            // dump($res['password']);
            // dump($password);die;
            Session::set('home',$res);
            setcookie("id",$res['id'],time()+60*10);
            $url = "http://".$_SERVER ['HTTP_HOST'];
            $data=array('msg'=>'登陆成功','flag'=>1,'url'=>$url);
            $data=json_encode($data);
            echo $data;
        }else{
            $data=array('msg'=>'账号或密码填写错误!!!','flag'=>2);
           $data=json_encode($data);
           echo $data;
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
        $promotion = input('get/d');
        $this->assign('promotion',$promotion);
        return $this->fetch();
        

        
    }
    
    
    //
    
  //注册
    public function regis()
    {
        $arr = $this->request->post();
        
        if($arr){
           $usermail= $arr['usermail']."";
            $reaa = DB::name('user')->where(['username'=>$arr['username']])->find();
        
            $arr['salt'] = generate_password(18);
            $arr['password'] = md5($arr['password'] . $arr['salt']);
            $reab = DB::name('user')->where(['usermail'=>$usermail])->find();
            $reac = DB::name('user')->where(['mobile'=>$arr['mobile']])->find();

            $resv=DB::name('user')->where(['username'=>$arr['username'],'password'=>$arr['password'],'usermail'=>$arr['usermail'],'mobile'=>$arr['mobile']])->find();


            if($resv){
                $data=array('msg'=>'账号已存在，请转往登录界面','flag'=>1);
            }else if($reaa){
                $data=array('msg'=>'用户名已经存在','flag'=>3);
            }else if($reac){
                $data=array('msg'=>'电话号码已经存在,请重新输入!!!','flag'=>2);
            }else if($reab){
                $data=array('msg'=>'邮箱已经存在','flag'=>4);
            }else if(!$reaa&&!$reab&&!$reac){
                $read=DB::name('user')->where(['promotion'=>$arr['promotion']])->find();
                // dump($reab);die;
                if($read){
                    $arr['pid']=$read['id']; 
                    $arr['promotion'] = byTgNo();
                    // dump($arr);die;
                    DB::name('user')->insert($arr);
                    $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/index";
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