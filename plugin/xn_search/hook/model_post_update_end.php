<?php exit;

$message = $arr['message'];
if($isfirst) {
	if(search_type() == 'fulltext') {
		$thread = thread__read($tid);
		$s = strip_tags($thread['subject'].' '.$message);
		$words = search_cn_encode($s);
		db_replace('thread_search', array('tid'=>$tid, 'message'=>$words));
	}
} else {
	if(search_type() == 'fulltext') {
		$s = strip_tags($message);
		$words = search_cn_encode($s);
		db_replace('post_search', array('pid'=>$pid, 'message'=>$words));
	}
}

?>