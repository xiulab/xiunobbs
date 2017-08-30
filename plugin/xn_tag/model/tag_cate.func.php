<?php

// hook model_tag_cate_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function tag_cate_create($arr) {
	// hook model_tag_cate_create_start.php
	$r = db_create('tag_cate', $arr);
	// hook model_tag_cate_create_end.php
	return $r;
}

function tag_cate_update($cateid, $arr) {
	// hook model_tag_cate_update_start.php
	$r = db_update('tag_cate', array('cateid'=>$cateid), $arr);
	// hook model_tag_cate_update_end.php
	return $r;
}

function tag_cate_read($cateid) {
	// hook model_tag_cate_read_start.php
	$tag_cate = db_find_one('tag_cate', array('cateid'=>$cateid));
	// hook model_tag_cate_read_end.php
	return $tag_cate;
}

function tag_cate_delete_by_fid($fid) {
	$tagcatelist = tag_cate_find_by_fid($fid);
	foreach ($tagcatelist as $tagcate) {
		tag_cate_delete($tagcate['cateid']);
	}
}

function tag_cate_delete($cateid) {
	// hook model_tag_cate_delete_start.php
	$taglist = tag_find_by_cateid($cateid);
	if($taglist) {
		foreach($taglist as $tag) {
			tag_delete($tag['tagid']);
		}
	}
	$r = db_delete('tag_cate', array('cateid'=>$cateid));
	// hook model_tag_cate_delete_end.php
	return $r;
}

// tagcatelist
function tag_cate_find_by_fid($fid) {
	$tagcatelist = db_find('tag_cate', array('fid'=>$fid), array('rank'=>-1), 1, 1000);
	if($tagcatelist) {
		foreach($tagcatelist as &$tagcate) {
			$tagcate['taglist'] = tag_find_by_cateid($tagcate['cateid']);
			$tagcate['tagmap'] = arrlist_change_key($tagcate['taglist'], 'tagid');
		}
	}
	return $tagcatelist;
}

function tag_cate_maxid() {
	return db_maxid('tag_cate', 'cateid');
}

// hook model_tag_cate_end.php

?>