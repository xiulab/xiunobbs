<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

if($conf['nav_2_bbs_on']) {
	
	// 自定义首页在这里写代码：
	
	
	// 自定义模板文件路径，修改下面文件：
	// hook xn_nav_2_include_index_htm_before.php
	include _include(APP_PATH.'plugin/xn_nav_2/view/htm/index.htm');
} else {
	
	// hook xn_nav_2_include_index_php_before.php
	include _include(APP_PATH.'route/index.php');
}


?>