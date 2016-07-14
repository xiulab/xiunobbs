<?php



function kv_get($k) {
	$k = addslashes($k);
	$arr = db_find_one("SELECT k,v FROM bbs_kv WHERE k='$k'");
	return $arr ? xn_json_decode($arr['v']) : NULL;
}

function kv_set($k, $v, $life = 0) {
	$k = addslashes($k);
	$v = addslashes(xn_json_encode($v));
	return db_exec("REPLACE INTO bbs_kv SET  k='$k', v='$v'");
}

function kv_delete($k) {
	$k = addslashes($k);
	return db_exec("DELETE FROM bbs_kv WHERE k='$k'");
}


?>