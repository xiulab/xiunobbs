<?php

// ------------> 最原生的 CURD，无关联其他数据。

function article__create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `bbs_article` SET $sqladd");
}

function article__update($articleid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `bbs_article` SET $sqladd WHERE articleid='$articleid'");
}

function article__read($articleid) {
	return db_find_one("SELECT * FROM `bbs_article` WHERE articleid='$articleid'");
}

function article__delete($articleid) {
	return db_exec("DELETE FROM `bbs_article` WHERE articleid='$articleid'");
}

function article__find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `bbs_article` $cond$orderby LIMIT $offset,$pagesize");
}

// ------------> 关联 CURD，主要是强相关的数据，比如缓存。弱相关的大量数据需要另外处理。

function article_create($arr) {
	$r = article__create($arr);
	return $r;
}

function article_update($articleid, $arr) {
	$r = article__update($articleid, $arr);
	return $r;
}

function article_read($articleid) {
	$article = article__read($articleid);
	$article AND article_format($article);
	return $article;
}

function article_delete($articleid) {
	$r = article__delete($articleid);
	return $r;
}

function article_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$articlelist = article__find($cond, $orderby, $page, $pagesize);
	if($articlelist) foreach ($articlelist as &$article) article_format($article);
	return $articlelist;
}

function article_replace($articleid, $arr) {
	$article = article_read($articleid);
	if($article) {
		$r = article_update($articleid, $arr);
	} else {
		$arr['articleid'] = $articleid;
		$r = article_create($arr);
	}
	return $r;
}

// ----------------> 其他方法

function article_format(&$article) {
	global $conf;
	$article['create_date_fmt'] = date('Y-n-j', $article['create_date']);
	$article['catename'] = array_value($conf['cate'], $article['cateid'], '');
}

function article_count($cond = array()) {
	return db_count('bbs_article', $cond);
}

function article_maxid() {
	return db_maxid('bbs_article', 'articleid');
}

?>