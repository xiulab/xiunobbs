<?php

// -------------> 用来统计表每天的最大ID，有利于加速！
function table_day_read($table, $year, $month, $day) {
	$arr = db_find_one("SELECT * FROM `bbs_table_day` WHERE year='$year' AND month='$month' AND day='$day' AND `table`='$table'");
	return $arr;
}

/*
	// 支持两种日期格式：年-月-日 or UNIXTIMESTAMP
	$maxtid = table_day_maxid('bbs_thread', '2014-9-1');
	$maxtid = table_day_maxid('bbs_thread', 1234567890);

*/
function table_day_maxid($table, $date) {

	// 不能小于 2014-9-24，不能大于等于当前时间
	$mintime = 1411516800; // strtotime('2014-9-24');
	!is_numeric($date) AND $date = strtotime($date);
	if($date < $mintime) return 0;

	list($year, $month, $day) = explode('-', date('Y-n-j', $date));
	$arr = table_day_read($table, $year, $month, $day);
	return $arr ? intval($arr['maxid']) : 0;
}

/*
	每天0点0分执行一次！最好 linux crontab 计划任务执行，web 触发的不准确
	统计常用表的最大ID，用来削减日期类的索引，和加速查询。
*/
function table_day_cron($crontime = 0) {
	global $time;
	$crontime = $crontime ? $crontime : $time;
	list($y, $m, $d) = explode('-', date('Y-n-j', $crontime)); // 往前推8个小时，确保在前一天。
	
	$table_map = array(
		'bbs_thread'=>'tid',
		'bbs_post'=>'pid',
		'bbs_user'=>'uid',
	);
	foreach ($table_map as $table=>$col) {
		$arr = db_find_one("SELECT MAX(`$col`) maxid FROM `$table` WHERE create_date<$crontime");
		$maxid = $arr['maxid'];
	
		$arr = db_find_one("SELECT COUNT(*) `count` FROM `$table` WHERE create_date<$crontime");
		$count = $arr['count'];
	
		db_exec("REPLACE INTO bbs_table_day SET `year`='$y', `month`='$m', `day`='$d', `create_date`='$crontime', `table`='$table', `maxid`='$maxid', `count`='$count'");
	}
}

// 重新生成数据
function table_day_rebuild() {
	global $time;
	$user = user__read(1);
	$crontime = $user['create_date'];
	while($crontime < $time) {
		table_day_cron($crontime);
		$crontime = $crontime + 86400;
	}
}


?>