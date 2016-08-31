<?php

// 此处的 $db 是局部变量，要注意，它返回后在定义为全局变量，可以有多个实例。
function db_new($dbconf) {
	// 数据库初始化，这里并不会产生连接！
	if($dbconf) {
		//print_r($dbconf);
		// 代码不仅仅是给人看的，更重要的是给编译器分析的，不要玩 $db = new $dbclass()，那样不利于优化和 opcache 。
		switch ($dbconf['type']) {
			case 'mysql':      $db = new db_mysql($dbconf['mysql']); 		break;
			case 'pdo_mysql':  $db = new db_pdo_mysql($dbconf['pdo_mysql']);	break;
			case 'pdo_sqlite': $db = new db_pdo_sqlite($dbconf['pdo_sqlite']);	break;
			case 'pdo_mongodb': $db = new db_pdo_mongodb($dbconf['pdo_mongodb']);	break;
			default: xn_message(-1, '不支持的 db type:'.$dbconf['type']);
		}
		if(!$db || ($db && $db->errstr)) {
			$errno = -1;
			$errstr = $db->errstr;
			return FALSE;
		}
		return $db;
	}
	return NULL;
}

// 测试连接
function db_connect($d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	
	$r = $d->connect();
	
	db_errno_errstr($r, $d);
	
	return $r;
}

function db_close($d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	$r = $d->close();
	
	db_errno_errstr($r, $d);
	
	return $r;
}

function db_sql_find_one($sql, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	$arr = $d->sql_find_one($sql);
	
	db_errno_errstr($arr, $d);
	
	return $arr;
}

function db_sql_find($sql, $key = NULL, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	$arr = $d->sql_find($sql, $key);
	
	db_errno_errstr($arr, $d);
	
	return $arr;
}

// 如果为 INSERT 或者 REPLACE，则返回 mysql_insert_id();
// 如果为 UPDATE 或者 DELETE，则返回 mysql_affected_rows();
// 对于非自增的表，INSERT 后，返回的一直是 0
// 判断是否执行成功: mysql_exec() === FALSE
function db_exec($sql, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	DEBUG AND xn_log($sql, 'mysql_exec');
	
	$n = $d->exec($sql);
	
	db_errno_errstr($n, $d);
	
	return $n;
}

function db_count($table, $cond = array(), $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$r = $d->count($d->tablepre.$table, $cond);
	
	db_errno_errstr($r, $d);
	
	return $r;
}

function db_maxid($table, $field, $cond = array(), $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$r = $d->maxid($d->tablepre.$table, $field, $cond);
	
	db_errno_errstr($r, $d);
	
	return $r;
}

// NO SQL 封装，可以支持 MySQL Marial PG MongoDB
function db_create($table, $arr, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	return db_insert($table, $arr);
}

function db_insert($table, $arr, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$sqladd = db_array_to_insert_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("INSERT INTO {$d->tablepre}$table $sqladd", $d);
}

function db_replace($table, $arr, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$sqladd = db_array_to_insert_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("REPLACE INTO {$d->tablepre}$table $sqladd", $d);
}

function db_update($table, $cond, $update, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$condadd = db_cond_to_sqladd($cond);
	$sqladd = db_array_to_update_sqladd($update);
	if(!$sqladd) return FALSE;
	return db_exec("UPDATE {$d->tablepre}$table SET $sqladd $condadd", $d);
}

function db_delete($table, $cond, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$condadd = db_cond_to_sqladd($cond);
	return db_exec("DELETE FROM {$d->tablepre}$table $condadd", $d);
}

function db_truncate($table, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	return $d->truncate($d->tablepre.$table);
}

function db_read($table, $cond, $d = NULL) {
	global $db;
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	$sqladd = db_cond_to_sqladd($cond);
	$sql = "SELECT * FROM {$d->tablepre}$table $sqladd";
	return db_sql_find_one($sql, $d);
}

