<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use app\index\controller\Base;
use think\Cache;
use think\Controller;
use think\Db;
use think\Config;
use think\Request;
use think\Session;
use \think\Loader;
use Captcha\Captcha;
use app\index\validate\Index as Indexv;
class Index extends HomeBase
{
    protected $site_config;

    public function _initialize()
    {
        parent::_initialize();
        $home = session('home');
        if(!$home['id']){
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/index";
            header("refresh:1;url=$url");
        }
        if (CBOPEN == 2) {
            $this->redirect(url('bbs/index/index'));
        }

        $this->site_config = Cache::get('site_config');
    }
    
    public function my_message()
    {
    	return view();
    }
    
    public function news_details()
    {
    	return view();
    }

    public function index()
    {

        $user = session('home');
        if($user){
            $id = 1;
        }else{
            $id = 2;
        }
        $this->assign('id',$id);
        $stock_rights_money = Db::name('user')->field('id,stock_rights')->where(['id'=>$user['id']])->find();
        $this->assign('stock_rights',$stock_rights_money['stock_rights']);
        return view();
    }
	
    //提币页面
    public function present(){

        if (!session('home.id')) {
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/";
            header("refresh:1;url=$url");
        }      
        $userid = session('home.id');
        // $phone=session('home.mobile');
        $list = Db::table('htd_user_wallet')
                ->alias('a')
                ->join('htd_currency c', 'c.id=a.cu_id')
                ->where('uid',$userid)
                ->select();
                // dump($list);

        $exchange_usd = Db::name('income_config')->field('name,value')->where('name','withdraw_min')->select();
        $exchange_usd = arr2name($exchange_usd);
        $withdraw_min = $exchange_usd['withdraw_min']['value'];

        $this->assign('withdraw_min',$withdraw_min);
        $this->assign('list',$list);
        $this->assign('phone',session('home.mobile'));
        $this->assign('uid',$userid);
        // $captcha = new Captcha();
        // $captcha->entry();
        return $this->fetch();        
    } 


    public function captcha()
    {
        $m = new Captcha(Config::get('captcha'));

        $img = $m->entry();
        return $img;
    }

    // 点击提取按钮
    public function ajaxsend(){
           $data = input();
        //    dump($data);exit;
           $res = Db::name('user_wallet')->where($data)->find();
           $base = new Base();
           if($res){            
            $base->ajaxReturn(['status' => 1, 'msg' =>'数据获取成功', 'result' =>$res]);
           }else{
            $base->ajaxReturn(['status' => 0, 'msg' =>'数据获取失败']);
           }          
    }    

    // 货币汇率
    public function exchange(){
              $data   = input();
              $result = Db::table('htd_currency')->where('id',$data['cu_id'])->value('price');
              $rmb    = $data['val']*$result;
              //美元汇率   
              $exchange_usd = Db::name('income_config')->field('name,value')->where('name','exchange_usd')->select();
              $exchange_usd = arr2name($exchange_usd);
              $usd    = $rmb*$exchange_usd['exchange_usd']['value'];

              $base = new Base();
              if($result){
                $base->ajaxReturn(['status' => 1, 'msg' =>'数据获取成功', 'result' =>['usd'=>$usd]]);
              }else{
                $base->ajaxReturn(['status' => 0, 'msg' =>'数据获取失败', 'result' =>'']);
              }              
    }

