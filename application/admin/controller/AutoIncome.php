<?php
namespace app\admin\controller;
use think\Db;

class AutoIncome 
{
	
	protected $month = 30; // 默认按30天计算
	protected $static_type = 101;
	protected $push_type = 102;
	protected $dy_type = 103;
	protected $global_type = 104;
	private $key = '160da32821f8bfe1c7dd3c86d64b946a';


	/* 
	*	每天执行一次收益动作
	* 	获取所有已经后台审核的订单并且创建时间不等于当天的
	*   循环所有符合条件订单发放静态收益、直推收益、动态收益
	*/
  public function autoToIncome(){

		$get_key = input('key/s');
		if(!$get_key){
			return 'No .....';
			exit;
		}
		if($get_key!=$this->key){
			return 'No ...';
			exit;
		}
		$time = time();
		// 获取创建时候小于当天并且后台审核的订单
		$toDayTime = strtotime(date('Y-m-d'));
		$orderWhere['create_time'] = array('<', $toDayTime);
		$orderWhere['is_check'] = 1; // 已审核
		$orderWhere['is_stop'] = 0;  // 订单没有终止的
		$orderWhere['num'] = array('>', 0);
		$orderAll = Db::name('execute_order')->where($orderWhere)->select();
		if($orderAll){
			// 获取htd价格
			$htd_price = Db::name('currency')->field('id,price')->where(['alias_name'=>'HTD'])->find();
			// 获取配置表
			$configs = Db::name('income_config')->field('name,value')->select();
			// 把配置项name转换成$configs['price_min1']['value']
			$configs = arr2name($configs);
			$i = 0;
			foreach($orderAll as $key=>$value){
				$rate =0;
				$giveIncome =0;
				$main_coin =0;
				$platform_coin=0;
				
				// 1、计算用户的静态收益收益率 6等级
				// 根据后台静态收益配置项判断收益
				if($value['total_money'] >= $configs['price_min1']['value'] && $value['total_money'] <= $configs['price_max1']['value']){
					// 收益率
					$rate = $configs['give1']['value']/100;
					// 收益比例 主流币+平台代币
					$main_coin_ratio = $configs['mainstream1']['value'];     // 主流币
					$platform_coin_ratio = $configs['platform1']['value'];   // 平台币
				}elseif($value['total_money'] >= $configs['price_min2']['value'] && $value['total_money'] <= $configs['price_max2']['value']){
					$rate = $configs['give2']['value']/100;
					$main_coin_ratio = $configs['mainstream2']['value'];
					$platform_coin_ratio = $configs['platform2']['value'];

				}elseif($value['total_money'] >= $configs['price_min3']['value'] && $value['total_money'] <= $configs['price_max3']['value']){
					$rate = $configs['give3']['value']/100;
					$main_coin_ratio = $configs['mainstream3']['value'];
					$platform_coin_ratio = $configs['platform3']['value'];

				}elseif($value['total_money'] >= $configs['price_min4']['value'] && $value['total_money'] <= $configs['price_max4']['value']){
					$rate = $configs['give4']['value']/100;
					$main_coin_ratio = $configs['mainstream4']['value'];
					$platform_coin_ratio = $configs['platform4']['value'];

				}elseif($value['total_money'] >= $configs['price_min5']['value'] && $value['total_money'] <= $configs['price_max5']['value']){
					$rate = $configs['give5']['value']/100;
					$main_coin_ratio = $configs['mainstream5']['value'];
					$platform_coin_ratio = $configs['platform5']['value'];
					
				}else{
					$rate = $configs['give6']['value']/100;
					$main_coin_ratio = $configs['mainstream6']['value'];
					$platform_coin_ratio = $configs['platform6']['value'];
				}
				
				$rate = numberByRetain($rate/$this->month, 8); // 除于30天取8位
				// +++++++ 计算当前用户静态收益：币种收益=收益率*币种数量 +++++++ //
				$giveIncome = $value['num']*$rate; 

				// 根据后台设置的比例把总收益拆分为主流币+平台代币
				$main_coin = $giveIncome*($main_coin_ratio/100);
				// 平台币*当前对应币种的价格
				$platform_coin = $giveIncome*($platform_coin_ratio/100);
				$platform_coin = $platform_coin*$value['price']/$htd_price['price'];
			
				
				// 开启事务
				Db::startTrans();
				try{
					
					// +++++++++++++++++++++++++++++++++静态收益++++++++++++++++++++++++++++++++++++++++++++++ begin
					// +++++ 把收益累加到当前用户对应币种钱包+++++ //
					$main_res = Db::name('user_wallet')->where(['uid'=>$value['uid'], 'cu_id'=>$value['cu_id']])->setInc('bonus_wallet', $main_coin);

					// 累加到平台币 cu_id=11
					$platform_res = Db::name('user_wallet')->where(['uid'=>$value['uid'], 'cu_id'=>11])->setInc('bonus_wallet', $platform_coin);
					// 把收益记录插入收益表
					$in_income_res1 =$this->insertToIncome($value['uid'],$value['uid'],$value['cu_id'],101,$value['total_money'],$giveIncome,$main_coin,$platform_coin,$rate,$value['order_no'],$time);
					// 插入日志
					$logNote = '获得静态收益:'.$giveIncome.'个,主流币'.$main_coin.'平台币'.$platform_coin;
					$in_log_res1 = $this->insertLog($value['uid'],$value['cu_id'],$logNote,$this->static_type,$value['order_no'],$time);
					// 把相应收益累加到直推上级
					$upUid = Db::name('user')->field('id,pid')->where(['id'=>$value['uid']])->find();
					// +++++++++++++++++++++++++++++++++静态收益++++++++++++++++++++++++++++++++++++++++++++++ end
				
					// +++++++++++++++++++++++++++++++++直推收益++++++++++++++++++++++++++++++++++++++++++++++ begin
					// 如果当前用户的pid=0 则不进入上级收益操作
					if(!$upUid['pid']){
						$main_coin_res = true;
						$platform_coin_res = true;
						$in_income_res2 = true;
						$in_log_res2 = true;
					}

					// 直推上级必须满足以下条件
					$up_order_money = isEnjoyUser($upUid['pid']); // 获取当前上级是否入单指定金额
					// +++++ 把收益累加到当前用户的直推用户+++++ //
					if($upUid['pid'] && $up_order_money){
						
						// 根据后台设置的直推收益配置
						$push_rate = $configs['push_rate']['value']?$configs['push_rate']['value']:100;
						if($push_rate==100){
							$main_coin_push = $main_coin;
							$platform_coin_push = $platform_coin;
						}else{
							// 如果后台设置不是拿100%，那么重新计算直推人的收益
							$push_total_income = $giveIncome*($push_rate/100);
							$main_coin_push = $push_total_income*($main_coin_ratio/100);
							$platform_coin_push = $push_total_income*($platform_coin_ratio/100);
							$platform_coin_push = $platform_coin_push*$value['price']/$htd_price['price'];
						}
						
						$whereUp['uid'] = $upUid['pid'];
						$whereUp['cu_id'] = $value['cu_id']; // 对应币种
						$main_coin_res = Db::name('user_wallet')->where($whereUp)->setInc('bonus_wallet', $main_coin_push);

						// 插入到平台币 cu_id=11
						$platform_coin_res = Db::name('user_wallet')->where(['uid'=>$upUid['pid'], 'cu_id'=>11])->setInc('bonus_wallet', $platform_coin_push);
					
						// 把直推收益记录插入收益表
						$in_income_res2 = $this->insertToIncome($value['uid'],$upUid['pid'],$value['cu_id'],102,$value['total_money'],$giveIncome,$main_coin,$platform_coin_push,$rate,$value['order_no'],$time);
						// 插入直推日志
						$drLogNote = '获的直推收益:'.$giveIncome.'个,主流币'.$main_coin_push.'平台币'.$platform_coin_push;
						$in_log_res2 = $this->insertLog($value['uid'],$value['cu_id'],$drLogNote,$this->push_type,$value['order_no'],$time);
					}
					// echo $main_res.'/'.$platform_res.'/'.$in_income_res1.'/'.$in_log_res1.'/'.$main_coin_res.'/'.$platform_coin_res.'/'.$in_income_res2.'/'.$in_log_res2.'/'.$order_up_res;die;
					// +++++++++++++++++++++++++++++++++直推收益++++++++++++++++++++++++++++++++++++++++++++++ end

					// +++++++++++++++++++++++++++++++++动态收益++++++++++++++++++++++++++++++++++++++++++++++ begin
					// +++++++ 获取动态收益的上级用户uid，根据对应参数计算动态收益+++++++ //
					// 1、获取当前用户所有的上级id
					$upAll_arr = '';
					$upAll_arr = getUpMemberIds($value['uid']);
					unset($GLOBALS['g_up_mids']); // 清空上一次循环全局数据

					// 2、循环上级id获取有效直推人数和入单金额是否达到条件
					if(!$upAll_arr){
						$dy_main_coin_res = true;
						$dy_platform_coin_res = true;
						$dy_in_income_res2 = true;
						$dy_in_log_res2 = true;
					}
					if($upAll_arr){
						
						foreach($upAll_arr as $ks=>$upId){
						
							$push_arr = '';
							$push_arr = getActivateUser($upId);   // 获取当前用户所有已激活的直推会员(入单500美元以上)

							$is_order_money = isEnjoyUser($upId); // 获取当前上级是否入单指定金额
							
							if(!$push_arr || !$is_order_money){
								continue; // 不符合获取动态收益条件跳过
							}
							$push_num = count($push_arr);
							
							// // 判断当前上级有效直推人数是否大于等于当前层数，如果大于获得动态收益
							// // 获取后台设置的获得层数设置
							if($push_num == $configs['people_num1']['value'] && $ks+1 <= $configs['layer1']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num2']['value'] && $ks+1 <= $configs['layer2']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num3']['value'] && $ks+1 <= $configs['layer3']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num4']['value'] && $ks+1 <= $configs['layer4']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num5']['value'] && $ks+1 <= $configs['layer5']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num6']['value'] && $ks+1 <= $configs['layer6']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num7']['value'] && $ks+1 <= $configs['layer7']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num8']['value'] && $ks+1 <= $configs['layer8']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num9']['value'] && $ks+1 <= $configs['layer9']['value']){

								$dy_is_true = true;
							}elseif($push_num == $configs['people_num10']['value'] && $ks+1 <= $configs['layer10']['value']){

								$dy_is_true = true;
							}else{
								$dy_is_true = false;
							}
							
							// // +++++ 把动态收益累加到获得收益的用户对应币种钱包+++++ //
							if($dy_is_true){
							
								$earnings_rate = $configs['earnings_rate']['value']?$configs['earnings_rate']['value']:0;
								if(!$earnings_rate){
									$dy_total_income =0;
									$dy_main_coin = 0;
									$dy_platform_coin = 0;
								}else{
									$dy_total_income = 0;
									$dy_platform_coin = 0;
									$dy_total_income = $giveIncome*($earnings_rate/100);
									// 根据后台设置的比例把总收益拆分为主流币+平台代币
									$dy_main_coin = $dy_total_income*($main_coin_ratio/100);
									$dy_platform_coin = $dy_total_income*($platform_coin_ratio/100);
									$dy_platform_coin = $dy_platform_coin*$value['price']/$htd_price['price'];
								}

								$whereDy['uid'] = $upId;
								$whereDy['cu_id'] = $value['cu_id']; // 对应币种
								// 插入到主流币
								$dy_main_coin_res = Db::name('user_wallet')->where($whereDy)->setInc('bonus_wallet', $dy_main_coin);
								// 插入到平台币 cu_id=11
								$dy_platform_coin_res = Db::name('user_wallet')->where(['uid'=>$upId, 'cu_id'=>11])->setInc('bonus_wallet', $dy_platform_coin);

								// 把动态收益记录插入收益表
								$dy_in_income_res2 = $this->insertToIncome($value['uid'],$upId,$value['cu_id'],103,$value['total_money'],$dy_total_income,$dy_main_coin,$dy_platform_coin,$earnings_rate,$value['order_no'],$time);
								// 插入直推日志
								$dyLogNote = '获的动态收益:'.$dy_total_income.'个,主流币'.$dy_main_coin.'平台币'.$dy_platform_coin;
								$dy_in_log_res2 = $this->insertLog($value['uid'],$value['cu_id'],$dyLogNote,$this->dy_type,$value['order_no'],$time);
							}
							
						}

					}
					// +++++++++++++++++++++++++++++++++动态收益++++++++++++++++++++++++++++++++++++++++++++++ end
					// +++++ 更新当前订单收益时间 +++++ //
					$order_up_res = Db::name('buy_order')->where(['id'=>$value['id']])->update(['out_income_time'=>time()]);

