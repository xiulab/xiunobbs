<?php exit;

if($action == 'digest') {
	
	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, lang('please_choose_thread'));
	
	$digest = param('digest');
	
	$threadlist = thread_find_by_tids($tidarr);
	if($digest == 2 || $digest == 3) {
		$username = $user['username'];
		thread_digest_system_send($digest,$threadlist);
	}
	
	foreach($threadlist as &$thread) {
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		if(forum_access_mod($fid, $gid, 'allowtop')) {
			thread_digest_change($tid, $digest, $thread['uid'], $thread['fid']);
			$arr = array(
				'uid' => $uid,
				'tid' => $thread['tid'],
				'pid' => $thread['firstpid'],
				'subject' => $thread['subject'],
				'comment' => '',
				'create_date' => $time,
				'action' => 'digest',
			);
			modlog_create($arr);
		}
	}
	
	message(0, lang('set_completely'));
	
}

?>