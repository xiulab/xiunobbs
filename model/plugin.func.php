<?php

 // 本地插件
//$plugin_srcfiles = array();
$plugin_paths = array();
$plugins = array(); // 跟官方插件合并

// todo: 对路径进行处理 include _include(APP_PATH.'view/htm/header.inc.htm');
$g_include_slot_kv = array();
function _include($srcfile) {
	global $conf;
	// 合并插件，存入 tmp_path
	$len = strlen(APP_PATH);
	$tmpfile = $conf['tmp_path'].substr(str_replace('/', '_', $srcfile), $len);
	if(!is_file($tmpfile) || DEBUG > 1) {
		// 开始编译
		$s = plugin_compile_srcfile($srcfile);

		// 支持 <template> <slot>
		$g_include_slot_kv = array();
		for($i = 0; $i < 10; $i++) {
			$s = preg_replace_callback('#<template\sinclude="(.*?)">(.*?)</template>#is', '_include_callback_1', $s);
			if(strpos($s, '<template') === FALSE) break;
		}
		file_put_contents_try($tmpfile, $s);

		$s = plugin_compile_srcfile($tmpfile);
		file_put_contents_try($tmpfile, $s);

	}
	return $tmpfile;
}

function _include_callback_1($m) {
	global $g_include_slot_kv;
	$r = file_get_contents($m[1]);
	preg_match_all('#<slot\sname="(.*?)">(.*?)</slot>#is', $m[2], $m2);
	if(!empty($m2[1])) {
		$kv = array_combine($m2[1], $m2[2]);
		$g_include_slot_kv += $kv;
		foreach($g_include_slot_kv as $slot=>$content) {
			$r = preg_replace('#<slot\sname="'.$slot.'"\s*/>#is', $content, $r);
		}
	}
	return $r;
}

// 在安装、卸载插件的时候，需要先初始化
function plugin_init() {
	global $plugin_paths, $plugins;

	$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
	if(is_array($plugin_paths)) {
		foreach($plugin_paths as $path) {
			$dir = file_name($path);
			$conffile = $path."/conf.json";
			if(!is_file($conffile)) continue;
			$arr = xn_json_decode(file_get_contents($conffile));
			if(empty($arr)) continue;
			$plugins[$dir] = $arr;

			// 额外的信息
			$plugins[$dir]['hooks'] = array();
			$hookpaths = glob(APP_PATH."plugin/$dir/hook/*.*"); // path
			if(is_array($hookpaths)) {
				foreach($hookpaths as $hookpath) {
					$hookname = file_name($hookpath);
					$plugins[$dir]['hooks'][$hookname] = $hookpath;
				}
			}

			// 本地 + 线上数据
			$plugins[$dir] = plugin_read_by_dir($dir);
		}
	}
}

// 插件依赖检测，返回依赖的插件列表，如果返回为空则表示不依赖
/*
	返回依赖的插件数组：
	array(
		'xn_ad'=>'1.0',
		'xn_umeditor'=>'1.0',
	);
*/
function plugin_dependencies($dir) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$plugin = $plugins[$dir];
	$dependencies = $plugin['dependencies'];

	// 检查插件依赖关系
	$arr = array();
	foreach($dependencies as $_dir=>$version) {
		if(!isset($plugins[$_dir]) || !$plugins[$_dir]['enable']) {
			$arr[$_dir] = $version;
		}
	}
	return $arr;
}

/*
	返回被依赖的插件数组：
	array(
		'xn_ad'=>'1.0',
		'xn_umeditor'=>'1.0',
	);
*/
function plugin_by_dependencies($dir) {
	global $plugins;

	$arr = array();
	foreach($plugins as $_dir=>$plugin) {
		if(isset($plugin['dependencies'][$dir]) && $plugin['enable']) {
			$arr[$_dir] = $plugin['version'];
		}
	}
	return $arr;
}

function plugin_enable($dir) {
	global $plugins;

	if(!isset($plugins[$dir])) {
		return FALSE;
	}

	$plugins[$dir]['enable'] = 1;

	//plugin_overwrite($dir, 'install');
	//plugin_hook($dir, 'install');

	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('enable'=>1), TRUE);

	plugin_clear_tmp_dir();

	return TRUE;
}

// 清空插件的临时目录
function plugin_clear_tmp_dir() {
	global $conf;
	rmdir_recusive($conf['tmp_path'], TRUE);
	xn_unlink($conf['tmp_path'].'model.min.php');
}

