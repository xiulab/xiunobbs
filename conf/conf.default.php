<?php

/*
	Xiuno BBS 4.0
	支持多台 DB，主从配置好以后，xn 会自动根据 SQL 读写分离。
	支持各种 cache，本机 apc/xcache/yac, 网络: redis/memcached/ttserver/mysql
	支持 CDN，细化可以单独针对 view upload 目录做 CDN，开启 CDN 后，配置 cdn_on 为 1
	支持临时目录设置，独立 Linux 主机，可以设置为 /dev/shm 通过内存加速
	
	主要从分布式部署的角度考虑，将变量存到 conf/conf.php 和 kv 表
*/
return array (

	// -------------> xiunophp 依赖的配置
	'db'=>array(
		'type'=>'mysql',
		'mysql' => array (
			'master' => array (		
				'host' => 'localhost',	// 非默认端口写法：localhost:3306						
				'user' => 'root',				
				'password' => 'root',				
				'name' => 'test',
				'tablepre' => 'bbs_',
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
				'tablepre' => 'bbs_',
				'charset' => 'utf8',				
				'engine'=>'myisam',  // innodb
			),			
			'slaves' => array()
		)
	),
	'cache'=> array(
		'enable' => TRUE,
		
		'type'=> 'mysql', // apc/xcache/redis/memcached/mysql
		'memcached'=> array (
			'host'=>'localhost',
			'port'=>'11211',
			'cachepre'=>'bbs_',
		),
		'redis'=> array (
			'host'=>'localhost',
			'port'=>'6379',
			'cachepre'=>'bbs_',
		),
		'mysql'=> array (
			'cachepre'=>'bbs_',
		),
	),
	
	'tmp_path' => './tmp/',			// 可以配置为 linux 下的 /dev/shm ，通过内存缓存临时文件
	'log_path' => './log/',			// 日志目录
	
	// -------------> xiuno bbs 配置
	
	'view_url' => 'view/',			// 可以设置为 cdn 的 url
	'upload_url' => 'upload/',		// 可以设置为 cdn 的 url
	'upload_path' => './upload/',		// 可以多台 web 时，可以通过 nfs 映射到文件服务器，文件服务器单独配置域名 upload_url
	
	'sitename' => 'Xiuno BBS',
	'timezone' => 'Asia/Shanghai',
	'lang' => 'zh-cn',
	'runlevel'=>5,									// 0: 站点关闭; 1: 管理员可读写; 2: 会员可读;  3: 会员可读写; 4：所有人只读; 5: 所有人可读写
	'runlevel_reason' => '站点正在维护中，请稍后访问。',				// 权限不足时，提示信息。
	
	// 'cookie_pre' => '',
	'cookie_domain' => '',
	'cookie_path' => '',
	'auth_key' => 'efdkjfjiiiwurjdmclsldow753jsdj438',

	'pagesize' => 20,
	'postlist_pagesize' => 1000,				// 详情页显示的回复数
	'cache_thread_list_pages' => 10,			// 缓存主题列表前 N 页
	
	'online_update_span' => 120,				// 在线更新的频度：单位秒，建议 120 秒(2分钟）
	'online_hold_time' => 3600,				// 多长时间以内的活跃用户为在线
	'session_delay_update' => 0,				// session 延迟更新，服务器压力巨大的时候，开启此项，默认为秒，一般设置为 300 秒
	
	'upload_image_width' => 927,				// 上传图片最大宽度 927 887 回复的帖子宽度减去 40 
	
	'new_thread_days' => 3,					// 定义几天内的帖子为最新贴，默认一天
	
	'order_default' => 'lastpid',				// 默认排序
	'update_views_on' => 1,					// 是否更新点击次数，比较消耗资源，大站请关闭此项。
	
	'version' => '4.0',		//
	'cdn_on' => 1,						// 是否启用 CDN，将改变IP的获取方式
	
	'url_rewrite_on' => 0,					// 是否开启 URL-Rewrite, 0: /?user-login.htm 1: /user-login.htm 2: /?/user/login/ 3: /user/login
	'user_create_email_on' => 0,				// 是否开启邮箱验证
	'user_resetpw_on' => 0,					// 是否开启密码找回，需要 SMTP 有效。
	 
	'version' => '3.0',					// 版本
	'installed' => 0,					// 安装时间
);

?>