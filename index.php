<?php


!defined('DEBUG') AND define('DEBUG', 0); 				// 发布的时候改为 0 

ob_start('ob_gzhandler');

$conf = (@include './conf/conf.php') OR exit(header('Location: install/'));

if(DEBUG) {
	include './xiunophp/xiunophp.php';
} else {
	include './xiunophp/xiunophp.min.php';
}


/*$arr = array('code'=>1, 'message'=> array("a\r\nb"));
echo json_encode($arr);
exit;*/
// 测试数据库连接
db_connect() OR message(-1, $errstr);

include './model.inc.php';

$sid = sess_start();

// 语言包
$lang = include("./lang/$conf[lang]/bbs.php");

// 支持 Token 接口（token 与 session 双重登陆机制，方便 REST 接口设计，也方便 $_SESSION 使用）
$uid = user_token_get();
empty($uid) AND $uid = intval(_SESSION('uid'));
$user = user_read($uid);

// 用户组
$gid = empty($user) ? 0 : intval($user['gid']);
$grouplist = group_list_cache();
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : $grouplist[0];

// 版块
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块
$forumarr = arrlist_key_values($forumlist_show, 'fid', 'name');

// 头部 header.inc.htm 
$header = array(
	'title'=>$conf['sitename'],
	'mobile_title'=>$conf['sitename'],
	'keywords'=>'',
	'description'=>'',
	'navs'=>array(),
);

// 运行时数据
$runtime = runtime_init();

// 检测站点运行级别
check_runlevel();

// 全站的设置数据，站点名称，描述，关键词，页脚代码等
$setting = kv_get('setting');

if(!defined('SKIP_ROUTE')) {
	$route = param(0, 'index');
	
	// 按照使用的频次排序，增加命中率，提高效率
	switch ($route) {
		case 'index': 	include './route/index.php'; 	break;
		case 'thread':	include './route/thread.php'; 	break;
		case 'forum': 	include './route/forum.php'; 	break;
		case 'user': 	include './route/user.php'; 	break;
		case 'my': 	include './route/my.php'; 	break;
		case 'attach': 	include './route/attach.php'; 	break;
		case 'search': 	include './route/search.php'; 	break;
		case 'post': 	include './route/post.php'; 	break;
		case 'mod': 	include './route/mod.php'; 	break;
		case 'test': 	include './route/test.php'; 	break;
		case 'browser': include './route/browser.php'; 	break;
		default: 
			// 为了支持插件，此处不利于编译优化
			(!is_word($route) || !is_file("./route/$route.php")) && http_404();
			include "./route/$route.php";
	}
}

?>