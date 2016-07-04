<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_zip.func.php';

$action = param(1);

plugin_init();

// 本地插件
if(empty($action) || $action == 'local_list') {

	$header['title']    = '插件管理';

	// 本地插件
	$pluginlist = $plugins;
	include "./admin/view/plugin_local_list.htm";

} elseif($action == 'official_list') {
	
	$pagesize = 10;
	$page = param(2, 1);
	
	// 线上插件
	$pluginlist = plugin_official_list(array(), array(), $page, $pagesize);
	$total = plugin_official_total();
	$pages = pages('admin/plugin-official_list-{page}.htm', $total, $page, $pagesize);
	include "./admin/view/plugin_official_list.htm";
	
} elseif($action == 'read') {
	
	$dir = param(2);
	$plugin = plugin_read($dir);
	empty($plugin) AND message(-1, '插件不存在');
	
	include "./admin/view/plugin_read.htm";
	
// 下载官方插件，如果为收费插件，则需要微信支付。客户端循环请求，直到支付成功。
} elseif($action == 'download') {
	
	$tmppath = $conf['tmp_path'];

	$dir = param(2);
	plugin_check_dir($dir);
	
	$official = plugin_official_read($dir);
	empty($official) AND message(-1, '插件不存在');
	
	// 检查版本
	if(version_compare($conf['version'], $official['bbs_version']) == -1) {
		message(-1, "此插件依赖的 Xiuno BBS 最低版本为 $official[bbs_version] ，您当前的版本：".$conf['version']);
	}
	
	// 下载，解压，校验
	$app_url = http_url_path();
	$siteid =  md5($app_url.$conf['auth_key']);
	$app_url = urlencode($app_url);
	$url = "http://plugin.xiuno.com/plugin-down-dir-$dir-siteid-$siteid-ajax-1.htm?app_url=$app_url";

	// 服务端开始下载，超时为 60 秒。
	$timeout = intval(ini_get('max_execution_time'));
	empty($timeout) AND $timeout = 60;
	$s = http_get($url, $timeout);
	if(empty($s) || substr($s, 0, 2) != 'PK') {
		$arr = xn_json_decode($s);
		empty($arr) AND message(-1, '服务端返回数据错误:'.$s);
		// code == 100 为收费插件
		message($arr['code'], $arr['message']); // 收费插件
	}
	$zipfile = $tmppath.$dir.'.zip';
	$destpath = "./plugin/$dir/";
	file_put_contents($zipfile, $s);
	xn_unzip($zipfile, $destpath);
	unlink($zipfile);
	
	if(!is_dir("./plugin/$dir")) {
		message(-1, "插件可能下载失败，目录不存在: plugin/$dir");
	} else {
		message(0, '插件下载解压成功:'.$destpath);
	}
	
// 安装插件
} elseif($action == 'install') {
	
	$dir = param(2);
	$r = plugin_install($dir);
	$r !== TRUE AND message(0, '安装失败，原因：'.$r);
	if(is_file("./plugin/$dir/install.php")) {
		include "./plugin/$dir/install.php";
	}
	message(0, '安装成功。');
	
// 卸载插件
} elseif($action == 'unstall') {
	
	$dir = param(2);
	$r = plugin_unstall($dir);
	$r !== TRUE AND message(0, '卸载失败，原因：'.$r);
	if(is_file("./plugin/$dir/unstall.php")) {
		include "./plugin/$dir/unstall.php";
	}
	message(0, '卸载成功。');
	
// 设置插件
} elseif($action == 'setting') {
	
	$dir = param(2);
	if(is_file("./plugin/$dir/setting.php")) {
		include "./plugin/$dir/setting.php";
	} else {
		message(0, '该插件并不支持设置功能。');
	}
}

// 预留
function plugin_check_dir($name) {
	if(!preg_match('#^\w+$#', $name)) {
		message(-1, '插件名称只能由字母、数字、下划线组成。');
	}
}

