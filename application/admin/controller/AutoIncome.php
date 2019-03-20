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

	/* 
	*	每天执行一次收益动作
	* 	获取所有已经后台审核的订单并且创建时间不等于当天的
	*   循环所有符合条件订单发放静态收益、直推收益、动态收益
	*/
  public function autoToIncome(){

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
			$i = 0;
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
				
				$rate = numberByRetain($rate/30, 8); // 除于30天取8位
				// +++++++ 计算当前用户静态收益：币种收益=收益率*币种数量 +++++++ //
				$giveIncome = $value['num']*$rate; 
				// 根据后台设置的比例把总收益拆分为主流币+平台代币
				$main_coin = $giveIncome*($main_coin_ratio/100);
				$platform_coin = $giveIncome*($platform_coin_ratio/100);
				
				// 开启事务
				Db::startTrans();
				try{

					// +++++++++++++++++++++++++++++++++静态收益++++++++++++++++++++++++++++++++++++++++++++++ begin
					// +++++ 把收益累加到当前用户对应币种钱包+++++ //
					$main_res = Db::name('user_wallet')->where(['uid'=>$value['uid'], 'cu_id'=>$value['cu_id']])->setInc('bonus_wallet', $main_coin);
					// echo Db::name('user_wallet')->getLastSql();die;
					// 累加到平台币 cu_id=11
					$platform_res = Db::name('user_wallet')->where(['uid'=>$value['uid'], 'cu_id'=>11])->setInc('bonus_wallet', $platform_coin);
					// 把收益记录插入收益表
					$in_income_res1 =$this->insertToIncome($value['uid'],$value['uid'],$value['cu_id'],101,$value['total_money'],$giveIncome,$main_coin,$platform_coin,$rate,$value['order_no']);
					// 插入日志
					$logNote = '获得静态收益:'.$giveIncome.'个,主流币'.$main_coin.'平台币'.$platform_coin;
					$in_log_res1 = $this->insertLog($value['uid'],$value['cu_id'],$logNote,$this->static_type,$value['order_no']);
					// 把相应收益累加到直推上级
					$upUid = Db::name('user')->field('id,pid')->where(['id'=>$value['uid']])->find();
					// echo $main_res.'/'.$platform_res.'/'.$in_income_res1.'/'.$in_log_res1;die;
					// +++++++++++++++++++++++++++++++++静态收益++++++++++++++++++++++++++++++++++++++++++++++ end

				
					// +++++++++++++++++++++++++++++++++直推收益++++++++++++++++++++++++++++++++++++++++++++++ begin
					// 如果当前用户的pid=0 则不进入上级收益操作
					if(!$upUid['pid']){
						$main_coin_res = true;
						$platform_coin_res = true;
						$in_income_res2 = true;
						$in_log_res2 = true;
					}
					// +++++ 把收益累加到当前用户的直推用户+++++ //
					if($upUid['pid']){

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
						}
						
						$whereUp['uid'] = $upUid['pid'];
						$whereUp['cu_id'] = $value['cu_id']; // 对应币种
						$main_coin_res = Db::name('user_wallet')->where($whereUp)->setInc('bonus_wallet', $main_coin_push);
						// echo Db::name('user_wallet')->getLastSql();die;

						// 插入到平台币 cu_id=11
						$platform_coin_res = Db::name('user_wallet')->where(['uid'=>$upUid['pid'], 'cu_id'=>11])->setInc('bonus_wallet', $platform_coin_push);
						// echo Db::name('user_wallet')->getLastSql();die;
						// echo $main_coin_res.'/'.$platform_coin_res;die;

						// 把直推收益记录插入收益表
						$in_income_res2 = $this->insertToIncome($value['uid'],$upUid['pid'],$value['cu_id'],102,$value['total_money'],$giveIncome,$main_coin,$platform_coin_push,$rate,$value['order_no']);
						// 插入直推日志
						$drLogNote = '获的直推收益:'.$giveIncome.'个,主流币'.$main_coin_push.'平台币'.$platform_coin_push;
						$in_log_res2 = $this->insertLog($value['uid'],$value['cu_id'],$drLogNote,$this->push_type,$value['order_no']);
					}
					// echo $main_res.'/'.$platform_res.'/'.$in_income_res1.'/'.$in_log_res1.'/'.$main_coin_res.'/'.$platform_coin_res.'/'.$in_income_res2.'/'.$in_log_res2.'/'.$order_up_res;die;
					// +++++++++++++++++++++++++++++++++直推收益++++++++++++++++++++++++++++++++++++++++++++++ end


					// +++++++++++++++++++++++++++++++++动态收益++++++++++++++++++++++++++++++++++++++++++++++ begin
					// +++++++ 获取动态收益的上级用户uid，根据对应参数计算动态收益+++++++ //
					// 1、获取当前用户所有的上级id
					$upAll_arr = getUpMemberIds($value['uid']);
					// $dy_income_data = []; // 获得动态收益的上级id集
					// 2、循环上级id获取有效直推人数和当前币种的入单金额是否达到条件
					if(!$upAll_arr){
						$dy_main_coin_res = true;
						$dy_platform_coin_res = true;
						$dy_in_income_res2 = true;
						$dy_in_log_res2 = true;
					}
					if($upAll_arr){

						foreach($upAll_arr as $k=>$upId){
							$push_arr = getActivateUser($upId);   // 获取当前用户所有已激活的直推会员(入单)
							$is_order_money = isEnjoyUser($upId); // 获取当前上级是否入单同等币种指定金额
							if(!$push_arr || !$is_order_money){
								continue; // 不符合获取动态收益条件跳过
							}
							$push_num = count($push_arr);
							// 判断当前上级有效直推人数是否大于等于当前层数，如果大于获得动态收益
							// 获取后台设置的获得层数设置
							if($push_num >= $configs['people_num1']['value'] && $k+1 >= $configs['layer1']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num2']['value'] && $k+1 >= $configs['layer2']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num3']['value'] && $k+1 >= $configs['layer3']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num4']['value'] && $k+1 >= $configs['layer4']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num5']['value'] && $k+1 >= $configs['layer5']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num6']['value'] && $k+1 >= $configs['layer6']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num7']['value'] && $k+1 >= $configs['layer7']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num8']['value'] && $k+1 >= $configs['layer8']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num9']['value'] && $k+1 >= $configs['layer9']['value']){
								$dy_is_true = true;
							}elseif($push_num >= $configs['people_num10']['value'] && $k+1 >= $configs['layer10']['value']){
								$dy_is_true = true;
							}else{
								$dy_is_true = false;
							}

							// +++++ 把动态收益累加到获得收益的用户对应币种钱包+++++ //
							if($dy_is_true){

								$earnings_rate = $configs['earnings_rate']['value']?$configs['earnings_rate']['value']:0;
								if(!$earnings_rate){
									$dy_total_income =0;
									$dy_main_coin = 0;
									$dy_platform_coin = 0;
								}else{
									$dy_total_income = $giveIncome*($earnings_rate/100);
									// 根据后台设置的比例把总收益拆分为主流币+平台代币
									$dy_main_coin = $dy_total_income*($main_coin_ratio/100);
									$dy_platform_coin = $dy_total_income*($platform_coin_ratio/100);
								}

								$whereDy['uid'] = $upId;
								$whereDy['cu_id'] = $value['cu_id']; // 对应币种
								// 插入到主流币
								$dy_main_coin_res = Db::name('user_wallet')->where($whereDy)->setInc('bonus_wallet', $dy_main_coin);
								// 插入到平台币 cu_id=11
								$dy_platform_coin_res = Db::name('user_wallet')->where(['uid'=>$upId, 'cu_id'=>11])->setInc('bonus_wallet', $dy_platform_coin);

								// 把动态收益记录插入收益表
								$dy_in_income_res2 = $this->insertToIncome($value['uid'],$upId,$value['cu_id'],103,$value['total_money'],$dy_total_income,$dy_main_coin,$dy_platform_coin,$earnings_rate,$value['order_no']);
								// 插入直推日志
								$dyLogNote = '获的动态收益:'.$dy_total_income.'个,主流币'.$dy_main_coin.'平台币'.$dy_platform_coin;
								$dy_in_log_res2 = $this->insertLog($value['uid'],$value['cu_id'],$dyLogNote,$this->dy_type,$value['order_no']);
							}
							// // 循环获取当前上级的所有下线
							// $upDown = getDownUserUids($upId);
							// p($upDown);die;
							// $dy_income_data[$k]['upId'] = $upId;	// 获得动态收益的上级
							// $dy_income_data[$k]['push_num'] = count($push_arr); // 直推有效会员人数
						}
					}
					// +++++++++++++++++++++++++++++++++动态收益++++++++++++++++++++++++++++++++++++++++++++++ end

					// +++++ 更新当前订单收益时间 +++++ //
					$order_up_res = Db::name('buy_order')->where(['id'=>$value['id']])->update(['out_income_time'=>time()]);
					// echo $main_res.'/'.$platform_res.'/'.$in_income_res1.'/'.$in_log_res1.'/'.$main_coin_res.'/'.$platform_coin_res.'/'.$in_income_res2.'/'.$in_log_res2.'/'.$order_up_res;die;

					// 提交事务
					Db::commit();   
					echo '成功处理订单收益';
				}catch(\Exception $e){
					// 回滚事务
					Db::rollback();
					echo '处理订单收益失败';
				}
				$i++;
			}
			echo '<br/>';
			echo '循环处理完成'.$i.'次';
			
		}else{
			echo '没有要处理的订单';
			exit;
			// echo "No dispose the orders\n";
		}
		// p($orderAll);
		// return json(array('code' => 200, 'msg' => '订单审核成功！'));
	}
	
	// 插入收益表
	public function insertToIncome($uid, $get_uid, $cu_id, $type, $base_money, $total_coin, $main_coin, $htd_coin, $percent, $order_no){

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
			'create_time' => time(),
			'out_time' => time()
		);
		$res1 = Db::name('income')->insert($data);
		return $res1;
	}

	// 插入日志表
	public function insertLog($uid,$cu_id,$note,$type,$order_no){

		$data = array(
			'uid' => $uid,
			'order_no' => $order_no,
			'cu_id' => $cu_id,
			'note' => $note,
			'type' => $type,
			'create_time' => time()
		);
		$res2 = Db::name('user_log')->insert($data);
		return $res2;
	}
}