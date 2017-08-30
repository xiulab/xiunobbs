<?php

$openid = true;
include _include(APP_PATH.'plugin/xn_wechat_public/model/wechat.func.php');
if ( $method == 'POST' ) {
	$email = param('email');                        // 邮箱或者手机号 / email or mobile
	$password = param('password');
	$openid = xn_decrypt(param('openid'));
	empty( $openid ) AND message('password', '错误的授权秘钥');
	empty( $email ) AND message('email', lang('email_is_empty'));
	if ( is_email($email, $err) ) {
		$user = user_read_by_email($email);
		empty( $user ) AND message('email', lang('email_not_exists'));
	} else {
		$user = user_read_by_username($email);
		empty( $user ) AND message('email', lang('username_not_exists'));
	}
	!is_password($password, $err) AND message('password', $err);
	md5($password . $user['salt']) != $user['password'] AND message('password', lang('password_incorrect'));
	user_update($user['uid'], array( 'login_ip' => $longip, 'login_date' => $time, 'logins+' => 1 ));
	$uid = $user['uid'];
	$open_user = db_find_one('user_open_wechat', array( 'uid' => $uid ));
	!empty( $open_user['uid'] ) AND message(1, '无需重复授权,谢谢!');
	$open_user = db_find_one('user_open_wechat', array( 'openid' => $openid ));
	!empty( $open_user['uid'] ) AND message(1, '无需重复授权,谢谢!');
	db_update('user_open_wechat', array( 'openid' => $openid ), array( 'uid' => $uid ));
    $_SESSION['uid'] = $uid;
    user_token_set($user['uid']);
    message(0, ' 授权登录成功!');
}
$code = param('code');
$auto = param(1, '');
$auto=  $auto=='auto' ? 1:0;
$state = param('state');
if ( $code ) {
	wechat_get_token($code, $state);
} else {
	wechat_login_link($auto);
}
?>