// 远程插件列表，从官方服务器获取插件列表，全部缓存到本地，定期更新
function plugin_official_list($cond = array(), $orderby = array('stars'=>-1), $page = 1, $pagesize = 20) {
	// hook plugin_official_list_start.php
	// 服务端插件信息，缓存起来
	$offlist = plugin_official_list_cache();
	$offlist = arrlist_cond_orderby($offlist, $cond, $orderby, $page, $pagesize);
	foreach($offlist as &$plugin) plugin_official_format($plugin);
	// hook plugin_official_list_end.php
	return $offlist;
}

/*
return Array (
    [xn_clear_rubbish] => Array
        (
            [pluginid] => 102
            [name] => 清理论坛垃圾
            [brief] => 清理过期的帖子，短消息，用户，附件。
            [version] => 2.1.10
            [bbs_version] => 2.0.3
            [cateid] => 0
            [styleid] => 0
            [dir] => xn_clear_rubbish
            [icon] => 1
            [img1] => 0
            [img2] => 0
            [img3] => 0
            [img4] => 0
            [price] => 0
            [uid] => 0
            [username] => 0
            [email] => 0
            [lastupdate] => 1379242488
            [stars] => 0
            [user_stars] => 0
            [installs] => 1102
            [sells] => 0
            [file_md5] => b265a5c3696e2616ebe203bcb61ca604
            [filename] => e1719e15ae8e4a8f9ec9cc67bcfde769.zip
            [is_cert] => 1
            [is_show] => 0
        )
)
*/

function plugin_official_list_cache() {
	// hook plugin_official_list_cache_start.php
	$s = cache_get('plugin_official_list');
	if($s === NULL || DEBUG) {
		$url = "http://plugin.xiuno.com/plugin-list-version-3.htm"; // 获取所有的插件，匹配到3.0以上的。
		$s = http_get($url, 30, 3);
		if(empty($s)) {
			xn_log('从官方获取插件数据失败。');
			return FALSE;
		}
		$r = xn_json_decode($s);
		if(!empty($r['servererror']) || (!empty($r['code']) && $r['code'] != 0)) {
			xn_log("从官方获取插件数据格式不对：官方返回数据：".$s);
			return FALSE;
		}
		
		$s = !empty($r['message']) ? $r['message'] : $r;
		cache_set('plugin_official_list', $s, 3600); // 缓存时间 1 小时。
	}
	// hook plugin_official_list_cache_end.php
	return $s;
}

function plugin_official_read($dir) {
	// hook plugin_official_read_start.php
	$offlist = plugin_official_list_cache();
	$plugin = isset($offlist[$dir]) ? $offlist[$dir] : array();
	plugin_official_format($plugin);
	// hook plugin_official_read_end.php
	return $plugin;
}

function plugin_local_read($dir) {
	// hook plugin_local_read_start.php
	
	global $plugins;
	
	if(isset($plugins[$dir])) {
		$plugin = $plugins[$dir];
		$plugin['is_official'] = 0;
	} else {
		$plugin = array();
	}
	
	// hook plugin_local_read_end.php
	return $plugin;
}

function plugin_read($dir) {
	// hook plugin_read_start.php
	if(is_dir("./plugin/$dir")) {
		$plugin = plugin_local_read($dir);
	} else {
		$plugin = plugin_official_read($dir);
	}
	// hook plugin_read_end.php
	return $plugin;
}

function plugin_official_format(&$plugin) {
	// hook plugin_official_format_start.php
	if(empty($plugin)) return;
	$dir = $plugin['dir'];
	$plugin['icon_url'] = "http://plugin.xiuno.com/upload/plugin/$plugin[pluginid]/icon.png";
	$plugin['downloaded'] = is_dir("./plugin/$plugin[dir]");
	$plugin['stars_fmt'] = str_repeat('<span class="icon star"></span>', $plugin['stars']);
	$plugin['user_stars_fmt'] = str_repeat('<span class="icon star"></span>', $plugin['user_stars']);

	if(is_dir("./plugin/$dir")) {
		$arr = plugin_local_read($dir);
		$plugin = array_merge($plugin, $arr);
	} else {
		$plugin['installed'] = 0;
		$plugin['setting_url'] = '';
	}
	$plugin['is_official'] = 1;
	// hook plugin_official_format_end.php
}

?>