<?php

!defined('DEBUG') AND exit('Access Denied.');

$smtplist = include './conf/smtp.conf.php';
include './xiunophp/form.func.php';
include './model/smtp.func.php';

$action = param(1);

if($action == 'list') {

	if($method == 'GET') {
		$header['title']    = 'SMTP 管理';
	
		$smtplist = smtp_find();
		$maxid = smtp_maxid();
		
		$readable = is_writable('./conf/smtp.conf.php');
		
		$default = array('host'=>'smtp.sina.com', 'port'=>25, 'user'=>'xxxx', 'email'=>'xxxx@sina.com', 'pass'=>'xxxx');
		empty($smtplist) AND $smtplist = array($default);
	
		$input_user_create_email_on = form_radio_yes_no('user_create_email_on', $conf['user_create_email_on']);
		$input_user_find_pw_on = form_radio_yes_no('user_find_pw_on', $conf['user_find_pw_on']);
		
		include "./admin/view/smtp_list.htm";
	}

// SMTP更新
} elseif($action == 'setting') {
	
	$conf['user_create_email_on'] = param('user_create_email_on', 0);
	$conf['user_find_pw_on'] = param('user_find_pw_on', 0);
	conf_save();
	message(0, '保存成功');
	
} elseif($action == 'update') {

	$id = param(2, 0);
	$email = param('email');
	$host = param('host');
	$port = param('port', 0);
	$user = param('user');
	$pass = param('pass');
	
	empty($email) AND message(1, 'Email 不能为空');
	empty($host) AND message(2, 'SMTP 主机不能为空');
	empty($port) AND message(3, 'SMTP 端口不能为空');
	empty($user) AND message(4, 'SMTP 用户名不能为空');
	empty($pass) AND message(5, '密码不能为空');
	
	$smtp = smtp_read($id);
	$arr = array(
		'host'	=> $host,
		'port'	=> $port,
		'user'	=> $user,
		'email'	=> $email,
		'pass'	=> $pass,
	);
	if(empty($smtp)) {
		$r = smtp_create($arr);
		message(0, '创建成功');
	} else {
		smtp_update($id, $arr);
		message(0, '更新成功');
	}
	
} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$id = param(2, 0);
	$smtp = smtp_read($id);
	empty($smtp) AND message(1, 'SMTP不存在');
	
	$r = smtp_delete($id);
	message(0, '删除成功');

}

?>
