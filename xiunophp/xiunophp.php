<?php

/*
* Copyright (C) 2015 xiuno.com
*/

/*
	
	XiunoPHP 4.0 只是定义了一些函数和全局变量，方便使用，并没有要求如何组织代码。
	
	XiunoPHP 4.0，采用简单结构，有利于 HHVM 编译 / opcode 缓存，完美支持 PHP7
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

// db cache
include './xiunophp/db_mysql.class.php';
include './xiunophp/db_pdo_mysql.class.php';
include './xiunophp/db_pdo_sqlite.class.php';
include './xiunophp/cache_apc.class.php';
include './xiunophp/cache_memcached.class.php';
include './xiunophp/cache_mysql.class.php';
include './xiunophp/cache_redis.class.php';
include './xiunophp/cache_saekv.class.php';
include './xiunophp/cache_xcache.class.php';
include './xiunophp/db.func.php';
include './xiunophp/cache.func.php';


// 初始化 db cache，这里并没有连接，在获取数据的时候会自动连接。
$db = !empty($conf['db']) ? db_new($conf['db']) : NULL;
$cache = !empty($conf['cache']) ? cache_new($conf['cache']) : NULL;
$db AND $db->errno AND xn_message(-1, $db->errstr); // 安装的时候检测过了，不必每次都检测。但是要考虑环境移植。
$cache AND $cache->errno AND xn_message(-1, $cache->errstr);

// ----------------------------------------------------------> 全局变量申明结束

// ----------------------------------------------------------> 全局函数

include './xiunophp/form.func.php';
include './xiunophp/image.func.php';
include './xiunophp/array.func.php';
include './xiunophp/xn_encrypt.func.php';
include './xiunophp/misc.func.php';

?>