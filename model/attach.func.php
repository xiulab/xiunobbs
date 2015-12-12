<?php

// ------------> 最原生的 CURD，无关联其他数据。

function attach__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_attach` SET $sqladd");
}

function attach__update($aid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_attach` SET $sqladd WHERE aid='$aid'");
}

function attach__read($aid) {
	return db_find_one("SELECT * FROM `bbs_attach` WHERE aid='$aid'");
}

function attach__delete($aid) {
	return db_exec("DELETE FROM `bbs_attach` WHERE aid='$aid'");
}

function attach__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_attach` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function attach_create($arr) {
	$r = attach__create($arr);
	return $r;
}

function attach_update($aid, $arr) {
	$r = attach__update($aid, $arr);
	return $r;
}

function attach_read($aid) {
	$attach = attach__read($aid);
	attach_format($attach);
	return $attach;
}

function attach_delete($aid) {
	global $conf;
	$attach = attach_read($aid);
	$path = $conf['upload_path'].'attach/'.$attach['filename'];
	file_exists($path) AND unlink($path);
	
	$r = attach__delete($aid);
	return $r;
}

function attach_delete_by_pid($pid) {
	global $conf;
	$attachlist = attach_find_by_pid($pid);
	foreach($attachlist as $attach) {
		$path = $conf['upload_path'].'attach/'.$attach['filename'];
		file_exists($path) AND unlink($path);
		attach__delete($attach['aid']);
	}
	return count($attachlist);
}

function attach_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$attachlist = attach__find($cond, $orderby, $page, $pagesize);
	if($attachlist) foreach ($attachlist as &$attach) attach_format($attach);
	return $attachlist;
}

function attach_find_by_pid($pid) {
	$attachlist = attach__find(array('pid'=>$pid), array(), 1, 1000);
	if($attachlist) foreach ($attachlist as &$attach) attach_format($attach);
	return $attachlist;
}

// 查找还没有关联的附件
function attach_find_just_upload($uid) {
	return attach_find(array('pid'=>0, 'uid'=>$uid), array(), 1, 1000);
}

// ------------> 其他方法

function attach_format(&$attach) {
	if(empty($attach)) return;
	$attach['create_date_fmt'] = date('Y-n-j', $attach['create_date']);
}

function attach_count($cond = array()) {
	$cond = cond_to_sqladd($cond);
	$arr = db_find_one("SELECT COUNT(*) AS num FROM `bbs_attach` $cond");
	return $arr['num'];
}

function attach_images_files($attachlist) {
	$images = $files = 0;
	foreach($attachlist as $attach) {
		$attach['isimage'] ? $images++ : $files++;
	}
	return array($images, $files);	
}

// 不在帖子内容中的附件列表
function attach_list_not_in_message($attachlist, $message) {
	global $conf;
	$imagelist = $filelist = array();
	foreach($attachlist as $attach) {
		$url = $conf['upload_url'].'attach/'.$attach['filename'];
		if(strpos($message, $url) === FALSE) {
			$attach['isimage'] ? ($imagelist[] = $attach) : ($filelist[] = $attach);
		}
	}
	return array($imagelist, $filelist);
}

// 120 长度 12345678901_ 11 + .xxxx 4 20150723/ 9
function attach_safe_name($name, $whitearr) {
	global $time;
	$ext = file_ext($name);
	$pre = file_pre($name);
	$pre = xn_urlencode($pre);
	$pre = substr($pre, 0, 89).'_'.$time; // 时间放到后面，好根据文件名前缀进行管理，比如 rm -rf 123_aaa*
	$ext = xn_urlencode($ext);
	!in_array($ext, $whitearr) AND $ext = '_'.$ext;
	return $pre.'.'.$ext;
}

function attach_type($name, $types) {
	$ext = file_ext($name);
	foreach($types as $type=>$exts) {
		if($type == 'all') continue;
		if(in_array($ext, $exts)) {
			return $type;
		}
	}
	return 'other';
}

// 扫描垃圾的附件
function attach_gc() {
	global $time, $conf;
	$attachlist = db_find("SELECT * FROM bbs_attach WHERE pid='0'");
	if(empty($attachlist)) return;
	foreach($attachlist as $attach) {
		// 如果是 1 天内的附件，则不处理，可能正在发帖
		if($time - $attach['create_date'] < 86400) continue;
		$filepath = $conf['upload_path'].$attach['filename'];
		is_file($filepath) AND unlink($filepath);
	}
}

?>