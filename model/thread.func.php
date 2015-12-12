<?php

// ------------> 最原生的 CURD，无关联其他数据。

function thread__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_thread` SET $sqladd");
}

function thread__update($tid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_thread` SET $sqladd WHERE tid='$tid'");
}

function thread__read($tid) {
	return db_find_one("SELECT * FROM `bbs_thread` WHERE tid='$tid'");
}

function thread__delete($tid) {
	return db_exec("DELETE FROM `bbs_thread` WHERE tid='$tid'");
}

function thread__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$arrlist = db_find("SELECT tid FROM `bbs_thread` $cond$orderby LIMIT $offset,$pagesize");
	if(empty($arrlist)) return array();
	$tids = implode(',', arrlist_values($arrlist, 'tid'));
	return db_find("SELECT * FROM `bbs_thread` WHERE tid IN ($tids)");
}

function thread_create($arr, &$pid) {
	global $conf;
	$fid = $arr['fid'];
	$uid = $arr['uid'];
	$subject = $arr['subject'];
	$message = $arr['message'];
	$time = $arr['time'];
	$longip = $arr['longip'];
	$sid = empty($arr['sid']) ? '' : $arr['sid'];
	
	# 论坛帖子数据，一页显示，不分页。
	$post = array(
		'tid'=>0,
		'isfirst'=>1,
		'uid'=>$uid,
		'create_date'=>$time,
		'userip'=>$longip,
		'sid'=>$sid,
		'message'=>$message,
	);
	$pid = post__create($post);
	if($pid === FALSE) return FALSE;
	
	// empty($pid) AND message(1, '创建帖子失败');
	// 创建主题
	$thread = array (
		'fid'=>$fid,
		'subject'=>$subject,
		'uid'=>$uid,
		'create_date'=>$time,
		'last_date'=>$time,
		'firstpid'=>$pid,
		'lastpid'=>$pid,
		'userip'=>$longip,
	);
	
	$tid = thread__create($thread);
	if($tid === FALSE) {
		post__delete($pid);
		return FALSE;
	}
	// 板块总数+1, 用户发帖+1
	
	// 更新统计数据
	$uid AND user__update($uid, array('threads+'=>1));
	forum__update($fid, array('threads+'=>1, 'todaythreads+'=>1));
	
	// 反过来关联
	post__update($pid, array('tid'=>$tid), $tid);

	// 最新发帖
	thread_new_create($tid);
	
	// 我参与的发帖
	$uid AND mythread_create($uid, $tid);
	
	// 更新附件
	$attachlist = attach_find_just_upload($uid);
	foreach($attachlist as $k=>$attach) {
		// 判断是否存在于内容中，不存在，则删除
		$url = $conf['upload_url'].'attach/'.$attach['filename'];
		$file = $conf['upload_path'].'attach/'.$attach['filename'];
		if(strpos($message, $url) === FALSE) {
			attach__delete($attach['aid']);
			is_file($file) AND unlink($file);
			unset($attachlist[$k]);
		} else {
			attach__update($attach['aid'], array('tid'=>$tid, 'pid'=>$pid));
		}
	}
	$images = $files = 0;
	list($images, $files) = attach_images_files($attachlist);
	($images || $files) AND thread__update($tid, array('images'=>$images, 'files'=>$files));
	($images || $files) AND post__update($pid, array('images'=>$images, 'files'=>$files));
	
	// SEO URL
	isset($arr['seo_url']) AND thread_url_create($tid, $arr['seo_url']);
	
	// 全站发帖数
	runtime_set('threads+', 1);
	runtime_set('todaythreads+', 1);
	
	// 清理缓存
	thread_tids_cache_delete($fid);
	thread_new_cache_delete();
	thread_lastpid_create($tid, $pid);
	thread_lastpid_cache_delete();
	
	// 更新板块信息。
	forum_list_cache_delete();
	
	return $tid;
}

