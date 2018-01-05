<?php exit;

if($action == 'highlight') {
	
	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, lang('please_choose_thread'));
	
	$highlight = param('highlight');
	if($highlight==0){
		$color['id']=0;
	}else{
		$color = db_find_one('subject_style',array('id'=>$highlight));
	}
	
	
	$threadlist = thread_find_by_tids($tidarr);

	foreach($threadlist as &$thread) {
		$fid = $thread['fid'];
		$tid = $thread['tid'];
		if(forum_access_mod($fid, $gid, 'allowtop')) {
			thread_update($tid, array('style_id'=>$color['id']));
			$arr = array(
				'uid' => $uid,
				'tid' => $thread['tid'],
				'pid' => $thread['firstpid'],
				'subject' => $thread['subject'],
				'comment' => '',
				'create_date' => $time,
				'action' => 'highlight',
			);
			modlog_create($arr);
		}
	}
	
	message(0, lang('set_completely'));
	
}elseif($action == 'get_style'){
	$data = db_sql_find('SELECT `name` FROM `bbs_subject_style`');
	xn_message(0,$data);
}

?>