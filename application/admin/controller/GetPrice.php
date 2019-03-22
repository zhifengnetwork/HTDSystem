<?php
namespace app\admin\controller;

use think\Db;

class CheckOrder 
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
		foreach($url_arr as $k=>$v){
			$res = '';
			$res = getUrl($v);
			$currency['status'] = $res['status'];
			$currency['ch'] =  $res['ch'];
			$currency['cu_name'] =  $k;
			$currency['price'] =  $res['tick']['data'][0]['price'];
			// update币种表对应币种价格
			if($currency['status']=='ok' && $currency['price']>0){
				$updates['price'] = $currency['price'];
				$updates['update_time'] = $currency['update_time'];
				Db::name('currency')->where(['alias_name'=>$currency['cu_name']])->update($updates);
			}
		}

		echo 'ok';
	}
   
}