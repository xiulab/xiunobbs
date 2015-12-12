<?php

define('APP_NAME', 'test');

chdir(getcwd().'/../');

include './XiunoPHP.3.0.php';

$user = db_find_one("SELECT * FROM `user` WHERE uid='1'");
x('select', $user['uid'], '1');

$r = db_exec("INSERT INTO user SET uid=1000, username='test1000'");
x('insert', $r, 1000);

$arr = db_find_one("SELECT * FROM user WHERE uid='1000'");
x('select', $arr['uid'], '1000');

$r = db_exec("DELETE FROM user WHERE uid='1000'");
x('insert', $r, 1);

$r = db_find_one("SELECT * FROM user WHERE uid='0'");
x('select', $r, NULL);


function x($info, $a, $b) {
	echo "$info: ... ".($a === $b ? 'true' : 'false'.", ".var_export($a, 1).", ".var_export($b, 1))."\r\n";
}
?>