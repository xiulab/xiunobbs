<?php

// 如果环境支持，可以直接改为 redis get() set() 持久存储相关 API，提高速度。

function kv_get($k) {
	$arr = db_find_one('kv', array('k'=>$k));
	return $arr ? xn_json_decode($arr['v']) : NULL;
}

function kv_set($k, $v, $life = 0) {
	$arr = array(
		'k'=>$k,
		'v'=>xn_json_encode($v),
	);
	$r = db_replace('kv', $arr);
	return $r;
}

function kv_delete($k) {
	$r = db_delete('kv', array('k'=>$k));
	return $r;
}


// --------------------> kv + cache

function kv_cache_get($k) {
	$r = cache_get($k);
	if($r === NULL) {
		$r = kv_get($k);
	}
	return $r;
}

function kv_cache_set($k, $v, $life = 0) {
	cache_set($k, $v, $life);
	$r = kv_set($k, $v);
	return $r;
}

function kv_cache_delete($k) {
	cache_delete($k);
	$r = kv_delete($k);
	return $r;
}



// ------------> kv + cache + setting

function setting_get($k) {
	global $setting;
	if($setting === FALSE) {
		$setting = kv_cache_get('setting', $setting);
	}
	empty($setting) AND $setting = array();
	return array_value($setting, $k, NULL);
}

// 全站的设置，全局变量 $setting = array();
function setting_set($k, $v) {
	global $setting;
	$setting[$k] = $v;
	return kv_cache_set('setting', $setting);
}

function setting_delete($k) {
	global $setting;
	if(empty($setting)) return TRUE;
	unset($setting[$k]);
	kv_cache_set('setting', $setting);
	return TRUE;
}

?>