<?php exit;


// 逻辑删除主题
function thread_logic_delete($tid) {
	global $conf;
	$thread = thread__read($tid);
	if(empty($thread)) return TRUE;
	if($thread['deleted'] == 1) return TRUE;

	$fid = $thread['fid'];
	$uid = $thread['uid'];
	
	// 删除我的主题
	//$uid AND mythread_delete($uid, $tid);
	
	// 清除相关缓存
	forum_list_cache_delete();
	
	$r = thread__update($tid, array('deleted'=>1));
	
	// 更新统计
	forum__update($fid, array('threads-'=>1));
	user__update($uid, array('threads-'=>1));
	
	// 全站统计
	runtime_set('threads-', 1);
	
	// 所有的  post.deleted = 1
	$arrlist = db_find('post', array('tid'=>$tid), array(), 1, 10000, '', array('pid'));
	foreach ($arrlist as $arr) {
		$pid = $arr['pid'];
		post__update($pid, array('deleted'=>1));
	}
	
	return $r;
}


// 恢复逻辑删除的主题
function thread_logic_recover($tid) {
	global $conf;
	$thread = thread__read($tid);
	if(empty($thread)) return TRUE;
	if($thread['deleted'] == 0) return TRUE;

	$fid = $thread['fid'];
	$uid = $thread['uid'];

	mythread_create($uid, $tid);
	
	// 清除相关缓存
	forum_list_cache_delete();
	
	$r = thread__update($tid, array('deleted'=>0));
	
	// 更新统计
	forum__update($fid, array('threads+'=>1));
	user__update($uid, array('threads+'=>1));
	
	// 全站统计
	runtime_set('threads+', 1);
	
	// 所有的  post.deleted = 0
	$arrlist = db_find('post', array('tid'=>$tid), array(), 1, 10000, '', array('pid'));
	foreach ($arrlist as $arr) {
		$pid = $arr['pid'];
		post__update($pid, array('deleted'=>0));
	}
	
	return $r;
}

?>