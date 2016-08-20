<?php

// hook model_tag_thread_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function tag_thread_create($tagid, $tid) {
	// hook model_tag_thread_create_start.php
	$arr = array('tagid'=>$tagid, 'tid'=>$tid);
	$r = db_create('tag_thread', $arr);
	// hook model_tag_thread_create_end.php
	return $r;
}

function tag_thread_delete($tagid, $tid) {
	// hook model_tag_thread_delete_start.php
	$r = db_delete('tag_thread', array('tagid'=>$tagid, 'tid'=>$tid));
	// hook model_tag_thread_delete_end.php
	return $r;
}

function tag_thread_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook model_tag_thread_find_start.php
	$tag_threadlist = db_find('tag_thread', $cond, $orderby, $page, $pagesize);
	// hook model_tag_thread_find_end.php
	return $tag_threadlist;
}

function tag_thread_delete_by_tagid($tagid) {
	$r = db_delete('tag_thread', array('tagid'=>$tagid));
	return $r;
}

function tag_thread_find_tagid_by_tid($tid) {
	$tagids = array();
	$tagthreadlist = tag_thread_find(array('tid'=>$tid), array(), 1, 1000);
	$tagids = arrlist_values($tagthreadlist, 'tagid');
	return $tagids;
}

// hook model_tag_thread_end.php

?>