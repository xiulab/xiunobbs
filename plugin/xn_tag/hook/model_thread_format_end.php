<?php exit;
	// todo
	// 如果版块启用了主题分类，则查询。
	global $time;
	$thread['taglist'] = array();
	if(!empty($forum['tagcatelist'])) {
		// 查询一下，此处应该有字段判断
		// tagids
		$tagidarr = array();
		if($thread['tagids_time'] < setting_get('tag_update_time')) {
			$tagidarr = tag_thread_find_tagid_by_tid($thread['tid']);
			$thread['tagids'] = implode(',', $tagidarr);
			thread_update($thread['tid'], array('tagids'=>$thread['tagids'], 'tagids_time'=>$time));
		} else {
			$tagidarr = explode(',', $thread['tagids']);
		}
		
		foreach($tagidarr as $tagid) {
			isset($forum['tagmap'][$tagid]) AND $thread['taglist'][] = $forum['tagmap'][$tagid];
		}
	}
	
?>