<?php

// hook check_func_php_start.php

function is_mobile($mobile, &$err) {
	// hook is_mobile_start.php
	if(!preg_match('#^\d{11}$#', $mobile)) {
		$err = '手机格式不正确';
		return FALSE;
	}
	// hook is_mobile_end.php
	return TRUE;
}

function is_email($email, &$err) {
	// hook is_email_start.php
	$len = mb_strlen($email, 'UTF-8');
	if(strlen($len) > 32) {
		$err = 'Email 太长:'.$len;
		return FALSE;
	} elseif(!preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/i', $email)) {
		$err = 'Email 格式不正确';
		return FALSE;
	}
	// hook is_email_end.php
	return TRUE;
}

function is_username($username, &$err = '') {
	// hook is_username_start.php
	$len = mb_strlen($username, 'UTF-8');
	if($len > 16) {
		$err = '用户名太长:'.$len;
		return FALSE;
	} elseif(!preg_match('#^[\w\x{4E00}-\x{9FA5}\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+$#u', $username)) {
		// 4E00-9FA5(中文)  1100-11FF(朝鲜文) 3130-318F(朝鲜文兼容字母) AC00-D7AF(朝鲜文音节)
		$err = '用户名格式不正确';
		return FALSE;
	}
	// hook is_username_end.php
	return TRUE;
}

// hook check_func_php_end.php

?>