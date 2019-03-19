<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use app\index\controller\Base;
use think\Cache;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Index extends HomeBase
{
    protected $site_config;

    public function _initialize()
    {
        parent::_initialize();
        if (CBOPEN == 2) {
            $this->redirect(url('bbs/index/index'));
        }

        $this->site_config = Cache::get('site_config');
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
        return view();
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
    //提币页面
    public function present(){
        // if (!session('userid')) {
        //     return $this->error('亲！请先登陆', 'user/login/index');
        // }      
        // $userid = session('userid');
        $userid = 2;
        
        $list = Db::table('htd_user_wallet')
                ->alias('a')
                ->join('htd_currency c', 'c.id=a.cu_id')
                ->where('uid',$userid)
                ->select();  
        $this->assign('list',$list);
        $this->assign('uid',$userid);
        return $this->fetch();        
    } 
    // 货币汇率
    public function exchange(){
              $data   = input();
              $result = Db::table('htd_currency')->where('id',$data['cu_id'])->value('price');
              $rmb    = $data['val']*$result;
            //   //美元汇率   
              $exchange_usd = Db::name('income_config')->field('name,value')->where('name','exchange_usd')->select();
              $exchange_usd = arr2name($exchange_usd);
              $usd    = $rmb*$exchange_usd['exchange_usd']['value'];

              $base = new Base();
              if($result){
                $base->ajaxReturn(['status' => 1, 'msg' =>'数据获取成功', 'result' =>$usd]);
              }else{
                $base->ajaxReturn(['status' => 0, 'msg' =>'数据获取失败', 'result' =>'']);
              }              
    }

    // 提币
    public function pick(){
        $data   = input();
        $where  = array('uid'=>$data['uid'],'cu_id'=> $data['cu_id']); 
        // dump($rmb);
        //美元汇率   
        $exchange_usd = Db::name('income_config')->field('name,value')->where('name','in',['exchange_usd','withdraw_min'])->select();
        $exchange_usd = arr2name($exchange_usd);
        $usd = $data['popover_convert'];
        $withdraw_min = $exchange_usd['withdraw_min']['value'];
        $where1 = [
            'uid' => $data['uid'],
            'cu_id' => $data['cu_id'],
            'cu_num' => $data['number']                 
        ];
        $base = new Base();
        if($data['remain_num']<$data['number']){
            $base->ajaxReturn(['status' => 0, 'msg' =>'货币剩余少于输入值', 'result' =>'']);
        }else if($usd<=$withdraw_min){
            $base->ajaxReturn(['status' => 0, 'msg' =>'货币大于50美元才能体现', 'result' =>'']);        
        }else{
            // 先计算剩余货币数量
            $data['remain_num'] = $data['remain_num']-$data['number'];
            // 更新钱包
            $res = Db::table('htd_user_wallet')->where($where)->update(['cu_num' => $data['remain_num']]);
            // 插入记录
            $user_extract = Db::table('htd_user_extract')->insert($where1);
            $base->ajaxReturn(['status' => 1, 'msg' =>'操作成功', 'result' =>'']);           
        }
    }

    //总收益
    public function totalrevenue()
    {
        return view();
    }
    
    //分享
    public function qrcode(){
        $id = session('user.user_id');
        $promotion = DB::name('user')->where('id',$id)->value('promotion');

        $data = array(
            'code' => $promotion,
//            'url' => 'http://fw.pt1130.cn/index/login/register?code='.$promotion
            'url' => 'http://'.$_SERVER['HTTP_HOST'].'/index/login/register?promotion='.$promotion
        );

//        dump($data);//exit;
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
