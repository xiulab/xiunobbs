if($ajax) {
	$_user = user_safe_info($_user);
        foreach($threadlist as &$thread) $thread = thread_safe_info($thread);
        message(0, array('user'=>$_user, 'threadlist'=>$threadlist));
}