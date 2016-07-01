<?php

// hook runtime_func_php_start.php

$g_runtime_save = 0;
function runtime_init() {
	// hook runtime_init_start.php
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
	// hook runtime_init_end.php
	return $runtime;
}

function runtime_get($k) {
	// hook runtime_get_start.php
	global $runtime;
	// hook runtime_get_end.php
	return array_value($runtime, $k, FALSE);
}

function runtime_set($k, $v, $save = FALSE) {
	// hook runtime_set_start.php
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
	// hook runtime_set_end.php
}

// 追加 runtime，用来初始化
function runtime_append($k, $v) {
	// hook runtime_append_start.php
	$arr = kv_get('runtime_append');
	empty($arr) AND $arr = array();
	$arr[$k] = $v;
	kv_set('runtime_append', $arr);
	// hook runtime_append_end.php
}

function runtime_save() {
	// hook runtime_save_start.php
	global $runtime, $g_runtime_save;
	
	if(!$g_runtime_save) return;
	$r = cache_set('runtime', $runtime);
	
	// hook runtime_save_end.php
}

function runtime_truncate() {
	// hook runtime_truncate_start.php
	global $conf;
	cache_delete('runtime');
	// hook runtime_truncate_end.php
}


// hook runtime_func_php_end.php

?>