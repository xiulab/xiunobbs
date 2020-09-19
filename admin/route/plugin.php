<?php

!defined('DEBUG') AND exit('Access Denied.');

include XIUNOPHP_PATH.'xn_zip.func.php';

$action = param(1);

// 初始化插件变量 / init plugin var
plugin_init();

// 插件依赖的环境检查
plugin_env_check();

empty($action) AND $action = 'local';

// 修正，使代码不标红
$plugins = isset($plugins) ? $plugins : [];
$conf = isset($conf) ? $conf : [];

if($action === 'local') {
	// 本地插件 local plugin list
	$pluginlist = $plugins;

	$pagination = '';
	$pugin_cate_html = '';

	$header['title']    = lang('local_plugin');
	$header['mobile_title'] = lang('local_plugin');

	include _include(ADMIN_PATH."view/htm/plugin_list.htm");

} elseif($action === 'install') {

	plugin_lock_start();

	$dir = param_word(2);
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
		include _include($installfile);
	}

	plugin_lock_end();

	// 卸载同类插件，防止安装类似插件。
	// 自动卸载掉其他已经安装的主题 / automatically unstall other theme plugin.
	if(strpos($dir, '_theme_') !== FALSE) {
		foreach($plugins as $_dir => $_plugin) {
			if($dir == $_dir) continue;
			if(strpos($_dir, '_theme_') !== FALSE) {
				plugin_unstall($_dir);
			}
		}
	} else {
		// 卸载掉同类插件
		$suffix = substr($dir, strpos($dir, '_'));
		foreach($plugins as $_dir => $_plugin) {
			if($dir == $_dir) continue;
			$_suffix = substr($_dir, strpos($_dir, '_'));
			if($suffix == $_suffix) {
				plugin_unstall($_dir);
			}
		}
	}

	$msg = lang('plugin_install_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));

} elseif($action === 'unstall') {

	plugin_lock_start();

	$dir = param_word(2);
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
		include _include($unstallfile);
	}

	// 删除插件
	//!DEBUG && rmdir_recusive("../plugin/$dir");

	plugin_lock_end();

	$msg = lang('plugin_unstall_sucessfully', array('name'=>$name, 'dir'=>"plugin/$dir"));
	message(0, jump($msg, http_referer(), 5));

} elseif($action === 'enable') {

	plugin_lock_start();

	$dir = param_word(2);
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

} elseif($action === 'disable') {

	plugin_lock_start();

	$dir = param_word(2);
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

} elseif($action === 'setting') {

	$dir = param_word(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];

	include _include(APP_PATH."plugin/$dir/setting.php");
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
	if($action === 'install') {
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

function plugin_is_local($dir) {
	global $plugins;
	return isset($plugins[$dir]) ? TRUE : FALSE;
}

function plugin_check_exists($dir, $local = TRUE) {
	global $plugins;
	!is_word($dir) AND message(-1, lang('plugin_name_error'));
	!isset($plugins[$dir]) AND message(-1, lang('plugin_not_exists'));
}

// bootstrap style
function plugin_cate_active($action, $plugin_cate, $cateid, $page) {
	$s = '';
	foreach ($plugin_cate as $_cateid=>$_catename) {
		$url = url("plugin-$action-$_cateid-$page");
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
