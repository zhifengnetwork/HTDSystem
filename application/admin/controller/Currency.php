<?php
namespace app\admin\controller;

use app\common\model\Currency as CurrencyModel;
use app\common\controller\AdminBase;
use think\Db;
class Currency extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
        $this->Currency = new CurrencyModel();

    }
    public function index()
    {
        $currencyData = Db::name('currency')->paginate(10);
        $i = 1;
        return $this->fetch('currency_index', ['currencyData' => $currencyData,'i' => $i]);
    }
    public function add()
    {
        return $this->fetch();
    }
    public function doadd($name,$alias_name)
    {

        $names = DB::name('currency')->where('name',$name)->find();
        if ($names){
            echo "<script>alert('币种名称不能重复');history.go(-1);</script>";exit;
        }
        $alias_names = DB::name('currency')->where('alias_name',$alias_name)->find();
        if ($alias_names){
            echo "<script>alert('币种简称不能重复');history.go(-1);</script>";exit;
        }
        $arr = $this->request->post();

        $file = request()->file('log');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . DS . 'uploads');
            if($info){
                $arr['log'] = 'uploads/'.date('Ymd',time()).'/'.$info->getFilename();
                $arr['total_num'] = date('Y-m-d H:i:s',time());
                $res = DB::name('currency')->insert($arr);
                if ($res){
                    $this->success('添加成功!',('Currency/index'));
                } else {
                    $this->error('添加失败!');
                }
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }


    }
    public function edit($id)
    {
        $res = DB::name('currency')->where('id',$id)->find();
        return $this->fetch('currency_edit', ['res' => $res]);
    }
    public function doedit($id)
    {
        $arr = $this->request->post();
        $file = request()->file('log');

        if($file){
            $res = DB::name('currency')->where('id',$id)->find();

            if ($res['log']){
                $log = unlink($res['log']);
                //文件没删除
                if (!$log){
                    $this->error('文件更新失败!');
                }
            }
            $info = $file->move(ROOT_PATH . DS . 'uploads');
            if($info){
                //上传文件的更新
                $arr['log'] = 'uploads/'.date('Ymd',time()).'/'.$info->getFilename();
                $arr['total_num'] = date('Y-m-d H:i:s',time());

                $res = DB::name('currency')->where('id',$id)->update($arr);
                if ($res){
                    $this->success('更新成功!',('Currency/index'));
                } else {
                    $this->error('更新失败!');
                }
            }
        }else{
            $res = DB::name('currency')->where('id',$id)->update($arr);
            if ($res) {
                $this->success('更新成功!',('Currency/index'));
            } else {

                $this->error('更新失败!');
            }
        }

    }
    public function delete($id)
    {
        $res = DB::name('currency')->where('id',$id)->find();
        if ($res['log']) {
            $info = unlink($res['log']);
            //文件没删除
            if (!$info) {
                $this->error('图片删除失败!');
            }
        }
        $info = DB::name('currency')->where('id',$id)->delete();
        if ($info){
            $this->success('删除成功!');
        } else {
            $this->error('删除失败!');
        }
    }
}