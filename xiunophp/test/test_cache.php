<?php

define('APP_NAME', 'test');

chdir(getcwd().'/../');

$conf = include './conf/conf.php';
$conf['cache']['type'] = 'memcached';

include './xiunophp/xiunophp.php';

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