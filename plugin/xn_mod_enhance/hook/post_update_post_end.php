<?php exit;

// 增加一条编辑历史 $pid, $uid, $time, $message
$logid = post_update_log_create(array(
	'pid'=>$pid,
	'uid'=>$uid,
	'create_date'=>$time,
	'message'=>$message,
));

$logid AND post__update($pid, array('updates+'=>1));

?>