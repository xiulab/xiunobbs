<?php

define('DEBUG', 1);
define('MESSAGE_HTM_PATH', './install/view/htm/message.htm');

// 切换到上一级目录，操作很方便。
chdir('../');

$conf = (@include './conf/conf.default.php');
$lang = include "./lang/$conf[lang]/bbs.php";
$lang += include "./lang/$conf[lang]/bbs_install.php";

include './xiunophp/xiunophp.php';
include './model.inc.php';
include './install/install.func.php';

$browser = get__browser();
check_browser($browser);

$action = param('action');

// 安装初始化检测,放这里
is_file('./conf/conf.php') AND empty($action) AND !DEBUG AND message(0, jump(lang('installed_tips'), '../'));

// 第一步，阅读
if(empty($action)) {
	
	if($method == 'GET') {
		$input = array();
		$input['lang'] = form_select('lang', array('zh-cn'=>'简体中文', 'zh-tw'=>'繁体中文', 'en-us'=>'English'), $conf['lang']);
		
		// 修改 conf.php
		include "./install/view/htm/index.htm";
	} else {
		$_lang = param('lang');
		$conf['lang'] = $_lang;
		//$r = conf_save('./conf/conf.php', $conf);
		$r = file_replace_var('./conf/conf.default.php', array('lang'=>$_lang));
		$r === FALSE AND message(-1, jump(lang('please_set_conf_file_writable'), ''));
		http_location('index.php?action=license');
	}
	
} elseif($action == 'license') {
	
	
	// 设置到 cookie
	
	include "./install/view/htm/license.htm";
	
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
		(!$mysql_support && !$pdo_mysql_support) AND message(0, lang('evn_not_support_php_mysql'));

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
		
		empty($host) AND message('host', lang('dbhost_is_empty'));
		empty($name) AND message('name', lang('dbname_is_empty'));
		empty($user) AND message('user', lang('dbuser_is_empty'));
		empty($adminpass) AND message('adminpass', lang('adminuser_is_empty'));
		empty($adminemail) AND message('adminemail', lang('adminpass_is_empty'));
		
		
		
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
		
		// 初始化
		copy('./conf/conf.default.php', './conf/conf.php');
		
		//$conf2['log_path'] = './log/';
		//$conf2['tmp_path'] = './tmp/';
		//$conf2['upload_path'] = './upload/';

		// 写入配置文件
		//conf_save('./conf/conf.php', $conf);
		
		// 管理员密码
		$salt = xn_rand(16);
		$password = md5(md5($adminpass).$salt);
		db_update('user', array('uid'=>1), array('username'=>$adminuser, 'password'=>$password, 'salt'=>$salt));
		
		$replace = array();
		$replace['db'] = $conf['db'];
		$replace['auth_key'] = xn_rand(64);
		$replace['installed'] = 1;
		file_replace_var('./conf/conf.php', $replace);
		
		message(0, lang('conguralation_installed'));
	}
}


?>
