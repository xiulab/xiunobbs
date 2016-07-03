<?php

$plugin_srcfiles = array();
$plugin_paths = array();
$plugins = array();

// 在安装、卸载插件的时候，需要先初始化
function plugin_init() {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$plugin_srcfiles = array_merge(
		glob('./model/*.php'), 
		glob('./pc/route/*.php'), 
		glob('./pc/view/*.*'), 
		glob('./admin/route/*.php'), 
		glob('./admin/view/*.*')
	);
	foreach($plugin_srcfiles as $k=>$file) {
		$filename = file_name($file);
		if(substr($filename, 0, 7) == 'backup_') {
			unset($plugin_srcfiles[$k]);
		}
	}
	$plugin_paths = glob('./plugin/*', GLOB_ONLYDIR);
	foreach ($plugin_paths as $path) {
		$dir = file_name($path);
		$plugins[$dir] = xn_json_decode(file_get_contents($path."/conf.json"));
		
		// 额外的信息
		$plugins[$dir]['dir'] = $dir;
		$plugins[$dir]['icon_url'] = "plugin/$dir/icon.png";
		$plugins[$dir]['have_setting'] = is_file("./plugin/$dir/setting.php");
		
		$plugins[$dir]['hooks'] = array();
		$hookpaths = glob("./plugin/$dir/hook/*.*"); // path
		foreach($hookpaths as $hookpath) {
			$hookname = file_name($hookpath);
			$plugins[$dir]['hooks'][$hookname] = $hookpath;
		}
	}
	/*
	// 检查文件目录和文件是否可写
	if(plugin_dir_is_writable()) {
		
	}
	*/
}

/*
	插件安装：
		把所有的插件点合并，重新写入文件。如果没有备份文件，则备份一份。
		插件名可以为源文件名：pc/view/header.htm
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
	json_conf_set('installed', 1, "./plugin/$dir/conf.json");
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
	json_conf_set('installed', 0, "./plugin/$dir/conf.json");
	return TRUE;
}

// 将所有的同名 hook 内容合并（按照优先级排序），需要判断 installed 是否为 1
function plugin_hooks_merge_by_rank($hookname) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$arr = array();
	
	foreach($plugins as $dir=>$plugin) {
		if(isset($plugin['installed']) && $plugin['installed'] == 0) continue;
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
	$files = glob_recursive("./plugin/$dir/overwrite/*");
	//$files = glob("./plugin/$dir/overwrite/*");
	foreach($files as $file) {
		$workfile = str_replace("./plugin/$dir/overwrite/", './', $file);
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
	$files = glob_recursive("./plugin/$dir/overwrite/*");
	//$files = glob("./plugin/$dir/overwrite/*");
	foreach($files as $file) {
		$workfile = str_replace("./plugin/$dir/overwrite/", './', $file);
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

//plugin_install('xn_ad');
//plugin_unstall('xn_ad');
//exit