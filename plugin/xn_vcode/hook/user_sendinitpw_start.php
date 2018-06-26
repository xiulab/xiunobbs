<?php exit;

$kv_vcode = kv_get('vcode');
if(!empty($kv_vcode['vcode_user_findpw_on']) && vcode_on($kv_vcode)) {
		
	$vcode_post = param('vcode');
	$vcode_sess = _SESSION('vcode');
	strtolower($vcode_post) != strtolower($vcode_sess) AND message('vcode', '验证码不正确');
	
	// 随机设置一下，防止重用
	$_SESSION['vcode'] = xn_rand(16);
	$_SESSION['vcode_initpw_ok'] = TRUE; // 注册的时候不再重复校验
}
?>