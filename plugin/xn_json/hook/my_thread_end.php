if($ajax) {
	$user = user_safe_info($user);
	foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
	 message(0, array('user'=>$user, 'threadlist'=>$threadlist));
}