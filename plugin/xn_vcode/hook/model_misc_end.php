<?php exit;

function vcode_on($kv_code) {
	global $gid, $user, $time;
	if(empty($user)) return FALSE;
	$r = (!empty($kv_code['vcode_gids']) && in_array($gid, $kv_code['vcode_gids'])) &&
		(empty($kv_code['vcode_create_date_day_less']) || $time - $user['create_date'] < $kv_code['vcode_create_date_day_less'] * 86400);
	return $r;
}

?>