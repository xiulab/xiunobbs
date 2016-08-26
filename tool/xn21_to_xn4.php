<?php

/*
	功能：本程序用于将 xn21 转换到 xn4
	步骤：
		假定您的域名为：http://bbs.domain.com/
		假定您的目录为：/data/wwwroot/bbs.domain.com/
		
		1. 备份好 2.1 的数据库
		2. 新建 4.0 的数据库 xiuno4
		3. 把 2.1 的移动到 /data/wwwroot/bbs.domain.com/old 目录，新上传 4.0 到 /data/wwwroot/bbs.domain.com/ 下
		4. 安装 4.0，访问 http://bbs.domain.com/install/
		5. 安装完毕以后，将  xn21_to_xn4.php 上传到 /data/wwwroot/bbs.domain.com/ 下
		6. 命令行执行:
			cd /data/wwwroot/bbs.domain.com/
			php xn21_to_xn4.php
*/

// 需要在命令行下运行。

define('XIUNO_BBS_2_PATH', './old/');

define('DEBUG', 1);

$tablepre = 'bbs_';

if(!$oldconf = include XIUNO_BBS_2_PATH.'conf/conf.php') {
	exit('请将原来的整站移动到 ./old 目录');
}

if(!$conf = include './conf/conf.php') {
	exit('请先安装完 Xiuno BBS 4.0。');
}

include './xiunophp/xiunophp.php';

$oldconf['db']['pdo_mysql']['master']['tablepre'] = 'bbs_';
$oldconf['db']['mysql']['master']['tablepre'] = 'bbs_';
$db = db_new($conf['db']);
$olddb = db_new($oldconf['db']);

!db_connect($db) AND exit('连接 4.0 数据库失败:'.$db->errstr);
!db_connect($olddb) AND exit('连接 3.0 数据库失败:'.$olddb->errstr);

if($conf['db']['pdo_mysql']['master']['host'] == $oldconf['db']['pdo_mysql']['master']['host'] && $conf['db']['pdo_mysql']['master']['name'] == $oldconf['db']['pdo_mysql']['master']['name']) {
	exit('不能在同一个数据库里升级，否则数据会被清空！请将新论坛安装到其他数据库。');
}

