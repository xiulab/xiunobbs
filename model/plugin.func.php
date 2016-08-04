<?php

 // 本地插件
$plugin_srcfiles = array();
$plugin_paths = array();
$plugins = array(); // 跟官方插件合并

// 官方插件列表
$official_plugins = array();

// 在安装、卸载插件的时候，需要先初始化
function plugin_init() {
	global $plugin_srcfiles, $plugin_paths, $plugins;
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
		$plugins[$dir] = is_file($conffile) ? xn_json_decode(file_get_contents($conffile)) : array();
		
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
	
	/*
	// 检查文件目录和文件是否可写
	if(plugin_dir_is_writable()) {
		
	}
	*/
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
	插件安装：
		把所有的插件点合并，重新写入文件。如果没有备份文件，则备份一份。
		插件名可以为源文件名：view/header.htm
*/
function plugin_install($dir) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	// 1. 直接覆盖的方式
	plugin_overwrite_install($dir);
	
	// 2. 标准的插件钩子
	$hooks = $plugins[$dir]['hooks'];
	$plugins[$dir]['installed'] = 1;
	foreach($hooks as $hookname=>$hookpath) {
		$srcfile = plugin_find_hookname_from_srcfile($hookname);
		if(!$srcfile) continue;
		
		$hookscontent = plugin_hooks_merge_by_rank($hookname);
		
		// 查找源文件，将合并的内容放进去。
		$backfile = plugin_backup_filename($srcfile);
		
		if($hookscontent) {
			!is_file($backfile) AND is_file($srcfile) AND copy($srcfile, $backfile);
			$basefile = is_file($backfile) ? $backfile : $srcfile;
			
			$s = file_get_contents($basefile);
			$s = preg_replace("#\t*//\shook\s$hookname\s*\r\n#is", $hookscontent, $s);
			$s = str_replace("<!--{hook $hookname}-->", $hookscontent, $s);
			
			file_put_contents($srcfile, $s);
			
		// 如果为空，则表示没有插件安装到此处，还原备份文件，删除备份文件
		} else {
			if(is_file($backfile)) {
				copy($backfile, $srcfile);
				clearstatcache();
				(filesize($srcfile) == filesize($backfile)) AND unlink($backfile);
			}
		}
	}
	// 写入配置文件
	json_conf_set('installed', 1, "../plugin/$dir/conf.json");
	return TRUE;
}

// copy from plugin_install 修改
function plugin_unstall($dir) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	// 1. 直接覆盖的方式
	plugin_overwrite_unstall($dir);
	
	// 2. 标准的插件钩子
	$hooks = $plugins[$dir]['hooks'];
	$plugins[$dir]['installed'] = 0;
	
	foreach($hooks as $hookname=>$hookpath) {
		$srcfile = plugin_find_hookname_from_srcfile($hookname);
		if(!$srcfile) continue;
		
		$hookscontent = plugin_hooks_merge_by_rank($hookname);
		
		// 查找源文件，将合并的内容放进去。
		$backfile = plugin_backup_filename($srcfile);
		
		if($hookscontent) {
			!is_file($backfile) AND is_file($srcfile) AND copy($srcfile, $backfile);
			$basefile = is_file($backfile) ? $backfile : $srcfile;
			
			$s = file_get_contents($basefile);
			$s = preg_replace("#\t*//\shook\s$hookname\s*\r\n#is", $hookscontent, $s);
			$s = str_replace("<!--{hook $hookname}-->", $hookscontent, $s);
			
			file_put_contents($srcfile, $s);
			
		// 如果为空，则表示没有插件安装到此处，还原备份文件，删除备份文件
		} else {
			if(is_file($backfile)) {
				copy($backfile, $srcfile);
				clearstatcache();
				(filesize($srcfile) == filesize($backfile)) AND unlink($backfile);
			}
		}
	}
	// 写入配置文件
	json_conf_set('installed', 0, "../plugin/$dir/conf.json");
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

function plugin_find_hookname_from_srcfile($hookname) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	foreach($plugin_srcfiles as $file) {
		$backfile = plugin_backup_filename($file);
		$basefile = is_file($backfile) ? $backfile : $file;
		$s = file_get_contents($basefile);
		if(strpos($s, "// hook $hookname") !== FALSE) {
			return $file;
		} elseif(strpos($s, "<!--{hook $hookname}-->") !== FALSE) {
			return $file;
		}
	}
	return FALSE;
}

function plugin_backup_filename($path) {
	$dirname = dirname($path);
	$filename = file_name($path);
	$s = "$dirname/backup_$filename";
	return $s;
}

// 先下载，购买，付费，再安装
function plugin_online_install($dir) {

}