// 不要在大循环里调用此函数！比较耗费资源。
function thread_update($tid, $arr) {
	global $conf;
	$thread = thread__read($tid);
	
	if(isset($arr['subject']) && $arr['subject'] != $thread['subject']) {
		thread_new_cache_delete();
		$thread['top'] > 0 AND thread_top_cache_delete();
	}
	
	// 更改 fid, 移动主题，相关资源也需要更新
	if(isset($arr['fid']) && $arr['fid'] != $thread['fid']) {
		forum__update($arr['fid'], array('threads+'=>1));
		forum__update($thread['fid'], array('threads-'=>1));
		thread_top_update_by_tid($tid, $arr['fid']);
		thread_lastpid_cache_delete();
	}
	
	!empty($thread['fid']) AND thread_tids_cache_delete($thread['fid']);
	
	// SEO URL
	if(isset($arr['seo_url'])) {
		thread_url_replace($tid, $arr['seo_url']);
		unset($arr['seo_url']);
	}
	
	if(!$arr) return TRUE;
	
	$r = thread__update($tid, $arr);
	!empty($arr['fid']) AND thread_tids_cache_delete($arr['fid'], TRUE);
	
	return $r;
}

// views + 1
function thread_inc_views($tid, $n = 1) {
	global $conf;
	if(!$conf['update_views_on']) return TRUE;
	$sqladd = strpos($conf['db']['type'], 'mysql') === FALSE ? '' : ' LOW_PRIORITY';
	return db_exec("UPDATE$sqladd `bbs_thread` SET views=views+$n WHERE tid='$tid'");
}

function thread_read($tid) {
	$thread = thread__read($tid);
	thread_format($thread);
	return $thread;
}

// 从缓存中读取，避免重复从数据库取数据，主要用来前端显示，可能有延迟。重要业务逻辑不要调用此函数，数据可能不准确，因为并没有清理缓存，针对 request 生命周期有效。
function thread_read_cache($tid) {
	static $cache = array(); // 用静态变量只能在当前 request 生命周期缓存，要跨进程，可以再加一层缓存： memcached/xcache/apc/
	if(isset($cache[$tid])) return $cache[$tid];
	$cache[$tid] = thread_read($tid);
	return $cache[$tid];
}

// 删除主题
function thread_delete($tid) {
	global $conf;
	$thread = thread__read($tid);
	if(empty($thread)) return TRUE;
	$fid = $thread['fid'];
	$uid = $thread['uid'];
	
	$r = thread__delete($tid);
	if($r === FALSE) return FALSE;
	
	// 删除所有回帖，同时更新 posts 统计数
	$n = post_delete_by_tid($tid);
	
	// 删除我的主题
	$uid AND mythread_delete($uid, $tid);
	
	// 删除附件
	
	// 更新统计
	forum__update($fid, array('threads-'=>1));
	user__update($uid, array('threads-'=>1));
	
	// 删除 SEO URL
	thread_url_delete($tid);
	
	// 全站统计
	runtime_set('threads-', 1);
	
	// 清除相关缓存
	thread_tids_cache_delete($fid);
	forum_list_cache_delete();
	
	// 最新和置顶也清理
	thread_new_delete($tid);
	thread_top_delete($tid);
	thread_lastpid_delete($tid);
	thread_new_cache_delete();
	thread_top_cache_delete();
	thread_lastpid_cache_delete();
	
	return $r;
}

function thread_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$threadlist = thread__find($cond, $orderby, $page, $pagesize);
	if($threadlist) foreach ($threadlist as &$thread) thread_format($thread);
	return $threadlist;
}

// $order: tid/lastpid/agrees
// 按照: 发帖时间/最后回复时间/喜欢数 倒序
function thread_find_by_fid($fid, $page = 1, $pagesize = 20, $order = 'tid') {
	global $conf, $forumlist;
	$key = "forum_tids_{$order}_$fid";
	if($page <= $conf['cache_thread_list_pages']) {
		$tids = cache_get($key);
		if($tids === NULL) {
			$tids = thread_find_tids($fid, $pagesize, $order);
			cache_set($key, $tids);
		}
		$threadlist = thread_find_by_tids($tids, $page, $pagesize, $order); // 对数组分页
	} else {
		$desc = TRUE;
		$limitpage = 50000; // 如果需要防止 CC 攻击，可以调整为 5000
		if($page > 100) {
			$forum = $forumlist[$fid];
			$totalpage = ceil($forum['threads'] / $pagesize);
			$halfpage = ceil($totalpage / 2);
			if($halfpage > $limitpage && $page > $limitpage && $page < ($totalpage - $limitpage)) {
				$page = $limitpage;
			}
			if($page > $halfpage) {
				$page = max(1, $totalpage - $page);
				$threadlist = thread_find(array('fid'=>$fid), array($order=>1), $page, $pagesize);
				$threadlist = array_reverse($threadlist, TRUE);
				$desc = FALSE;
			}
		}
		if($desc) {
			$threadlist = thread_find(array('fid'=>$fid), array($order=>-1), $page, $pagesize);
		}
	}
	// 查找置顶帖
	if($order == $conf['order_default'] && $page == 1) {
		//$toplist3 = thread_top_find(0);
		$toplist3 = array();
		$toplist1 = thread_top_find($fid);
		$threadlist = $toplist3 + $toplist1 + $threadlist;
	}
	return $threadlist;
}

