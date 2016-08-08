<?php

 // 本地插件
$plugin_srcfiles = array();
$plugin_paths = array();
$plugins = array(); // 跟官方插件合并

// 官方插件列表
$official_plugins = array();

define('PLUGIN_OFFICIAL_URL', 'http://plugin.xiuno.com/');
// 在安装、卸载插件的时候，需要先初始化
function plugin_init() {
	global $plugin_srcfiles, $plugin_paths, $plugins, $official_plugins;
	$plugin_srcfiles = array_merge(
		glob('../model/*.php'), 
		glob('../route/*.php'), 
		glob('../view/htm/*.*'), 
		glob('../admin/route/*.php'), 
		glob('../admin/view/htm/*.*'),
		array('../index.php', './index.php')
	);
	foreach($plugin_srcfiles as $k=>$file) {
		$filename = file_name($file);
		if(substr($filename, 0, 7) == 'backup_') {
			unset($plugin_srcfiles[$k]);
		}
	}
	$plugin_paths = glob('../plugin/*', GLOB_ONLYDIR);
	foreach($plugin_paths as $path) {
		$dir = file_name($path);
		$conffile = $path."/conf.json";
		if(!is_file($conffile)) continue;
		$arr = xn_json_decode(file_get_contents($conffile));
		if(empty($arr)) continue;
		$plugins[$dir] = $arr;
		
		// 额外的信息
		$plugins[$dir]['hooks'] = array();
		$hookpaths = glob("../plugin/$dir/hook/*.*"); // path
		foreach($hookpaths as $hookpath) {
			$hookname = file_name($hookpath);
			$plugins[$dir]['hooks'][$hookname] = $hookpath;
		}
		
		// 合并本地，线上
		$plugins[$dir] = plugin_read($dir);
	}
	
	$official_plugins = plugin_official_list_cache();
	empty($official_plugins) AND $official_plugins = array();
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
		if(!isset($plugins[$_dir]) || $plugins[$_dir]['enable']) {
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
		if(in_array($dir, $plugin['dependencies'])) {
			$arr[$_dir] = $version;
		}
	}
	return $arr;
}

function plugin_enable($dir) {
	global $plugins;
	
	$plugins[$dir]['enable'] = 1;
	
	plugin_overwrite($dir, 'install');
	plugin_hook($dir, 'install');
	
	file_replace_var("../plugin/$dir/conf.json", array('enable'=>1), TRUE);
	return TRUE;
}

function plugin_disable($dir) {
	global $plugins;
	
	$plugins[$dir]['enable'] = 0;
	
	plugin_overwrite($dir, 'unstall');
	plugin_hook($dir, 'unstall');
	
	file_replace_var("../plugin/$dir/conf.json", array('enable'=>0), TRUE);
	return TRUE;
}


/*
	插件安装：
		把所有的插件点合并，重新写入文件。如果没有备份文件，则备份一份。
		插件名可以为源文件名：view/header.htm
*/
function plugin_install($dir) {
	global $plugins;
	
	$plugins[$dir]['installed'] = 1;
	$plugins[$dir]['enable'] = 1;
	
	// 1. 直接覆盖的方式
	plugin_overwrite($dir, 'install');
	
	// 2. 钩子的方式
	plugin_hook($dir, 'install');
	
	// 写入配置文件
	file_replace_var("../plugin/$dir/conf.json", array('installed'=>1, 'enable'=>1), TRUE);
	
	return TRUE;
}

// copy from plugin_install 修改
function plugin_unstall($dir) {
	global $plugins;
	
	$plugins[$dir]['installed'] = 0;
	$plugins[$dir]['enable'] = 0;
	
	// 1. 直接覆盖的方式
	plugin_overwrite($dir, 'unstall');
	
	// 2. 钩子的方式
	plugin_hook($dir, 'unstall');
	
	// 写入配置文件
	file_replace_var("../plugin/$dir/conf.json", array('installed'=>0, 'enable'=>0), TRUE);
	
	return TRUE;
}

