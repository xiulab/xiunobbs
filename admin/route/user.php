<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if(empty($action) || $action == 'list') {

	$header['title']    = '用户管理';

	$pagesize = 20;
	$srchtype = param(2);
	$keyword  = trim(urldecode(param(3)));
	$page     = param(4, 0);

	$cond = array();
	if($keyword) {
		!in_array($srchtype, array('uid', 'username', 'email', 'gid', 'create_ip')) AND $srchtype = 'uid';
		$cond[$srchtype] = $srchtype == 'create_ip' ? ip2long($keyword) : $keyword; 
	}

	$n = user_count($cond);
	$page = page($page, $n, $pagesize);
	$userlist = user_find($cond, array('uid'=>-1), $page, $pagesize);
	$pagination = pagination(url("admin/user-list-$srchtype-".urlencode($keyword).'-{page}'), $n, $page, $pagesize);
	$pager = pager(url("admin/user-list-$srchtype-".urlencode($keyword).'-{page}'), $n, $page, $pagesize);

	foreach ($userlist as &$_user) {
		$_user['group'] = array_value($grouplist, $_user['gid'], '');
	}

	include "./admin/view/user_list.htm";

} elseif($action == 'create') {

	if($method == 'GET') {

		$header['title'] = '用户创建';

		include "./admin/view/user_create.htm";

	} elseif ($method == 'POST') {

		$email = param('email');
		$username = param('username');
		$password = param('password');
		$gid = param('gid');

		$email AND !is_email($email, $err) AND message(2, $err);
		$username AND !is_username($username, $err) AND message(3, $err);
		// !is_password($password, $err) AND message(4, $err);

		$user = user_read_by_email($email);
		$user AND message(2, '用户 EMAIL 已经存在');

		$user = user_read_by_username($username);
		$user AND message(3, '用户已经存在');

		$salt = mt_rand(10000000, 9999999999);
		$state = user_create(array(
			'username'=>$username,
			'password'=>md5($password.$salt),
			'salt'=>$salt,
			'gid'=>$gid,
			'email'=>$email,
			'create_ip'=>ip2long(ip()),
			'create_date'=>$time
		));
		$state !== FALSE ? message(0, '创建成功') : message(11, '创建失败');

	}

} elseif($action == 'update') {

	$_uid = param(2, 0);
	
	if($method == 'GET') {

		$header['title'] = '用户更新';
		
		$user = user_read($uid);
		
		$input['email'] = form_text('email', $user['email']);
		$input['username'] = form_text('username', $user['username']);
		$input['password'] = form_password('password', $user['password']);
		$grouparr = arrlist_key_values($grouplist, 'gid', 'name');
		$input['gid'] = form_select('gid', $grouparr, $user['gid']);

		include "./admin/view/user_update.htm";

	} elseif($method == 'POST') {

		$email = param('email');
		$username = param('username');
		$password = param('password');
		$_gid = param('gid');
		
		$old = user_read($_uid);
		empty($old) AND message('username', '指定的 UID 不存在');
		
		$email AND !is_email($email, $err) AND message(2, $err);
		if($email AND $old['email'] != $email) {
			$user = user_read_by_email($email);
			$user AND message('email', '用户 EMAIL 已经存在');
		}

		$arr = array();
		$arr['email'] = $email;
	
		if($username AND $old['username'] != $username) {
			$user = user_read_by_username($username);
			$user AND message('username', '用户已经存在');
		}

		$arr['username'] = $username;
		$arr['gid'] = $_gid;

		if($password) {
			$salt = mt_rand(10000000, 9999999999);
			$arr['password'] = md5($password.$salt);
			$arr['salt'] = $salt;
		}

		$r = user_update($uid, $arr);
		$r !== FALSE ? message(0, '更新成功') : message(-1, '更新失败');
	}

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$uid = param('uid', 0);

	$state = user_delete($uid);
	$state === FALSE AND message(11, '删除失败');

	message(0, '删除成功');

} elseif($action == 'group') {
	
	if($method == 'GET') {
		
		$header['title']    = '用户组管理';
	
		include "./admin/view/user_group.htm";
	
	} else {
		
		conf_save() OR message(-1, '保存到配置文件 conf/conf.php 失败，请检查文件的可写权限。');
		
		$email = param('email', array(''));
		$host = param('host', array(0));
		$port = param('port', array(0));
		$user = param('user', array(''));
		$pass = param('pass', array(''));
		
		$smtplist = array();
		foreach ($email as $k=>$v) {
			$smtplist[$k] = array(
				'email'=>$email[$k],
				'host'=>$host[$k],
				'port'=>$port[$k],
				'user'=>$user[$k],
				'pass'=>$pass[$k],
			);
		}
		$r = file_put_content_try('./conf/smtp.conf.php', "<?php\r\nreturn ".var_export($smtplist,true).";\r\n?>");
		!$r AND message(-1, '保存数据到配置文件 conf/smtp.conf.php 失败，请检查文件的可写权限。');
		
		message(0, '保存成功');
	}
} else {
	
	http_404();
	
}
?>