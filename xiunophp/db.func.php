<?php

function db_new($dbconf) {
	// 数据库初始化，这里并不会产生连接！
	if($dbconf) {
		switch ($dbconf['type']) {
			case 'mysql':      $db = new db_mysql($dbconf['mysql']); 		break;
			case 'pdo_mysql':  $db = new db_pdo_mysql($dbconf['pdo_mysql']);	break;
			case 'pdo_sqlite': $db = new db_pdo_sqlite($dbconf['pdo_sqlite']);	break;
			case 'pdo_mongodb': $db = new db_pdo_mongodb($dbconf['pdo_mongodb']);	break;
			default: xn_message(-1, '不支持的 db type:'.$dbconf['type']);
		}
		if(!$db || ($db && $db->errstr)) xn_message(-1, $db->errstr);
		return $db;
	}
	return NULL;
}

function db_sql_find_one($sql, $abort = TRUE) {
	global $db;
	if(!$db) return FALSE;
	$arr = $db->find_one($sql);
	if($arr === FALSE && $db->errno != 0) {
		$s = "mysql sql: $sql, mysql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'mysql_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $arr;
}

function db_sql_find($sql, $key = NULL, $abort = TRUE) {
	global $db;
	if(!$db) return FALSE;
	$arr = $db->find($sql, $key);
	if($arr === FALSE && $db->errno != 0) {
		$s = "mysql sql: $sql, mysql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'mysql_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $arr;
}

// 如果为 INSERT 或者 REPLACE，则返回 mysql_insert_id();
// 如果为 UPDATE 或者 DELETE，则返回 mysql_affected_rows();
// 对于非自增的表，INSERT 后，返回的一直是 0
// 判断是否执行成功: mysql_exec() === FALSE
function db_exec($sql, $abort = TRUE) {
	global $db;
	if(!$db) return FALSE;
	
	DEBUG AND xn_log($sql, 'mysql_exec');
	$n = $db->exec($sql);
	
	if($n === FALSE && $db->errno != 0) {
		$s = "sql: $sql, sql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'db_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $n;
}

function db_count($table, $cond = array(), $abort = TRUE) {
	global $db;
	$r = $db->count($table, $cond);
	if($r === FALSE && $db->errno != 0) {
		$s = "sql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'db_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $r;
}

function db_maxid($table, $field, $abort = TRUE) {
	global $db;
	$r = $db->maxid($table, $field);
	if($r === FALSE && $db->errno != 0) {
		$s = "sql: $sql, sql errno: ".$db->errno.", errstr: ".$db->errstr;
		xn_log($s, 'db_error');
		$abort AND xn_message(-1, $db->errstr);
	}
	return $r;
}

// NO SQL 封装，可以支持 MySQL Marial PG MongoDB
function db_create($table, $arr) {
	return db_insert($table, $arr);
}

function db_insert($table, $arr) {
	$sqladd = array_to_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("INSERT INTO `$table` SET $sqladd");
}

function db_replace($table, $arr) {
	$sqladd = array_to_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("REPLACE INTO `$table` SET $sqladd");
}

function db_update($table, $cond, $update) {
	$condadd = cond_to_sqladd($cond);
	$sqladd = array_to_sqladd($update);
	if(!$sqladd) return FALSE;
	return db_exec("UPDATE `$table` SET $sqladd $condadd");
}

function db_delete($table, $cond) {
	$condadd = cond_to_sqladd($cond);
	return db_exec("DELETE FROM `$table` $condadd");
}

function db_read($table, $cond) {
	$sqladd = cond_to_sqladd($cond);
	$sql = "SELECT * FROM `$table` $sqladd";
	return db_find_one($sql);
}
	
function db_find($table, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10, $key = '', $abort = TRUE) {
	if(strtoupper(substr($table, 0, 7)) != 'SELECT ') {
		$cond = cond_to_sqladd($cond);
		$orderby = orderby_to_sqladd($orderby);
		$offset = ($page - 1) * $pagesize;
		return db_sql_find("SELECT * FROM `$table` $cond$orderby LIMIT $offset,$pagesize", $key, $abort);
	} else {
		// 兼容 XiunoPHP 3.0
		$sql = $table;
		$key = $cond;
		$abort = $orderby;
		return db_sql_find($sql, $key, $abort);
	}
}

function db_find_one($table, $cond = array(), $orderby = array()) {
	if(strtoupper(substr($table, 0, 7)) != 'SELECT ') {
		$cond = cond_to_sqladd($cond);
		$orderby = orderby_to_sqladd($orderby);
		return db_sql_find_one("SELECT * FROM `$table` $cond$orderby LIMIT 1");
	} else {
		// 兼容 XiunoPHP 3.0
		$sql = $table;
		$abort = $cond;
		return db_sql_find_one($sql, $abort);
	}
}

// 创建表
/*
	$ddl = array(
		'id' => array('');
	);
*/
function db_table_create($tablename, $ddl) {
	global $db;
	return $db->table_create($tablename, $ddl);
}

// 删除表
function db_table_delete($tablename) {
	global $db;
	return $db->table_delete($tablename, $ddl);
}

// 增加一列，默认增加到最后
function db_table_col_add($tablename, $ddl, $after = '') {
	global $db;
	return $db->table_table_col_add($tablename, $ddl, $after);
}

// 删除一列，默认增加到最后
function db_table_col_delete($tablename, $colnames) {
	global $db;
	return $db->table_table_col_delete($tablename, $colnames);
}

// 添加索引 $colnames = array('gid', 'create_date')
function db_index_create($tablename, $colnames) {
	global $db;
	return $db->index_create($tablename, $colnames);
}

// 删除索引 $colnames = array('gid', 'create_date')
function db_index_delete($tablename, $colnames) {
	global $db;
	return $db->index_delete($tablename, $colnames);
}

// 测试连接
function db_connect(&$err) {
	global $db;
	$r = $db->connect();
	//$err = $db->errstr;
	return $r;
}

function db_close() {
	global $db;
	$r = $db->close();
	//$err = $db->errstr;
	return $r;
}

?>