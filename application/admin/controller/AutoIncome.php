<?php
namespace app\admin\controller;
use think\Db;

class AutoIncome 
{
	
	protected $month = 30; // 默认按30天计算
	/* 
	*	每天执行一次收益动作
	* 	获取所有已经后台审核的订单并且创建时间不等于当天的
	*   循环所有符合条件订单发放静态收益、直推收益、动态收益
	*/
    public function autoToIncome()
    {
		// 获取创建时候小于当天并且后台审核的订单
		$toDayTime = strtotime(date('Y-m-d'));
		$orderWhere['create_time'] = array('<', $toDayTime);
		$orderWhere['is_check'] = 1; // 已审核
		$orderWhere['is_stop'] = 0;  // 订单没有终止的
		$orderAll = Db::name('buy_order')->where($orderWhere)->select();
		if($orderAll){
			// 获取配置表
			$configs = Db::name('income_config')->field('name,value')->select();
			// 把配置项name转换成$configs['price_min1']['value']
			$configs = arr2name($configs); 
			foreach($orderAll as $key=>$value){
				// 1、计算用户的静态收益收益率 6等级
				// 根据后台静态收益配置项判断收益
				if($value['total_money'] >= $configs['price_min1']['value'] && $value['total_money'] <= $configs['price_max1']['value']){
					// 收益率
					$rate = $configs['give1']['value']/100;
					// 收益比例 主流币+平台代币
					$main_coin_ratio = $configs['mainstream1']['value'];     // 主流币
					$platform_coin_ratio = $configs['platform1']['value'];   // 平台币
				}elseif($value['total_money'] > $configs['price_min2']['value'] && $value['total_money'] <= $configs['price_max2']['value']){
					$rate = $configs['give2']['value']/100;
					$main_coin_ratio = $configs['mainstream2']['value'];
					$platform_coin_ratio = $configs['platform2']['value'];

				}elseif($value['total_money'] > $configs['price_min3']['value'] && $value['total_money'] <= $configs['price_max3']['value']){
					$rate = $configs['give3']['value']/100;
					$main_coin_ratio = $configs['mainstream3']['value'];
					$platform_coin_ratio = $configs['platform3']['value'];

				}elseif($value['total_money'] > $configs['price_min4']['value'] && $value['total_money'] <= $configs['price_max4']['value']){
					$rate = $configs['give4']['value']/100;
					$main_coin_ratio = $configs['mainstream4']['value'];
					$platform_coin_ratio = $configs['platform4']['value'];

				}elseif($value['total_money'] > $configs['price_min5']['value'] && $value['total_money'] <= $configs['price_max5']['value']){
					$rate = $configs['give5']['value']/100;
					$main_coin_ratio = $configs['mainstream5']['value'];
					$platform_coin_ratio = $configs['platform5']['value'];
					
				}else{
					$rate = $configs['give6']['value']/100;
					$main_coin_ratio = $configs['mainstream6']['value'];
					$platform_coin_ratio = $configs['platform6']['value'];
				}
				
				// 币种收益=收益率*币种数量
				$giveIncome = $value['num']*$rate;
				// 根据后台设置的比例把总收益拆分为主流币+平台代币
				$main_coin = $giveIncome*($main_coin_ratio/100);
				$platform_coin = $giveIncome*($platform_coin_ratio/100);
				






				// 开启事务
				Db::startTrans();
				try{
					// 累加到当前用户对应币种钱包
					$main_res = Db::name('user_wallet')->where(['uid'=>$value['uid'], 'cu_id'=>$value['cu_id']])->setInc('bonus_wallet', $main_coin);
					// 累加到平台币 cu_id=11
					$platform_res = Db::name('user_wallet')->where(['uid'=>$value['uid'], 'cu_id'=>11])->setInc('bonus_wallet', $platform_coin);
					// 把收益记录插入收益表
					$this->insertToIncome($value['uid'],$value['uid'],$value['cu_id'],101,$value['total_money'],$giveIncome,$main_coin,$platform_coin,$rate);
					// 插入日志
					$logNote = '获得静态收益:'.$giveIncome.'主流币'.$main_coin.'平台币'.$platform_coin;
					$this->insertLog($value['uid'],$value['cu_id'],$logNote);

					// 把相应收益累加到直推上级
					$upUid = Db::name('user')->field('id,pid')->where(['id'=>$value['uid']])->find();
					// 把收益累加到当前用户的直推用户
					if($upUid['pid']){
						$whereUp['id'] = $upUid['pid'];
						$whereUp['cu_id'] = $value['cu_id']; // 对应币种
						$main_coin_res = Db::name('user_wallet')->where($whereUp)->setInc('bonus_wallet', $main_coin);
						// 插入到平台币 cu_id=11
						$platform_coin_res = Db::name('user_wallet')->where(['uid'=>$upUid['pid'], 'cu_id'=>11])->setInc('bonus_wallet', $platform_coin);
						// 把直推收益记录插入收益表
						$this->insertToIncome($value['uid'],$value['pid'],$value['cu_id'],102,$value['total_money'],$giveIncome,$main_coin,$platform_coin,$rate);
						// 插入直推日志
						$drLogNote = '获的直推收益:'.$giveIncome.'主流币'.$main_coin.'平台币'.$platform_coin;
						$this->insertLog($value['uid'],$value['cu_id'],$drLogNote);
					}

					// 提交事务
					Db::commit();   
					echo 'ok';
				}catch(\Exception $e){
					// 回滚事务
					Db::rollback();
					echo 'err';
				}
			}
			echo '完成';
		}else{
			echo '没有要处理的订单';
			exit;
			// echo "No dispose the orders\n";
		}

		//echo Db::name('buy_order')->getLastSql();
		p($orderAll);


	}
	
	// 插入收益表
	public function insertToIncome($uid, $get_uid, $cu_id, $type, $base_money, $total_coin, $main_coin, $htd_coin, $percent){

		$data = array(
			'uid' => $uid,
			'get_uid' => $get_uid,
			'cu_id' => $cu_id,
			'type' => $type,
			'total_coin' => $total_coin,
			'base_money' => $base_money,
			'main_coin' => $main_coin,
			'htd_coin' => $htd_coin,
			'percent' => $percent,
			'out_flag' => 1,
			'out_time' => time()
		);
		$res1 = Db::name('income')->insert($data);
		return $res1;
	}

	// 插入日志表
	public function insertLog($uid,$cu_id,$note){

		$data = array(
			'uid' => $uid,
			'cu_id' => $cu_id,
			'note' => $note,
			'create_time' => time()
		);
		$res2 = Db::name('user_log')->insert($data);
		return $res2;
	}
}