function plugin_overwrite_install($dir) {
	$files = glob_recursive("../plugin/$dir/overwrite/*");
	//$files = glob("./plugin/$dir/overwrite/*");
	foreach($files as $file) {
		$workfile = str_replace("../plugin/$dir/overwrite/", './', $file);
		if(is_dir($file)) {
			!is_dir($workfile) AND mkdir($workfile, 0777, TRUE);
		} elseif(is_file($file)) {
			$dirname = dirname($workfile);
			$filename = file_name($workfile);
			$backfile = "$dirname/backup_$filename";
			if(is_file($workfile) && !is_file($backfile)) {
				copy($workfile, $backfile);
			}
			copy($file, $workfile);
		}
	}
}

function plugin_overwrite_unstall($dir) {
	$files = glob_recursive("../plugin/$dir/overwrite/*");
	//$files = glob("./plugin/$dir/overwrite/*");
	foreach($files as $file) {
		$workfile = str_replace("../plugin/$dir/overwrite/", './', $file);
		if(is_dir($file)) {
			// todo: 删除目录
			// !is_dir($workfile) AND mkdir($workfile, 0777, TRUE);
		} elseif(is_file($file)) {
			$dirname = dirname($workfile);
			$filename = file_name($workfile);
			$backfile = "$dirname/backup_$filename";
			if(is_file($backfile)) {
				copy($backfile, $workfile);
				unlink($backfile);
			}
			unlink($workfile);
		}
	}
}







// -------------------> 官方插件列表缓存到本地。

// 条件满足的总数
function plugin_official_total($cond = array()) {
	$offlist = plugin_official_list_cache();
	$offlist = arrlist_cond_orderby($offlist, $cond, array(), 1, 1000);
	return count($offlist);
}

// 远程插件列表，从官方服务器获取插件列表，全部缓存到本地，定期更新
function plugin_official_list($cond = array(), $orderby = array('pluginid'=>-1), $page = 1, $pagesize = 20) {
	// 服务端插件信息，缓存起来
	$offlist = plugin_official_list_cache();
	$offlist = arrlist_cond_orderby($offlist, $cond, $orderby, $page, $pagesize);
	foreach($offlist as &$plugin) $plugin = plugin_read($plugin['dir']);
	return $offlist;
}

function plugin_official_list_cache() {
	$s = cache_get('plugin_official_list');
	if($s === NULL) {
		$url = "http://plugin.xiuno.com/plugin-list-version-3.htm"; // 获取所有的插件，匹配到3.0以上的。
		$s = http_get($url, 30, 3);
		if(empty($s)) {
			return xn_error(-1, '从官方获取插件数据失败。');
		}
		$r = xn_json_decode($s);
		if(!empty($r['servererror']) || (!empty($r['code']) && $r['code'] != 0)) {
			return xn_error(-1, '从官方获取插件数据格式不对。');
		}
		
		$s = !empty($r['message']) ? $r['message'] : $r;
		cache_set('plugin_official_list', $s, 3600); // 缓存时间 1 小时。
	}
	return $s;
}

function plugin_official_read($dir) {
	$offlist = plugin_official_list_cache();
	$plugin = isset($offlist[$dir]) ? $offlist[$dir] : array();
	return $plugin;
}

// -------------------> 本地插件列表缓存到本地。
// 安装，卸载，禁用，更新
function plugin_read($dir) {
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
	!isset($official['$official']) && $official['version'] = '1.0';
	!isset($official['bbs_version']) && $official['bbs_version'] = '4.0';
	!isset($official['version']) && $official['version'] = 0;
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
	
	$plugin = $local + $official;
	
	// 额外的判断
	$plugin['icon_url'] = $plugin['pluginid'] ? "http://plugin.xiuno.com/upload/plugin/$plugin[pluginid]/icon.png" : "../plugin/$dir/icon.png";
	$plugin['icon_url'] = $plugin['pluginid'] ? "http://plugin.xiuno.com/upload/plugin/$plugin[pluginid]/icon.png" : "../plugin/$dir/icon.png";
	$plugin['setting_url'] = $plugin['installed'] && is_file("../plugin/$dir/setting.php") ? "plugin-setting-$dir.htm" : "";
	$plugin['downloaded'] = isset($plugins[$dir]);
	$plugin['stars_fmt'] = $plugin['pluginid'] ? str_repeat('<span class="icon star"></span>', $plugin['stars']) : '';
	$plugin['user_stars_fmt'] = $plugin['pluginid'] ? str_repeat('<span class="icon star"></span>', $plugin['user_stars']) : '';
	$plugin['have_upgrade'] = $plugin['pluginid'] && version_compare($official['version'], $local['version']) > 0 ? TRUE : FALSE;
	
	return $plugin;
}


//plugin_install('xn_ad');
//plugin_unstall('xn_ad');
//exit