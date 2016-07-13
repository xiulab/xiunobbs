<?php

// 过滤关键词，如果返回 FALSE，则包含敏感词，不允许发。
function badword_filter($s, &$badword) {
	// hook badword_filter_start.php
	global $conf;
	if(!$conf['badword_on'] || !$s) return $s;
	
	static $badwords = NULL;
	if($badwords === NULL) $badwords = (array)kv_get('badwords');
	foreach($badwords as $k=>$v) {
		if(strpos($s, $k) !== FALSE) {
			if(isset($v)) {
				$badword = $k;
				if($v == '#') return FALSE;
				$s = str_replace($k, $v, $s);
			} else {
				$s = str_replace($k, '', $s);
			}
		}
	}
	// hook badword_filter_end.php
	return $s;
}

function badword_implode($glue1, $glue2, $arr) {
	// hook badword_implode_start.php
	if(empty($arr)) return '';
	$s = '';
	foreach($arr as $k=>$v) {
		$s .= ($s ? $glue2 : '').$k.($v ? $glue1.$v : '');
	}
	// hook badword_implode_end.php
	return $s;
}

// 对 key-value 数组进行组合
function badword_explode($sep1, $sep2, $s) {
	// hook badword_explode_start.php
	$arr = $arr2 = $arr3 = array();
	$arr = explode($sep2, $s);
	foreach($arr as $v) {
		$arr2 = explode($sep1, $v);
		$arr3[$arr2[0]] = (isset($arr2[1]) ? $arr2[1] : '');
	}
	// hook badword_explode_end.php
	return $arr3;
}

// 谨慎的保存配置文件，先备份，再保存。
function conf_save() {
	// hook conf_save_start.php
	global $conf, $time;
	$file = './conf/conf.php';
	$backfile = './conf/conf-'.date('Y-n-j', $time).'.php';
	
	$s = "<?php\r\nreturn ".var_export($conf,true).";\r\n?>";
	// 备份文件，如果备份失败，则直接返回
	$r = copy($file, $backfile);
	if(!$r) return FALSE;
	$r = file_put_contents($file, $s, LOCK_EX); // 独占锁，防止并发写乱
	if(!$r) {
		copy($backfile, $file); // 还原
		return FALSE;
	}
	// 大致校验是否写入成功
	$s = file_get_content_try($file);
	if(substr(trim($s), -2) != '?>') {
		copy($backfile, $file); // 还原
		return FALSE;
	}
	// hook conf_save_end.php
	return TRUE;
}

// 变量的方式
function conf_set($k, $v, $save = TRUE) {
	// hook conf_set_start.php
	global $conf;
	$conf[$k] = $v;
	// hook conf_set_end.php
	return $save ? conf_save() : TRUE;
}

// 正则的方式修改配置文件，容易被写入 web shell
/*function conf_set($k, $v, $conffile = './conf/conf.php') {
	// hook conf_set_start.php
	$s = file_get_contents($conffile);
	$sep = "\n";
	$s = str_replace("\r\n", $sep, $s);
	$arr = explode($sep, trim($s));
	
	foreach($arr as $line=>&$s) {
		$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\'.*?\',#ism', "'$k' => '$v',", $s);
		$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\d+\s*,#ism', "'$k' => $v,", $s);
	}
	
	$s = implode($sep, $arr);
	return file_put_contents($conffile, $s, LOCK_EX);
}*/

// 正则的方式修改配置文件，不害怕 web shell 写入
function json_conf_set($k, $v, $conffile = './conf.json') {
	$s = file_get_contents($conffile);
	$sep = "\n";
	$s = str_replace("\r\n", $sep, $s);
	$arr = explode($sep, trim($s));
	
	$k2 = preg_quote($k);
	foreach($arr as $line=>&$s) {
		$s = preg_replace('#"'.$k2.'"\s*:\s*".*?"#ism', "\"$k\" : \"$v\"", $s);
		$s = preg_replace('#"'.$k2.'"\s*:\s*\d+\s*#ism', "\"$k\" : $v", $s);
	}
	
	$s = implode($sep, $arr);
	// hook conf_set_end.php
	return file_put_contents($conffile, $s, LOCK_EX);
}

