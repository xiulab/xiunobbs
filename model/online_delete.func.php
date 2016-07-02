<?php

// hook online_func_php_start.php

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
	return db_count('bbs_session');
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

// hook online_func_php_end.php

?>