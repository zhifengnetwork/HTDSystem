<?php
namespace app\admin\controller;

use app\common\model\Currency as CurrencyModel;
use app\common\controller\AdminBase;
use think\Db;
use think\Session;
class Currency extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
        $this->Currency = new CurrencyModel();

    }
    public function index($name = '',$names = '')
    {
        if ($name){
            $currencyData = Db::name('currency')->where('name','like','%'.$name.'%')->paginate(15);
            Session::set('name',$name);
            $names = Session::get('name');
        } else {
            $currencyData = Db::name('currency')->paginate(15);
        }

        $i = 1;

        return $this->fetch('currency_index', ['currencyData' => $currencyData,'i' => $i,'names'=> $names]);
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
        $file1 = request()->file('wallet_qrcode');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            if ($file1) {
                $info1 = $file1->move(ROOT_PATH.DS.'uploads/qrcode');
                $info = $file->move(ROOT_PATH . DS . 'uploads');
                if ($info) {
                    if ($info1) {
                        $arr['log'] = 'uploads/' . date('Ymd', time()) . '/' . $info->getFilename();
                        $arr['total_num'] = date('Y-m-d H:i:s', time());
                        $arr['wallet_qrcode'] = 'uploads/qrcode/' . date('Ymd', time()) . '/' . $info1->getFilename();
                        $res = DB::name('currency')->insert($arr);
                        if ($res) {
                            $this->success('添加成功!', ('Currency/index'));
                        } else {
                            $this->error('添加失败!');
                        }
                    }else{
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
                } else {
                    // 上传失败获取错误信息
                    echo $file->getError();
                }
            }
        }


    }
    public function edit($id)
    {
        $res = DB::name('currency')->where('id',$id)->find();
        return $this->fetch('currency_edit', ['res' => $res]);
    }
    public function doedit($id,$name,$alias_name)
    {
        $curr = DB::name('currency')->where('id',$id)->find();
        $arr = $this->request->post();
        $file = request()->file('log');
        $file1 = request()->file('wallet_qrcode');
        if ($curr['name'] !== $name) {
            $names = DB::name('currency')->where('name', $name)->find();
            if ($names) {
                echo "<script>alert('币种名称不能重复');history.go(-1);</script>";
                exit;
            }
        }
        if ($curr['alias_name'] !== $alias_name) {
            $alias_names = DB::name('currency')->where('alias_name', $alias_name)->find();
            if ($alias_names) {
                echo "<script>alert('币种简称不能重复');history.go(-1);</script>";
                exit;
            }
        }

        if($file1){
            //上传文件
            $res = DB::name('currency')->where('id',$id)->find();
            dump($res);die;
            //判断文件是否存在
            if (is_file($res['wallet_qrcode'])) {
                if ($res['wallet_qrcode']) {
                    $log = unlink($res['wallet_qrcode']);
                    //文件删除
                    if (!$log) {
                        $this->error('文件更新失败!');
                    }
                }
            }
            $info1 = $file1->move(ROOT_PATH . DS . 'uploads/qrcode');
            if($info1){
                //上传文件的更新
                $arr['wallet_qrcode'] = 'uploads/qrcode/'.date('Ymd',time()).'/'.$info1->getFilename();

            }
        }

        if($file){
            //上传文件
            $res = DB::name('currency')->where('id',$id)->find();
            //判断文件是否存在
            if (is_file($res['log'])) {
                if ($res['log']) {
                    $log = unlink($res['log']);
                    //文件删除
                    if (!$log) {
                        $this->error('文件更新失败!');
                    }
                }
            }
            $info = $file->move(ROOT_PATH . DS . 'uploads');
            if($info){
                //上传文件的更新
                $arr['log'] = 'uploads/'.date('Ymd',time()).'/'.$info->getFilename();
            }
            $res = DB::name('currency')->where('id',$id)->update($arr);
            if ($res){
                $this->success('更新成功!',('Currency/index'));
            } else {
                echo "<script>alert('更新失败');history.go(-1);</script>";
                exit;
            }
        }else{
            //没有上传文件
            $res = DB::name('currency')->where('id',$id)->update($arr);
            if ($res) {
                $this->success('更新成功!',('Currency/index'));
            } else {

                echo "<script>alert('更新失败');history.go(-1);</script>";
                exit;
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
        if ($res['wallet_qrcode']) {
            $info = unlink($res['wallet_qrcode']);
            //文件没删除
            if (!$info) {
                $this->error('图片删除失败!');
            }
        }
        $info = DB::name('currency')->where('id',$id)->delete();
        if ($info){
            return json(array('code' => 200, 'msg' => '删除成功'));
        } else {
            return json(array('code' => 0, 'msg' => '删除失败'));
        }
    }
    //修改状态
    public function status($id)
    {
        $res = DB::name('currency')->where('id',$id)->find();
        if($res['status'] == '1'){
            $info = DB::name('currency')->where('id',$id)->setField('status','0');
            $this->success('隐藏成功');
        } else if ($res['status'] == '0') {
            DB::name('currency')->where('id',$id)->setField('status','1');
            $this->success('显示成功');
        }
    }
}