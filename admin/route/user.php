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
	$pagination = pagination(url("user-list-$srchtype-".urlencode($keyword).'-{page}'), $n, $page, $pagesize);
	$pager = pager(url("user-list-$srchtype-".urlencode($keyword).'-{page}'), $n, $page, $pagesize);

	foreach ($userlist as &$_user) {
		$_user['group'] = array_value($grouplist, $_user['gid'], '');
	}

	include "./view/htm/user_list.htm";

} elseif($action == 'create') {

	if($method == 'GET') {

		$header['title'] = '用户创建';

		$input['email'] = form_text('email', '');
		$input['username'] = form_text('username','');
		$input['password'] = form_password('password', '');
		$grouparr = arrlist_key_values($grouplist, 'gid', 'name');
		$input['_gid'] = form_select('_gid', $grouparr, 0);
		
		include "./view/htm/user_create.htm";

	} elseif ($method == 'POST') {

		$email = param('email');
		$username = param('username');
		$password = param('password');
		$_gid = param('_gid');
		
		empty($email) AND message('email', '请输入邮箱');
		$email AND !is_email($email, $err) AND message('email', $err);
		$username AND !is_username($username, $err) AND message('username', $err);

		$user = user_read_by_email($email);
		$user AND message('email', '用户 EMAIL 已经存在');

		$user = user_read_by_username($username);
		$user AND message('username', '用户已经存在');

		$salt = xn_rand(16);
		$r = user_create(array(
			'username'=>$username,
			'password'=>md5(md5($password).$salt),
			'salt'=>$salt,
			'gid'=>$_gid,
			'email'=>$email,
			'create_ip'=>ip2long(ip()),
			'create_date'=>$time
		));
		$r === FALSE AND message(-1, '创建失败');
		
		message(0, '创建成功');

	}

} elseif($action == 'update') {

	$_uid = param(2, 0);
	
	if($method == 'GET') {

		$header['title'] = '用户更新';
		
		$user = user_read($_uid);
		
		$input['email'] = form_text('email', $user['email']);
		$input['username'] = form_text('username', $user['username']);
		$input['password'] = form_password('password', '');
		$grouparr = arrlist_key_values($grouplist, 'gid', 'name');
		$input['_gid'] = form_select('_gid', $grouparr, $user['gid']);

		include "./view/htm/user_update.htm";

	} elseif($method == 'POST') {

		$email = param('email');
		$username = param('username');
		$password = param('password');
		$_gid = param('_gid');
		
		$old = user_read($_uid);
		empty($old) AND message('username', '指定的 UID 不存在');
		
		$email AND !is_email($email, $err) AND message(2, $err);
		if($email AND $old['email'] != $email) {
			$user = user_read_by_email($email);
			$user AND $user['uid'] != $_uid AND message('email', '用户 EMAIL 已经存在');
		}
		if($username AND $old['username'] != $username) {
			$user = user_read_by_username($username);
			$user AND $user['uid'] != $_uid AND message('username', '用户已经存在');
		}
		
		$arr = array();
		$arr['email'] = $email;
		$arr['username'] = $username;
		$arr['gid'] = $_gid;
		
		if($password) {
			$salt = xn_rand(16);
			$arr['password'] = md5(md5($password).$salt);
			$arr['salt'] = $salt;
		}
		
		// 仅仅更新发生变化的部分
		$update = array_diff_value($arr, $old);
		empty($update) AND message(-1, '没有数据变动');

		$r = user_update($_uid, $update);
		$r === FALSE AND message(-1, '更新失败');
		
		message(0, '更新成功');
	}

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$_uid = param('uid', 0);
	
	$_user = user_read($_uid);
	empty($_user) AND message(-1, '用户不存在');
	($_user['gid'] == 1) AND message(-1, '不能直接删除管理员，请先编辑为普通用户组。');

	$r = user_delete($_uid);
	$r === FALSE AND message(-1, '删除失败');
	
	
	message(0, '删除成功');
	
} else {
	
	http_404();
	
}
?>