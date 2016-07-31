<?php

// hook user_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function user__create($arr) {
	// hook user__create_start.php
	$r = db_insert('user', $arr);
	// hook user__create_end.php
	return $r;
}

function user__update($uid, $update) {
	// hook user__update_start.php
	$r = db_update('user', array('uid'=>$uid), $update);
	// hook user__update_end.php
	return $r;
}

function user__read($uid) {
	// hook user__read_start.php
	$user = db_find_one('user', array('uid'=>$uid));
	// hook user__read_end.php
	return $user;
}

function user__delete($uid) {
	// hook user__delete_start.php
	$r = db_delete('user', array('uid'=>$uid));
	// hook user__delete_end.php
	return $r;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function user_create($arr) {
	// hook user_create_start.php
	global $conf;
	$r = user__create($arr);
	
	// 全站统计
	runtime_set('users+', 1);
	runtime_set('todayusers+', 1);
	
	// hook user_create_end.php
	return $r;
}

function user_update($uid, $arr) {
	// hook user_update_start.php
	global $conf;
	$r = user__update($uid, $arr);
	$conf['cache']['type'] != 'mysql' AND cache_delete("user-$uid");
	// hook user_update_end.php
	return $r;
}

function user_read($uid) {
	// hook user_read_start.php
	if(empty($uid)) return array();
	$uid = intval($uid);
	$user = user__read($uid);
	user_format($user);
	// hook user_read_end.php
	return $user;
}

// 从缓存中读取，避免重复从数据库取数据，主要用来前端显示，可能有延迟。重要业务逻辑不要调用此函数，数据可能不准确，因为并没有清理缓存，针对 request 生命周期有效。
function user_read_cache($uid) {
	// hook user_read_cache_start.php
	global $conf;
	static $cache = array(); // 用静态变量只能在当前 request 生命周期缓存，要跨进程，可以再加一层缓存： memcached/xcache/apc/
	if(isset($cache[$uid])) return $cache[$uid];
	
	if($conf['cache']['type'] != 'mysql') {
		$r = cache_get("user-$uid");
		if($r === NULL) {
			$cache[$uid] = user_read($uid);
			cache_set("user-$uid", $cache[$uid]);
		} else {
			$cache[$uid] = $r;
		}
	} else {
		$cache[$uid] = user_read($uid);
	}
	
	// hook user_read_cache_end.php
	return $cache[$uid];
}

function user_delete($uid) {
	// hook user_delete_start.php
	global $conf;
	// 清理用户资源
	$threadlist = mythread_find_by_uid($uid, 1, 1000);
	foreach($threadlist as $thread) {
		thread_delete($thread['tid']);
	}
	
	$r = user__delete($uid);
	
	$conf['cache']['type'] != 'mysql' AND cache_delete("user-$uid");
	
	// 全站统计
	runtime_set('users-', 1);
	
	// hook user_delete_end.php
	return $r;
}

function user_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook user_find_start.php
	$userlist = db_find('user', $cond, $orderby, $page, $pagesize);
	if($userlist) foreach ($userlist as &$user) user_format($user);
	// hook user_find_end.php
	return $userlist;
}

// ------------> 其他方法

function user_read_by_email($email) {
	// hook user_read_by_email_start.php
	$user = db_find_one('user', array('email'=>$email));
	user_format($user);
	// hook user_read_by_email_end.php
	return $user;
}

function user_read_by_username($username) {
	// hook user_read_by_username_start.php
	$user = db_find_one('user', array('username'=>$username));
	user_format($user);
	// hook user_read_by_username_end.php
	return $user;
}

function user_count($cond = array()) {
	// hook user_count_start.php
	$n = db_count('user', $cond);
	// hook user_count_end.php
	return $n;
}

function user_maxid($cond = array()) {
	// hook user_maxid_start.php
	$n = db_maxid('user', 'uid');
	// hook user_maxid_end.php
	return $n;
}

function user_format(&$user) {
	// hook user_format_start.php
	global $conf, $grouplist;
	if(empty($user)) return;

	$user['create_ip_fmt']   = long2ip($user['create_ip']);
	$user['create_date_fmt'] = empty($user['create_date']) ? '0000-00-00' : date('Y-m-d', $user['create_date']);
	$user['login_ip_fmt']    = long2ip($user['login_ip']);
	$user['login_date_fmt'] = empty($user['login_date']) ? '0000-00-00' : date('Y-m-d', $user['login_date']);
	
	$user['groupname'] = group_name($user['gid']);
	
	$dir = substr(sprintf("%09d", $user['uid']), 0, 3);
	$user['avatar_url'] = $user['avatar'] ? $conf['upload_url']."avatar/$dir/$user[uid].png?".$user['avatar'] : 'view/img/avatar.png';
	$user['online_status'] = 1;
	// hook user_format_end.php
}

