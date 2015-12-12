<?php

return  array (

	// -------------> xiunophp 依赖的配置
	'cache'=> array(
		'enable' => TRUE,
		'type'=> 'xcache', // apc/xcache/yac/redis/memcached/mysql/saekv
		'memcached'=> array (
			'host'=>'localhost',
			'port'=>'11211',
		),
		'redis'=> array (
			'host'=>'localhost',
			'port'=>'6379',
		),
	),
	
	'tmp_path' => './',			// 可以配置为 linux 下的 /dev/shm ，通过内存缓存临时文件
	'log_path' => './'
);
?>