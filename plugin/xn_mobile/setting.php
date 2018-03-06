<?php

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('mobile_setting');
	
	$input = array();
	$input['user_resetpw_on'] = form_radio_yes_no('user_resetpw_on', $kv['user_resetpw_on']);
	$input['user_create_on'] = form_radio_yes_no('user_create_on', $kv['user_create_on']);
	$input['bind_on'] = form_radio_yes_no('bind_on', $kv['bind_on']);
	$input['force_post_bind_on'] = form_radio_yes_no('force_post_bind_on', $kv['force_post_bind_on']);
	$input['force_view_bind_on'] = form_radio_yes_no('force_view_bind_on', $kv['force_view_bind_on']);
	$input['send_plat'] = form_select('send_plat', 
						array('tencent'=>'腾讯云短信平台', 'aliyun'=>'阿里云短信平台'), 
						$kv['send_plat']
					);
					
	$input['tencent_appid'] = form_text('tencent_appid', $kv['tencent_appid']);
	$input['tencent_appkey'] = form_text('tencent_appkey', $kv['tencent_appkey']);
	$input['tencent_sign'] = form_text('tencent_sign', $kv['tencent_sign']);
	$input['aliyun_appid'] = form_text('aliyun_appid', $kv['aliyun_appid']);
	$input['aliyun_appkey'] = form_text('aliyun_appkey', $kv['aliyun_appkey']);
	$input['aliyun_sign'] = form_text('aliyun_sign', $kv['aliyun_sign'], FALSE, $conf['sitename']);
	$input['aliyun_templateid'] = form_text('aliyun_templateid', $kv['aliyun_templateid'], FALSE, 'SMS_1234567');
	
	include _include(APP_PATH.'plugin/xn_mobile/setting.htm');
	
} else {

	$login_type = param('login_type', 0);
	$user_resetpw_on = param('user_resetpw_on', 0);
	$user_create_on = param('user_create_on', 0);
	$bind_on = param('bind_on', 0);
	$force_post_bind_on = param('force_post_bind_on', 0);
	$force_view_bind_on = param('force_view_bind_on', 0);
	$send_plat = param('send_plat');
	$tencent_appid = param('tencent_appid');
	$tencent_appkey = param('tencent_appkey');
	$tencent_sign = param('tencent_sign');
	$aliyun_appid = param('aliyun_appid');
	$aliyun_appkey = param('aliyun_appkey');
	$aliyun_sign = param('aliyun_sign');
	$aliyun_templateid = param('aliyun_templateid');
	
	$kv = array();
	$kv['login_type'] = $login_type;
	$kv['user_resetpw_on'] = $user_resetpw_on;
	$kv['user_create_on'] = $user_create_on;
	$kv['bind_on'] = $bind_on;
	$kv['force_post_bind_on'] = $force_post_bind_on;
	$kv['force_view_bind_on'] = $force_view_bind_on;
	$kv['send_plat'] = $send_plat;
	$kv['tencent_appid'] = $tencent_appid;
	$kv['tencent_appkey'] = $tencent_appkey;
	$kv['tencent_sign'] = $tencent_sign;
	$kv['aliyun_appid'] = $aliyun_appid;
	$kv['aliyun_appkey'] = $aliyun_appkey;
	$kv['aliyun_sign'] = $aliyun_sign;
	$kv['aliyun_templateid'] = $aliyun_templateid;
	
	kv_set('mobile_setting', $kv);
	
	message(0, '修改成功');
}
	
?>