function user_guest() {
	// hook user_guest_start.php
	global $conf;
	static $guest = NULL;
	if($guest) return $guest; // 返回引用，节省内存。
	$guest = array (
		'uid' => 0,
		'gid' => 0,
		'groupname' => '游客组',
		'username' => '游客',
		'avatar_url' => 'view/img/avatar.png',
		'create_ip_fmt' => '',
		'create_date_fmt' => '',
		'login_date_fmt' => '',
		'email' => '',
		
		'threads' => 0,
		'posts' => 0,
	);
	// hook user_guest_end.php
	return $guest; // 防止内存拷贝
}

// 前台登录验证
function user_login_check($user) {
	// hook user_login_check_start.php
	$user['uid'] == 0 AND message(-1, jump('请登录', 'user-login.htm'));
	$dbuser = user_read($user['uid']);
	$dbuser['password'] != $user['password'] AND message(-1, jump('密码已经修改，请重新登录', 'user-login.htm'));
	// hook user_login_check_end.php
	return $user;
}

// 根据喜欢数来调整用户组
function user_update_group($uid) {
	// hook user_update_group_start.php
	global $conf, $grouplist;
	$user = user_read_cache($uid);
	if($user['gid'] < 100) return FALSE;
	
	// 遍历 agrees 范围，调整用户组
	// todo:
	/*
	foreach($grouplist as $group) {
		if($group['gid'] < 100) continue;
		$n = $user['threads'] + $user['posts'];
		if($n > $group['agreesfrom'] && $n < $group['agreesto']) {
			$user['gid'] = $group['gid'];
			user_update($uid, array('gid'=>$group['gid']));
			return TRUE;
		}
	}
	*/
	// hook user_update_group_end.php
	return FALSE;
}

// uids: 1,2,3,4 -> array()
function user_find_by_uids($uids) {
	// hook user_find_by_uids_start.php
	$uids = trim($uids);
	if(empty($uids)) return array();
	$arr = explode(',', $uids);
	$r = array();
	foreach($arr as $uid) {
		$user = user_read_cache($uid);
		if(empty($user)) continue;
		$r[$user['uid']] = $user;
	}
	// hook user_find_by_uids_end.php
	return $r;
}

// 获取用户安全信息
function user_safe_info($user) {
	// hook user_safe_info_start.php
	unset($user['password']);
	unset($user['email']);
	unset($user['salt']);
	unset($user['password_sms']);
	unset($user['idnumber']);
	unset($user['realname']);
	unset($user['qq']);
	unset($user['mobile']);
	unset($user['create_ip']);
	unset($user['create_ip_fmt']);
	unset($user['create_date']);
	unset($user['create_date_fmt']);
	unset($user['login_ip_fmt']);
	unset($user['login_date_fmt']);
	unset($user['logins']);
	// hook user_safe_info_end.php
	return $user;
}


// 用户
function user_token_get() {
	global $time;
	$_uid = user_token_get_do();
	if(!$_uid) {
		setcookie('bbs_token', '', $time - 86400, '');
	}
	return $_uid;
}

// 用户
function user_token_get_do() {
	global $time, $ip, $useragent, $conf;
	$token = param('bbs_token');
	if(empty($token)) return FALSE;
	$tokenkey = md5($useragent.$conf['auth_key']);
	$s = xn_decrypt($token, $tokenkey);
	if(empty($s)) return FALSE;
	$arr = explode("\t", $s);
	if(count($arr) != 3) return FALSE;
	list($_ip, $_time, $_uid) = $arr;
	if($ip != $_ip) return FALSE;
	if($time - $_time > 86400) return FALSE;
	return $_uid;	
}

function user_token_gen($uid) {
	global $ip, $time, $useragent, $conf;
	$tokenkey = md5($useragent.$conf['auth_key']);
	$token = xn_encrypt("$ip	$time	$uid", $tokenkey);
	return $token;
}
// hook user_func_php_end.php

?>