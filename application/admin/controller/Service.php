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

	//接收后台信息发送短信
	public function sendMsg(){
		$data = input();
		if(!check_mobile_number($data['mobile'])){
			return json(array('code' => 0, 'msg' => '手机格式不正确'));
		}
		if(!$data['msg']){
			return json(array('code' => 0, 'msg' => '请输入发送内容'));
		}
		// 发送短信
		$res = $this->send($data['mobile'],$data['msg']);
		// $json_obj = json_decode($res,true); 
		// if($json_obj != 1){
		// 	return json(array('code' => 0, 'msg' => '发送失败'));
		// }
		return json(array('code' => 200, 'msg' => '发送成功'));

	}


	private function send($mobile,$msg){
		
		// $smsCode = rand(123456,999999);
		$tpl = '【HTD】'.$msg.' 若非您本人，请忽略本短信。';
		$post_data = array();
		$post_data['userid'] = 2787;
		$post_data['account'] = 'qx3854';
		$post_data['password'] = 'Aa12321';
		$post_data['content'] = $tpl; // 短信的内容，内容需要UTF-8编码
		$post_data['mobile'] = $mobile; // 发信发送的目的号码.多个号码之间用半角逗号隔开 
		$post_data['sendtime'] = ''; // 为空表示立即发送，定时发送格式2010-10-24 09:08:10
		$url='http://120.25.105.164:8888/sms.aspx?action=send';
		$o='';
		foreach ($post_data as $k=>$v)
		{
		$o.="$k=".urlencode($v).'&';
		}
		$post_data=substr($o,0,-1);
		$result= curl_post($url,$post_data);
		return $result['message'];
		// return $result;
	}
	
	
}