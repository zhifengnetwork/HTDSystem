<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Article as ArticleModel;
use app\common\model\Articlecate as ArticlecateModel;
use app\common\model\Upload as UploadModel;
use think\Db;

class Articles extends AdminBase
{
    protected $category_model;
    protected $article_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->category_model = new ArticlecateModel();
        $this->article_model  = new ArticleModel();
        $category_level_list  = $this->category_model->catetree();
        $this->assign('category_level_list', $category_level_list);
    }

    public function index()
    {

        $article_list = Db::name('article')->paginate(10);
        return $this->fetch('index', ['article_list' => $article_list]);

    }
    public function add()
    {
        return view();
    }
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $data['open']=$data['uid']=1;
        
            $data['time'] = $data['updatetime']=time();
            //开启图片本地化
//            if(isset($data['piclocal'])){
//                $uplon=new UploadModel();
//                $data['content']=$uplon->getCurContent($data['content']);
//            }else{
//                $data['content']= remove_xss($data['content']);
//            }

            if ($this->article_model->allowField(true)->save($data)) {
                if (!empty($data['linkinfo'])) {
                    $data['linkinfo'] = remove_xss($data['linkinfo']);
                    if (!empty($data['score'])) {
                        $data['score']     = 0;
                        $data['otherinfo'] = '';
                    }
                    $res = hook('attachlinksave', array('score' => $data['attachscore'], 'linkinfo' => $data['linkinfo'], 'id' => $this->article_model->id, 'otherinfo' => $data['otherinfo'],'edit' => 0, 'type' => 1));
                }

                return json(array('code' => 200, 'msg' => '添加成功'));
            } else {
                return json(array('code' => 0, 'msg' => '添加失败'));
            }

        }
    }

    public function toggle($id, $status, $name)
    {
        if ($this->request->isGet()) {

            if ($this->article_model->where('id', $id)->update([$name => $status]) !== false) {
                //  $this->success('更新成功');
                return json(array('code' => 200, 'msg' => '更新成功'));
            } else {
                // $this->error('更新失败');
                return json(array('code' => 0, 'msg' => '更新失败'));
            }
        }

    }
    /**
     * 编辑分类
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $category = new ArticlecateModel();

        $tptcs = $category->catetree();

        $this->assign(array('tptcs' => $tptcs));
        $tptc = $this->article_model->find($id);

        return $this->fetch('edit', ['tptca' => $tptc]);
        //return view();
    }

    /**
     * 更新分类
     * @throws \think\Exception
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['updatetime'] = time();
            // 过滤post数组中的非数据表字段数据
            $res = $this->article_model->allowField(true)->save($data, ['id' => $data['id']]);
            if ($res) {
                if (isset($data['oldlinkinfo'])) {
                    $data['linkinfo'] = remove_xss($data['linkinfo']);
                    if (!empty($data['score'])) {
                        $data['score']     = 0;
                        $data['otherinfo'] = '';
                    }
                    $res = hook('attachlinksave', array('score' => $data['attachscore'], 'linkinfo' => $data['linkinfo'], 'otherinfo' => $data['otherinfo'], 'id' => $data['id'], 'edit' => 1, 'type' => 1));
                }
                return json(array('code' => 200, 'msg' => '更新成功'));
            } else {
                return json(array('code' => 0, 'msg' => '更新失败'));
            }
        }
    }

    /**
     * 删除分类
     * @param $id
     * @throws \think\Exception
     */
    public function delete($id)
    {

        $info  = $this->article_model->find($id);
        $score = getpoint($info['uid'], 'articleadd', $id);
        point_note(0 - $score, $info['uid'], 'articledelete', $id);

        if ($this->article_model->destroy($id)) {

            return json(array('code' => 200, 'msg' => '删除成功'));
        } else {
            return json(array('code' => 0, 'msg' => '删除失败'));
        }
    }
    public function alldelete()
    {
        $params = input('post.');
        foreach ($params['ids'] as $k => $v) {
            $info  = $this->article_model->find($v);
            $score = getpoint($info['uid'], 'articleadd', $v);
            point_note(0 - $score, $info['uid'], 'articledelete', $v);

        }

        $ids    = implode(',', $params['ids']);
        $result = $this->article_model->destroy($ids);
        if ($result) {
            return json(array('code' => 200, 'msg' => '删除成功'));
        } else {
            return json(array('code' => 0, 'msg' => '删除失败'));
        }
    }
    public function status($id)
    {
        $res = DB::name('article')->where('id',$id)->find();
        if($res['open'] == '1'){
            $info = DB::name('article')->where('id',$id)->setField('open','2');
            $this->success('隐藏成功');
        } else if ($res['open'] == '2') {
            DB::name('article')->where('id',$id)->setField('open','1');
            $this->success('显示成功');
        }
    }
}
