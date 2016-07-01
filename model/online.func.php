<?php

// hook online_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

// 此 model 依赖的 2 个全局变量
$g_online_data = array();
$g_online_save = 0;

function online__create($arr) {
	// hook online__create_start.php
	$sqladd = array_to_sqladd($arr);
	// hook online__create_end.php
	return db_exec("INSERT INTO `bbs_online` SET $sqladd");
}

function online__update($sid, $arr) {
	// hook online__update_start.php
	$sqladd = array_to_sqladd($arr);
	// hook online__update_end.php
	return db_exec("UPDATE `bbs_online` SET $sqladd WHERE sid='$sid'");
}

function online__read($sid) {
	// hook online__read_start.php
	// hook online__read_end.php
	return db_find_one("SELECT * FROM `bbs_online` WHERE sid='$sid'");
}

function online__delete($sid) {
	// hook online__delete_start.php
	// hook online__delete_end.php
	return db_exec("DELETE FROM `bbs_online` WHERE sid='$sid'");
}

function online__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook online__find_start.php
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	// hook online__find_end.php
	return db_find("SELECT * FROM `bbs_online` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function online_create($arr) {
	// hook online_create_start.php
	$r = online__create($arr);
	// hook online_create_end.php
	return $r;
}

function online_update($sid, $arr) {
	// hook online_update_start.php
	$r = online__update($sid, $arr);
	// hook online_update_end.php
	return $r;
}

function online_replace($arr) {
	// hook online_replace_start.php
	$sqladd = array_to_sqladd($arr);
	// hook online_replace_end.php
	return db_exec("REPLACE INTO `bbs_online` SET $sqladd");
}

function online_read($sid) {
	// hook online_read_start.php
	$online = online__read($sid);
	online_format($online);
	// hook online_read_end.php
	return $online;
}

function online_delete($sid) {
	// hook online_delete_start.php
	$r = online__delete($sid);
	// hook online_delete_end.php
	return $r;
}

function online_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 1000) {
	// hook online_find_start.php
	$onlinelist = online__find($cond, $orderby, $page, $pagesize);
	if($onlinelist) foreach ($onlinelist as &$online) online_format($online);
	// hook online_find_end.php
	return $onlinelist;
}


// ------------> 其他方法

function online_format(&$online) {
	// hook online_format_start.php
	global $conf, $grouplist;
	if(empty($online)) return;
	$online['last_date_fmt'] = date('Y-n-j', $online['last_date']);
	$online['groupname'] = $grouplist[$online['gid']]['name'];
	$user = $online['uid'] ? user_read_cache($online['uid']) : user_guest();
	$online['username'] = $user['username'];
	// hook online_format_end.php
}

function online_count($cond = array()) {
	// hook online_count_start.php
	// hook online_count_end.php
	return db_count('bbs_online');
}

// 在线数据缓存，失效时间：1分钟。
function online_find_cache($fid = 0, $life = 60) {
	// hook online_find_cache_start.php
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
	// hook online_find_cache_end.php
	return !$onlinelist ? array() : $onlinelist;
}

// 强制更新 forumlist 缓存
function online_list_cache_delete($fid = 0) {
	// hook online_list_cache_delete_start.php
	global $conf, $forumlist;
	cache_delete('onlinelist');
	if($fid) {
		cache_delete('onlinelist_'.$fid);
	} else {
		if(is_array($forumlist)) { foreach($forumlist as $fid=>$forum) {
			cache_delete('onlinelist_'.$fid);
		}}
	}
	// hook online_list_cache_delete_end.php
}

/*
	1. 第一次请求没有 sid, 则生成一个 sid, 发送到客户端保存到 cookie
	2. 第二次访问，才会记录到 online 表
	3. online 表中记录了少量 session 数据
	4. 每隔5分钟清理一次过期的会话记录(超过1小时没活动的）

*/
function online_init() {
	// hook online_init_start.php
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
	// hook online_init_end.php
	return $sid;
}

function online_save($force = FALSE) {
	// hook online_save_start.php
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
	
	// hook online_save_end.php
	return $arr;
}

// 计划任务，每隔10分钟清理一次。
function online_gc() {
	// hook online_gc_start.php
	global $time, $conf;
	$expiry = $time - $conf['online_hold_time'];
	$n = db_exec("DELETE FROM `bbs_online` WHERE last_date<'$expiry'");
	
	// 重新统计在线数, MyISAM count 快，InnoDB 慢。
	runtime_set('onlines', max(1, online_count()));
	
	// 清理缓存
	online_list_cache_delete();
	// hook online_gc_end.php
}

// 类似 session_set

function online_set($k, $v) {
	// hook online_set_start.php
	global $g_online_data, $g_online_save;
	$g_online_data[$k] = $v;
	$g_online_save = 1;
	// hook online_set_end.php
}

function online_unset($k) {
	// hook online_unset_start.php
	global $g_online_data, $g_online_save;
	unset($g_online_data[$k]);
	$g_online_save = 1;
	// hook online_unset_end.php
}

function online_get($k) {
	// hook online_get_start.php
	global $g_online_data, $sid;
	if(empty($g_online_data)) {
		$online = online_read($sid);
		if(empty($online)) {
			$online = online_save(TRUE);
		}
		$g_online_data = $online['data'] ? xn_json_decode($online['data']) : array();
	}
	// hook online_get_end.php
	return array_value($g_online_data, $k, NULL);
}


// hook online_func_php_end.php

?>