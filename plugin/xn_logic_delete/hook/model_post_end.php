<?php exit;

// 关联的资源和操作都受到影响。
// 不能下载附件
function post_logic_delete($pid) {
	global $conf;
	$post = post_read_cache($pid);
	if(empty($post)) return TRUE; // 已经不存在了。
	
	$tid = $post['tid'];
	$uid = $post['uid'];
	$thread = thread_read_cache($tid);
	$fid = $thread['fid'];
	
	if(!$post['isfirst']) {
		//thread__update($tid, array('posts-'=>1));
		$uid AND user__update($uid, array('posts-'=>1));
		runtime_set('posts-', 1);
	} else {
		//post_list_cache_delete($tid);
	}
	
	// ($post['images'] || $post['files']) AND attach_delete_by_pid($pid);
	
	// $r = post__delete($pid);
	$r = post__update($pid, array('deleted'=>1));
	
	return $r;
}




function post_logic_recover($pid) {
	global $conf;
	$post = post_read_cache($pid);
	if(empty($post)) return TRUE; // 已经不存在了。
	
	$tid = $post['tid'];
	$uid = $post['uid'];
	$thread = thread_read_cache($tid);
	$fid = $thread['fid'];
	
	if(!$post['isfirst']) {
		//thread__update($tid, array('posts+'=>1));
		$uid AND user__update($uid, array('posts+'=>1));
		runtime_set('posts+', 1);
	} else {
		//post_list_cache_delete($tid);
	}
	
	// $r = post__delete($pid);
	$r = post__update($pid, array('deleted'=>0));
	
	return $r;
}


?>