<?php

function is_mobile($mobile, &$err) {
	if(!preg_match('#^\d{11}$#', $mobile)) {
		$err = '手机格式不正确';
		return FALSE;
	}
	return TRUE;
}

function is_email($email, &$err) {
	if(!preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/i', $email)) {
		$err = 'Email 格式不正确';
		return FALSE;
	}
	return TRUE;
}

function is_username($username, &$err = '') {
	$len = mb_strlen($username, 'UTF-8');
	if($len > 16) {
		$err = '用户名太长:'.$len;
		return FALSE;
	} elseif(!preg_match('#^[\w\x{4E00}-\x{9FA5}\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+$#u', $username)) {
		// 4E00-9FA5(中文)  1100-11FF(朝鲜文) 3130-318F(朝鲜文兼容字母) AC00-D7AF(朝鲜文音节)
		$err = '用户名格式不正确';
		return FALSE;
	}
	return TRUE;
}

function is_password($password, &$err = '') {
	if(strlen($password) < 8) {
		$err = '密码太短';
		return FALSE;
	} elseif(strlen($password) > 32) {
		$err = '密码太长';
		return FALSE;
	}
	return TRUE;
}

?>