function thread_read_by_seo_url($url) {
	$url = addslashes($url);
	$tid = thread_url_read_by_url($url);
	$thread = $tid ? thread_read($tid) : array();
	return $thread;
}

// 默认搜索标题
function thread_find_by_keyword($keyword) {
	$threadlist = db_find("SELECT * FROM `bbs_thread` WHERE subject LIKE '%$keyword%' LIMIT 60");
	arrlist_multisort($threadlist, 'tid', FALSE); // 用 PHP 排序，mysql 排序消耗太大。
	if($threadlist) {
		foreach ($threadlist as &$thread) {
			thread_format($thread);
			$thread['subject'] = post_highlight_keyword($thread['subject'], $keyword);
		}
	}
	return $threadlist;
}

// 前 10 页 tids
function thread_find_tids($fid, $pagesize = 20, $order = 'tid') {
	global $conf;
	$limit = $pagesize * $conf['cache_thread_list_pages'];
	$tidlist = db_find("SELECT tid FROM `bbs_thread` WHERE fid='$fid' ORDER BY $order DESC LIMIT 0, $limit", 'tid');
	$tids = arrlist_values($tidlist, 'tid');
	return $tids;
}

function thread_find_by_tids($tids, $page = 1, $pagesize = 20, $order = 'tid') {
	$start = ($page - 1) * $pagesize;
	$tids = array_slice($tids, $start, $pagesize);
	if(!$tids) return array();
	$in = implode(',', $tids);
	$threadlist = db_find("SELECT * FROM `bbs_thread` WHERE tid IN ($in) ORDER BY $order DESC", 'tid');
	if($threadlist) foreach($threadlist as &$thread) thread_format($thread);
	return $threadlist;
}

// $order: tid/lastpid/agrees
function thread_tids_cache_delete_by_order($fid, $order = 'tid') {
	$key = "forum_tids_{$order}_$fid";
	cache_delete($key);
}

// 更新三种缓存，如果指定了 $tid ，则判断在缓存内才更新。
function thread_tids_cache_delete($fid, $force = FALSE) {
	global $conf;
	static $deleted = FALSE;
	if($deleted && !$force) return;
	$limit = $conf['cache_thread_list_pages'] * $conf['pagesize'];
	foreach(array('tid', 'lastpid', 'agrees') as $v) {
		thread_tids_cache_delete_by_order($fid, $v);
	}
	$deleted = TRUE;
}

function thread_format(&$thread) {
	global $conf, $forumlist;
	if(empty($thread)) return;
	$thread['create_date_fmt'] = humandate($thread['create_date']);
	$thread['last_date_fmt'] = humandate($thread['last_date']);
	
	$user = user_read_cache($thread['uid']);
	empty($user) AND $user = user_guest();
	$thread['username'] = $user['username'];
	$thread['user_avatar_url'] = $user['avatar_url'];
	
	$forum = $forumlist[$thread['fid']];
	$thread['forumname'] = $forum['name'];
	
	if($thread['last_date'] == $thread['create_date']) {
		//$thread['last_date'] = 0;
		$thread['last_date_fmt'] = '';
		$thread['lastuid'] = 0;
		$thread['lastusername'] = '';
	} else {
		$lastuser = $thread['lastuid'] ? user_read_cache($thread['lastuid']) : array();
		$thread['lastusername'] = $thread['lastuid'] ? $lastuser['username'] : '游客';
	}
	
	$thread['seo_url'] = $conf['seo_url_rewrite'] && $thread['url_on'] ? thread_url_read($thread['tid']) : '';
	$thread['url'] = $thread['seo_url'] ? $thread['seo_url'] : "thread-$thread[tid].htm";
	$thread['user_url'] = "user-$thread[uid]".($thread['uid'] ? '' : "-$thread[firstpid]").".htm";
	
	$n = $thread['agrees'] + $thread['posts'];
	$agree_level = thread_get_level($n, $conf['agrees_level']);
	$thread['posts_class'] = 'posts_'.thread_get_level($thread['posts'], $conf['posts_level']);
	$thread['agrees_class'] = 'agrees_'.$agree_level;
	$thread['thread_class'] = 'thread_agrees_'.$agree_level;
	$thread['top_class'] = $thread['top'] ? 'thread_top_'.$thread['top'] : '';
	
}

