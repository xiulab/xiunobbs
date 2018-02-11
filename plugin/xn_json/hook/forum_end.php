if($ajax) {
	$forum = forum_safe_info($forum);
	foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
	message(0, array('forum'=>$forum, 'threadlist'=>$threadlist));
}