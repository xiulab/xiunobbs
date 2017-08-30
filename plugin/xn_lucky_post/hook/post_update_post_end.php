<?php exit;

if($isfirst) {
	if($thread['is_lucky_thread']) {
		// 对 id 进行处理
		$lucky_pids = trim(param('lucky_pids'));
		$success_template = trim(param('success_template'));
		$lucky_pids = str_replace(array('，'), ',', $lucky_pids);
		$lucky_pids = str_replace(array('　', ' '), '', $lucky_pids);
		$lucky_arr = explode(',', $lucky_pids);
		foreach ($lucky_arr as &$v) {
			$v = abs(intval($v));
		}
		sort($lucky_arr);
		$lucky_pids = implode(',', $lucky_arr);
		$arr = array(
			'pids'=>$lucky_pids,
			'success_template'=>$success_template,
		);
		$r = db_update('thread_lucky_post', array('tid'=>$tid), $arr);
	}
}

?>