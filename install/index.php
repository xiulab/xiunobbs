<?php

chdir('../');

define('DEBUG', 0);
define('APP_NAME', 'install');

$conf = (@include './conf/conf.default.php');

include './xiunophp/xiunophp.php';
include './model.inc.php';
include './model/friendlink.func.php';

$browser = get__browser();
check_browser($browser);

$route = param(0, 'index');

// 安装初始化检测,放这里
is_file('./conf/conf.php') AND $route == 'index' AND !DEBUG AND message(0, jump('程序已经安装过了，如需重新安装，请删除 conf/conf.php ！', '../'));

switch($route) {
	case 'index' : include "./install/view/index.htm"; break;
	case 'step1' : include './install/route/step1.php'; break;
	case 'step2' : include './install/route/step2.php'; break;
	case 'step3' : include './install/route/step3.php'; break;
	case 'step4' : include './install/route/step4.php'; break;
	case 'error' : break;
	default: message(0, '未知操作，请确认!');
}

// 提示信息
/*
	message(0, '登录成功');
	message(1, '密码错误');
	message(-1, '数据库连接失败');
	
	code:
		< 0 全局错误，比如：系统错误：数据库丢失连接/文件不可读写
		= 0 正确
		> 0 一般业务逻辑错误，可以定位到具体控件，比如：用户名为空/密码为空
*/
function message($code, $message) {
	global $db, $cache, $starttime, $conf, $browser, $ajax, $uid, $gid, $user;
	
	if($ajax) {
		echo xn_json_encode(array('code'=>$code, 'message'=>$message));
	} else {
		$header['title'] = '提示信息';
		include "./install/view/message.htm";
		
	}
	exit;
}

?>
