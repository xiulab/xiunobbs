<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if($action == 'list') {

	$header['title']    = '用户管理';

	$pagesize = 20;
	$srchtype = param(2);
	$keyword  = trim(urldecode(param(3)));
	$page     = param(4, 0);

	$cond = array();
	if($keyword) {
		!in_array($srchtype, array('uid', 'username', 'mobile', 'email', 'gid', 'create_ip')) AND $srchtype = 'uid';
		$cond[$srchtype] = $srchtype == 'create_ip' ? ip2long($keyword) : $keyword; 
	}

	$n = user_count($cond);
	$page = page($page, $n, $pagesize);
	$userlist = user_find($cond, array('uid'=>-1), $page, $pagesize);
	$pagehtml = pages("admin/user-list-$srchtype-".urlencode($keyword).'-{page}.htm', $n, $page, $pagesize);

	foreach ($userlist as &$_user) {
		$_user['group'] = array_value($grouplist, $_user['gid'], '');
	}

	include "./admin/view/user_list.htm";

} elseif($action == 'create') {

	if($method == 'GET') {

		$header['title'] = '用户创建';

		include "./admin/view/user_create.htm";

	} elseif ($method == 'POST') {

		$mobile = param('mobile');
		$email = param('email');
		$username = param('username');
		$password = param('password');
		$gid = param('gid');

		$mobile AND !is_mobile($mobile, $err) AND message(1, $err);
		$email AND !is_email($email, $err) AND message(2, $err);
		$username AND !is_username($username, $err) AND message(3, $err);
		// !is_password($password, $err) AND message(4, $err);

		if($mobile) {
			$user = user_read_by_mobile($mobile);
			$user AND message(1, '用户手机已经存在');
		}
		
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
			'mobile'=>$mobile,
			'create_ip'=>ip2long(ip()),
			'create_date'=>$time
		));
		$state !== FALSE ? message(0, '创建成功') : message(11, '创建失败');

	}

} elseif($action == 'update') {

	$uid = param(2, 0);
	
	if($method == 'GET') {

		$header['title'] = '用户更新';
		
		$user = user_read($uid);

		include "./admin/view/user_update.htm";

	} elseif($method == 'POST') {

		$mobile = param('mobile');
		$email = param('email');
		$username = param('username');
		$password = param('password');
		$gid = param('gid');
		
		$old = user_read($uid);

		$email AND !is_email($email, $err) AND message(2, $err);
		if($email AND $old['email'] != $email) {
			$user = user_read_by_email($email);
			$user AND message(2, '用户 EMAIL 已经存在');
		}

		$arr = array();
		$arr['email'] = $email;
		// 非管理员(gid = 1)，不允许修改其他用户的手机号、用户名、用户组、密码
		if($user['gid'] == 1) {
			$mobile AND !is_mobile($mobile, $err) AND message(1, $err);
			//$username AND !is_username($username, $err) AND message(3, $err);

			
			if($mobile AND $old['mobile'] != $mobile) {
				$user = user_read_by_mobile($mobile);
				$user AND message(1, '用户手机已经存在');
			}

			if($username AND $old['username'] != $username) {
				$user = user_read_by_username($username);
				$user AND message(3, '用户已经存在');
			}

			$arr['mobile'] = $mobile;
			$arr['username'] = $username;
			$arr['gid'] = $gid;

			if($password) {
				!is_password($password, $err) AND message(4, $err);
				$salt = mt_rand(10000000, 9999999999);
				$arr['password'] = md5($password.$salt);
				$arr['salt'] = $salt;
			}
		}

		$r = user_update($uid, $arr);
		$r !== FALSE ? message(0, '更新成功') : message(11, '更新失败');
	}

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$uid = param('uid', 0);

	$state = user_delete($uid);
	$state === FALSE AND message(11, '删除失败');

	message(0, '删除成功');

}
?>