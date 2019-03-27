<?php
namespace app\index\controller;

use Captcha\Captcha;
use think\Config;
use think\Controller;
use think\Db;
use think\Session;
class Login extends Controller
{//登录成功通过session值判断，如果已经登录自动跳转主页
      public function index()
      {
          $home = session('home');
            // dump($home['id']);die;
            if(!empty($home['id'])){

				$url = "http://".$_SERVER ['HTTP_HOST']."/index/my/my";
			    header("refresh:1;url=$url");
			}else{

				return $this->fetch();
			}
    
    }


    public function captcha()
    {
        $m = new Captcha(Config::get('captcha'));

        $img = $m->entry();
        return $img;
    }
   //登录
    public function login()
    {
        $site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
        $yzmarr = explode(',', $site_config['site_yzm']);
        if (in_array(4, $yzmarr)) {
            $yzm = 1;
        } else {
            $yzm = 0;
        }
        $arr = $this->request->post();
        $data = $this->request->only( 'verify');
        if ($yzm == 1) {
            if (!captcha_check($data['verify'])) {
                return json(array('flag' => 1, 'msg' => '验证码错误'));
            }
        }
        $res = DB::name('user')->where(['username'=>$arr['username']])->find();
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
            }else{
                $data=array('msg'=>'密码填写错误!!!','flag'=>2);
            }
        }else{
            $data=array('msg'=>'用户名填写错误!!!','flag'=>2);
        }
        $data = json_encode($data,1);
        echo $data;
       
    }
    /*
    *   我的团队所有币种本金统计,不包括自己
    *   对应币种数量乘以当前币种价格
     */
    public function directdrive(){
            
        $home = session('home');
        $configs = Db::name('income_config')->field('id,name,value')->where('name','in',['exchange_usd'])->select();
        $configs = arr2name($configs);
        $usd = $configs['exchange_usd']['value'];
        $res = '';    
        // 所有直推用户
        $res = DB::name('user')->where(['pid'=>$home['id']])->select();
        $users = getDownUserUids2($home['id']);
        unset($GLOBALS['g_down_Uids']);
        $money = 0;
        if($users){
            foreach ($users as $key=>$val)
            {
                $wallet = $this->user_wallet($val);
                $money += $wallet;
            }
        }
        if($res){
            $this->assign('aa', $res);
        }
        if(!$money){
            $total_money = 0;
        }else{
            $total_money = $money/$usd;
        }
        $this->assign('money', $total_money);
        $this->assign('aa', $res);
        return view();
    }

    // 获取用户所有币种钱包记录,并且对应币种数量乘当前币种价格
    private function user_wallet($uid){
        $arr = Db::name('user_wallet')->field('id,uid,cu_id,cu_num')->where(['uid'=>$uid])->select();
        $cur = Db::name('currency')->field('id,price')->select();
        $money = 0;
        foreach($arr as $key=>$val)
        {
            foreach($cur as $k => $v){
                // 比对币种
                if($val['cu_id']==$v['id']){
                    $ftp = $val['cu_num']*$v['price'];
                    $money += $ftp;
                }
            }
        }
        if(!$money){
            return 0;
        }
        return $money;
    }

    private function currency($id)
    {
        $cur = db('currency')->where(['id'=>$id])->select();
        if($cur){
            return $cur['price'];
        }
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
        // p($arr);die;
        if($arr){
            $usermail= $arr['userEmail']."";
            $reaa = DB::name('user')->where(['username'=>$arr['userName']])->find();
            $arr['salt'] = generate_password(18);
            $arr['password'] = md5($arr['password'] . $arr['salt']);
            if($usermail){
                $reab = DB::name('user')->where(['usermail'=>$usermail])->find();
                $data['usermail'] = $arr['userEmail'];
            }else{
                $reab = false;
            }
            $resv=DB::name('user')->where(['username'=>$arr['userName'],'password'=>$arr['password'],'usermail'=>$usermail,'mobile'=>$arr['userPhone']])->find();
            if(!$arr['verify']){
                $data=array('msg'=>"验证码不可为空",'flag'=>5);
            }
            if(!$arr['userPhone']){
                $data=array('msg'=>"手机号不可为空",'flag'=>5);
            }
            $checkData['sms_type'] = $arr['sms_type'];
            $checkData['code'] = $arr['verify'];
            $checkData['phone'] = $arr['userPhone'];
            $res = checkPhoneCode($checkData);
            if($res['code']==0){
                return array('code' => 0, 'msg' => $res['msg']);
            }
            if($resv){
                $data=array('msg'=>'账号已存在，请转往登录界面','flag'=>1);
            }else if($reaa){
                $data=array('msg'=>'用户名已经存在','flag'=>3);
            }else if($reab){
                $data=array('msg'=>'邮箱已经存在','flag'=>4);
            }else if(!$reaa){
                $read=DB::name('user')->where(['promotion'=>$arr['rec']])->find();
                if($read){
                    $data = array(
                        "username"=>$arr['userName'],
                        "password"=>$arr['password'],
                        "mobile"=>$arr['userPhone'],
                        "regtime"=>time(),
                        "pid"=>$read['id'],
                        "promotion"=>byTgNo(),
                        "salt"=>$arr['salt']
                    );
                    $res = DB::name('user')->insert($data);
                    // 生成钱包
                    if($res){
                        $userId = Db::name('user')->getLastInsID();
                        $in_res = createWallet($userId);
                        $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/index/";
                        $data=array('msg'=>"注册成功",'flag'=>5,'url'=>$url);
                    }else{
                        $data=array('msg'=>"注册失败",'flag'=>5);
                    }
                   
                }else{
                    $data=array('msg'=>"推广码不存在,不能进行注册!!!",'flag'=>6);
                }
            }   
            $data = json_encode($data);
            echo $data;
        }
    }
    //忘记密码
    public function retrieve()
    {
        return $this->fetch();
    }
    //处理忘记密码
    public function retrie()
    {
        $arr = $this->request->post();
        $ress = Db::name('user')->where('username',$arr['username'])->find();
        if (!$ress){
            return json_encode(array('msg'=>'没有此用户名，请确定后重新输入','code'=>1));
        }
        if ($arr['mobile'] != $ress['mobile']) {
            return json_encode(array('msg'=>'用户名绑定的手机号不一致','code'=>2));
        }
        $checkData['sms_type'] = 3;
        $checkData['code'] = $arr['verify'];
        $checkData['phone'] = $arr['mobile'];
        $res = checkPhoneCode($checkData);
        if($res['code']==0){
            return json($res);
            
        }
        $arr['password'] = md5($arr['password'] . $ress['salt']);
        $info = Db::name('user')->where('id',$ress['id'])->update(['password'=>$arr['password']]);

        if ($info){
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/index/";
            return json_encode(array('msg'=>'修改成功','code'=>4,'url'=>$url));
        } else {
            return json_encode(array('msg'=>'修改失败','code'=>3));

        }
    }

    /**
     * 获取手机验证码
     * @param $sms_type int
     * @param $phone string
    */
    public function getPhoneVerify(){

        // 传入类型：1注册 2提币；3找回密码 
        $param = input('post.');
        $sms_type = intval($param['sms_type']);
        if(!$sms_type || !$param['phone']){
            return json(array('code' => 0, 'msg' => '缺少参数'));
        }
        $data = ['sms_type'=>$sms_type, 'phone'=>$param['phone']];
        $res = getPhoneCode($data);
        return json($res);
        // p($res);
    }

}