function plugin_disable($dir) {
	global $plugins;

	if(!isset($plugins[$dir])) {
		return FALSE;
	}

	$plugins[$dir]['enable'] = 0;

	//plugin_overwrite($dir, 'unstall');
	//plugin_hook($dir, 'unstall');

	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('enable'=>0), TRUE);

	plugin_clear_tmp_dir();

	return TRUE;
}

// 安装所有的本地插件
function plugin_install_all() {
	global $plugins;

	// 检查文件更新
	foreach ($plugins as $dir=>$plugin) {
		plugin_install($dir);
	}
}

// 卸载所有的本地插件
function plugin_unstall_all() {
	global $plugins;

	// 检查文件更新
	foreach ($plugins as $dir=>$plugin) {
		plugin_unstall($dir);
	}
}
/*
	插件安装：
		把所有的插件点合并，重新写入文件。如果没有备份文件，则备份一份。
		插件名可以为源文件名：view/header.htm
*/
function plugin_install($dir) {
	global $plugins, $conf;

	if(!isset($plugins[$dir])) {
		return FALSE;
	}

	$plugins[$dir]['installed'] = 1;
	$plugins[$dir]['enable'] = 1;

	// 1. 直接覆盖的方式
	//plugin_overwrite($dir, 'install');

	// 2. 钩子的方式
	//plugin_hook($dir, 'install');

	// 写入配置文件
	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('installed'=>1, 'enable'=>1), TRUE);

	plugin_clear_tmp_dir();

	return TRUE;
}

// copy from plugin_install 修改
function plugin_unstall($dir) {
	global $plugins;

	if(!isset($plugins[$dir])) {
		return TRUE;
	}

	$plugins[$dir]['installed'] = 0;
	$plugins[$dir]['enable'] = 0;

	// 1. 直接覆盖的方式
	//plugin_overwrite($dir, 'unstall');

	// 2. 钩子的方式
	//plugin_hook($dir, 'unstall');

	// 写入配置文件
	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('installed'=>0, 'enable'=>0), TRUE);

	plugin_clear_tmp_dir();

	return TRUE;
}

function plugin_paths_enabled() {
	static $return_paths;
	if(empty($return_paths)) {
		$return_paths = array();
		$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
		if(empty($plugin_paths)) return array();
		foreach($plugin_paths as $path) {
			$conffile = $path."/conf.json";
			if(!is_file($conffile)) continue;
			$pconf = xn_json_decode(file_get_contents($conffile));
			if(empty($pconf)) continue;
			if(empty($pconf['enable']) || empty($pconf['installed'])) continue;
			$return_paths[$path] = $pconf;
		}
	}
	return $return_paths;
}

// 编译源文件，把插件合并到该文件，不需要递归，执行的过程中 include _include() 自动会递归。
function plugin_compile_srcfile($srcfile) {
	global $conf;
	// 判断是否开启插件
	if(!empty($conf['disabled_plugin'])) {
		$s = file_get_contents($srcfile);
		return $s;
	}

	// 如果有 overwrite，则用 overwrite 替换掉
	$srcfile = plugin_find_overwrite($srcfile);
	$s = file_get_contents($srcfile);

	// 最多支持 10 层
	for($i = 0; $i < 10; $i++) {
		if(strpos($s, '<!--{hook') !== FALSE || strpos($s, '// hook') !== FALSE) {
			$s = preg_replace('#<!--{hook\s+(.*?)}-->#', '// hook \\1', $s);
			$s = preg_replace_callback('#//\s*hook\s+(\S+)#is', 'plugin_compile_srcfile_callback', $s);
		} else {
			break;
		}
	}
	return $s;
}


// 只返回一个权重最高的文件名
function plugin_find_overwrite($srcfile) {
	//$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);

	$plugin_paths = plugin_paths_enabled();

	$len = strlen(APP_PATH);
	/*
	// 如果发现插件目录，则尝试去掉插件目录前缀，避免新建的 overwrite 目录过深。
	if(strpos($srcfile, '/plugin/') !== FALSE) {
		preg_match('#'.preg_quote(APP_PATH).'plugin/\w+/#i', $srcfile, $m);
		if(!empty($m[0])) {
			$len = strlen($m[0]);
		}
	}*/

	$returnfile = $srcfile;
	$maxrank = 0;
	foreach($plugin_paths as $path=>$pconf) {

		// 文件路径后半部分
		$dir = file_name($path);
		$filepath_half = substr($srcfile, $len);
		$overwrite_file = APP_PATH."plugin/$dir/overwrite/$filepath_half";
		if(is_file($overwrite_file)) {
			$rank = isset($pconf['overwrites_rank'][$filepath_half]) ? $pconf['overwrites_rank'][$filepath_half] : 0;
			if($rank >= $maxrank) {
				$returnfile = $overwrite_file;
				$maxrank = $rank;
			}
		}
	}
	return $returnfile;
}

