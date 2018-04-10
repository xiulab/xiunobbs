<?php

/*
* Copyright (C) 2015 xiuno.com
*/

!defined('DEBUG') AND exit('Access Denied.');

if($conf['nav_2_bbs_on']) {
	include _include(APP_PATH.'plugin/xn_nav_2/view/htm/nav_2_index.htm');
} else {
	include _include(APP_PATH.'route/index.php');
}


?>