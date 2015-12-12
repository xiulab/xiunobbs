<?php

define('DEBUG', 1); 				// 发布的时候改为 0 
define('APP_NAME', 'bbs');			// 应用的名称
define('APP_PATH', '../../');			// 应用的路径

chdir(APP_PATH);

$conf = include './conf/conf.php';
include './xiunophp/xiunophp.php';
include './xiunophp/form.func.php';
include './model.inc.php';
include './plugin/xn_qq_login/qq_login.func.php';

$user = user_token_get('', 'bbs');
$user['gid'] != 1 AND message(-1, '需要管理员权限才能设置。');

// 检测浏览器
$browser = get__browser();
check_browser($browser);

$runtime = runtime_init();

if($method == 'GET') {

	$qq = kv_get('qq_login');
	
	!isset($qq['enable']) && $qq['enable'] = 0;
	!isset($qq['meta']) && $qq['meta'] = '';
	!isset($qq['appid']) && $qq['appid'] = '';
	!isset($qq['appkey']) && $qq['appkey'] = '';
	
	$input['enable'] = form_radio_yes_no('enable', $qq['enable']);
	$input['meta'] = form_text('meta', htmlspecialchars($qq['meta']), 600);
	$input['appid'] = form_text('appid', $qq['appid'], 300);
	$input['appkey'] = form_text('appkey', $qq['appkey'], 300);
	
	$header = array();
	$header['title'] = 'QQ 登陆设置';
	
	include './plugin/xn_qq_login/setting.htm';

} else {

	$enable = param('enable', 0);
	$meta = param('meta', '', FALSE);
	$appid = param('appid');
	$appkey = param('appkey');
	
	$arr = array('enable'=>$enable, 'meta'=>$meta, 'appid'=>$appid, 'appkey'=>$appkey);
	kv_set('qq_login', $arr);
	
	// 此处会丢失
	runtime_set('qq_login_enable', $enable, TRUE);
	
	message(0, '设置成功！');
}

?>