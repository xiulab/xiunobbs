<?php

// hook attach_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function attach__create($arr) {
	// hook attach__create_start.php
	$r = db_create('attach', $arr);
	// hook attach__create_end.php
	return $r;
}

function attach__update($aid, $arr) {
	// hook attach__update_start.php
	$r = db_update('attach', array('aid'=>$aid), $arr);
	// hook attach__update_end.php
	return $r;
}

function attach__read($aid) {
	// hook attach__read_start.php
	$attach = db_find_one('attach', array('aid'=>$aid));
	// hook attach__read_end.php
	return $attach;
}

function attach__delete($aid) {
	// hook attach__delete_start.php
	$r = db_delete('attach', array('aid'=>$aid));
	// hook attach__delete_end.php
	return $r;
}

function attach__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook attach__find_start.php
	$attachlist = db_find('attach', $cond, $orderby, $page, $pagesize);
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
	list($attachlist, $imagelist, $filelist) = attach_find_by_pid($pid);
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

// 获取 $filelist $imagelist
function attach_find_by_pid($pid) {
	// hook attach_find_by_pid_start.php
	$attachlist = $imagelist = $filelist = array();
	$attachlist = attach__find(array('pid'=>$pid), array(), 1, 1000);
	if($attachlist) {
		foreach ($attachlist as $attach) {
			attach_format($attach);
			$attach['isimage'] ? ($imagelist[] = $attach) : ($filelist[] = $attach);
		}
	}
	// hook attach_find_by_pid_end.php
	return array($attachlist, $imagelist, $filelist);
}

// ------------> 其他方法

function attach_format(&$attach) {
	global $conf;
	// hook attach_format_start.php
	if(empty($attach)) return;
	$attach['create_date_fmt'] = date('Y-n-j', $attach['create_date']);
	$attach['url'] = $conf['upload_url'].'attach/'.$attach['filename'];
	// hook attach_format_end.php
}

function attach_count($cond = array()) {
	// hook attach_count_start.php
	$cond = db_cond_to_sqladd($cond);
	$n = db_count('attach', $cond);
	// hook attach_count_end.php
	return $n;
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

// 扫描垃圾的附件，每日清理一次
function attach_gc() {
	// hook attach_gc_start.php
	global $time, $conf;
	$tmpfiles = glob($conf['upload_path'].'tmp/*.*');
	foreach ($tmpfiles as $file) {
		// 清理超过一天还没处理的临时文件
		if($time - filemtime($file) > 86400) {
			unlink($file);
		}
	}
	// hook attach_gc_end.php
}

// 关联 session 中的临时文件，并不会重新统计 images, files
function attach_assoc_post($pid) {
	global $uid, $time, $conf;
	$tmp_files = _SESSION('tmp_files');
	if(empty($tmp_files)) return;
	
	$post = post__read($pid);
	if(empty($post)) return;
	
	$tid = $post['tid'];
	foreach($tmp_files as $key=>$file) {
		
		// 将文件移动到 upload/attach 目录
		$filename = file_name($file['url']);
		
		$day = date('Ymd', $time);
		$path = $conf['upload_path'].'attach/'.$day;
		$url = $conf['upload_url'].'attach/'.$day;
		!is_dir($path) AND mkdir($path, 0777, TRUE);
		
		$destfile = $path.'/'.$filename;
		$desturl = $url.'/'.$filename;
		if(!copy($file['path'], $destfile)) {
			continue;
			//message(-1, $file['path']." ".$destfile.(file_exists($file['path'])).file_exists($destfile));
		}
		if(filesize($destfile) != filesize($file['path'])) {
			continue;
		}
		
		$arr = array(
			'tid'=>$tid,
			'pid'=>$pid,
			'uid'=>$uid,
			'filesize'=>$file['filesize'],
			'width'=>$file['width'],
			'height'=>$file['height'],
			'filename'=>"$day/$filename",
			'orgfilename'=>$file['orgfilename'],
			'filetype'=>$file['filetype'],
			'create_date'=>$time,
			'comment'=>'',
			'downloads'=>0,
			'isimage'=>$file['isimage']
		);
		
		// 插入后，进行关联
		$aid = attach_create($arr);
		$post['message_new'] = str_replace($file['url'], $desturl, $post['message']);
		
		unset($_SESSION['tmp_files'][$key]);
	}
	$post['message_new'] != $post['message'] AND post__update($pid, array('message'=>$post['message_new']));
	
	// 处理不在 message 中的图片，删除掉没有插入的图片附件
	list($attachlist, $imagelist, $filelist) = attach_find_by_pid($pid);
	foreach($imagelist as $k=>$attach) {
		$url = $conf['upload_url'].'attach/'.$attach['filename'];
		if(strpos($post['message_new'], $url) === FALSE) {
			unset($attachlist[$k]);
			unset($imagelist[$k]);
			attach_delete($attach['aid']);
		}
	}
	
	// 更新 images files
	$images = count($imagelist);
	$files = count($filelist);
	$post['isfirst'] AND thread__update($tid, array('images'=>$images, 'files'=>$files));
	post__update($pid, array('images'=>$images, 'files'=>$files));
	
	return TRUE;
}


// hook attach_func_php_end.php

?>