<?php

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

$sql = "DROP TABLE IF EXISTS {$tablepre}post_update_log;";
$r = db_exec($sql);

?>