function db_find($table, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10, $key = '', $col = array(), $d = NULL) {
	global $db;
	
	// 高效写法，定参有利于编译器优化
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	return $d->find($table, $cond, $orderby, $page, $pagesize, $key, $col);
	/*
	$page = max(1, $page);
	$cond = db_cond_to_sqladd($cond);
	$orderby = db_orderby_to_sqladd($orderby);
	$offset = ($page - 1) * $pagesize;
	$cols = $col ? implode(',', $col) : '*';
	return db_sql_find("SELECT $cols FROM {$d->tablepre}$table $cond$orderby LIMIT $offset,$pagesize", $key, $d);
	*/
	/* 兼容性写法: 需要兼容，请自行打开开这段注释，默认不兼容
	if(is_array($table)) {
		
		$cond = array_value($table, 'cond');
		$orderby = array_value($table, 'orderby');
		$page = array_value($table, 'page');
		$pagesize = array_value($table, 'pagesize');
		$key = array_value($table, 'key');
		$d = array_value($table, 'db');
		
		$d = $db ? $db : $d;
		if(!$d) return FALSE;
		
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$offset = ($page - 1) * $pagesize;
		$cols = $col ? implode(',', $col) : '*';
		return db_sql_find("SELECT $cols FROM {$d->tablepre}$table $cond$orderby LIMIT $offset,$pagesize", $key, $d);
		
	} elseif(strtoupper(substr($table, 0, 7)) != 'SELECT ') {
		
		$d = $db ? $db : $d;
		if(!$d) return FALSE;
		
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$offset = ($page - 1) * $pagesize;
		$cols = $col ? implode(',', $col) : '*';
		return db_sql_find("SELECT $cols FROM {$d->tablepre}$table $cond$orderby LIMIT $offset,$pagesize", $key, $d);
		
	} else {
		
		$d = $db ? $db : $d;
		if(!$d) return FALSE;
		
		// 兼容 XiunoPHP 3.0
		// $sql, $key, $abort(废弃）
		return db_sql_find($table, $cond, $d);
	}
	*/
}

function db_find_one($table, $cond = array(), $orderby = array(), $col = array(), $d = NULL) {
	global $db;
	
	// 高效写法，定参有利于编译器优化
	$d = $db ? $db : $d;
	if(!$d) return FALSE;
	
	return $d->find_one($table, $cond, $orderby, $col);
	
	/*
	$cond = db_cond_to_sqladd($cond);
	$orderby = db_orderby_to_sqladd($orderby);
	$cols = $col ? implode(',', $col) : '*';
	return db_sql_find_one("SELECT $cols FROM {$d->tablepre}$table $cond$orderby LIMIT 1", $d);*/
	
	/* 兼容性写法: 需要兼容，请自行打开开这段注释，默认不兼容
	if(is_array($table)) {
		
		$cond = array_value($table, 'cond');
		$orderby = array_value($table, 'orderby');
		$d = array_value($table, 'db');
		
		$d = $db ? $db : $d;
		if(!$d) return FALSE;
		
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$cols = $col ? implode(',', $col) : '*';
		return db_sql_find_one("SELECT $cols FROM {$d->tablepre}$table $cond$orderby LIMIT 1", $d);
		
	} elseif(strtoupper(substr($table, 0, 7)) != 'SELECT ') {
		
		$d = $db ? $db : $d;
		if(!$d) return FALSE;
		
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$cols = $col ? implode(',', $col) : '*';
		return db_sql_find_one("SELECT $cols FROM {$d->tablepre}$table $cond$orderby LIMIT 1", $d);
		
	} else {
		
		$d = $db ? $db : $d;
		if(!$d) return FALSE;
		
		// 兼容 XiunoPHP 3.0
		// $sql, $abort(废弃)
		return db_sql_find_one($table, $d);
	}
	*/
}

// 保存 $db 错误到全局
function db_errno_errstr($r, $d = NULL) {
	global $erno, $errstr;
	if($r === FALSE) { //  && $d->errno != 0
		$errno = $d->errno;
		$errstr = db_errstr_safe($errno, $d->errstr);
		$s = "sql errno: ".$errno.", errstr: ".$errstr;
		xn_log($s, 'db_error');
	}
}

// 安全的错误信息
function db_errstr_safe($errno, $errstr) {
	if(DEBUG) return $errstr;
	if($errno == 1049) {
		return '数据库名不存在，请手工创建';
	} elseif($errno == 2003 ) {
		return '连接数据库服务器失败，请检查IP是否正确，或者防火墙设置';
	} elseif($errno == 1024) {
		return '连接数据库失败';
	} elseif($errno == 1045) {
		return '数据库账户密码错误';
	}
	return $errstr;
}

//-----------------------------------> 表结构和索引相关，不常用

