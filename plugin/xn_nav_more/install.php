<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆安装
	admin/plugin-install-xn_friendlink.htm
*/

!defined('DEBUG') AND exit('Forbidden');

// 初始化
$kv = kv_get('qq_login');
if(!$kv) {
	$kv = array('meta'=>'', 'appid'=>'', 'appkey'=>'');
	kv_set('qq_login', $kv);
}
	

?>