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
	public function lists()
	{
		$info = Db::name('user_extract')->order('id desc')->paginate(10);
		$this->assign('info',$info);

		return $this->fetch();
	}

	/** 
	*  审核
	*/
	public function withdrawal_audit()
	{
		$id = input('id/d');
		$status = input('status/d');
		
		$data = array();

		if ($status == 1) {
			$data['status'] = $status;
			
		}else if ($status == 2) {
			$data['status'] = $status;
		}

		$info = Db::name('user_extract')->where('id',$id)->update($data);
		if($info){
			return json(array('code' => 200, 'msg' => '更新成功'));
		}else{
			return json(array('code' => 0, 'msg' => '更新失败'));
		}
	}

	/** 
	*  删除
	*/
	public function del()
	{
		$id = input('id/d');

		if ($id) {
			$bool = Db::name('user_extract')->delete($id);
		}
		
		if ($bool) {
			return json(array('code' => 200, 'msg' => '删除成功'));
		} else {
			return json(array('code' => 0, 'msg' => '删除失败'));
		}
	}

	/** 
	*  详情
	*/
	public function detail()
	{
		$id = input('id/d');
		$note = input('note/s');

		if ($note) {
			Db::name('user_extract')->where('id',$id)->update(['note' => $note]);
		}

		$detail = Db::name('user_extract')->where('id',$id)->find();
		$currency = Db::name('currency')->where('id',$detail['cu_id'])->find();

		$this->assign('detail',$detail);
		$this->assign('currency',$currency);

		return $this->fetch();
	}
}