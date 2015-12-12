<?php

// 计划任务
function cron_run($force = 0) {
	global $conf, $time, $forumlist, $runtime;
	$cron_1_last_date = runtime_get('cron_1_last_date');
	$cron_2_last_date = runtime_get('cron_2_last_date');
	
	$t = $time - $cron_1_last_date;
	
	if($t > 300 || $force) {
		$lock = cache_get('cron_lock_1');
		if($lock === NULL) {
			cache_set('cron_lock_1', 1, 10); // 设置 10 秒超时
			
			// 清理在线
			online_gc();
			
			runtime_set('cron_1_last_date', $time);
			
			cache_delete('cron_lock_1');
		}
	}
	
	$t = $time - $cron_2_last_date;
	if($t > 86400 || $force) {
		
		$lock = cache_get('cron_lock_2'); // 高并发下, mysql 机制实现的锁锁不住，但是没关系
		if($lock === NULL) {
			cache_set('cron_lock_2', 1, 10); // 设置 10 秒超时
			
			// 每日统计清 0
			runtime_set('todayposts', 0);
			runtime_set('todaythreads', 0);
			runtime_set('todayusers', 0);
			
			foreach($forumlist as $fid=>$forum) {
				forum__update($fid, array('todayposts'=>0, 'todaythreads'=>0));
			}
			forum_list_cache_delete();
			
			
			// 清理最新发帖，只保留 100 条。
			thread_new_gc();
			thread_lastpid_gc();
			
			// 清理在线
			online_gc();
			
			// 清理临时附件
			attach_gc();
			
			// 清空每日 IP 限制
			ipaccess_truncate();
			
			// 清理游客喜欢限制
			guest_agree_truncate();
			
			list($y, $n, $d) = explode(' ', date('Y n j', $time)); 	// 0 点
			$today = mktime(0, 0, 0, $n, $d, $y);			// -8 hours
			runtime_set('cron_2_last_date', $today, TRUE);		// 加到1天后
			
			// 每日生成最新的 sitemap
			thread_new_sitemap();
			
			// 往前推8个小时，尽量保证在前一天
			table_day_cron($time - 8 * 3600);
			
			cache_delete('cron_lock_2');
		}
		
	}
}


?>