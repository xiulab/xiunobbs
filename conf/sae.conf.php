<?php

// 参考： http://apidoc.sinaapp.com/class-SaeStorage.html

// 顺便吐槽下，居然没有 createDomain() 的接口，需要开发者手工创建一个 upload 的 domain

// 虽然 SAE 很有争议，但是还是有很多开发者想玩一玩，XN3 只需要一个文件配置文件就搞定。正式运营环境建议使用 VPS 或者独立服务器。
if(IN_SAE) {
	$saestorage = new SaeStorage();
	//$saestorage->setDomainAttr('domain', array('private'=>'false'));
	$n = $saestorage->getDomainCapacity('upload');
	//if($n == NULL || $saestorage->errno() == -7) exit('SaeStorage upload 磁盘配额不足!');
	if($n == NULL || $saestorage->errno()) exit($saestorage->errmsg());
	
	$conf['db'] = array (
		'type'=>'mysql',
		'mysql' => array (
			'master' => array (								
				'host' => SAE_MYSQL_HOST_M.(SAE_MYSQL_PORT == 3306 ? '' : ':'.SAE_MYSQL_PORT),			
				'user' => SAE_MYSQL_USER,			
				'password' => SAE_MYSQL_PASS,		
				'name' => SAE_MYSQL_DB,		
				'charset' => 'utf8',				
				'engine'=>'MyISAM',
			),			
			'slaves' => array()
		)
	);
	$conf['upload_url'] = $saestorage->geturl('upload', '').'/';
	$conf['upload_path'] = 'saestor://upload/';
	$conf['tmp_path'] = SAE_TMP_PATH;
}
	
// 函数如果放在 if 里面不利于 opcode 缓存。
/*function sae_move_upload_file($srcfile, $destfile) {
	global $saestorage;
	return $saestorage->upload('upload', $srcfile, $destfile);
}*/

?>
