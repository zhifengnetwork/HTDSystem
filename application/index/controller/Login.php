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
         $dataa['salt'] = generate_password(18);
       $arr['password'] = md5($arr['password'] . $dataa['salt']);
       $res = DB::name('user')->where($arr)->find();
      
       //这里直接通过返回值给前端，让前端页面实现自己跳转，以下替换
       //前端想要什么类型的传值都在$data添加，前端我做好了传值样式。
       
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
       
       $dataa['salt'] = generate_password(18);
       $arr['password'] = md5($arr['password'] . $dataa['salt']);
        //$arrs['password'] = md5($arr['password']);

      
        $reab = DB::name('user')->where(['usermail'=>$usermail])->find();
        $reac = DB::name('user')->where(['mobile'=>$arr['mobile']])->find();

 $resv=DB::name('user')->where(['username'=>$arr['username'],'password'=>$arr['password'],'usermail'=>$arr['usermail'],'mobile'=>$arr['mobile']])->find();


 if($resv){
            
         //Session::set('home',$resv);
            //setcookie("id",$resv['id'],time()+60*10);
            
     
     
     $data=array('msg'=>'账号已存在，请转往登录界面','flag'=>1);
            $data=json_encode($data);
            echo $data;
            
       
           // exit;
            
        }else if($reac){
            
            $data=array('msg'=>'账号已经存在','flag'=>2);
       $data=json_encode($data);
            echo $data;
         // exit;
       
        }else if($reaa){
            
            $data=array('msg'=>'用户名已经存在','flag'=>3);
         $data=json_encode($data);
            echo $data;
       ///   exit;
    
        }else if($reab){
            
            $data=array('msg'=>'邮箱已经存在','flag'=>4);
         $data=json_encode($data);
            echo $data;
           
         ///  exit;
        }else if(!$reaa&&!$reab&&!$reac){
          
          
            $read=DB::name('user')->where(['promotion'=>$arr['promotion']])->find();
            if($read){
            
            $arr['pid']=$read['id']; 
            
 
          DB::name('user')->insert($arr);
            $data=array('msg'=>"注册成功",'flag'=>5);
            $data=json_encode($data);
            echo $data;
        }
         //   exit;
         
        
        }   
        
        
         
        }
        
       
    }

    public function retrieve()
    {
        return $this->fetch();
    }



}