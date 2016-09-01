<?php

 // 本地插件
//$plugin_srcfiles = array();
$plugin_paths = array();
$plugins = array(); // 跟官方插件合并

// 官方插件列表
$official_plugins = array();

define('PLUGIN_OFFICIAL_URL', DEBUG == 3 ? 'http://plugin.x.com/' : 'http://plugin.xiuno.com/');

// todo: 对路径进行处理 include _include(APP_PATH.'view/htm/header.inc.htm');
function _include($srcfile) {
	global $conf;
	// 合并插件，存入 tmp_path
	$len = strlen(APP_PATH);
	$tmpfile = $conf['tmp_path'].substr(str_replace('/', '_', $srcfile), $len);
	if(!is_file($tmpfile) || DEBUG > 1) {
		// 开始编译
		$s = plugin_compile_srcfile($srcfile);
		file_put_contents_try($tmpfile, $s);
	}
	return $tmpfile;
}

// 在安装、卸载插件的时候，需要先初始化
function plugin_init() {
	global $plugin_srcfiles, $plugin_paths, $plugins, $official_plugins;
	/*$plugin_srcfiles = array_merge(
		glob(APP_PATH.'model/*.php'), 
		glob(APP_PATH.'route/*.php'), 
		glob(APP_PATH.'view/htm/*.*'), 
		glob(ADMIN_PATH.'route/*.php'), 
		glob(ADMIN_PATH.'view/htm/*.*'),
		glob(APP_PATH.'lang/en-us/*.*'),
		glob(APP_PATH.'lang/zh-cn/*.*'),
		glob(APP_PATH.'lang/zh-tw/*.*'),
		array(APP_PATH.'model.inc.php')
	);
	foreach($plugin_srcfiles as $k=>$file) {
		$filename = file_name($file);
		if(is_backfile($filename)) {
			unset($plugin_srcfiles[$k]);
		}
	}*/
	
	$official_plugins = plugin_official_list_cache();
	empty($official_plugins) AND $official_plugins = array();
	
	$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
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
		
		// 合并本地，线上
		$plugins[$dir] = plugin_read($dir);
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
	rmdir_recusive($conf['tmp_path'].'src/', TRUE);
	xn_unlink($conf['tmp_path'].'model.min.php');
}

function plugin_disable($dir) {
	global $plugins;
	
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
/*
function plugin_overwrite($dir, $action = 'install') {
	$files = glob_recursive(APP_PATH."plugin/$dir/overwrite/*");
	if(empty($files)) return;
	//$files = glob("./plugin/$dir/overwrite/*");
	foreach($files as $file) {
		$workfile = $file; //str_replace(APP_PATH."plugin/$dir/overwrite/", '../', $file); // todo: 使用绝对路径后就没有必要再这么做
		if(is_dir($file)) {
			!is_dir($workfile) AND mkdir($workfile, 0777, TRUE);
		} elseif(is_file($file)) {
			$backfile = file_backname($workfile);
			if($action == 'install') {
				
				// 如果没有改动，则不安装，如果备份不存在，则也成立。
				if(xn_filemtime($workfile) <= xn_filemtime($backfile)) {
					continue;
				}
				
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
*/

/*
function plugin_hook($dir, $action = 'install') {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$hooks = $plugins[$dir]['hooks'];
	foreach($hooks as $hookname=>$hookpath) {
		$srcfile = plugin_find_srcfile_by_hookname($hookname);
		if(!$srcfile) continue;
		
		// 查找源文件，将合并的内容放进去。
		$backfile = file_backname($srcfile);
		
		// 如果没有改动，则不安装，如果备份不存在，则也成立。
		if($action == 'install' && xn_filemtime($hookpath) <= xn_filemtime($backfile)) {
			continue;
		} 
		
		$hookscontent = plugin_hooks_merge_by_rank($hookname);
		
		if($hookscontent && $action == 'install') {
			$r = file_backup($srcfile);
			if($r === FALSE) continue;
			$s = file_get_contents($srcfile); // 直接对源文件进行操作，因为有备份可以恢复
			$s = str_replace("// hook $hookname", "// hook $hookname\r\n".$hookscontent, $s);
			$s = str_replace("<!--{hook $hookname}-->", "<!--{hook $hookname}-->".$hookscontent, $s);
			
			file_put_contents_try($srcfile, $s);
			
		// 如果为空，则表示没有插件安装到此处，还原备份文件，删除备份文件
		} else {
			file_backup_restore($srcfile);
		}
	}
	return TRUE;
}*/

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
/*
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
}*/

/*function plugin_overwrite_install($dir) {
	plugin_overwrite($dir, 'install');
	return TRUE;
}*/

/*function plugin_overwrite_unstall($dir) {
	plugin_overwrite($dir, 'unstall');
	return TRUE;
}*/

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
			if(!$pconf['enable'] || !$pconf['installed']) continue;
			$return_paths[$path] = $pconf;
		}
	}
	return $return_paths;
}

// 编译源文件，把插件合并到该文件，不需要递归，执行的过程中 include _include() 自动会递归。
function plugin_compile_srcfile($srcfile) {
	// 如果有 overwrite，则用 overwrite 替换掉
	$srcfile = plugin_find_overwrite($srcfile);
	$s = file_get_contents($srcfile);
	
	$s = preg_replace('#<!--{hook\s+(.*?)}-->#', '// hook \\1', $s);
	$s = preg_replace_callback('#//\s*hook\s+(\S+)#is', 'plugin_compile_srcfile_callback', $s);
	return $s;
}


// 只返回一个权重最高的文件名
function plugin_find_overwrite($srcfile) {
	//$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
	
	$plugin_paths = plugin_paths_enabled();
	
	$len = strlen(APP_PATH);
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
					$rank = isset($pconf['hooks_rank'][$hookname]) ? $pconf['hooks_rank'][$hookname] : 0;
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
				$t = preg_replace('#^\s*<\?php\s*exit;(.*?)\?>\s*$#is', '\\1', $t);
				
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
	$plugin['have_upgrade'] = $plugin['installed'] && version_compare($official['version'], $local['version']) > 0 ? TRUE : FALSE;
	$plugin['official_version'] = $official['version']; // 官方版本
	return $plugin;
}