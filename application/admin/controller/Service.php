<?php
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Db;

/**
 * 提现
 */
class Service extends AdminBase
{
	protected function _initialize()
    {
        parent::_initialize();
    }

	/** 
	*  发送短信页面
	*/
	public function index()
	{
		return $this->fetch('service_index');
	}

}