<?php

// hook model_inc_start.php

// 可以合并成一个文件，加快速度
// merge to one file.

// hook model_inc_start.php

if(DEBUG) {
	
	include _include(APP_PATH.'model/kv.func.php');	// 
	include _include(APP_PATH.'model/group.func.php');
	include _include(APP_PATH.'model/user.func.php');
	include _include(APP_PATH.'model/forum.func.php');
	include _include(APP_PATH.'model/forum_access.func.php');
	include _include(APP_PATH.'model/thread.func.php');
	include _include(APP_PATH.'model/thread_top.func.php');
	include _include(APP_PATH.'model/post.func.php');
	include _include(APP_PATH.'model/attach.func.php');
	include _include(APP_PATH.'model/check.func.php');
	include _include(APP_PATH.'model/mythread.func.php');
	include _include(APP_PATH.'model/runtime.func.php');
	include _include(APP_PATH.'model/table_day.func.php');
	include _include(APP_PATH.'model/cron.func.php');
	include _include(APP_PATH.'model/session.func.php');	// 
	
	// hook model_inc_include.php
	
} else {
	
	$model_min_file = $conf['tmp_path'].'model.min.php';
	$isfile = is_file($model_min_file);
	if(!$isfile) {
		$s = php_strip_whitespace(_include(APP_PATH.'model/kv.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/group.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/user.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/forum.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/forum_access.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/thread.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/thread_top.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/post.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/attach.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/check.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/mythread.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/runtime.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/table_day.func.php'));
		$s .= php_strip_whitespace(_include(APP_PATH.'model/cron.func.php'));
		$s .= xn_php_strip_whitespace(_include(APP_PATH.'model/session.func.php'));
		// hook model_inc_merge.php
		
		$r = file_put_contents($model_min_file, $s);
		unset($s);
	}
	include _include($model_min_file);
	
}

// hook model_inc_end.php



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