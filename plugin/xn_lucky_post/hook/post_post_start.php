<?php exit;

if($thread['is_lucky_thread']) {
	$pidarr = db_find('post', array('tid'=>$tid), array(), 1, 10000, '', array('pid', 'uid'));
	foreach($pidarr as $arr) {
		if($arr['uid'] == $uid && $gid != 1) {
			message(-1, '此类型的主题只能回复一次！');
		}
	}

	if($thread['posts'] >= 10000) {
		message(-1, '最多只能回复 10000 楼！');
	}

}
?>