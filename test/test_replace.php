<?php

include '../xiunophp/xiunophp.php';

$r = file_replace_var('./conf.php', array('version'=>'bb\\\''));
$r = file_replace_var('./conf.json', array('version'=>'bb\\\''), TRUE);

echo $r;
