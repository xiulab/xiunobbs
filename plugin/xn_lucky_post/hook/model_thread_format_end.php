if($thread['is_lucky_thread']) {
	$arr = db_find_one('thread_lucky_post', array('tid'=>$thread['tid']));
	$thread['lucky_pids'] = $arr['pids'];
	$thread['lucky_pid_arr'] = explode(',', $arr['pids']);
	$thread['success_template'] = $arr['success_template'];
} else {
	$thread['lucky_pids'] = '';
	$thread['success_template'] = '';
	$thread['lucky_pid_arr'] = array();
}