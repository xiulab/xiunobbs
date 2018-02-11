<?php

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;

$sql = "ALTER TABLE {$tablepre}post ADD COLUMN last_update_date int NOT NULL default '0', ADD COLUMN last_update_uid int NOT NULL default '0', ADD COLUMN last_update_reason varchar(128) NOT NULL default ''";
$r = db_exec($sql);

$sql = "ALTER TABLE {$tablepre}post_update_log ADD COLUMN reason varchar(128) NOT NULL DEFAULT ''";
$r = db_exec($sql);

?>