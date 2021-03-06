<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Session;
use think\Db;
use think\Cache;
use think\Controller;
use think\Request;
use think\Exception;

class My extends Base
{
      public function my()
    {
        $home = session('home');

        if($home){
            setcookie("as",1,time()+6000);
            $user = DB::name('user')->where('username',$home['username'])->find();
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
        $user_id = session('home.id');
        $user = Db::name('user')->where('id',$user_id)->field('id,mobile,usermail as email')->find();
        
        $this->assign('info',$user);

        return $this->fetch();
    }

    //修改邮箱或电话号码
    public function change()
    {
        $data = input('post.');
       
        $bool = false;
        $result = array();

        $user = Db::name('user')->where('id',$data['user_id'])->find();

        if (($user['mobile'] == $data['mobile']) && ($user['usermail'] == $data['email'])) {
            return json(array('msg'=>"你没有任何修改", 'code'=>0));
        }

        if ($user['mobile'] != $data['mobile']) {
            $result['mobile'] = $data['mobile'];
        }

        if ($user['usermail'] != $data['email']) {
            $result['usermail'] = $data['email'];
        }

        if ($result) {
            $bool = Db::name('user')->where('id',$data['user_id'])->update($result);
        }

        if ($bool) {
            return json(array('msg'=>"修改成功",'code'=>200));
        } else {
            return json(array('msg'=>"修改失败", 'code'=>0));
        }
    }

    public function set_pass()
    {
        return $this->fetch();
    }

    //显示图片
    public function up_id_card_new()
    {
        $img = $this->get_img();    //获取图片路径
        
        if (!$img) {
            $img[0] = '';
            $img[1] = '';
        }
        
        $this->assign('img',$img);

        return $this->fetch();
    }

    //上传身份证
    public function upload()
    {
        $id = session('home.id');
        $img = input('post.');
        $img = json_decode($img['img'],true);

        $data = array();
        $del_img = array();
        $is_img = false;
        $user_img = $this->get_img();   //获取图片路径

        //判断是否已存在照片
        foreach ($img as $k1 => $v1) {
            foreach ($user_img as $k2 => $v2) {
                if ($v1 == $v2) {
                    $data[$k1] = $v2;
                    unset($img[$k1]);
                    $is_img = true;
                }
            }
        }

        //是否有照片上传,没有直接返回
        if (!$img) {
            return json(array('code'=>0,'msg'=>'没有上传图片'));
        }
        
        foreach ($img as $key => $value) {
            $imageName = md5(mt_rand(0,100000).time()).'.png';  //文件名

            if (strstr($value,",")){
               $image = explode(',',$value);
               $image = $image[1];
            }

            $path = 'uploads'.DS.'id_card'.DS.Date('Ymd');
            if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                mkdir($path,0777,true);
            }

            $imageSrc= $path.DS.$imageName;

            //图片写入文件
            $image = file_put_contents($imageSrc, base64_decode($image));

           if ($image) {
                $data[$key] = DS.$imageSrc;
                $is_img = true;
           }else{
                $is_img = false;
                break;
           }
       }

       //图片路径存到数据库
       if ($is_img) {
            $data = json_encode($data);
            $bool = Db::name('user')->where('id',$id)->update(['idcard_url'=>$data]);
       }

       if ($bool) {
           return json(array('code'=>200,'msg'=>'上传成功'));
       } else{
            return json(array('code'=>0,'msg'=>'上传失败'));
       }
    }

    //获取图片路径
    public function get_img()
    {
        $id = session('home.id');
        $url = Db::name('user')->where('id',$id)->value('idcard_url');
        $url = $url ? json_decode($url,true) : array();

        return $url;
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
            $url = "http://".$_SERVER ['HTTP_HOST'];
            return json_encode(array('msg'=>'修改成功','flag'=>1,'url'=>$url));
        } else {

            return json_encode(array('msg'=>'修改失败','flag'=>0));
        }
    }

}