function plugin_overwrite($dir, $action = 'install') {
	$files = glob_recursive("../plugin/$dir/overwrite/*");
	//$files = glob("./plugin/$dir/overwrite/*");
	foreach($files as $file) {
		$workfile = str_replace("../plugin/$dir/overwrite/", '../', $file);
		if(is_dir($file)) {
			!is_dir($workfile) AND mkdir($workfile, 0777, TRUE);
		} elseif(is_file($file)) {
			$backfile = file_backname($workfile);
			if($action == 'install') {
				// 覆盖
				if(is_file($workfile)) {
					$r = file_backup($workfile);
					if($r === FALSE) continue;
					xn_copy($file, $workfile);
				// 新增的文件，做个标志
				} else {
					touch($backfile); // 空文件作为标志
					xn_copy($file, $workfile);
				}
			} elseif($action == 'unstall') {
				// 判断标志:，
				// 删除新增的文件，空备份文件表示原文件为新增
				if(!file_get_contents($backfile)) {
					xn_unlink($backfile);
					xn_unlink($workfile);
				// 还原备份文件
				} else {
					file_backup_restore($workfile);
				}
			}
		}
	}
}

function plugin_hook($dir, $action = 'install') {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$hooks = $plugins[$dir]['hooks'];
	foreach($hooks as $hookname=>$hookpath) {
		$srcfile = plugin_find_srcfile_by_hookname($hookname);
		if(!$srcfile) continue;
		
		$hookscontent = plugin_hooks_merge_by_rank($hookname);
		
		// 查找源文件，将合并的内容放进去。
		$backfile = file_backname($srcfile);
		
		if($hookscontent && $action == 'install') {
			$r = file_backup($srcfile);
			if($r === FALSE) continue;
			$s = file_get_contents($srcfile); // 直接对源文件进行操作，因为有备份可以恢复
			$s = preg_replace("#(\t*//\shook\s$hookname\s*\r\n)#is", "\\1".$hookscontent, $s);
			$s = str_replace("<!--{hook $hookname}-->", "<!--{hook $hookname}-->".$hookscontent, $s);
			
			file_put_contents_try($srcfile, $s);
			
		// 如果为空，则表示没有插件安装到此处，还原备份文件，删除备份文件
		} else {
			file_backup_restore($srcfile);
		}
	}
	return TRUE;
}

// 将所有的同名 hook 内容合并（按照优先级排序），需要判断 installed 是否为 1
function plugin_hooks_merge_by_rank($hookname) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$arr = array();
	
	foreach($plugins as $dir=>$plugin) {
		if(isset($plugin['installed']) && $plugin['installed'] == 0) continue; // 未安装的跳过
		if(isset($plugin['enable']) && $plugin['enable'] == 0) continue; // 禁用的跳过
		foreach($plugin['hooks'] as $hookname2=>$hookpath) {
			if($hookname2 != $hookname) continue;
			$rank = isset($plugin['hooks_rank'][$hookname]) ? $plugin['hooks_rank'][$hookname] : 0;
			$s = file_get_contents($hookpath);
			$arr[$rank][] = file_get_contents($hookpath);
		}
	}
	$s = '';
	foreach ($arr as $arr2) {
		$s .= implode("\r\n", $arr2);
	}
	return $s;
}

function plugin_find_srcfile_by_hookname($hookname) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	foreach($plugin_srcfiles as $file) {
		if(!is_file($file)) continue; // 可能在卸载的过程中，文件已经不存在了，但仍然在列表中。
		$s = file_get_contents($file);
		if(!$s) return FALSE;
		if(strpos($s, "// hook $hookname") !== FALSE) {
			return $file;
		} elseif(strpos($s, "<!--{hook $hookname}-->") !== FALSE) {
			return $file;
		}
	}
	return FALSE;
}

function plugin_overwrite_install($dir) {
	plugin_overwrite($dir, 'install');
	return TRUE;
}

function plugin_overwrite_unstall($dir) {
	plugin_overwrite($dir, 'unstall');
	return TRUE;
}



// 先下载，购买，付费，再安装
function plugin_online_install($dir) {

}



// -------------------> 官方插件列表缓存到本地。

// 条件满足的总数
function plugin_official_total($cond = array()) {
	global $official_plugins;
	$offlist = $official_plugins;
	$offlist = arrlist_cond_orderby($offlist, $cond, array(), 1, 1000);
	return count($offlist);
}

