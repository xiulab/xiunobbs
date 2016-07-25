<?php

// hook smtp_func_php_start.php

// 用配置文件来保存 smtp 列表数据

function smtp_create($arr) {
	// hook smtp_create_start.php
	global $smtplist;
	$smtplist[] = $arr;
	smtp_save();
	// hook smtp_create_end.php
	return count($smtplist);
}

function smtp_update($id, $arr) {
	// hook smtp_update_start.php
	global $smtplist;
	if(!isset($smtplist[$id])) return FALSE;
	foreach($arr as $k=>$v) {
		$smtplist[$id][$k] = $v;
	}
	smtp_save();
	// hook smtp_update_end.php
	return TRUE;
}

function smtp_read($id) {
	// hook smtp_read_start.php
	global $smtplist;
	// hook smtp_read_end.php
	return isset($smtplist[$id]) ? $smtplist[$id] : array();
}

function smtp_delete($id) {
	// hook smtp_delete_start.php
	global $smtplist;
	unset($smtplist[$id]);
	smtp_save();
	// hook smtp_delete_end.php
	return TRUE;
}

function smtp_save() {
	// hook smtp_save_start.php
	global $smtplist;
	// hook smtp_save_end.php
	file_put_contents('./conf/smtp.conf.php', "<?php\r\nreturn ".var_export($smtplist,true).";\r\n?>");
}

function smtp_init($confile) {
	return include $confile;
}

function smtp_find() {
	// hook smtp_find_start.php
	// hook smtp_find_end.php
	global $smtplist;
	return $smtplist;
	//return include './conf/smtp.conf.php';
}

function smtp_count() {
	// hook smtp_count_start.php
	global $smtplist;
	$n = count($smtplist);
	// hook smtp_count_end.php
	return $n;
}

function smtp_maxid() {
	// hook smtp_maxid_start.php
	// hook smtp_maxid_end.php
	return smtp_count() - 1;
}


// hook smtp_func_php_end.php

?>