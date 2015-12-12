<?php

/*
	功能：演示表单操作，并且过滤，入库。
	注意：
		请使用浏览器访问 http://domain/xiunophp/simple/8/8.php
*/

chdir('../../../');

// 请自行修改 conf.php 中的 mysql 配置
$conf = include './xiunophp/simple/8/conf.php';

include './xiunophp/xiunophp.php';
include './xiunophp/form.func.php';
include './xiunophp/simple/8/user.func.php';

$input_name = form_text('name', 'Jack', 150);
$input_password = form_password('password', '', 150);

if($method === 'GET') {
	include './xiunophp/simple/7/7.htm';
} else {
	$name = param('name');
	$password = param('password');
	$arr = array(
		'username'=>$name,
		'password'=>$password,
	);
	
	user_create_table(); // 这里为了演示使用，正式运行时一般放到安装程序里。
	$uid = user_create($arr);
	
	echo '您输入的用户名为：'.$name.', 密码是：'.$password.', uid:'.$uid;
	
}

?>