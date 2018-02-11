if($ajax) {
	foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
	message(0, $threadlist);
}