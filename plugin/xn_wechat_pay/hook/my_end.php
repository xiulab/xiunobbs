
elseif( $action == 'wechat_pay' ){
$page = max(1, param(2, 1));
$pagesize = 20;
$header['title'] = '充值金币 Top-up gold COINS';
$paylog = db_find('user_paylog', array( 'uid' => $uid ), array( 'rid' => -1 ), $page, $pagesize);
$n = db_count('user_paylog', array( 'uid' => $uid ));

foreach ( $paylog as &$v ) {
	$v['regdate_fmt'] = $v['regdate'] ? date('Y-n-j H:i', $v['regdate']) : '0000-00-00';
	$v['paydate_fmt'] = $v['paydate'] ? date('Y-n-j H:i', $v['paydate']) : '0000-00-00';
}
$pagination = pagination(url('my-wechat_pay-{page}'), $n, $page, $pagesize);

include './plugin/xn_wechat_pay/view/htm/wechat_pay.htm';
}
