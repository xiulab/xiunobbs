<?php exit;
	kv_set('search_conf', $search_conf);
	
	if(search_type() == 'fulltext') {
		$s = strip_tags($message);
		$words = search_cn_encode($s);
		db_create('post_search', array('pid'=>$pid, 'message'=>$words));
	}
?>