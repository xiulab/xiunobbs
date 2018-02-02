<?php exit;

if($action == 'digest') {
	
	if($method == 'GET') {
		
		include _include(APP_PATH.'plugin/xn_digest/view/htm/mod_digest.htm');
		
	} else {
		
		$digest = param('digest', 0);
		
		$tidarr = param('tidarr', array(0));
		empty($tidarr) AND message(-1, lang('please_choose_thread'));
		$threadlist = thread_find_by_tids($tidarr);
		
		// hook mod_digest_start.php
		
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
		
		// hook mod_digest_end.php
		
		message(0, lang('set_completely'));
	
	}
}

?>