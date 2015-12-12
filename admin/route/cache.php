<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if($action == 'clear') {

	if($method == 'GET') {

		$header['title'] = '清理缓存';

		include "./admin/view/cache_clear.htm";

	} elseif ($method == 'POST') {
		
		$old = $runtime;
		$clearcache = param('clearcache', 0);
		$clearcache AND $r = cache_truncate();
		
		// 清空缓存会导致今日发帖丢失
		runtime_init();
		runtime_set('todayposts', $old['todayposts']);
		runtime_set('todayusers', $old['todayusers']);
		runtime_set('todaythreads', $old['todaythreads']);
		
		$rebuildmaxid = param('rebuildmaxid', 0);
		if($rebuildmaxid) {
			@ignore_user_abort(TRUE);
			@set_time_limit(0);
			table_day_rebuild();
		}
		
		thread_new_sitemap();
		
		message(0, '清理成功');
	}

} elseif($action == 'maxid') {
	
}

?>
