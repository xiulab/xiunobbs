<?php

function admin_token_check() {
	global $longip, $time, $useragent, $conf;
	$admin_token = param('bbs_admin_token');
	if(empty($admin_token)) {
		$_REQUEST[0] = 'index';
		$_REQUEST[1] = 'login';
	} else {
		$s = xn_decrypt($admin_token);
		if(empty($s)) {
			setcookie('bbs_admin_token', '', 0, '', '', '', TRUE);
			message(-1, '令牌错误');
		}
		list($_ip, $_time, $_useragent_md5) = explode("\t", $s);
		// 后台超过 3600 自动退出。
		if($_ip != $longip || $_useragent_md5 != $useragent_md5 || $time - $_time > 3600) {
			setcookie('bbs_admin_token', '', 0, '', '', '', TRUE);
			message(-1, '管理登陆令牌失效，请重新登录');
		}
		// 超过半小时，重新发新令牌，防止过期
		if($time - $_time > 1800) {
			admin_token_set();
		}
	}
}

function admin_token_set() {
	global $longip, $time, $useragent, $conf;
	$admin_token = param('bbs_admin_token');
	$s = "$longip	$time	$useragent_md5";
	$admin_token = xn_encrypt($s);
	setcookie('bbs_admin_token', $admin_token, $time + 3600, '',  '', 0, TRUE);
}

function admin_token_clean() {
	global $time;
	setcookie('bbs_admin_token', '', $time - 86400, '', '', 0, TRUE);
}

// bootstrap style
function admin_tab_active($arr, $active) {
	$s = '';
	foreach ($arr as $k=>$v) {
		$s .= '<a role="button" class="btn btn btn-secondary'.($active == $k ? ' active' : '').'" href="'.$v['url'].'">'.$v['text'].'</a>';
	}
	return $s;
}
?>