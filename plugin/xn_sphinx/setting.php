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

	$sphinx = kv_get('sphinx');
	
	!isset($sphinx['enable']) && $sphinx['enable'] = 0;
	!isset($sphinx['sphinx_host']) && $sphinx['sphinx_host'] = '';
	!isset($sphinx['sphinx_port']) && $sphinx['sphinx_port'] = '';
	!isset($sphinx['sphinx_datasrc']) && $sphinx['sphinx_datasrc'] = '';
	!isset($sphinx['sphinx_deltasrc']) && $sphinx['sphinx_deltasrc'] = '';
	
	$input['enable'] = form_radio_yes_no('enable', $sphinx['enable']);
	$input['sphinx_host'] = form_text('sphinx_host', $sphinx['sphinx_host'], 300);
	$input['sphinx_port'] = form_text('sphinx_port', $sphinx['sphinx_port'], 100);
	$input['sphinx_datasrc'] = form_text('sphinx_datasrc', $sphinx['sphinx_datasrc'], 300);
	$input['sphinx_deltasrc'] = form_text('sphinx_deltasrc', $sphinx['sphinx_deltasrc'], 300);
	
	$header = array();
	$header['title'] = 'Sphinx 搜索服务设置';
	
	include './plugin/xn_sphinx/setting.htm';

} else {

	$enable = param('enable', 0);
	$sphinx_host = param('sphinx_host');
	$sphinx_port = param('sphinx_port');
	$sphinx_datasrc = param('sphinx_datasrc');
	$sphinx_deltasrc = param('sphinx_deltasrc');
	
	$arr = array('enable'=>$enable, 'sphinx_host'=>$sphinx_host, 'sphinx_port'=>$sphinx_port, 'sphinx_datasrc'=>$sphinx_datasrc, 'sphinx_deltasrc'=>$sphinx_deltasrc);
	kv_set('sphinx', $arr);
	
	message(0, '设置成功！');
}

?>