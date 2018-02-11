<?php exit;



// 增加一条编辑历史 $pid, $uid, $time, $message
$update_reason = param('update_reason');
$logid = post_update_log_create(array(
	'pid'=>$pid,
	'uid'=>$uid,
	'create_date'=>$time,
	'reason'=>$update_reason,
	'message'=>$post['message_fmt'],
));


$logid AND post__update($pid, array('updates+'=>1, 'last_update_uid'=>$uid, 'last_update_date'=>$time, 'last_update_reason'=>$update_reason));

?>