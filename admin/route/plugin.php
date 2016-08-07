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
	
	$header['title']    = lang('local_plugin');
	$header['mobile_title'] = lang('local_plugin');
	
	//print_r($plugins);exit;
	
	include "./view/htm/plugin_list.htm";

// 分类，分页
} elseif($action == 'official') {

	$cateid = param(2, 0);
	$page = param(3, 1);
	$pagesize = 10;
	$cond = $cateid ? array('cateid'=>$cateid) : array();
			
	// 插件分类
	$pugin_cates = array(0=>lang('pugin_cate_0'), 1=>lang('pugin_cate_1'), 2=>lang('pugin_cate_2'), 3=>lang('pugin_cate_3'), 4=>lang('pugin_cate_4'), 99=>lang('pugin_cate_99'));

	$pugin_cate_html = plugin_cate_active($pugin_cates, $cateid, $page);
	
	// 线上插件
	$total = plugin_official_total($cond);
	$pluginlist = plugin_official_list($cond, array('pluginid'=>-1), $page, $pagesize);
	$pagination = pagination(url("plugin-official-$cateid-{page}"), $total, $page, $pagesize);
	
	$header['title']    = lang('official_plugin');
	$header['mobile_title'] = lang('official_plugin');
	
//	print_r($pluginlist);exit;
	
	include "./view/htm/plugin_list.htm";
	
} elseif($action == 'read') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	
	$plugin = plugin_read($dir);
	
	$tab = $plugin['pluginid'] ? 'official' : 'local';
	
	$header['title']    = lang('plugin_detail').'-'.$plugin['name'];
	$header['mobile_title'] = $plugin['name'];
	
	include "./view/htm/plugin_read.htm";
	
// 下载官方插件。
} elseif($action == 'download') {
	
	$dir = param(2);
	plugin_check_exists($dir, FALSE);
	
	//print_r($official_plugins);exit;
	
	$official = plugin_official_read($dir);
	empty($official) AND message(-1, lang('plugin_not_exists'));
	
	// 检查版本
	if(version_compare($conf['version'], $official['bbs_version']) == -1) {
		message(-1, lang('plugin_versio_not_match', array('bbs_version'=>$official['bbs_version'], 'version'=>$conf['version'])));
	}
	
	// 下载，解压
	plugin_download_unzip($dir);
	
	// 检查解压是否成功
	message(0, jump(lang('plugin_download_sucessfully', array('dir'=>$dir)), url("plugin-read-$dir"), 2));
	
} elseif($action == 'install') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'install');
	
	// 安装插件
	plugin_install($dir);
	
	$installfile = "../plugin/$dir/install.php";
	if(is_file($installfile)) {
		include $installfile;
	}
	
	$msg = lang('plugin_install_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'unstall') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'unstall');
	
	// 卸载插件
	plugin_unstall($dir);
	
	$unstallfile = "../plugin/$dir/unstall.php";
	if(is_file($unstallfile)) {
		include $unstallfile;
	}
	
	// 删除插件
	//!DEBUG && rmdir_recusive("../plugin/$dir");
	
	$msg = lang('plugin_unstall_sucessfully', array('name'=>$name, 'dir'=>"plugin/$dir"));
	message(0, jump($msg, http_referer(), 5));
	
} elseif($action == 'enable') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'install');
	
	// 启用插件
	plugin_enable($dir);
	
	$msg = lang('plugin_enable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'disable') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'unstall');
	
	// 禁用插件
	plugin_disable($dir);
	
	$msg = lang('plugin_disable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'upgrade') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 判断插件版本
	$plugin = plugin_read($dir);
	!$plugin['have_upgrade'] AND message(-1, lang('plugin_not_need_update'));
	
	// 检查目录可写
	plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency();
	
	// 安装插件
	plugin_install($dir);
	
	$msg = lang('plugin_upgrade_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'setting') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	include "../plugin/$dir/setting.php";
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
	$msg = lang('plugin_set_relatied_dir_writable', array('dir'=>implode(', ', $dirarr)));
	!empty($dirarr) AND message(-1, $msg);
}

function plugin_check_dependency($dir, $action = 'install') {
	global $plugins;
	$name = $plugins[$dir]['name'];
	if($action == 'install') {
		$arr = plugin_dependencies($dir);
		if(!empty($arr)) {
			$s = plugin_dependency_arr_to_links($arr);
			$msg = lang('plugin_dependency_following', array('name'=>$name, 's'=>$s));
			message(-1, $msg);
		}
	} else {
		$arr = plugin_by_dependencies($dir);
		if(!empty($arr)) {
			$s = plugin_dependency_arr_to_links($arr);
			$msg = lang('plugin_being_dependent_cant_delete', array('name'=>$name, 's'=>$s));
			message(-1, $msg);
		}
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
	$app_url = xn_urlencode($app_url);
	$url = PLUGIN_OFFICIAL_URL."plugin-download-$dir-$siteid-$app_url.htm"; // $siteid 用来防止别人伪造站点，GET 不够安全，但不是太影响

	// 服务端开始下载
	set_time_limit(0); // 设置超时
	$s = http_get($url, 120);
	empty($s) AND message(-1, lang('server_response_empty')); 
	substr($s, 0, 2) != 'PK' AND message(-1, lang('server_response_error').':'.$s);
	//$arr = xn_json_decode($s);
	//empty($arr['message']) AND message(-1, '服务端返回数据错误：'.$s);
	//$arr['code'] != 0 AND message(-1, '服务端返回数据错误：'.$arr['message']);
	
	$zipfile = $conf['tmp_path'].'plugin_'.$dir.'.zip';
	$destpath = "../plugin/$dir/";
	file_put_contents($zipfile, $s);
	$files = xn_unzip($zipfile, $destpath);
	empty($files) AND message(-1, lang('zip_data_error'));
	unlink($zipfile);
	// 检查配置文件
	$conffile = "../plugin/$dir/conf.json";
	!is_file($conffile) AND message(-1, 'conf.json '.lang('not_exists'));
	$arr = xn_json_decode(file_get_contents($conffile));
	empty($arr['name']) AND message(-1, 'conf.json '.lang('format_maybe_error'));
	
	!is_dir("../plugin/$dir") AND message(-1, lang('plugin_maybe_download_failed')." plugin/$dir");
}

function plugin_check_exists($dir, $local = TRUE) {
	global $plugins, $official_plugins;
	!is_word($dir) AND message(-1, lang('plugin_name_error'));
	if($local) {
		!isset($plugins[$dir]) AND message(-1, lang('plugin_not_exists'));
	} else {
		!isset($official_plugins[$dir]) AND message(-1, lang('plugin_not_exists'));
	}
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