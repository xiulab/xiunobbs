<?php

!defined('DEBUG') AND exit('Access Denied.');

include './model/modlog.func.php';

$action = param(1);

if($action == 'top') {

	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, '请选择主题');
	
	$header['title'] = '置顶';
	
	if($method == 'GET') {

		// 选中第一个
		$tid = $tidarr[0];
		$thread = thread_read($tid);
		
		include './pc/view/mod_top.htm';

	} else if($method == 'POST') {

		$top = param('top');
		
		$threadlist = thread_find_by_tids($tidarr, 1, 20);
		
		// 设置置顶
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
		
		message(0, '设置完成');
	}
	
} elseif($action == 'delete') {

	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, '请选择主题');
	
	$header['title'] = '删除';
	
	if($method == 'GET') {

		include './pc/view/mod_delete.htm';

	} else if($method == 'POST') {

		$threadlist = thread_find_by_tids($tidarr, 1, 1000);
		
		// 设置置顶
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
		
		message(0, '删除完成');
	}
	
} elseif($action == 'move') {

	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, '请选择主题');
	
	$header['title'] = '移动';
	
	if($method == 'GET') {

		// 选中第一个
		$tid = $tidarr[0];
		$thread = thread_read($tid);
		
		include './pc/view/mod_move.htm';

	} else if($method == 'POST') {

		$newfid = param('newfid', 0);
		
		!forum_read($newfid) AND message(1, '板块不存在');
		
		$threadlist = thread_find_by_tids($tidarr, 1, 1000);
		
		// 设置置顶
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
		
		message(0, '移动完成');
	}
	
} elseif($action == 'deleteuser') {
	
	$_uid = param(2, 0);
	
	$method != 'POST' AND message(-1, 'Method error');
	
	empty($group['allowdeleteuser']) AND message(-1, '您无权删除用户');
	
	$u = user_read($_uid);
	empty($u) AND message(-1, '用户不存在或者已经被删除。');
	
	$u['gid'] < 6 AND message(-1, '不允许删除管理组，请先调整用户用户组。');
	
	$r = user_delete($_uid);
	$r === FALSE ? message(-1, '删除失败') : message(0, '删除成功');
	
} elseif($action == 'banip') {
	
	$method != 'POST' AND message(-1, 'Method error');
	
	$_ip = xn_urldecode(param(2));
	empty($_ip) AND message(-1, 'IP 为空');
	
	$_ip = long2ip(ip2long($_ip)); // 安全过滤
	$day = intval(xn_urldecode(param(3)));
	
	empty($group['allowbanuser']) AND message(-1, '您无权禁止 IP');
	
	$arr = explode('.', $_ip);
	$arr[0] == '0' AND message(-1, 'IP 地址不能以 0 开头。');
	
	$banip = banip_read_by_ip($_ip);
	if($day == -1) {
		$r = banip_delete($banip['banid']);
	} else {
		$day == 0 AND $day = 3650;
		$arr = array(
			'ip0'=>$arr[0],
			'ip1'=>$arr[1],
			'ip2'=>$arr[2],
			'ip3'=>$arr[3],
			'uid'=>$uid,
			'create_date'=>$time,
			'uid'=>$uid,
			'expiry'=>$time + 86400 * $day,
		);
		if(empty($banip)) {
			$r = banip_create($arr);
		} else {
			$r = banip_update($banip['banid'], $arr);
		}
	}
	$r === FALSE ? message(-1, '操作失败') : message(0, '操作成功');
}


?>