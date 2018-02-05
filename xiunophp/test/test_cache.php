<?php

$conf = array();
$conf['cache']['enable'] = true;
$conf['cache']['type'] = 'mysql';
$conf['cache']['mysql'] = array(
	'type' => 'pdo_mysql',
	'pdo_mysql' => array (
		'master' => array (
			'host' => '127.0.0.1',
			'user' => 'root',
			'password' => 'root',
			'name' => 'xiuno8',
			'tablepre' => 'bbs_',
			'charset' => 'utf8',
			'engine' => 'myisam',
		),
		'slaves' => array (),
	),
);

include '../xiunophp.php';

$r = cache_get('test2');
x('cache_get test2:', $r, NULL);

$r = cache_set('test', array('123'));
x('cache_set', $r, true);

$r = cache_get('test');
x('cache_get', $r[0], '123');

$r = cache_delete('test');
x('cache_delete', $r, TRUE);

$r = cache_get('test');
x('cache_get', $r, NULL);

$r = cache_truncate();
x('cache_truncate', $r, TRUE);

function x($info, $a, $b) {
        echo "$info: ... ".($a === $b ? '[ok]' : var_export($a, 1).", except:".var_export($b, 1))."\r\n";
}
?>