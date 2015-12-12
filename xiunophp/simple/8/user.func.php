<?php

// ------------> 最原生的 CURD，无关联其他数据。

function user_create($arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("INSERT INTO `test_user` SET $sqladd");
}

function user_update($uid, $arr) {
	$sqladd = array_to_sqladd($arr);
	return db_exec("UPDATE `test_user` SET $sqladd WHERE uid='$uid'");
}

function user_read($uid) {
	return db_find_one("SELECT * FROM `test_user` WHERE uid='$uid'");
}

function user_delete($uid) {
	return db_exec("DELETE FROM `test_user` WHERE uid='$uid'");
}

function user_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$cond = cond_to_sqladd($cond);
	$orderby = orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	return db_find("SELECT * FROM `test_user` $cond$orderby LIMIT $offset,$pagesize");
}


function user_create_table() {
	$r = db_exec("DROP TABLE IF EXISTS `test_user`");
	$r = db_exec("CREATE TABLE `test_user` (
	  uid int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户编号',
	  username char(32) NOT NULL DEFAULT '' COMMENT '用户名',
	  password char(32) NOT NULL DEFAULT '' COMMENT '密码',
	  PRIMARY KEY (uid)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
}
?>