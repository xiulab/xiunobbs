<?php

/*
	功能：操作数据库
	注意：发生错误的时候，返回 FALSE
*/

chdir('../../../');

// 请修改配置文件，设置正确的 mysql 账号密码
$conf = include './simple/4/conf.php';

include './xiunophp/xiunophp.php';

// 创建表
$r = db_exec("DROP TABLE IF EXISTS `test_user`");
$r = db_exec("CREATE TABLE `test_user` (
  uid int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户编号',
  username char(32) NOT NULL DEFAULT '' COMMENT '用户名',
  password char(32) NOT NULL DEFAULT '' COMMENT '密码',
  PRIMARY KEY (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");

// 插入数据
$r = db_exec("INSERT INTO `test_user` SET username='Jack'");
$r = db_exec("INSERT INTO `test_user` SET username='Tom'");

// 查找一条数据
$arr = db_find_one("SELECT * FROM `test_user` WHERE username='Jack'");
print_r($arr);

// 查找多条数据
$arrlist = db_find("SELECT * FROM `test_user` WHERE uid>0");
print_r($arrlist);


/*

结果输出：

Array
(
    [uid] => 1
    [username] => Jack
)

Array
(
    [0] => Array
        (
            [uid] => 1
            [username] => Jack
        )

    [1] => Array
        (
            [uid] => 2
            [username] => Tom
        )

)

*/

?>