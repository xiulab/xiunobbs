<?php

/*
* Copyright (C) 2015 xiuno.com
*/

/*
	XiunoPHP 3.0，采用简单结构，有利于 HHVM 编译 / opcode 缓存，完美支持 PHP7
	1. 不要 include 变量
	2. 不要采用 eval(), 正则表达式 e 修饰符
	3. 不要采用 autoload
	4. 不要采用 $$var 多重变量
	5. 不要使用 PHP 高级特性 __call __set __get 等魔术方法
	6. 尽量采用函数封装功能，通过前缀区分模块。
*/

!defined('DEBUG') AND define('DEBUG', 1); // 1: 开发模式， 2: 线上调试：日志记录，0: 关闭
!defined('APP_NAME') AND define('APP_NAME', 'www');

error_reporting(DEBUG ? E_ALL : 0);
version_compare(PHP_VERSION, '5.3.0', '<') AND set_magic_quotes_runtime(0);
$get_magic_quotes_gpc = get_magic_quotes_gpc();

// 头部，判断是否运行在命令行下
define('IN_CMD', !empty($_SERVER['SHELL']) || empty($_SERVER['REMOTE_ADDR']));
if(IN_CMD) {
	!isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] = '';
	!isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'] = '';
	!isset($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] = 'GET';
} else {
	header("Content-type: text/html; charset=utf-8");
	//header("Cache-Control: max-age=0;"); // 手机返回的时候回导致刷新
	//header("Cache-Control: no-store;");
	header("X-Powered-By: XiunoPHP 3.0");
}


// ----------------------------------------------------------> 全局变量申明开始，一共大概十几个

$starttime = microtime(1);
$time = time();

empty($conf) AND $conf = array('db'=>NULL, 'cache'=>NULL, 'tmp_path'=>'./', 'log_path'=>'./', 'timezone'=>'Asia/Shanghai');
empty($uid) AND $uid = 0;

$upload_tmp_dir = ini_get('upload_tmp_dir');
!$upload_tmp_dir AND $upload_tmp_dir = './';
define('APP_TMP_PATH', empty($conf['tmp_path']) ? $upload_tmp_dir : $conf['tmp_path']);
define('APP_LOG_PATH', empty($conf['log_path']) ? './' : $conf['log_path']);
define('APP_CACHE_PRE', empty($conf['cache']['pre']) ? 'pre_' : $conf['cache']['pre']);
define('URL_REWRITE_PATH_FORMAT_ON', !empty($conf['url_rewrite_on']) && $conf['url_rewrite_on'] == 3);	// 是否开启 / 路径

$ip = ip();
// $ip = '220.166.164.200';
$longip = ip2long($ip);
$longip < 0 AND $longip = sprintf("%u", $longip); // fix 32 位 OS 下溢出的问题

// 语言包变量
$lang = array();

// $_SERVER['REQUEST_METHOD'] === 'PUT' ? @parse_str(file_get_contents('php://input', false , null, -1 , $_SERVER['CONTENT_LENGTH']), $_PUT) : $_PUT = array(); // 不需要支持 PUT
$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(trim($_SERVER['HTTP_X_REQUESTED_WITH'])) == 'xmlhttprequest';
$method = $_SERVER['REQUEST_METHOD'];

// 全局的错误，进程下很方便。
$errno = 0;
$errstr = '';

// error_handle
// register_shutdown_function('shutdown_handle');
set_error_handler('error_handle', -1);
empty($conf['timezone']) AND $conf['timezone'] = 'Asia/Shanghai';
date_default_timezone_set($conf['timezone']);

// 超级全局变量
$_GET += init_query_string();
$_REQUEST = array_merge($_COOKIE, $_POST, $_GET);


$db = NULL;
$cache = NULL;

// ----------------------------------------------------------> 全局变量申明结束


// ----------------------------------------------------------> 全局函数

if(!function_exists('message')) {
	// 此处不利于 HHVM，应该强制要求 APP 定义 message 函数，为了正确性，暂时如此。
	function message($code, $message) {
		xn_message($code, $message);
	}
}

// 此处不利于 HHVM，应该强制要求 APP 定义 message 函数，为了正确性，暂时如此。
function xn_message($code, $message) {
	global $ajax;
	echo $ajax ? xn_json_encode(array('code'=>$code, 'message'=>$message)) : $message;
	exit;
}

function log_post_data() {
	global $method;
	if($method != 'POST') return;
	$post = $_POST;
	isset($post['password']) AND $post['password'] = '******'; 		// 干掉密码信息
	isset($post['password_new']) AND $post['password_new'] = '******'; 	// 干掉密码信息
	isset($post['password_old']) AND $post['password_old'] = '******'; 	// 干掉密码信息

	xn_log(xn_json_encode($post), APP_NAME.'_post_data');
}

// 中断流程很危险！可能会导致数据问题，线上模式不允许中断流程！
function error_handle($errno, $errstr, $errfile, $errline) {
	global $time, $ajax;
	$br = ($ajax ? "\n" : "<br>\n");
	IN_CMD AND $errstr = str_replace('<br>', "\n", $errstr);
	$s = $br."Error[$errno]: $errstr, File: $errfile, Line: $errline";
	xn_log($s, 'php_error'); // 所有PHP错误报告都记录日志
	if(DEBUG) {
		// 如果放在 register_shutdown_function 里面，文件句柄会被关闭，然后这里就写入不了文件了！
		// if(strpos($s, 'error_log(') !== FALSE) return TRUE;
		echo $s.$br;
		$arr = debug_backtrace();
		array_shift($arr);
		foreach($arr as $v) {
			$args = '';
			if(!empty($v['args'])) foreach ($v['args'] as $v2) $args .= ($args ? ' , ' : '').(is_array($v2) ? 'array('.count($v2).')' : $v2);
			echo $br."File: $v[file], Line: $v[line], $v[function]($args) ";
		}
		echo $br;
		return TRUE;
	} else {
		return FALSE;
	}
	// true 表示不执行 PHP 内部错误处理程序, false 表示执行PHP默认处理
	//return DEBUG ? FALSE : TRUE;
}

// 使用全局变量记录错误信息
function xn_error($no, $str, $return = FALSE) {
	global $errno, $errstr;
	$errno = $no;
	$errstr = $str;
	return $return;
}

function array_value($arr, $key, $default = 0) {
	return isset($arr[$key]) ? $arr[$key] : $default;
}

function array_isset_push(&$arr, $key, $value) {
	!isset($arr[$key]) AND $arr[$key] = array();
	$arr[$key][] = $value;
}

/*
	param(1);
	param(1, '');
	param(1, 0);
	param(1, array());
	param(1, array(''));
	param(1, array(0));
*/
function param($key, $defval = '', $safe = TRUE) {
	if(!isset($_REQUEST[$key]) || ($key === 0 && empty($_REQUEST[$key]))) {
		if(is_array($defval)) {
			return array();
		} else {
			return $defval;
		}
	}
	$val = $_REQUEST[$key];
	$val = param_force($val, $defval, $safe);
	return $val;
}

/*
	仅支持一维数组的类型强制转换。
	param_force($val);
	param_force($val, '');
	param_force($val, 0);
	param_force($arr, array());
	param_force($arr, array(''));
	param_force($arr, array(0));
*/
function param_force($val, $defval, $safe = TRUE) {
	global $get_magic_quotes_gpc;
	if(is_array($defval)) {
		$defval = empty($defval) ? '' : $defval[0]; // 数组的第一个元素，如果没有则为空字符串
		if(is_array($val)) {
			foreach($val as &$v) {
				if(is_array($v)) {
					$v = $defval;
				} else {
					if(is_string($defval)) {
						//$v = trim($v);
						$safe AND !$get_magic_quotes_gpc && $v = addslashes($v);
						!$safe AND $get_magic_quotes_gpc && $v = stripslashes($v);
						$safe AND $v = htmlspecialchars($v);
					} else {
						$v = intval($v);
					}
				}
			}
		} else {
			return array();
		}
	} else {
		if(is_array($val)) {
			$val = $defval;
		} else {
			if(is_string($defval)) {
				//$val = trim($val);
				$safe AND !$get_magic_quotes_gpc && $val = addslashes($val);
				!$safe AND $get_magic_quotes_gpc && $val = stripslashes($val);
				$safe AND $val = htmlspecialchars($val);
			} else {
				$val = intval($val);
			}
		}
	}
	return $val;
}

/*
	lang('mobile_length_error');
	lang('mobile_length_error', array('mobile'=>$mobile));
*/
function lang($key, $arr = array()) {
	global $lang;
	if(!isset($lang[$key])) return 'lang['.$key.']';
	$s = $lang[$key];
	if(!empty($arr)) {
		foreach($arr as $k=>$v) {
			$s = str_replace('$'.$k, $v, $s);
		}
	}
	return $s;
}

function jump($message, $url = '', $delay = 3) {
	global $ajax;
	if($ajax) return $message;
	if(!$url) return $message;
	$htmladd = '<script>setTimeout(function() {window.location=\''.$url.'\'}, '.($delay * 1000).');</script>';
	return '<a href="'.$url.'">'.$message.'</a>'.$htmladd;
}

function array_addslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_addslashes($v);
		}
	} else {
		$var = addslashes($var);
	}
	return $var;
}

function array_stripslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_stripslashes($v);
		}
	} else {
		$var = stripslashes($var);
	}
	return $var;
}

function array_htmlspecialchars(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_htmlspecialchars($v);
		}
	} else {
		$var = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $var);
	}
	return $var;
}

function array_trim(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_trim($v);
		}
	} else {
		$var = trim($var);
	}
	return $var;
}

/*
	$data = array();
	$data[] = array('volume' => 67, 'edition' => 2);
	$data[] = array('volume' => 86, 'edition' => 1);
	$data[] = array('volume' => 85, 'edition' => 6);
	$data[] = array('volume' => 98, 'edition' => 2);
	$data[] = array('volume' => 86, 'edition' => 6);
	$data[] = array('volume' => 67, 'edition' => 7);
	arrlist_multisort($data, 'edition', TRUE);
*/
// 对多维数组排序
function arrlist_multisort(&$arrlist, $col, $asc = TRUE) {
	$colarr = array();
	foreach($arrlist as $k=>$arr) {
		$colarr[$k] = $arr[$col];
	}
	$asc = $asc ? SORT_ASC : SORT_DESC;
	array_multisort($colarr, $asc, $arrlist);
	return $arrlist;
}

