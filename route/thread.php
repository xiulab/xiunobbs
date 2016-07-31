<?php

!defined('DEBUG') AND exit('Access Denied.');



$action = param(1);


// hook thread_action_before.php

// 发表主题帖
if($action == 'create') {
	
	// hook thread_create_get_post.php
		
	empty($user) AND http_location(url('user-login'));

	if($method == 'GET') {
		
		// hook thread_create_get_start.php
		
		$fid = param(2, 0);
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		if(empty($forumlist_allowthread)) {
			message(-1, '您所在的用户组没有权限发主题');
			// header("Location:user-login.htm");
			exit;
		}
		
		$header['title'] = '发帖';
		
		// hook thread_create_get_end.php
		
		include './view/htm/post.htm';
		
	} else {
		
		// hook thread_create_thread_start.php
		
		include './xiunophp/xn_html_safe.func.php';
		
		$fid = param('fid', 0);
		$forum = forum_read($fid);
		empty($forum) AND message('fid', '板块不存在'.$fid);
		
		$r = forum_access_user($fid, $gid, 'allowthread');
		!$r AND message(-1, '您（'.$user['groupname'].'）无权限在此版块发帖');
		
		$subject = htmlspecialchars(param('subject', '', FALSE));
		empty($subject) AND message('subject', '标题不能为空');
		xn_strlen($subject) > 128 AND message('subject', '标题最长80个字符');
		
		$message = param('message', '', FALSE);
		empty($message) AND message('message', '内容不能为空'.$fid);
		$doctype = param('doctype', 0);
		$doctype > 2 AND message(-1, '不支持的文档格式。');
		xn_strlen($message) > 2028000 AND message('message', '内容太长');
		
		$thread = array (
			'fid'=>$fid,
			'uid'=>$uid,
			'sid'=>$sid,
			'subject'=>$subject,
			'message'=>$message,
			'time'=>$time,
			'longip'=>$longip,
			'doctype'=>$doctype,
		);
		
		// thread_create_thread_before.php
		
		$tid = thread_create($thread, $pid);
		$pid === FALSE AND message(-1, '创建帖子失败');
		$tid === FALSE AND message(-1, '创建主题失败');
		
		// hook thread_create_thread_end.php
		message(0, '发帖成功');
	}
	
// hook thread_action_add.php

// 帖子详情
} else {
	
	// thread-{tid}-{page}-{keyword}.htm
	$tid = param(1, 0);
	$page = param(2, 1);
	$keyword = param(3);
	
	// hook thread_info_start.php
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在');;
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(3, '板块不存在'.$fid);
	
	$postlist = post_find_by_tid($tid);
	empty($postlist) AND message(4, '帖子不存在');
	
	empty($postlist[$thread['firstpid']]) AND message(-1, '数据有问题。');
	$first = $postlist[$thread['firstpid']];
	unset($postlist[$thread['firstpid']]);
	
	$keywordurl = '';
	if($keyword) {
		$thread['subject'] = post_highlight_keyword($thread['subject'], $keyword);
		//$first['message'] = post_highlight_keyword($first['subject']);
		$keywordurl = "-$keyword";
	}
	$allowpost = forum_access_user($fid, $gid, 'allowpost') ? 1 : 0;
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate') ? 1 : 0;
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete') ? 1 : 0;
	
	forum_access_user($fid, $gid, 'allowread') OR message(-1, '您所在的用户组无权访问该板块。');
	
	$pagesize = $conf['pagesize'];
	$pagination = pagination("thread-$tid-{page}$keywordurl.htm", $thread['posts'], $page, $pagesize);
	
	$attachlist = $imagelist = $filelist = array();
	$first['files'] AND list($attachlist, $imagelist, $filelist) = attach_find_by_pid($thread['firstpid']);
	
	thread_inc_views($tid); // 如果是大站，可以用单独的点击服务，减少 db 压力
	
	$header['title'] = $thread['subject'].'-'.$forum['name'].'-'.$conf['sitename']; 		// 网站标题
	$header['mobile_title'] = '帖子详情';
	$header['keywords'] = $header['title']; 
	$header['navs'][] = "<a href=\"forum-$fid.htm\">$forum[name]</a>";
	$header['navs'][] = "<a href=\"$thread[url]\">$thread[subject]</a>";
	
	// hook thread_info_end.php
	include './view/htm/thread.htm';
	
}


?>