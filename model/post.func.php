<?php

// hook post_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function post__create($arr) {
	// hook post__create_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("INSERT INTO `bbs_post` SET $sqladd");
	// hook post__create_end.php
	return $r;
}

function post__update($pid, $arr) {
	// hook post__update_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("UPDATE `bbs_post` SET $sqladd WHERE pid='$pid'");
	// hook post__update_end.php
	return $r;
}

function post__read($pid) {
	// hook post__read_start.php
	$post = db_find_one("SELECT * FROM `bbs_post` WHERE pid='$pid'");
	// hook post__read_end.php
	return $post;
}

function post__delete($pid) {
	// hook post__delete_start.php
	$r = db_exec("DELETE FROM `bbs_post` WHERE pid='$pid'");
	// hook post__delete_end.php
	return $r;
}

function post__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook post__find_start.php
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$postlist = db_find("SELECT * FROM `bbs_post` $cond$orderby LIMIT $offset,$pagesize", 'pid');
	// hook post__find_end.php
	return $postlist;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

// 回帖
function post_create($arr, $fid) {
	// hook post_create_start.php
	global $conf, $time;
	$pid = post__create($arr);
	if(!$pid) return $pid;
	
	$tid = $arr['tid'];
	$uid = $arr['uid'];

	// 回帖
	if($tid > 0) {
		thread__update($tid, array('posts+'=>1, 'lastpid'=>$pid, 'lastuid'=>$uid, 'last_date'=>$time));
		$uid AND user__update($uid, array('posts+'=>1));
	
		runtime_set('posts+', 1);
		runtime_set('todayposts+', 1);
		forum__update($fid, array('todayposts+'=>1));
		
		// 最新回复
		thread_lastpid_create($tid, $pid);
		thread_tids_cache_delete_by_order($fid, 'lastpid');
	}
	
	post_list_cache_delete($tid);
	
	// 更新板块信息。
	thread_lastpid_cache_delete();
	forum_list_cache_delete();
	
	// 更新附件
	$attachlist = attach_find_just_upload($uid);
	if($attachlist) {
		foreach($attachlist as $attach) {
			attach__update($attach['aid'], array('tid'=>$tid, 'pid'=>$pid));
		}
		list($images, $files) = attach_images_files($attachlist);
		post__update($pid, array('images'=>$images, 'files'=>$files));
	}
	
	// hook post_create_end.php
	return $pid;
}

// 编辑回帖
function post_update($pid, $arr, $tid = 0) {
	// hook post_update_start.php
	global $conf, $user;
	$post = post__read($pid);
	if(empty($post)) return FALSE;
	$tid = $post['tid'];
	$uid = $post['uid'];
	$isfirst = $post['isfirst'];
	
	$r = post__update($pid, $arr);
	
	post_list_cache_delete($tid);
	
	// 如果 message 发生了变化。
	if(isset($arr['message']) AND $arr['message'] != $post['message']) {
		// 更新附件数
		$oldlist = attach_find_by_pid($pid);
		$newlist = attach_find_just_upload($user['uid']);
		$attachlist = array_merge($oldlist, $newlist);
		foreach($attachlist as $k=>$attach) {
			$url = $conf['upload_url'].'attach/'.$attach['filename'];
			$file = $conf['upload_path'].'attach/'.$attach['filename'];
			if(strpos($arr['message'], $url) === FALSE) {
				attach__delete($attach['aid']);
				is_file($file) AND unlink($file);
				unset($attachlist[$k]);
			} else {
				attach__update($attach['aid'], array('tid'=>$tid, 'pid'=>$pid));
			}
		}
		list($images, $files) = attach_images_files($attachlist);
		post__update($pid, array('images'=>$images, 'files'=>$files));
		thread__update($tid, array('images'=>$images, 'files'=>$files));
	}
	
	// hook post_update_end.php
	return $r;
}

function post_read($pid) {
	// hook post_read_start.php
	$post = post__read($pid);
	post_format($post);
	// hook post_read_end.php
	return $post;
}

