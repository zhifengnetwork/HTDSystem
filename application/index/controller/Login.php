<?php
namespace app\index\controller;
use Captcha\Captcha;
use think\Config;
use think\Controller;
use think\Db;
use think\Session;
use app\index\controller\createWallet;
class Login extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    //登录
    public function login()
    {
       $arr = $this->request->post();
       $res = DB::name('user')->where($arr)->find();
       if ($res){
           Session::set('home',$res);
           $this->success('登录成功!');
       } else {
           echo "<script>history.go(-1);</script>";
           exit;
       }
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
        var_dump($users);
        $user = Db::name('user')->whereOr($users)->find();

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
}