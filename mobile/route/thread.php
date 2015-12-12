<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';

$action = param(1);
		
$uid AND $user = user_read($uid);
empty($user) AND $user = user_guest();

// 发表主题帖
if($action == 'create') {
	
	if($method == 'GET') {
		
		$fid = param(1, 0);
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		if(empty($forumlist_allowthread)) {
			header("Location:user-login.htm");
			exit;
		}
		
		$header['title'] = '发帖'.($uid == 0 ? ' [匿名模式]' : '');
		include './mobile/view/thread_create.htm';
		
	} else {

		
	}
} else {
	
	// 支持自定义的 SEO URL: http://x.com/xxx-xxx-xxx
	// Rewrite 以后：http://x.com/thread-seo-xxx-xxx-xxxx-xxx.htm
	// index 中如果开启了 rewrite, $tid, $thread 会被初始化！
	
	$conf['seo_url_rewrite'] AND $tid == -1 AND message(1, '主题不存在');
	if(!$conf['seo_url_rewrite'] || $conf['seo_url_rewrite'] && isset($tid) && $tid == 0) {
		$tid = param(1, 0);
		$thread = thread_read($tid);
		empty($thread) AND message(1, '主题不存在');
	}
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(3, '板块不存在'.$fid);
	
	$postlist = post_find_by_tid($tid);
	empty($postlist) AND message(4, '帖子不存在');
	
	$r = forum_access_user($fid, $gid, 'allowread');// OR message(-1, '您所在的用户组无权访问该板块。');
	
	empty($postlist[$thread['firstpid']]) AND message(-1, '数据有问题。');
	$first = $postlist[$thread['firstpid']];
	unset($postlist[$thread['firstpid']]);
	
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
	
	$seo_url = $thread['seo_url']; // 模板需要
	
	$header['title'] = $thread['subject'].'-'.$forum['name'].'-'.$conf['sitename']; 		// 网站标题
	$header['keywords'] = $header['title']; 		// 关键词
	
	thread_inc_views($tid); // 如果是大站，可以用单独的点击服务，减少 db 压力
	
	if(!$group['allowviewip']) {
		unset($thread['userip']);
		unset($thread['sid']);
	}
	
	include './mobile/view/thread.htm';
}

?>