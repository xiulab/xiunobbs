<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';

$action = param(1);

$uid AND $user = user_read($uid);
empty($user) AND $user = user_guest();

// 发表主题帖
if($action == 'create') {
	
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'threads') AND message(-1, '您的 IP 今日发表主题数达到上限，请明天再来。');
	
	if($method == 'GET') {
		
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
		include './pc/view/thread_create.htm';
		
	} else {
		
		$fid = param('fid', 0);
		$forum = forum_read($fid);
		empty($forum) AND message(3, '板块不存在'.$fid);
		
		$r = forum_access_user($fid, $gid, 'allowthread');
		if(!$r) {
			if($gid == 0) {
				$r = forum_access_user($fid, 101, 'allowthread');
				$r AND user_login_check($user);
			}
			message(10, '您（'.$user['groupname'].'）无权限在此版块发帖');
		}
		
		$subject = htmlspecialchars(param('subject', '', FALSE));
		$message = param('message', '', FALSE);
		$seo_url = $conf['seo_url_rewrite'] && $group['allowcustomurl'] ? preg_replace('#[^\w\-]#', '', strtolower(param('seo_url'))) : ''; // 只允许英文和 - 
		
		empty($subject) AND message(1, '标题不能为空'.$fid);
		$gid != 1 AND $subject = badword_filter($subject, $badword);
		$subject === FALSE AND message(1, '标题中包含敏感关键词: '.$badword);
		empty($message) AND message(2, '内容不能为空'.$fid);
		$conf['seo_url_rewrite'] AND $seo_url AND thread_read_by_seo_url($seo_url) AND message(4, '自定义的 URL 已经存在，请修改。'); // 这里可能有并发问题，seo_url 并非 UNIQUE KEY
		$gid != 1 AND $message = xn_html_safe($message);
		$gid != 1 AND $message = badword_filter($message, $badword);
		$message === FALSE AND message(2, '内容中包含敏感关键词: '.$badword);
		strlen($seo_url) > 128 AND message(3, '自定义 URL 太长');
		mb_strlen($subject, 'UTF-8') > 128 AND message(1, '标题最长80个字符');
		mb_strlen($message, 'UTF-8') > 2028000 AND message(2, '内容太长');
		
		
		
		// 检测是否灌水
		thread_check_flood($gid, $fid, $subject) AND message(1, '系统检测到您可能在灌水。');
		
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
		$seo_url AND $thread['seo_url'] = $seo_url;
		$tid = thread_create($thread, $pid);
		$pid === FALSE AND message(1, '创建帖子失败');
		$tid === FALSE AND message(1, '创建主题失败');
		
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'threads');
		
		if($ajax) {
			ob_start();
			$thread = thread_read($tid);
			$threadlist = array($thread);
			include './pc/view/thread_list_body.inc.htm';
			$middle = ob_get_clean();
			message(0, $middle);
		} else {
			message(0, '发帖成功');
		}
	}
	
// 处理 2.1 老版本 URL
} else if($action == 'index') {
	
	$tid = param(5, 0);
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: thread-$tid.htm");
	exit;
	
// 帖子详情
// $action == 'seo' 也会跳到此处
} else {
	
	// 支持自定义的 SEO URL: http://x.com/xxx-xxx-xxx
	// Rewrite 以后：http://x.com/thread-seo-xxx-xxx-xxxx-xxx.htm
	// index 中如果开启了 rewrite, $tid, $thread 会被初始化！
	
	$conf['seo_url_rewrite'] AND $tid == -1 AND exit(header("HTTP/1.1 404 Not Found"));
	if(!$conf['seo_url_rewrite'] || $conf['seo_url_rewrite'] && isset($tid) && $tid == 0) {
		$tid = param(1, 0);
		$thread = thread_read($tid);
		empty($thread) AND exit(header("HTTP/1.1 404 Not Found"));
	}
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
	
	$seo_url = $thread['seo_url']; // 模板需要
	
	
	
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
	include './pc/view/thread.htm';
	
}


?>