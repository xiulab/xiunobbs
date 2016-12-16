<?php exit;

if(isset($user['online_time'])) {
	$user['online_time_fmt'] = kanxue_stars($user['online_time'], $title);
	$user['online_time_title'] = $title;
} else {
	$user['online_time_fmt'] = '';
	$user['online_time_title'] = '';
}

?>