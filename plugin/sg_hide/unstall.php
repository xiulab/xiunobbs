<?php
!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE `{$tablepre}thread` DROP `hide1`;
ALTER TABLE `{$tablepre}thread` DROP `hide2`;
";

$r = db_exec($sql);
$r === FALSE AND message(-1, '卸载表失败');

?>