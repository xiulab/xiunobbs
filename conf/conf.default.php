<?php

/*
	Xiuno BBS 3.0
	支持多台 DB，主从配置好以后，xn 会自动根据 SQL 读写分离。
	支持各种 cache，本机 apc/xcache/yac, 网络: redis/memcached/ttserver/mysql
	支持 CDN，细化可以单独针对 view upload 目录做 CDN，并且可以设置 CDN IP 白名单
	支持临时目录设置，独立 Linux 主机，可以设置为 /dev/shm 通过内存加速
*/
return array (

	// -------------> xiunophp 依赖的配置
	'db'=>array(
		'type'=>'mysql',
		'mysql' => array (
			'master' => array (								
				'host' => 'localhost',								
				'user' => 'root',				
				'password' => 'root',				
				'name' => 'test',				
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
				'charset' => 'utf8',				
				'engine'=>'myisam',  // innodb
			),			
			'slaves' => array()
		)
	),
	'cache'=> array(
		'enable' => TRUE,
		'type'=> 'mysql', // apc/xcache/yac/redis/memcached/mysql/saekv
		'memcached'=> array (
			'host'=>'localhost',
			'port'=>'11211',
		),
		'redis'=> array (
			'host'=>'localhost',
			'port'=>'6379',
		),
	),
	
	'tmp_path' => './tmp/',			// 可以配置为 linux 下的 /dev/shm ，通过内存缓存临时文件
	'log_path' => './log/',			// 日志目录
	
	// -------------> xiuno bbs 3.0 配置
	
	'static_url' => 'static/',		// 可以设置为 cdn 的 url
	'upload_url' => 'upload/',		// 可以设置为 cdn 的 url
	'upload_path' => './upload/',		// 可以多台 web 时，可以通过 nfs 映射到文件服务器，文件服务器单独配置域名 upload_url
	
	'sitename' => 'Xiuno BBS',
	'timezone' => 'Asia/Shanghai',
	'lang' => 'zh-cn',
	'runlevel'=>5,									// 0: 站点关闭; 1: 管理员可读写; 2: 会员可读;  3: 会员可读写; 4：所有人只读; 5: 所有人可读写
	'runlevel_reason' => '站点正在维护中，请稍后访问。',				// 权限不足时，提示信息。
	
	// 'cookie_pre' => '',
	'cookie_domain' => '',
	'cookie_path' => '/',
	'auth_key' => 'efdkjfjiiiwurjdmclsldow753jsdj438',

	'pagesize' => 20,
	'postlist_pagesize' => 1000,				// 详情页显示的回复数
	'cache_thread_list_pages' => 10,			// 缓存主题列表前 N 页
	
	'online_update_span' => 120,				// 在线更新的频度：单位秒，建议 120 秒(2分钟）
	'online_hold_time' => 3600,				// 多长时间以内的活跃用户为在线
	'seo_url_rewrite' => 1,					// 是否开启 SEO URL Rewrite，商业版本支持
	
	'upload_image_width' => 927,				// 上传图片最大宽度 927 887 回复的帖子宽度减去 40 
	
	'new_thread_days' => 3,					// 定义几天内的帖子为最新贴，默认一天
	
	'order_default' => 'lastpid',				// 默认排序
	'update_views_on' => 1,					// 是否更新点击次数，比较消耗资源，大站请关闭此项。
	
	// 分级显示，次数越多越显眼，顺序从大到小！
	'agrees_level' => array(30, 80, 150, 300),
	'posts_level' => array(10, 50, 100, 500),
  	
	'version' => '3.0',		//
	'cdn_on' => TRUE,		// 是否启用 CDN，将改变IP的获取方式
	/* 抛弃
	// // 可以设置 cdn ip 白名单，找 cdn 服务商要列表，一下为 360 cdn 实例
	'cdn_ip' => array (
		'183.136.133.*',
		'220.181.55.*',
		'101.226.4.*',
		'180.153.235.*',
		'122.143.15.*',
		'27.221.20.*',
		'202.102.85.*',
		'61.160.224.*',
		'112.25.60.*',
		'182.140.227.*',
		'221.204.14.*',
		'222.73.144.*',
		'61.240.144.*',
		'113.17.174.*',
		'125.88.189.*',
		'120.52.18.*',
	),*/
	
	// 文章分类，根据二次开发需求自行扩充
	'cate' => array(
		1=>'页脚文章',
		2=>'公司动态',
	),
	
	'user_create_email_on' => 0,	// 是否开启注册用户 Email 验证
	'user_find_pw_on' => 0,		// 是否开启密码找回，需要 SMTP 有效。
	'banip_on' => 0,		// 是否启用禁止 ip
	'ipaccess_on' => 0,		// 是否启用 ip 访问控制，开启后将会防止灌水，每天的上限
	'ipaccess' => array('mails'=>0, 'users'=>0, 'threads'=> 0, 'posts'=> 0, 'attachs'=>0, 'attachsizes'=>0, 'action1'=>0, 'action2'=>0, 'action3'=>0, 'action4'=>0),
	'check_flood_on' => 0,		// 开启防止灌水
	'check_flood' => array('users'=>10, 'posts'=>10, 'threads'=>5),	// 一段时间内连续操作的数量上限
	'badword_on' => 0,		// 开启关键词过滤
	
	'tietuku_on' => 0,		// 是否开启贴图库，节省站点空间
	'tietuku_token' => '00f47da319173e011683b6f4c63b46f8fe8a9471:ak9XYzQ5YmhIalIwYlNwMFJwaVB6Vm9XMFBjPQ==:eyJkZWFkbGluZSI6MTQ0MDY2MzkzOSwiYWN0aW9uIjoiZ2V0IiwidWlkIjoiNTgyNyIsImFpZCI6IjEyNzc5IiwiZnJvbSI6ImZpbGUifQ==',
	
	'version' => '3.0',		// 版本
	'installed' => 0,		// 安装时间
);

?>
