<?php

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "ALTER TABLE ADD INDEX {$tablepre}user mobile(mobile)";

$r = db_exec($sql);

$kv = kv_get('mobile_setting');
if(!$kv) {
	
	$kv = array();
	$kv['login_type'] = 0;
	$kv['find_pw_on'] = 0;
	$kv['create_user_on'] = 0;
	$kv['bind_on'] = 0;
	$kv['force_bind_on'] = 0;
	$kv['send_plat'] = 0;
	$kv['tencent_appid'] = '';
	$kv['tencent_appkey'] = '';
	$kv['aliyun_appid'] = '';
	$kv['aliyun_appkey'] = '';
	
	kv_set('mobile_setting', $kv);
}

?>