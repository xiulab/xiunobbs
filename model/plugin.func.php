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
		glob('./model/*.php'), 
		glob('./route/*.php'), 
		glob('./view/htm/*.*'), 
		glob('./admin/route/*.php'), 
		glob('./admin/view/htm/*.*'),
		array('./index.php', 'admin/index.php')
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

// 插件依赖检测，返回依赖的插件列表
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







// -------------------> 官方插件列表缓存到本地。

function plugin_official_total($cond = array()) {
	$offlist = plugin_official_list_cache();
	return count($offlist);
}

// 远程插件列表，从官方服务器获取插件列表，全部缓存到本地，定期更新
function plugin_official_list($cond = array(), $orderby = array('stars'=>-1), $page = 1, $pagesize = 20) {
	// 服务端插件信息，缓存起来
	$offlist = plugin_official_list_cache();
	$offlist = arrlist_cond_orderby($offlist, $cond, $orderby, $page, $pagesize);
	foreach($offlist as &$plugin) plugin_official_format($plugin);
	return $offlist;
}

/*
return Array (
    [xn_clear_rubbish] => Array
        (
            [pluginid] => 102
            [dir] => xn_clear_rubbish
            [name] => 清理论坛垃圾
            [brief] => 清理过期的帖子，短消息，用户，附件。
            [version] => 1.0
            [bbs_version] => 4.0
            [cateid] => 0
            [styleid] => 0
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
	$s = cache_get('plugin_official_list');
	if($s === NULL || DEBUG) {
		$url = "http://plugin.xiuno.com/plugin-list-version-4.htm"; // 获取所有的插件，匹配到3.0以上的。
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
	plugin_official_format($plugin);
	return $plugin;
}

function plugin_official_format(&$plugin) {
	global $plugins;
	if(empty($plugin)) return;
	$dir = $plugin['dir'];
	$plugin['icon_url'] = "http://plugin.xiuno.com/upload/plugin/$plugin[pluginid]/icon.png";
	$plugin['downloaded'] = isset($plugins[$dir]);
	$plugin['installed'] = isset($plugins[$dir]) && $plugins[$dir]['installed'];
	$plugin['stars_fmt'] = str_repeat('<span class="icon star"></span>', $plugin['stars']);
	$plugin['user_stars_fmt'] = str_repeat('<span class="icon star"></span>', $plugin['user_stars']);

	if(is_dir("./plugin/$dir")) {
		
	} else {
		$plugin['setting_url'] = '';
	}
	$plugin['is_official'] = 1;
}






//plugin_install('xn_ad');
//plugin_unstall('xn_ad');
//exit