<?php

// hook article_func_php_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function article__create($arr) {
	// hook article__create_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("INSERT INTO `bbs_article` SET $sqladd");
	// hook article__create_end.php
	return $r;
}

function article__update($articleid, $arr) {
	// hook article__update_start.php
	$sqladd = array_to_sqladd($arr);
	$r = db_exec("UPDATE `bbs_article` SET $sqladd WHERE articleid='$articleid'");
	// hook article__update_end.php
	return $r;
}

function article__read($articleid) {
	// hook article__read_start.php
	$article = db_find_one("SELECT * FROM `bbs_article` WHERE articleid='$articleid'");
	// hook article__read_end.php
	return $article;
}

function article__delete($articleid) {
	// hook article__delete_start.php
	$r = db_exec("DELETE FROM `bbs_article` WHERE articleid='$articleid'");
	// hook article__delete_end.php
	return $r;
}

function article__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook article__find_start.php
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$article = db_find("SELECT * FROM `bbs_article` $cond$orderby LIMIT $offset,$pagesize");
	// hook article__find_end.php
	return $article;
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function article_create($arr) {
	// hook article_create_start.php
	$r = article__create($arr);
	// hook article_create_end.php
	return $r;
}

function article_update($articleid, $arr) {
	// hook article_update_start.php
	$r = article__update($articleid, $arr);
	// hook article_update_end.php
	return $r;
}

function article_read($articleid) {
	// hook article_read_start.php
	$article = article__read($articleid);
	$article AND article_format($article);
	// hook article_read_end.php
	return $article;
}

function article_delete($articleid) {
	// hook article_delete_start.php
	$r = article__delete($articleid);
	// hook article_delete_end.php
	return $r;
}

function article_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook article_find_start.php
	$articlelist = article__find($cond, $orderby, $page, $pagesize);
	if($articlelist) foreach ($articlelist as &$article) article_format($article);
	// hook article_find_end.php
	return $articlelist;
}

function article_replace($articleid, $arr) {
	// hook article_replace_start.php
	$article = article_read($articleid);
	if($article) {
		$r = article_update($articleid, $arr);
	} else {
		$arr['articleid'] = $articleid;
		$r = article_create($arr);
	}
	// hook article_replace_end.php
	return $r;
}

// ----------------> 其他方法

function article_format(&$article) {
	// hook article_format_start.php
	global $conf;
	$article['create_date_fmt'] = date('Y-n-j', $article['create_date']);
	$article['catename'] = array_value($conf['cate'], $article['cateid'], '');
	// hook article_format_end.php
}

function article_count($cond = array()) {
	// hook article_count_start.php
	$n = db_count('bbs_article', $cond);
	// hook article_count_end.php
	return $n;
}

function article_maxid() {
	// hook article_maxid_start.php
	$n = db_maxid('bbs_article', 'articleid');
	// hook article_maxid_end.php
	return $n;
}


// hook article_func_php_end.php

?>