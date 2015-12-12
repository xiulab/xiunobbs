<?php

$g_runtime_save = 0;
function runtime_init() {
	global $conf;
	$runtime = cache_get('runtime'); // 实时运行的数据，初始化！
	if($runtime === NULL || !isset($runtime['users']) || !isset($runtime['onlines'])) {
		$runtime = array();
		$runtime['users'] = user_count();
		$runtime['posts'] = post_count();
		$runtime['threads'] = thread_count();
		$runtime['posts'] -= $runtime['threads']; // 减去首帖
		$runtime['todayusers'] = 0;
		$runtime['todayposts'] = 0;
		$runtime['todaythreads'] = 0;
		$runtime['onlines'] = max(1, online_count());
		
		// runtime_append
		$arr = kv_get('runtime_append');
		is_array($arr) AND $runtime += $arr;
		
		cache_set('runtime', $runtime, TRUE);
		
	}
	return $runtime;
}

function runtime_get($k) {
	global $runtime;
	return array_value($runtime, $k, FALSE);
}

function runtime_set($k, $v, $save = FALSE) {
	global $conf, $runtime, $g_runtime_save;
	$op = substr($k, -1);
	if($op == '+' || $op == '-') {
		$k = substr($k, 0, -1);
		!isset($runtime[$k]) AND $runtime[$k] = '';
		$v = $op == '+' ? ($runtime[$k] + $v) : ($runtime[$k] - $v);
	}
	
	$default_keys = array('users', 'posts', 'threads', 'todayusers', 'todayposts', 'todaythreads', 'onlines');
	if(!in_array($k, $default_keys)) {
		runtime_append($k, $v);
	}
	$runtime[$k] = $v;
	if($save) {
		return cache_set('runtime', $runtime);
	} else {
		$g_runtime_save = 1;
		return TRUE;
	}
}

// 追加 runtime，用来初始化
function runtime_append($k, $v) {
	$arr = kv_get('runtime_append');
	empty($arr) AND $arr = array();
	$arr[$k] = $v;
	kv_set('runtime_append', $arr);
}

function runtime_save() {
	global $runtime, $g_runtime_save;
	
	if(!$g_runtime_save) return;
	$r = cache_set('runtime', $runtime);
	
}

function runtime_truncate() {
	global $conf;
	cache_delete('runtime');
}

?>