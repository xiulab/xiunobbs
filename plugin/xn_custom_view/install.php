<?php

/*
	Xiuno BBS 4.0 插件实例：广告插件安装
	admin/plugin-install-xn_ad.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$setting = setting_get('xn_ad_setting');
if(empty($setting)) {
	$setting = array('body_start'=>'', 'body_end'=>'', 'footer_end'=>'');
	setting_set('xn_ad_setting', $setting);
}

?>