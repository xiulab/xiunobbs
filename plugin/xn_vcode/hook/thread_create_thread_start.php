<?php exit;

$vcode_post = param('vcode');
$vcode_sess = _SESSION('vcode');
strtolower($vcode_post) != strtolower($vcode_sess) AND message('vcode', '验证码不正确');

?>