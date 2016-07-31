<?php

define('DEBUG', 1);
define('MESSAGE_HTM_PATH', './install/view/htm/message.htm');

// 切换到上一级目录，操作很方便。
chdir('../');

$conf = (@include './conf/conf.default.php');

include './xiunophp/xiunophp.php';
include './model.inc.php';
include './install/install.func.php';

$browser = get__browser();
check_browser($browser);

$action = param('action');

// 安装初始化检测,放这里
is_file('./conf/conf.php') AND empty($action) AND !DEBUG AND message(0, jump('程序已经安装过了，如需重新安装，请删除 conf/conf.php ！', '../'));

// 第一步，阅读
if(empty($action)) {
	include "./install/view/htm/index.htm";
} elseif($action == 'env') {
	if($method == 'GET') {
		$succeed = 1;
		$env = $write = array();
		get_env($env, $write);
		include "./install/view/htm/env.htm";
	} else {
	
	}
} elseif($action == 'db') {
	
	if($method == 'GET') {
		
		$succeed = 1;
		$mysql_support = function_exists('mysql_connect');
		$pdo_mysql_support = extension_loaded('pdo_mysql');
		(!$mysql_support && !$pdo_mysql_support) AND message(0, '当前 PHP 环境不支持 mysql 和 pdo_mysql，无法继续安装。');

		include "./install/view/htm/db.htm";
		
	} else {
		
		$type = param('type');	
		$host = param('host');	
		$name = param('name');	
		$user = param('user');
		$password = param('password');
		$force = param('force');
		
		$adminemail = param('adminemail');
		$adminuser = param('adminuser');
		$adminpass = param('adminpass');
		
		empty($host) AND message('host', '数据库主机不能为空。');
		empty($name) AND message('name', '数据库名不能为空。');
		empty($user) AND message('user', '用户名不能为空。');
		empty($adminpass) AND message('adminpass', '管理员密码不能为空！');
		empty($adminemail) AND message('adminemail', '管理员密码不能为空！');
		
		// 设置超时尽量短一些
		set_time_limit(60);
		ini_set('mysql.connect_timeout',  5);
		ini_set('default_socket_timeout', 5); 

		$conf['db']['type'] = $type;	
		$conf['db']['mysql']['master']['host'] = $host;
		$conf['db']['mysql']['master']['name'] = $name;
		$conf['db']['mysql']['master']['user'] = $user;
		$conf['db']['mysql']['master']['password'] = $password;
		$conf['db']['pdo_mysql']['master']['host'] = $host;
		$conf['db']['pdo_mysql']['master']['name'] = $name;
		$conf['db']['pdo_mysql']['master']['user'] = $user;
		$conf['db']['pdo_mysql']['master']['password'] = $password;
		
		$db = db_new($conf['db']);
		if(db_connect($db) === FALSE) {
			message(-1, "$errstr (errno: $errno)");
		} 
		
		// 连接成功以后，开始建表，导数据。
		
		install_sql_file('./install/install.sql');
		
		// 生成 auth_key
		$conf['auth_key'] = xn_rand(64);
		
		// 设置为已经安装
		$conf['installed'] = 1;
		
		// 初始化
		touch('./conf/conf.php');
		
		//$conf2['log_path'] = './log/';
		//$conf2['tmp_path'] = './tmp/';
		//$conf2['upload_path'] = './upload/';

		// 写入配置文件
		conf_save('./conf/conf.php', $conf);
		
		message(0, '恭喜，安装成功');
	}
}


?>
