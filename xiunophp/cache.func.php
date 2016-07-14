<?php

function cache_new($cacheconf) {
	global $db;
	// 缓存初始化，这里并不会产生连接！在真正使用的时候才连接。
	// 这里采用最笨拙的方式而不采用 new $classname 的方式，有利于 opcode 缓存。
	if($cacheconf && $cacheconf['enable']) {
		switch ($cacheconf['type']) {
			case 'redis': 	  $cache = new cache_redis($cacheconf['redis']); 	     break;
			case 'memcached': $cache = new cache_memcached($cacheconf['memcached']); break;
			case 'mysql': 	  $cache = new cache_mysql(!empty($cacheconf['mysql']) ? $cacheconf['mysql'] : $db); break;
			case 'xcache': 	  $cache = new cache_xcache(); 	break;
			case 'apc': 	  $cache = new cache_apc(); 	break;
			case 'saekv': 	  $cache = new cache_saekv(); 	break;
			default: xn_message(-1, '不支持的 cache type:'.$conf['cache']['type']);
		}
		if(!$cache || ($cache && $cache->errstr)) xn_message(-1, $cache->errstr);
		return $cache;
	}
	return NULL;
}

function cache_get($k, $fromkv = FALSE) {
	global $cache, $db;
	if(!$cache) return $db ? kv_get($k) : FALSE;
	$r = $cache->get($k);
	if($r === FALSE && $cache->errno != 0) {
		xn_message(-1, $cache->errstr);
	} elseif($r === NULL && $fromkv) {
		$r = kv_get($k);
		cache_set($k, $r);
		return $r;
	}
	return $r;
}

function cache_set($k, $v, $life = 0) {
	global $cache, $db;
	if(!$cache) return $db ? kv_set($k, $v, $life) : FALSE;
	$r = $cache->set($k, $v, $life);

	if($r === FALSE && $cache->errno != 0) {
		xn_message(-1, $cache->errstr);
	}
	return $r;
}

function cache_delete($k) {
	global $cache, $db;
	if(!$cache) return $db ? kv_delete($k) : FALSE;
	$r = $cache->delete($k);
	if($r === FALSE && $cache->errno != 0) {
		xn_message(-1, $cache->errstr);
	}
	return $r;
}

// 尽量避免调用此方法，不会清理保存在 kv 中的数据，逐条 cache_delete() 比较保险
function cache_truncate() {
	global $cache;
	if(!$cache) return FALSE;
	$r = $cache->truncate();
	if($r === FALSE && $cache->errno != 0) {
		xn_message(-1, $cache->errstr);
	}
	return $r;
}

?>