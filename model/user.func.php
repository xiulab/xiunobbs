<?php

// ------------> 最原生的 CURD，无关联其他数据。

function user__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_user` SET $sqladd");
}

function user__update($uid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_user` SET $sqladd WHERE uid='$uid'");
}

function user__read($uid) {
	return db_find_one("SELECT * FROM `bbs_user` WHERE uid='$uid'");
}

function user__delete($uid) {
	return db_exec("DELETE FROM `bbs_user` WHERE uid='$uid'");
}

function user__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_user` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function user_create($arr) {
	global $conf;
	$r = user__create($arr);
	
	// 全站统计
	runtime_set('users+', 1);
	runtime_set('todayusers+', 1);
	
	return $r;
}

function user_update($uid, $arr) {
	global $conf;
	$r = user__update($uid, $arr);
	$conf['cache']['type'] != 'mysql' AND cache_delete("user-$uid");
	return $r;
}

function user_read($uid) {
	$user = user__read($uid);
	user_format($user);
	return $user;
}

// 从缓存中读取，避免重复从数据库取数据，主要用来前端显示，可能有延迟。重要业务逻辑不要调用此函数，数据可能不准确，因为并没有清理缓存，针对 request 生命周期有效。
function user_read_cache($uid) {
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
	
	return $cache[$uid];
}

function user_delete($uid) {
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
	
	return $r;
}

function user_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$userlist = user__find($cond, $orderby, $page, $pagesize);
	if($userlist) foreach ($userlist as &$user) user_format($user);
	return $userlist;
}

// ------------> 其他方法

function user_read_by_email($email) {
	$user = db_find_one("SELECT * FROM `bbs_user` WHERE email='$email' LIMIT 1");
	user_format($user);
	return $user;
}

function user_read_by_mobile($mobile) {
	$user = db_find_one("SELECT * FROM `bbs_user` WHERE mobile='$mobile' LIMIT 1");
	user_format($user);
	return $user;
}

function user_read_by_platid_openid($platid, $openid) {
	return db_find_one("SELECT * FROM `bbs_user` WHERE platid='$platid' AND openid='$openid' LIMIT 1");
}

function user_read_by_username($username) {
	$user = db_find_one("SELECT * FROM `bbs_user` WHERE username='$username' LIMIT 1");
	user_format($user);
	return $user;
}

function user_count($cond = array()) {
	return db_count('bbs_user', $cond);
}

function user_maxid($cond = array()) {
	return db_maxid('bbs_user', 'uid');
}

function user_format(&$user) {
	global $conf, $grouplist;
	if(empty($user)) return;

	$user['create_ip_fmt']   = long2ip($user['create_ip']);
	$user['create_date_fmt'] = empty($user['create_date']) ? '0000-00-00' : date('Y-m-d', $user['create_date']);
	$user['login_ip_fmt']    = long2ip($user['login_ip']);
	$user['login_date_fmt'] = empty($user['login_date']) ? '0000-00-00' : date('Y-m-d', $user['login_date']);
	
	$group = $grouplist[$user['gid']];
	$user['groupname'] = $group['name'];
	
	$dir = substr(sprintf("%09d", $user['uid']), 0, 3);
	$user['avatar_url'] = $user['avatar'] ? $conf['upload_url']."avatar/$dir/$user[uid].png?".$user['avatar'] : 'static/avatar.png';
	$user['online_status'] = 1;
}

function user_token_set($uid = 0, $gid = 0, $password = '', $avatar = 0, $username = '', $cookipre = '', $expiry = 86400, $path = '/', $domain = '') {
	global $time, $conf, $ip;
	empty($cookipre) AND $cookipre = APP_NAME;
	empty($path) AND $path = $conf['cookie_path'];
	empty($domain) AND $domain = $conf['cookie_domain'];
	$s = encrypt("$uid\t$gid\t$time\t$ip\t$password\t$avatar\t$username", $conf['auth_key']);
	setcookie($cookipre.'_token', $s, $time + $expiry, $path, $domain);
	return $s;
}

// 这个函数会支持游客，其他函数需要自行判断！！！
function user_token_get($s = '', $cookipre = '') {
	global $conf;
	empty($cookipre) AND $cookipre = APP_NAME;
	$guest = user_guest();
	if (!$s) $s = param($cookipre.'_token');
	if (!$s) return $guest;
	$s2 = decrypt($s, $conf['auth_key']);
	if (!$s2) return $guest;
	$arr = explode("\t", $s2);
	if (count($arr) < 7) return $guest;
	$token = array();
	$token['uid'] = $arr[0];
	$token['gid'] = $arr[1];
	$token['time'] = $arr[2];
	$token['ip'] = $arr[3];
	$token['password'] = $arr[4];
	$token['avatar'] = $arr[5];
	
	$dir = substr(sprintf("%09d", $token['uid']), 0, 3);
	$token['avatar_url'] = $token['avatar'] ? $conf['upload_url']."avatar/$dir/$token[uid].png?".$token['avatar'] : 'static/avatar.png';
	
	$token['username'] = $arr[6];
	
	// if($token['password'] != $user['password']) return array(); // 修改密码，需要重新登录
	return $token;
}

function user_token_clean($path = '/', $domain = '', $cookiepre = '') {
	global $time, $conf;
	empty($path) AND $path = $conf['cookie_path'];
	empty($domain) AND $domain = $conf['cookie_domain'];
	!$cookiepre AND $cookiepre = APP_NAME;
	setcookie($cookiepre.'_token', '', $time, $path, $domain);
}

function user_guest() {
	global $conf;
	static $guest = NULL;
	if($guest) return $guest; // 返回引用，节省内存。
	$guest = array (
		'uid' => 0,
		'gid' => 0,
		'groupname' => '游客组',
		'username' => '游客',
		'avatar_url' => 'static/avatar.png',
		'create_ip_fmt' => '',
		'create_date_fmt' => '',
		'login_date_fmt' => '',
		'email' => '',
		
		'threads' => 0,
		'posts' => 0,
		'agrees' => 0,
	);
	return $guest; // 防止内存拷贝
}

// 前台登录验证
function user_login_check($user) {
	$user['uid'] == 0 AND message(10001, jump('请登录', 'user-login.htm'));
	$dbuser = user_read($user['uid']);
	$dbuser['password'] != $user['password'] AND message(10002, jump('密码已经修改，请重新登录', 'user-login.htm'));
	return $user;
}

// 根据喜欢数来调整用户组
function user_update_group($uid) {
	global $conf, $grouplist;
	$user = user_read_cache($uid);
	if($user['gid'] < 100) return FALSE;
	
	// 遍历 agrees 范围，调整用户组
	foreach($grouplist as $group) {
		if($group['gid'] < 100) continue;
		$n = $user['agrees'] + $user['threads'] + $user['posts'];
		if($n > $group['agreesfrom'] && $n < $group['agreesto']) {
			$user['gid'] = $group['gid'];
			user_update($uid, array('gid'=>$group['gid']));
			return TRUE;
		}
	}
	return FALSE;
}

// uids: 1,2,3,4 -> array()
function user_find_by_uids($uids) {
	$uids = trim($uids);
	if(empty($uids)) return array();
	$arr = explode(',', $uids);
	$r = array();
	foreach($arr as $uid) {
		$user = user_read_cache($uid);
		if(empty($user)) continue;
		$r[$user['uid']] = $user;
	}
	return $r;
}

// 获取用户安全信息
function user_safe_info($user) {
	unset($user['password']);
	unset($user['email']);
	unset($user['today_agrees']);
	unset($user['salt']);
	unset($user['password_sms']);
	unset($user['idnumber']);
	unset($user['realname']);
	unset($user['qq']);
	unset($user['mobile']);
	unset($user['create_ip']);
	unset($user['create_ip_fmt']);
	return $user;
}

// 检测是否在恶意注册用户，如果同一个IP连续注册数太多，认为在灌水
function user_check_flood($longip) {
	global $conf;
	if(!$conf['check_flood_on']) return FALSE;
	$userlist = user_find(array(), array('uid'=>-1), 1, 20);
	if(empty($userlist)) return FALSE;
	$n = 0;
	foreach($userlist as $user) {
		if($user['create_ip'] == $longip) {
			$n++;
			if($n > $conf['check_flood']['users']) return TRUE;
		}
	}
	return FALSE;
}
?>