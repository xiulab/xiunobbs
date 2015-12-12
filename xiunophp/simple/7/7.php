<?php

/*
	功能：演示表单操作
	注意：
		请使用浏览器访问 http://domain/xiunophp/simple/7/7.php
*/

chdir('../../../');

include './xiunophp/xiunophp.php';
include './xiunophp/form.func.php';

$input_name = form_text('name', 'Jack', 150);
$input_password = form_password('password', '', 150);

if($method === 'GET') {
	include './xiunophp/simple/7/7.htm';
} else {
	$name = param('name');
	$password = param('password');
	echo '您输入的用户名为：'.$name.', 密码是：'.$password;
}

?>