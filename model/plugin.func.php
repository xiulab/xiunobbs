<?php

// 查找字符串第 n 次出现
function strnpos($haystack, $needle, $n) {
	$len = strlen($haystack);
	$len2 = strlen($needle);
	if($n == 1) return strpos($haystack, $needle);
	$start = strpos($haystack, $needle); // 第一次出现的地方
	while ($start !== FALSE && $start < $len && --$n > 0) {
		$start = strpos($haystack, $needle, $start + $len2);
	}
	return $start;
}

// 返回文件指定行的偏移位置，一行的开始
function range_start_line($file, $line) {
	$s = file_get_contents($file);
	$rn = strpos($s, "\r\n") === FALSE ? "\n" : "\r\n";
	$len = strlen($rn);
	if($line < 2) return 0;
	$pos = strnpos($s, $rn, $line - 1) + $len;
	return $pos;
}

// 返回文件指定行的偏移位置，一行的结束
function range_end_line($file, $line) {
	$s = file_get_contents($file);
	$rn = strpos($s, "\r\n") === FALSE ? "\n" : "\r\n";
	$len = strlen($rn);
	if($line == 0) return 0;
	$pos = strnpos($s, $rn, $line); // 如果等于 FALSE 则为文件末尾
	$pos === FALSE AND $pos = strlen($s);
	return $pos;
}

// 返回文件关键词的偏移量，开始位置
function range_start_keyword($file, $keyword) {
	$s = file_get_contents($file);
	$n = strpos($s, $keyword);
	return $n; // 如果没有找到则为 FALSE
}

// 返回文件关键词的偏移量，结束位置
function range_end_keyword($file, $keyword) {
	$s = file_get_contents($file);
	$len = strlen($keyword);
	$n = strpos($s, $keyword);
	return $n === FALSE ? FALSE : $n + $len; // 如果没有找到则为 FALSE
}

function plugin_install($arr) {
	$file = $arr['file'];
	$range_start = intval($arr['range_start']); 
	$range_end = intval($arr['range_end']); 
	$code = $arr['code'];
	
	$s = file_get_contents($file);
	
	$pre = substr($s, 0, $range_start);
	$suffix = substr($s, $range_end);
	$s = $pre.$code.$suffix;
	
	$r = file_put_contents($file, $s);
	return $r;
}

// $file: 插入的源文件名
// $keyword: 关键词
// $codefile: 需要插入的代码
function plugin_install_before($file, $keyword, $code) {
	$range_start = range_start_keyword($file, $keyword);
	if($range_start === FALSE) return xn_error(-1, '未找到特定字符串，可能版本不对或插件已经卸载。');
	
	if(range_start_keyword($file, $code.$keyword) !== FALSE) return xn_error(-1, '插件点已经安装。');
	
	$plugin = array (
		'file'=> $file,							// bbs 源文件
		'range_start'=> $range_start,					// bbs 源文件开始第几个字符
		'range_end'=> $range_start,					// bbs 源文件结束到第几个字符
		'code'=> $code,							// 要插入的代码
	);
	
	return plugin_install($plugin);
}

function plugin_install_after($file, $keyword, $code) {
	$range_start = range_start_keyword($file, $keyword);
	if($range_start === FALSE) return xn_error(-1, '未找到特定字符串，可能版本不对或插件已经卸载。');
	
	if(range_start_keyword($file, $keyword.$code) !== FALSE) return xn_error(-1, '插件点已经安装。');
	
	$plugin = array (
		'file'=> $file,							// bbs 源文件
		'range_start'=> $range_start + strlen($keyword),		// bbs 源文件开始第几个字符
		'range_end'=> $range_start + strlen($keyword),			// bbs 源文件结束到第几个字符
		'code'=> $code,							// 要插入的代码
	);
	
	return plugin_install($plugin);
}

// 前面追加
function plugin_install_unshift($file, $s) {
	$old = file_get_contents($file);
	$new = $s.$old;
	if(strpos($old, $s) !== FALSE) return TRUE; // 如果已经存在，则不再追加
	return file_put_contents($file, $new);
}

// 末尾追加
function plugin_install_append($file, $s) {
	$old = file_get_contents($file);
	$new = $old.$s;
	if(strpos($old, $s) !== FALSE) return TRUE; // 如果已经存在，则不再追加
	return file_put_contents($file, $new);
}

function plugin_install_remove($file, $old) {
	$s = file_get_contents($file);
	$new = str_replace($old, '', $s);
	return file_put_contents($file, $new);
}

// $file: 需要卸载的源文件名
// $keyword: 关键词
// $codefile: 需要插入的代码
function plugin_unstall_before($file, $keyword, $code) {
	$range_start = range_start_keyword($file, $code.$keyword);
	if($range_start === FALSE) return xn_error(-1, '未找到特定字符串，可能版本不对或插件已经卸载。');
	$range_end = $range_start + strlen($code.$keyword);
	
	$plugin = array (
		'file'=> $file,							// bbs 源文件
		'range_start'=> $range_start,					// bbs 源文件开始第几个字符
		'range_end'=> $range_end,					// bbs 源文件结束到第几个字符
		'code'=> $keyword,						// 要插入的代码
	);
	
	return plugin_install($plugin);
}

// $file: 需要卸载的源文件名
// $keyword: 关键词
// $codefile: 需要插入的代码
function plugin_unstall_after($file, $keyword, $code) {
	$range_start = range_start_keyword($file, $keyword.$code);
	if($range_start === FALSE) return xn_error(-1, '未找到特定字符串，可能版本不对或插件已经卸载。');
	$range_end = $range_start + strlen($keyword.$code);
	
	$plugin = array (
		'file'=> $file,							// bbs 源文件
		'range_start'=> $range_start,					// bbs 源文件开始第几个字符
		'range_end'=> $range_end,					// bbs 源文件结束到第几个字符
		'code'=> $keyword,						// 要插入的代码
	);
	
	return plugin_install($plugin);
}