// 对数组进行查找，排序，筛选，只支持一种条件排序
function arrlist_cond_orderby($arrlist, $cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$resultarr = array();
	// 根据条件，筛选结果
	if($cond) {
		foreach($arrlist as $key=>$val) {
			$ok = TRUE;
			foreach($cond as $k=>$v) {
				if(!isset($val[$k]) || $val[$k] != $v) {
					$ok = FALSE;
					break;
				}
			}
			if($ok) $resultarr[$key] = $val;
		}
	} else {
		$resultarr = $arrlist;
	}

	if($orderby) {
		list($k, $v) = each($orderby);
		arrlist_multisort($resultarr, $k, $v == 1);
	}

	$start = ($page - 1) * $pagesize;

	$resultarr = array_assoc_slice($resultarr, $start, $pagesize);
	return $resultarr;
}

function array_assoc_slice($arrlist, $start, $length = 0) {
	if(isset($arrlist[0])) return array_slice($arrlist, $start, $length);
	$keys = array_keys($arrlist);
	$keys2 = array_slice($keys, $start, $length);
	$retlist = array();
	foreach ($keys2 as $key) {
		$retlist[$key] = $arrlist[$key];
	}

	return $retlist;
}


// 从一个二维数组中取出一个 key=>value 格式的一维数组
function arrlist_key_values($arrlist, $key, $value) {
	$return = array();
	if($key) {
		foreach((array)$arrlist as $arr) {
			$return[$arr[$key]] = $arr[$value];
		}
	} else {
		foreach((array)$arrlist as $arr) {
			$return[] = $arr[$value];
		}
	}
	return $return;
}

/* php 5.5:
function array_column($arrlist, $key) {
	return arrlist_values($arrlist, $key);
}
*/

// 从一个二维数组中取出一个 values() 格式的一维数组，某一列key
function arrlist_values($arrlist, $key) {
	if(!$arrlist) return array();
	$return = array();
	foreach($arrlist as &$arr) {
		$return[] = $arr[$key];
	}
	return $return;
}

// 将 key 更换为某一列的值，在对多维数组排序后，数字key会丢失，需要此函数
function arrlist_change_key(&$arrlist, $key, $pre = '') {
	$return = array();
	if(empty($arrlist)) return $return;
	foreach($arrlist as &$arr) {
		$return[$pre.''.$arr[$key]] = $arr;
	}
	$arrlist = $return;
}

// 根据某一列的值进行 chunk
function arrlist_chunk($arrlist, $key) {
	$r = array();
	if(empty($arrlist)) return $r;
	foreach($arrlist as &$arr) {
		!isset($r[$arr[$key]]) AND $r[$arr[$key]] = array();
		$r[$arr[$key]][] = $arr;
	}
	return $r;
}

/*
	array(
		'name'=>'abc',
		'stocks+'=>1,
		'date'=>12345678900,
	)

*/
//user_update(123, array('stocks+'=>1));
function array_to_sqladd($arr) {
	$s = '';
	foreach($arr as $k=>$v) {
		$v = addslashes($v);
		$op = substr($k, -1);
		if($op == '+' || $op == '-') {
			$k = substr($k, 0, -1);
			$s .= "`$k`=`$k`$op'$v',";
		} else {
			$s .= "`$k`='$v',";
		}
	}
	return substr($s, 0, -1);
}

// $old 表示是否早期的数据，如果相等则不变更
function array_to_sql_update($arr, $old = array()) {
	$s = '';
	foreach($arr as $k=>$v) {
		$v = addslashes($v);
		$op = substr($k, -1);
		if($op == '+' || $op == '-') {
			$k = substr($k, 0, -1);
			$s .= "`$k`=`$k`$op'$v',";
		} else {
			if(isset($old[$k]) && $old[$k] != $v) {
				$s .= "`$k`='$v',";
			}
			$s .= "`$k`='$v',";
		}
	}
	return substr($s, 0, -1);
}

/*
	array('id'=>123, 'groupid'=>123)
	array('id'=>array('>' => 100, '<' => 200))
	array('username'=>array('LIKE' => 'jack'))

*/
function cond_to_sqladd($cond) {
	$s = '';
	if(!empty($cond)) {
		$s = ' WHERE ';
		foreach($cond as $k=>$v) {
			if(!is_array($v)) {
				$v = addslashes($v);
				$s .= "$k = '$v' AND ";
			} else {
				foreach($v as $k1=>$v1) {
					$v1 = addslashes($v1);
					$k1 == 'LIKE' AND $v1 = "%$v1%";
					$s .= "$k $k1 '$v1' AND ";
				}
			}
		}
		$s = substr($s, 0, -4);
	}
	return $s;
}

function orderby_to_sqladd($orderby) {
	$s = '';
	if(!empty($orderby)) {
		$s .= ' ORDER BY ';
		$comma = '';
		foreach($orderby as $k=>$v) {
			$s .= $comma."$k ".($v == 1 ? ' ASC ' : ' DESC ');
			$comma = ',';
		}
	}
	return $s;
}

function db_new($dbconf) {
	// 数据库初始化，这里并不会产生连接！
	if($dbconf) {
		switch ($dbconf['type']) {
			case 'mysql':      $db = new db_mysql($dbconf['mysql']); 		break;
			case 'pdo_mysql':  $db = new db_pdo_mysql($dbconf['pdo_mysql']);	break;
			case 'pdo_sqlite': $db = new db_pdo_sqlite($dbconf['pdo_sqlite']);	break;
			default: xn_message(-1, '不支持的 db type:'.$dbconf['type']);
		}
		if(!$db || ($db && $db->errstr)) xn_message(-1, $db->errstr);
		return $db;
	}
	return NULL;
}

