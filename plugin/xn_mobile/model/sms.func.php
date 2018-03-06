<?php

include APP_PATH.'plugin/xn_mobile/model/sms_tencent.func.php';
include APP_PATH.'plugin/xn_mobile/model/sms_aliyun.func.php';

// 发送验证码接口
function sms_send_code($tomobile, $code) {
	// 根据类型调用不同的短信发送 SDK
	$kv = kv_get('mobile_setting');
	$r = FALSE;
	if($kv['send_plat'] == 'tencent') {
		$r = sms_tencent_send_code($tomobile, $code, $kv['tencent_appid'], $kv['tencent_appkey'], $kv['tencent_sign']);
	} elseif($kv['send_plat'] == 'aliyun') {
		$r = sms_aliyun_send_code($tomobile, $code, $kv['aliyun_appid'], $kv['aliyun_appkey'], $kv['aliyun_sign'], $kv['aliyun_templateid']);
	}
	return $r;
}

// sms_send('15600900902', "您的初始密码为：123456");

/*

Array
(
    [result] => 0
    [errmsg] => OK
    [ext] => 
    [sid] => 8:xxxxxxxxxxxxxxxxxxxxxxx
    [fee] => 1
)

*/

?>