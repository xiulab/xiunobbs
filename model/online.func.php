<?php

// ------------> 最原生的 CURD，无关联其他数据。

function online__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_session` $cond$orderby LIMIT $offset,$pagesize");
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
	$user = $online['uid'] ? user_read_cache($online['uid']) : user_guest();
	$online['username'] = $user['username'];
	$online['groupname'] = $grouplist[$user['gid']]['name'];
}

function online_count($cond = array()) {
	return db_count('bbs_session');
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

?>