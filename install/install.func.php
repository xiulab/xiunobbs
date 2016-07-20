<?php

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
		$write[$dir] = xn_is_writable('./'.$dir);
	}
}

?>