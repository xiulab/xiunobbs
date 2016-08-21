<?php

/*
 * Copyright (C) xiuno.com
 */

//$_SERVER['REQUEST_URI'] = '/?user-login.htm';
//$_SERVER['REQUEST_METHOD'] = 'POST';
//$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
//$_COOKIE['bbs_sid'] = 'e1d8c2790b9dd08267e6ea2595c3bc82';
//$postdata = 'email=admin&password=c4ca4238a0b923820dcc509a6f75849b';
//parse_str($postdata, $_POST);

// 0: Production mode; 1: Developer mode; 2: Developer Plugin mode;
!defined('DEBUG') AND define('DEBUG', 2);
define('APP_PATH', dirname(__FILE__).'/'); // __DIR__
!defined('ADMIN_PATH') AND define('ADMIN_PATH', APP_PATH.'admin/');
!defined('XIUNOPHP_PATH') AND define('XIUNOPHP_PATH', APP_PATH.'xiunophp/');

// !ini_get('zlib.output_compression') AND ob_start('ob_gzhandler');

ob_start('ob_gzhandler');
$conf = (@include APP_PATH.'conf/conf.php') OR exit(header('Location: install/'));

// 转换为绝对路径，防止被包含时出错。
substr($conf['log_path'], 0, 2) == './' AND $conf['log_path'] = APP_PATH.$conf['log_path']; 
substr($conf['tmp_path'], 0, 2) == './' AND $conf['tmp_path'] = APP_PATH.$conf['tmp_path']; 
substr($conf['upload_path'], 0, 2) == './' AND $conf['upload_path'] = APP_PATH.$conf['upload_path']; 

if(DEBUG) {
	include XIUNOPHP_PATH.'xiunophp.php';
} else {
	include XIUNOPHP_PATH.'xiunophp.min.php';
}

// 测试数据库连接 / try to connect database
db_connect() OR exit($errstr);

include APP_PATH.'model/plugin.func.php';
include APP_PATH.'model/misc.func.php';
include _include(APP_PATH.'model.inc.php');

// 查找所有开启的插件，合并 hook file 内容
	
if(DEBUG == 2) {
	xn_mkdir($conf['tmp_path'].'src');
	//plugin_init();
	//plugin_unstall_all();
}
$sid = sess_start();

// 语言 / Language
$lang = include(APP_PATH."lang/$conf[lang]/bbs.php");


// 支持 Token 接口（token 与 session 双重登陆机制，方便 REST 接口设计，也方便 $_SESSION 使用）
// Support Token interface (token and session dual landing mechanism, to facilitate the design of the REST interface, but also to facilitate the use of $_SESSION)
$uid = user_token_get();
empty($uid) AND $uid = intval(_SESSION('uid'));
$user = user_read($uid);

// 用户组 / Group
$gid = empty($user) ? 0 : intval($user['gid']);
$grouplist = group_list_cache();
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : $grouplist[0];

// 版块 / Forum
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块 / filter no permission forum
$forumarr = arrlist_key_values($forumlist_show, 'fid', 'name');

// 头部 header.inc.htm 
$header = array(
	'title'=>$conf['sitename'],
	'mobile_title'=>'',
	'mobile_link'=>'./',
	'keywords'=>'', // 搜索引擎自行分析 keywords, 自己指定没用 / Search engine automatic analysis of key words, their own designation is not used
	'description'=>$conf['sitebrief'],
	'navs'=>array(),
);

// 运行时数据，存放于 cache_set() / runteime data
$runtime = runtime_init();

// 默认为 NULL，全局设置，存放于 kv_cache_set()
$setting = FALSE;

// 检测站点运行级别 / restricted access
check_runlevel();

// 全站的设置数据，站点名称，描述，关键词
// $setting = kv_get('setting');

$route = param(0, 'index');

if(!defined('SKIP_ROUTE')) {
	
	// 按照使用的频次排序，增加命中率，提高效率
	// According to the frequency of the use of sorting, increase the hit rate, improve efficiency
	switch ($route) {
		case 'index': 	include _include(APP_PATH.'route/index.php'); 	break;
		case 'thread':	include _include(APP_PATH.'route/thread.php'); 	break;
		case 'forum': 	include _include(APP_PATH.'route/forum.php'); 	break;
		case 'user': 	include _include(APP_PATH.'route/user.php'); 	break;
		case 'my': 	include _include(APP_PATH.'route/my.php'); 	break;
		case 'attach': 	include _include(APP_PATH.'route/attach.php'); 	break;
		case 'search': 	include _include(APP_PATH.'route/search.php'); 	break;
		case 'post': 	include _include(APP_PATH.'route/post.php'); 	break;
		case 'mod': 	include _include(APP_PATH.'route/mod.php'); 	break;
		case 'browser': include _include(APP_PATH.'route/browser.php'); 	break;
		default: 
			// 为了支持插件，此处不利于编译优化
			// In order to support / plug-in, here is not conducive to compiler optimization
			(!is_word($route) || !is_file(APP_PATH."route/$route.php")) && http_404();
			include _include(APP_PATH."route/$route.php");
	}
}

?>