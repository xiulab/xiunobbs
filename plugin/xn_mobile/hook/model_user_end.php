<?php exit;

function user_read_by_mobile($mobile) {
	global $g_static_users;
	// hook model_user_read_by_mobile_start.php
	$user = db_find_one('user', array('mobile'=>$mobile));
	user_format($user);
	$g_static_users[$user['uid']] = $user;
	// hook model_user_read_by_mobile_end.php
	return $user;
}


?>