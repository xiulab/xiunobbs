<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';

$action = param(1);

$uid AND $user = user_read($uid);
empty($user) AND $user = user_guest();

// hook thread_action_before.php

// 发表主题帖
if($action == 'create') {
	
	// hook thread_create_get_post.php
	
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'threads') AND message(-1, '您的 IP 今日发表主题数达到上限，请明天再来。');
	
	if($method == 'GET') {
		
		// hook thread_create_get_start.php
		
		check_standard_browser();
		$fid = param(2, 0);
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		if(empty($forumlist_allowthread)) {
			message(-1, '您所在的用户组没有权限发主题');
			// header("Location:user-login.htm");
			exit;
		}
		
		$header['title'] = '发帖'.($uid == 0 ? ' [匿名模式]' : '');
		
		// hook thread_create_get_end.php
		
		include './view/htm/thread_create.htm';
		
	} else {
		
		// hook thread_create_post_start.php
		
		$fid = param('fid', 0);
		$forum = forum_read($fid);
		empty($forum) AND message('fid', '板块不存在'.$fid);
		
		$r = forum_access_user($fid, $gid, 'allowthread');
		if(!$r) {
			if($gid == 0) {
				$r = forum_access_user($fid, 101, 'allowthread');
				$r AND user_login_check($user);
			}
			message(-1, '您（'.$user['groupname'].'）无权限在此版块发帖');
		}
		
		$subject = htmlspecialchars(param('subject', '', FALSE));
		$message = param('message', '', FALSE);
		
		empty($subject) AND message('subject', '标题不能为空'.$fid);
		$gid != 1 AND $subject = badword_filter($subject, $badword);
		$subject === FALSE AND message('subject', '标题中包含敏感关键词: '.$badword);
		empty($message) AND message('message', '内容不能为空'.$fid);
		$gid != 1 AND $message = xn_html_safe($message);
		$gid != 1 AND $message = badword_filter($message, $badword);
		$message === FALSE AND message('message', '内容中包含敏感关键词: '.$badword);
		mb_strlen($subject, 'UTF-8') > 128 AND message('subject', '标题最长80个字符');
		mb_strlen($message, 'UTF-8') > 2028000 AND message('message', '内容太长');
		
		// todo: 检测是否灌水
		thread_check_flood($gid, $fid, $subject) AND message(-1, '系统检测到您可能在灌水。');
		
		$thread = array(
			'fid'=>$fid,
			'uid'=>$uid,
			'sid'=>$sid,
			'subject'=>$subject,
			'message'=>$message,
			'time'=>$time,
			'longip'=>$longip,
			'sid'=>$sid,
		);
		$tid = thread_create($thread, $pid);
		$pid === FALSE AND message(-1, '创建帖子失败');
		$tid === FALSE AND message(-1, '创建主题失败');
		
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'threads');
		
		// hook thread_create_post_ajax_end.php
		message(0, '发帖成功');
	}
	
// 处理 2.1 老版本 URL
} else if($action == 'index') {
	
	// hook thread_index_get.php
	
	$tid = param(5, 0);
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: thread-$tid.htm");
	exit;
	
// hook thread_action_add.php

// 帖子详情
// $action == 'seo' 也会跳到此处
} else {
	
	// hook thread_info_start.php
	$tid = param(1, 0);
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
	
	
	$header['title'] = $thread['subject'].'-'.$forum['name'].'-'.$conf['sitename']; 		// 网站标题
	$header['keywords'] = $header['title']; 							// 关键词
	
	$keyword = param('keyword'); // 可能有关键字需要高亮显示
	if($keyword) {
		$thread['subject'] = post_highlight_keyword($thread['subject'], $keyword);
		//$first['message'] = post_highlight_keyword($first['subject']);
	}
	$allowpost = forum_access_user($fid, $gid, 'allowpost') ? 1 : 0;
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate') ? 1 : 0;
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete') ? 1 : 0;
	
	forum_access_user($fid, $gid, 'allowread') OR message(-1, '您所在的用户组无权访问该板块。');
	
	// ajax 不需要以下数据
	
	// threadlist
	$page = 1;
	$pagesize = $conf['pagesize'];
	$pages = pages("forum-$fid-{page}.htm", $forum['threads'], $page, $pagesize);
	$threadlist = thread_find(array('fid'=>$fid), array('tid'=>-1), $page = 1, $pagesize);
	
	// 升级需要查找附件
	$attachlist = $imagelist = $filelist = array();
	if($first['images'] || $first['files']) {
		$attachlist = attach_find_by_pid($first['pid']);
		list($imagelist, $filelist) = attach_list_not_in_message($attachlist, $first['message']);
	}
	
	thread_inc_views($tid); // 如果是大站，可以用单独的点击服务，减少 db 压力
	
	$header['navs'][] = "<a href=\"forum-$fid.htm\">$forum[name]</a>";
	$header['navs'][] = "<a href=\"$thread[url]\">$thread[subject]</a>";
	
	if(!$group['allowviewip']) {
		unset($thread['userip']);
		unset($thread['sid']);
	}
	// hook thread_info_end.php
	include './view/htm/thread.htm';
	
}


?>