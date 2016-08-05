<?php

include '../xiunophp/xiunophp.php';

$arr = array(1=>array('fid'=>1));
$s = xn_json_encode($arr);
echo $s;

$arr = xn_json_decode($s);

print_r($arr);
