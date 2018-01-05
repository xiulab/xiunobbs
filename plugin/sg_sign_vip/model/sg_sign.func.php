<?php
/*
	Xiuno BBS 4.0 每日签到
	插件由查鸽信息网制作网址：http://cha.sgahz.net/
*/
function sg_sign_create($form, $arr) {
	$r = db_insert($form, $arr);
	return $r;
}
function sg_sign_update($form, $field, $id, $arr) {
	$r = db_update($form, array($field=>$id), $arr);
	return $r;
}
function sg_sign_read($form, $field, $id) {
	$thread = db_find_one($form, array($field=>$id));
	return $thread;
}
function sg_sign_find($form, $field, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10) {
	$arrlist = db_find($form, $cond, $orderby, $page, $pagesize, $field, array($field));
	if(empty($arrlist)) return array();
	$tidarr = arrlist_values($arrlist, $field);
	$sg_signlist = db_find($form, array($field=>$tidarr), $orderby, 1, $pagesize, $field);
	return $sg_signlist;
}
?>