/*
$ddl = array(
	array('uid', 'int(11)'),
	array('regip', 'int(11)'),
	array('regdate', 'int(11)'),
	array('username', 'char(16)'),
	array('password', 'char(32)'),
);
*/
/*
function db_table_create($tablename, $ddl) {
	global $db;
	return $db->table_create($tablename, $ddl);
}

// 删除表
function db_table_drop($tablename) {
	global $db;
	return $db->table_drop($tablename);
}

// 增加一列，默认增加到最后
function db_table_column_add($tablename, $ddl, $after = '') {
	global $db;
	return $db->table_table_column_add($tablename, $ddl, $after);
}

// 删除一列，默认增加到最后
function db_table_column_drop($tablename, $colnames) {
	global $db;
	return $db->table_table_column_drop($tablename, $colnames);
}

// 添加索引 $index = array('gid'=>1, 'create_date'=>-1)
function db_index_create($tablename, $index) {
	global $db;
	return $db->index_create($tablename, $index);
}

// 删除索引 $index = array('gid'=>1, 'create_date'=>-1)
function db_index_drop($tablename, $index) {
	global $db;
	return $db->index_drop($tablename, $index);
}
*/


//----------------------------------->  表结构和索引相关 end
/*
$cond = array('id'=>123, 'groupid'=>array('>'=>100, 'LIKE'=>'\'jack'));
$s = db_cond_to_sqladd($cond);
echo $s;

WHERE id=123 AND groupid>100 AND groupid LIKE '%\'jack%' 

// 格式：
array('id'=>123, 'groupid'=>123)
array('id'=>array(1,2,3,4,5))
array('id'=>array('>' => 100, '<' => 200))
array('username'=>array('LIKE' => 'jack'))
*/

function db_cond_to_sqladd($cond) {
	$s = '';
	if(!empty($cond)) {
		$s = ' WHERE ';
		foreach($cond as $k=>$v) {
			if(!is_array($v)) {
				$v = (is_int($v) || is_float($v)) ? $v : "'".addslashes($v)."'";
				$s .= "`$k`=$v AND ";
			} elseif(isset($v[0])) {
				// OR 效率比 IN 高
				$s .= '(';
				//$v = array_reverse($v);
				foreach ($v as $v1) {
					$v1 = (is_int($v1) || is_float($v1)) ? $v1 : "'".addslashes($v1)."'";
					$s .= "`$k`=$v1 OR ";
				}
				$s = substr($s, 0, -4);
				$s .= ') AND ';
				
				/*
				$ids = implode(',', $v);
				$s .= "$k IN ($ids) AND ";
				*/
			} else {
				foreach($v as $k1=>$v1) {
					if($k1 == 'LIKE') {
						$k1 = ' LIKE ';
						$v1="%$v1%";
					}
					$v1 = (is_int($v1) || is_float($v1)) ? $v1 : "'".addslashes($v1)."'";
					$s .= "`$k`$k1$v1 AND ";
				}
			}
		}
		$s = substr($s, 0, -4);
	}
	return $s;
}

function db_orderby_to_sqladd($orderby) {
	$s = '';
	if(!empty($orderby)) {
		$s .= ' ORDER BY ';
		$comma = '';
		foreach($orderby as $k=>$v) {
			$s .= $comma."`$k` ".($v == 1 ? ' ASC ' : ' DESC ');
			$comma = ',';
		}
	}
	return $s;
}


/*
	$arr = array(
		'name'=>'abc',
		'stocks+'=>1,
		'date'=>12345678900,
	)
	db_array_to_update_sqladd($arr);
*/
function db_array_to_update_sqladd($arr) {
	$s = '';
	foreach($arr as $k=>$v) {
		$v = addslashes($v);
		$op = substr($k, -1);
		if($op == '+' || $op == '-') {
			$k = substr($k, 0, -1);
			$v = (is_int($v) || is_float($v)) ? $v : "'$v'";
			$s .= "`$k`=$k$op$v,";
		} else {
			$v = (is_int($v) || is_float($v)) ? $v : "'$v'";
			$s .= "`$k`=$v,";
		}
	}
	return substr($s, 0, -1);
}

/*
	$arr = array(
		'name'=>'abc',
		'date'=>12345678900,
	)
	db_array_to_insert_sqladd($arr);
*/
function db_array_to_insert_sqladd($arr) {
	$s = '';
	$keys = array();
	$values = array();
	foreach($arr as $k=>$v) {
		$k = addslashes($k);
		$v = addslashes($v);
		$keys[] = '`'.$k.'`';
		$v = (is_int($v) || is_float($v)) ? $v : "'$v'";
		$values[] = $v;
	}
	$keystr = implode(',', $keys);
	$valstr = implode(',', $values);
	$sqladd = "($keystr) VALUES ($valstr)";
	return $sqladd;
}

?>