echo "upgrade group:\r\n";
$grouplist = $olddb->sql_find("SELECT * FROM {$tablepre}group");
$db->exec("TRUNCATE `{$tablepre}group`");
foreach ($grouplist as $group) {
	$group['groupid'] > 10 && $group['groupid'] += 90;
	$arr = array(
		'gid'=>$group['groupid'],
		'name'=>$group['name'],
		'allowread'=>$group['allowread'],
		'allowthread'=>$group['allowthread'],
		'allowpost'=>$group['allowpost'],
		'allowattach'=>$group['allowattach'],
		'allowdown'=>$group['allowdown'],
		'allowtop'=>$group['allowtop'],
		'allowupdate'=>$group['allowupdate'],
		'allowdelete'=>$group['allowdelete'],
		'allowmove'=>$group['allowmove'],
		'allowbanuser'=>$group['allowbanuser'],
		'allowdeleteuser'=>$group['allowdeleteuser'],
		'allowviewip'=>$group['allowviewip'],
		
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO `{$tablepre}group` $sqladd");
	if($r === FALSE) echo($db->errstr);
	echo ".";
}
echo "[ok]\r\n";
unset($grouplist);


echo "upgrade user:\r\n";
$userlist = $olddb->sql_find("SELECT * FROM {$tablepre}user");
$db->exec("TRUNCATE {$tablepre}user");
foreach ($userlist as $user) {
	$user['groupid'] > 10 && $user['groupid'] += 90;
	$arr = array(
		'uid'=>$user['uid'],
		'gid'=>$user['groupid'],
		'email'=>$user['email'],
		'username'=>$user['username'],
		'password'=>$user['password'],
		'salt'=>$user['salt'],
		'threads'=>$user['threads'],
		'posts'=>$user['posts'],
		'credits'=>$user['credits'],
		'create_ip'=>$user['create_ip'],
		'create_date'=>$user['create_date'],
		'avatar'=>$user['avatar'],
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO bbs_user $sqladd");
	if($r === FALSE) echo($db->errstr);
	
	echo ".";
}
echo "[ok]\r\n";
unset($userlist);

echo "upgrade qq login:\r\n";

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}user_open_plat (
	uid int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户编号',
	platid tinyint(1) NOT NULL DEFAULT '0' COMMENT '平台编号 0:本站 1:QQ 登录 2:微信登陆 3:支付宝登录 ',
	openid char(40) NOT NULL DEFAULT '' COMMENT '第三方唯一标识',
	PRIMARY KEY (uid),
	KEY openid_platid (platid,openid)
) ENGINE=MyISAM AUTO_INCREMENT=8805 DEFAULT CHARSET=utf8
";
$r = db_exec($sql);
$r === FALSE AND message(-1, '创建表结构失败'); // 中断，安装失败。

$userlist = $olddb->sql_find("SELECT * FROM {$tablepre}user_qqlogin");
if($userlist) {
	$db->exec("TRUNCATE `bbs_user_open_plat`");
	foreach ($userlist as $user) {
		$arr = array(
			'uid'=>$user['uid'],
			'platid'=>1,
			'openid'=>$user['openid'],
		);
		$sqladd = db_array_to_insert_sqladd($arr);
		$r = $db->exec("INSERT INTO {$tablepre}user_open_plat $sqladd");
		echo ".";
	}
	echo "[ok]\r\n";
	unset($userlist);
}

echo "upgrade forum:\r\n";
$forumlist = $olddb->sql_find("SELECT * FROM {$tablepre}forum");
$db->exec("TRUNCATE `{$tablepre}forum`");
foreach ($forumlist as $forum) {
	$arr = array(
		'fid'=>$forum['fid'],
		'name'=>$forum['name'],
		'rank'=>$forum['rank'],
		'threads'=>$forum['threads'],
		'todayposts'=>$forum['todayposts'],
		'todaythreads'=>$forum['todayposts'],
		'brief'=>$forum['brief'],
		'accesson'=>$forum['accesson'],
		'orderby'=>$forum['orderby'],
		'icon'=>0,
		'moduids'=>str_replace("\t", ',', $forum['modids']),
		'seo_title'=>$forum['seo_title'],
		'seo_keywords'=>$forum['seo_keywords'],
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO {$tablepre}forum $sqladd");
	if($r === FALSE) echo($db->errstr);
	
	echo ".";
}
echo "[ok]\r\n";
unset($forumlist);

echo "upgrade forum_access:\r\n";
$accesslist = $olddb->sql_find("SELECT * FROM {$tablepre}forum_access");
$db->exec("TRUNCATE `{$tablepre}forum_access`");
foreach ($accesslist as $access) {
	$arr = array(
		'fid'=>$access['fid'],
		'gid'=>$access['groupid'],
		'allowread'=>$access['allowread'],
		'allowthread'=>$access['allowthread'],
		'allowpost'=>$access['allowpost'],
		'allowattach'=>$access['allowattach'],
		'allowdown'=>$access['allowdown']
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO bbs_forum_access $sqladd");
	if($r === FALSE) echo($db->errstr);
	echo ".";
}
echo "[ok]\r\n";
unset($accesslist);

echo "upgrade thread:\r\n";
$threadlist = $olddb->sql_find("SELECT * FROM {$tablepre}thread");
$db->exec("TRUNCATE `{$tablepre}thread`");
$db->exec("TRUNCATE `{$tablepre}thread_top`");
foreach ($threadlist as $thread) {
	$arr = array(
		'fid'=>$thread['fid'],
		'tid'=>$thread['tid'],
		'top'=>$thread['top'],
		'uid'=>$thread['uid'],
		'subject'=>$thread['subject'],
		'create_date'=>$thread['dateline'],
		'last_date'=>$thread['lastpost'] ? $thread['lastpost'] : $thread['dateline'],
		'views'=>$thread['views'],
		'posts'=>max(0, $thread['posts'] - 1),
		'images'=>$thread['imagenum'],
		'files'=>$thread['attachnum'],
		'mods'=>$thread['modnum'],	# 预留
		'closed'=>$thread['closed'],	# 预留
		'firstpid'=>$thread['firstpid'],
		'lastuid'=>$thread['lastuid'],
		'lastpid'=>0,	# 此处应该求最后一个，暂时没用
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO {$tablepre}thread $sqladd");
	if($r === FALSE) echo($db->errstr);
	
	if($thread['top'] > 0) {
		$db->exec("INSERT INTO {$tablepre}thread_top SET fid='$thread[fid]', tid='$thread[tid]', top='$thread[top]'");
	}
	
	$db->exec("INSERT INTO {$tablepre}mythread SET uid='$thread[uid]',tid='$thread[tid]'");
	
	echo ".";
}
echo "[ok]\r\n";
unset($threadlist);

echo "upgrade post:\r\n";
$postlist = $olddb->sql_find("SELECT * FROM {$tablepre}post");
$db->exec("TRUNCATE `{$tablepre}post`");
$i = 0;
foreach ($postlist as $post) {
	if(strlen($post['message']) > 1024000) continue;
	$arr = array(
		'tid'=>$post['tid'],
		'pid'=>$post['pid'],
		'uid'=>$post['uid'],
		'isfirst'=>($thread['firstpid'] == $post['pid'] ? 1 : 0),
		'create_date'=>$post['dateline'],
		'userip'=>$post['userip'],
		'images'=>$post['imagenum'],
		'files'=>$post['attachnum'],
		'message'=>$post['message'],
		'message_fmt'=>$post['message'],
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO {$tablepre}post $sqladd");
	if($r === FALSE) echo($db->errstr);
	if($i++ > 10) { echo  "."; $i = 0; }
}
echo "[ok]\r\n";
unset($postlist);

echo "upgrade attach:\r\n";
$attachlist = $olddb->sql_find("SELECT * FROM {$tablepre}attach");
$db->exec("TRUNCATE `{$tablepre}attach`");
foreach ($attachlist as $attach) {
	$arr = array(
		'aid'=>$attach['aid'],
		'tid'=>$attach['tid'],
		'pid'=>$attach['pid'],
		'uid'=>$attach['uid'],
		'filesize'=>$attach['filesize'],
		'width'=>$attach['width'],
		'height'=>$attach['height'],
		'filename'=>$attach['filename'],
		'orgfilename'=>$attach['orgfilename'],
		'filetype'=>$attach['filetype'],
		'create_date'=>$attach['dateline'],
		'comment'=>$attach['comment'],
		'downloads'=>$attach['downloads'],
		'credits'=>0,
		'golds'=>0,
		'rmbs'=>0,
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO {$tablepre}attach $sqladd");
	if($r === FALSE) echo($db->errstr);
	echo ".";
}
echo "[ok]\r\n";
unset($attachlist);

echo "upgrade modlog:\r\n";
$modloglist = $olddb->sql_find("SELECT * FROM {$tablepre}modlog");
foreach ($modloglist as $modlog) {
	$arr = array(
		'logid'=>$modlog['logid'],
		'uid'=>$modlog['uid'],
		'tid'=>$modlog['tid'],
		'pid'=>$modlog['pid'],
		'subject'=>$modlog['subject'],
		'comment'=>$modlog['comment'],
		'create_date'=>$modlog['dateline'],
		'action'=>$modlog['action'],
	);
	$sqladd = db_array_to_insert_sqladd($arr);
	$r = $db->exec("INSERT INTO {$tablepre}modlog $sqladd");
	if($r === FALSE) echo($db->errstr);
	echo ".";
}
echo "[ok]\r\n";
unset($modloglist);


echo "upgrade friendlink:\r\n";

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}friendlink (
  linkid bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  type smallint(11) NOT NULL DEFAULT '0',
  rank smallint(11) NOT NULL DEFAULT '0',
  create_date int(11) unsigned NOT NULL DEFAULT '0',
  name char(32) NOT NULL DEFAULT '',
  url char(64) NOT NULL DEFAULT '',
  PRIMARY KEY (linkid),
  KEY type (type)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8
";
$r = db_exec($sql);
$r === FALSE AND message(-1, '创建友情链接表结构失败');

$linklist = $olddb->sql_find("SELECT * FROM {$tablepre}friendlink");
if($linklist) {
	foreach ($linklist as $link) {
		$arr = array(
			'linkid'=>$link['linkid'],
			'type'=>$link['type'],
			'rank'=>$link['rank'],
			'create_date'=>0,
			'name'=>$link['name'],
			'url'=>$link['url'],
		);
		$sqladd = db_array_to_insert_sqladd($arr);
		$r = $db->exec("INSERT INTO bbs_friendlink $sqladd");
		echo ".";
	}
}
echo "[ok]\r\n";
unset($linklist);


// 主题分类


$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag_cate (
	cateid int(11) unsigned NOT NULL AUTO_INCREMENT,
	fid int(11) unsigned NOT NULL DEFAULT '0',		# 属于哪个版块
	name char(32) NOT NULL DEFAULT '',
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (cateid),
	KEY (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag (
	tagid int(11) unsigned NOT NULL AUTO_INCREMENT,
	cateid int(11) unsigned NOT NULL DEFAULT '0',
	name char(32) NOT NULL DEFAULT '',
	rank int(11) unsigned NOT NULL DEFAULT '0',
	enable int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (tagid),
	KEY (cateid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}tag_thread (
	tagid int(11) unsigned NOT NULL DEFAULT '0',
	tid int(11) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (tagid, tid),
	KEY (tid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$r = db_exec($sql);

$tagcatelist = $olddb->sql_find("SELECT * FROM {$tablepre}thread_type_cate");
if($tagcatelist) {
	foreach ($tagcatelist as $tagcate) {
		$arr = array(
			'fid'=>$tagcate['fid'],
			'cateid'=>$tagcate['cateid'],
			'name'=>$tagcate['catename'],
			'rank'=>$tagcate['rank'],
			'enable'=>$tagcate['enable'],
		);
		$sqladd = db_array_to_insert_sqladd($arr);
		$r = $db->exec("INSERT INTO tag_cate $sqladd");
		echo ".";
	}
}
echo "[ok]\r\n";
unset($tagcatelist);

$taglist = $olddb->sql_find("SELECT * FROM {$tablepre}tag");
if($taglist) {
	foreach ($taglist as $tag) {
		$arr = array(
			'fid'=>$tag['fid'],
			'tagid'=>$tag['typeid'],
			'name'=>$tag['typename'],
			'rank'=>$tag['rank'],
			'enable'=>$tag['enable'],
		);
		$sqladd = db_array_to_insert_sqladd($arr);
		$r = $db->exec("INSERT INTO tag_cate $sqladd");
		echo ".";
	}
}
echo "[ok]\r\n";
unset($taglist);

/*
  fid smallint(6) NOT NULL default '0',			# 版块id
  tid int(11) NOT NULL default '0',			# tid
  typeidsum int(11) unsigned NOT NULL default '0',	# 这个值是一个“和”
  */
$taglist = $olddb->sql_find("SELECT * FROM {$tablepre}thread_type_data");
if($taglist) {
	foreach ($taglist as $tag) {
		$arr = array(
			'tagid'=>$tag['fid'],
			'tid'=>$tag['typeid'],
		);
		$sqladd = db_array_to_insert_sqladd($arr);
		$r = $db->exec("INSERT INTO tag_thread $sqladd");
		echo ".";
	}
}
echo "[ok]\r\n";
unset($taglist);



// 站点介绍
/*$arr = $olddb->sql_find_one("SELECT * FROM kv WHERE k='sitebrief'");
$sitebrief = $arr['v'];
file_replace_var('./conf/conf.php', array('sitebrief'=>$sitebrief));*/

// 递归拷贝目录
copy_recusive(XIUNO_BBS_2_PATH.'upload/avatar', "./upload/avatar");
copy_recusive(XIUNO_BBS_2_PATH.'upload/forum', "./upload/forum");
copy_recusive(XIUNO_BBS_2_PATH.'upload/attach', "./upload/attach");

mkdir('./tmp/src', 0777);

echo '<a href="../">升级完成，点击进入论坛。</a>';

?>
