<?php

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

$sql = "ALTER TABLE {$tablepre}post DROP COLUMN updates, DROP COLUMN last_update_date, DROP COLUMN last_update_uid, DROP COLUMN last_update_reason";
$r = db_exec($sql);

$sql = "DROP TABLE IF EXISTS {$tablepre}post_update_log;";
$r = db_exec($sql);


?>