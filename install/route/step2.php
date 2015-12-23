<?php

!defined('DEBUG') AND exit('Access Denied.');

$mysql_support = function_exists('mysql_connect');
$pdo_mysql_support = extension_loaded('pdo_mysql');
(!$mysql_support && !$pdo_mysql_support) AND message(0, '当前 PHP 环境不支持 mysql 和 pdo_mysql，无法继续安装。');

if($method == 'GET') {
	
	include './install/view/step2.htm';
	
} else {
	
	$type = param('type');	
	$host = param('host');	
	$name = param('name');	
	$user = param('user');
	$pass = param('pass');
	$force = param('force');
	$adminemail = param('adminemail');
	$adminuser = param('adminuser');
	$adminpass = param('adminpass');
	$test_data = param('test_data', 0);
	empty($host) AND message(1, '数据库主机不能为空。');
	empty($name) AND message(2, '数据库名不能为空。');
	empty($user) AND message(3, '用户名不能为空。');
	empty($adminpass) AND message(8, '管理员密码不能为空！');
	
	// 我还是老老实实用原生吧..
	$status = 'ok';
	$error = '';
	if($type == 'mysql') {
		
		$link = @mysql_connect($host, $user, $pass, TRUE);
		!$link AND message(-1, "MySQL 账号密码可能有误：".mysql_error());
		$r = mysql_select_db($name);
		if(!$r) {
			if(mysql_errno() == 1049) {
				$r = mysql_query("CREATE DATABASE $name");
				$r = mysql_select_db($name);
				!$r AND message(1, 'MySQL 账户权限可能受限：'.mysql_error());
			} else {
				message(1, 'MySQL error:'.mysql_error());
			}
		}
		
		$conf['db']['type'] = 'mysql';
		$conf['db']['mysql'] = array(
			'master' => array (
				'host' => $host,
				'user' => $user,
				'password' => $pass,
				'name' => $name,
				'charset' => 'utf8',
				'engine'=>'MyISAM',
			),
			'slaves' => array()
		);
		$db = new db_mysql($conf['db']['mysql']);
		
	} elseif($type == 'pdo_mysql') {
		
		if(strpos($host, ':') !== FALSE) {
			list($host, $port) = explode(':', $host);
		} else {
			$port = 3306;
		}
		try {
			$link = new PDO("mysql:host=$host;port=$port;", $user, $pass);
		} catch (Exception $e) {    
        		$error = $e->getMessage();
        		message(1, $error);
		}
		
		$host = $host.':'.$port;

		$conf['db']['type'] = 'pdo_mysql';
		$conf['db']['pdo_mysql'] = array(
			'master' => array (
				'host' => $host,
				'user' => $user,
				'password' => $pass,
				'name' => $name,
				'charset' => 'utf8',
				'engine'=>'MyISAM',
			),
			'slaves' => array()
		);
		
		$db = new db_pdo_mysql($conf['db']['pdo_mysql']);
		$db->connect();
		
		if($db->errno == -10000) {
			$db->errno = 0;
			$r = $link->exec("CREATE DATABASE `$name`");
			if($r === FALSE) {
				message(-1, "尝试创建数据库失败：$name");
			}
		} elseif($db->errno != 0) {
			message(-1, $db->errstr);
		}
        } else {
        	
		message(-1, '不支持的 type');   
		     	
	}
	
	$r = $db->find_one("SELECT * FROM `bbs_user` LIMIT 1");
	!empty($r) AND !$force AND message(5, '已经安装过了。');
	
	!is_dir('./upload/avatar') AND mkdir('./upload/avatar', 0777);
	!is_dir('./upload/forum') AND mkdir('./upload/forum', 0777);
	!is_dir('./upload/attach') AND mkdir('./upload/attach', 0777);
	
	$conf['auth_key'] = md5(time()).md5(uniqid());
	file_put_contents('./conf/conf.php', "<?php\r\nreturn ".var_export($conf,true).";\r\n?>");
	
	write_database('./install/install.sql');
	
	
	$salt = rand(100000, 999999);
	$pwd = md5(md5($adminpass).$salt);
	$admin = array (
		'username' => $adminuser,
		'email' => $adminemail,
		'password' => $pwd,
		'salt' => $salt,
		'create_ip' => $longip,
		'create_date' => $time,
	);
	user_update(1, $admin);
	/*friendlink_create(array(
		'name'         => 'Xiuno BBS',
		'url'         => 'http://bbs.xiuno.com/',
		'rank'         => 0,
		'create_date'  => $time,
	));*/
	
	$setting = array('sitebrief'=>'', 'seo_title'=>'', 'seo_keywords'=>'', 'seo_description'=>'', 'footer_code'=>'');
	kv_set('setting', $setting);
	
	// 写测试数据
	if($test_data == 1) {
		runtime_truncate();
		runtime_init();
		online_init();
		for($i=0; $i<5; $i++) {
			$subject = '欢迎使用 Xiuno BBS 3.0 新一代论坛系统。'.$i;
			$message = '祝您使用愉快！';
			$thread = array(
				'fid'=>1,
				'uid'=>1,
				'subject'=>$subject,
				'message'=>$message,
				'seo_url'=>'',
				'time'=>$time,
				'longip'=>$longip,
			);
			$tid = thread_create($thread, $pid);
			
			for($j=0; $j<2; $j++) {
				$post = array(
					'tid'=>$tid,
					'uid'=>1,
					'create_date'=>$time,
					'userip'=>$longip,
					'isfirst'=>0,
					'message'=>$message.rand(1, 10000),
				);
				$pid = post_create($post, 1);
			}
		}
	}

	message(0, '安装成功。');
}



// 写数据库
function write_database($filename, $echo = FALSE) {
	global $db;
	$s = file_get_contents($filename);
	$s = str_replace("\r\n", "\n", $s);
	$s = preg_replace('#\n\#[^\n]*?\n#is', "\n", $s);
	$sqlarr = explode(";\n", $s);

	foreach($sqlarr as $sql) {
		if(!trim($sql)) continue;
		if($echo) echo $sql."<br>\r\n";
		db_exec($sql) === FALSE AND message(-1, "<b>Error:</b><br>\n$sql<br>\n<font color='red'>".$db->errno.":".$db->errstr.'</font>');
	}
}

?>
