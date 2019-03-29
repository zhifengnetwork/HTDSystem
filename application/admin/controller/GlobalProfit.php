<?php
namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Db;

class GlobalProfit
{
	
	private $key = '160da32821f8bfe1c7dd3c86d64b946a';
	/* 
	* 根据后台设置的分红时间进行全球分红操作
	* 1、获取后台全球分红配置信息
	* 2、获取所有用户的user_wallet表
	* 3、循环计算当前用户的当前币种动静态收益的总数*分红比例
	* 4、把当前用户对应币种的分红累加到分红钱包
	*/
    public function globalToProfit()
    {
			$get_key = input('key/s');
			if(!$get_key){
				return 'No .....';
				exit;
			}
			if($get_key!=$this->key){
				return 'No ...';
				exit;
			}
			// 获取配置表 profit_day 分红天数 , profit_rate 分红比例
			$configs = Db::name('income_config')->field('name,value')->where('name', 'in', ['profit_day', 'profit_rate'])->select();
			// 把配置项name转换成$configs['profit_day']['value']
			$configs = arr2name($configs);

			// 获取上一次分红时间和后台设置的天数进行比对
			$global_one = Db::name('global_profit')->order('out_time desc')->find();
			if(!$global_one){
				return 'No Exit';
				exit;
			}
			if(!$configs['profit_rate']['value']){
				return 'No Exit';
				exit;
			}
			$day_num = $configs['profit_rate']['value']?$configs['profit_rate']['value']:1;
			// 计算是否到分红时间
			$out_times = $global_one['out_time']+($configs['profit_day']['value']*3600*24);
			// 比对上一次分红时间
			if(time() < $out_times){
				return 'No Time';
				exit;
			}

			$rate = $configs['profit_rate']['value']>0?$configs['profit_rate']['value']:10;
			// 获取所有用户的user_wallet
			$where['bonus_wallet'] = array('>',0);
			$all_user_wallet = Db::name('user_wallet')->field('id,uid,cu_id,cu_num,bonus_wallet,rate_wallet,status')->where($where)->select();
			if($all_user_wallet){

				// 循环累加动静态收益*分红比例
				foreach($all_user_wallet as $k=>$v){
					$total_num = 0;
					if($v['bonus_wallet']>0){
						$is_order_res = isEnjoyUser($v['uid']);
						if(!$is_order_res){
							continue; // 不符合获取动态收益条件跳过
						}
						$total_num  = $v['bonus_wallet']*($rate/100);
						$total_num = numberByRetain($total_num, 8);
						// 开启事务
						Db::startTrans();
						try{
							$user_where = ['uid'=>$v['uid'], 'cu_id'=>$v['cu_id']];
							// 获取累加前数量
							$old_num = Db::name('user_wallet')->field('id,uid,cu_id,rate_wallet')->where($user_where)->find();
							// 累加到分红钱包
							$inc_res = Db::name('user_wallet')->where($user_where)->setInc('rate_wallet', $total_num);

							// 插入收益记录表
							$in_income = array(
								'uid' => $v['uid'],
								'get_uid' => $v['uid'],
								'cu_id' => $v['cu_id'],
								'type'	=> 104,
								'total_coin' => $v['bonus_wallet'], // 分红总币数
								'main_coin' => $total_num,
								'percent' => $rate,
								'create_time' => time(),
								'out_flag' => 1,
								'out_time' => time()

							);
							$res_log = Db::name('income')->insert($in_income);

							// 插入日志
							$in_log = array(
								'uid' => $v['uid'],
								'cu_id' => $v['cu_id'],
								'type'	=> 104,
								'old_account' => $old_num['rate_wallet'],
								'now_account' => $old_num['rate_wallet']+$total_num,
								'note'	=> '全球分红',
								'create_time' => time()
							);
							$res_log = Db::name('user_log')->insert($in_log);

							// 插入一条记录到htd_global_profit
							$in_global = array(
								'out_time' => time(),
								'note'	=> '全球分红'
							);
							$res_global = Db::name('global_profit')->insert($in_global);

							// 提交事务
							Db::commit();   
							echo  time()." ok \n";

						}catch(\Exception $e){
							// 回滚事务
							Db::rollback();
							echo  time()." err \n";
						}

					}
				}
			}

    }
   
}