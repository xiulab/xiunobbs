<?php

!defined('DEBUG') AND exit('Access Denied.');
	
// 模板初始化依赖
$fid = param(1, 0);
$page = param(2, 1);
$order = param(3);
!in_array($order, array('tid', 'lastpid')) AND $order = $conf['order_default']; // 默认按照顶贴时间排序

$forum = forum_read($fid);
empty($forum) AND message(3, '板块不存在'.$fid);

forum_access_user($fid, $gid, 'allowread') OR message(-1, '您所在的用户组无权访问该板块。');

$pagesize = $conf['pagesize'];
$pagination = pagination(url("forum-$fid-{page}-$order"), $forum['threads'], $page, $pagesize);

$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);

$header['title'] = $forum['seo_title'] ? $forum['seo_title'] : $forum['name'].'-'.$conf['sitename']; 		// 网站标题
$header['mobile_title'] = $forum['name'];
$header['keywords'] = $forum['seo_keywords']; 		// 关键词
$header['navs'][] = "<a href=\"forum-$fid.htm\">$forum[name]</a>";

include './view/htm/forum.htm';

?>