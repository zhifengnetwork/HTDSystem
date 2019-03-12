<?php
namespace app\index\controller;

use org\Http;
use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

class Api extends Controller
{
    protected $site_config;
    public function _initialize()
    {
        parent::_initialize();
        $this->site_config = Cache::get('site_config');
    }

    public function getemotion()
    {
        $path = WEB_URL . '/public/plugins/wangEditor/emotion/';
        $dir  = ROOT_PATH . 'public' . DS . 'plugins' . DS . 'wangEditor' . DS . 'emotion' . DS;

        $array = array();
        foreach (glob($dir . '*', GLOB_ONLYDIR) as $files) {
            $files1             = iconv('GB2312', 'UTF-8', $files);
            $filename           = basename($files1);
            $k                  = $filename;
            $array[$k]['title'] = $filename;
            if (is_dir($files)) {
                $array[$k]['data'] = array();
                if ($dh = opendir($files)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file != "." && $file != "..") {
                            $result = pathinfo($file);
                            $file   = iconv('GB2312', 'UTF-8', $file);
                            $n      = str_replace('.' . $result['extension'], '', $file);
                            array_push($array[$k]['data'], array('icon' => $path . $filename . '/' . $file, 'value' => $n));
                        }
                    }
                    closedir($dh);
                }
            }
        }
        return json_encode($array);
    }
    public function downinfo()
    {

        $data = request()->param();
        Db::name('attach')->where('id', $data['id'])->setInc('download');
        $info = Db::name('attach')->where('id', $data['id'])->find();
        return json(array('code' => 200, 'msg' => '开始下载', 'url' => $info['savepath']));
    }
    public function download($url, $name, $local)
    {
        $local1 = $local;
        if (strpos($url, 'zip') === false || strpos($url, 'rar') === false) {
            $local1 = 0;
        }
        $down = new Http();
        if ($local1 == 1) {
            $down->download($url, $name);
        }
    }
    public function getMyItem()
    {
        $uid = session('userid');

        $tableArr = ['article', 'forum'];
        $table    = in_array(input('item'), $tableArr) ? input('item') : '';
        if (!$uid || !$table) {
            return json(array('code' => 0, 'msg' => '请求非法'));
        }

        $limit = is_number(input('limit')) ? input('limit') : 1;
        $page  = is_number(input('page')) ? input('page') : 10;
        $pre   = ($page - 1) * $limit;

        $model = Db::name($table);
        $count = $model->where("uid = {$uid}")->count();

        $field = 'm.*,c.template,c.name as catename';
        $order = 'updatetime DESC';
        if ($table == 'forum') {
            $field = 'm.*,c.name as catename';
            $order = 'time DESC';
        }
        $tptc = $model->alias('m')->join($table . 'cate c', 'c.id=m.tid', 'LEFT')->where("uid = {$uid}")->field($field)->order($order)->limit($pre, $limit)->select();
        foreach ($tptc as $k => $v) {
            $tptc[$k]['title'] = strip_tags($v['title']);
        }
        return json(array('code' => 0, 'msg' => '', 'count' => $count, 'data' => $tptc));
    }
    public function getMyCollect()
    {
        $uid      = session('userid');
        $type     = is_number(input('ctype')) ? input('ctype') : '';
        $tableArr = ['article', 'forum'];
        $table    = in_array(input('item'), $tableArr) ? input('item') : '';
        if (!$type || !$uid || !$table) {
            return json(array('code' => 0, 'msg' => '请求非法'));
        }

        $limit = is_number(input('limit')) ? input('limit') : 1;
        $page  = is_number(input('page')) ? input('page') : 10;
        $pre   = ($page - 1) * $limit;
        $model = Db::name('collect');
        $count = $model->where("uid = {$uid}")->count();
        $field = 'c.*,t.uid as zuid,u.username,t.id as fid,t.title,a.template';
        if ($table == 'forum') {
            $field = 'c.*,t.uid as zuid,u.username,t.id as fid,t.title';
        }

        $tptc = $model->alias('c')->join($table . ' t', 'c.sid=t.id', 'LEFT')->join($table . 'cate a', 'a.id=t.tid')->join('user u', 'u.id=t.uid')->field($field)->where("c.uid = {$uid} and c.type = {$type}")->order('id DESC')->limit($pre, $limit)->select();
        foreach ($tptc as $k => $v) {
            $tptc[$k]['title'] = strip_tags($v['title']);
        }
        return json(array('code' => 0, 'msg' => '', 'count' => $count, 'data' => $tptc));
    }
    public function zan_collect()
    {
        $data = $this->request->param();
        $id   = $data['id'];
        $uid  = session('userid');
        if (!session('userid') || !session('username')) {

            return json(array('code' => 0, 'msg' => '登录后才能操作'));
        } else {

            //状态:
            // 0 用户 1 帖子 2 评论
            $zan_collect = $data['zan_collect'];

            $msgsubject                         = '';
            $zan_collect == 'zan' ? $msgsubject = '点赞' : $msgsubject = '收藏';
            $tablename                          = '';
            $type                               = $data['type'];
            switch ($type) {
                case 1:
                    $tablename = 'forum';

                    break;

                case 2:

                    $tablename = 'comment';
                    break;

                case 3:

                    $tablename = 'article';
                    break;

                default:
                    $msgsubject = '关注';
                    $tablename  = 'user';
                    break;
            }
            $zuid = $id;
            if ($type != '0') {
                $zuid = Db::name($tablename)->where('id', $id)->value('uid');

            }
            if ($zuid == $uid) {
                return json(array('code' => 0, 'res' => '减', 'msg' => '不可以孤芳自赏哦'));

            }

            $insertdata['type'] = $type;
            $insertdata['uid']  = $uid;
            $insertdata['sid']  = $id;

            $n = Db::name($zan_collect)->where($insertdata)->find();
            if (empty($n)) {
                $insertdata['time'] = time();
                if (Db::name($zan_collect)->insert($insertdata)) {

                    Db::name($tablename)->where('id', $id)->setInc($zan_collect);

                    return json(array('code' => 200, 'res' => '加', 'msg' => $msgsubject . '成功'));

                } else {
                    return json(array('code' => 0, 'res' => '加', 'msg' => $msgsubject . '失败'));

                }
            } else {
                if (Db::name($zan_collect)->where('id', $n['id'])->delete()) {
                    Db::name($tablename)->where('id', $id)->setDec($zan_collect);
                    return json(array('code' => 200, 'res' => '减', 'msg' => $msgsubject . '成功'));

                } else {
                    return json(array('code' => 0, 'res' => '减', 'msg' => $msgsubject . '失败'));
                }
            }

        }
    }