					// 提交事务
					Db::commit();  

				}catch(\Exception $e){
					// 回滚事务
					Db::rollback();
					echo  time()." the orders err\n";
				}
				$i++;
			}
			echo  time().'|'.$i." ok orders\n";
			
		}else{
			echo time()." No dispose the orders\n";
			exit;
		}
		
	}
	
	// 插入收益表
	public function insertToIncome($uid, $get_uid, $cu_id, $type, $base_money, $total_coin, $main_coin, $htd_coin, $percent, $order_no, $time){

		$data = array(
			'uid' => $uid,
			'get_uid' => $get_uid,
			'order_no' => $order_no,
			'cu_id' => $cu_id,
			'type' => $type,
			'total_coin' => $total_coin,
			'base_money' => $base_money,
			'main_coin' => $main_coin,
			'htd_coin' => $htd_coin,
			'percent' => $percent,
			'out_flag' => 1,
			'create_time' => $time,
			'out_time' => $time
		);
		$res1 = Db::name('income')->insert($data);
		return $res1;
	}

	// 插入日志表
	public function insertLog($uid,$cu_id,$note,$type,$order_no,$time){

		$data = array(
			'uid' => $uid,
			'order_no' => $order_no,
			'cu_id' => $cu_id,
			'note' => $note,
			'type' => $type,
			'create_time' => $time
		);
		$res2 = Db::name('user_log')->insert($data);
		return $res2;
	}

	// 生成钱包记录
	public function byUserWallet($uid,$cu_id){
		if(!$uid){
			return true;
		}
		// 判断当前投资币种是否存在钱包表，如果没有插入一条
		$wallet_is = Db::name('user_wallet')->where(['uid'=>$uid,'cu_id'=>$cu_id])->find();
		if(!$wallet_is){
			$data_in = array(
				'uid' => $uid,
				'cu_id' => $cu_id
			);
			$res11 = Db::name('user_wallet')->insert($data_in);
		}
		return true;
	}
}