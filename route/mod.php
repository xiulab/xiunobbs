<?php

!defined('DEBUG') AND exit('Access Denied.');

include _include(APP_PATH.'model/modlog.func.php');

$action = param(1);

($method != 'POST') AND message(-1, 'Method Error');

// hook mod_start.php

if($action == 'top') {

	// hook mod_top_start.php
	
	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, lang('please_choose_thread'));
	
	$top = param('top');
	
	$threadlist = thread_find_by_tids($tidarr);
	
	foreach($threadlist as &$thread) {
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		if(forum_access_mod($fid, $gid, 'allowtop')) {
			thread_top_change($tid, $top);
			$arr = array(
				'uid' => $uid,
				'tid' => $thread['tid'],
				'pid' => $thread['firstpid'],
				'subject' => $thread['subject'],
				'comment' => '',
				'create_date' => $time,
				'action' => 'top',
			);
			modlog_create($arr);
			
		}
	}
	
	// hook mod_top_end.php
	
	message(0, lang('set_completely'));
	
} elseif($action == 'delete') {

	// hook mod_delete_start.php
	
	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, lang('please_choose_thread'));
	
	$threadlist = thread_find_by_tids($tidarr);
	
	foreach($threadlist as &$thread) {
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		if(forum_access_mod($fid, $gid, 'allowdelete')) {
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
		}
	}
	
	// hook mod_delete_end.php
	
	message(0, lang('delete_completely'));
	
} elseif($action == 'move') {

	// hook mod_move_start.php
	
	$tids = param(2);
	$newfid = param(3);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, lang('please_choose_thread'));

	!forum_read($newfid) AND message(1, lang('forum_not_exists'));
	
	$threadlist = thread_find_by_tids($tidarr);
	
	foreach($threadlist as &$thread) {
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		if(forum_access_mod($fid, $gid, 'allowmove')) {
			thread_update($tid, array('fid'=>$newfid));
			$arr = array(
				'uid' => $uid,
				'tid' => $thread['tid'],
				'pid' => $thread['firstpid'],
				'subject' => $thread['subject'],
				'comment' => '',
				'create_date' => $time,
				'action' => 'move',
			);
			modlog_create($arr);
		}
	}
	
	// hook mod_move_end.php
	
	message(0, lang('move_completely'));
	
} elseif($action == 'deleteuser') {
	
	// hook mod_delete_user_start.php
	
	$_uid = param(2, 0);
	
	$method != 'POST' AND message(-1, 'Method error');
	
	empty($group['allowdeleteuser']) AND message(-1, lang('insufficient_delete_user_privilege'));
	
	$u = user_read($_uid);
	empty($u) AND message(-1, lang('user_not_exists_or_deleted'));
	
	$u['gid'] < 6 AND message(-1, lang('cant_delete_admin_group'));
	
	$r = user_delete($_uid);
	$r === FALSE AND message(-1, lang('delete_failed'));

	// hook mod_delete_user_end.php
	
	message(0, lang('delete_successfully'));
}

// hook mod_end.php

?>