//打赏
    public function tipauthor()
    {
        $data   = $this->request->param();
        $thread = Db::name('forum')->where('id', $data['id'])->find();
        $zuid   = $thread['uid'];
        $pay    = is_number(input('pay')) ? input('pay') : '';
        if ($pay <= 0) {
            exit('{"code":0,"msg":"你想干啥"}');
        }
        $uid = session('userid');
        if (!session('userid') || !session('username')) {
            exit('{"code":0,"msg":"登录后才能操作"}');
        } else {

            if ($zuid == $uid) {
                exit('{"code":0,"msg":"你太无聊了，不可以孤芳自赏哦"}');
            } else {

                $point = Db::name('user')->where('id', $uid)->value('point');

                if ($point < $pay) {
                    exit('{"code":-1,"msg":"量力而行，你只有' . $point . $this->site_config['jifen_name'] . '"}');
                } else {
                    point_note(0 - $pay, $uid, 'tipauthor', $data['id']);
                    point_note($pay, $zuid, 'tipauthor', $data['id']);
                    Db::name('user')->where('id', $zuid)->setInc('tips');
                    exit('{"code":200,"msg":"打赏成功"}');
                }
            }
        }
    }
 function gettaoke()
    {

        $taoke_config = Db::name('system')->field('value')->where('name', 'taoke')->find();

        $taoke_config = unserialize($taoke_config['value']);

        $htd = new Http();
        if ($line = $htd->get_curl($taoke_config['quan_api'].'&page=1')) {
            ///$line = str_replace('\\t', '', $line);
           $_res = json_decode($line, true);
           $res=$_res['result'];
           $data=array();
           $arr=array_rand(range(1,100),6);
           $i=0;
           foreach ($arr as $k => $v){
            $i++;
            $data[$k]['pic']=$res[$v]['Pic'];
            $data[$k]['title']=$res[$v]['Title'];
            $data[$k]['quan']=floatval($res[$v]['Quan_price']);
            $data[$k]['price']=floatval($res[$v]['Org_Price']);
            $data[$k]['link']=$res[$v]['Quan_link'];
              if($i==6) break;
           }
           return json(array('code'=>200,'data'=>$data));
        }
    }
}
