<?php

function kv_get($k) {
	$arr = db_find_one('kv', array('k'=>$k));
	return $arr ? xn_json_decode($arr['v']) : NULL;
}

function kv_set($k, $v, $life = 0) {
	$arr = array(
		'k'=>$k,
		'v'=>$v,
	);
	$r = db_replace('kv', $arr);
	return $r;
}

function kv_delete($k) {
	$r = db_delete('kv', array('k'=>$k));
	return $r;
}


?>