<?php

/*
	APP 配置文件模板，由 skel.php 自动生成
*/
return array (

	// -------------> xiunophp 配置
	// PHP 高版本，默认支持 pdo_mysql
	'db'=>array(
		'type'=>'mysql',
		'mysql' => array (
			'master' => array (		
				'host' => 'localhost',	// 非默认端口写法：localhost:3306						
				'user' => 'root',				
				'password' => 'root',				
				'name' => 'test',
				'tablepre' => 'app_',
				'charset' => 'utf8',				
				'engine'=>'myisam', // innodb
				
			),			
			'slaves' => array()
		),
		'pdo_mysql' => array (
			'master' => array (								
				'host' => 'localhost',				
				'user' => 'root',
				'password' => 'root',	
				'name' => 'test',
				'tablepre' => 'app_',
				'charset' => 'utf8',				
				'engine'=>'myisam',  // innodb
			),			
			'slaves' => array()
		)
	),
	'cache'=> array(
		'enable' => FALSE,
		'type'=> 'mysql', // apc/xcache/redis/memcached/mysql
		'memcached'=> array (
			'host'=>'localhost',
			'port'=>'11211',
			'cachepre'=>'app_',
		),
		'redis'=> array (
			'host'=>'localhost',
			'port'=>'6379',
			'cachepre'=>'app_',
		),
		'mysql'=> array (
			'cachepre'=>'app_',
		),
	),
	
	'tmp_path' => './tmp/',			// 可以配置为 linux 下的 /dev/shm ，通过内存缓存临时文件
	'log_path' => './log/',			// 日志目录
	'timezone' => 'Asia/Shanghai',		// 时区
	'url_rewrite_on' => 0,			// 是否开启 URL-Rewrite, 0: /?user-login.htm 1: /user-login.htm 2: /?/user/login/ 3: /user/login
	
);

?>