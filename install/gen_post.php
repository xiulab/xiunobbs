<?php

chdir('./');

define('DEBUG', 0);
define('APP_NAME', 'bbs');

define('IN_SAE', class_exists('SaeKV'));

$conf = (@include './conf/conf.php');

// 支持 SAE
IN_SAE AND include './conf/sae.conf.php';
include './xiunophp/xiunophp.php';
include './model.inc.php';

$browser = get__browser();
check_browser($browser);

runtime_init();
online_init();
for($i=1; $i<1000; $i++) {
	$subject = '欢迎使用 Xiuno BBS 3.0 新一代论坛系统。'.$i;
	$message = '祝您使用愉快！';
	$thread = array(
		'fid'=>1,
		'uid'=>1,
		'subject'=>$subject,
		'message'=>$message,
		'seo_url'=>'',
		'time'=>$time,
		'longip'=>$longip,
	);
	$tid = thread_create($thread, $longip);
	for($j=0; $j<10; $j++) {
		$post = array(
			'tid'=>$tid,
			'uid'=>1,
			'create_date'=>$time,
			'userip'=>$longip,
			'isfirst'=>0,
			'message'=>$message.rand(1, 10000),
		);
		$pid = post_create($post, 1);
	}
	if($i % 100 == 0) echo '.';
}

cron_run(1);

echo '生成数据完毕';

?>
