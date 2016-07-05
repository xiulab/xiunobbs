<?php

!defined('DEBUG') AND exit('Access Denied.');


// 初始状态为通过

$succeed = 1;
$env = $write = array();
get_env($env, $write);

include './install/view/step1.htm';

function get_env(&$env, &$write) {
	$env['os']['name'] = '操作系统';
	$env['os']['must'] = TRUE;
	$env['os']['current'] = PHP_OS;
	$env['os']['need'] = '类UNIX';
	$env['os']['status'] = 1;
	
	$env['php_version']['name'] = 'PHP版本';
	$env['php_version']['must'] = TRUE;
	$env['php_version']['current'] = PHP_VERSION;
	$env['php_version']['need'] = '5.0';
	$env['php_version']['status'] = version_compare(PHP_VERSION , '5') > 0;
	
	$env['gd_version']['name'] = 'GD图像处理库';
	$env['gd_version']['must'] = FALSE;
	$env['gd_version']['need'] = '1.0';
	// 头像缩略需要，没有也可以。
	if(function_exists('gd_info')) {
		$gd_info = gd_info();
		preg_match('/\d(?:.\d)+/', $gd_info['GD Version'], $arr);
		$gd_version = $arr[0];
		$env['gd_version']['current'] = $gd_version;
		$env['gd_version']['status'] = version_compare($gd_version , '1') > 0 ? 1 : 2;
	} else {
		$env['gd_version']['current'] = 'None';
		$env['gd_version']['status'] = 2;
	}
	
	/* PHP7 废弃
	$env['php_short_open_tag']['name'] = 'PHP短标签';
	$env['php_short_open_tag']['must'] = TRUE;
	$env['php_short_open_tag']['current'] = ini_get('short_open_tag') ? '已开启' : '未开启';
	$env['php_short_open_tag']['need'] = '开启';
	$env['php_short_open_tag']['status'] = ini_get('short_open_tag') ? 1 : 0;
	*/

	// 目录可写
	$writedir = array(
		'conf/',
		'log/',
		'tmp/',
		'upload/',
		'plugin/'
	);

	$write = array();
	foreach($writedir as &$dir) {
		$write[$dir] = is_writable_check('./'.$dir);
	}
}

function is_writable_check($file) {
	// 主要是兼容 windows
	try {
		// 配置文件单独测试
		if(strpos($file,'conf/conf.php')) {
			$n = @file_put_contents($file, 'a');
			if($n > 0) {
				unlink($file);
				return TRUE;
			} else {
				return FALSE;
			}
		}
		if(is_file($file)) {
			if(strpos(strtoupper(PHP_OS), 'WIN') !== FALSE) {
				$fp = @fopen($file, 'rb+');
				@fclose($fp);
				return (bool)$fp;
			} else {
				return is_writable($file);
			}
		} elseif(is_dir($file)) {
			$tmpfile = $file.'/____tmp.tmp';
			$n = @file_put_contents($tmpfile, 'a');
			if($n > 0) {
				unlink($tmpfile);
				return TRUE;
			} else {
				return FALSE;
			}
			}
	} catch(Exception $e) {
		return false;
	}
}

?>