<?php
define('DEBUG', 0);
define('BASE_HREF', '../../');
define('SKIP_ROUTE', true);
// 跳过路由处理，否则 index.php 中会中断流程
chdir('../../');
include './index.php';
include './plugin/xn_wechat_pay/model/wechatpay.class.php';
include './plugin/xn_wechat_public/model/wechat.class.php';
$xml = file_get_contents("php://input");
//$xml='<xml><appid><![CDATA[wx0c5f72b064b4e880]]></appid> <bank_type><![CDATA[CFT]]></bank_type> <cash_fee><![CDATA[100]]></cash_fee> <fee_type><![CDATA[CNY]]></fee_type> <is_subscribe><![CDATA[Y]]></is_subscribe> <mch_id><![CDATA[1325378701]]></mch_id> <nonce_str><![CDATA[o8ju49pdyru7s0m3vrthp0pu0jxk2kun]]></nonce_str> <openid><![CDATA[ooyfVvgJvTOF6T64qwXrrO47uEAY]]></openid> <out_trade_no><![CDATA[wx14711406606571]]></out_trade_no> <result_code><![CDATA[SUCCESS]]></result_code> <return_code><![CDATA[SUCCESS]]></return_code> <sign><![CDATA[6BE8AB5476A0B340EA968EF7B4A7DA5A]]></sign> <time_end><![CDATA[20160814101106]]></time_end> <total_fee>100</total_fee> <trade_type><![CDATA[JSAPI]]></trade_type> <transaction_id><![CDATA[4000482001201608141304065313]]></transaction_id> </xml>';
$wxpay = new wechatpay($conf);
$_input = $wxpay->FromXml($xml);
if ( empty( $_input['out_trade_no'] ) ) {
	exit( 'FAIL' );
} else {
	xn_log($xml, 'paylog');
	if ( xn_lock_start($_input['out_trade_no'], 100) == false )
		exit( 'FAIL' );
	$paylog = db_find_one('user_paylog', array( 'pay' => $_input['out_trade_no'] ));
	if ( empty( $paylog ) ) {
		xn_lock_end($_input['out_trade_no']);
		exit( 'FAIL' );
	} elseif ( !empty( $paylog['paydate'] ) ) {
		xn_lock_end($_input['out_trade_no']);
		exit( 'SUCCESS' );
	}
	$input['out_trade_no'] = $_input['out_trade_no'];
	$order = $wxpay->orderQuery($input);
	if ( $order['result_code'] == 'SUCCESS' && $order['trade_state'] == 'SUCCESS' ) {
		$rmbs = $_input['total_fee'] * 0.01 * $conf['wx_mkpr'];
		db_update('user_paylog', array( 'rid' => $paylog['rid'] ), array( 'paydate' => time(), 'transaction_id' => $_input['transaction_id'] ));
		$rmbs >= 1 AND user_update($paylog['uid'], array( 'rmbs+' => $rmbs ));
		xn_lock_end($_input['out_trade_no']);
		$_input['desc'] = '您有一笔' . ( $_input['total_fee'] * 0.01 ) . '元的充值已经到账\n到账时间：' . date('Y-n-j H:i:s', $time) . '\n查看更多<a href=\"http://' . _SERVER('HTTP_HOST') . '/' . url('my-wechat_pay') . '\">订单详情</a>';
		
		$wechat_class = new wechat($conf);
		//xn_log(xn_json_encode($_input), 'wechat_send_msg_error');
		$wechat_class->send_msg($_input);
		exit( 'SUCCESS' );
	} else {
		xn_lock_end($_input['out_trade_no']);
		exit( 'FAIL' );
	}
}
?>