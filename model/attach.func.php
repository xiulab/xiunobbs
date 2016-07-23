<?php

// hook attach_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function attach__create($arr) {
	// hook attach__create_start.php
	$r = db_create('bbs_attach', $arr);
	// hook attach__create_end.php
	return $r;
}

function attach__update($aid, $arr) {
	// hook attach__update_start.php
	$r = db_update('bbs_attach', array('aid'=>$aid), $arr);
	// hook attach__update_end.php
	return $r;
}

function attach__read($aid) {
	// hook attach__read_start.php
	$attach = db_find_one('bbs_attach', array('aid'=>$aid));
	// hook attach__read_end.php
	return $attach;
}

function attach__delete($aid) {
	// hook attach__delete_start.php
	$r = db_delete('bbs_attach', array('aid'=>$aid));
	// hook attach__delete_end.php
	return $r;
}

function attach__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook attach__find_start.php
	$attachlist = db_find('bbs_attach', $cond, $orderby, $page, $pagesize);
	// hook attach__find_end.php
	return $attachlist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function attach_create($arr) {
	// hook attach_create_start.php
	$r = attach__create($arr);
	// hook attach_create_end.php
	return $r;
}

function attach_update($aid, $arr) {
	// hook attach_update_start.php
	$r = attach__update($aid, $arr);
	// hook attach_update_end.php
	return $r;
}

function attach_read($aid) {
	// hook attach_read_start.php
	$attach = attach__read($aid);
	attach_format($attach);
	// hook attach_read_end.php
	return $attach;
}

function attach_delete($aid) {
	// hook attach_delete_start.php
	global $conf;
	$attach = attach_read($aid);
	$path = $conf['upload_path'].'attach/'.$attach['filename'];
	file_exists($path) AND unlink($path);
	
	$r = attach__delete($aid);
	// hook attach_delete_end.php
	return $r;
}

function attach_delete_by_pid($pid) {
	// hook attach_delete_by_pid_start.php
	global $conf;
	$attachlist = attach_find_by_pid($pid);
	foreach($attachlist as $attach) {
		$path = $conf['upload_path'].'attach/'.$attach['filename'];
		file_exists($path) AND unlink($path);
		attach__delete($attach['aid']);
	}
	// hook attach_delete_by_pid_end.php
	return count($attachlist);
}

function attach_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook attach_find_start.php
	$attachlist = attach__find($cond, $orderby, $page, $pagesize);
	if($attachlist) foreach ($attachlist as &$attach) attach_format($attach);
	// hook attach_find_end.php
	return $attachlist;
}

function attach_find_by_pid($pid) {
	// hook attach_find_by_pid_start.php
	$attachlist = attach__find(array('pid'=>$pid), array(), 1, 1000);
	if($attachlist) foreach ($attachlist as &$attach) attach_format($attach);
	// hook attach_find_by_pid_end.php
	return $attachlist;
}

// 查找还没有关联的附件
function attach_find_just_upload($uid) {
	// hook attach_find_just_upload_start.php
	// hook attach_find_just_upload_end.php
	return attach_find(array('pid'=>0, 'uid'=>$uid), array(), 1, 1000);
}

// ------------> 其他方法

function attach_format(&$attach) {
	// hook attach_format_start.php
	if(empty($attach)) return;
	$attach['create_date_fmt'] = date('Y-n-j', $attach['create_date']);
	// hook attach_format_end.php
}

function attach_count($cond = array()) {
	// hook attach_count_start.php
	$cond = cond_to_sqladd($cond);
	$n = db_count('bbs_attach', $cond);
	// hook attach_count_end.php
	return $n;
}

function attach_images_files($attachlist) {
	// hook attach_images_files_start.php
	$images = $files = 0;
	foreach($attachlist as $attach) {
		$attach['isimage'] ? $images++ : $files++;
	}
	// hook attach_images_files_end.php
	return array($images, $files);	
}

// 不在帖子内容中的附件列表
function attach_list_not_in_message($attachlist, $message) {
	// hook attach_list_not_in_message_start.php
	global $conf;
	$imagelist = $filelist = array();
	foreach($attachlist as $attach) {
		$url = $conf['upload_url'].'attach/'.$attach['filename'];
		if(strpos($message, $url) === FALSE) {
			$attach['isimage'] ? ($imagelist[] = $attach) : ($filelist[] = $attach);
		}
	}
	// hook attach_list_not_in_message_end.php
	return array($imagelist, $filelist);
}

// 120 长度 12345678901_ 11 + .xxxx 4 20150723/ 9
function attach_safe_name($name, $whitearr) {
	// hook attach_safe_name_start.php
	global $time;
	$ext = file_ext($name);
	$pre = file_pre($name);
	$pre = xn_urlencode($pre);
	$pre = substr($pre, 0, 89).'_'.$time; // 时间放到后面，好根据文件名前缀进行管理，比如 rm -rf 123_aaa*
	$ext = xn_urlencode($ext);
	!in_array($ext, $whitearr) AND $ext = '_'.$ext;
	// hook attach_safe_name_end.php
	return $pre.'.'.$ext;
}

function attach_type($name, $types) {
	// hook attach_type_start.php
	$ext = file_ext($name);
	foreach($types as $type=>$exts) {
		if($type == 'all') continue;
		if(in_array($ext, $exts)) {
			return $type;
		}
	}
	// hook attach_type_end.php
	return 'other';
}

// 扫描垃圾的附件
function attach_gc() {
	// hook attach_gc_start.php
	global $time, $conf;
	$attachlist = db_find('bbs_attach', array('pid'=>0));
	if(empty($attachlist)) return;
	foreach($attachlist as $attach) {
		// 如果是 1 天内的附件，则不处理，可能正在发帖
		if($time - $attach['create_date'] < 86400) continue;
		$filepath = $conf['upload_path'].$attach['filename'];
		is_file($filepath) AND unlink($filepath);
	}
	// hook attach_gc_end.php
}


// hook attach_func_php_end.php

?>