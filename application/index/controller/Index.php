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
            exit;
        }
        // if (CBOPEN == 2) {
        //     $this->redirect(url('bbs/index/index'));
        // }

        // $this->site_config = Cache::get('site_config');
    }
    
    public function my_message()
    {
        $res = Db::name('article')->where('open','1')->order('settop DESC','updatetime DESC')->select();
        $this->assign('res',$res);

        return view();


    }
    
    public function news_details($id)
    {
        $res = Db::name('article')->where('id',$id)->find();
        $this->assign('res',$res);
        return view();
    }

    public function index()
    {
        $home = session('home');
        if(!$home['id']){
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/index";
            header("refresh:1;url=$url");
            exit;
        }
        if($home){
            $id = 1;
        }else{
            $id = 2;
        }
        $this->assign('id',$id);

        $money = 0;
        $res = Db::name('article')->where('open','1')->order('settop DESC','updatetime DESC')->find();
        $this->assign('res',$res);
        $this->assign('money',$money);


        return view();
    }
	
    //提币页面
    public function present(){

        if (!session('home.id')) {
            $url = "http://".$_SERVER ['HTTP_HOST']."/index/login/";
            header("refresh:1;url=$url");
        }      
        $userid = session('home.id');
        $list = Db::table('htd_user_wallet')
                ->alias('a')
                ->join('htd_currency c', 'c.id=a.cu_id')
                ->where('uid',$userid)
                ->select();

        $exchange_usd = Db::name('income_config')->field('name,value')->where('name','in',['exchange_usd','withdraw_min','global_min'])->select();
        $exchange_usd = arr2name($exchange_usd);
        $withdraw_min = $exchange_usd['withdraw_min']['value'];
        $global_min = $exchange_usd['global_min']['value'];
        $this->assign('withdraw_min',$withdraw_min);
        $this->assign('global_min',$global_min);
        $this->assign('list',$list);
        $this->assign('phone',session('home.mobile'));
        $this->assign('uid',$userid);
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
           $res = Db::name('user_wallet')->where($data)->find();
           $base = new Base();
           if($res){            
            $base->ajaxReturn(['status' => 1, 'msg' =>'数据获取成功', 'result' =>$res]);
           }else{
            $base->ajaxReturn(['status' => 0, 'msg' =>'数据获取失败']);
           }          
    }    

    // 人民币转美金
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

    // 用户提币(本金、收益、分红)
    public function pick(){

        if(!is_post()){
            return json(array('status' => 0, 'msg' => '提交方式错误'));
        }
        $data = input('post.');
        $data['cu_id'] = intval($data['cu_id']);
        $data['type'] = intval($data['type']); // 提币类型
        $sms_type = 2; // 提币验证码类型
        $data['uid'] = session('home.id');
        // htd id=11不可以操作
        if($data['cu_id']==11){
            return json(array('status' => 0, 'msg' => '当前币种不可操作'));
        }
        if($data['type']>3 || $data['type']<1){
            return json(array('status' => 0, 'msg' => '类型错误..'));
        }

        if($data['type'] == 1){ // 本金
            $data['cu_num'] = $data['cu_num'] ;
            $cu_type = 'cu_num';
            $flag = '本金';
            $type = 901;
        }elseif($data['type'] == 2){ // 收益
            // $data['cu_num'] = $data['bonus_wallet'] ;
            $cu_type = 'bonus_wallet';
            $flag = '收益';
            $type = 902;
        }elseif($data['type'] == 3){ // 分红
            // $data['cu_num'] = $data['rate_wallet'] ;
            $cu_type = 'rate_wallet';
            $flag = '分红';
            $type = 903;
        }else{
            return json(array('status' => 0, 'msg' => '提币类型错误,请刷新页面', 'result' => ''));
        }

        if(!$data['cu_id']){
            return json(array('status' => 0, 'msg' => '币种ID出错', 'result' => ''));
        }
        // 判断币种是否存在以及状态是否正常
        $currency_one = Db::name('currency')->where(['id'=>$data['cu_id']])->find();
        if(!$currency_one){
            return json(array('status' => 0, 'msg' => '当前币种不存在', 'result' => ''));
        }
        if(!$currency_one['status']){
            return json(array('status' => 0, 'msg' => '当前币种交易暂停', 'result' => ''));
        }

        // 判断数量
        if(!$data['cu_num']){
            return json(array('status' => 0, 'msg' => '数量不可小于0', 'result' => ''));
        }
        if(!is_numeric($data['cu_num'])){
            return json(array('status' => 0, 'msg' => '请输入正确数量', 'result' => ''));
        }

        // 钱包地址判断
        if(!$data['wallet_addr']){
            return json(array('status' => 0, 'msg' => '钱包地址不可为空', 'result' => ''));
        }
        if(!$data['verify']){
            return json(array('status' => 0, 'msg' => '请输入验证码', 'result' => ''));
        }
        // 获取当前用户的币种钱包
        $user_wallet = Db::name('user_wallet')->where(['uid'=>$data['uid'], 'cu_id'=>$data['cu_id'] ])->find();
        if(!$user_wallet){
            return json(array('status' => 0, 'msg' => '当前币种钱包不存在', 'result' => ''));
        }

        // 获取美元汇率换算
        $exchange_usd = Db::name('income_config')->field('name,value')->where('name','in',['exchange_usd','withdraw_min','global_min'])->select();
        $exchange_usd = arr2name($exchange_usd);
        // 判断当前提币金额是否达到后台设置的条件
        $usd_total = $data['cu_num']*$currency_one['price']/$exchange_usd['exchange_usd']['value'];
        $usd = $usd_total;
        
        // 收益提币最低金额
        $withdraw_min = $exchange_usd['withdraw_min']['value'];

        // 判断钱包数量是否足够
        if($data['type']==1){
            // 判断本金
            if($user_wallet['cu_num'] < $data['cu_num']){
                return json(array('status' => 0, 'msg' => '不可大于可提本金', 'result' => ''));
            }
        }
        if($data['type']==2){
            // 判断收益
            if($user_wallet['bonus_wallet'] < $data['cu_num']){
                return json(array('status' => 0, 'msg' => '不可大于可提收益', 'result' => ''));
            }
            // 判断后台设置
            $min_usd = $data['cu_num']*$currency_one['price']/$exchange_usd['exchange_usd']['value'];
            if($min_usd < $exchange_usd['withdraw_min']['value']){
                // 计算最低提币数量
                $min_num = $exchange_usd['withdraw_min']['value']/$currency_one['price'];
                $min_num = numberByRetain($min_num, 8);
                return json(array('status' => 0, 'msg' => '最低提币数量'.$min_num));
            }
        }
        if($data['type']==3){
            // 判断分红
            if($user_wallet['rate_wallet'] < $data['cu_num']){
                return json(array('status' => 0, 'msg' => '不可大于可提分红', 'result' => ''));
            }
            // 根据后台设置判断
            $min_usd = $data['cu_num']*$currency_one['price']/$exchange_usd['exchange_usd']['value'];
            if($min_usd < $exchange_usd['global_min']['value']){
                // 计算最低提币数量
                $min_num = $exchange_usd['global_min']['value']/$currency_one['price'];
                $min_num = numberByRetain($min_num, 8);
                return json(array('status' => 0, 'msg' => '最低提币数量'.$min_num));
            }
        }
        
        // 验证手机验证码是否正确
        $checkData['sms_type'] = $sms_type;
        $checkData['code'] = $data['verify'];
        $checkData['phone'] = session('home.mobile');

        // 用于更新数据
        $where  = array('uid'=>$data['uid'],'cu_id'=> $data['cu_id']);

        if($data['qrcode_addr']){
            $where1['qrcode_addr'] = $data['qrcode_addr'];
        }

        Db::startTrans();
        try{

            // 检查手机验证码
            $res_code = checkPhoneCode($checkData);
            if($res_code['code']==0){
                return json(array('status' => 0, 'msg' => $res_code['msg']));
            }
            // 如果为本金，手续费为5%，其他则为1%
            if($data['type'] == 1){
                
                $data['number'] = $user_wallet['cu_num']; // 当前币种本金
                // 本金手续费
                $charge = numberByRetain($data['number']/100*5, 8);
                
                $where1 = [
                    'uid'       => $data['uid'],
                    'cu_id'     => $data['cu_id'],
                    'type'      => $data['type'],
                    'cu_num'    => $data['number'],
                    'out_num'    => $data['number']-$charge, // 平台最终转出数量
                    'create_time' => time(),
                    'wallet_addr'   => $data['wallet_addr'],
                    'tb_charge' => $charge,
                    'note'      => $data['note'], // 用户备注
                ];
                // 扣减全部本金
                $res1 = Db::name('user_wallet')->where($where)->setDec($cu_type,$data['number']);
                // 调用方法：扣减股权
                $checkStock = checkStock($data['uid'],$data['cu_id'],$data['number']);

                // 减掉执行收益表对应币种记录的相应数量
                $res2 = Db::name('execute_order')->where($where)->setDec('num',$data['number']);
                // 终止当前币种记录
                $res123 = Db::name('execute_order')->where(['uid'=>$data['uid'], 'cu_id'=>$data['cu_id']])->update(['num'=>0, 'is_stop'=>1]);  

                // 插入日志
                $this->insertLog($data['uid'],$data['cu_id'],'终止合同',888); // 终止合同

                // 插入数据到提币表
                $res3 = Db::name('user_extract')->insert($where1);
                // 获取当前币种logo
                $log = Db::name('currency')->where('id',$data['cu_id'])->value('log'); 

                $suc_data = [
                    'suc_name'     => session('home.username'),
                    'su_num'       => $data['number'],
                    'su_time'      => date('Y-m-d,H:i:s',time()),
                    'su_charge'    => $charge,
                    'su_log'       => $log       
                ];

                // 插入日志 901提币
                $note_log = $flag.'-提币-'.$data['number'];
                $res_log = $this->insertLog($data['uid'], $data['cu_id'], $note_log, $type);
                // 提交事务
                Db::commit();
                return json(array('status' => 1, 'msg' => '操作成功', 'result' => $suc_data));

            }else{
                // 收益、分红手续费
                $charge = numberByRetain($data['cu_num']/100, 8);           
                $where1 = [
                    'uid'      => $data['uid'],
                    'cu_id'    => $data['cu_id'],
                    'type'    => $data['type'],
                    'cu_num'   => $data['cu_num'], // 提币数量
                    'tb_charge'=> $charge,
                    'out_num'  => $data['cu_num']-$charge,  // 平台最终转出数量
                    'note'     => $data['note'],
                    'wallet_addr'  => $data['wallet_addr'],
                    'create_time'=> time()
                ];

                $res_1 = true;
                $res_2 = true;
                // 判断提币类型
                if($data['type']==2){
                    // 对应扣减收益钱包
                    $old_num = $user_wallet['bonus_wallet']; // 扣减前数量
                    $res_1 = Db::name('user_wallet')->where($where)->setDec('bonus_wallet',$data['cu_num']);
                }
                if($data['type']==3){
                    // 对应扣减分红钱包
                    $old_num = $user_wallet['rate_wallet']; // 扣减前数量
                    $res_2 = Db::name('user_wallet')->where($where)->setDec('rate_wallet',$data['cu_num']);
                }
                $res_3 = Db::name('user_extract')->insert($where1);
                
                // 成功提交后返回数据显示到页面
                $alias_name = Db::name('currency')->where('id',$data['cu_id'])->value('alias_name');
                $suc_data = [
                    'suc_name'  => session('home.username'),
                    'su_num'    => $data['cu_num'],
                    'su_time'   => date('Y-m-d,H:i:s',time()),
                    'su_charge' => $charge,
                    'alias_name'=> $alias_name
                ];
                
                // 插入日志 902提币
                $note = $data['cu_id'].$flag.'提币'.$data['cu_num'];
                $now_num = $old_num - $data['cu_num'];
                $res_log2 = $this->insertLog($data['uid'], $data['cu_id'], $note, $type, $old_num, $now_num);

                // 提交事务
                Db::commit();
                return json(array('status' => 1, 'msg' => '操作成功', 'result' => $suc_data));
            } 

        } catch (\Exception $e) {
            // p($e);die;
            // 回滚事务
            Db::rollback();
            return json(array('status' => 0, 'msg' => '网络异常，稍后再试', 'result' => ''));

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
        $income =  Db::name('user_wallet')->field('id,uid,cu_id,bonus_wallet+rate_wallet total_num')->where(['uid'=>$home['id']])->select();
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
        // 获取当前用户所有钱包记录并且进行累加
        $user_wallet = Db::name('user_wallet')->field('id,uid,cu_id')->where(['uid'=>$home['id']])->select();
        $htd_num = 0;
        foreach($user_wallet as $k=>$v){
            $user_wallet[$k]['num'] = 0;
            if($v['cu_id']!=11){
                $user_wallet[$k]['num'] = Db::name('income')->where(['get_uid'=>$home['id'],'cu_id'=>$v['cu_id']])->whereTime('create_time','today')->sum('main_coin');
                if(empty($user_wallet[$k]['num'])){
                    $user_wallet[$k]['num'] = 0;
                }
            }
            $htd_nums = Db::name('income')->where(['get_uid'=>$home['id'],'cu_id'=>$v['cu_id']])->whereTime('create_time','today')->sum('htd_coin');
            $htd_num += $htd_nums;
            if($v['cu_id']==11){
                $user_wallet[$k]['num'] = $htd_num;
            }
            $user_wallet[$k]['create_time'] = time();
        }
        $this->assign('income',$user_wallet);
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
        
        $income = Db::name('user_wallet')->where(['uid'=>$home['id']])->select();
        $moeny =0;
        $cu =[];
        foreach($income as $k => $v){

            $cu[$k]['cu_id']= $v['cu_id'];
            $moeny = $v['cu_num']+$v['bonus_wallet']+$v['rate_wallet'];
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

    // 股权页面
    public function stock(){
        $user = session('home');
        $stock_rights_money = Db::name('user')->field('id,stock_rights')->where(['id'=>$user['id']])->find();
        $this->assign('stock_rights',$stock_rights_money['stock_rights']);
        return view();
    }


    // 插入日志表
	public function insertLog($uid,$cu_id,$note,$type,$old_account='', $now_account=''){

		$data = array(
			'uid' => $uid,
            'cu_id' => $cu_id,
            'old_account' => $old_account,
            'now_account' => $now_account,
			'note' => $note,
			'type' => $type,
			'create_time' => time()
		);
		$res12 = Db::name('user_log')->insert($data);
		return $res12;
    }
    
    // 获取对应币种的所有本金
    public function getAllnum(){

        if(!is_post()){
            return json(array('code' => 0, 'msg' => '提交方式错误'));
        }
        $cu_id = input('post.cu_id/d');
        $uid = input('post.uid/d');
        // 获取当前用户对应币种的全部本金
        $all_num = Db::name('user_wallet')->field('id,uid,cu_id,cu_num')->where(['cu_id'=>$cu_id, 'uid'=>$uid])->find();
        if(!$all_num){
            return json(array('code' => 0, 'msg' => '本金为0'));
        }
        return $all_num;
    }
}
