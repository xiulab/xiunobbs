
if ( empty( $user ) && empty( $code ) && empty( $openid ) && !empty( $conf['wx_appkey'] ) ) {
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
	if ( stripos($agent, 'MicroMessenger') !== false ) {
		http_location('./plugin/xn_wechat_public/route/login.php');
	}
}