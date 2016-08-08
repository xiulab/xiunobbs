<?php

chdir('../');

define('SKIP_ROUTE', TRUE);

include './index.php';

for($i=1; $i<1000; $i++) {
	$subject = '欢迎使用 Xiuno BBS 4.0 新一代论坛系统。'.$i;
	$message = '祝您使用愉快！';
	$thread = array(
		'fid'=>1,
		'uid'=>1,
		'subject'=>$subject,
		'message'=>$message,
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
		$pid = post_create($post, 1, 1);
	}
	if($i % 100 == 0) echo '.';
}

echo '生成数据完毕';

?>
