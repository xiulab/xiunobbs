<?php exit;

// 如果已经软删除了，则数值调整，防止重复减少
if($post['deleted'] == 1) {
	thread__update($tid, array('posts+'=>1));
	$uid AND user__update($uid, array('posts+'=>1));
	runtime_set('posts+', 1);
}

?>