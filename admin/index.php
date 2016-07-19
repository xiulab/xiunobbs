<?php

// 切换到上一级目录
chdir('../');

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs_admin');		// 应用的名称

$conf = (@include './conf/conf.php') OR exit(header('Location: install/'));
include './xiunophp/xiunophp.php';

// 测试数据库连接
db_connect($err) OR exit($err);

include './model.inc.php';

// 语言包
$lang = include('./lang/zh-cn.php');

// 用户
$uid = _SESSION('uid');
$user = user_read($uid);

// 用户组
$gid = empty($user) ? 0 : $user['gid'];
$grouplist = group_list_cache();
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : array();

// 版块
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// 有权限查看的板块

// 头部 header.inc.htm 
$header = array(
	'title'=>$conf['sitename'],
	'keywords'=>'',
	'description'=>'',
	'navs'=>array(),
);

// 运行时数据
$runtime = runtime_init();

// 检测浏览器， 不支持 IE8
$browser = get__browser();
check_browser($browser);

// 检测站点运行级别
check_runlevel();

// 检测 IP 封锁，可以作为自带插件
//check_banip($ip);

// 记录 POST 数据
//DEBUG AND xn_log_post_data();

// 全站的设置数据，站点名称，描述，关键词，页脚代码等
$setting = cache_get('setting', TRUE);

//DEBUG AND ($method == 'POST' || $ajax) AND sleep(1);

$route = param(0, 'index');

// 只允许管理员登陆后台
if($gid != 1) {
	message(-1, jump('对不起，您不是管理员，无权登陆后台。', url('user-login')));
}

// 管理员令牌检查
admin_check_token();

// todo: HHVM 不支持动态 include $filename
switch ($route) {
	case 'index':		include './admin/route/index.php'; 		break;
	case 'setting': 	include './admin/route/setting.php'; 		break;
	case 'forum': 		include './admin/route/forum.php'; 		break;
	case 'friendlink': 	include './admin/route/friendlink.php'; 	break;
	case 'group': 		include './admin/route/group.php'; 		break;
	case 'user':		include './admin/route/user.php'; 		break;
	default: http_404();
}


function admin_check_token() {
	global $longip, $time, $useragent;
	$admin_token = param('admin_token');
	if(empty($admin_token)) {
		$_REQUEST[0] = 'index';
		$_REQUEST[1] = 'login';
	} else {
		$useragent_md5 = md5($useragent);
		$key = md5($useragent_md5.$ip.$conf['auth_key']);
		$s = xn_decrypt($admin_token, $key);
		if(empty($s)) {
			setcookie('admin_token', '', 0, '', '', '', TRUE);
			message(-1, 'Token 错误');
		}
		list($_ip, $_useragent_md5, $_time) = explode("\t", $s);
		// 后台超过 3600 自动退出。
		if($_ip != $longip || $_useragent_md5 != $useragent_md5 || $time - $_time > 3600) {
			setcookie('admin_token', '', 0, '', '', '', TRUE);
			message(-1, '凭证失效，请重新登录');
		}
	}
}

function admin_set_token() {
	global $longip, $time, $useragent;
	$admin_token = param('admin_token');
	$useragent_md5 = md5($useragent);
	$key = md5($useragent_md5.$ip.$conf['auth_key']);
	$s = "$longip	$time	$useragent_md5";
	$admin_token = xn_encrypt($s, $key);
	setcookie('admin_token', $admin_token, $time + 3600, '', '', '', TRUE);
}

?>