<?php

// hook model_tag_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function tag_create($arr) {
	// hook model_tag_create_start.php
	$r = db_create('tag', $arr);
	// hook model_tag_create_end.php
	return $r;
}

function tag_update($tagid, $arr) {
	// hook model_tag_update_start.php
	$r = db_update('tag', array('tagid'=>$tagid), $arr);
	// hook model_tag_update_end.php
	return $r;
}

function tag_read($tagid) {
	// hook model_tag_read_start.php
	$tag = db_find_one('tag', array('tagid'=>$tagid));
	// hook model_tag_read_end.php
	return $tag;
}

function tag_delete($tagid) {
	// hook model_tag_delete_start.php
	tag_thread_delete_by_tagid($tagid);
	$r = db_delete('tag', array('tagid'=>$tagid));
	// 关联删除掉
	
	// hook model_tag_delete_end.php
	return $r;
}

function tag_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook model_tag_find_start.php
	$taglist = db_find('tag', $cond, $orderby, $page, $pagesize);
	// hook model_tag_find_end.php
	return $taglist;
}

// $taglist
function tag_find_by_cateid($cateid) {
	$taglist = array();
	$taglist = tag_find(array('cateid'=>$cateid), array('rank'=>-1), 1, 1000);
	return $taglist;
}

// $taglist 
function tag_fetch_from_catelist($tagcatelist) {
	$taglist = array();
	if($tagcatelist) {
		foreach($tagcatelist as $tagcate) {
			$taglist = array_merge($taglist, $tagcate['taglist']);
		}
	}
	$taglist = arrlist_change_key($taglist, 'tagid');
	return $taglist;
}

function tag_maxid() {
	return db_maxid('tag', 'tagid');
}

// hook model_tag_end.php

?>