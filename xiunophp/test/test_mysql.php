<?php

$conf = include '../../conf/conf.php';

include '../xiunophp.php';

$r = db_exec("INSERT INTO bbs_user SET uid='1000', username='test1000'");
x('insert', $r, '1000');

$arr = db_sql_find_one("SELECT * FROM bbs_user WHERE uid='1000'");
x('select', $arr['uid'], '1000');

$r = db_exec("DELETE FROM bbs_user WHERE uid='1000'");
x('insert', $r, 1);


function x($info, $a, $b) {
	echo "$info: ... ".($a === $b ? 'true' : 'false'.", ".var_export($a, 1).", ".var_export($b, 1))."\r\n";
}
?>