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
		$one_data = Db::name('user_extract')->where(['id'=>$id])->find();
		if(!$one_data){
			return json(array('code' => 0, 'msg' => '提币订单不存在'));
		}
		$data = array();
		
		if ($status == 1) { // 通过
			$data['status'] = $status;
			$info = Db::name('user_extract')->where('id',$id)->update($data);
			if($info){
				return json(array('code' => 200, 'msg' => '更新成功'));
			}else{
				return json(array('code' => 0, 'msg' => '更新失败'));
			}
		}
		
		// 1本金 cu_num 2收益 bonus_wallet 3分红rate_wallet
		if ($status == 2) { // 不通过,对应币种数量原路返回

			$data['status'] = $status;
			if($one_data['type'] == 1){
				$cu_num = $one_data['cu_num'];
				$fiends = 'cu_num';
			}
			if($one_data['type'] == 2){
				$cu_num = $one_data['cu_num'];
				$fiends = 'bonus_wallet';

			}
			if($one_data['type'] == 3){
				$cu_num = $one_data['cu_num'];
				$fiends = 'rate_wallet';

			}
			$where['uid'] = $one_data['uid'];
			$where['cu_id'] = $one_data['cu_id'];
			$res = Db::name('user_wallet')->where($where)->setInc($fiends, $cu_num);
			$info = Db::name('user_extract')->where('id',$id)->update($data);

			if($res && $info){
				return json(array('code' => 200, 'msg' => '拒绝成功，数量原路返回'));
			}else{
				return json(array('code' => 0, 'msg' => '拒绝失败..'));
			}
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