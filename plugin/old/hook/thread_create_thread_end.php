<?php exit;
		// todo:
		$tag_cate_id_arr = param('tag_cate_id', array(0));
		foreach($tag_cate_id_arr as $tag_cate_id => $tagid) {
			tag_thread_create($tagid, $tid);
		}
?>