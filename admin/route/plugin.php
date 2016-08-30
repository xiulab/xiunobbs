<?php

!defined('DEBUG') AND exit('Access Denied.');

include XIUNOPHP_PATH.'xn_zip.func.php';

$action = param(1);

// 初始化插件变量 / init plugin var
plugin_init();

empty($action) AND $action = 'local';

if($action == 'local') {
	
	// 本地插件 local plugin list
	$pluginlist = $plugins;
	
	$pagination = '';
	$pugin_cate_html = '';
	
	$header['title']    = lang('local_plugin');
	$header['mobile_title'] = lang('local_plugin');
	
	include _include(ADMIN_PATH."view/htm/plugin_list.htm");

} elseif($action == 'official') {

	$cateid = param(2, 0);
	$page = param(3, 1);
	$pagesize = 10;
	$cond = $cateid ? array('cateid'=>$cateid) : array();
			
	// plugin category
	$pugin_cates = array(0=>lang('pugin_cate_0'), 1=>lang('pugin_cate_1'), 2=>lang('pugin_cate_2'), 3=>lang('pugin_cate_3'), 4=>lang('pugin_cate_4'), 99=>lang('pugin_cate_99'));

	$pugin_cate_html = plugin_cate_active($pugin_cates, $cateid, $page);
	
	// official plugin
	$total = plugin_official_total($cond);
	$pluginlist = plugin_official_list($cond, array('pluginid'=>-1), $page, $pagesize);
	$pagination = pagination(url("plugin-official-$cateid-{page}"), $total, $page, $pagesize);
	
	$header['title']    = lang('official_plugin');
	$header['mobile_title'] = lang('official_plugin');
	
	include _include(ADMIN_PATH."view/htm/plugin_list.htm");
	
} elseif($action == 'read') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	
	$plugin = plugin_read($dir);
	
	$tab = $plugin['pluginid'] ? 'official' : 'local';
	
	$header['title']    = lang('plugin_detail').'-'.$plugin['name'];
	$header['mobile_title'] = $plugin['name'];
	
	include _include(ADMIN_PATH."view/htm/plugin_read.htm");
	
// 下载官方插件。 / download official plugin
} elseif($action == 'download') {
	
	plugin_lock_start();
	
	$dir = param(2);
	plugin_check_exists($dir, FALSE);
	
	//print_r($official_plugins);exit;
	
	$official = plugin_official_read($dir);
	empty($official) AND message(-1, lang('plugin_not_exists'));
	
	// 检查版本  / check version match
	if(version_compare($conf['version'], $official['bbs_version']) == -1) {
		message(-1, lang('plugin_versio_not_match', array('bbs_version'=>$official['bbs_version'], 'version'=>$conf['version'])));
	}
	
	// 下载，解压 / download and zip
	plugin_download_unzip($dir);
	
	plugin_lock_end();
	
	// 检查解压是否成功 / check the zip if sucess
	message(0, jump(lang('plugin_download_sucessfully', array('dir'=>$dir)), url("plugin-read-$dir"), 3));
	
} elseif($action == 'install') {
	
	plugin_lock_start();
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写 / check directory writable
	//plugin_check_dir_is_writable();
	
	// 插件依赖检查 / check plugin dependency
	plugin_check_dependency($dir, 'install');
	
	// 安装插件 / install plugin
	plugin_install($dir);
	
	$installfile = APP_PATH."plugin/$dir/install.php";
	if(is_file($installfile)) {
		include $installfile;
	}
	
	plugin_lock_end();
	
	$msg = lang('plugin_install_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'unstall') {
	
	plugin_lock_start();
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	// plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'unstall');
	
	// 卸载插件
	plugin_unstall($dir);
	
	$unstallfile = APP_PATH."plugin/$dir/unstall.php";
	if(is_file($unstallfile)) {
		include $unstallfile;
	}
	
	// 删除插件
	//!DEBUG && rmdir_recusive("../plugin/$dir");
	
	plugin_lock_end();
	
	$msg = lang('plugin_unstall_sucessfully', array('name'=>$name, 'dir'=>"plugin/$dir"));
	message(0, jump($msg, http_referer(), 5));
	
} elseif($action == 'enable') {
	
	plugin_lock_start();
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	//plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'install');
	
	// 启用插件
	plugin_enable($dir);
	
	plugin_lock_end();
	
	$msg = lang('plugin_enable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'disable') {
	
	plugin_lock_start();
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 检查目录可写
	//plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'unstall');
	
	// 禁用插件
	plugin_disable($dir);
	
	plugin_lock_end();
	
	$msg = lang('plugin_disable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'upgrade') {
	
	plugin_lock_start();
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// 判断插件版本
	$plugin = plugin_read($dir);
	!$plugin['have_upgrade'] AND message(-1, lang('plugin_not_need_update'));
	
	// 检查目录可写
	//plugin_check_dir_is_writable();
	
	// 插件依赖检查
	plugin_check_dependency($dir, 'install');
	
	
	
	// copy from $action == "download"
	$official = plugin_official_read($dir);
	empty($official) AND message(-1, lang('plugin_not_exists'));
	
	// 检查版本  / check version match
	if(version_compare($conf['version'], $official['bbs_version']) == -1) {
		message(-1, lang('plugin_versio_not_match', array('bbs_version'=>$official['bbs_version'], 'version'=>$conf['version'])));
	}
	
	// 下载，解压 / download and zip
	plugin_download_unzip($dir);
	// copy end
	
	
	
	// 安装插件
	plugin_install($dir);
	
	plugin_lock_end();
	
	$msg = lang('plugin_upgrade_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'setting') {
	
	$dir = param(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	include APP_PATH."plugin/$dir/setting.php";
}


	

// 检查目录是否可写，插件要求 model view admin 目录文件可写。
/*
function plugin_check_dir_is_writable() {
	// 检测目录和文件可写
	$dirs = array(
		APP_PATH.'model', 
		APP_PATH.'plugin', 
		APP_PATH.'view', 
		APP_PATH.'route', 
		APP_PATH.'view/js', 
		APP_PATH.'view/htm', 
		APP_PATH.'view/css', 
		APP_PATH.'plugin', 
		ADMIN_PATH.'route', 
		ADMIN_PATH.'view/htm');
	$dirarr = array();
	foreach($dirs as $dir) {
		if(!xn_is_writable($dir)) {
			$dirarr[] = $dir;
		}
	}
	$msg = lang('plugin_set_relatied_dir_writable', array('dir'=>implode(', ', $dirarr)));
	!empty($dirarr) AND message(-1, $msg);
}*/

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

function plugin_dependency_arr_to_links($arr) {
	global $plugins;
	$s = '';
	foreach($arr as $dir=>$version) {
		//if(!isset($plugins[$dir])) continue;
		$name = isset($plugins[$dir]['name']) ? $plugins[$dir]['name'] : $dir;
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

function plugin_lock_start() {
	global $route, $action;
	!xn_lock_start($route.'_'.$action) AND message(-1, lang('plugin_task_locked'));
}

function plugin_lock_end() {
	global $route, $action;
	xn_lock_end($route.'_'.$action);
}


?>