    // 提币
    public function pick(){
            $data       = input();
            // dump($data);exit;
            // dump($data);
            // exit;
            $validate   = new Indexv();

            // $validate->rule('zip', '/^\d{6}$/');
            // $validate->rule([
            //     'number'   => '^[0-9]{1,11}([.][0-9]{1,8})?$',
            // ]);
            $base       = new Base();
            // if(!$data['verify']){
            //     $base->ajaxReturn(['status' => 0, 'msg' =>'请输入验证码', 'result' =>'']); 
            // }

            // $checkData['sms_type'] = $data['sms_type'];
            // $checkData['code'] = $data['verify'];

            // $checkData['phone'] =  session('home.mobile');
            // //  session('home.mobile');
              
            // $res = checkPhoneCode($checkData);
            // if($res['code']==0){
            //     $base->ajaxReturn(['status' => 0, 'msg' =>$res['msg']]); 
            // }        
            if(!$validate->check($data)){
                $msg = $validate->getError();
                $base->ajaxReturn(['status' => 0, 'msg' =>$msg, 'result' =>'']);
            }
            
            //美元汇率   
            $exchange_usd = Db::name('income_config')->field('name,value')->where('name','in',['exchange_usd','withdraw_min'])->select();
            $exchange_usd = arr2name($exchange_usd);
            $usd = $data['popover_convert'];
            // 提币最低金额
            $withdraw_min = $exchange_usd['withdraw_min']['value'];
           
            switch ($data['type'])
            {
              case 0:
                $base->ajaxReturn(['status' => 0, 'msg' =>'请选择提币类型', 'result' =>'']);
                
              break;  
              case 1:
                $data['cu_num'] = $data['cu_num'] ;
                $cu_type = 'cu_num';
              break;
              case 2:
                $data['cu_num'] = $data['bonus_wallet'] ;
                $cu_type = 'bonus_wallet';
              break;
              case 3:
                $data['cu_num'] = $data['rate_wallet'] ;
                $cu_type = 'rate_wallet';
              break;                                          
            }
            // dump($cu_type);
            // $res = Db::table('htd_user_wallet')->where($where)->update(['cu_num' => $data['remain_num']]);
            if($usd<$withdraw_min&&$data['type']!=1){
                $base->ajaxReturn(['status' => 0, 'msg' =>'货币大于等于50美元才能体现', 'result' =>'']);        
            }else if($data['cu_num']<$data['number']){
                $base->ajaxReturn(['status' => 0, 'msg' =>'货币剩余少于输入值', 'result' =>'']);
            }
            // else if(empty($data['qrcode_addr'])){
            //     $base->ajaxReturn(['status' => 0, 'msg' =>'请选择图片', 'result' =>'']);
            // }
            Db::startTrans();
            // 用于更新数据
            $where  = array('uid'=>$data['uid'],'cu_id'=> $data['cu_id']);
            // 计算手续费
            $res = $data['cu_num']-$data['number'];
           
            // 如果为本金，手续费为5%，其他则为1%
            if($cu_type === 'cu_num'){
              
                $charge = numberByRetain($data['number']/100*5, 8);
            
                try{
                    // $can = $data['cu_num']-$charge;
                    $where1 = [
                        'uid'      => $data['uid'],
                        'cu_id'    => $data['cu_id'],
                        'cu_num'   => $data['number'],
                        // 'cu_num'   => $data['number'],
                        'tb_charge'=> $charge,
                        'note'     => $data['note'],
                        // 'qrcode_addr' => $data['number'],    
                    ];      
                    
                    // $subtract = $data['number']+$charge;
                    Db::table('htd_user_wallet')->where($where)->setDec($cu_type,$data['number']);
                    // update([$cu_type => 0]);
                    // 减掉相应数量
                    Db::name('execute_order')->where($where)->setDec('num',$data['number']);
                    // 用于插入数据
                    Db::table('htd_user_extract')->insert($where1);
                    // $suc_money = session('home');                                    
                    // 提交事务
                    Db::commit();
                    // dump(session('home.username'));
                    // $u_name = session('home.username');
                    $suc_data = [
                        'suc_name'  => session('home.username'),
                        'su_num'=> $data['number'],
                        'su_time'   => time(),
                        'su_charge' => $charge 
                    ];
                    $base->ajaxReturn(['status' => 1, 'msg' =>'操作成功', 'result' => $suc_data]);    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    $base->ajaxReturn(['status' => 0, 'msg' =>$e->getMessage(), 'result' =>'']);
                }    

            }else{
                $charge = numberByRetain($data['number']/100, 8);              
                $where1 = [
                    'uid'      => $data['uid'],
                    'cu_id'    => $data['cu_id'],
                    // 提币数量
                    'cu_num'   => $data['number'],
                    'tb_charge'=> $charge,
                    'note'     => $data['note'],
                    'qrcode_addr' => $data['qrcode_addr'],    
                ];                                 

                try{
                    Db::table('htd_user_wallet')->where($where)->setDec($cu_type,$data['number']);
                    Db::name('execute_order')->where($where)->setDec('num',$data['number']);
                    Db::table('htd_user_extract')->insert($where1);
                    // 提交事务
                    Db::commit(); 
                    $base->ajaxReturn(['status' => 1, 'msg' =>'操作成功', 'result' =>'']);    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    $base->ajaxReturn(['status' => 0, 'msg' =>'操作失败', 'result' =>'']);
                }
            }                                           
    }


