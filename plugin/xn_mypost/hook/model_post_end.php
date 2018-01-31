
// 此处有缓存，是否有必要？
function post_find_by_uid($uid, $page = 1, $pagesize = 50) {
	global $conf;
	
	// hook model_post_find_by_uid_start.php
	
	$arrlist = db_find('post', array('uid'=>$uid), array('pid'=>-1), $page, $pagesize, '', array('pid'));
	$pids = arrlist_values($arrlist, 'pid');
	$postlist = post_find_by_pids($pids);
	$postlist = arrlist_multisort($postlist, 'pid', FALSE);
	
	foreach($postlist as $k=>&$post) {
		user_post_message_format($post['message_fmt']);
		$post['filelist'] = array();
		$post['floor'] = 0; // 默认
		$thread = thread_read_cache($post['tid']);
		$post['subject'] = $thread['subject'];
		// 干掉主题帖
		if($post['isfirst']) {
			//unset($postlist[$k]);
		}
	}
	
	// hook model_post_find_by_uid_end.php
	return $postlist;
}