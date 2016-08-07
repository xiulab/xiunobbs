<?php

!defined('DEBUG') AND exit('Access Denied.');

// hook forum_start.php

$fid = param(1, 0);
$page = param(2, 1);
$order = param(3, $conf['order_default']);	// thread orderby
$order != 'tid' AND $order = 'lastpid';		// default order by reply time

$forum = forum_read($fid);
empty($forum) AND message(3, lang('forum_not_exists'));

forum_access_user($fid, $gid, 'allowread') OR message(-1, lang('insufficient_visit_forum_privilege'));

$pagesize = $conf['pagesize'];
$pagination = pagination(url("forum-$fid-{page}-$order"), $forum['threads'], $page, $pagesize);

$threadlist = thread_find_by_fid($fid, $page, $pagesize, $order);

$header['title'] = $forum['seo_title'] ? $forum['seo_title'] : $forum['name'].'-'.$conf['sitename']; 		// 网站标题
$header['mobile_title'] = $forum['name'];
$header['keywords'] = $forum['seo_keywords']; 		// 关键词
$header['navs'][] = "<a href=\"forum-$fid.htm\">$forum[name]</a>";

// hook forum_end.php

include './view/htm/forum.htm';

?>