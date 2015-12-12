<?php

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './xiunophp/form.func.php';
include './model.inc.php';

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, '需要管理员权限才能设置。');

// 检测浏览器
$browser = get__browser();
check_browser($browser);

$runtime = runtime_init();

if($method == 'GET') {

	$input = array();
	$input['tietuku_on'] = form_radio_yes_no('tietuku_on', $conf['tietuku_on']);
	$input['tietuku_token'] = form_textarea('tietuku_token', htmlspecialchars($conf['tietuku_token']), 600, 100);
	
	$header = array();
	$header['title'] = '贴图库设置';
	
	include './plugin/xn_tietuku/setting.htm';

} else {

	$tietuku_on = param('tietuku_on', 0);
	$tietuku_token = param('tietuku_token', '', FALSE);
	
	conf_set('tietuku_on', $tietuku_on);
	conf_set('tietuku_token', $tietuku_token);
	conf_save();
	
	message(0, '设置成功！');
}

?>