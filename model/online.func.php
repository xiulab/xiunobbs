<?php

// ------------> 最原生的 CURD，无关联其他数据。

// 此 model 依赖的 2 个全局变量
$g_online_data = array();
$g_online_save = 0;

function online__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_online` SET $sqladd");
}

function online__update($sid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_online` SET $sqladd WHERE sid='$sid'");
}

function online__read($sid) {
	return db_find_one("SELECT * FROM `bbs_online` WHERE sid='$sid'");
}

function online__delete($sid) {
	return db_exec("DELETE FROM `bbs_online` WHERE sid='$sid'");
}

function online__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_online` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function online_create($arr) {
	$r = online__create($arr);
	return $r;
}

function online_update($sid, $arr) {
	$r = online__update($sid, $arr);
	return $r;
}

function online_replace($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("REPLACE INTO `bbs_online` SET $sqladd");
}

function online_read($sid) {
	$online = online__read($sid);
	online_format($online);
	return $online;
}

function online_delete($sid) {
	$r = online__delete($sid);
	return $r;
}

function online_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	$onlinelist = online__find($cond, $orderby, $page, $pagesize);
	if($onlinelist) foreach ($onlinelist as &$online) online_format($online);
	return $onlinelist;
}


// ------------> 其他方法

function online_format(&$online) {
	global $conf, $grouplist;
	if(empty($online)) return;
	$online['last_date_fmt'] = date('Y-n-j', $online['last_date']);
	$online['groupname'] = $grouplist[$online['gid']]['name'];
	$user = $online['uid'] ? user_read_cache($online['uid']) : user_guest();
	$online['username'] = $user['username'];
}

function online_count($cond = array()) {
	return db_count('bbs_online');
}

// 在线数据缓存，失效时间：1分钟。
function online_find_cache($fid = 0, $life = 60) {
	global $runtime;
	if($runtime['onlines'] >= 300) return array();
	
	$key = 'onlinelist'.($fid ? "_$fid" : '');
	$onlinelist = cache_get($key);
	if($onlinelist === NULL) {
		$cond = $fid ? array('fid'=>$fid) : array();
		//$cond['uid'] = array('>'=>0);
		$onlinelist = online_find($cond, array(), 1, 300);
		cache_set($key, $onlinelist, $life);
	}
	return !$onlinelist ? array() : $onlinelist;
}

// 强制更新 forumlist 缓存
function online_list_cache_delete($fid = 0) {
	global $conf, $forumlist;
	cache_delete('onlinelist');
	if($fid) {
		cache_delete('onlinelist_'.$fid);
	} else {
		if(is_array($forumlist)) { foreach($forumlist as $fid=>$forum) {
			cache_delete('onlinelist_'.$fid);
		}}
	}
}

/*
	1. 第一次请求没有 sid, 则生成一个 sid, 发送到客户端保存到 cookie
	2. 第二次访问，才会记录到 online 表
	3. online 表中记录了少量 session 数据
	4. 每隔5分钟清理一次过期的会话记录(超过1小时没活动的）

*/
function online_init() {
	global $time, $conf, $uid, $g_online_save;
	
	$sid = param('bbs_sid');
	$lastdate = param('bbs_online_last_date');
	if(empty($sid)) {
		$sid = uniqid();
		setcookie('bbs_sid', $sid, $time + 86400, $conf['cookie_path'], $conf['cookie_domain']);
		$uid AND $g_online_save = 1;
	}
	if(empty($lastdate) || ($lastdate && $time - $lastdate > $conf['online_update_span'])) {
		$lastdate AND $g_online_save = 1; // 访问第二次才插入 online 表，防止蜘蛛导致人数暴涨。
		setcookie('bbs_online_last_date', (empty($lastdate) ? $time - $conf['online_update_span'] : $time), $time + 86400, $conf['cookie_path'], $conf['cookie_domain']);
	}
	return $sid;
}

function online_save($force = FALSE) {
	global $uid, $gid, $sid, $fid, $longip, $time, $conf, $runtime, $g_online_data, $g_online_save;
	
	$arr = array();
	if($force || $g_online_save) {
		$data = $g_online_data ? xn_json_encode($g_online_data) : '';
		strlen($data) > 255 AND $data = '';
		$arr = array (
			'sid'=>$sid,
			'uid'=>$uid,
			'gid'=>$gid,
			'fid'=>$fid,
			'url'=>$_SERVER['REQUEST_URI'],
			'ip'=>$longip,
			'useragent'=>$_SERVER['HTTP_USER_AGENT'],
			'data'=>$data,
			'last_date'=>$time,
		);
		online_replace($arr);
		
		// 如果设置为5分钟，则实时更新。比较耗费资源
		if($conf['online_update_span'] < 300) {
			online_list_cache_delete($fid);
		}
	}
	
	return $arr;
}

// 计划任务，每隔10分钟清理一次。
function online_gc() {
	global $time, $conf;
	$expiry = $time - $conf['online_hold_time'];
	$n = db_exec("DELETE FROM `bbs_online` WHERE last_date<'$expiry'");
	
	// 重新统计在线数, MyISAM count 快，InnoDB 慢。
	runtime_set('onlines', max(1, online_count()));
	
	// 清理缓存
	online_list_cache_delete();
}

// 类似 session_set

function online_set($k, $v) {
	global $g_online_data, $g_online_save;
	$g_online_data[$k] = $v;
	$g_online_save = 1;
}

function online_unset($k) {
	global $g_online_data, $g_online_save;
	unset($g_online_data[$k]);
	$g_online_save = 1;
}

function online_get($k) {
	global $g_online_data, $sid;
	if(empty($g_online_data)) {
		$online = online_read($sid);
		if(empty($online)) {
			$online = online_save(TRUE);
		}
		$g_online_data = $online['data'] ? xn_json_decode($online['data']) : array();
	}
	return array_value($g_online_data, $k, NULL);
}

?>