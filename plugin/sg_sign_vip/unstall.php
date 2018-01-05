<?php

/*
	Xiuno BBS 4.0 每日签到卸载
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$r = db_exec("DROP TABLE IF EXISTS {$tablepre}sg_sign;");
$r === FALSE AND message(-1, '卸载签到表sg_sign失败');
$r = db_exec("DROP TABLE IF EXISTS {$tablepre}sg_sign_set;");
$r === FALSE AND message(-1, '卸载签到表sg_sign_set失败');

?>