// 从缓存中读取，避免重复从数据库取数据，主要用来前端显示，可能有延迟。重要业务逻辑不要调用此函数，数据可能不准确，因为并没有清理缓存，针对 request 生命周期有效。
function post_read_cache($pid) {
	// hook post_read_cache_start.php
	static $cache = array(); // 用静态变量只能在当前 request 生命周期缓存，要跨进程，可以再加一层缓存： memcached/xcache/apc/
	if(isset($cache[$pid])) return $cache[$pid];
	$cache[$pid] = post_read($pid);
	// hook post_read_cache_end.php
	return $cache[$pid];
}

// $tid 用来清理缓存
function post_delete($pid) {
	// hook post_delete_start.php
	global $conf;
	$post = post_read_cache($pid);
	if(empty($post)) return TRUE; // 已经不存在了。
	
	$tid = $post['tid'];
	$uid = $post['uid'];
	$thread = thread_read_cache($tid);
	$fid = $thread['fid'];
	
	$r = post__delete($pid);
	if($r === FALSE) return FALSE;
	
	!$post['isfirst'] AND thread__update($tid, array('posts-'=>1));
	!$post['isfirst'] AND $uid AND user__update($uid, array('posts-'=>1));
	
	!$post['isfirst'] AND runtime_set('posts-', 1);
	
	// 清理缓存
	$post['isfirst'] AND post_list_cache_delete($tid);
	
	($post['images'] || $post['files']) AND attach_delete_by_pid($pid);
	
	// 检查 lastpid
	thread_check_lastpid($tid, $pid);
	
	// hook post_delete_end.php
	return $r;
}

// 此处有可能会超时
function post_delete_by_tid($tid) {
	// hook post_delete_by_tid_start.php
	$postlist = post_find_by_tid($tid);
	foreach($postlist as $post) {
		post_delete($post['pid']);
	}
	// hook post_delete_by_tid_end.php
	return count($postlist);
}

// 这些 pid 都是同一个 tid 下的，它与 post_delete() 是同级关系，不能互相调用。
/*
function post_delete_by_pids($pids) {
	// hook post_delete_by_pids_start.php
	if(empty($pids)) return TRUE;
	$sqladd = implode(',', $pids);
	$n = count($pids);
	$postlist = post_find_by_pids($pids);
	$tidarr = $uidarr = array();
	foreach($postlist as $post) {
		if($post['isfirst']) continue;
		!isset($tidarr[$post['tid']]) AND $tidarr[$post['tid']] = 0;
		 $tidarr[$post['tid']]++;
		!isset($uidarr[$post['uid']]) AND $uidarr[$post['uid']] = 0;
		$uidarr[$post['uid']]++;
	}
	foreach($tidarr as $tid=>$n) {
		thread_update($tid, array('posts-'=>$n));
		post_list_cache_delete($tid);
	}
	foreach($uidarr as $uid=>$n) {
		$uid AND user_update($uid, array('posts-'=>$n));
		
	}
	// hook post_delete_by_pids_end.php
	return db_exec("DELETE FROM post WHERE pid IN($sqladd)");
}
*/

function post_find_by_pids($pids) {
	// hook post_find_by_pids_start.php
	if(empty($pids)) return array();
	$sqladd = implode(',', $pids);
	$postlist = db_find("SELECT * FROM bbs_post WHERE pid IN($sqladd)");
	// hook post_find_by_pids_end.php
	return $postlist;
}

function post_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook post_find_start.php
	$postlist = post__find($cond, $orderby, $page, $pagesize);
	$floor = 0;
	if($postlist) foreach($postlist as &$post) {
		$post['floor'] = $floor++;
		post_format($post);
	}
	// hook post_find_end.php
	return $postlist;
}

function post_find_by_tid($tid, $pagesize = 0) {
	// hook post_find_by_tid_start.php
	global $conf;
	empty($pagesize) AND $pagesize = $conf['postlist_pagesize'];

	$key = "postlist_$tid";
	$postlist = cache_get($key);
	if($postlist === NULL) {
		$postlist = post__find(array('tid'=>$tid), array('pid'=>1), 1, $pagesize);
		cache_set($key, $postlist);
	}
	if($postlist) {
		$floor = 0;
		foreach($postlist as &$post) {
			$post['floor'] = $floor++;
			post_format($post);
		}
	}
	// hook post_find_by_tid_end.php
	return $postlist;
}

