<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;

/**
 * 提现
 */
class Withdrawal extends AdminBase
{
	protected function _initialize()
    {
        parent::_initialize();
    }

	/** 
	*  列表
	*/
	public function withdrawal_list()
	{
		$info = Db::name('user_extract')->order('id desc')->paginate(10);
		$this->assign('info',$info);

		return $this->fetch();
	}

	/** 
	*  提现
	*/
	public function withdrawal()
	{
		// return $this->fetch();
	}

	/** 
	*  审核
	*/
	public function withdrawal_audit()
	{
		$id = I('id/d');
		$status = I('id/d');

		$info = Db::name('user_extract')->where('id',$status)->update(['status'=>$status]);

		if ($info != false) {
			return json(array('code' => 200, 'msg' => '更新成功'));
		} else {
			return json(array('code' => 0, 'msg' => '更新失败'));
		}
	}

	/** 
	*  删除
	*/
	public function del()
	{
		$id = I('id/d');

		if ($id) {
			$bool = Db::name('user_extract')->delete($id);
		}
		
		if ($bool) {
			return json(array('code' => 200, 'msg' => '删除成功'));
		} else {
			return json(array('code' => 0, 'msg' => '删除失败'));
		}
	}
}