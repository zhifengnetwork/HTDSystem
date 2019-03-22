<?php
namespace app\admin\controller;

use think\Db;

class GetPrice
{
	
	public function getPriceApi(){
		header('content-type:text/html;charset=utf-8');
		// 根据url循环获取
		$url_arr = array(
			'BTC' => 'https://api.huobi.pro/market/trade?symbol=btcusdt',
			'BCH' => 'https://api.huobi.pro/market/trade?symbol=bchusdt',
			'ETH' => 'https://api.huobi.pro/market/trade?symbol=ethusdt',
			'XRP' => 'https://api.huobi.pro/market/trade?symbol=xrpusdt',
			'EOS' => 'https://api.huobi.pro/market/trade?symbol=eosusdt',
			'LTC' => 'https://api.huobi.pro/market/trade?symbol=ltcusdt',
			'TRX' => 'https://api.huobi.pro/market/trade?symbol=trxusdt',
			'ETC' => 'https://api.huobi.pro/market/trade?symbol=etcusdt',
			'HT'  => 'https://api.huobi.pro/market/trade?symbol=htusdt'
		);
		$currency_arr = [];
		// 获取配置表
		$configs = Db::name('income_config')->field('name,value')->select();
		// 把配置项name转换成$configs['price_min1']['value']
		$configs = arr2name($configs);
		foreach($url_arr as $k=>$v){
			$res = '';
			$res = getUrl($v);
			$currency['status'] = $res['status'];
			$currency['ch'] =  $res['ch'];
			$currency['cu_name'] =  $k;
			$currency['price'] =  $res['tick']['data'][0]['price']*$configs['exchange_usd']['value'];
			// update币种表对应币种价格
			if($currency['status']=='ok' && $currency['price']>0){
				$updates['price'] = $currency['price'];
				$updates['update_price_time'] = time();
				Db::name('currency')->where(['alias_name'=>$currency['cu_name']])->update($updates);
			}
		}

		echo 'ok';
	}
   
}