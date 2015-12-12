<?php

!defined('DEBUG') AND exit('Access Denied.');

include './model/modlog.func.php';

$action = param(1);

if($action == 'top') {

	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, '请选择主题');
	
	$header['title'] = '置顶';
	
	if($method == 'GET') {

		// 选中第一个
		$tid = $tidarr[0];
		$thread = thread_read($tid);
		
		include './mobile/view/mod_top.htm';

	}
	
} elseif($action == 'delete') {

	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, '请选择主题');
	
	$header['title'] = '删除';
	
	if($method == 'GET') {

		include './mobile/view/mod_delete.htm';

	}
	
} elseif($action == 'move') {

	$tids = param(2);
	$arr = explode('_', $tids);
	$tidarr = param_force($arr, array(0));
	empty($tidarr) AND message(-1, '请选择主题');
	
	$header['title'] = '移动';
	
	if($method == 'GET') {

		// 选中第一个
		$tid = $tidarr[0];
		$thread = thread_read($tid);
		
		include './mobile/view/mod_move.htm';

	}
	
}



?>