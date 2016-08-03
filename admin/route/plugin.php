<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_zip.func.php';

$action = param(1);

// 初始化插件变量
plugin_init();


// 本地插件
if(empty($action) || $action == 'local_list') {

	$header['title']    = '插件管理';
	$header['mobile_title'] = '插件管理';
	
	// 本地插件
	$pluginlist = plugin_local_list();
	include "./admin/view/plugin_local_list.htm";

} elseif($action == 'official_list') {

	$pagesize = 10;
	$page = param(2, 1);
	$cateid = param(3, 0);
	$cond = $cateid ? array('cateid'=>$cateid) : array();
			
	// 插件分类
	$pugin_cates = array(0=>'所有插件', 1=>'风格模板', 2=>'小型插件', 3=>'大型插件', 4=>'接口整合', 99=>'未分类');

	// 线上插件
	$pluginlist = plugin_official_list($cond, array(), $page, $pagesize);
	$total = plugin_official_total();
	$pages = pagination(url('plugin-official_list-{page}'), $total, $page, $pagesize);
	
} elseif($action == 'read') {
	
	$dir = param(2);
	$plugin = plugin_read($dir);
	empty($plugin) AND message(-1, '插件不存在');
	
	include "./admin/view/plugin_read.htm";
	
// 下载官方插件。
} elseif($action == 'download') {
	
	$tmppath = ini_get('upload_tmp_dir').'/';
	$tmppath == '/' AND $tmppath = './tmp/';

	$dir = param(2);
	!preg_match('#^\w+$#', $dir) AND message(-1, 'dir 不合法。');
	
	$official = plugin_official_read($dir);
	empty($official) AND message(-1, '插件不存在');
	
	// 检查版本
	if(version_compare($conf['version'], $official['bbs_version']) == -1) {
		message(-1, "此插件依赖的 Xiuno BBS 最低版本为 $official[bbs_version] ，您当前的版本：".$conf['version']);
	}
	
	// 下载，解压，校验
	$app_url = http_url_path();
	$siteid =  md5($app_url.$conf['auth_key']);
	$app_url = urlencode($app_url);
	$url = "http://plugin.xiuno.com/plugin-down-dir-$dir-siteid-$siteid-ajax-1.htm?app_url=$app_url";

	// 服务端开始下载
	$s = http_get($url, 60);
	if(empty($s) || substr($s, 0, 2) != 'PK') {
		$arr = xn_json_decode($s);
		empty($arr['message']) && $arr['message'] = '';
		message(-1, '服务端返回数据错误：'.$arr['message']);
	}
	$zipfile = $tmppath.$dir.'.zip';
	$destpath = "./plugin/$dir/";
	file_put_contents($zipfile, $s);
	xn_unzip($zipfile, $destpath);
	unlink($zipfile);
	
	if(!is_dir("./plugin/$dir")) {
		message(-1, "插件可能下载失败，目录不存在: plugin/$dir");
	} else {
		message(0, '插件下载解压成功:'.$destpath);
	}
	
}

?>