<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use app\common\model\User as UserModel;
use app\index\model\Article as ArticleModel;
use think\Cache;
use think\Db;
use think\Session;

class Articles extends HomeBase
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

    public function lists()
    {
        $a=input('cate_alias');
        $alias                = is_valid_character(input('cate_alias')) ? input('cate_alias') : '';
        $cateinfo['showall']  = 0;
        $cateinfo['catename'] = '全部';
        $article              = Db::name('article');
        if ($alias) {
            $cateinfo['showall'] = 1;

            $info                 = Db::name('articlecate')->where('alias', $alias)->find();
            $catealias['tid']     = $info['id'];
            $cateinfo['catename'] = $info['name'];
            $cateinfo['template'] = $info['template'];
            $article_list         = Db::name('article')->alias('a')->join('user u', 'u.id=a.uid')->where('a.open', 1)->where($catealias)->field('a.*,u.username')->order('a.id desc')->paginate($this->site_config['c_list_main']);
        } else {
            $article_list = Db::name('article')->alias('a')->join('articlecate c', 'c.id=a.tid')->join('user u', 'u.id=a.uid')->where('a.open', 1)->field('a.*,c.name,c.template,u.username')->order('a.id desc')->paginate($this->site_config['c_list_main']);
        }

        //阅读排行
        $artphb = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->field('a.*,c.name,c.template')->order('view desc')->limit($this->site_config['c_list_phb'])->select();
        $this->assign('artphb', $artphb);
        //文章推荐
        $artchoice = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->where("choice=1")->field('a.*,c.name,c.template')->order('id desc')->limit($this->site_config['c_list_choice'])->select();
        $this->assign('artchoice', $artchoice);

        $this->assign('cateinfo', $cateinfo);
        $this->assign('article_list', $article_list);
        return view();
    }
    public function choice()
    {   
        $article              = Db::name('article');
        $choice = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->join('user u', 'u.id=a.uid')->where("a.choice=1")->field('a.*,c.name,c.template,u.username')->order('id desc')->paginate($this->site_config['c_list_choice']);
        $this->assign('artchoice', $choice);

        //阅读排行
        $artphb = $article->alias('a')->join('articlecate c', 'c.id=a.tid')->field('a.*,c.name,c.template')->order('view desc')->limit($this->site_config['c_list_phb'])->select();
        $this->assign('artphb', $artphb);
        return view();
    }
    public function add()
    {
        $site_config = Cache::get('site_config');

        if (!session('userid') || !session('username')) {
            $this->error('亲！请登录', url('user/login/index'));
        } else {

            //检查权限
            $user = new UserModel();
            if (!$user->checkauths(session('userid'))) {
                $this->error('您的账户被限制使用', url('index/index/index'));
            }
            $article = new ArticleModel();
            if (request()->isPost()) {

                $data = input('post.');

                if ($data['tid'] == 0) {
                    return json(array('code' => 0, 'msg' => '版块为空'));
                }

                if ($data['content'] == '') {
                    return json(array('code' => 0, 'msg' => '内容为空'));
                }
                $data['time'] = $data['updatetime'] = time();

                $data['uid'] = session('userid');

                if ($site_config['article_sh'] == 0) {
                    $isdeveloper = Db::name('user')->where('id', $data['uid'])->value('developer');

                    $data['open'] = $isdeveloper > 0 ? 1 : 0;
                } else {
                    $data['open'] = 1;
                }

                $data['view'] = 1;

                $data['description'] = mb_substr(remove_xss($data['content']), 0, 200, 'utf-8');

                $data['title']   = strip_tags($data['title']);
                $data['content'] = remove_xss($data['content']);

                if ($article->add($data)) {
                    $fid = $article->getLastInsID();
                    point_note($site_config['jifen_add'], session('userid'), 'articleadd', $fid);

                    //附件链接信息
                    if (!empty($data['linkinfo'])) {
                        $data['linkinfo'] = remove_xss($data['linkinfo']);
                        if (!empty($data['score'])) {
                            $data['score']     = 0;
                            $data['otherinfo'] = '';
                        }
                        $res = hook('attachlinksave', array('score' => $data['attachscore'], 'linkinfo' => $data['linkinfo'], 'otherinfo' => $data['otherinfo'], 'id' => $fid, 'edit' => 0, 'type' => 1));
                    }

                    return json(array('code' => 200, 'msg' => '添加成功'));
                } else {
                    return json(array('code' => 0, 'msg' => '添加失败'));
                }
            }

            $category = Db::name('articlecate');
            $tptc     = $category->select();
            $this->assign('tptc', $tptc);
           

            $this->assign('title', '发布帖子');

            return view();
        }
    }
    public function edit()
    {
        $site_config = Cache::get('site_config');

        if (!session('userid') || !session('username')) {
            $this->error('亲！请登录', url('user/login/index'));
        } else {
            //检查权限
            $user = new UserModel();
            if (!$user->checkauths(session('userid'))) {
                $this->error('您的账户被限制使用', url('index/index/index'));
            }
            $id = input('id');
            session('editartid', $id);

            $uid     = session('userid');
            $article = new ArticleModel();
            $a       = $article->find($id);
            if (empty($id) || $a == null || $a['uid'] != $uid) {
                $this->error('亲！您迷路了');
            } else {
                if (request()->isPost()) {

                    $data       = input('post.');
                    $data['id'] = session('editartid');

                    if (isset($data['downlinks'])) {
                        $data['outlink']   = remove_xss($data['outlink']);
                        $data['downlinks'] = remove_xss($data['downlinks']);
                        if ($data['outlink'] && $data['content'] == "") {
                            $data['content'] = '外部链接';
                        }

                    }

                    session('editartid', null);
                    if ($data['content'] == '') {
                        return json(array('code' => 0, 'msg' => '内容为空'));
                    }
                    $data['description'] = mb_substr(remove_xss($data['content']), 0, 200, 'utf-8');
                    $data['title']       = strip_tags($data['title']);
                    //V2.2.1
                    $data['updatetime'] = time();
                    // $data['title']=  hook('trigtitle',array('title'=>$data['title'],'id'=>$data['id']),true,'title');

                    //$data['coverpic']= remove_xss($data['coverpic']);
                    $data['content'] = remove_xss($data['content']);
                    if ($article->edit($data)) {
                        if (!empty($data['fee'])) {
                            $res = hook('threadfee', array('score' => $data['fee'], 'id' => $data['id'], 'edit' => 1));
                        }
                        //附件链接信息
                        if (!empty($data['linkinfo'])) {
                            $data['linkinfo'] = remove_xss($data['linkinfo']);
                            if (!empty($data['score'])) {
                                $data['score']     = 0;
                                $data['otherinfo'] = '';
                            }
                            $res = hook('attachlinksave', array('score' => $data['attachscore'], 'linkinfo' => $data['linkinfo'], 'otherinfo' => $data['otherinfo'], 'id' => $data['id'], 'edit' => 1, 'type' => 1));
                        }
                        return json(array('code' => 200, 'msg' => '修改成功'));
                    } else {
                        return json(array('code' => 0, 'msg' => '修改失败'));
                    }
                }

                $category      = Db::name('articlecate');
                $tptc          = $article->find($id);
                $tptc['title'] = strip_tags($tptc['title']);
                $tptcs         = $category->select();
                $this->assign(array('tptcs' => $tptcs, 'tptc' => $tptc));
                $this->assign('title', '编辑帖子');
                return view();
            }
        }
    }
}
