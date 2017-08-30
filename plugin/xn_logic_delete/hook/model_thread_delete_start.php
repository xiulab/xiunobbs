<?php exit;

// 如果已经逻辑删除过，则需要做一下处理，否则按照正常逻辑走。
// 防止减 2 次。
if($thread['deleted'] == 1) {
	forum__update($fid, array('threads+'=>1));
	user__update($uid, array('threads+'=>1));
	runtime_set('threads+', 1);

}