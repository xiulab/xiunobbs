<?php


// 可以合并成一个文件，加快速度
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


/*
// 实际测试，加速非常有限，反而增加了复杂度。
$model_merge_file = './tmp/model.inc.php';

$include_merge_model = FALSE;
$isfile = is_file($model_merge_file);
if(!DEBUG && !$isfile) {
	$s = php_strip_whitespace('./model/group.func.php');
	$s .= php_strip_whitespace('./model/user.func.php');
	$s .= php_strip_whitespace('./model/forum.func.php');
	$s .= php_strip_whitespace('./model/forum_access.func.php');
	$s .= php_strip_whitespace('./model/thread.func.php');
	$s .= php_strip_whitespace('./model/thread_new.func.php');
	$s .= php_strip_whitespace('./model/thread_top.func.php');
	$s .= php_strip_whitespace('./model/post.func.php');
	$s .= php_strip_whitespace('./model/attach.func.php');
	$s .= php_strip_whitespace('./model/check.func.php');
	$s .= php_strip_whitespace('./model/mythread.func.php');
	$s .= php_strip_whitespace('./model/runtime.func.php');
	$s .= php_strip_whitespace('./model/online.func.php');
	$s .= php_strip_whitespace('./model/table_day.func.php');
	$s .= php_strip_whitespace('./model/cron.func.php');
	$s .= php_strip_whitespace('./model/misc.func.php');
	$include_merge_model = file_put_contents($model_merge_file, $s);
	unset($s);
}

if($isfile) {
	include $model_merge_file;
} else {
	include './model/group.func.php';
	include './model/user.func.php';
	include './model/forum.func.php';
	include './model/forum_access.func.php';
	include './model/thread.func.php';
	include './model/thread_new.func.php';
	include './model/thread_top.func.php';
	include './model/post.func.php';
	include './model/attach.func.php';
	include './model/check.func.php';
	include './model/mythread.func.php';
	include './model/runtime.func.php';
	include './model/online.func.php';
	include './model/table_day.func.php';
	include './model/cron.func.php';
	include './model/misc.func.php';	// 杂项
}*/


/*
	message(0, '登录成功');
	message(1, '密码错误');
	message(-1, '数据库连接失败');
	
	code:
		< 0 全局错误，比如：系统错误：数据库丢失连接/文件不可读写
		= 0 正确
		> 0 一般业务逻辑错误，可以定位到具体控件，比如：用户名为空/密码为空
*/
function message($code, $message) {
	global $header;
	
	// 防止 message 本身出现错误死循环
	static $called = FALSE;
	$called ? exit("code: $code, message: $message") : $called = TRUE;
	
	if($ajax) {
		echo xn_json_encode(array('code'=>$code, 'message'=>$message));
		runtime_save();
	} else {
		$header['title'] = '提示信息';
		include "./view/htm/message.htm";
	}
	exit;
}

?>