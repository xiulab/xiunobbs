<?php

// 接口需要够买，请自行咨询：http://inolink.com/
function sms_send($mobile, $message, &$err) {
	global $conf;
	$arr = array(
		'0' => 	'发送成功',
		'1' => 	'发送成功',
		'-1' => 	'账号未注册',
		'-2' => 	'其他错误',
		'-3' => 	'密码错误',
		'-4' => 	'手机号格式不对',
		'-5' => 	'余额不足',
		'-6' => 	'定时发送时间不是有效的时间格式',
		'-7' => 	'提交信息末尾未加签名，请添加中文企业签名【 】',
		'-8' => 	'发送内容需在1到500个字之间',
		'-9' => 	'发送号码为空',
	);
	$message = urlencode(iconv('UTF-8', 'GBK', $message.'【'.$conf['sitename'].'】'));
	
	// 单条接口
	$s = "http://inolink.com/WS/Send.aspx?CorpID=xxx&Pwd=xxx&Mobile=$mobile&Content=$message&Cell=&SendTime=";
	
	// 批量发送接口
	//$s = "http://inolink.com/WS/BatchSend.aspx?CorpID=TCLKxxx&Pwd=xxx&Mobile=$mobile&Content=$message&Cell=&SendTime=";
	
	$r = http_get($s, 10, 3);
	xn_log($s.',return:'.$r, 'sms');
	
	if($r === FALSE) {
		$err = '短信网关超时';
		return FALSE;
	}
	if(!isset($arr[$r])) {
		$err = '网关返回的数据有问题'.$r;
		return FALSE;
	}
	if($r == '1' || $r == '0') {
		return TRUE;
	} else {
		$err = $arr[$r];
		return $r;
	}
}

?>
