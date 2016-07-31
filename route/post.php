<?php

// 创建新帖
!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';

$action = param(1);

empty($user) AND http_location(url('user-login'));

if($action == 'create') {
	
	$tid = param(2);
	$quick = param(3);
		
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	
	$forum = forum_read($fid);
	empty($forum) AND message(-1, '板块不存在:'.$fid);
	
	$r = forum_access_user($fid, $gid, 'allowpost');
	if(!$r) {
		message(-1, '您（'.$user['groupname'].'）无权限在此版块发帖');
	}
	
	if($method == 'GET') {
		
		include './view/htm/post.htm';
		
	} else {
		
		$message = param('message', '', FALSE);
		empty($message) AND message('message', '内容不能为空'.$fid);
		
		$doctype = param('doctype', 0);
		//$gid != 1 AND $doctype != 1 AND $message = xn_html_safe($message);
		xn_strlen($message) > 2028000 AND message('message', '内容太长');
		
		// 检测是否超过最大回复数
		$thread['posts'] >= 1000 AND message(-1, '该主题已经达到最大回复数 1000，不能再回复，请另起主题。');
		$thread['top'] > 0 AND thread_top_cache_delete();
		
		# 论坛帖子数据，一页显示，不分页。
		$post = array(
			'tid'=>$tid,
			'uid'=>$uid,
			'create_date'=>$time,
			'userip'=>$longip,
			'isfirst'=>0,
			'doctype'=>$doctype,
			'message'=>$message,
		);
		$pid = post_create($post, $fid, $gid);
		empty($pid) AND message(-1, '创建帖子失败');
		
		// 最新发帖
		// thread_top_create($fid, $tid);

		$post = post_read($pid);
		$post['floor'] = $thread['posts'] + 1;
		$postlist = array($post);
		
		$allowpost = forum_access_user($fid, $gid, 'allowpost');
		$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
		$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
		
		// 直接返回帖子的 html
		$return_html = param('return_html', 0);
		if($return_html) {
			$filelist = array();
			ob_start();
			include './view/htm/post_list.inc.htm';
			$s = ob_get_clean();
			message(0, $s);
		} else {
			message(0, '回帖成功');
		}
	
	}
	
} elseif($action == 'update') {

	$pid = param(2);
	$post = post_read($pid);
	empty($post) AND message(-1, '帖子不存在:'.$pid);
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, '板块不存在:'.$fid);
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
	!$allowupdate AND !$post['allowupdate'] AND message(-1, '无权编辑该贴');
	
	if($method == 'GET') {
		
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		// 如果为数据库减肥，则 message 可能会被设置为空。
		$post['message'] = htmlspecialchars($post['message'] ? $post['message'] : $post['message_fmt']);
		
		$attachlist = $imagelist = $filelist = array();
		if($post['files']) {
			list($attachlist, $imagelist, $filelist) = attach_find_by_pid($pid);
		}
		
		include './view/htm/post.htm';
		
	} elseif($method == 'POST') {
		
		$subject = htmlspecialchars(param('subject', '', FALSE));
		$message = param('message', '', FALSE);
		$doctype = param('doctype', 0);
		
		empty($message) AND message('message', '内容不能为空');
		mb_strlen($message, 'UTF-8') > 2048000 AND message('message', '内容太长');
		
		$arr = array();
		if($isfirst) {
			$newfid = param('fid');
			$forum = forum_read($newfid);
			empty($forum) AND message('fid', '板块不存在:'.$newfid);
			
			if($fid != $newfid) {
				!forum_access_user($fid, $gid, 'allowthread') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
				$post['uid'] != $uid AND !forum_access_mod($fid, $gid, 'allowupdate') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块编辑帖子');
				$arr['fid'] = $newfid;
			}
			if($subject != $thread['subject']) {
				mb_strlen($subject, 'UTF-8') > 80 AND message('subject', '标题最长80个字符');
				$arr['subject'] = $subject;
			}
			$arr AND thread_update($tid, $arr) === FALSE AND message(-1, '更新主题失败');
		}
		$r = post_update($pid, array('doctype'=>$doctype, 'message'=>$message));
		$r === FALSE AND message(-1, '更新帖子失败');
		
		message(0, lang('update_success'));
		//message(0, array('pid'=>$pid, 'subject'=>$subject, 'message'=>$message));
	}
	
} elseif($action == 'delete') {

	$pid = param(2, 0);
	
	if($method != 'POST') message(-1, '方法不对');
	
	$post = post_read($pid);
	empty($post) AND message(-1, '帖子不存在:'.$pid);
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, '板块不存在:'.$fid);
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
	!$allowdelete AND !$post['allowdelete'] AND message(-1, '无权删除该帖');
	
	if($isfirst) {
		// 清除所有的回复。喜欢。还有相关资源
		thread_delete($tid);
	} else {
		post_delete($pid);
		//post_list_cache_delete($tid);
	}
	
	
	message(0, lang('delete_success'));

} else {
	
	message(-1, '没有此功能');
	
}

?>