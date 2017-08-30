<?php

/*
	Xiuno BBS 4.0 插件：合并版块
	admin/plugin-setting-xn_forum_merge.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$action = param(3);

if(empty($action)) {
	
	// 选择版块
	if($method == 'GET') {
		
		include _include(APP_PATH.'plugin/xn_forum_merge/setting.htm');
		
	} else {
		$fid1 = param('fid1', 0); // 保留
		$fid2 = param('fid2', 0); // 删除
		$tag_id_arr = param('tag_id_arr', array(0));
		
		$forum1 = forum_read($fid1);		
		$forum2 = forum_read($fid2);	
		
		$fid1 == $fid2 AND message('fid2', '请选择不同的版块');
		empty($forum1) AND message('fid1', '版块1 不存在');	
		empty($forum2) AND message('fid2', '版块2 不存在');
		if($forum2['threads'] > 50000) {
			message('fid2', '为了防止超时，大于 50000 主题数不能合并，请手工修改代码 plugin/xn_forum_merge/setting.php 调整该限制。');	
		}
		
		//$forum1['threads'] += $forum2['threads'];
		//$forum1['posts'] += $forum2['posts'];
		if(!empty($tag_id_arr)) {
			$arrlist = db_find('thread', array('fid'=>$fid2), array(), 1, 50000, '', array('tid'));
			foreach($arrlist as $arr) {
				foreach($tag_id_arr as $cateid => $tagid) {
					tag_thread_create($tagid, $arr['tid']);
				}
			}
		}
		
		forum_update($fid1, array('threads+'=>$forum2['threads']));
		db_update('thread', array('fid'=>$fid2), array('fid'=>$fid1));
		db_update('post', array('fid'=>$fid2), array('fid'=>$fid1));
		db_update('thread_top', array('fid'=>$fid2), array('fid'=>$fid1));
		db_update('session', array('fid'=>$fid2), array('fid'=>$fid1));
		
		forum_delete($fid2);
		//db_delete('forum_access', array('fid'=>$fid2));
		
		// 清理老论坛的 tag 和关联数据
		db_update('thread', array('fid'=>$fid1), array('tagids'=> '', 'tagids_time'=> 0));
		tag_cate_delete_by_fid($fid2);
		
		// 清理缓存
		cache_truncate();
		
		message(0, '合并版块成功');
	}
}
?>