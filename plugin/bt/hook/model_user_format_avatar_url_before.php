<?php exit;

$dir = substr(sprintf("%09d", $user['uid']), 0, 3);
$dir = xn_get_dir($user['uid']);

?>