// 正则的方式修改多行
/*
function conf_mset($replacearr, $start = FALSE, $end = FALSE, $conffile = './conf/conf.php') {
	// hook conf_mset_start.php
	$s = file_get_contents($conffile);
	$sep = "\n";
	$s = str_replace("\r\n", $sep, $s);
	$arr = explode($sep, trim($s));
	
	foreach($arr as $line=>&$s) {
		if($start !== FALSE && !($line >= $start && $line <= $end)) continue;
		foreach ($replacearr as $k=>$v) {
			$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\'.*?\',#ism', "'$k' => '$v',", $s);
			$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\d+\s*,#ism', "'$k' => $v,", $s);
		}
	}
	
	$s = implode($sep, $arr);
	// hook conf_mset_end.php
	return file_put_contents($conffile, $s);
}
*/

/*
$s = file_get_contents($conffile);
$s = conf_replace($s, array('sitename'=>$sitename, 'runlevel'=>$runlevel));
file_put_contents($conffile, $s);
*/
/*
function conf_replace($s, $replacearr) {
	// hook conf_replace_start.php
	// 从16行-33行，正则替换
	
	$sep = "\n";
	$s = str_replace("\r\n", $sep, $s);
	$arr = explode($sep, trim($s));
	
	foreach($arr as &$s) {
		foreach($replacearr as $k=>$v) {
			$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\'.*?\',#ism', "'$k' => '$v',", $s);
			$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\d+\s*,#ism', "'$k' => $v,", $s);
		}
	}
	
	$s = implode($sep, $arr);
	// hook conf_replace_end.php
	return $s;
}
*/

/*
function str_line_replace($s, $startline, $endline, $replacearr) {
	// hook str_line_replace_start.php
	// 从16行-33行，正则替换
	empty($startline) AND $startline = 1;
	$sep = "\n";
	$s = str_replace("\r\n", $sep, $s);
	$arr = explode($sep, trim($s));
	$arr1 = array_slice($arr, 0, $startline - 1); // 此处: startline - 1 为长度
	$endline > count($arr)  AND $endline = count($arr);
	$arr2 = array_slice($arr, $startline - 1, $endline - $startline + 1); // 此处: startline - 1 为偏移量
	$arr3 = array_slice($arr, $endline);
	
	foreach($arr2 as &$s) {
		foreach($replacearr as $k=>$v) {
			$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\'.*?\',(\s+)#ism', "'$k' => '$v',\${1}", $s);
			$s = preg_replace('#\''.preg_quote($k).'\'\s*=\>\s*\d+\s*,(\s+)#ism', "'$k' => $v,\${1}", $s);
		}
	}
	$arr = $arr1 + $arr2 + $arr3;
	$s = implode($sep, $arr);
	// hook str_line_replace_end.php
	return $s;
}
*/

// 检测站点的运行级别
function check_runlevel() {
	// hook check_runlevel_start.php
	global $conf, $method, $gid;
	$is_user_action = (param(0) == 'user');
	switch ($conf['runlevel']) {
		case 0: message(-1, $conf['runlevel_reason']); break;
		case 1: $gid != 1 AND message(-1, $conf['runlevel_reason']); break;
		case 2: ($gid == 0 OR ($gid != 1 AND $method != 'GET' AND !$is_user_action)) AND message(-1, '当前站点设置状态：会员只读'); break;
		case 3: $gid == 0 AND !$is_user_action AND message(-1, '当前站点设置状态：会员可读写，游客不允许访问'); break;
		case 4: $method != 'GET' AND message(-1, '当前站点设置状态：所有用户只读'); break;
		//case 5: break;
	}
	// hook check_runlevel_end.php
}

function check_banip($ip) {
	// hook check_banip_start.php
	global $conf, $gid;
	if(empty($conf['banip_on'])) return;
	if($gid == 1) return;
	$r = banip_read_by_ip($ip);
	$r AND message(-1, '您的 IP 已经被禁止。');
	// hook check_banip_end.php
}

function check_standard_browser() {
	// hook check_standard_browser_start.php
	global $browser;
	if($browser['name'] == 'ie' && $browser['version'] < 10) {
		header('Location: browser.htm');
		exit;
		//return FALSE;
	} else {
		//return TRUE;
	}
	// hook check_standard_browser_end.php
}

?>