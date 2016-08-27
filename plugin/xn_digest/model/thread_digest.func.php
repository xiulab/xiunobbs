<?php

function thread_digest_delete($tid, $uid) {
	$r = db_delete('thread_digest', array('tid'=>$tid));
	if($r !== FALSE) {
		user_update($uid, array('digests-'=>1));
	}
	return $r;
}

function thread_digest_create($tid, $digest, $uid) {
	$r = db_create('thread_digest', array('tid'=>$tid, 'digest'=>$digest));
	if($r !== FALSE) {
		user_update($uid, array('digests+'=>1));
	}
	return $r;
}

function thread_digest_change($tid, $digest, $uid) {
	if($digest == 0) {
		thread_digest_delete($tid, $uid);
	} else {
		thread_digest_create($tid, $digest, $uid);
	}
	thread_update($tid, array('digest'=>$digest));
}

?>