function thread_format_last_date(&$thread) {
	if($thread['last_date'] != $thread['create_date']) {
		$thread['last_date_fmt'] = humandate($thread['last_date']);
	} else {
		$thread['create_date_fmt'] = humandate($thread['create_date']);
	}
}

function thread_count($cond = array()) {
	return db_count('bbs_thread', $cond);
}

function thread_maxid() {
	return db_maxid('bbs_thread', 'tid');
}

function thread_get_level($n, $levelarr) {
	foreach($levelarr as $k=>$level) {
		if($n <= $level) return $k;
	}
	return $k;
}

// 检测是否在灌水，如果近期连续发表了5篇主题，或者相同标题的文章，则认为在灌水。
function thread_check_flood($gid, $fid, $subject) {
	global $sid, $uid, $conf;
	if(!$conf['check_flood_on']) return FALSE;
	if($gid > 0 AND $gid < 5) return FALSE;
	$threads = 0;
	$threadlist = thread_find_by_fid($fid, 1, 10, 'tid');
	if(empty($threadlist)) return FALSE;
	foreach ($threadlist as $thread) {
		if($thread['uid'] == $uid || $uid == 0 && $thread['sid'] == $sid) {
			$threads++;
			if($thread['subject'] == $subject) {
				return TRUE;
			}
		}
	}
	if($threads > $conf['check_flood']['threads']) return TRUE;
	return FALSE;
}

// 对 $threadlist 权限过滤
function thread_list_access_filter(&$threadlist, $gid) {
	global $conf, $forumlist;
	if(empty($threadlist)) return;
	foreach($threadlist as $tid=>$thread) {
		if(empty($forumlist[$thread['fid']]['accesson'])) continue;
		if($thread['top'] > 0) continue;
		if(!forum_access_user($thread['fid'], $gid, 'allowread')) {
			unset($threadlist[$tid]);
		}
	}
}

// SEO URL，自定义 URL，拆分为单独的表，减小 thread 表的尺寸。
function thread_url_read($tid) {
	$arr = db_find_one("SELECT * FROM bbs_thread_url WHERE tid='$tid'");
	return $arr ? $arr['url'] : '';
}

function thread_url_read_by_url($url) {
	$arr = db_find_one("SELECT * FROM bbs_thread_url WHERE url='$url'");
	return $arr ? $arr['tid'] : 0;
}

function thread_url_create($tid, $url) {
	if(!$url) return TRUE;
	$r = db_exec("INSERT INTO bbs_thread_url SET tid='$tid', url='$url'");
	$r !== FALSE AND thread__update($tid, array('url_on' => 1));
	return $r;
}

function thread_url_update($tid, $url) {
	$tid = intval($tid);
	$r = db_exec("UPDATE bbs_thread_url SET url='$url' WHERE tid='$tid'");
	$r !== FALSE AND thread__update($tid, array('url_on' => $url ? 1 : 0));
	return $r;
}

function thread_url_delete($tid) {
	$r = db_exec("DELETE FROM bbs_thread_url WHERE tid='$tid'");
	return $r;
}

function thread_url_replace($tid, $url) {
	$arr = thread_url_read($tid);
	if($url) {
		if(empty($arr)) {
			return thread_url_create($tid, $url);
		} else {
			return thread_url_update($tid, $url);
		}
	} else {
		if(!empty($arr)) {
			return thread_url_delete($tid);
		}
		return TRUE;
	}
}

function thread_check_lastpid($tid, $lastpid) {
	// 查找最新的 pid
	$thread = thread_read_cache($tid);
	if(empty($thread)) return;
	if($thread['lastpid'] == $lastpid) {
		$arr = db_find_one("SELECT pid FROM bbs_post WHERE tid='$tid' ORDER BY pid DESC LIMIT 1");
		if(empty($arr)) return;
		$lastpid = $arr['pid'];
		db_exec("UPDATE bbs_thread SET lastpid='$lastpid' WHERE tid='$tid'");
		// 如果在最新主题当中，应该清理掉。
		//thread_lastpid_truncate();
	}
}

?>