function plugin_compile_srcfile_callback($m) {
	static $hooks;
	if(empty($hooks)) {
		$hooks = array();
		$plugin_paths = plugin_paths_enabled();

		//$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
		foreach($plugin_paths as $path=>$pconf) {
			$dir = file_name($path);
			$hookpaths = glob(APP_PATH."plugin/$dir/hook/*.*"); // path
			if(is_array($hookpaths)) {
				foreach($hookpaths as $hookpath) {
					$hookname = file_name($hookpath);
					$rank = isset($pconf['hooks_rank']["$hookname"]) ? $pconf['hooks_rank']["$hookname"] : 0;
					$hooks[$hookname][] = array('hookpath'=>$hookpath, 'rank'=>$rank);
				}
			}
		}
		foreach ($hooks as $hookname=>$arrlist) {
			$arrlist = arrlist_multisort($arrlist, 'rank', FALSE);
			$hooks[$hookname] = arrlist_values($arrlist, 'hookpath');
		}

	}

	$s = '';
	$hookname = $m[1];
	if(!empty($hooks[$hookname])) {
		$fileext = file_ext($hookname);
		foreach($hooks[$hookname] as $path) {
			$t = file_get_contents($path);
			if($fileext == 'php' && preg_match('#^\s*<\?php\s+exit;#is', $t)) {
				// 正则表达式去除兼容性比较好。
				$t = preg_replace('#^\s*<\?php\s*exit;(.*?)(?:\?>)?\s*$#is', '\\1', $t);

				/* 去掉首尾标签
				if(substr($t, 0, 5) == '<?php' && substr($t, -2, 2) == '?>') {
					$t = substr($t, 5, -2);
				}
				// 去掉 exit;
				$t = preg_replace('#\s*exit;\s*#', "\r\n", $t);
				*/
			}
			$s .= $t;
		}
	}
	return $s;
}

// -------------------> 本地插件列表缓存到本地。
// 安装，卸载，禁用，更新
function plugin_read_by_dir($dir) {
	global $plugins;

	$local = array_value($plugins, $dir, array());
	if(empty($local)) return array();

	// 本地插件信息
	//!isset($plugin['dir']) && $plugin['dir'] = '';
	!isset($local['name']) && $local['name'] = '';
	!isset($local['price']) && $local['price'] = 0;
	!isset($local['brief']) && $local['brief'] = '';
	!isset($local['version']) && $local['version'] = '1.0';
	!isset($local['bbs_version']) && $local['bbs_version'] = '4.0';
	!isset($local['installed']) && $local['installed'] = 0;
	!isset($local['enable']) && $local['enable'] = 0;
	!isset($local['hooks']) && $local['hooks'] = array();
	!isset($local['hooks_rank']) && $local['hooks_rank'] = array();
	!isset($local['dependencies']) && $local['dependencies'] = array();
	!isset($local['icon_url']) && $local['icon_url'] = '';
	!isset($local['have_setting']) && $local['have_setting'] = 0;
	!isset($local['setting_url']) && $local['setting_url'] = 0;
	!isset($local['pluginid']) && $local['pluginid'] = 0;

	$plugin = $local;
	// 额外的判断
	$plugin['icon_url'] = "../plugin/$dir/icon.png";
	$plugin['setting_url'] = $plugin['installed'] && is_file("../plugin/$dir/setting.php") ? "plugin-setting-$dir.htm" : "";
	$plugin['downloaded'] = isset($plugins[$dir]);
	$plugin['stars_fmt'] = $plugin['pluginid'] ? str_repeat('<span class="icon star"></span>', $plugin['stars']) : '';
	$plugin['user_stars_fmt'] = $plugin['pluginid'] ? str_repeat('<span class="icon star"></span>', $plugin['user_stars']) : '';
	$plugin['is_cert_fmt'] = empty($plugin['is_cert']) ? '<span class="text-danger">'.lang('no').'</span>' : '<span class="text-success">'.lang('yes').'</span>';
	$plugin['have_upgrade'] = false;
	$plugin['official_version'] = $local['version']; // 官方版本
	$plugin['img1_url'] = ''; // 官方版本
	$plugin['img2_url'] = ''; // 官方版本
	$plugin['img3_url'] = ''; // 官方版本
	$plugin['img4_url'] = ''; // 官方版本
	return $plugin;
}

function plugin_siteid() {
	global $conf;
	$auth_key = $conf['auth_key'];
	$siteip = _SERVER('SERVER_ADDR');
	$siteid = md5($auth_key.$siteip);
	return $siteid;
}