function db_sql_find_one($sql, $abort = TRUE) {
	global $db;
	if(!$db) return FALSE;
	$arr = $db->find_one($sql);
	if($arr === FALSE && $db->errno != 0) {
		$s = "mysql sql: $sql, mysql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'mysql_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $arr;
}

function db_sql_find($sql, $key = NULL, $abort = TRUE) {
	global $db;
	if(!$db) return FALSE;
	$arr = $db->find($sql, $key);
	if($arr === FALSE && $db->errno != 0) {
		$s = "mysql sql: $sql, mysql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'mysql_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $arr;
}

// 如果为 INSERT 或者 REPLACE，则返回 mysql_insert_id();
// 如果为 UPDATE 或者 DELETE，则返回 mysql_affected_rows();
// 对于非自增的表，INSERT 后，返回的一直是 0
// 判断是否执行成功: mysql_exec() === FALSE
function db_exec($sql, $abort = TRUE) {
	global $db;
	if(!$db) return FALSE;
	
	DEBUG AND xn_log($sql, 'mysql_exec');
	$n = $db->exec($sql);
	
	if($n === FALSE && $db->errno != 0) {
		$s = "sql: $sql, sql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'db_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $n;
}

function db_count($table, $cond = array(), $abort = TRUE) {
	global $db;
	$r = $db->count($table, $cond);
	if($r === FALSE && $db->errno != 0) {
		$s = "sql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'db_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $r;
}

function db_maxid($table, $field, $abort = TRUE) {
	global $db;
	$r = $db->maxid($table, $field);
	if($r === FALSE && $db->errno != 0) {
		$s = "sql: $sql, sql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'db_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $r;
}

/* db 层不对外提供，会导致大量的 NOSQL 写法，不利于阅读和维护。 */
function db_create($table, $arr) {
	return db_insert($table, $arr);
}

function db_insert($table, $arr) {
	$sqladd = array_to_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("INSERT INTO `$table` SET $sqladd");
}

function db_replace($table, $arr) {
	$sqladd = array_to_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("REPLACE INTO `$table` SET $sqladd");
}

function db_update($table, $cond, $update) {
	$condadd = cond_to_sqladd($cond);
	$sqladd = array_to_sqladd($update);
	if(!$sqladd) return FALSE;
	return db_exec("UPDATE `$table` SET $sqladd $condadd");
}

function db_delete($table, $cond) {
	$condadd = cond_to_sqladd($cond);
	return db_exec("DELETE FROM `$table` $condadd");
}

function db_read($table, $cond) {
	$sqladd = cond_to_sqladd($cond);
	$sql = "SELECT * FROM `$table` $sqladd";
	return db_find_one($sql);
}
	
function db_find($table, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10, $key = '', $abort = TRUE) {
	if(strtoupper(substr($table, 0, 7)) != 'SELECT ') {
		$cond = cond_to_sqladd($cond);
		$orderby = orderby_to_sqladd($orderby);
		$offset = ($page - 1) * $pagesize;
		return db_sql_find("SELECT * FROM `$table` $cond$orderby LIMIT $offset,$pagesize", $key, $abort);
	} else {
		// 兼容 XiunoPHP 3.0
		$sql = $table;
		$key = $cond;
		$abort = $orderby;
		return db_sql_find($sql, $key, $abort);
	}
}

function db_find_one($table, $cond = array(), $orderby = array()) {
	if(strtoupper(substr($table, 0, 7)) != 'SELECT ') {
		$cond = cond_to_sqladd($cond);
		$orderby = orderby_to_sqladd($orderby);
		return db_sql_find_one("SELECT * FROM `$table` $cond$orderby LIMIT 1");
	} else {
		// 兼容 XiunoPHP 3.0
		$sql = $table;
		$abort = $cond;
		return db_sql_find_one($sql, $abort);
	}
}

function db_connect(&$err) {
	global $db;
	$r = $db->connect();
	//$err = $db->errstr;
	return $r;
}

function db_close() {
	global $db;
	$r = $db->close();
	//$err = $db->errstr;
	return $r;
}

function kv_get($k) {
	$k = addslashes($k);
	$arr = db_find_one("SELECT k,v FROM bbs_kv WHERE k='$k'");
	return $arr ? xn_json_decode($arr['v']) : NULL;
}

function kv_set($k, $v, $life = 0) {
	$k = addslashes($k);
	$v = addslashes(xn_json_encode($v));
	return db_exec("REPLACE INTO bbs_kv SET  k='$k', v='$v'");
}

function kv_delete($k) {
	$k = addslashes($k);
	return db_exec("DELETE FROM bbs_kv WHERE k='$k'");
}

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

// ---------------------> encrypt function
function xxtea_long2str($v, $w) {
	$len = count($v);
	$n = ($len - 1) << 2;
	if ($w) {
		$m = $v[$len - 1];
		if (($m < $n - 3) || ($m > $n)) return false;
		$n = $m;
	}
	$s = array();
	for ($i = 0; $i < $len; $i++) {
		$s[$i] = pack("V", $v[$i]);
	}
	if ($w) {
		return substr(join('', $s), 0, $n);
	}
	else {
		return join('', $s);
	}
}

function xxtea_str2long($s, $w) {
	$v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
	$v = array_values($v);
	if ($w) {
		$v[count($v)] = strlen($s);
	}
	return $v;
}

function xxtea_int32($n) {
	while ($n >= 2147483648) $n -= 4294967296;
	while ($n <= -2147483649) $n += 4294967296;
	return (int)$n;
}

function xxtea_encrypt($str, $key) {
	if ($str == "") {
		return "";
	}
	$v = xxtea_str2long($str, true);
	$k = xxtea_str2long($key, false);
	if (count($k) < 4) {
		for ($i = count($k); $i < 4; $i++) {
			$k[$i] = 0;
		}
	}
	$n = count($v) - 1;

	$z = $v[$n];
	$y = $v[0];
	$delta = 0x9E3779B9;
	$q = floor(6 + 52 / ($n + 1));
	$sum = 0;
	while (0 < $q--) {
		$sum = xxtea_int32($sum + $delta);
		$e = $sum >> 2 & 3;
		for ($p = 0; $p < $n; $p++) {
			$y = $v[$p + 1];
			$mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$z = $v[$p] = xxtea_int32($v[$p] + $mx);
		}
		$y = $v[0];
		$mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
		$z = $v[$n] = xxtea_int32($v[$n] + $mx);
	}
	return xxtea_long2str($v, false);
}

function xxtea_decrypt($str, $key) {
	if ($str == "") {
		return "";
	}
	$v = xxtea_str2long($str, false);
	$k = xxtea_str2long($key, false);
	if (count($k) < 4) {
		for ($i = count($k); $i < 4; $i++) {
			$k[$i] = 0;
		}
	}
	$n = count($v) - 1;

	$z = $v[$n];
	$y = $v[0];
	$delta = 0x9E3779B9;
	$q = floor(6 + 52 / ($n + 1));
	$sum = xxtea_int32($q * $delta);
	while ($sum != 0) {
		$e = $sum >> 2 & 3;
		for ($p = $n; $p > 0; $p--) {
			$z = $v[$p - 1];
			$mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$y = $v[$p] = xxtea_int32($v[$p] - $mx);
		}
		$z = $v[$n];
		$mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
		$y = $v[0] = xxtea_int32($v[0] - $mx);
		$sum = xxtea_int32($sum - $delta);
	}
	return xxtea_long2str($v, true);
}

function xn_urlencode($s) {
	$s = str_replace('-', '_2d', $s);
	$s = str_replace('.', '_2e', $s);
	$s = str_replace('+', '_2b', $s);
	$s = str_replace('=', '_3d', $s);
	$s = urlencode($s);
	$s = str_replace('%', '_', $s);
	return $s;
}

function xn_urldecode($s) {
	$s = str_replace('_', '%', $s);
	$s = urldecode($s);
	return $s;
}

function xn_json_encode($arg) {
	$r = '';
	switch (gettype($arg)) {
		case 'array':
			$r = is_number_array($arg) ? xn_json_number_array_to_string($arg) : xn_json_assoc_array_to_string($arg);
		break;
		case 'object':
			return xn_json_encode(get_object_vars($arg));
		break;
		case 'integer':
		case 'double':
			$r = is_numeric($arg) ? (string)$arg : 'null';
		break;
		case 'string':
		$r = '"' . strtr($arg, array(
			"\r"   => '\\r',    "\n"   => '\\n',    "\t"   => '\\t',     "\b"   => '\\b',
			"\f"   => '\\f',    '\\'   => '\\\\',   '"'    => '\"',
			"\x00" => '\u0000', "\x01" => '\u0001', "\x02" => '\u0002', "\x03" => '\u0003',
			"\x04" => '\u0004', "\x05" => '\u0005', "\x06" => '\u0006', "\x07" => '\u0007',
			"\x08" => '\b',     "\x0b" => '\u000b', "\x0c" => '\f',     "\x0e" => '\u000e',
			"\x0f" => '\u000f', "\x10" => '\u0010', "\x11" => '\u0011', "\x12" => '\u0012',
			"\x13" => '\u0013', "\x14" => '\u0014', "\x15" => '\u0015', "\x16" => '\u0016',
			"\x17" => '\u0017', "\x18" => '\u0018', "\x19" => '\u0019', "\x1a" => '\u001a',
			"\x1b" => '\u001b', "\x1c" => '\u001c', "\x1d" => '\u001d', "\x1e" => '\u001e',
			"\x1f" => '\u001f'
			)) . '"';
		break;
		case 'boolean':
			$r = $arg ? 1 : 0;
		break;
		default:
			$r = 'null';
	}
	return $r;
}

function xn_json_number_array_to_string($arr) {
	$s = '';
	foreach ($arr as $k=>$v) {
		$s .= ','.xn_json_encode($v);
	}
	$s = substr($s, 1);
	$r = '['.$s.']';
	return $r;
}

function xn_json_assoc_array_to_string($arr) {
	$s = '';
	foreach ($arr as $k=>$v) {
		$s .= ',"'.$k.'":'.xn_json_encode($v);
	}
	$s = substr($s, 1);
	$r = '{'.$s.'}';
	return $r;
}
function is_number_array($arr) {
	$i = 0;
	foreach ($arr as $k=>$v) {
		if(!is_numeric($k) || $k != $i++) return FALSE; // 如果从0 开始，并且连续，则为数字数组
	}
	return TRUE;
}
function xn_json_decode($json) {
	return json_decode($json, 1);
}

/*
// 此函数太耗费资源已经废弃。
function xn_json_encode($json) {
	if(version_compare(PHP_VERSION, '5.4.0') == 1) {
		return json_encode($json, JSON_UNESCAPED_UNICODE);
	} else {
		$json = json_encode($json);
		return ucs2_to_utf8($json);
	}
}
// 此函数仅仅在工具中使用！不允许在主程序调用。不利于APC，并且可能有安全问题。
function ucs2_to_utf8($s) {
	$s = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $s);
	return $s;
}
*/

function encrypt($txt, $key = 'abcd9667676effff') {
	return xn_urlencode(base64_encode(xxtea_encrypt($txt, $key)));
}

function decrypt($txt, $key = 'abcd9667676effff') {
	return xxtea_decrypt(base64_decode(xn_urldecode($txt)), $key);
}
// ---------------------> encrypt function end


// 用例：pages('user-list-{page}.htm', 100, 10, 5);
function pages($url, $totalnum, $page, $pagesize = 20) {
	$totalpage = ceil($totalnum / $pagesize);
	if($totalpage < 2) return '';
	$page = min($totalpage, $page);
	$shownum = 5;	// 显示多少个页 * 2

	$start = max(1, $page - $shownum);
	$end = min($totalpage, $page + $shownum);

	// 不足 $shownum，补全左右两侧
	$right = $page + $shownum - $totalpage;
	$right > 0 && $start = max(1, $start -= $right);
	$left = $page - $shownum;
	$left < 0 && $end = min($totalpage, $end -= $left);

	$s = '';
	$page != 1 && $s .= '<a href="'.str_replace('{page}', $page-1, $url).'">◀</a>';
	if($start > 1) $s .= '<a href="'.str_replace('{page}', 1, $url).'">1 '.($start > 2 ? '... ' : '').'</a>';
	for($i=$start; $i<=$end; $i++) {
		if($i == $page) {
			$s .= '<a href="'.str_replace('{page}', $i, $url).'" class="active">'.$i.'</a>';// active
		} else {
			$s .= '<a href="'.str_replace('{page}', $i, $url).'">'.$i.'</a>';
		}
	}
	if($end != $totalpage) $s .= '<a href="'.str_replace('{page}', $totalpage, $url).'">'.($totalpage - $end > 1 ? '... ' : '').$totalpage.'</a>';
	$page != $totalpage && $s .= '<a href="'.str_replace('{page}', $page+1, $url).'">▶</a>';
	return $s;
}

// 简单的上一页，下一页，比较省资源，不用count(), 推荐使用。
function simple_pages($url, $totalnum, $page, $pagesize = 20) {
	$totalpage = ceil($totalnum / $pagesize);
	if($totalpage < 2) return '';
	$page = min($totalpage, $page);

	$s = '';
	$page > 1 AND $s .= '<a href="'.str_replace('{page}', $page-1, $url).'">上一页</a>';
	$s .= " $page / $totalpage ";
	$totalnum >= $pagesize AND $page != $totalpage AND $s .= '<a href="'.str_replace('{page}', $page+1, $url).'">下一页</a>';
	return $s;
}

function page($page, $n, $pagesize) {
	$total = ceil($n / $pagesize);
	$total < 1 AND $total = 1;
	return mid($page, 1, $total);
}

function mid($n, $min, $max) {
	if($n < $min) return $min;
	if($n > $max) return $max;
	return $n;
}

function humandate($timestamp) {
	global $time;
	$seconds = $time - $timestamp;
	if($seconds > 31536000) {
		return date('Y-n-j', $timestamp);
	} elseif($seconds > 2592000) {
		return floor($seconds / 2592000).'月前';
	} elseif($seconds > 86400) {
		return floor($seconds / 86400).'天前';
	} elseif($seconds > 3600) {
		return floor($seconds / 3600).'小时前';
	} elseif($seconds > 60) {
		return floor($seconds / 60).'分钟前';
	} else {
		return $seconds.'秒前';
	}
}

function humannumber($num) {
	$num > 100000 && $num = ceil($num / 10000).'万';
	return $num;
}

function humansize($num) {
	if($num > 1073741824) {
		return number_format($num / 1073741824, 2, '.', '').'G';
	} elseif($num > 1048576) {
		return number_format($num / 1048576, 2, '.', '').'M';
	} elseif($num > 1024) {
		return number_format($num / 1024, 2, '.', '').'K';
	} else {
		return $num.'B';
	}
}

// 不安全的获取 IP 方式，在开启CDN的时候，如果被人猜到真实 IP，则可以伪造。
function ip() {
	global $conf;
	$ip = '127.0.0.1';
	if(empty($conf['cdn_on'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} else {
		if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			$arr = array_filter(explode(',', $ip));
			$ip = end($arr);
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
	}
	return long2ip(ip2long($ip));
}

// 安全获取用户IP，信任 CDN 发过来的 X-FORWARDED-FOR
/*
function ip() {
	global $conf;
	$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'; // 如果有 CDN 的时候，为离服务器最近的 IP
	if(empty($conf['cdn_ip']) || empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $ip;
	} else {
		// 判断 cdnip 合法性，严格过滤 HTTP_X_FORWARDED_FOR
		// X-Forwarded-For: client1, proxy1, proxy2, ...
		// 离服务器最最近的为最后一个 proxy2，应该在 $conf['cdn_ip'] 当中才安全可信
		foreach($conf['cdn_ip'] as $cdnip) {
			$pos1 = strrpos($cdnip, '.');
			$pos2 = strrpos($ip, '.');
			// 合法 CDN IP 段
			if($ip == $cdnip || ($pos1 == $pos2 && substr($cdnip, $pos1) == '.*' && substr($cdnip, 0, $pos1) == substr($ip, 0, $pos2))) {
				$userips = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['HTTP_X_REAL_IP'];
				if(empty($userips)) return $ip; // 此处 CDN 未转发 userip，有错误，可能需要记录日志
				$arr = array_values(array_filter(explode(',', $userips)));
				return long2ip(ip2long(end($arr)));
			}
		}
		return $ip;
	}
}
*/

// 日志记录
function xn_log($s, $file = 'error') {
	global $time, $ip, $conf, $uid;
	$day = date('Ymd', $time);
	$mtime = date('Y-m-d H:i:s'); // 默认值为 time()
	$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$logpath = APP_LOG_PATH.$day;
	!is_dir($logpath) AND mkdir($logpath, 0777, true);

	$s = str_replace(array("\r\n", "\n", "\t"), ' ', $s);
	$s = "<?php exit;?>\t$mtime\t$ip\t$url\t$uid\t$s\r\n";

	@error_log($s, 3, $logpath."/$file.php");
}

/*
	中国国情下的判断浏览器类型，简直就是五代十国，乱七八糟，对博主的收集表示感谢

	参考：
	http://www.cnblogs.com/wangchao928/p/4166805.html
	http://www.useragentstring.com/pages/Internet%20Explorer/
	https://github.com/serbanghita/Mobile-Detect/blob/master/Mobile_Detect.php

	Mozilla/4.0 (compatible; MSIE 5.0; Windows NT)
	Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)

	Win7+ie9：
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 2.0.50727; SLCC2; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; Tablet PC 2.0; .NET4.0E)

	win7+ie11，模拟 78910 头是一样的
	mozilla/5.0 (windows nt 6.1; wow64; trident/7.0; rv:11.0) like gecko

	Win7+ie8：
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; InfoPath.3)

	WinXP+ie8：
	Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; GTB7.0)

	WinXP+ie7：
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)

	WinXP+ie6：
	Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)

	傲游3.1.7在Win7+ie9,高速模式:
	Mozilla/5.0 (Windows; U; Windows NT 6.1; ) AppleWebKit/534.12 (KHTML, like Gecko) Maxthon/3.0 Safari/534.12

	傲游3.1.7在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)

	搜狗
	搜狗3.0在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E; SE 2.X MetaSr 1.0)

	搜狗3.0在Win7+ie9,高速模式:
	Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.33 Safari/534.3 SE 2.X MetaSr 1.0

	360
	360浏览器3.0在Win7+ie9:
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E)

	QQ 浏览器
	QQ 浏览器6.9(11079)在Win7+ie9,极速模式:
	Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.41 Safari/535.1 QQBrowser/6.9.11079.201

	QQ浏览器6.9(11079)在Win7+ie9,IE内核兼容模式:
	Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.3; .NET4.0C; .NET4.0E) QQBrowser/6.9.11079.201

	阿云浏览器
	阿云浏览器 1.3.0.1724 Beta 在Win7+ie9:
	Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)

	MIUI V5
	Mozilla/5.0 (Linux; U; Android <android-version>; <location>; <MODEL> Build/<ProductLine>) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30 XiaoMi/MiuiBrowser/1.0
*/
function get__browser() {
	// 默认为 chrome 标准浏览器
	$browser = array(
		'device'=>'pc', // pc|mobile|pad
		'name'=>'chrome', // chrome|firefox|ie|opera
		'version'=>30,
	);
	$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
	// 主要判断是否为垃圾IE6789
	if(strpos($agent, 'msie') !== FALSE || stripos($agent, 'trident') !== FALSE) {
		$browser['name'] = 'ie';
		$browser['version'] = 8;
		preg_match('#msie\s*([\d\.]+)#is', $agent, $m);
		if(!empty($m[1])) {
			if(strpos($agent, 'compatible; msie 7.0;') !== FALSE) {
				$browser['version'] = 8;
			} else {
				$browser['version'] = intval($m[1]);
			}
		} else {
			// 匹配兼容模式 Trident/7.0，兼容模式下会有此标志 $trident = 7;
			preg_match('#Trident/([\d\.]+)#is', $agent, $m);
			if(!empty($m[1])) {
				$trident = intval($m[1]);
				$trident == 4 AND $browser['version'] = 8;
				$trident == 5 AND $browser['version'] = 9;
				$trident > 5 AND $browser['version'] = 10;
			}
		}
	}

	if(isset($_SERVER['HTTP_X_WAP_PROFILE']) || (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap") || stripos($agent, 'phone')  || stripos($agent, 'mobile') || strpos($agent, 'ipod'))) {
		$browser['device'] = 'mobile';
	} elseif(strpos($agent, 'pad') !== FALSE) {
		$browser['device'] = 'pad';
		$browser['name'] = '';
		$browser['version'] = '';
	/*
	} elseif(strpos($agent, 'miui') !== FALSE) {
		$browser['device'] = 'mobile';
		$browser['name'] = 'xiaomi';
		$browser['version'] = '';
	*/
	} else {
		$robots = array('bot', 'spider', 'slurp');
		foreach($robots as $robot) {
			if(strpos($agent, $robot) !== FALSE) {
				$browser['name'] = 'robot';
				return $browser;
			}
		}
	}
	return $browser;
}

function check_browser($browser, $abort = TRUE) {
	if($browser['name'] == 'ie' && $browser['version'] < 8) {
		include './pc/view/browser.htm';
		exit;
	//} elseif($browser['device'] != 'pc') {
	//	header('Location: mobile/');
	//	exit;
	}
}

function is_robot() {
	$browser = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
	$robots = array('bot', 'spider', 'slurp');
	foreach($robots as $robot) {
		if(strpos($browser, $robot) !== FALSE) {
			return TRUE;
		}
	}
	return FALSE;
}

// 语言包导致代码可读性变差，语言包自己想办法吧，大部分项目是不需要的。
// return : zh-cn / ko-kr / en
// zh-CN,zh;q=0.8,en;q=0.6
/*function browser_lang() {
	// return 'zh-cn';
	$accept = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) : '';
	$accept = substr($accept, 0, strpos($accept, ';'));
	if(strpos($accept, 'ko-kr') !== FALSE) {
		return 'ko-kr';
	// } elseif(strpos($accept, 'en') !== FALSE) {
	// 	return 'en';
	} else {
		return 'zh-cn';
	}
}*/

/**
 * URL format: http://www.domain.com/demo/user-login.htm?a=b&c=d
 * array(
 *     0 => user,
 *     1 => login
 *     a => b
 *     c => d
 * )
 */
function init_query_string() {

	!empty($_SERVER['HTTP_X_REWRITE_URL']) AND $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	!isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'] = '';

	// 兼容 iis6
	$_SERVER['REQUEST_URI'] = str_replace('/index.php?', '/', $_SERVER['REQUEST_URI']);

	$arr = parse_url($_SERVER['REQUEST_URI']);

	$q = $arr['path'];
	$pos = strrpos($q, '/');
	$pos === FALSE && $pos = -1;
	$q = substr($q, $pos + 1);
	if(substr($q, -4) == '.htm') $q = substr($q, 0, -4);
	$r = $q ? (array)explode('-', $q) : array();

	// 将 xxx.htm?a=b&c=d 后面的正常的 _GET 放到 $_SERVER['_GET']
	if(!empty($arr['query'])) {
		parse_str($arr['query'], $arr2);
		$_SERVER['_GET'] = $arr2;
		$r += $arr2;
	}

	$_SERVER['REQUEST_URI_NO_PATH'] = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);

	// 是否开启 /user/login 这种格式的 URL
	if(defined('URL_REWRITE_PATH_FORMAT_ON')) {
		$r = init_query_string_by_path_formt($_SERVER['REQUEST_URI']) + $r;
	}

	isset($r[0]) AND $r[0] == 'index.php' AND $r[0] = 'index';
	return $r;
}

/**
 * 支持 URL format: http://www.domain.com/user/login?a=1&b=2
 * array(
 *     0 => user,
 *     1 => login,
 *     a => 1,
 *     b => 2
 * )
 */
function init_query_string_by_path_formt($s) {
	$get = array();
	substr($s, 0, 1) == '/' AND $s = substr($s, 1);
	$arr = explode('/', $s);
	$get = $arr;
	$last = array_pop($arr);
	if(strpos($last, '?') !== FALSE) {
		$get = $arr;
		$arr1 = explode('?', $last);
		parse_str($arr1[1], $arr2);
		$get[] = $arr1[0];
		$get = array_merge($get, $arr2);
	}
	return $get;
}

// 安全请求一个 URL
// ini_set('default_socket_timeout', 60);
function http_get($url, $timeout = 5, $times = 3) {
//	$arr = array(
//			'ssl' => array (
//			'verify_peer'   => TRUE,
//			'cafile'        => './cacert.pem',
//			'verify_depth'  => 5,
//			'method'  	=> 'GET',
//			'timeout'  	=> $timeout,
//			'CN_match'      => 'secure.example.com'
//		)
//	);
	$arr = array(
		'http' => array(
			'method'=> 'GET',
			'timeout' => $timeout
		)
	);
	$stream = stream_context_create($arr);
	while($times-- > 0) {
		$s = file_get_contents($url, NULL, $stream, 0, 4096000);
		if($s !== FALSE) return $s;
	}
	return FALSE;
}

function http_post($url, $post = '', $timeout = 10, $times = 3) {
	$stream = stream_context_create(array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\nx-requested-with: XMLHttpRequest", 'method' => 'POST', 'content' => $post, 'timeout' => $timeout)));
	while($times-- > 0) {
		$s = file_get_contents($url, NULL, $stream, 0, 4096000);
		if($s !== FALSE) return $s;
	}
	return FALSE;
}

function https_get($url, $timeout=30, $cookie = '') {
	return https_post($url, $timeout, '', $cookie);
}

function https_post($url, $timeout=30, $post = '', $cookie = '') {
	$w = stream_get_wrappers();
	$allow_url_fopen = strtolower(ini_get('allow_url_fopen'));
	$allow_url_fopen = (empty($allow_url_fopen) || $allow_url_fopen == 'off') ? 0 : 1;
	if(extension_loaded('openssl') && in_array('https', $w) && $allow_url_fopen) {
		return file_get_contents($url);
	} elseif (!function_exists('curl_init')) {
		return xn_error(-1, 'server not installed curl.');
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 2); // 1/2
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在，默认可以省略
	if($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if($cookie) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: $cookie"));
	}
	(!ini_get('safe_mode') && !ini_get('open_basedir')) && curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转, 安全模式不允许
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$data = curl_exec($ch);
	if(curl_errno($ch)) {
		return xn_error(-1, 'Errno'.curl_error($ch));
	}
	if(!$data) {
		curl_close($ch);
		return '';
	}

	list($header, $data) = explode("\r\n\r\n", $data);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match('/Location:(.*?)\n/', $header, $matches);
		$url = trim(array_pop($matches));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$data = curl_exec($ch);
	}
	curl_close($ch);
	return $data;
}

// 多线程抓取数据，需要CURL支持，一般在命令行下执行，此函数收集于互联网，由 xiuno 整理。
function http_multi_get($urls) {
	// 如果不支持，则转为单线程顺序抓取
	if(!function_exists('curl_multi_init')) {
		$data = array();
		foreach($urls as $k=>$url) {
			$data[$k] = https_get($url);
		}
		return $data;
	}

	$multi_handle = curl_multi_init();
	foreach ($urls as $i => $url) {
		$conn[$i] = curl_init($url);
		curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
		$timeout = 3;
		curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT, $timeout); // 超时 seconds
		curl_setopt($conn[$i], CURLOPT_FOLLOWLOCATION, 1);
		//curl_easy_setopt(curl, CURLOPT_NOSIGNAL, 1);
		curl_multi_add_handle($multi_handle, $conn[$i]);
	}
	do{
		$mrc = curl_multi_exec($multi_handle, $active);
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	while($active and $mrc == CURLM_OK) {
		if(curl_multi_select($multi_handle) != - 1) {
			do{
				$mrc = curl_multi_exec($multi_handle, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	}
	foreach($urls as $i => $url) {
		$data[$i] = curl_multi_getcontent($conn[$i]);
		curl_multi_remove_handle($multi_handle, $conn[$i]);
		curl_close($conn[$i]);
	}
	return $data;
}

function file_get_content_try($file, $times = 3) {
	while($times-- > 0) {
		$fp = fopen($file, 'rb');
		if($fp) {
			$size = filesize($file);
			if($size == 0) return '';
			$s = fread($fp, $size);
			fclose($fp);
			return $s;
		} else {
			sleep(1);
		}
	}
	return FALSE;
}

function file_put_content_try($file, $s, $times = 3) {
	while($times-- > 0) {
		$fp = fopen($file, 'wb');
		if($fp AND flock($fp, LOCK_EX)){
			$n = fwrite($fp, $s);
			version_compare(PHP_VERSION, '5.3.2', '>=') AND flock($fp, LOCK_UN);
			fclose($fp);
			return $n;
		} else {
			sleep(1);
		}
	}
	return FALSE;
}

// 判断一个字符串是否在另外一个字符串里面，分隔符 ,
function in_string($s, $str) {
	if(!$s || !$str) return FALSE;
	$s = ",$s,";
	$str = ",$str,";
	return strpos($str, $s) !== FALSE;
}

function move_upload_file($srcfile, $destfile) {
	//$r = move_uploaded_file($srcfile, $destfile);
	$r = copy($srcfile, $destfile);
	return $r;
}

// 文件后缀名，不包含 .
function file_ext($filename) {
	return strtolower(substr(strrchr($filename, '.'), 1));
}

// 文件的前缀，不包含 .
function file_pre($filename) {
	return substr($filename, 0, strrpos($filename, '.'));
}

// 获取路径中的文件名
function file_name($path) {
	return substr($path, strrpos($path, '/') + 1);
}

// 在 header 头中发送DEBUG信息
function t($name = '') {
	global $starttime;
	header("Time $name:".substr(microtime(1) - $starttime, 0, 7));
}

// 获取 http://xxx.com/path/
function http_url_path() {
	$port = $_SERVER['SERVER_PORT'];
	//$portadd = ($port == 80 ? '' : ':'.$port);
	$host = $_SERVER['HTTP_HOST'];  // host 里包含 port
	$path = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
	$http = (($port == 443) || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')) ? 'https' : 'http';
	return  "$http://$host$path/";
}

function url($url) {
	global $conf;
	empty($conf['url_rewrite_on']) AND $conf['url_rewrite_on'] = 0;
	if($conf['url_rewrite_on'] == 0) {
		$url = "?$url.htm";
	} elseif($conf['url_rewrite_on'] == 1) {
		$url = "$url.htm";
	} elseif($conf['url_rewrite_on'] == 2) {
		$url = str_replace('-', '/', $url);
		$url = "?$url";
	} elseif($conf['url_rewrite_on'] == 3) {
		$url = str_replace('-', '/', $url);
		$url = "$url";
	}
	return $url;
}

// 递归遍历目录
function glob_recursive($pattern, $flags = 0) {
	$files = glob($pattern, $flags);
	foreach(glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		 $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
	}
	return $files;
}

// 递归删除目录，这个函数比较危险，传参一定要小心
function rmdir_recusive($dir, $keepdir = 0) {
	if($dir == '/' || $dir == './' || $dir == '../') return FALSE;// 不允许删除根目录，避免程序意外删除数据。
	if(!is_dir($dir)) return FALSE;
	
	substr($dir, -1) != '/' AND $dir .= '/';
	$dir2 = $dir.'*';
	
	$files = glob($dir2);
	foreach($files as $file) {
		if($file == '.' || $file == '..') continue;
		if(!is_dir($file)) {
			unlink($file);
		} else {
			rmdir_recusive($file);
		}
	}
	if(!$keepdir) rmdir($dir);
	return TRUE;
}

/*************************** form.func.php ************************************/

function form_radio_yes_no($name, $checked = 0) {
	$checked = intval($checked);
	return form_radio($name, array(1=>'是', 0=>'否'), $checked);
}

function form_radio($name, $arr, $checked = 0) {
	empty($arr) && $arr = array('否', '是');
	$s = '';

	foreach((array)$arr as $k=>$v) {
		$add = $k == $checked ? ' checked="checked"' : '';
		$s .= "<label><input type=\"radio\" name=\"$name\" value=\"$k\" class=\"noborder\"$add />$v</label> &nbsp; \r\n";
	}
	return $s;
}

function form_select($name, $arr, $checked = 0, $id = TRUE) {
	if(empty($arr)) return '';
	$idadd = $id === TRUE ? "id=\"$name\"" : ($id ? "id=\"$id\"" : '');
	$s = "<select name=\"$name\" $idadd> \r\n";
	$s .= form_options($arr, $checked);
	$s .= "</select> \r\n";
	return $s;
}

function form_options($arr, $checked = 0) {
	$s = '';
	foreach((array)$arr as $k=>$v) {
		$add = $k == $checked ? ' selected="selected"' : '';
		$s .= "<option value=\"$k\"$add>$v</option> \r\n";
	}
	return $s;
}

function form_text($name, $value, $width = 150) {
	is_numeric($width) AND $width .= 'px';
	$s = "<input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\" style=\"width: {$width}\" />";
	return $s;
}

function form_hidden($name, $value) {
	$s = "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$value\" />";
	return $s;
}

function form_textarea($name, $value, $width = 600,  $height = 300) {
	is_numeric($width) AND $width .= 'px';
	is_numeric($height) AND $height .= 'px';
	$s = "<textarea name=\"$name\" id=\"$name\" style=\"width: {$width}; height: {$height};\">$value</textarea>";
	return $s;
}

function form_password($name, $value, $width = 150) {
	is_numeric($width) AND $width .= 'px';
	$s = "<input type=\"password\" name=\"$name\" id=\"$name\" value=\"$value\" style=\"width: {$width}\" />";
	return $s;
}

function form_time($name, $value, $width = 150) {
	is_numeric($width) AND $width .= 'px';
	$s = "<input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\" style=\"width: {$width}\" />";
	return $s;
}

/*************************** image.func.php ************************************/

// 安全缩略，按照ID存储
/*
	$arr = image_safe_thumb('abc.jpg', 123, '.jpg', './upload/', 100, 100);
	array(
		'filesize'=>1234,
		'width'=>100,
		'height'=>100,
		'fileurl' => '001/0123/1233.jpg'
	);
*/

// 不包含 .
function image_ext($filename) {
	return strtolower(substr(strrchr($filename, '.'), 1));
}

// 获取安全的文件名，如果文件存在，则加时间戳和随机数，避免重复
function image_safe_name($filename, $dir) {
	global $time;
	// 最后一个 . 保留，其他的 . 替换
	$s1 = substr($filename, 0, strrpos($filename, '.'));
	$s2 = substr(strrchr($filename, '.'), 1);
	$s1 = preg_replace('#\W#', '_', $s1);
	$s2 = preg_replace('#\W#', '_', $s2);
	if(is_file($dir."$s1.$s2")) {
		$newname = $s1.$time.rand(1, 1000).'.'.$s2;
	} else {
		$newname = "$s1.$s2";
	}
	return $newname;
}

// 缩略图的名字
function image_thumb_name($filename) {
	return substr($filename, 0, strrpos($filename, '.')).'_thumb'.strrchr($filename, '.');
}

// 随即文件名
function image_rand_name($k) {
	global $time;
	return $time.'_'.rand(1000000000, 9999999999).'_'.$k;
}

/*
	实例：
	image_set_dir(123, './upload');

	000/000/1.jpg
	000/000/100.jpg
	000/000/100.jpg
	000/000/999.jpg
	000/001/1000.jpg
	000/001/001.jpg
	000/002/001.jpg
*/
function image_set_dir($id, $dir) {

	$id = sprintf("%09d", $id);
	$s1 = substr($id, 0, 3);
	$s2 = substr($id, 3, 3);
	$dir = $dir."$s1/$s2";
	!is_dir($dir) && mkdir($dir, 0777, TRUE);

	return "$s1/$s2";
}

// 取得 user home 路径
function image_get_dir($id) {
	$id = sprintf("%09d", $id);
	$s1 = substr($id, 0, 3);
	$s2 = substr($id, 3, 3);
	return "$s1/$s2";
}

/*
	实例：
 	image_thumb('xxx.jpg', 'xxx_thumb.jpg', 200, 200);

 	返回：
 	array('filesize'=>0, 'width'=>0, 'height'=>0)
 */
function image_thumb($sourcefile, $destfile, $forcedwidth = 80, $forcedheight = 80) {
	$return = array('filesize'=>0, 'width'=>0, 'height'=>0);
	$destext = image_ext($destfile);
	if(!in_array($destext, array('gif', 'jpg', 'bmp', 'png'))) {
		return $return;
	}

	$imginfo = getimagesize($sourcefile);
	$src_width = $imginfo[0];
	$src_height = $imginfo[1];
	if($src_width == 0 || $src_height == 0) {
		return $return;
	}

	if(!function_exists('imagecreatefromjpeg')) {
		copy($sourcefile, $destfile);
		$return = array('filesize'=>filesize($destfile), 'width'=>$src_width, 'height'=>$src_height);
		return $return;
	}

	// 按规定比例缩略
	$src_scale = $src_width / $src_height;
	$des_scale = $forcedwidth / $forcedheight;
	if($src_width <= $forcedwidth && $src_height <= $forcedheight) {
		$des_width = $src_width;
		$des_height = $src_height;
	} elseif($src_scale >= $des_scale) {
		$des_width = ($src_width >= $forcedwidth) ? $forcedwidth : $src_width;
		$des_height = $des_width / $src_scale;
		$des_height = ($des_height >= $forcedheight) ? $forcedheight : $des_height;
	} else {
		$des_height = ($src_height >= $forcedheight) ? $forcedheight : $src_height;
		$des_width = $des_height * $src_scale;
		$des_width = ($des_width >= $forcedwidth) ? $forcedwidth : $des_width;
	}

	switch ($imginfo['mime']) {
		case 'image/jpeg':
			$img_src = imagecreatefromjpeg($sourcefile);
			!$img_src && $img_src = imagecreatefromgif($sourcefile);
			break;
		case 'image/gif':
			$img_src = imagecreatefromgif($sourcefile);
			!$img_src && $img_src = imagecreatefromjpeg($sourcefile);
			break;
		case 'image/png':
			$img_src = imagecreatefrompng($sourcefile);
			break;
		case 'image/wbmp':
			$img_src = imagecreatefromwbmp($sourcefile);
			break;
		default :
			return $return;
	}

	if(!$img_src) return $return;

	$img_dst = imagecreatetruecolor($des_width, $des_height);
	imagefill($img_dst, 0, 0 , 0xFFFFFF);
	imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $des_width, $des_height, $src_width, $src_height);

	$tmppath = APP_TMP_PATH;

	$tmpfile = $tmppath.md5($destfile).'.tmp';
	switch($destext) {
		case 'jpg': imagejpeg($img_dst, $tmpfile, 90); break;
		case 'gif': imagegif($img_dst, $tmpfile); break;
		case 'png': imagepng($img_dst, $tmpfile); break;
	}
	$r = array('filesize'=>filesize($tmpfile), 'width'=>$des_width, 'height'=>$des_height);;
	copy($tmpfile, $destfile);
	is_file($tmpfile) && unlink($tmpfile);
	imagedestroy($img_dst);
	return $r;
}


/**
 * 图片裁切
 *
 * @param string $sourcefile	原图片路径(绝对路径/abc.jpg)
 * @param string $destfile 		裁切后生成新名称(绝对路径/rename.jpg)
 * @param int $clipx 			被裁切图片的X坐标
 * @param int $clipy 			被裁切图片的Y坐标
 * @param int $clipwidth 		被裁区域的宽度
 * @param int $clipheight 		被裁区域的高度
 * image_clip('xxx/x.jpg', 'xxx/newx.jpg', 10, 40, 150, 150)
 */
function image_clip($sourcefile, $destfile, $clipx, $clipy, $clipwidth, $clipheight) {
	$getimgsize = getimagesize($sourcefile);
	if(empty($getimgsize)) {
		return 0;
	} else {
		$imgwidth = $getimgsize[0];
		$imgheight = $getimgsize[1];
		if($imgwidth == 0 || $imgheight == 0) {
			return 0;
		}
	}

	if(!function_exists('imagecreatefromjpeg')) {
		copy($sourcefile, $destfile);
		return filesize($destfile);
	}
	switch($getimgsize[2]) {
		case 1 :
			$imgcolor = imagecreatefromgif($sourcefile);
			break;
		case 2 :
			$imgcolor = imagecreatefromjpeg($sourcefile);
			break;
		case 3 :
			$imgcolor = imagecreatefrompng($sourcefile);
			break;
	}

	if(!$imgcolor) return 0;

	$img_dst = imagecreatetruecolor($clipwidth, $clipheight);
	imagefill($img_dst, 0, 0 , 0xFFFFFF);
	imagecopyresampled($img_dst, $imgcolor, 0, 0, $clipx, $clipy, $imgwidth, $imgheight, $imgwidth, $imgheight);

	$tmppath = APP_TMP_PATH;

	$tmpfile = $tmppath.md5($destfile).'.tmp';
	imagejpeg($img_dst, $tmpfile, 100);
	$n = filesize($tmpfile);
	copy($tmpfile, $destfile);
	is_file($tmpfile) && @unlink($tmpfile);
	return $n;
}

// 先裁切后缩略，因为确定了，width, height, 不需要返回宽高。
function image_clip_thumb($sourcefile, $destfile, $forcedwidth = 80, $forcedheight = 80) {
	// 获取原图片宽高
	$getimgsize = getimagesize($sourcefile);
	if(empty($getimgsize)) {
		return 0;
	} else {
		$src_width = $getimgsize[0];
		$src_height = $getimgsize[1];
		if($src_width == 0 || $src_height == 0) {
			return 0;
		}
	}

	$src_scale = $src_width / $src_height;
	$des_scale = $forcedwidth / $forcedheight;

	if($src_width <= $forcedwidth && $src_height <= $forcedheight) {
		$des_width = $src_width;
		$des_height = $src_height;
		$n = image_clip($sourcefile, $destfile, 0, 0, $des_width, $des_height);
		return filesize($destfile);
	// 原图为横着的矩形
	} elseif($src_scale >= $des_scale) {
		// 以原图的高度作为标准，进行缩略
		$des_height = $src_height;
		$des_width = $src_height / $des_scale;
		$n = image_clip($sourcefile, $destfile, 0, 0, $des_width, $des_height);
		if($n <= 0) return 0;
		$r = image_thumb($destfile, $destfile, $forcedwidth, $forcedheight);
		return $r['filesize'];
	// 原图为竖着的矩形
	} else {
		// 以原图的宽度作为标准，进行缩略
		$des_width = $src_width;
		$des_height = $src_width / $des_scale;

		// echo "src_scale: $src_scale, src_width: $src_width, src_height: $src_height \n";
		// echo "des_scale: $des_scale, forcedwidth: $forcedwidth, forcedheight: $forcedheight \n";
		// echo "des_width: $des_width, des_height: $des_height \n";
		// exit;

		$n = image_clip($sourcefile, $destfile, 0, 0, $des_width, $des_height);
		if($n <= 0) return 0;
		$r = image_thumb($destfile, $destfile, $forcedwidth, $forcedheight);
		return $r['filesize'];
	}
}

function image_safe_thumb($sourcefile, $id, $ext, $dir1, $forcedwidth, $forcedheight, $randomname = 0) {
	global $time, $ip;
	$dir2 = image_set_dir($id, $dir1);
	$filename = $randomname ? md5(rand(0, 1000000000).$time.$ip).$ext : $id.$ext;
	$filepath = "$dir1$dir2/$filename";
	$arr = image_thumb($sourcefile, $filepath, $forcedwidth, $forcedheight);
	$arr['fileurl'] = "$dir2/$filename";
	return $arr;
}

// image_thumb('D:/image/IMG_0433.JPG', 'd:/image/xxx.gif');
// echo image_clip_thumb('d:/image/editor_bg.gif', 'd:/image/editor_bg_2.gif', 200, 200);

/*************************** db.class.php ************************************/

class db_mysql {
	
	public $conf = array(); // 配置，可以支持主从
	public $wlink = NULL;  // 写连接
	public $rlink = NULL;  // 读连接
	public $link = NULL;   // 最后一次使用的连接
	public $errno = 0;
	public $errstr = '';
	public $sqls = array();
	
	public function __construct(&$conf) {
		$this->conf = &$conf;
	}
	
	// 根据配置文件连接
	public function connect() {
		$this->wlink = $this->connect_master();
		$this->rlink = $this->connect_slave();
		return $this->wlink && $this->rlink;
	}
	
	// 连接写服务器
	public function connect_master() {
		if($this->wlink) return $this->wlink;
		$conf = $this->conf['master'];
		if(!$this->wlink) $this->wlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		return $this->wlink;
	}
	
	// 连接从服务器，如果有多台，则随机挑选一台，如果为空，则与主服务器一致。
	public function connect_slave() {
		if($this->rlink) return $this->rlink;
		if(empty($this->conf['slaves'])) {
			if($this->wlink === NULL) $this->wlink = $this->connect_master();
			$this->rlink = $this->wlink;
		} else {
			$n = array_rand($this->conf['slaves']);
			$conf = $this->conf['slaves'][$n];
			$this->rlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		}
		return $this->rlink;
	}
	
	public function real_connect($host, $user, $password, $name, $charset = '', $engine = '') {
		if(IN_SAE) {
			$link = @mysql_connect($host, $user, $password); // 如果用户名相同，则返回同一个连接。 fastcgi 持久连接更省资源
		} else {
			$link = @mysql_pconnect($host, $user, $password); // 如果用户名相同，则返回同一个连接。 fastcgi 持久连接更省资源
		}
		if(!$link) { $this->error(-10000); return FALSE; }
		if(!mysql_select_db($name, $link)) { $this->error(-10001); return FALSE; }
		strtolower($engine) == 'innodb' AND $this->query("SET innodb_flush_log_at_trx_commit=no", $link);
		$charset AND $this->query("SET names $charset, sql_mode=''", $link);
		return $link;
	}
	
	public function find_one($sql) {
		$query = $this->query($sql);
		if(!$query) return $query;
		// 如果结果为空，返回 FALSE
		return mysql_fetch_assoc($query);
	}
	
	public function find($sql, $key = NULL) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$arrlist = array();
		while($arr = mysql_fetch_assoc($query)) {
			$key ? $arrlist[$arr[$key]] = $arr : $arrlist[] = $arr; // 顺序没有问题，尽管是数字，仍然是有序的，看来内部实现是链表，与 js 数组不同。
		}
		return $arrlist;
	}
	
	public function query($sql, $link = NULL) {
		if(!$link) {
			if(!$this->rlink && !$this->connect_slave()) return FALSE;;
			$link = $this->link = $this->rlink;
		}
		$query = mysql_query($sql, $link);
		if($query === FALSE) $this->error();
		
		if(count($this->sqls) < 1000) $this->sqls[] = $sql;
		
		return $query;
	}
	
	public function exec($sql, $link = NULL) {
		if(!$link) {
			if(!$this->wlink && !$this->connect_master()) return FALSE;
			$link = $this->link = $this->wlink;
		}
		$query = mysql_query($sql, $this->wlink);
		if($query !== FALSE) {
			$pre = strtoupper(substr(trim($sql), 0, 7));
			if($pre == 'INSERT ' || $pre == 'REPLACE') return mysql_insert_id($this->wlink);
			elseif($pre == 'UPDATE ' || $pre == 'DELETE ') return mysql_affected_rows($this->wlink);
		} else {
			$this->error();
		}
		
		if(count($this->sqls) < 1000) $this->sqls[] = $sql;
		
		return $query;
	}
	
	public function count($table, $cond = array()) {
		$cond = cond_to_sqladd($cond);
		$sql = "SELECT COUNT(*) AS num FROM `$table` $cond";
		$arr = $this->find_one($sql);
		return !empty($arr) ? intval($arr['num']) : $arr;
	}
	
	public function maxid($table, $field) {
		$sql = "SELECT MAX($field) AS maxid FROM `$table`";
		$arr = $this->find_one($sql);
		return !empty($arr) ? intval($arr['maxid']) : $arr;
	}
	
	//public function version() {
	//	return mysql_get_server_info($this->link);
	//}
	
	public function version() {
		$r = $this->find_one("SELECT VERSION() AS v");
		return $r['v'];
	}
	
	public function error($errno = 0, $errstr = '') {
		$this->errno = $errno ? $errno : ($this->link ? mysql_errno($this->link) : mysql_errno());
		$this->errstr = $errstr ? $errstr : ($this->link ? mysql_error($this->link) : mysql_error());
		DEBUG AND trigger_error('Database Error:'.$this->errstr);
	}
	
	// pconnect 不释放连接
	public function __destruct() {
		if($this->wlink) $this->wlink = NULL;
		if($this->rlink) $this->rlink = NULL;
	}
}

/*
$conf = array (
	'master' => array (								
		'host' => 'localhost',					
		'user' => 'root',		
		'password' => 'root',	
		'name' => 'test',	
		'charset' => 'utf8',	
		'tablepre' => 'bbs_',				
		'engine'=>'MyISAM',
	),			
	'slaves' => array(
		array(
		'host' => 'localhost',					
		'user' => 'root',		
		'password' => 'root',	
		'name' => 'test',	
		'charset' => 'utf8',	
		'tablepre' => 'bbs_',				
		'engine'=>'MyISAM',
		),
	)
);
$m = new db_mysql($conf);
if($m->errno) exit($m->errstr);
$userlist = $m->find_one("SELECT * FROM user");
$r = $m->exec("UPDATE user SET mobile='abc' WHERE uid=1");
if($m->errno) exit($m->errstr);
print_r($userlist);*/




class db_pdo_mysql {
	
	public $conf = array(); // 配置，可以支持主从
	public $wlink = NULL;  // 写连接
	public $rlink = NULL;  // 读连接
	public $link = NULL;   // 最后一次使用的连接
	public $errno = 0;
	public $errstr = '';
	public $sqls = array();
	
	public function __construct(&$conf) {
		$this->conf = &$conf;
	}
	
	// 根据配置文件连接
	public function connect() {
		$this->wlink = $this->connect_master();
		$this->rlink = $this->connect_slave();
		return $this->wlink && $this->rlink;
	}
	
	// 连接写服务器
	public function connect_master() {
		if($this->wlink) return $this->wlink;
		$conf = $this->conf['master'];
		$this->wlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		return $this->wlink;
	}
	
	// 连接从服务器，如果有多台，则随机挑选一台，如果为空，则与主服务器一致。
	public function connect_slave() {
		if($this->rlink) return $this->rlink;
		if(empty($this->conf['slaves'])) {
			if(!$this->wlink) $this->wlink = $this->connect_master();
			$this->rlink = $this->wlink;
		} else {
			$n = array_rand($this->conf['slaves']);
			$conf = $this->conf['slaves'][$n];
			$this->rlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		}
		return $this->rlink;
	}
	
	public function real_connect($host, $user, $password, $name, $charset = '', $engine = '') {
		if(strpos($host, ':') !== FALSE) {
			list($host, $port) = explode(':', $host);
		} else {
			$port = 3306;
		}
		try {
			$link = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $password);
			//$link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		} catch (Exception $e) {  
			$this->error(-10000, '连接数据库服务器失败:'.$e->getMessage());
			return FALSE;
	        }
	        //$link->setFetchMode(PDO::FETCH_ASSOC);
			$charset AND $link->query("SET names $charset, sql_mode=''");
		 //$link->query('SET NAMES '.($charset ? $charset.',' : '').', sql_mode=""');  
		return $link;
	}

	public function find_one($sql) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$query->setFetchMode(PDO::FETCH_ASSOC);
		return $query->fetch();
	}
	
	public function find($sql, $key = NULL) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$arrlist = $query->fetchAll();
		$key AND arrlist_change_key($arrlist, $key);
		return $arrlist;
	}
	
	public function query($sql) {
		if(!$this->rlink && !$this->connect_slave()) return FALSE;
		$link = $this->link = $this->rlink;
		$query = $link->query($sql);
		if($query === FALSE) $this->error();
		if(count($this->sqls) < 1000) $this->sqls[] = $sql;
		return $query;
	}
	
	public function exec($sql) {
		if(!$this->wlink && !$this->connect_master()) return FALSE;
		$link = $this->link = $this->wlink;
		$n = $link->exec($sql); // 返回受到影响的行，插入的 id ?
		if($n !== FALSE) {
			$pre = strtoupper(substr(trim($sql), 0, 7));
			if($pre == 'INSERT ' || $pre == 'REPLACE') {
				return $this->last_insert_id();
			}
		} else {
			$this->error();
		}
		
		if(count($this->sqls) < 1000) $this->sqls[] = $sql;
		
		return $n;
	}
	
	// innoDB 通过 information_schema 读取大致的行数
	// SELECT TABLE_ROWS FROM information_schema.tables WHERE TABLE_SCHEMA = '$table' AND TABLE_NAME = '$table';
	// SELECT TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table';
	public function count($table, $cond = array()) {
		$cond = cond_to_sqladd($cond);
		$sql = "SELECT COUNT(*) AS num FROM `$table` $cond";
		$arr = $this->find_one($sql);
		return !empty($arr) ? intval($arr['num']) : $arr;
	}
	
	public function maxid($table, $field) {
		$sql = "SELECT MAX($field) AS maxid FROM `$table`";
		$arr = $this->find_one($sql);
		return !empty($arr) ? intval($arr['maxid']) : $arr;
	}
	
	public function last_insert_id() {
		return $this->wlink->lastinsertid();
	}
	
	public function version() {
		$r = $this->find_one("SELECT VERSION() AS v");
		return $r['v'];
	}
	
	// 设置错误。
	public function error($errno = 0, $errstr = '') {
		$error = $this->link ? $this->link->errorInfo() : array(0, $errno, $errstr);
		$this->errno = $errno ? $errno : (isset($error[1]) ? $error[1] : 0);
		$this->errstr = $errstr ? $errstr : (isset($error[2]) ? $error[2] : '');
		DEBUG AND trigger_error('Database Error:'.$this->errstr);
	}
	
	public function __destruct() {
		if($this->wlink) $this->wlink = NULL;
		if($this->rlink) $this->rlink = NULL;
	}
}


class db_pdo_sqlite {
	public $conf = array(); // 配置，可以支持主从
	public $wlink = NULL;  // 写连接
	public $rlink = NULL;  // 读连接
	public $link = NULL;   // 最后一次使用的连接
	public $errno = 0;
	public $errstr = '';
	public function __construct(&$conf) {
		$this->conf = &$conf;
	}
	
	// 根据配置文件连接
	public function connect() {
		$this->wlink = $this->connect_master();
		$this->rlink = $this->connect_slave();
		return $this->wlink && $this->rlink;
	}
	
	// 连接写服务器
	public function connect_master() {
		if($this->wlink) return $this->wlink;
		$conf = $this->conf['master'];
		$this->wlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		return $this->wlink;
	}
	
	// 连接从服务器，如果有多台，则随机挑选一台，如果为空，则与主服务器一致。
	public function connect_slave() {
		if($this->rlink) return $this->rlink;
		if(empty($this->conf['slaves'])) {
			if(!$this->wlink) $this->wlink = $this->connect_master();
			$this->rlink = $this->wlink;
		} else {
			$n = array_rand($this->conf['slaves']);
			$conf = $this->conf['slaves'][$n];
			$this->rlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		}
		return $this->rlink;
	}
	
	public function real_connect($host, $user, $password, $name, $charset = '', $engine = '') {
		$sqlitedb = "sqlite:$host";
		try {
			$link = new PDO($sqlitedb);//连接sqlite
			$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (Exception $e) {
			$this->error(-10000, '连接数据库服务器失败:'.$e->getMessage());
			return FALSE;
	        }
	        //$link->setFetchMode(PDO::FETCH_ASSOC);
		return $link;
		
	}

	public function find_one($sql) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$query->setFetchMode(PDO::FETCH_ASSOC);
		return $query->fetch();
	}
	
	public function find($sql, $key = NULL) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$query->setFetchMode(PDO::FETCH_ASSOC);
		$arrlist = $query->fetchAll();
		$key AND arrlist_change_key($arrlist, $key);
		return $arrlist;
	}
	
	public function query($sql) {
		if(!$this->rlink && !$this->connect_slave()) return FALSE;
		$link = $this->link = $this->rlink;
		$query = $link->query($sql);
		if($query === FALSE) $this->error();
		
		if(count($this->sqls) < 1000) $this->sqls[] = $sql;
		
		return $query;
	}
	
	public function exec($sql) {
		if(!$this->wlink && !$this->connect_master()) return FALSE;
		$link = $this->link = $this->wlink;
		$n = $link->exec($sql); // 返回受到影响的行，插入的 id ?
		if($n !== FALSE) {
			$pre = strtoupper(substr(trim($sql), 0, 7));
			if($pre == 'INSERT ' || $pre == 'REPLACE') {
				return $this->last_insert_id();
			}
		} else {
			$this->error();
		}
		
		if(count($this->sqls) < 1000) $this->sqls[] = $sql;
		
		return $n;
	}
	
	public function count($table, $cond = array()) {
		$cond = cond_to_sqladd($cond);
		$sql = "SELECT COUNT(*) AS num FROM `$table` $cond";
		$arr = $this->find_one($sql);
		return !empty($arr) ? intval($arr['num']) : $arr;
	}
	
	public function maxid($table, $field) {
		$sql = "SELECT MAX($field) AS maxid FROM `$table`";
		$arr = $this->find_one($sql);
		return !empty($arr) ? intval($arr['maxid']) : $arr;
	}
	
	public function last_insert_id() {
		$this->wlink->lastinsertid();
	}
	
	public function version() {
		$r = $this->find_one("SELECT VERSION() AS v");
		return $r['v'];
	}
	
	// 设置错误。
	public function error($errno = 0, $errstr = '') {
		$error = $this->link ? $this->link->errorInfo() : array(0, 0, '');
		$this->errno = $errno ? $errno : (isset($error[1]) ? $error[1] : 0);
		$this->errstr = $errstr ? $errstr : (isset($error[2]) ? $error[2] : '');
		DEBUG AND trigger_error('Database Error:'.$this->errstr);
	}
	
	public function __destruct() {
		if($this->wlink) $this->wlink = NULL;
		if($this->rlink) $this->rlink = NULL;
	}
}


/*************************** cache.class.php ************************************/


class cache_apc {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!function_exists('apc_get')) {
                        $this->error(1, 'APC 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
        	$k = APP_CACHE_PRE.$k;
                return apc_store($k, $v, $life);
        }
        public function get($k) {
        	$k = APP_CACHE_PRE.$k;
                return apc_get($k);
        }
        public function delete($k) {
        	$k = APP_CACHE_PRE.$k;
                return apc_delete($k);
        }
        public function truncate() {
                return apc_clear_cache('user');
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

// 经过测试 xcache3.1 xcache_set() life 参数不管用
class cache_xcache {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!function_exists('xcache_set')) {
                        $this->error(1, 'Xcache 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
        	$k = APP_CACHE_PRE.$k;
                return xcache_set($k, $v, $life);
        }
        // 取不到数据的时候返回 NULL，不是 FALSE
        public function get($k) {
        	$k = APP_CACHE_PRE.$k;
                return xcache_get($k);
        }
        public function delete($k) {
        	$k = APP_CACHE_PRE.$k;
                return xcache_unset($k);
        }
        public function truncate() {
                xcache_unset_by_prefix(APP_CACHE_PRE);
                return TRUE;
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

class cache_redis {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!extension_loaded('Redis')) {
                        $this->error(1, ' Redis 扩展没有加载');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
                if($this->link) return $this->link;
                $redis = new Redis;
                $r = $redis->connect('localhost', '6379');
                if(!$r) {
                        $this->error(2, '连接 Redis 服务器失败。');
                        return FALSE;
                }
                //$redis->select('xn');
                $this->link = $redis;
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $v = xn_json_encode($v);
                $r = $this->link->set($k, $v);
                $life AND $r AND $this->link->expire($k, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->get($k);
                return $r === FALSE ? NULL : xn_json_decode($r);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->del($k) ? TRUE : FALSE;
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->flushdb(); // flushall
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

class cache_memcached {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!extension_loaded('Memcache') && !extension_loaded('Memcached') ) {
                        $this->error(1, ' Memcached 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
                $conf = $this->conf;
                if($this->link) return $this->link;
                if(extension_loaded('Memcache')) {
                        $memcache = new Memcache;
                } elseif(extension_loaded('Memcached')) {
                        $memcache = new Memcached;
                } else {
                        $this->error(2, 'Memcache 扩展不存在。');
                        return FALSE;
                }
                $r = $memcache->connect($conf['host'], $conf['port']);
                if(!$r) {
                        $this->error(3, '连接 Memcached 服务器失败。');
                        return FALSE;
                }
                $this->link = $memcache;
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->set($k, $v, 0, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->get($k);
                return $r === FALSE ? NULL : $r;
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->delete($k); // TRUE|FALSE
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->flush();
        }
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

// mysql 的实现封装耦合性有点高，要求有 bbs_cache 表存在。参看 install/install.sql 中的结构。
class cache_mysql {
        public $conf = array();
        public $db = NULL;
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public $table = 'bbs_cache';
        public function __construct($conf = array()) {
                if(!is_array($conf)) {
                        $this->db = $conf;
                } else {
                        $this->conf = $conf;
                        if(function_exists('mysql_connect')) {
                                $db = new db_mysql($conf);
                                $db->errstr AND $this->error($db->errno, $db->errstr);
                        } elseif(class_exists('PDO')) {
                                $db = new db_pdo_mysql($conf);
                                $db->errstr AND $this->error($db->errno, $db->errstr);
                        } else {
                                $this->error(1, 'PHP 的 mysqllib, pdo_mysql 扩展没有加载');
                        }
                        $this->db = $db;
                }
        }
        public function connect() {
                if($this->link) return $this->link;
                $db = $this->db;
                $this->link = $db->connect();
                $db->errstr AND $this->error($db->errno, $db->errstr);
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $time = time();
                $expiry = $life ? $time + $life : 0;
                $v = addslashes(xn_json_encode($v));
                $r = $this->db->exec("REPLACE INTO `{$this->table}` SET k='$k',v='$v',expiry='$expiry'");
                if($this->db->errno) $this->error($this->db->errno, $this->db->errstr);
                return $r !== FALSE;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $time = time();
                $arr = $this->db->find_one("SELECT * FROM `{$this->table}` WHERE k='$k'");
                if(!$arr) return NULL;
                if($arr['expiry'] && $time > $arr['expiry']) {
                        $this->db->exec("DELETE FROM `{$this->table}` WHERE k='$k'", $this->link);
                        return NULL;
                }
                return xn_json_decode($arr['v'], 1);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->db->exec("DELETE FROM `{$this->table}` WHERE k='$k'", $this->link);
                return empty($r) ? FALSE : TRUE;
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                $r = $this->db->exec("TRUNCATE `{$this->table}`", $this->link);
                return TRUE;
        }
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno ? $errno : ($this->link ? mysql_errno($this->link) : mysql_errno());
                $this->errstr = $errstr ? $errstr : ($this->link ? mysql_error($this->link) : mysql_error());
        }
        public function __destruct() {

        }
}

class cache_saekv {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!extension_loaded('SaeKV')) {
                        $this->error(1, ' SaeKV 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $kv = new SaeKV();
                $this->link = $kv;
                $this->conf = $conf;
        }
        public function connect() {
                if($this->link) return $this->link;
                $this->link->init();
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->set($k, $v, 0, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->get($k);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->delete($k);
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                $keys = $kv->pkrget(APP_CACHE_PRE, 100); // 获取 100 条
                foreach($keys as $k) {
                        $this->delete($k);
                }
                return TRUE;// $this->link->flush(); // 很不幸 sae 并未提供 flush all 接口
        }
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

/*************** 如果配置文件配置了 DB, CACHE, 初始化 DB CACHE **************/

// 初始化 db cache，这里并没有连接，在获取数据的时候会自动连接。

$db = !empty($conf['db']) ? db_new($conf['db']) : NULL;
$cache = !empty($conf['cache']) ? cache_new($conf['cache']) : NULL;
$db AND $db->errno AND xn_message(-1, $db->errstr); // 安装的时候检测过了，不必每次都检测。但是要考虑环境移植。
$cache AND $cache->errno AND xn_message(-1, $cache->errstr);

?>