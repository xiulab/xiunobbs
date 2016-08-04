<?php

!defined('DEBUG') AND exit('Access Denied.');

include '../xiunophp/xn_zip.func.php';

$action = param(1);

// 初始化插件变量
plugin_init();

// 本地插件
empty($action) AND $action = 'local';

if($action == 'local') {
	
	// 本地插件
	$pluginlist = $plugins;
	
	$pagination = '';
	$pugin_cate_html = '';
	
	$header['title']    = '本地插件';
	$header['mobile_title'] = '本地插件';
	
	include "./view/htm/plugin_list.htm";

// 分类，分页
} elseif($action == 'official') {

	$cateid = param(2, 0);
	$page = param(3, 1);
	$pagesize = 10;
	$cond = $cateid ? array('cateid'=>$cateid) : array();
			
	// 插件分类
	$pugin_cates = array(0=>'所有插件', 1=>'风格模板', 2=>'小型插件', 3=>'大型插件', 4=>'接口整合', 99=>'未分类');

	$pugin_cate_html = plugin_cate_active($pugin_cates, $cateid, $page);
	
	// 线上插件
	$total = plugin_official_total($cond);
	$pluginlist = plugin_official_list($cond, array('pluginid'=>-1), $page, $pagesize);
	$pagination = pagination(url('plugin-official-$cateid-{page}'), $total, $page, $pagesize);
	
	$header['title']    = '官方插件';
	$header['mobile_title'] = '官方插件';
	
	include "./view/htm/plugin_list.htm";
	
} elseif($action == 'read') {
	
	$dir = param(2);
	$plugin = plugin_read($dir);
	empty($plugin) AND message(-1, '插件不存在');
	
	$tab = $plugin['plugind'] ? 'official' : 'local';
	
	$header['title']    = '插件详情-'.$plugin['name'];
	$header['mobile_title'] = '插件详情-'.$plugin['name'];
	
	include "./view/plugin_read.htm";
	
// 下载官方插件。
} elseif($action == 'download') {
	
	$dir = param(2);
	!preg_match('#^\w+$#', $dir) AND message(-1, 'dir 不合法。');
	
	$official = plugin_official_read($dir);
	empty($official) AND message(-1, '插件不存在');
	
	// 检查版本
	if(version_compare($conf['version'], $official['bbs_version']) == -1) {
		message(-1, "此插件依赖的 Xiuno BBS 最低版本为 $official[bbs_version] ，您当前的版本：".$conf['version']);
	}
	
	// 下载，解压
	plugin_download_unzip($dir);
	
	// 检查解压是否成功
	if(!is_dir("../plugin/$dir")) {
		message(-1, "插件可能下载失败，目录不存在: plugin/$dir");
	} else {
		message(0, jump('插件下载成功:'.$destpath.", ，请点击进行安装", url("plugin-read-$dir"), 2));
	}
	
} elseif($action == 'install') {
	
	$dir = param(2);
	
	// 检查目录可写
	plugin_dir_check_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency();
	
	// 安装插件
	plugin_install($dir);
	
	message(0, jump('安装成功', http_referer(), 1));
	
} elseif($action == 'unstall') {
	
	$dir = param(2);
	
	// 检查目录可写
	plugin_dir_check_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency();
	
	// 卸载插件
	plugin_unstall($dir);
	
	message(0, jump('卸载成功', http_referer(), 1));
	
} elseif($action == 'enable') {
	
	$dir = param(2);
	
	// 检查目录可写
	plugin_dir_check_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency();
	
	// 启用插件
	plugin_enable($dir);
	
	message(0, jump('卸载成功', http_referer(), 1));
	
} elseif($action == 'disable') {
	
	$dir = param(2);
	
	// 检查目录可写
	plugin_dir_check_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency();
	
	// 禁用插件
	plugin_disable($dir);
	
	message(0, jump('卸载成功', http_referer(), 1));
	
} elseif($action == 'upgrade') {
	
	$dir = param(2);
	
	// 检查目录可写
	plugin_dir_check_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency();
	
	// 安装插件
	plugin_install($dir);
	
	message(0, jump('卸载成功', http_referer(), 1));
	
}


	

// 检查目录是否可写，插件要求 model view admin 目录文件可写。
function plugin_check_dir_is_writable() {
	// 检测目录和文件可写
	$dirs = array('../model', '../plugin', '../view', '../view/js', '../view/htm', '../view/css', '../plugin', '../admin', '../admin/route', '../admin/view/htm');
	$dirarr = array();
	foreach($dirs as $dir) {
		if(!xn_is_writable($dir)) {
			$dirarr[] = $dir;
		}
	}
	message(-1, '在安装插件目录期间，请设置： '.implode(', ', $dirarr).' 和文件为可写。');
}

function plugin_check_dependency($dir) {
	$arr = plugin_dependency_arr_to_links($dir);
	if(!empty($arr)) {
		$s = plugin_dependency_arr_to_links($arr);
		message(-1, $s);
	}
}

function plugin_dependency_arr_to_links($dir) {
	global $plugins;
	$s = '';
	foreach($arr as $dir) {
		if(!isset($plugins[$dir])) continue;
		$name = $plugins[$dir]['name'];
		$url = url("plugin-read-$dir");
		$s .= " <a href=\"$url\">【{$name}】</a> ";
	}
	return $s;
}


// 下载插件、解压
function plugin_download_unzip($dir) {
	global $conf;
	$app_url = http_url_path();
	$siteid =  md5($app_url.$conf['auth_key']);
	$app_url = urlencode($app_url);
	$url = "http://plugin.xiuno.com/plugin-down-dir-$dir-siteid-$siteid-ajax-1.htm?app_url=$app_url";

	// 服务端开始下载
	$s = http_get($url, 60);
	if(empty($s) || substr($s, 0, 2) != 'PK') {
		$arr = xn_json_decode($s);
		empty($arr['message']) && $arr['message'] = '';
		message(-1, '服务端返回数据错误：'.$arr['message']);
	}
	$tmppath = $conf['tmp_path'];
	$zipfile = $tmppath.$dir.'.zip';
	$destpath = "../plugin/$dir/";
	file_put_contents($zipfile, $s);
	xn_unzip($zipfile, $destpath);
	unlink($zipfile);
}



// bootstrap style
function plugin_cate_active($plugin_cate, $cateid, $page) {
	$s = '';
	foreach ($plugin_cate as $_cateid=>$_catename) {
		$url = url("plugin-official-$_cateid-$page");
		$s .= '<a role="button" class="btn btn btn-secondary'.($cateid == $_cateid ? ' active' : '').'" href="'.$url.'">'.$_catename.'</a>';
	}
	return $s;
}


?>