    public function getPhoneVerify(){
        /**
        * 获取手机验证码
        * @param $sms_type int
        * @param $phone string
        */

        // 传入类型：1注册 2提币；手机号
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
    
    public function upload(){
        $base64 = input('post.dataImg');
        $res = uploadImg($base64);
        return $res;
    }    


    //总收益
    public function totalrevenue()
    {   
        $home = session('home');
        if(empty($home)){
            return $this->error('亲！要先登录才能进行查看!!!', 'index/login/index');
        }
        $income = db('income')->where(['uid'=>$home['id']])->select();
        // dump($home['id']);die;
        if(empty($income)){
            $this->assign('income',$income);
        }
        $this->assign('income',$income);
        return view();
    }

    //今日收益
    public function dayrevenue()
    {   
        $home = session('home');
        if(empty($home)){
            return $this->error('亲！要先登录才能进行查看!!!', 'index/login/index');
        }
        //当天开始时间
        $start_time=strtotime(date("Y-m-d",time()));
        
        //当天结束之间
        $end_time=$start_time+60*60*24;
        $income = db('income')->whereTime('create_time','today')->where("uid = '".$home['id']."'")->select();
        if($income){
            $this->assign('income',$income);
        }
        $this->assign('income',$income);
        return view();
    }
    
    //分享
    public function qrcode(){
        $home = session('home');
        if(empty($home)){
            return $this->redirect('index/login/index');
        }
        if($home){
            $id = $home['id'];
            $promotion = DB::name('user')->where('id',$id)->value('promotion');
            $data = array(
                'code' => $promotion,
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/index/login/register?promotion='.$promotion
            );
        }
        $this->assign('data',$data);
        return view();
    }
	

    public function search()
    {
        $ks  = input('ks');
        $kss = urldecode(input('ks'));
        if (empty($ks) || $kss == ' ') {
            return $this->error('亲！你没有输入关键字');
        } else {
            $article      = Db::name('article');
            $open['open'] = 1;

            $map['f.title|f.keywords|f.description|f.content'] = ['like', "%{$kss}%"];

            $tptc = $article->alias('f')->join('articlecate c', 'c.id=f.tid')->join('user m', 'm.id=f.uid')->field('f.*,c.id as cid,m.id as userid,m.userhead,m.username,c.name,c.template')->order('f.id desc')->where($open)->where($map)->paginate(5, false, $config = ['query' => array('ks' => $ks)]);
            $this->assign('tptc', $tptc);
            return view();
        }
    }

    public function errors()
    {
        return view();
    }

    //钱包余额
    public function money()
    {
        $home = session('home');
        if(empty($home)){
            return $this->error('亲！要先登录才能进行查看!!!', 'index/login/index');
        }
        
        $income = db('user_wallet')->where("uid = '".$home['id']."'")->select();
        $moeny =0;
        $cu =[];
        foreach($income as $k => $v){
            $cu[$k]['cu_id']= $v['cu_id'];

            $moeny +=$v['cu_num']+$v['bonus_wallet']+$v['rate_wallet'];
            $cu[$k]['bonus_wallet'] = $moeny;
        };
        if($cu){
            $this->assign('income',$cu);
        }
        $this->assign('income',$cu);
        return view();
    }

    public function article()
    {
        $id = is_number(input('id')) ? input('id') : '';

        if (empty($id)) {
            return $this->error('亲！你迷路了', 'index/index/index');
        } else {
            $article = Db::name('article');
            $a       = $article->where('open', 1)->find($id);
            if ($a) {
                if ($a['outlink']) {
                    $this->success('正在跳转到外部页面', $a['outlink'], null, 1);
                }

                $article->where("id", $id)->setInc('view', 1);
                $t = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->join('user m', 'm.id=a.uid')->field('a.*,c.id as cid,c.name,c.template,c.alias,m.id as userid,m.grades,m.point,m.userhead,m.username,m.status')->where('a.id', $id)->find();
                $this->assign('t', $t);
                //阅读排行
                $artphb = $article->where('tid', $t['tid'])->order('view desc')->limit($this->site_config['c_list_phb'])->select();
                $this->assign('artphb', $artphb);
                //文章推荐
                $choice['tid']    = $t['tid'];
                $choice['choice'] = 1;
                $artchoice        = $article->where($choice)->order('id desc')->limit($this->site_config['c_view_main'])->select();
                $this->assign('artchoice', $artchoice);

                //查询当前用户是否收藏该文章
                $iscollect  = 0;
                $commentzan = array();
                $uid        = session('userid');
                if ($uid) {
                    $collect = Db::name('collect')->where(array('uid' => $uid, 'sid' => $id, 'type' => 3))->find();
                    if ($collect) {
                        $iscollect = 1;
                    }
                    //查询用户点赞过的文章评论
                    $commentzan = Db::name('zan')->where(array('uid' => $uid, 'type' => 3))->column('sid');

                }
                //评论
                $tptc = Db::name('artcomment')->alias('c')->join('user m', 'm.id=c.uid')->where("fid = {$id}")->order('c.id asc')->field('c.*,m.id as userid,m.grades,m.attestation,m.point,m.userhead,m.username')->paginate(10, false, ['query' => Request::instance()->param()]);

                $this->assign('tptc', $tptc);

                $this->assign('iscollect', $iscollect);
                $this->assign('commentzan', $commentzan);

                return view();
            } else {
                return $this->error('亲！你迷路了', 'index/index/index');
            }
        }
    }
    public function soft()
    {
        $id = is_number(input('id')) ? input('id') : '';
        if (empty($id)) {
            return $this->error('亲！你迷路了', 'index/index/index');
        } else {
            $article = Db::name('article');
            $a       = $article->where('open', 1)->where("id = {$id}")->find();
            if ($a) {
                $article->where("id = {$id}")->setInc('view', 1);
                $t = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->join('user m', 'm.id=a.uid')->field('a.*,c.id as cid,c.name,c.template,c.alias,m.id as userid,m.grades,m.point,m.userhead,m.username,m.sex,m.status')->where('a.id', $id)->find();
                //阅读排行
                $artphb = $article->where("tid = {$t['tid']}")->order('view desc')->limit($this->site_config['c_view_phb'])->select();
                $this->assign('artphb', $artphb);
                $this->assign('t', $t);

                //查询当前用户是否收藏该文章
                $iscollect  = 0;
                $commentzan = array();
                $uid        = session('userid');
                if ($uid) {
                    $collect = Db::name('collect')->where(array('uid' => $uid, 'sid' => $id, 'type' => 3))->find();
                    if ($collect) {
                        $iscollect = 1;
                    }
                    //查询用户点赞过的文章评论
                    $commentzan = Db::name('zan')->where(array('uid' => $uid, 'type' => 3))->column('sid');

                }
                //评论
                $tptc = Db::name('artcomment')->alias('c')->join('user m', 'm.id=c.uid')->where("fid = {$id}")->order('c.id asc')->field('c.*,m.id as userid,m.grades,m.attestation,m.point,m.userhead,m.username')->paginate(10, false, ['query' => Request::instance()->param()]);

                $this->assign('tptc', $tptc);
                $this->assign('iscollect', $iscollect);
                $this->assign('commentzan', $commentzan);
                return view();
            } else {
                return $this->error('亲！你迷路了', 'index/index/index');
            }
        }
    }
    public function page()
    {
        $id = is_number(input('id')) ? input('id') : '';
        if (empty($id)) {
            return $this->error('亲！你迷路了', 'index/index/index');
        } else {
            $article = Db::name('article');
            $a       = $article->where('open', 1)->where("id = {$id}")->find();
            if ($a) {
                $article->where("id = {$id}")->setInc('view', 1);
                $t = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->join('user m', 'm.id=a.uid')->field('a.*,c.id as cid,c.name,c.template,c.alias,m.id as userid,m.grades,m.point,m.userhead,m.username,m.sex,m.status')->where('a.id', $id)->find();
               // print_r($t);
                $this->assign('t', $t);
            }

            return view();
        }
    }
}
