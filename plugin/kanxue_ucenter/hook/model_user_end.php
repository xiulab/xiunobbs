<?php exit;

// 高效率的方法：放入队列，由队列来更新。
function kanxue_ucenter_user_update($uid, $update) {
	global $conf;
	ksort($update);
	$s = http_build_query($update);
	$update['sign'] = md5($s.$conf['auth_key']);
	$r = http_post($url, $update, '', 10, 3); // 重试 3 次。
	return $r;
}

?>