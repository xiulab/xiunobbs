<?php
!defined('DEBUG') AND exit( 'Access Denied.' );
$action = param(0, 'index');
$header['title'] = lang('wechat_pay');
$header['mobile_title'] = lang('wechat_pay');
switch ( $action ) {
	case 'index'://支付请求
		empty( $conf['wx_mkpr'] ) AND message(1, '支付已关闭');
		$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
		$agent = stripos($agent, 'MicroMessenger');
		if ( $agent !== false ) {
			$input['trade_type'] = 'JSAPI';
		} else {
			$input['trade_type'] = 'NATIVE';
			$input['product_id'] = $uid;
		}
		$input['total_fee'] = param('price', 5);
		$input['content'] = $user['username'] . '充值金币' . (int)$input['total_fee'] * $conf['wx_mkpr'] . '枚';
		$input['total_fee'] *= 100;
		
		$open_user = db_find_one('user_open_weixin', array( 'uid' => $uid ));
		$str='<div style="text-align: center"><img src="'.$conf['wx_xcode'].'" width="200" height="200"></div>';
		$str.='请先用微信扫描二维码,绑定帐号以后才能支付!<br>Please use first WeChat scan qr code,<br> after binding account to pay!';
		empty( $open_user['openid'] ) AND message(1, $str);
		
		$input['openid'] = $open_user['openid'];
		
		$wxpay = new wechatpay($conf);
		$order = $wxpay->unifiedOrder($input);
		
		$input['out_trade_no'] = $order['out_trade_no'];
		$jsApiParameters = $wxpay->GetJsApiParameters($order);
		$input['price'] = $input['total_fee'] / 100;
		$rid = db_create('user_paylog', array( 'type' => 1, 'uid' => $uid, 'regdate' => time(), 'rmbs' => $input['total_fee'], 'pay' => $input['out_trade_no'] ));
		if ( $rid ) {
			
			$input['return_url'] = url('my-profile');
			include './plugin/xn_wechat_pay/view/htm/pay.htm';
		} else {
			message(1, '订单创建失败');
		}
		break;
	default:
		message(1, '此功能还没有');
}
?>