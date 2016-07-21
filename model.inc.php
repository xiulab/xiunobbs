<?php

// 可以合并成一个文件，加快速度
/*
include './model/kv.func.php';
include './model/group.func.php';
include './model/user.func.php';
include './model/forum.func.php';
include './model/forum_access.func.php';
include './model/thread.func.php';
include './model/thread_new.func.php';
include './model/thread_top.func.php';
include './model/thread_lastpid.func.php';
include './model/post.func.php';
include './model/attach.func.php';
include './model/check.func.php';
include './model/mythread.func.php';
include './model/runtime.func.php';
include './model/online.func.php';
include './model/table_day.func.php';
include './model/cron.func.php';
include './model/banip.func.php';	// 
include './model/ipaccess.func.php';	// 
include './model/misc.func.php';	// 杂项
include './model/plugin.func.php';	// 
include './model/session.func.php';	// 
*/


// 实际测试，加速非常有限，反而增加了复杂度。
$model_merge_file = './tmp/model.min.php';

$include_merge_model = FALSE;
$isfile = is_file($model_merge_file);
if(!DEBUG && !$isfile) {
	$s = php_strip_whitespace('./model/kv.func.php');
	$s .= php_strip_whitespace('./model/group.func.php');
	$s .= php_strip_whitespace('./model/user.func.php');
	$s .= php_strip_whitespace('./model/forum.func.php');
	$s .= php_strip_whitespace('./model/forum_access.func.php');
	$s .= php_strip_whitespace('./model/thread.func.php');
	$s .= php_strip_whitespace('./model/thread_new.func.php');
	$s .= php_strip_whitespace('./model/thread_top.func.php');
	$s .= php_strip_whitespace('./model/thread_lastpid.func.php');
	$s .= php_strip_whitespace('./model/post.func.php');
	$s .= php_strip_whitespace('./model/attach.func.php');
	$s .= php_strip_whitespace('./model/check.func.php');
	$s .= php_strip_whitespace('./model/mythread.func.php');
	$s .= php_strip_whitespace('./model/runtime.func.php');
	$s .= php_strip_whitespace('./model/online.func.php');
	$s .= php_strip_whitespace('./model/table_day.func.php');
	$s .= php_strip_whitespace('./model/cron.func.php');
	$s .= php_strip_whitespace('./model/banip.func.php');
	$s .= php_strip_whitespace('./model/ipaccess.func.php');
	$s .= php_strip_whitespace('./model/misc.func.php');
	$s .= php_strip_whitespace('./model/plugin.func.php');
	$s .= xn_php_strip_whitespace('./model/session.func.php');
	$include_merge_model = file_put_contents($model_merge_file, $s);
	unset($s);
}

if($isfile) {
	include $model_merge_file;
	//include './model/session.func.php';	// 
} else {
	include './model/kv.func.php';
	include './model/group.func.php';
	include './model/user.func.php';
	include './model/forum.func.php';
	include './model/forum_access.func.php';
	include './model/thread.func.php';
	include './model/thread_new.func.php';
	include './model/thread_top.func.php';
	include './model/thread_lastpid.func.php';
	include './model/post.func.php';
	include './model/attach.func.php';
	include './model/check.func.php';
	include './model/mythread.func.php';
	include './model/runtime.func.php';
	include './model/online.func.php';
	include './model/table_day.func.php';
	include './model/cron.func.php';
	include './model/banip.func.php';	// 
	include './model/ipaccess.func.php';	// 
	include './model/misc.func.php';	// 杂项
	include './model/plugin.func.php';	// 
	include './model/session.func.php';	// 
}


function xn_php_strip_whitespace($file) {
	$s = php_strip_whitespace($file);
	if(substr($s, 0, 5) == '<?php') {
		$s = substr($s, 5);
	}
	if(substr($s, -2) == '?>') {
		$s = substr($s, 0, -2);
	}
	return $s;
}
?>