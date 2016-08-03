<?php

define('DEBUG', 1); 				// 发布的时候改为 0 

$conf = include './conf/conf.php';

include '../../xiunophp.php';

include './model/misc.func.php';

// 测试数据库连接
// db_connect() OR message(-1, $errstr);

$route = param(0, 'index');

switch ($route) {
	case 'index': 	include './route/index.php'; 	break;
	case 'test': 	include './route/test.php'; 	break;
	default: http_404();
}

?>