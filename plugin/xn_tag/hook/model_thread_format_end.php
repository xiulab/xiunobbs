<?php exit;
	// todo
	// 如果版块启用了主题分类，则查询。
	$thread['taglist'] = array();
	if(!empty($forum['tagcatelist'])) {
		// 查询一下，此处应该有字段判断
		$tagids = tag_thread_find_tagid_by_tid($thread['tid']);
		foreach($tagids as $tagid) {
			isset($forum['tagmap'][$tagid]) AND $thread['taglist'][] = $forum['tagmap'][$tagid];
		}
	}
	
?>