function plugin_install_replace($file, $old, $new) {
	$s = file_get_contents($file);
	$s2 = str_replace($old, $new, $s);
	if($s != $s2) {
		return file_put_contents($file, $s2);
	} else {
		return xn_error(-1, '为找到指定的字符串，插件不能正常安装。');
	}
}

function plugin_unstall_replace($file, $new, $old) {
	$s = file_get_contents($file);
	$s2 = str_replace($old, $new, $s);
	if($s != $s2) {
		return file_put_contents($file, $s2);
	} else {
		return xn_error(-1, '为找到指定的字符串，可能版本不对或插件已经卸载');
	}
}

// 卸载 code，直接删除指定的数据
function plugin_unstall_remove($file, $code) {
	$range_start = range_start_keyword($file, $code);
	if($range_start === FALSE) return xn_error(-1, '未找到特定字符串，可能版本不对或插件已经卸载。');
	$range_end = $range_start + strlen($code);
	
	$plugin = array (
		'file'=> $file,							// bbs 源文件
		'range_start'=> $range_start,					// bbs 源文件开始第几个字符
		'range_end'=> $range_end,					// bbs 源文件结束到第几个字符
		'code'=> '',							// 要插入的代码
	);
	
	return plugin_install($plugin);
}

/*
function plugin_install_view($name) {
	if(is_dir("./plugin/$name/view")) {
		$view_name = is_file('./pc/view/.plugin_dir') ? file_get_contents('./pc/view/.plugin_dir') : 'view_default';
		rename('./pc/view', './pc/'.$view_name);
		rename("./plugin/$name/view", './pc/view');
	}
}
function plugin_install_route($name) {
	if(is_dir("./plugin/$name/route")) {
		$route_name = is_file('./pc/route/.plugin_dir') ? file_get_contents('./pc/route/.plugin_dir') : 'route_default';
		rename('./pc/route', './pc/'.$route_name);
		rename("./plugin/$name/route", './pc/route');
	}
}
function plugin_unstall_view($name) {
	if(is_dir("./pc/view_default")) {
		rename('./pc/view', "./plugin/$name/view");
		rename("./pc/view_default", './pc/view');
	}
}
function plugin_unstall_route($name) {
	if(is_dir("./pc/route_default")) {
		rename('./pc/route', "./plugin/$name/route");
		rename("./pc/route_default", './pc/route');
	}
}
*/











// 本地插件列表
function plugin_local_list() {
	$plugin_dirs = glob('./plugin/*', GLOB_ONLYDIR|GLOB_NOSORT);
	if(!$plugin_dirs) return array();
	$plugins = array();
	foreach($plugin_dirs as $dir) {
		$dir = substr($dir, strrpos($dir, '/') + 1);
		$plugins[$dir] = plugin_local_read($dir);
	}
	return $plugins;
}

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
            [name] => 清理论坛垃圾
            [brief] => 清理过期的帖子，短消息，用户，附件。
            [version] => 2.1.10
            [bbs_version] => 2.0.3
            [cateid] => 0
            [styleid] => 0
            [dir] => xn_clear_rubbish
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
		$url = "http://plugin.xiuno.com/plugin-list-version-3.htm"; // 获取所有的插件，匹配到3.0以上的。
		$s = http_get($url, 30, 3);
		if(empty($s)) {
			xn_log('从官方获取插件数据失败。');
			return FALSE;
		}
		$r = xn_json_decode($s);
		if(!empty($r['servererror']) || (!empty($r['code']) && $r['code'] != 0)) {
			xn_log("从官方获取插件数据格式不对。");
			return FALSE;
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

function plugin_local_read($dir) {
	$empty = array('name'=>'', 'version'=>'1.0', 'bbs_version'=>'3.0', 'brief'=>'无', 'installed'=>0, 'dir'=>$dir, 'icon_url'=>'static/plugin_icon.png', 'is_official'=>0);
	if(!is_file("./plugin/$dir/conf.json")) return $empty;
	$plugin = xn_json_decode(file_get_contents("./plugin/$dir/conf.json"));
	$plugin AND $r['dir'] = $dir;
	empty($plugin['installed']) AND $plugin['installed'] = 0;
	$plugin['icon_url'] = "plugin/$dir/icon.png";
	$plugin['setting_url'] = is_file("./plugin/$dir/setting.php") ? "plugin/$dir/setting.php" : '';
	$plugin['dir'] = $dir;
	$plugin['is_official'] = 0;
	return $plugin ? $plugin : $empty;
}

function plugin_read($dir) {
	if(is_dir("./plugin/$dir")) {
		$plugin = plugin_local_read($dir);
	} else {
		$plugin = plugin_official_read($dir);
	}
	return $plugin;
}

function plugin_official_format(&$plugin) {
	if(empty($plugin)) return;
	$dir = $plugin['dir'];
	$plugin['icon_url'] = "http://plugin.xiuno.com/upload/plugin/$plugin[pluginid]/icon.png";
	$plugin['downloaded'] = is_dir("./plugin/$plugin[dir]");
	$plugin['stars_fmt'] = str_repeat('<span class="icon star"></span>', $plugin['stars']);
	$plugin['user_stars_fmt'] = str_repeat('<span class="icon star"></span>', $plugin['user_stars']);

	if(is_dir("./plugin/$dir")) {
		$arr = plugin_local_read($dir);
		$plugin = array_merge($plugin, $arr);
	} else {
		$plugin['installed'] = 0;
		$plugin['setting_url'] = '';
	}
	$plugin['is_official'] = 1;
}
?>