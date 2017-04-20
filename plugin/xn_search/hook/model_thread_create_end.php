<?php exit;
	if(search_type() == 'fulltext') {
		$s = strip_tags($subject.' '.$message);
		$words = search_cn_encode($s);
		db_create('thread_search', array('tid'=>$tid, 'message'=>$words));
	}
?>