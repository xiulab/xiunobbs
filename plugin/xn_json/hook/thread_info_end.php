if($ajax) {
	$thread = thread_safe_info($thread);
	foreach($postlist as &$post) $post = post_safe_info($post);
	message(0, array('thread'=>$thread, 'postlist'=>$postlist));
}