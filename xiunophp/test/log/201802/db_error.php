<?php exit;?>	2018-02-05 21:28:07	255.255.255.255		0	SQL:REPLACE INTO bbs_cache (`k`,`v`,`expiry`) VALUES ('bbs_test','[\"123\"]','0') errno: 0, errstr: 连接数据库服务器失败:could not find driver
<?php exit;?>	2018-02-05 21:28:07	255.255.255.255		0	SQL:DELETE FROM bbs_cache  WHERE `k`='bbs_test'  errno: 0, errstr: 连接数据库服务器失败:could not find driver
<?php exit;?>	2018-02-05 21:34:02	255.255.255.255		0	SQL:REPLACE INTO bbs_cache (`k`,`v`,`expiry`) VALUES ('bbs_test','[\"123\"]','0') errno: 0, errstr: 连接数据库服务器失败:could not find driver
<?php exit;?>	2018-02-05 21:39:31	255.255.255.255		0	SQL:REPLACE INTO bbs_cache (`k`,`v`,`expiry`) VALUES ('pre_test','[\"123\"]','0') errno: 0, errstr: 连接数据库服务器失败:could not find driver
<?php exit;?>	2018-02-05 21:39:50	255.255.255.255		0	SQL:REPLACE INTO bbs_cache (`k`,`v`,`expiry`) VALUES ('pre_test','[\"123\"]','0') errno: 0, errstr: 连接数据库服务器失败:could not find driver
<?php exit;?>	2018-02-05 21:39:52	255.255.255.255		0	SQL:DELETE FROM bbs_cache  WHERE `k`='pre_test'  errno: 0, errstr: 连接数据库服务器失败:could not find driver
<?php exit;?>	2018-02-05 22:18:33	127.0.0.1	/bbs.xiuno.com/xiunophp/test/test_mysql.php	0	SQL:SELECT * FROM `user` WHERE uid='1' errno: 1146, errstr: Table 'xiuno8.user' doesn't exist
<?php exit;?>	2018-02-05 22:18:33	127.0.0.1	/bbs.xiuno.com/xiunophp/test/test_mysql.php	0	SQL:INSERT INTO user SET uid=1000, username='test1000' errno: 1146, errstr: Table 'xiuno8.user' doesn't exist
<?php exit;?>	2018-02-05 22:18:33	127.0.0.1	/bbs.xiuno.com/xiunophp/test/test_mysql.php	0	SQL:SELECT * FROM user WHERE uid='1000' errno: 1146, errstr: Table 'xiuno8.user' doesn't exist
<?php exit;?>	2018-02-05 22:18:33	127.0.0.1	/bbs.xiuno.com/xiunophp/test/test_mysql.php	0	SQL:DELETE FROM user WHERE uid='1000' errno: 1146, errstr: Table 'xiuno8.user' doesn't exist
<?php exit;?>	2018-02-05 22:18:33	127.0.0.1	/bbs.xiuno.com/xiunophp/test/test_mysql.php	0	SQL:SELECT * FROM user WHERE uid='0' errno: 1146, errstr: Table 'xiuno8.user' doesn't exist
