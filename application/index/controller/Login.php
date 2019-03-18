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
      
       //这里直接通过返回值给前端，让前端页面实现自己跳转，以下替换
       //前端想要什么类型的传值都在$data添加
       
       if ($res){
           Session::set('home',$res);
           setcookie("id",$res['id'],time()+60*10);
           $data=array('msg'=>'登陆成功','flag'=>1);
           $data=json_encode($data);
           echo $data;
       } else {
           $data=array('msg'=>'登录失败','flag'=>2);
           $data=json_encode($data);
           echo $data;
           exit;
       }
       /*
       if ($res){
           Session::set('home',$res);
           setcookie("id",$res['id'],time()+60*10);
           $url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
		   header("refresh:1;url=$url");
       } else {
           echo "<script>history.go(-1);</script>";
           exit;
       }*/
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
        dump($promotion);
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
            $arrs['password'] = md5($arr['password']);

      
            $reab = DB::name('user')->where(['usermail'=>$usermail])->find();
            $reac = DB::name('user')->where(['mobile'=>$arr['mobile']])->find();

            $resv=DB::name('user')->where(['username'=>$arr['username'],'password'=>$arrs['password'],'usermail'=>$arr['usermail'],'mobile'=>$arr['mobile']])->find();
            if($resv){
                $data=array('msg'=>'账号已存在，请转往登录界面');
                $data=json_encode($data);
                echo $data;
            }else if($reac){
                $data=array('msg'=>'账号已经存在');
                $data=json_encode($data);
                echo $data;
            }else if($reaa){
                $data=array('msg'=>'用户名已经存在');
                $data=json_encode($data);
                echo $data;
            }else if($reab){
                $data=array('msg'=>'邮箱已经存在');
                $data=json_encode($data);
                echo $data;
            }
            $arr['salt'] = generate_password(18);
            $arr['promotion'] = byTgNo();
            $arr['password'] = md5($arr['password'] . $arr['salt']);
            $arr['regtime'] = time();
            dump($arr);die;
            $res = Db('user') -> insert($arr);
            $res = db('user')->add($arr);
            // else if(!$reaa&&!$reab&&!$reac){
            
            //     //兄弟，我这里能读取数据，却不能写入，写入，下面就不执行，我测试别的写法都能写入，语法也没问题
            //     //
            //     dump($arr);
            //     die;
            //     $res = db('user')->add($arr);
            //     if($res){
            //         $data=array('msg'=>"注册成功",'username'=>$res);
            //         $data=json_encode($data);

            //     }
            // }   
        }
    }


    public function retrieve()
    {
        return $this->fetch();
    }



}