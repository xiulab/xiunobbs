 <?php exit;

	$type = param('type', 0);
	foreach($threadlist as &$thread) {
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		if(forum_access_mod($fid, $gid, 'allowdelete')) {
			// 逻辑删除
			if($type == 0) {
				thread_logic_delete($tid);
			// 物理删除
			} else if($type == 1) {
				if($group['allowharddelete']) {
					thread_delete($tid);
					$arr = array(
						'uid' => $uid,
						'tid' => $thread['tid'],
						'pid' => $thread['firstpid'],
						'subject' => $thread['subject'],
						'comment' => '',
						'create_date' => $time,
						'action' => 'delete',
					);
					modlog_create($arr);
				} else {
					thread_logic_delete($tid);
				}
			// 逻辑恢复
			} elseif($type == 2) {
				thread_logic_recover($tid);
			}
		}
	}
	
	if($type == 2) {
		message(0, '恢复成功');
	} else {
		message(0, lang('delete_completely'));
	}
?>