// 远程插件列表，从官方服务器获取插件列表，全部缓存到本地，定期更新
function plugin_official_list($cond = array(), $orderby = array('pluginid'=>-1), $page = 1, $pagesize = 20) {
	global $official_plugins;
	// 服务端插件信息，缓存起来
	$offlist = $official_plugins;
	$offlist = arrlist_cond_orderby($offlist, $cond, $orderby, $page, $pagesize);
	foreach($offlist as &$plugin) $plugin = plugin_read($plugin['dir'], FALSE);
	return $offlist;
}

function plugin_official_list_cache() {
	$s = cache_get('plugin_official_list');
	if($s === NULL) {
		$url = PLUGIN_OFFICIAL_URL."plugin-all-4.htm"; // 获取所有的插件，匹配到3.0以上的。
		$s = http_get($url, 30, 3);
		
		// 检查返回值是否正确
		if(empty($s)) return xn_error(-1, '从官方获取插件数据失败。');
		$r = xn_json_decode($s);
		if(empty($r)) return xn_error(-1, '从官方获取插件数据格式不对。');
		
		$s = $r;
		cache_set('plugin_official_list', $s, 3600); // 缓存时间 1 小时。
	}
	return $s;
}

function plugin_official_read($dir) {
	global $official_plugins;
	$offlist = $official_plugins;
	$plugin = isset($offlist[$dir]) ? $offlist[$dir] : array();
	return $plugin;
}

// -------------------> 本地插件列表缓存到本地。
// 安装，卸载，禁用，更新
function plugin_read($dir, $local_first = TRUE) {
	global $plugins;
	
	$local = array_value($plugins, $dir, array());
	$official = plugin_official_read($dir);
	
	if(empty($local) && empty($official)) return array();
	
	// 本地插件信息
	//!isset($plugin['dir']) && $plugin['dir'] = '';
	!isset($local['name']) && $local['name'] = '';
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
	
	// 加上官方插件的信息
	!isset($official['pluginid']) && $official['pluginid'] = 0;
	!isset($official['name']) && $official['name'] = '';
	!isset($official['brief']) && $official['brief'] = '';
	!isset($official['bbs_version']) && $official['bbs_version'] = '4.0';
	!isset($official['version']) && $official['version'] = '1.0';
	!isset($official['cateid']) && $official['cateid'] = 0;
	!isset($official['lastupdate']) && $official['lastupdate'] = 0;
	!isset($official['stars']) && $official['stars'] = 0;
	!isset($official['user_stars']) && $official['user_stars'] = 0;
	!isset($official['installs']) && $official['installs'] = 0;
	!isset($official['sells']) && $official['sells'] = 0;
	!isset($official['file_md5']) && $official['file_md5'] = '';
	!isset($official['filename']) && $official['filename'] = '';
	!isset($official['is_cert']) && $official['is_cert'] = 0;
	!isset($official['is_show']) && $official['is_show'] = 0;
	
	if($local_first) {
		$plugin = $local + $official;
	} else {
		$plugin = $official + $local;
	}
	// 额外的判断
	$plugin['icon_url'] = $plugin['pluginid'] ? PLUGIN_OFFICIAL_URL."upload/plugin/$plugin[pluginid]/icon.png" : "../plugin/$dir/icon.png";
	$plugin['setting_url'] = $plugin['installed'] && is_file("../plugin/$dir/setting.php") ? "plugin-setting-$dir.htm" : "";
	$plugin['downloaded'] = isset($plugins[$dir]);
	$plugin['stars_fmt'] = $plugin['pluginid'] ? str_repeat('<span class="icon star"></span>', $plugin['stars']) : '';
	$plugin['user_stars_fmt'] = $plugin['pluginid'] ? str_repeat('<span class="icon star"></span>', $plugin['user_stars']) : '';
	$plugin['have_upgrade'] = $plugin['pluginid'] && version_compare($official['version'], $local['version']) > 0 ? TRUE : FALSE;
	$plugin['official_version'] = $official['version']; // 官方版本
	
	return $plugin;
}