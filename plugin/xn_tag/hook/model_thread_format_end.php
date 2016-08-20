<?php exit;
	// todo
	// 如果版块启用了主题分类，则查询。
	$thread['taglist'] = array();
	if($forum['tagcatelist']) {
		// 查询一下
		$tagids = tag_thread_find_tagid_by_tid($thread['tid']);
		foreach($tagids as $tagid) {
			isset($forum['tagmap'][$tagid]) AND $thread['taglist'][] = $forum['tagmap'][$tagid];
		}
	}
	
?>