function post_list_cache_delete($tid) {
	// hook post_list_cache_delete_start.php
	global $conf;
	$r = cache_delete("postlist_$tid");
	// hook post_list_cache_delete_end.php
	return $r;
}

// ------------> 其他方法

function post_format(&$post) {
	// hook post_format_start.php
	global $conf;
	if(empty($post)) return;
	$post['create_date_fmt'] = humandate($post['create_date']);
	
	$user = $post['uid'] ? user_read_cache($post['uid']) : user_guest();
	$post['username'] = $user['username'];
	$post['user_avatar_url'] = $user['avatar_url'];
	$post['user'] = $user;
	!isset($post['floor']) AND  $post['floor'] = '';
	
	// 权限判断
	global $uid, $sid, $longip;
	$post['allowupdate'] = (($uid != 0 && $uid == $post['uid']) || ($uid == 0 && $post['uid'] == 0 && $post['userip'] == $longip && $post['sid'] == $sid)) ? 1 : 0;
	$post['allowdelete'] = (($uid != 0 && $uid == $post['uid']) || ($uid == 0 && $post['uid'] == 0 && $post['userip'] == $longip && $post['sid'] == $sid)) ? 1 : 0;
	
	$post['user_url'] = "user-$post[uid]".($post['uid'] ? '' : "-$post[pid]").".htm";
	// hook post_format_end.php
}

function post_count($cond = array()) {
	// hook post_count_start.php
	$n = db_count('bbs_post', $cond);
	// hook post_count_end.php
	return $n;
}

function post_maxid() {
	// hook post_maxid_start.php
	$n = db_maxid('bbs_post', 'pid');
	// hook post_maxid_end.php
	return $n;
}

function post_highlight_keyword($str, $k) {
	// hook post_highlight_keyword_start.php
	$r = str_ireplace($k, '<span class="red">'.$k.'</span>', $str);
	// hook post_highlight_keyword_end.php
	return $r;
}


// 检测是否在灌水，如果近期连续发表了5篇主题，或者相同标题的文章，则认为在灌水。
function post_check_flood($gid, $tid, $message) {
	// hook post_check_flood_start.php
	global $sid, $uid, $conf;
	if(!$conf['check_flood_on']) return FALSE;
	if($gid > 0 AND $gid < 5) return FALSE;
	
	$posts = 0;
	$postlist = post_find_by_tid($tid);
	if(empty($postlist)) return FALSE;
	$postlist = array_slice($postlist, -20, 20);
	foreach ($postlist as $post) {
		if($post['uid'] == $uid || $uid == 0 && $post['sid'] == $sid) {
			$posts++;
			if($post['message'] == $message) {
				return TRUE;
			}
		}
	}
	if($posts > $conf['check_flood']['posts']) return TRUE;
	// hook post_check_flood_end.php
	return FALSE;
}

// 将不存在的附件加入到 message
function post_attach_list_add($imagelist, $filelist) {
	// hook post_attach_list_add_start.php
	$s = '';
	if($imagelist || $filelist) {
		$s = '<br>';
		$s .= '<p class="margin">附件列表：</p>';
		$s .= '<p class="hr"></p>';
		$s .= '<ul>';
		foreach ($imagelist as $attach) {
			$s .= '<li><a href="upload/attach/'.$attach['filename'].'" target="_blank"><img src="upload/attach/'.$attach['filename'].'" width="'.$attach['width'].'" height="'.$attach['height'].'" /></a></li>';
		}
		foreach ($filelist as $file) {
			$s .= '<li><i class="icon filetype '.$attach['filetype'].' small"></i> <a href="upload/attach/'.$attach['filename'].'" target="_blank">'.$attach['orgfilename'].'</a></li>';
		}
		$s .= '</ul>';
	}
	$s = htmlspecialchars($s);
	// hook post_attach_list_add_end.php
	return $s;
}


// hook post_func_php_end.php

?>