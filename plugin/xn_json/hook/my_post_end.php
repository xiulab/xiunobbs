if($ajax) {
	foreach($postlist as &$post) $post = post_safe_info($post);
	message(0, $postlist);
}