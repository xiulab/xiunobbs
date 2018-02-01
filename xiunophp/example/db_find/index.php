<?php

/*
	演示操作数据库的方法
	XiunoPHP 会自动根据需求连接数据库，不使用不连接。
*/

// 确保您的 DB 账号密码填写正确
$conf = array (
	'db'=>array(
		'type'=>'pdo_mysql',
		'pdo_mysql' => array (
			'master' => array (								
				'host' => 'localhost',				
				'user' => 'root',
				'password' => 'root',	
				'name' => 'test',
				'tablepre' => '',
				'charset' => 'utf8',				
				'engine'=>'myisam',  // innodb
			),			
			'slaves' => array()
		)
	)
);

define('DEBUG', 2);

include '../../xiunophp.php';

$r = db_exec('CREATE TABLE test_user(id int(11), name char(32)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;');
db_insert('test_user', array('id'=>1, 'name'=>'Jack'));
db_insert('test_user', array('id'=>2, 'name'=>'Ok'));
$user = db_find_one('test_user', array('id'=>1));

print_r($user);

// 你会发现，全部是函数操作，不需要 new，也不用管多实例（当然也支持多个实例）
// 更多方法请参考 xiunophp/db.func.php

?>