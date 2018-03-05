<?php exit;

$kv_vcode = kv_get('vcode');
if(!empty($kv_vcode['vcode_thread_create_on'])) {
	if(empty(_SESSION('vcode_initpw_ok'))) {
		$vcode_post = param('vcode');
		$vcode_sess = _SESSION('vcode');
		strtolower($vcode_post) != strtolower($vcode_sess) AND message('vcode', '验证码不正确');
	}
}
?>