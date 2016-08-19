<?php

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

error_reporting(DEBUG ? E_ALL : 0);
version_compare(PHP_VERSION, '5.3.0', '<') AND set_magic_quotes_runtime(0);
$get_magic_quotes_gpc = get_magic_quotes_gpc();
$starttime = microtime(1);
$time = time();
$_SERVER['APP_PATH'] = getcwd(); // 保存当前路径, register_shutdown_function() 需要 chdir() 到该路径

// 头部，判断是否运行在命令行下
define('IN_CMD', !empty($_SERVER['SHELL']) || empty($_SERVER['REMOTE_ADDR']));
if(IN_CMD) {
	!isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] = '';
	!isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'] = '';
	!isset($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] = 'GET';
} else {
	header("Content-type: text/html; charset=utf-8");
	header("Cache-Control: max-age=0;"); // 手机返回的时候回导致刷新
	header("Cache-Control: no-store;");
	//header("X-Powered-By: XiunoPHP 4.0");
}

// hook xiunophp_include_before.php

// ----------------------------------------------------------> db cache class

$__getcwd = getcwd();
chdir(dirname(__FILE__));

include './db_mysql.class.php';
include './db_pdo_mysql.class.php';
include './db_pdo_sqlite.class.php';
include './cache_apc.class.php';
include './cache_memcached.class.php';
include './cache_mysql.class.php';
include './cache_redis.class.php';
include './cache_xcache.class.php';

// ----------------------------------------------------------> 全局函数

include './db.func.php';
include './cache.func.php';
include './form.func.php';
include './image.func.php';
include './array.func.php';
include './xn_encrypt.func.php';
include './misc.func.php';

chdir($__getcwd);
unset($__getcwd);

// hook xiunophp_include_after.php

empty($conf) AND $conf = array('db'=>array(), 'cache'=>array(), 'tmp_path'=>'./', 'log_path'=>'./', 'timezone'=>'Asia/Shanghai');
empty($conf['tmp_path']) AND $conf['tmp_path'] = ini_get('upload_tmp_dir');
empty($conf['log_path']) AND $conf['log_path'] = './';

$ip = ip();
$longip = ip2long($ip);
$longip < 0 AND $longip = sprintf("%u", $longip); // fix 32 位 OS 下溢出的问题
$useragent = _SERVER('HTTP_USER_AGENT');

// 语言包变量
!isset($lang) AND $lang = array();

// $_SERVER['REQUEST_METHOD'] === 'PUT' ? @parse_str(file_get_contents('php://input', false , null, -1 , $_SERVER['CONTENT_LENGTH']), $_PUT) : $_PUT = array(); // 不需要支持 PUT
$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(trim($_SERVER['HTTP_X_REQUESTED_WITH'])) == 'xmlhttprequest';
$method = $_SERVER['REQUEST_METHOD'];

// 全局的错误，非多线程下很方便。
$errno = 0;
$errstr = '';

// error_handle
// register_shutdown_function('xn_shutdown_handle');
set_error_handler('error_handle', -1);
empty($conf['timezone']) AND $conf['timezone'] = 'Asia/Shanghai';
date_default_timezone_set($conf['timezone']);

// 超级全局变量
$_GET += xn_init_query_string();
$_REQUEST = array_merge($_COOKIE, $_POST, $_GET);

// 初始化 db cache，这里并没有连接，在获取数据的时候会自动连接。
$db = !empty($conf['db']) ? db_new($conf['db']) : NULL;
$db AND $db->errno AND xn_message(-1, $db->errstr); // 安装的时候检测过了，不必每次都检测。但是要考虑环境移植。

$conf['cache']['mysql']['db'] = $db; // 这里直接传 $db，复用 $db；如果传配置文件，会产生新链接。
$cache = !empty($conf['cache']) ? cache_new($conf['cache']) : NULL;
unset($conf['cache']['mysql']['db']); // 用完清除，防止保存到配置文件
!$cache AND $errno AND xn_message(-1, $errstr);

?>